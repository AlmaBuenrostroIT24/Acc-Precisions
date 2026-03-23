<?php

namespace App\Http\Controllers;

use App\Models\Costing\Costing;
use App\Models\Costing\CostingOperation;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\OrderSchedule;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CostingController extends Controller
{
    public function index()
    {
        $search = trim((string) request('search'));

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

                return (object) [
                    'pn' => $pn,
                    'total_orders' => $group->count(),
                    'latest_due_date' => $latestDueDate,
                    'customer_summary' => $customers->take(2)->implode(', '),
                    'customer_count' => $customers->count(),
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
            return view('quotes.costing._results', compact('pnOrders'));
        }

        return view('quotes.costing.index', compact('orders', 'pnOrders', 'search'));
    }

    public function edit(OrderSchedule $order)
    {
        $order->loadMissing('costing.operations');

        $costing = $order->costing;
        $operations = $costing?->operations ?? collect();
        $blankRows = max(6, $operations->count());

        return view('quotes.costing.edit', [
            'order' => $order,
            'costing' => $costing,
            'operations' => $operations,
            'blankRows' => $blankRows,
        ]);
    }

    public function update(Request $request, OrderSchedule $order)
    {
        $validated = $request->validate([
            'type_material' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'notes_bottom' => ['nullable', 'string'],
            'total_material' => ['nullable', 'string'],
            'total_outsource' => ['nullable', 'string'],
            'sale_price' => ['nullable', 'string'],
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

        DB::transaction(function () use ($validated, $order) {
            $notes = trim((string) ($validated['notes_bottom'] ?? ''));
            if ($notes === '') {
                $notes = trim((string) ($validated['notes'] ?? ''));
            }

            $costing = Costing::query()->updateOrCreate(
                ['order_schedule_id' => $order->id],
                [
                    'status' => 'draft',
                    'type_material' => $validated['type_material'] ?? null,
                    'total_material' => $this->parseMoney($validated['total_material'] ?? null),
                    'total_outsource' => $this->parseMoney($validated['total_outsource'] ?? null),
                    'total_time_order' => $this->timeStringToDecimalHours($validated['total_time_order'] ?? null),
                    'total_labor' => $this->parseMoney($validated['total_labor'] ?? null),
                    'sale_price' => $this->parseMoney($validated['sale_price'] ?? null),
                    'grandtotal_cost' => $this->parseMoney($validated['grandtotal_cost'] ?? null),
                    'difference_cost' => $this->parseMoney($validated['difference_cost'] ?? null),
                    'percentage' => $this->parseMoney($validated['percentage'] ?? null),
                    'notes' => $notes !== '' ? $notes : null,
                    'updated_by' => Auth::id(),
                    'created_by' => optional($order->costing)->created_by ?? Auth::id(),
                ]
            );

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

            $costing->operations()->delete();

            foreach ($operations as $operation) {
                $costing->operations()->create([
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
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
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

    public function pdf(OrderSchedule $order)
    {
        $pdf = Pdf::loadView('quotes.costing.pdf', [
            'order' => $order,
        ])->setPaper('letter', 'portrait');

        $cleanWorkId = preg_replace('/[\/\\\\]/', '_', (string) $order->work_id);

        return $pdf->stream("costing_{$cleanWorkId}.pdf");
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
}
