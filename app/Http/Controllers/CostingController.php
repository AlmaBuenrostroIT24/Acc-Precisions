<?php

namespace App\Http\Controllers;

use App\Models\Costing\Costing;
use App\Models\Costing\CostingLog;
use App\Models\Costing\CostingOperation;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\OrderSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CostingController extends Controller
{
    public function index()
    {
        $search = trim((string) request('search'));
        $costingsByOrder = Costing::query()
            ->select(['order_schedule_id', 'sale_price', 'price_pcs', 'grandtotal_cost', 'difference_cost', 'notes', 'updated_at'])
            ->get()
            ->keyBy('order_schedule_id');

        $costingOrderIds = $costingsByOrder
            ->keys()
            ->flip();

        $orders = OrderSchedule::query()
            ->select([
                'id',
                'work_id',
                'co',
                'cust_po',
                'PN as pn',
                'Part_description',
                'costumer as customer',
                'qty',
                'wo_qty',
                'operation',
                'due_date',
            ])
            ->orderByDesc('due_date')
            ->get();

        $orders->transform(function ($order) use ($costingOrderIds, $costingsByOrder) {
            $order->has_costing = $costingOrderIds->has($order->id);
            $costing = $costingOrderIds->has($order->id) ? $costingsByOrder->get($order->id) : null;
            $order->sale_price = (float) ($costing->sale_price ?? 0);
            $order->grandtotal_cost = (float) ($costing->grandtotal_cost ?? 0);
            $order->difference_cost = (float) ($costing->difference_cost ?? 0);
            $order->costing_notes = trim((string) ($costing->notes ?? ''));
            $order->costing_notes_date = optional($costing?->updated_at)->format('Y-m-d H:i:s');
            $order->costing_updated_at = optional($costing?->updated_at)->format('Y-m-d H:i:s');
            $storedPricePcs = (float) ($costing->price_pcs ?? 0);
            $order->price_pcs = $storedPricePcs > 0
                ? $storedPricePcs
                : ((float) ($order->wo_qty ?? 0) > 0
                    ? round($order->grandtotal_cost / (float) $order->wo_qty, 2)
                    : 0);
            return $order;
        });

        $pnOrders = $orders
            ->filter(function ($order) {
                return filled($order->pn) && filled($order->work_id);
            })
            ->sortBy([
                ['pn', 'asc'],
                ['work_id', 'asc'],
            ])
            ->groupBy('pn')
            ->map(function ($group, $pn) {
                $group = $group
                    ->unique('work_id')
                    ->values();

                $latestDueDate = $group
                    ->pluck('due_date')
                    ->filter()
                    ->sortDesc()
                    ->first();

                $customers = $group
                    ->pluck('customer')
                    ->filter()
                    ->unique()
                    ->values();

                $notes = $group
                    ->filter(fn ($order) => filled($order->costing_notes))
                    ->map(function ($order) {
                        return [
                            'work_id' => $order->work_id,
                            'note' => $order->costing_notes,
                            'date' => $order->costing_notes_date ?: 'N/A',
                        ];
                    })
                    ->unique(fn ($item) => $item['work_id'] . '|' . $item['note'] . '|' . $item['date'])
                    ->values();

                return (object) [
                    'pn' => $pn,
                    'total_orders' => $group->count(),
                    'latest_due_date' => $latestDueDate,
                    'customer_summary' => $customers->take(2)->implode(', '),
                    'customer_count' => $customers->count(),
                    'has_costing' => $group->contains(fn ($order) => !empty($order->has_costing)),
                    'notes_summary' => $notes->take(2)->pluck('note')->implode(' | '),
                    'notes_count' => $notes->count(),
                    'notes_items' => $notes,
                    'sale_price' => (float) $group->sum(fn ($order) => (float) ($order->sale_price ?? 0)),
                    'grandtotal_cost' => (float) $group->sum(fn ($order) => (float) ($order->grandtotal_cost ?? 0)),
                    'difference_cost' => (float) $group->sum(fn ($order) => (float) ($order->difference_cost ?? 0)),
                    'orders' => $group,
                ];
            });

        if ($search !== '') {
            $searchLower = mb_strtolower($search);

            $pnOrders = $pnOrders->filter(function ($pnOrder) use ($searchLower) {
                $haystack = mb_strtolower(
                    $pnOrder->pn . ' ' .
                    $pnOrder->customer_summary . ' ' .
                    $pnOrder->orders->pluck('work_id')->implode(' ')
                );

                return str_contains($haystack, $searchLower);
            });
        }

        $pnOrders = $pnOrders->values();

        $summary = [
            'pn_count' => $pnOrders->count(),
            'order_count' => $pnOrders->sum('total_orders'),
            'latest_costing_date' => $pnOrders
                ->flatMap(fn ($item) => $item->orders->pluck('costing_updated_at'))
                ->filter()
                ->sortDesc()
                ->first(),
            'costed_pn_count' => $pnOrders->filter(fn ($item) => !empty($item->has_costing))->count(),
            'notes_pn_count' => $pnOrders->filter(fn ($item) => (int) ($item->notes_count ?? 0) > 0)->count(),
        ];

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $pnOrders->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $pnOrders = new LengthAwarePaginator(
            $currentItems,
            $pnOrders->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        if (request()->ajax()) {
            return view('quotes.costing._results', compact('pnOrders', 'summary'));
        }

        return view('quotes.costing.index', compact('orders', 'pnOrders', 'search', 'summary'));
    }

    public function edit(OrderSchedule $order)
    {
        $order->loadMissing('costing.operations');

        $costing = $order->costing;
        $operations = $costing?->operations ?? collect();
        $blankRows = max(6, $operations->count());
        $costingAudit = null;

        if ($costing) {
            $userIds = collect([$costing->created_by, $costing->updated_by])
                ->filter()
                ->unique()
                ->values();

            $logUserIds = CostingLog::query()
                ->where('costing_id', $costing->id)
                ->whereNotNull('user_id')
                ->pluck('user_id');

            $userIds = $userIds
                ->merge($logUserIds)
                ->filter()
                ->unique()
                ->values();

            $userNames = User::query()
                ->whereIn('id', $userIds)
                ->pluck('name', 'id');

            $createdByName = $costing->created_by ? ($userNames[$costing->created_by] ?? null) : null;
            $updatedByName = $costing->updated_by ? ($userNames[$costing->updated_by] ?? null) : null;
            $otherEditors = $userIds
                ->reject(fn ($id) => in_array($id, array_filter([$costing->created_by, $costing->updated_by]), true))
                ->map(fn ($id) => $userNames[$id] ?? null)
                ->filter()
                ->values();

            if ($createdByName && $updatedByName && $createdByName !== $updatedByName) {
                $costingAudit = "Created by {$createdByName}. Last saved by {$updatedByName}.";
            } else {
                $costingAudit = $updatedByName
                    ? "Saved by {$updatedByName}."
                    : ($createdByName ? "Saved by {$createdByName}." : null);
            }

            if ($otherEditors->isNotEmpty()) {
                $costingAudit .= ' Also edited by ' . $otherEditors->implode(', ') . '.';
            }
        }

        return view('quotes.costing.edit', [
            'order' => $order,
            'costing' => $costing,
            'operations' => $operations,
            'blankRows' => $blankRows,
            'costingAudit' => $costingAudit,
        ]);
    }

    public function update(Request $request, OrderSchedule $order)
    {
        $validated = $request->validate([
            'type_material' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'notes_bottom' => ['nullable', 'string'],
            'drawing_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'quote_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'total_material' => ['nullable', 'string'],
            'total_outsource' => ['nullable', 'string'],
            'sale_price' => ['nullable', 'string'],
            'price_pcs' => ['nullable', 'string'],
            'grandtotal_cost' => ['nullable', 'string'],
            'difference_cost' => ['nullable', 'string'],
            'percentage' => ['nullable', 'string'],
            'total_labor' => ['nullable', 'string'],
            'total_time_order' => ['nullable', 'string'],
            'operations' => ['nullable', 'array'],
            'operations.*.name_operation' => ['nullable', 'string', 'max:150'],
            'operations.*.resource_name' => ['nullable', 'string', 'max:150'],
            'operations.*.time_programming' => ['nullable', 'string'],
            'operations.*.time_setup' => ['nullable', 'string'],
            'operations.*.runtime_pcs' => ['nullable', 'string'],
            'operations.*.runtime_total' => ['nullable', 'string'],
            'operations.*.total_time_operation' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $request, $order) {
            $notes = trim((string) ($validated['notes_bottom'] ?? ''));

            $grandTotalCost = $this->parseMoney($validated['grandtotal_cost'] ?? null);
            $woQty = (float) ($order->wo_qty ?? 0);

            $payload = [
                'status' => 'draft',
                'type_material' => $validated['type_material'] ?? null,
                'total_material' => $this->parseMoney($validated['total_material'] ?? null),
                'total_outsource' => $this->parseMoney($validated['total_outsource'] ?? null),
                'total_time_order' => $this->timeStringToDecimalHours($validated['total_time_order'] ?? null),
                'total_labor' => $this->parseMoney($validated['total_labor'] ?? null),
                'sale_price' => $this->parseMoney($validated['sale_price'] ?? null),
                'price_pcs' => $woQty > 0 ? round($grandTotalCost / $woQty, 2) : 0,
                'grandtotal_cost' => $grandTotalCost,
                'difference_cost' => $this->parseMoney($validated['difference_cost'] ?? null),
                'percentage' => $this->parseMoney($validated['percentage'] ?? null),
                'notes' => $notes !== '' ? $notes : null,
                'updated_by' => Auth::id(),
            ];

            $costing = Costing::query()->firstOrNew([
                'order_schedule_id' => $order->id,
            ]);

            $isNewCosting = !$costing->exists;
            if ($isNewCosting) {
                $costing->created_by = Auth::id();
            }

            $originalCostingValues = $costing->exists
                ? $costing->only(array_merge(array_keys($payload), ['drawing_pdf_path', 'quote_pdf_path']))
                : [];

            if ($request->hasFile('drawing_pdf')) {
                if ($costing->exists && $costing->drawing_pdf_path) {
                    Storage::disk('public')->delete($costing->drawing_pdf_path);
                }

                $payload['drawing_pdf_path'] = $request->file('drawing_pdf')->store(
                    "costings/{$order->id}",
                    'public'
                );
            }

            if ($request->hasFile('quote_pdf')) {
                if ($costing->exists && $costing->quote_pdf_path) {
                    Storage::disk('public')->delete($costing->quote_pdf_path);
                }

                $payload['quote_pdf_path'] = $request->file('quote_pdf')->store(
                    "costings/{$order->id}",
                    'public'
                );
            }

            $costing->fill($payload);
            $costing->save();

            $this->logCostingChanges($costing, $originalCostingValues, $payload, $isNewCosting);

            $operations = collect($validated['operations'] ?? [])
                ->map(function ($operation) {
                    return [
                        'name_operation' => trim((string) ($operation['name_operation'] ?? '')),
                        'resource_name' => trim((string) ($operation['resource_name'] ?? '')),
                        'time_programming' => $this->timeStringToDecimalHours($operation['time_programming'] ?? null),
                        'time_setup' => $this->timeStringToDecimalHours($operation['time_setup'] ?? null),
                        'runtime_pcs' => $this->timeStringToDecimalHours($operation['runtime_pcs'] ?? null),
                        'runtime_total' => $this->timeStringToDecimalHours($operation['runtime_total'] ?? null),
                        'total_time_operation' => $this->timeStringToDecimalHours($operation['total_time_operation'] ?? null),
                    ];
                })
                ->filter(function ($operation, $index) {
                    if ($index === 0) {
                        return true;
                    }

                    return $operation['name_operation'] !== ''
                        || $operation['resource_name'] !== ''
                        || $operation['time_programming'] > 0
                        || $operation['time_setup'] > 0
                        || $operation['runtime_pcs'] > 0
                        || $operation['runtime_total'] > 0
                        || $operation['total_time_operation'] > 0;
                })
                ->values();

            $existingOperations = $costing->operations()->orderBy('id')->get()->values();

            foreach ($operations as $index => $operation) {
                $operationPayload = [
                    'status' => 'active',
                    'name_operation' => $operation['name_operation'] !== '' ? $operation['name_operation'] : null,
                    'resource_name' => $operation['resource_name'] !== '' ? $operation['resource_name'] : null,
                    'time_programming' => $operation['time_programming'],
                    'time_setup' => $operation['time_setup'],
                    'runtime_pcs' => $operation['runtime_pcs'],
                    'runtime_total' => $operation['runtime_total'],
                    'total_time_operation' => $operation['total_time_operation'],
                    'labor_rate' => 120,
                    'operation_cost' => round($operation['total_time_operation'] * 120, 2),
                    'updated_by' => Auth::id(),
                ];

                $existingOperation = $existingOperations->get($index);
                $isNewOperation = !$existingOperation;

                if ($existingOperation) {
                    $originalOperationValues = $existingOperation->only(array_keys($operationPayload));
                    $existingOperation->fill($operationPayload);
                    $existingOperation->save();
                    $this->logOperationChanges($costing, $existingOperation, $originalOperationValues, $operationPayload, false);
                    continue;
                }

                $newOperation = $costing->operations()->create($operationPayload + [
                    'created_by' => Auth::id(),
                ]);

                $this->logOperationChanges($costing, $newOperation, [], $operationPayload, $isNewOperation);
            }

            if ($existingOperations->count() > $operations->count()) {
                $existingOperations
                    ->slice($operations->count())
                    ->each(function (CostingOperation $operation) use ($costing) {
                        CostingLog::create([
                            'costing_id' => $costing->id,
                            'costing_operation_id' => $operation->id,
                            'action' => 'deleted',
                            'description' => sprintf(
                                'Operation %s deleted.',
                                $operation->name_operation ?: ('#' . $operation->id)
                            ),
                            'user_id' => Auth::id(),
                        ]);

                        $operation->update([
                            'deleted_by' => Auth::id(),
                        ]);
                        $operation->delete();
                    });
            }
        });

        if ($request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Costing saved successfully.',
            ]);
        }

        return redirect()
            ->route('costing.edit', $order)
            ->with('success', 'Costing saved successfully.');
    }

    public function deletePdf(OrderSchedule $order, string $type)
    {
        $order->loadMissing('costing');

        $costing = $order->costing;

        if (!$costing) {
            return redirect()
                ->route('costing.edit', $order)
                ->withErrors('No costing record found for this order.');
        }

        if (!in_array($type, ['drawing', 'quote'], true)) {
            abort(404);
        }

        $column = $type === 'drawing' ? 'drawing_pdf_path' : 'quote_pdf_path';

        if (!$costing->{$column}) {
            return redirect()
                ->route('costing.edit', $order)
                ->with('success', 'PDF already removed.');
        }

        DB::transaction(function () use ($costing, $column) {
            $originalValues = $costing->only([$column]);

            Storage::disk('public')->delete($costing->{$column});

            $costing->fill([
                $column => null,
                'updated_by' => Auth::id(),
            ]);
            $costing->save();

            $this->logCostingChanges($costing, $originalValues, [$column => null], false);
        });

        return redirect()
            ->route('costing.edit', $order)
            ->with('success', 'PDF removed successfully.');
    }

    public function uploadPdf(Request $request, OrderSchedule $order, string $type)
    {
        $order->loadMissing('costing');

        $costing = $order->costing;

        if (!$costing) {
            return redirect()
                ->route('costing.edit', $order)
                ->withErrors('Save the costing first before uploading PDFs.');
        }

        if (!in_array($type, ['drawing', 'quote'], true)) {
            abort(404);
        }

        $field = $type === 'drawing' ? 'drawing_pdf' : 'quote_pdf';
        $column = $type === 'drawing' ? 'drawing_pdf_path' : 'quote_pdf_path';
        $label = $type === 'drawing' ? 'Drawing PDF' : 'Quote PDF';

        $validated = $request->validate([
            $field => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $originalValues = $costing->only([$column]);

        if ($costing->{$column}) {
            Storage::disk('public')->delete($costing->{$column});
        }

        $path = $validated[$field]->store("costings/{$order->id}", 'public');

        $costing->fill([
            $column => $path,
            'updated_by' => Auth::id(),
        ]);
        $costing->save();

        $this->logCostingChanges($costing, $originalValues, [$column => $path], false);

        return redirect()
            ->route('costing.edit', $order)
            ->with('success', $label . ' uploaded successfully.');
    }

    public function destroy(OrderSchedule $order)
    {
        $order->loadMissing('costing.operations');

        $costing = $order->costing;

        if (!$costing) {
            return redirect()
                ->route('costing.edit', $order)
                ->withErrors('No costing record found for this order.');
        }

        DB::transaction(function () use ($costing) {
            if ($costing->drawing_pdf_path) {
                Storage::disk('public')->delete($costing->drawing_pdf_path);
            }

            if ($costing->quote_pdf_path) {
                Storage::disk('public')->delete($costing->quote_pdf_path);
            }

            $costing->operations->each(function (CostingOperation $operation) use ($costing) {
                CostingLog::create([
                    'costing_id' => $costing->id,
                    'costing_operation_id' => $operation->id,
                    'action' => 'deleted',
                    'description' => sprintf(
                        'Operation %s deleted with costing removal.',
                        $operation->name_operation ?: ('#' . $operation->id)
                    ),
                    'user_id' => Auth::id(),
                ]);

                $operation->update([
                    'deleted_by' => Auth::id(),
                ]);
                $operation->delete();
            });

            CostingLog::create([
                'costing_id' => $costing->id,
                'action' => 'deleted',
                'description' => 'Costing deleted.',
                'user_id' => Auth::id(),
            ]);

            $costing->update([
                'deleted_by' => Auth::id(),
            ]);
            $costing->delete();
        });

        return redirect()
            ->route('costing')
            ->with('success', 'Costing deleted successfully.');
    }

    public function logs(OrderSchedule $order)
    {
        $logs = CostingLog::query()
            ->whereHas('costing', function ($query) use ($order) {
                $query->where('order_schedule_id', $order->id);
            })
            ->with(['user', 'costingOperation'])
            ->latest('created_at')
            ->get();

        return view('quotes.costing._logs', compact('logs', 'order'));
    }

    public function pdf(OrderSchedule $order)
    {
        $order->loadMissing('costing.operations');

        $pdf = Pdf::loadView('quotes.costing.pdf', [
            'order' => $order,
            'costing' => $order->costing,
            'operations' => $order->costing?->operations ?? collect(),
            'printedBy' => Auth::user()?->name ?? 'N/A',
        ])->setPaper('letter', 'portrait');

        $cleanWorkId = preg_replace('/[\/\\\\]/', '_', (string) $order->work_id);

        return $pdf->stream("costing_{$cleanWorkId}.pdf");
    }

    public function pdfSheet(OrderSchedule $order)
    {
        $order->loadMissing('costing.operations');

        $pdf = Pdf::loadView('quotes.costing.pdf_sheet', [
            'order' => $order,
            'costing' => $order->costing,
            'operations' => $order->costing?->operations ?? collect(),
        ])->setPaper('letter', 'portrait');

        $cleanWorkId = preg_replace('/[\/\\\\]/', '_', (string) $order->work_id);

        return $pdf->stream("costing_sheet_{$cleanWorkId}.pdf");
    }

    private function parseMoney($value): float
    {
        $clean = trim((string) $value);

        if ($clean === '') {
            return 0;
        }

        return (float) str_replace(',', '', $clean);
    }

    private function timeStringToDecimalHours($value): float
    {
        $clean = trim((string) $value);

        if ($clean === '') {
            return 0;
        }

        if (preg_match('/^\d+(?:\.\d+)?$/', $clean)) {
            return (float) $clean;
        }

        $parts = explode(':', $clean);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        $seconds = (int) ($parts[2] ?? 0);

        return round($hours + ($minutes / 60) + ($seconds / 3600), 4);
    }

    private function logCostingChanges(Costing $costing, array $original, array $payload, bool $isNew): void
    {
        $fields = [
            'type_material',
            'drawing_pdf_path',
            'quote_pdf_path',
            'total_material',
            'total_outsource',
            'total_time_order',
            'total_labor',
            'sale_price',
            'price_pcs',
            'grandtotal_cost',
            'difference_cost',
            'percentage',
            'notes',
        ];

        foreach ($fields as $field) {
            $old = $original[$field] ?? null;
            $new = $payload[$field] ?? null;

            if (!$isNew && !$this->valueChanged($old, $new)) {
                continue;
            }

            CostingLog::create([
                'costing_id' => $costing->id,
                'action' => $isNew ? 'created' : 'updated',
                'field_changed' => $field,
                'old_value' => $isNew ? null : $this->displayLogValue($field, $old),
                'new_value' => $this->displayLogValue($field, $new),
                'description' => sprintf(
                    'Costing %s changed from %s to %s.',
                    str_replace('_', ' ', $field),
                    $isNew ? 'blank' : $this->displayLogValue($field, $old),
                    $this->displayLogValue($field, $new)
                ),
                'user_id' => Auth::id(),
            ]);
        }
    }

    private function logOperationChanges(Costing $costing, CostingOperation $operation, array $original, array $payload, bool $isNew): void
    {
        $fields = [
            'name_operation',
            'resource_name',
            'time_programming',
            'time_setup',
            'runtime_pcs',
            'runtime_total',
            'total_time_operation',
            'labor_rate',
            'operation_cost',
        ];

        foreach ($fields as $field) {
            $old = $original[$field] ?? null;
            $new = $payload[$field] ?? null;

            if (!$isNew && !$this->valueChanged($old, $new)) {
                continue;
            }

            CostingLog::create([
                'costing_id' => $costing->id,
                'costing_operation_id' => $operation->id,
                'action' => $isNew ? 'created' : 'updated',
                'field_changed' => $field,
                'old_value' => $isNew ? null : $this->displayLogValue($field, $old),
                'new_value' => $this->displayLogValue($field, $new),
                'description' => sprintf(
                    'Operation %s: %s changed from %s to %s.',
                    $operation->name_operation ?: ('#' . $operation->id),
                    str_replace('_', ' ', $field),
                    $isNew ? 'blank' : $this->displayLogValue($field, $old),
                    $this->displayLogValue($field, $new)
                ),
                'user_id' => Auth::id(),
            ]);
        }
    }

    private function valueChanged($old, $new): bool
    {
        return $this->normalizeLogComparableValue($old) !== $this->normalizeLogComparableValue($new);
    }

    private function normalizeLogComparableValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_numeric($value)) {
            return number_format((float) $value, 4, '.', '');
        }

        $stringValue = trim((string) $value);

        if (preg_match('/^\d+:\d{2}(?::\d{2})?$/', $stringValue)) {
            return number_format($this->timeStringToDecimalHours($stringValue), 4, '.', '');
        }

        return $stringValue;
    }

    private function displayLogValue(string $field, $value): string
    {
        if ($value === null || $value === '') {
            return 'blank';
        }

        if (in_array($field, ['total_material', 'total_outsource', 'total_labor', 'sale_price', 'price_pcs', 'grandtotal_cost', 'difference_cost', 'labor_rate', 'operation_cost'], true)) {
            return number_format((float) $value, 2);
        }

        if (in_array($field, ['percentage'], true)) {
            return number_format((float) $value, 2);
        }

        if (in_array($field, ['total_time_order', 'time_programming', 'time_setup', 'runtime_pcs', 'runtime_total', 'total_time_operation'], true)) {
            return $this->decimalHoursToTimeString((float) $value);
        }

        return (string) $value;
    }

    private function decimalHoursToTimeString(float $value): string
    {
        $totalSeconds = (int) round($value * 3600);
        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
