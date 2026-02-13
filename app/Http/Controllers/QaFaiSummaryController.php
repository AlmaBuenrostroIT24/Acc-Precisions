<?php

namespace App\Http\Controllers;

use App\Models\QaFaiSummary;
use Illuminate\Http\Request;
use App\Models\OrderSchedule;
use Illuminate\Support\Facades\Log;
use App\Models\QaSamplingPlan;
use Illuminate\Support\Facades\DB; // si no tienes modelo Status
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PDF;
use App\Services\InspectionSummary;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Exports\BladeTableExport;

class QaFaiSummaryController extends Controller
{

    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 1. TAB "FAI/IPI SUMMARY" ------------Parts Revision----------------
     * ===================================================================================================================
     */

    public function partsrevision()
    {
        // Solo devuelve la vista; las tablas vendrán por AJAX
        return view('qa.faisummary.faisummary_partsrevision');
    }
    //	31009/1

    public function partsrevisionData(Request $request)
    {
        try {
            $bucket = $request->query('bucket');
            if (!in_array($bucket, ['empty', 'process'], true)) {
                return response()->json(['data' => []]);
            }

            $user = auth()->user();

            $select = [
                'id',
                'parent_id',
                'work_id',
                'PN',
                'Part_description',
                'operation',
                'wo_qty',
                'group_wo_qty',
                'due_date',
                'location',
                'status_inspection',
                'sampling',
                'sampling_check',
                'inspection_progress',
            ];

            // Campos opcionales (evita "Unknown column" si la DB no está migrada)
            foreach (['co', 'cust_po', 'costumer', 'qty', 'ncr_number', 'ncr_notes'] as $col) {
                if (Schema::hasColumn('orders_schedule', $col)) {
                    $select[] = $col;
                }
            }

            $hasNcrCols = Schema::hasColumn('orders_schedule', 'ncr_number') && Schema::hasColumn('orders_schedule', 'ncr_notes');

            $rows = OrderSchedule::query()
                ->select($select)
                ->where('status', '<>', 'sent')
                ->where('status_order', '!=', 'inactive')
                ->where(function ($q) {
                    $q->whereNull('status_inspection')
                        ->orWhere('status_inspection', '<>', 'completed');
                })
                ->whereNull('parent_id')
                // 🔒 Siempre limitamos a Yarnell + Hearst
                ->whereRaw('LOWER(location) IN (?, ?)', ['yarnell', 'hearst'])

                // 👇 Filtros por rol, pero se saltan si el usuario tiene QAAll
                ->when(
                    $user && $user->hasRole('QAdmin') && !$user->hasRole('QA'),
                    fn($q) => $q->whereRaw('LOWER(location) = ?', ['yarnell'])
                )
                ->when(
                    $user && ($user->hasRole('QASupportHearst')) && !$user->hasRole('QA'),
                    fn($q) => $q->whereRaw('LOWER(location) = ?', ['hearst'])
                )

                // Buckets de inspección
                ->when(
                    $bucket === 'empty',
                    fn($q) => $q->where(
                        fn($w) =>
                        $w->whereNull('status_inspection')
                            ->orWhere('status_inspection', 'pending')
                    ),
                    fn($q) => $q->where('status_inspection', 'in_progress')
                )
                ->orderByDesc('due_date')
                ->get();


            $opName = function (int $i): string {
                return match ($i) {
                    1 => '1st Op',
                    2 => '2nd Op',
                    3 => '3rd Op',
                    default => "{$i}th Op"
                };
            };

            $updates = [];

            // 👇 capturamos &$updates por referencia
            $data = $rows->map(function ($r) use ($bucket, $opName, $hasNcrCols, &$updates) {
                $pn   = trim((string) $r->PN);
                $desc = trim(\Illuminate\Support\Str::before((string) $r->Part_description, ','));
                $part = $pn . ' - ' . $desc;

                // 2025-12-17: enviar una fecha "sort" (YYYY-MM-DD) para que DataTables ordene correctamente
                // aunque el display sea M/d/Y.
                $dueSort = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('Y-m-d') : null;
                $dueFormatted = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('M/d/Y') : null;

                $sum = (int) ($r->group_wo_qty ?? 0);

                $btn = '<button class="btn btn-sm btn-erp-primary erp-table-btn"
              data-toggle="modal" data-target="#editModal"
              data-id="' . e($r->id) . '"
              data-workid="' . e($r->work_id) . '"
              data-woqty="' . e($sum) . '"
              data-operation="' . e($r->operation) . '"
              data-pn="' . e($r->PN) . '"
              data-description="' . e($r->Part_description) . '"
              data-sampling="' . e($r->sampling ?? 0) . '"
              data-sampling_check="' . e($r->sampling_check ?? 'Normal') . '">
              <i class="fas fa-edit"></i>
            </button>';

                $btnOther = '';
                if ($bucket === 'process') {
                    $hasNcr = $hasNcrCols && !empty($r->ncr_number);
                    $title = $hasNcr ? ('NCR: ' . $r->ncr_number) : 'Register NCR';

                    // Si la DB aún no tiene columnas NCR, dejamos el modal en modo "solo vista" (sin URL para guardar).
                    $postUrl = $hasNcrCols ? route('schedule.finished.ncr', $r->id) : '';

                    $btnOther = ' <button type="button" class="btn btn-sm btn-erp-warning erp-table-btn ml-1 btn-ncr ' . ($hasNcr ? 'is-active' : '') . '"
                     title="' . e($title) . '"
                     data-id="' . e($r->id) . '"
                     data-url="' . e($postUrl) . '"
                     data-work-id="' . e($r->work_id) . '"
                     data-operation="' . e($r->operation ?? '') . '"
                     data-co="' . e($r->co ?? '') . '"
                     data-cust-po="' . e($r->cust_po ?? '') . '"
                     data-pn="' . e($r->PN ?? '') . '"
                     data-part-description="' . e($r->Part_description ?? '') . '"
                     data-customer="' . e($r->costumer ?? '') . '"
                     data-qty="' . e($r->qty ?? '') . '"
                     data-wo-qty="' . e($sum) . '"
                     data-ncr-reviewer="' . e($r->ncr_reviewer ?? '') . '"
                     data-ncr-number="' . e($hasNcr ? ($r->ncr_number ?? '') : '') . '"
                     data-ncr-notes="' . e($hasNcr ? ($r->ncr_notes ?? '') : '') . '">
                     <i class="fas fa-exclamation-triangle text-purple"></i>
                 </button>';
                }

                $row = [
                    'id'             => (int) $r->id,
                    'part'           => $part,
                    'work_id'        => trim((string) $r->work_id),
                    'actions'        => '<div class="btn-group btn-group-sm">' . $btn . $btnOther . '</div>',
                    'ops'            => (int) ($r->operation ?? 0),
                    'wo_qty'         => $sum,
                    'sampling'       => (int) ($r->sampling ?? 0),
                    'sampling_check' => (string) ($r->sampling_check ?? 'Normal'),
                    'due_date'       => $dueFormatted,
                    'due_date_sort'  => $dueSort,
                ];

                if ($bucket === 'process') {
                    $ops      = (int) ($r->operation ?? 0);
                    $sampling = (int) ($r->sampling  ?? 0);

                    // 👉 Caso especial: SIN operaciones y SIN sampling → SOLO leyenda "Done"
                    if ($ops === 0 && $sampling === 0) {
                        $pct = 100;

                        $row['progress'] = '<span class="badge badge-info">Done</span>';
                        $row['progress_pct'] = $pct;

                        // Guardar en BD si cambió el porcentaje
                        if ((int)($r->inspection_progress ?? 0) !== $pct) {
                            $updates[] = [
                                'id' => (int) $r->id,
                                'inspection_progress' => $pct,
                            ];
                        }

                        // No calculamos nada más para este registro
                        return $row;
                    }

                    // 👉 Resto de casos: cálculo normal de progreso con barra
                    $ipiReq   = max(0, $sampling - 1);
                    $perOpReq = 1 + $ipiReq;
                    $totalReq = $ops * $perOpReq;
                    $done     = 0;

                    if ($ops > 0) {
                        $qtyExpr = \Illuminate\Support\Facades\Schema::hasColumn('qa_faisummary', 'qty_pcs')
                            ? 'qty_pcs'
                            : (\Illuminate\Support\Facades\Schema::hasColumn('qa_faisummary', 'sample_idx')
                                ? 'sample_idx'
                                : '1');

                        $pass = DB::table('qa_faisummary')
                            ->select('operation', 'insp_type', DB::raw("SUM(COALESCE($qtyExpr,1)) as qty"))
                            ->where('order_schedule_id', $r->id)
                            ->whereRaw('LOWER(results) = ?', ['pass'])
                            ->groupBy('operation', 'insp_type')
                            ->get();

                        $fai = [];
                        $ipi = [];
                        foreach ($pass as $p) {
                            $name = (string) $p->operation;
                            $q    = (int) $p->qty;
                            $type = strtoupper((string) $p->insp_type);
                            if ($type === 'FAI') $fai[$name] = ($fai[$name] ?? 0) + $q;
                            if ($type === 'IPI') $ipi[$name] = ($ipi[$name] ?? 0) + $q;
                        }

                        for ($i = 1; $i <= $ops; $i++) {
                            $name = $opName($i);
                            $done += min($fai[$name] ?? 0, 1) + min($ipi[$name] ?? 0, $ipiReq);
                        }
                    }

                    $pct = $totalReq > 0 ? (int) round(($done / $totalReq) * 100) : 0;

                    $row['progress'] = '<div class="progress" data-order-id="' . e($r->id) . '" style="height:18px;">
                  <div class="progress-bar bg-secondary" role="progressbar"
                       style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>';
                    $row['progress_pct'] = $pct;

                    // guardar solo si cambió
                    if ((int) ($r->inspection_progress ?? 0) !== $pct) {
                        $updates[] = [
                            'id' => (int)$r->id,
                            'inspection_progress' => $pct,
                        ];
                    }
                }

                return $row;
            });

            // --- GUARDAR EN LOTE con UPDATE ... CASE (sin upsert) ---
            if (!empty($updates)) {
                $ids = array_column($updates, 'id');
                $caseSql = 'CASE id ';
                foreach ($updates as $u) {
                    $caseSql .= 'WHEN ' . (int)$u['id'] . ' THEN ' . (int)$u['inspection_progress'] . ' ';
                }
                $caseSql .= 'END';

                DB::table('orders_schedule')
                    ->whereIn('id', $ids)
                    ->update([
                        'inspection_progress' => DB::raw($caseSql),
                    ]);
            }

            return response()->json([
                'data' => $data->values()->all(),
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            /* Log::error('partsrevisionData error', [
            'msg' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]); */
            return response()->json(['data' => [], 'error' => 'Server error'], 500);
        }
    }



    public function updateOperation(Request $request, $id)
    {
        $request->validate([
            'operation'      => 'sometimes|integer|min:0',
            'sampling'       => 'sometimes|integer|min:0',
            'sampling_check' => 'sometimes|string|max:100',
        ]);

        if (!$request->hasAny(['operation', 'sampling', 'sampling_check'])) {
            return response()->json(['success' => false, 'message' => 'No fields to update.'], 422);
        }

        $order = OrderSchedule::findOrFail($id);

        // Guardar sampling_check si viene
        if ($request->has('sampling_check')) { // usa has() para permitir string vacío si algún día lo necesitas
            $order->sampling_check = $request->input('sampling_check');
        }

        // MUY IMPORTANTE: asignar operation y sampling aunque sean 0
        if ($request->has('operation')) {
            $order->operation = (int) $request->input('operation', 0);
        }
        if ($request->has('sampling')) {
            $order->sampling = (int) $request->input('sampling', 0);
        }

        // Recalcular totales si cambió operation o sampling (has() permite 0)
        if ($request->has('operation') || $request->has('sampling')) {
            $op  = (int) ($order->operation ?? 0);
            $smp = (int) ($order->sampling ?? 0);

            $order->total_fai = $op;          // 1 por operación
            $order->total_ipi = $op * $smp - $order->total_fai;   // operación * muestreo
        }

        $order->save();

        return response()->json([
            'success'        => true,
            'operation'      => (int) $order->operation,
            'sampling'       => (int) $order->sampling,
            'sampling_check' => (string) ($order->sampling_check ?? ''),
            'total_fai'      => (int) $order->total_fai,
            'total_ipi'      => (int) $order->total_ipi,
        ]);
    }

    public function storeSingle(Request $request)
    {
        //Log::info('storeSingle called', $request->all());
        $validated = $request->validate([
            'order_schedule_id' => 'required|exists:orders_schedule,id',
            'date' => 'required|date_format:Y-m-d',
            'insp_type' => 'required|in:FAI,IPI',
            'operation' => 'required|string',
            'operator' => 'nullable|string',
            'results' => 'required|in:pass,no pass',
            'sb_is' => 'nullable|string',
            'observation' => 'nullable|string',
            'station' => 'nullable|string',
            'method' => 'required|in:Manual,Vmm/Manual,Visual,Vmm,Keyence,Keyence/Manual',
            'inspector' => 'required|string',
            'qty_pcs'   => 'nullable|integer|min:1',
            'qty_process' => 'nullable|integer|min:0',
        ]);

        // Default: si no envían qty_process, guardar 1 (solo para FAI)
        if (($validated['insp_type'] ?? null) === 'FAI' && (!array_key_exists('qty_process', $validated) || $validated['qty_process'] === null)) {
            $validated['qty_process'] = 1;
        }

        // ⭐ Convertir fecha a datetime agregando la hora actual
        $validated['date'] = $validated['date'] . ' ' . now()->format('H:i:s');

        // 🔍 Obtener la ubicación del order_schedule
        $order = \App\Models\OrderSchedule::find($validated['order_schedule_id']);
        if ($order) {
            $validated['loc_inspection'] = $order->location; // 👈 Aquí copiamos location
        }

        if ($request->has('id')) {
            $row = \App\Models\QaFaiSummary::find($request->id);
            if (!$row) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }
            $row->update($validated);
        } else {
            $row = \App\Models\QaFaiSummary::create($validated);
        }
        return response()->json(['success' => true, 'id' => $row->id]);
    }


    /*========== ordenar los FAI summary registrados==========*/
    public function getByOrder($orderScheduleId)
    {
        $rows = \App\Models\QaFaiSummary::where('order_schedule_id', $orderScheduleId)
            ->orderBy('date', 'desc') // más recientes primero 
            ->orderBy('id', 'desc') // desempate estable 
            ->get();
        return response()->json($rows);
    }

    public function get(Request $request)
    {
        $lotSize = max(1, (int) $request->query('lot_size', 0));
        $type    = strtolower(trim((string) $request->query('sampling_type', 'normal')));

        // normaliza alias
        $type = match (true) {
            in_array($type, ['t', 'tight', 'tightened'], true) => 'tightened',
            in_array($type, ['r', 'reduced'], true)          => 'reduced',
            default                                         => 'normal',
        };

        $qtyField = match ($type) {
            'tightened' => 'tightened_qty',
            'reduced'   => Schema::hasColumn('qa_sampling_plans', 'reduced_qty') ? 'reduced_qty' : 'normal_qty',
            default     => 'normal_qty',
        };

        $plan = QaSamplingPlan::query()
            ->where('min_qty', '<=', $lotSize)
            ->where(fn($q) => $q->where('max_qty', '>=', $lotSize)->orWhereNull('max_qty'))
            ->first()
            ?? QaSamplingPlan::query()->orderBy('min_qty', 'asc')->first();

        if (!$plan) {
            // Fallback: inspecciona todo el lote (ajústalo a tu política)
            return response()->json([
                'ok'            => true,
                'lot_size'      => $lotSize,
                'sampling_type' => $type,
                'sample_qty'    => $lotSize,
                'sample_size'   => $lotSize,
                'surface_qty'   => 0,
                'plan_id'       => null,
                'fallback'      => 'no-plan-row',
            ], 200);
        }

        $calc = function ($value, $isPercent) use ($lotSize) {
            if ($value === null) return 1;
            $n = $isPercent ? (int) ceil($lotSize * ((float)$value / 100)) : (int) $value;
            return max(1, min($n, $lotSize)); // clamp 1..lot
        };

        $baseQtyField = Schema::hasColumn($plan->getTable(), $qtyField) ? $qtyField : 'normal_qty';

        $sampleQty  = $calc($plan->{$baseQtyField}, (bool) $plan->is_percent);
        $surfaceQty = $calc($plan->surface_qty,     (bool) $plan->is_percent);

        return response()->json([
            'ok'            => true,
            'lot_size'      => $lotSize,
            'sampling_type' => $type,
            'sample_qty'    => $sampleQty,
            'sample_size'   => $sampleQty,   // alias
            'surface_qty'   => $surfaceQty,
            'plan_id'       => $plan->id,
            'fallback'      => null,
        ], 200);
    }


    public function byOrderStation($orderScheduleId)
    {
        $order = OrderSchedule::select('id', 'location')->findOrFail($orderScheduleId);
        $loc = strtolower($order->location ?? '');

        $rows = DB::table('gen_stations as s')
            ->join('gen_location as l', 'l.id', '=', 's.location_id')
            ->select('s.id', 's.station', 'l.location as location')
            ->whereRaw('LOWER(l.location) = ?', [$loc])
            ->orderBy('s.station')
            ->get();


        return response()->json($rows);
    }

    public function byOrderOperator($orderScheduleId)
    {
        $order = OrderSchedule::select('id', 'location')->findOrFail($orderScheduleId);
        $loc = strtolower($order->location ?? '');

        // usando query builder por si no tienes modelo:
        $rows = DB::table('gen_operators as o')
            ->join('gen_location as l', 'l.id', '=', 'o.location_id')
            ->select('o.id', 'o.operator', 'o.ope_position', 'l.location as location')
            ->whereRaw('LOWER(l.location) = ?', [strtolower($loc)])
            ->orderBy('o.operator')
            ->get();

        return response()->json($rows);
    }

    /**Inspection COMPLETED */
    public function updateStatusInspection(Request $request, $id)
    {
        $request->validate([
            'status_inspection' => 'required|in:pending,in_progress,completed',
            'inspection_note'   => 'nullable|string|max:500',
        ]);

        $order = OrderSchedule::findOrFail($id);

        $prev = strtolower((string) $order->status_inspection);
        $new  = strtolower($request->status_inspection);

        // Early return (opcional): si no cambia nada y no hay nota nueva
        if ($prev === $new && !$request->filled('inspection_note')) {
            return response()->json([
                'success'           => true,
                'status_inspection' => $order->status_inspection,
                'inspection_endate' => $order->inspection_endate,
                'completed_by'      => $order->completed_by,
            ]);
        }

        if ($request->filled('inspection_note')) {
            $order->inspection_note = $request->inspection_note;
        }

        $order->status_inspection = $new;

        // Transición a COMPLETED → sellar una vez
        if ($new === 'completed' && $prev !== 'completed') {
            $order->inspection_endate = $order->inspection_endate ?? now();
            $order->completed_by      = $order->completed_by ?? Auth::id();
            // Si se marca como Completed, el progreso debe ser 100% para reflejar el estado real en Schedule.
            $order->inspection_progress = 100;
        }

        // Reversión desde COMPLETED → limpiar (tú lo quieres así)
        if (in_array($new, ['pending', 'in_progress'], true) && $prev === 'completed') {
            $order->inspection_endate = null;
            $order->completed_by      = null;
        }

        $order->save();

        return response()->json([
            'success'           => true,
            'status_inspection' => $order->status_inspection,
            'inspection_endate' => $order->inspection_endate,
            'completed_by'      => $order->completed_by,
        ]);
    }

    public function destroy($id)
    {
        $row = QaFaiSummary::find($id);
        if (!$row) {
            return response()->json(['error' => 'Fila no encontrada'], 404);
        }
        $row->delete();
        return response()->json(['success' => true]);
    }


    /**Validar si la operacion ya esta validado en la base de datos */
    public function validateOps(Request $request, $orderId)
    {
        $ops = $request->input('ops');
        $order = OrderSchedule::findOrFail($orderId);

        return response()->json([
            'saved' => !empty($order->operation), // true si ya está guardado en BD
        ]);
    }


    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 2. TAB "FAI/IPI SUMMARY" ------------Summary---------------
     * ===================================================================================================================
     */

    public function summary(Request $request)
    {
        $tbl   = (new \App\Models\QaFaiSummary)->getTable(); // 'qa_faisummary'
        $year  = $request->integer('year');
        $month = $request->integer('month');
        $day   = $request->input('day');

        // === Query base ===
        $q = \App\Models\QaFaiSummary::query()
            ->with(['orderSchedule:id,work_id,location,PN']);

        // 📅 Filtro de fechas (prioridad: día > año+mes > año > mes > mes actual)
        if ($day) {
            $q->whereDate('date', \Carbon\Carbon::parse($day)->toDateString());
        } elseif ($year && $month) {
            $q->whereYear('date', $year)->whereMonth('date', $month);
        } elseif ($year) {
            $q->whereYear('date', $year);
        } elseif ($month) {
            $q->whereYear('date', now()->year)->whereMonth('date', $month);
        } else {
            // Por defecto: mes actual
            $q->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
            $year  = now()->year;
            $month = now()->month;
        }

        // === Ordenar últimos registrados primero
        $inspections = $q->orderByDesc("$tbl.date")
            ->orderByDesc("$tbl.id")
            ->get();

        // === Stats para el dashboard ===
        $statsQuery = \App\Models\QaFaiSummary::query();

        if ($day) {
            $statsQuery->whereDate('date', \Carbon\Carbon::parse($day)->toDateString());
        } elseif ($year && $month) {
            $statsQuery->whereYear('date', $year)->whereMonth('date', $month);
        } elseif ($year) {
            $statsQuery->whereYear('date', $year);
        } elseif ($month) {
            $statsQuery->whereYear('date', now()->year)->whereMonth('date', $month);
        } else {
            $statsQuery->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
            $year  = now()->year;
            $month = now()->month;
        }

        $monthTotal = (clone $statsQuery)->count();
        $monthPass  = (clone $statsQuery)->whereRaw('LOWER(TRIM(results)) = ?', ['pass'])->count();
        $monthFail  = (clone $statsQuery)->whereRaw('LOWER(TRIM(results)) IN ("fail","no pass","nopass","no_pass")')->count();
        $passRate   = $monthTotal ? number_format(($monthPass * 100) / $monthTotal, 2, '.', '') : '0.00';

        $monthStats = [
            'year'  => $year,
            'month' => $month,
            'total' => $monthTotal,
            'pass'  => $monthPass,
            'fail'  => $monthFail,
            'rate'  => $passRate,
        ];

        return view('qa.faisummary.faisummary_summary', compact(
            'inspections',
            'year',
            'month',
            'day',
            'monthStats'
        ));
    }

    public function general(Request $request)
    {
        $data = $this->getFaiSummaryData($request);

        /** @var \Illuminate\Support\Collection $inspections */
        $inspections = $data['inspections'];

        // ===============================
        // SOLO para failedOrders:
        // excluir órdenes cuya inspección esté COMPLETED
        // ===============================
        $failedOrders = $inspections

            // A) Solo inspecciones FAI y con orden asociada
            ->filter(function ($i) {
                return $i->orderSchedule
                    && strcasecmp(trim((string)$i->insp_type), 'FAI') === 0;
            })

            // B) EXCLUIR órdenes COMPLETED (solo para chips)
            ->filter(function ($i) {
                return strtolower($i->orderSchedule->status_inspection ?? '') !== 'completed';
            })

            // C) Agrupar por orden
            ->groupBy(function ($i) {
                return $i->order_schedule_id;
            })

            // D) Último FAI
            ->map(function ($group) {
                return $group->sortByDesc('date')->first();
            })

            // E) Último FAI sea FAIL/NO PASS
            ->filter(function ($latest) {
                $result = strtolower(trim((string) $latest->results));

                return in_array($result, [
                    'fail',
                    'no pass',
                    'nopass',
                    'no_pass'
                ], true);
            })

            ->values();

        $data['failedOrders'] = $failedOrders;

        return view('qa.faisummary.faisummary_summary', $data);
    }



    /**
     * 🔁 Reutiliza esta función para vista, Excel y PDF
     */
    protected function getFaiSummaryData(Request $request): array
    {
        $query = QaFaiSummary::with('orderSchedule');

        // === filtros EXACTOS como los del formulario ===
        if ($request->filled('operator')) {
            $query->where('operator', $request->operator);
        }

        if ($request->filled('inspector')) {
            $query->where('inspector', $request->inspector);
        }

        if ($request->filled('location')) {
            $query->where('loc_inspection', $request->location);
        }

        // Filtros de fecha (year/month/day) según tu lógica actual
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        if ($request->filled('day')) {
            $query->whereDate('date', $request->day);
        }

        $inspections = $query->orderByDesc('date')->get();

        // === monthStats (usa tu lógica actual; aquí solo un ejemplo) ===
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        $monthQuery = QaFaiSummary::whereYear('date', $year)
            ->whereMonth('date', $month);

        $total = (clone $monthQuery)->count();
        $pass  = (clone $monthQuery)->where('results', 'pass')->count();
        $fail  = (clone $monthQuery)->where('results', 'no pass')->count();

        $rate = $total > 0 ? number_format(($pass * 100) / $total, 2, '.', '') : '0.00';

        $monthStats = [
            'total' => $total,
            'pass'  => $pass,
            'fail'  => $fail,
            'rate'  => $rate,
            'month' => $month,
            'year'  => $year,
        ];

        return compact('inspections', 'monthStats');
    }

    // ================== EXCEL ==================
    public function exportFai14(Request $request)
    {
        $data = $this->getFaiSummaryData($request); // lo que ya tienes

        $export = new BladeTableExport(
            view: 'qa.faisummary.faisummary_summaryexcel',
            data: $data,
            title: 'FAI / IPI Summary Report',
            columnFormats: [
                'A' => NumberFormat::FORMAT_DATE_DATETIME,
                'L' => NumberFormat::FORMAT_NUMBER, // Qty Insp.
            ],
            centerCols: ['B', 'C', 'D', 'E', 'F', 'G', 'J', 'K', 'M', 'N'],
            rightCols: ['L'],
            wrapCols: ['H', 'I'], // SB/IS y Observation
        );

        return Excel::download($export, 'FAI_Summary_' . now()->format('Ymd_His') . '.xlsx');
    }

    // ================== PDF ==================
    public function exportPdf(Request $request)
    {
        $data = $this->getFaiSummaryData($request);

        /** @var \Illuminate\Support\Collection $inspections */
        $inspections = $data['inspections'];

        // 📊 RESUMEN basado SOLO en los registros del PDF
        $total = $inspections->count();

        $pass = $inspections->filter(function ($row) {
            return strcasecmp(trim((string)$row->results), 'pass') === 0;
        })->count();

        $fail = $inspections->filter(function ($row) {
            return strcasecmp(trim((string)$row->results), 'no pass') === 0;
        })->count();

        $rate = $total > 0 ? number_format(($pass * 100) / $total, 2, '.', '') : '0.00';

        $pdfStats = [
            'total' => $total,
            'pass'  => $pass,
            'fail'  => $fail,
            'rate'  => $rate,
        ];

        // 👇 estos vienen de los filtros del formulario
        $month = $request->input('month');   // puede ser null
        $year  = $request->input('year');    // puede ser null

        $generatedAt = now();
        $user        = auth()->user();

        $pdf = Pdf::loadView('qa.faisummary.faisummary_summarypdf', [
            'inspections' => $inspections,
            'pdfStats'    => $pdfStats,
            'generatedAt' => $generatedAt,
            'user'        => $user,

            // 👉 los mandamos a la vista para el Period:
            'month'       => $month,
            'year'        => $year,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('FAI_Summary_' . $generatedAt->format('Ymd_His') . '.pdf');
    }


    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 3. TAB "FAI/IPI SUMMARY" ------------Completed FAI Summary----------------
     * ===================================================================================================================
     */


    public function faicompleted(Request $request)
    {
        $year  = $request->integer('year');   // null/0 si no viene
        $month = $request->integer('month');  // null/0 si no viene
        $day   = $request->input('day');      // string o null

        $select = [
            'id',
            'parent_id',
            'work_id',
            'PN',
            'Part_description',
            'operation',
            'wo_qty',
            'group_wo_qty',
            'location',
            'status_inspection',
            'total_fai',
            'total_ipi',
            'sampling',
            'sampling_check',
            'inspection_endate',
        ];

        $orderscompleted = \App\Models\OrderSchedule::query()
            ->select($select)
            ->whereNull('parent_id')
            ->where('status_inspection', 'completed')

            // 📅 Filtros por fecha (SOLO si llegan)
            ->when($day, function ($q) use ($day) {
                $q->whereDate('inspection_endate', \Carbon\Carbon::parse($day)->toDateString());
            })
            ->when($year && $month, function ($q) use ($year, $month) {
                $q->whereYear('inspection_endate', $year)
                    ->whereMonth('inspection_endate', $month);
            })
            ->when($year && !$month, function ($q) use ($year) {
                $q->whereYear('inspection_endate', $year);
            })
            ->when(!$year && $month, function ($q) use ($month) {
                $q->whereYear('inspection_endate', now()->year)
                    ->whereMonth('inspection_endate', $month);
            })

            // ✅ Sumas de piezas aprobadas (qty_pcs) desde qa_faisummary
            ->withSum([
                'faiSummaries as fai_pass_qty' => function ($q) {
                    $q->where('insp_type', 'FAI')
                        ->where('results', 'pass'); // ajusta a 'Pass' si en tu BD está con mayúscula
                },
            ], 'qty_pcs')
            ->withSum([
                'faiSummaries as ipi_pass_qty' => function ($q) {
                    $q->where('insp_type', 'IPI')
                        ->where('results', 'pass');
                },
            ], 'qty_pcs')

            ->orderByDesc('inspection_endate')
            ->get();

        return view('qa.faisummary.faisummary_completed', compact(
            'orderscompleted',
            'year',
            'month',
            'day'
        ));
    }

    public function updateStatus(Request $request, OrderSchedule $order)
    {
        $request->validate([
            'status_inspection' => 'required|string'
        ]);

        $order->status_inspection = $request->status_inspection;
        $order->save();

        return response()->json(['success' => true]);
    }



    public function pdf(OrderSchedule $order)
    {
        // 0) Normaliza: si llaman con un hijo, usa el padre del grupo
        $parentId = $order->parent_id ?: $order->id;

        $generatedAt = now('America/Los_Angeles');
        $user = auth()->user(); // 👈 usuario logueado

        // === Total qty padre + hijos ===
        $totalQty = \App\Models\OrderSchedule::query()
            ->where(function ($q) use ($parentId, $order) {
                $q->where('id', $parentId)      // el padre
                    ->orWhere('parent_id', $parentId); // todos los hijos
            })
            ->sum('qty');

        // 1) Header base del PDF
        $header = [
            'id'                => $order->id,
            'work_id'           => $order->work_id,
            'pn'                => $order->PN,
            'description'       => $order->Part_description,
            'location'          => ucfirst($order->location ?? ''),
            'co'                => $order->co ?? '',
            'cust_po'           => $order->cust_po ?? '',
            'costumer'          => $order->costumer ?? '',
            'qty'               => $totalQty,   // 👈 suma padre + hijos
            'group_wo_qty'      => $order->group_wo_qty ?? 0,
            'due_date'          => $order->due_date,
            'operation'         => (int)($order->operation === 'default_value' ? 0 : ($order->operation ?? 0)),
            'sampling'          => (int)($order->sampling ?? 0),
            'sampling_check'    => $order->sampling_check ?? '',
            'total_fai'         => (int)($order->total_fai ?? 0),
            'total_ipi'         => (int)($order->total_ipi ?? 0),
            'status_inspection' => $order->status_inspection,
            'inspection_note' => $order->inspection_note,
        ];

        // 2) Líneas "DELIVER ... PIECES BY ... PO#..." sólo de HIJOS (agrupado por due_date+cust_po)
        $deliveries = \App\Models\OrderSchedule::query()
            ->where('parent_id', $parentId)
            ->selectRaw("
            due_date,
            COALESCE(cust_po,'') AS cust_po,
            SUM(COALESCE(wo_qty, qty, 0)) AS pieces
        ")
            ->groupBy('due_date', 'cust_po')
            ->orderBy('due_date')
            ->get()
            ->map(function ($r) {
                return [
                    'date'    => $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('m/d/Y') : '—',
                    'cust_po' => $r->cust_po ?: '—',
                    'pieces'  => (int) $r->pieces,
                ];
            })
            ->values()
            ->all();

        // (OPCIONAL) agregar también una línea del PADRE
        if ($header['due_date'] || $header['cust_po']) {
            $deliveries[] = [
                'date'    => $header['due_date'] ? \Carbon\Carbon::parse($header['due_date'])->format('m/d/Y') : '—',
                'cust_po' => $header['cust_po'] ?: '—',
                'pieces'  => (int) ($order->qty ?? $order->qty ?? 0),
            ];
        }

        // 3) Fechas de due_date de los hijos (lista y rango)
        $childrenDueDates = \App\Models\OrderSchedule::query()
            ->where('parent_id', $parentId)
            ->where('status', '<>', 'sent')           // opcional
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->distinct()
            ->pluck('due_date')
            ->all();

        $childrenDueDatesFmt = array_map(
            fn($d) => \Carbon\Carbon::parse($d)->format('m/d/Y'),
            $childrenDueDates
        );

        $header['children_due_dates_fmt'] = $childrenDueDatesFmt;
        $header['min_child_due'] = $childrenDueDates ? \Carbon\Carbon::parse($childrenDueDates[0])->format('m/d/Y') : null;
        $header['max_child_due'] = $childrenDueDates ? \Carbon\Carbon::parse(end($childrenDueDates))->format('m/d/Y') : null;
        $header['deliveries'] = $deliveries;

        // 4) Filas de inspección
        $rows = QaFaiSummary::where('order_schedule_id', $order->id)
            ->orderBy('date')
            ->orderBy('insp_type')
            ->orderBy('operation')
            ->get([
                'date',
                'insp_type',
                'operation',
                'operator',
                'results',
                'sb_is',
                'observation',
                'qty_pcs',
                'station',
                'method',
                'inspector',
            ]);

        $generatedAt = now('America/Los_Angeles');

        // 5) Resumen
        $summary = app(InspectionSummary::class)->summarize(
            $order,
            $header['operation'],
            $header['sampling']
        );

        // 6) Render PDF
        $pdf = PDF::loadView('qa.faisummary.faisummary_pdf', [
            'order'       => $order,
            'header'      => $header,
            'rows'        => $rows,
            'summary'     => $summary,
            'generatedAt' => $generatedAt,
            'user'        => $user,   // 👈 pasar a la vista
        ])->setPaper('letter', 'landscape');

        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        $canvas      = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
        $size = 10;

        $w = $canvas->get_width();
        $canvas->page_text($w - 70, 5, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, $size, [0, 0, 0]);

        $filename = 'FAI_' . str_replace(['/', '\\'], '-', (string)$order->work_id) . '.pdf';
        // Si viene ?download=1 => descarga, si no => stream en navegador
        if (request()->boolean('download')) {
            return $dompdf->stream($filename, ['Attachment' => true]);
        }
        return $dompdf->stream($filename, ['Attachment' => false]);
    }

    public function completedView(OrderSchedule $order)
    {
        $summary = app(InspectionSummary::class)->summarize($order, $order->operation, $order->sampling);

        return view('qa.faisummary.completed', [
            'order' => $order,
            'summary' => $summary,
        ]);
    }

    protected function baseCompletedQuery(Request $request)
    {
        $q = OrderSchedule::query()
            ->select([
                'id',
                'parent_id',
                'work_id',
                'PN',
                'Part_description',
                'operation',
                'wo_qty',
                'group_wo_qty',
                'location',
                'status_inspection',
                'total_fai',
                'total_ipi',
                'sampling',
                'sampling_check',
                'inspection_endate'
            ])
            ->whereNull('parent_id')
            ->where('status_inspection', 'completed')
            ->orderByDesc('inspection_endate') // o el orden que prefieras
            ->withSum([
                'faiSummaries as fai_pass_qty' => function ($q) {
                    $q->where('insp_type', 'FAI')
                        ->where('results', 'pass'); // ajusta a 'Pass' si en tu BD está con mayúscula
                },
            ], 'qty_pcs')
            ->withSum([
                'faiSummaries as ipi_pass_qty' => function ($q) {
                    $q->where('insp_type', 'IPI')
                        ->where('results', 'pass');
                },
            ], 'qty_pcs');

        // filtro de búsqueda global (igual que el de DataTables.search())
        if ($search = trim($request->get('q', ''))) {
            $q->where(function ($w) use ($search) {
                $w->where('work_id', 'like', "%{$search}%")
                    ->orWhere('PN', 'like', "%{$search}%")
                    ->orWhere('Part_description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return $q;
    }
    public function exportCompletedExcel(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'strlen');

        if (!empty($ids)) {
            $rows = OrderSchedule::query()
                ->select([
                    'id',
                    'parent_id',
                    'work_id',
                    'PN',
                    'Part_description',
                    'operation',
                    'wo_qty',
                    'group_wo_qty',
                    'location',
                    'status_inspection',
                    'total_fai',
                    'total_ipi',
                    'sampling',
                    'sampling_check',
                    'inspection_endate'
                ])
                ->whereIn('id', $ids)
                ->withSum([
                    'faiSummaries as fai_pass_qty' => function ($q) {
                        $q->where('insp_type', 'FAI')
                            ->whereRaw('LOWER(results) = "pass"');
                    },
                ], 'qty_pcs')
                ->withSum([
                    'faiSummaries as ipi_pass_qty' => function ($q) {
                        $q->where('insp_type', 'IPI')
                            ->whereRaw('LOWER(results) = "pass"');
                    },
                ], 'qty_pcs')
                ->orderByDesc('inspection_endate')
                ->get();
        } else {
            $rows = $this->baseCompletedQuery($request)->get();
        }

        // ============================
        // EXPORT REUTILIZABLE (12 columnas)
        // ============================

        return Excel::download(
            new \App\Exports\BladeTableExport(
                view: 'qa.faisummary.excel_completed_table',
                data: ['rows' => $rows],

                title: 'FAI / IPI Completed Report',

                // ==== FORMATOS DE CADA COLUMNA (A..L = 12 columnas) ====
                columnFormats: [
                    'A' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME, // fecha
                    'G' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,        // wo_qty
                    'H' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,        // group_wo_qty
                    'K' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,        // total_fai
                    'L' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE,        // total_ipi
                ],

                // ==== COLUMNAS PARA CENTRAR ====
                centerCols: ['B', 'C', 'D', 'F', 'G', 'I', 'J'],

                // ==== COLUMNAS NUMÉRICAS A LA DERECHA ====
                rightCols: ['H', 'K', 'L'],

                // ==== ENVOLVER TEXTO (si aplica) ====
                wrapCols: ['F'], // Part_description (si es larga)
            ),

            'FAI_Completed_' . now()->format('Ymd_His') . '.xlsx'
        );
    }


    public function exportCompletedPdf(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'strlen');

        if (!empty($ids)) {
            $rows = OrderSchedule::query()
                ->select([
                    'id',
                    'parent_id',
                    'work_id',
                    'PN',
                    'Part_description',
                    'operation',
                    'wo_qty',
                    'group_wo_qty',
                    'location',
                    'status_inspection',
                    'total_fai',
                    'total_ipi',
                    'sampling',
                    'sampling_check',
                    'inspection_endate'
                ])
                ->whereIn('id', $ids)
                ->withSum([
                    'faiSummaries as fai_pass_qty' => function ($q) {
                        $q->where('insp_type', 'FAI')->whereRaw('LOWER(results) = "pass"');
                    },
                ], 'qty_pcs')
                ->withSum([
                    'faiSummaries as ipi_pass_qty' => function ($q) {
                        $q->where('insp_type', 'IPI')->whereRaw('LOWER(results) = "pass"');
                    },
                ], 'qty_pcs')
                ->orderByDesc('inspection_endate')
                ->get();
        } else {
            $rows = $this->baseCompletedQuery($request)->get();
        }

        $logoPath = public_path('vendor/adminlte/dist/img/accl.png'); // coloca tu logo aquí
        $generatedAt = now()->format('F j, Y g:i A');
        $userName = auth()->user()->name ?? 'System';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('qa.faisummary.pdf_completed_table', [
            'rows'         => $rows,
            'logoPath'     => $logoPath,
            'generated_at' => $generatedAt,
            'user'         => $userName,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('FAI_Completed.pdf');
    }


    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 4. TAB "FAI/IPI SUMMARY" ------------Reject FAI Orders---------------
     * ===================================================================================================================
     */

    public function rejectedfaiorders(Request $request)
    {
        // Reutilizamos tu helper
        $data = $this->getFaiSummaryData($request);

        /** @var \Illuminate\Support\Collection $inspections */
        $inspections = $data['inspections'];

        $failedOrders = $inspections

            // A) Solo inspecciones FAI y con orden asociada
            ->filter(function ($i) {
                return $i->orderSchedule
                    && strcasecmp(trim((string)$i->insp_type), 'FAI') === 0;
            })

            // B) Agrupar por orden
            ->groupBy(function ($i) {
                return $i->order_schedule_id;
            })

            // C) Para cada orden:
            //    - Ver si tiene AL MENOS UN FAI no pass
            //    - Si sí, regresamos un registro para representar la fila (ej. el último FAI)
            ->map(function ($group) {
                $hasNonPass = $group->contains(function ($insp) {
                    $result = strtolower(trim((string) $insp->results));
                    return in_array($result, [
                        'fail',
                        'no pass',
                        'nopass',
                        'no_pass'
                    ], true);
                });

                if (! $hasNonPass) {
                    return null; // Esta orden nunca tuvo FAI no pass → no se incluye
                }

                // Puedes elegir qué inspección mostrar:
                // 1) El ÚLTIMO FAI (por fecha)
                return $group->sortByDesc('date')->first();

                // o 2) El ÚLTIMO FAI NO PASS (descomenta esto si prefieres eso)
                /*
            return $group->filter(function ($insp) {
                    $result = strtolower(trim((string) $insp->results));
                    return in_array($result, ['fail', 'no pass', 'nopass', 'no_pass'], true);
                })
                ->sortByDesc('date')
                ->first();
            */
            })

            // Quitar los null (órdenes sin FAI no pass)
            ->filter()

            ->values();

        // Puedes mandar solo esto, o también otros datos de $data si quieres
        return view('qa.faisummary.faisummary_rejectedfaiorders', [
            'failedOrders' => $failedOrders,
        ]);
    }

    public function orderInspections($orderId)
    {
        // Todas las inspecciones de esa orden (FAI + IPI, pass + no pass)
        $inspections = QaFaiSummary::with('orderSchedule')
            ->where('order_schedule_id', $orderId)
            ->orderBy('date', 'desc')
            ->get();

        // Devolvemos solo el HTML de las filas para el modal
        $html = view('qa.faisummary.faisummary_rejectedfairows', compact('inspections'))->render();

        return response()->json([
            'html' => $html,
        ]);
    }


    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 5. TAB "FAI/IPI SUMMARY" ------------FAI Summary Statistics---------------
     * ===================================================================================================================
     */


    // Mostrar listado de registros
    public function faistatistics()
    {

        return view('qa.faisummary.faisummary_statistics');
    }


    public function faistatisticsData(Request $request)
    {
        $year = (int) ($request->integer('year') ?: now()->format('Y'));

        $rows = DB::table('qa_faisummary')
            ->whereYear('created_at', $year) // 👈 aquí
            ->selectRaw('
            QUARTER(`created_at`) as q, -- 👈 y aquí
            COUNT(*) as total,
            SUM(CASE WHEN LOWER(TRIM(results)) IN ("pass","ok") THEN 1 ELSE 0 END) as pass_cnt,
            SUM(CASE WHEN LOWER(TRIM(results)) IN ("no pass","fail","np") THEN 1 ELSE 0 END) as fail_cnt
        ')
            ->groupBy('q')
            ->orderBy('q')
            ->get();

        $quarters = collect([1, 2, 3, 4])->map(function ($q) use ($rows) {
            $r = $rows->firstWhere('q', $q);
            $total = (int) ($r->total ?? 0);
            $pass  = (int) ($r->pass_cnt ?? 0);
            $fail  = (int) ($r->fail_cnt ?? 0);
            return [
                'quarter'  => "Q{$q}",
                'total'    => $total,
                'pass'     => $pass,
                'fail'     => $fail,
                'pass_pct' => $total ? round($pass * 100 / $total, 2) : 0,
                'fail_pct' => $total ? round($fail * 100 / $total, 2) : 0,
            ];
        });

        // Totales por tipo de inspección (FAI / IPI)
        $typesRaw = DB::table('qa_faisummary')
            ->whereYear('created_at', $year)
            ->selectRaw("
                UPPER(TRIM(COALESCE(insp_type, 'N/A'))) as insp_type,
                COUNT(*) as total,
                SUM(CASE WHEN LOWER(TRIM(results)) IN ('pass','ok') THEN 1 ELSE 0 END) as pass_cnt,
                SUM(CASE WHEN LOWER(TRIM(results)) IN ('no pass','fail','np','no') THEN 1 ELSE 0 END) as fail_cnt
            ")
            ->groupBy('insp_type')
            ->get();

        $types = $typesRaw->map(function ($r) {
            $total = (int) ($r->total ?? 0);
            $pass  = (int) ($r->pass_cnt ?? 0);
            $fail  = (int) ($r->fail_cnt ?? 0);
            return [
                'type'     => $r->insp_type ?: 'N/A',
                'total'    => $total,
                'pass'     => $pass,
                'fail'     => $fail,
                'pass_pct' => $total ? round($pass * 100 / $total, 2) : 0,
                'fail_pct' => $total ? round($fail * 100 / $total, 2) : 0,
            ];
        });

        $global = [
            'year'  => $year,
            'total' => $quarters->sum('total'),
            'pass'  => $quarters->sum('pass'),
            'fail'  => $quarters->sum('fail'),
        ];
        $global['pass_pct'] = $global['total'] ? round($global['pass'] * 100 / $global['total'], 2) : 0;
        $global['fail_pct'] = $global['total'] ? round($global['fail'] * 100 / $global['total'], 2) : 0;

        return response()->json([
            'quarters' => $quarters,
            'global'   => $global,
            'types'    => $types,
        ]);
    }


    public function faistatisticsBy(Request $request)
    {
        $group = strtolower($request->get('group', 'operator')); // 'operator' | 'inspector'
        abort_unless(in_array($group, ['operator', 'inspector'], true), 400);

        // 1) Rango: prioriza start/end; si no, usa period/anchor
        $startRaw = $request->get('start');
        $endRaw   = $request->get('end');

        if ($startRaw && $endRaw) {
            try {
                $startC = \Carbon\Carbon::parse($startRaw);
                $endC   = \Carbon\Carbon::parse($endRaw);

                // Si no traen hora explícita, amplia a todo el día
                if (!preg_match('/\d{2}:\d{2}/', $startRaw)) $startC = $startC->startOfDay();
                if (!preg_match('/\d{2}:\d{2}/', $endRaw))   $endC   = $endC->endOfDay();

                // sanity: si end < start, intercambia
                if ($endC->lt($startC)) [$startC, $endC] = [$endC, $startC];

                $start = $startC->format('Y-m-d H:i:s');
                $end   = $endC->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Invalid start/end'], 422);
            }
        } else {
            $period = strtolower($request->get('period', 'year'));     // day|week|month|quarter|year
            $anchor = $request->get('anchor', now()->toDateString());  // yyyy-mm-dd
            // <<< asegúrate que periodBounds devuelva DATETIME (no toDateString)
            [$start, $end] = $this->periodBounds($period, $anchor);    // 'Y-m-d H:i:s'
        }

        $nameFilter = trim((string) $request->get('name', ''));

        // 2) Columna a agrupar
        $col = $group === 'inspector' ? 'inspector' : 'operator';
        $nameExpr = "COALESCE(NULLIF(TRIM($col),''), '(Sin $group)')";

        // 3) Query (usa la columna de fecha correcta: 'created_at' o 'date')
        $q = DB::table('qa_faisummary')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("$nameExpr AS name")
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(results)) IN ('pass','ok','p') THEN 1 ELSE 0 END) AS pass_cnt")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(results)) IN ('no pass','fail','np','f','no') THEN 1 ELSE 0 END) AS fail_cnt")
            ->groupBy(DB::raw($nameExpr));

        if ($nameFilter !== '') {
            $q->having('name', '=', $nameFilter);
        }

        $rows = $q->orderByDesc('total')->get();

        $data = $rows->map(function ($r) {
            $t = (int)($r->total ?? 0);
            $p = (int)($r->pass_cnt ?? 0);
            $f = (int)($r->fail_cnt ?? 0);
            return [
                'name'     => $r->name,
                'total'    => $t,
                'pass'     => $p,
                'fail'     => $f,
                'pass_pct' => $t ? round($p * 100 / $t, 2) : 0.0,
                'fail_pct' => $t ? round($f * 100 / $t, 2) : 0.0,
            ];
        })->values();

        return response()->json([
            'group' => $group,
            'start' => $start, // con hora
            'end'   => $end,   // con hora
            'rows'  => $data,
        ]);
    }


    /**
     * Calcula rango por periodo respecto a anchor (Y-m-d).
     */
    protected function periodBounds(string $period, ?string $anchorDate): array
    {
        $anchor = $anchorDate ? Carbon::parse($anchorDate) : now();

        switch (strtolower($period)) {
            case 'day':
                $start = $anchor->copy()->startOfDay();
                $end   = $anchor->copy()->endOfDay();
                break;
            case 'week':
                // Si quieres ISO (lunes-domingo) asegúrate que Carbon esté configurado con weekStartsAt = Monday
                $start = $anchor->copy()->startOfWeek();
                $end   = $anchor->copy()->endOfWeek();
                break;
            case 'month':
                $start = $anchor->copy()->startOfMonth();
                $end   = $anchor->copy()->endOfMonth();
                break;
            case 'quarter':
            case 'trimester':
                $start = $anchor->copy()->firstOfQuarter();
                $end   = $anchor->copy()->lastOfQuarter()->endOfDay();
                break;
            case 'year':
            default:
                $start = $anchor->copy()->startOfYear();
                $end   = $anchor->copy()->endOfYear();
                break;
        }
        return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
    }


    public function faistatisticsByQuarterOperator(Request $request)
    {
        $year     = (int) $request->get('year', now()->year);
        $operator = $request->get('operator'); // <- opcional (nombre tal como viene en la tabla)

        // Base query
        $base = DB::table('qa_faisummary')
            ->whereYear('created_at', $year);

        if (!empty($operator)) {
            $base->where('operator', $operator);
        }

        // 1) Datos por operador × quarter
        $q = (clone $base)
            ->selectRaw("
            COALESCE(NULLIF(operator, ''), '(Sin operador)') as operator,
            QUARTER(created_at) as quarter,
            COUNT(*) as total,
            SUM(results = 'pass')    as pass,
            SUM(results = 'no pass') as fail
        ")
            ->groupBy('operator', 'quarter')
            ->orderBy('operator')
            ->get();

        // Normaliza a matriz [operator][quarter]
        $rows = [];
        foreach ($q as $r) {
            $op = $r->operator;
            $rows[$op][$r->quarter] = [
                'total'    => (int) $r->total,
                'pass'     => (int) $r->pass,
                'fail'     => (int) $r->fail,
                'pass_pct' => $r->total ? ($r->pass * 100 / $r->total) : 0,
                'fail_pct' => $r->total ? ($r->fail * 100 / $r->total) : 0,
            ];
        }

        // 2) Lista de operadores disponibles (para poblar el <select>)
        //    La basamos en el mismo filtro de año, y si llegó 'operator', igual
        //    devolvemos la lista completa de ese año (para que el select siga mostrando todos).
        $operators = (clone $base)
            ->whereNotNull('operator')
            ->where('operator', '<>', '')
            ->distinct()
            ->orderBy('operator')
            ->pluck('operator')
            ->values(); // array simple

        return response()->json([
            'rows'      => $rows,
            'operators' => $operators,
            'year'      => $year,
            'operator'  => $operator,
        ]);
    }

    public function nextNcarNumber(Request $request)
    {
        $type = strtolower((string) $request->query('type', ''));

        $map = [
            'internal' => 'INTERNAL',
            'external' => 'EXTERNAL',
            'customer' => 'CUSTOMER',
            'qa'       => 'QA',
        ];

        if (!isset($map[$type])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid NCAR type.',
            ], 422);
        }

        $code = $map[$type];

        $row = DB::table('qa_ncartype as t')
            ->join('qa_ncar_counter as c', 'c.ncartype_id', '=', 't.id')
            ->select([
                't.id as ncartype_id',
                't.code',
                't.prefix',
                'c.next_number',
            ])
            ->where('t.code', $code)
            ->first();

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'NCAR type/counter not found.',
            ], 404);
        }

        return response()->json([
            'success'     => true,
            'type'        => $type,
            'code'        => $row->code,
            'ncartype_id' => (int) $row->ncartype_id,
            'prefix'      => (string) $row->prefix,
            'next_number' => (int) $row->next_number,
            'ncar_no'     => ((string) $row->prefix) . ((int) $row->next_number),
        ]);
    }

    public function storeNcar(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['nullable', 'integer'],
            'type' => ['required', 'string', 'in:internal,external'],
            'stage' => ['nullable', 'string', 'max:120'],
            'ncar_date' => ['nullable', 'date'],
            'nc_description' => ['nullable', 'string'],
        ]);

        $code = $data['type'] === 'internal' ? 'INTERNAL' : 'EXTERNAL';

        $typeRow = DB::table('qa_ncartype')
            ->select(['id', 'prefix', 'code'])
            ->where('code', $code)
            ->first();

        if (!$typeRow) {
            return response()->json([
                'success' => false,
                'message' => 'NCAR type not found in qa_ncartype.',
            ], 404);
        }

        $order = null;
        if (!empty($data['order_id'])) {
            $order = DB::table('orders_schedule')
                ->select(['id', 'work_id', 'co', 'cust_po', 'PN', 'Part_description', 'costumer', 'qty', 'location'])
                ->where('id', (int) $data['order_id'])
                ->first();
        }

        $insert = [
            'order_id' => $order ? (int) $order->id : null,
            'ncartype_id' => (int) $typeRow->id,
            'ncar_no' => '',
            'ncar_date' => $data['ncar_date'] ?? null,
            'status' => 'New',
            'ncar_customer' => $order ? (string) ($order->costumer ?? '') : null,
            'stage' => $data['stage'] ?? null,
            'nc_description' => $data['nc_description'] ?? null,
            'location' => $order ? (string) ($order->location ?? '') : null,
        ];

        // Guardar qty si viene numérica desde orders_schedule (si no, dejar null)
        if ($order && isset($order->qty)) {
            $q = is_numeric($order->qty) ? (int) $order->qty : null;
            $insert['qty'] = $q > 0 ? $q : null;
        }

        $id = DB::table('qa_ncar')->insertGetId($insert);

        $ncar = DB::table('qa_ncar as n')
            ->join('qa_ncartype as t', 't.id', '=', 'n.ncartype_id')
            ->select([
                'n.id',
                'n.ncar_no',
                'n.created_at',
                'n.ncar_date',
                'n.status',
                't.name as type_name',
            ])
            ->where('n.id', $id)
            ->first();

        return response()->json([
            'success' => true,
            'id' => (int) $ncar->id,
            'ncar_no' => (string) $ncar->ncar_no,
            'created_at' => (string) $ncar->created_at,
            'ncar_date' => $ncar->ncar_date,
            'status' => $ncar->status,
            'type' => $ncar->type_name,
        ]);
    }
}
