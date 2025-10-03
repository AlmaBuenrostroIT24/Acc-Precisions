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



class QaFaiSummaryController extends Controller
{
    /*===========================================================================================================================
                                            Todo el tab relacionado a parts revision
    ============================================================================================================================*/
    // Mostrar listado de registros

    public function partsrevision()
    {
        // Solo devuelve la vista; las tablas vendrán por AJAX
        return view('qa.faisummary.faisummary_partsrevision');
    }
    //	31009/1


    public function partsrevisionData(Request $request)
    {
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
            'group_wo_qty',   // 👈 usar total guardado en el padre
            'due_date',
            'location',
            'status_inspection',
            'sampling',
            'sampling_check',
        ];

        $rows = OrderSchedule::query()
            ->select($select)
            ->where('status', '<>', 'sent')
            ->whereNull('parent_id')                 // 👈 solo padres
            ->whereRaw('LOWER(location) IN (?, ?)', ['yarnell', 'hearst'])
            ->when($user && $user->hasRole('QAdmin'), fn($q) => $q->whereRaw('LOWER(location) = ?', ['yarnell']))
            ->when($user && $user->hasRole('QA'),     fn($q) => $q->whereRaw('LOWER(location) = ?', ['hearst']))
            ->when(
                $bucket === 'empty',
                fn($q) => $q->where(fn($w) => $w->whereNull('status_inspection')->orWhere('status_inspection', 'pending')),
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

        $data = $rows->map(function ($r) use ($bucket, $opName) {
            $pn   = trim((string) $r->PN);
            $desc = trim(\Illuminate\Support\Str::before((string) $r->Part_description, ','));
            $part = $pn . ' - ' . $desc;
            $dueFormatted = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('M/d/Y') : null;

            $sum = (int) ($r->group_wo_qty ?? 0); // 👈 total del grupo desde DB

            $btn = '<button class="btn btn-sm btn-primary"
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
            if ($bucket === 'empty') {
                $btnOther = ' <button class="btn btn-sm btn-warning ml-1"
                data-toggle="modal" data-target="#otherModal"
                data-id="' . e($r->id) . '"
                data-pn="' . e($r->PN) . '"
                data-description="' . e($r->Part_description) . '"
                data-woqty="' . e($sum) . '"
                data-location="' . e($r->location) . '">
                <i class="fas fa-clipboard-list"></i>
            </button>';
            }

            $row = [
                'id'             => (int) $r->id,
                'part'           => $part,
                'work_id'        => trim((string) $r->work_id),
                'actions'        => '<div class="btn-group btn-group-sm">' . $btn . $btnOther . '</div>',
                'ops'            => (int) ($r->operation ?? 0),
                'wo_qty'         => $sum,                  // 👈 total del grupo
                'sampling'       => (int) ($r->sampling ?? 0),
                'sampling_check' => (string) ($r->sampling_check ?? 'Normal'),
                'due_date'       => $dueFormatted,
            ];

            if ($bucket === 'process') {
                $ops = (int) ($r->operation ?? 0);
                $sampling = (int) ($r->sampling ?? 0);
                $perOpReq = 1 + $sampling;
                $totalReq = $ops * $perOpReq;
                $done = 0;

                if ($ops > 0) {
                    $qtyExpr = \Illuminate\Support\Facades\Schema::hasColumn('qa_faisummary', 'qty_pcs')
                        ? 'qty_pcs'
                        : (\Illuminate\Support\Facades\Schema::hasColumn('qa_faisummary', 'sample_idx') ? 'sample_idx' : '1');

                    $pass = \DB::table('qa_faisummary')
                        ->select('operation', 'insp_type', \DB::raw("SUM(COALESCE($qtyExpr,1)) as qty"))
                        ->where('order_schedule_id', $r->id)
                        ->whereRaw('LOWER(results) = ?', ['pass'])
                        ->groupBy('operation', 'insp_type')
                        ->get();

                    $fai = [];
                    $ipi = [];
                    foreach ($pass as $p) {
                        $name = (string)$p->operation;
                        $q = (int)$p->qty;
                        $type = strtoupper((string)$p->insp_type);
                        if ($type === 'FAI') $fai[$name] = ($fai[$name] ?? 0) + $q;
                        if ($type === 'IPI') $ipi[$name] = ($ipi[$name] ?? 0) + $q;
                    }
                    for ($i = 1; $i <= $ops; $i++) {
                        $name = $opName($i);
                        $done += min($fai[$name] ?? 0, 1) + min($ipi[$name] ?? 0, $sampling);
                    }
                }

                $row['progress'] = '<div class="progress" data-order-id="' . e($r->id) . '" style="height:18px;">
                  <div class="progress-bar bg-secondary" role="progressbar"
                       style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>';
                $row['progress_pct'] = $totalReq > 0 ? (int) round(($done / $totalReq) * 100) : 0;
            }

            return $row;
        });

        return response()->json(['data' => $data]);
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
            $order->total_ipi = $op * $smp;   // operación * muestreo
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
        Log::info('storeSingle called', $request->all());
        $validated = $request->validate([
            'order_schedule_id' => 'required|exists:orders_schedule,id',
            'date' => 'required|date',
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
        ]);
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
            ->select('o.id', 'o.operator', 'l.location as location')
            ->whereRaw('LOWER(l.location) = ?', [strtolower($loc)])
            ->orderBy('o.operator')
            ->get();

        return response()->json($rows);
    }

    /**Inspection COMPLETED */
    public function updateStatusInspection(Request $request, $id)
    {
        $request->validate([
            'status_inspection' => 'required|in:pending,in_progress,completed'
        ]);

        $order = OrderSchedule::findOrFail($id);
        $order->status_inspection = $request->status_inspection;

        // ✅ Si pasa a "completed", guarda la fecha/hora
        if ($request->status_inspection === 'completed') {
            $order->inspection_endate = now();
        }
        $order->save();

        return response()->json(['success' => true]);
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


    //===========================================================================================================================
    /*===========================================================================================================================
         Todo el tab relacionado a parts summary
    ============================================================================================================================*/

    // Mostrar listado de registros

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
            $q->whereDate('created_at', \Carbon\Carbon::parse($day)->toDateString());
        } elseif ($year && $month) {
            $q->whereYear('created_at', $year)->whereMonth('created_at', $month);
        } elseif ($year) {
            $q->whereYear('created_at', $year);
        } elseif ($month) {
            $q->whereYear('created_at', now()->year)->whereMonth('created_at', $month);
        } else {
            // Por defecto: mes actual
            $q->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            $year  = now()->year;
            $month = now()->month;
        }

        // === Ordenar últimos registrados primero
        $inspections = $q->orderByDesc("$tbl.created_at")
            ->orderByDesc("$tbl.id")
            ->get();

        // === Stats para el dashboard ===
        $statsQuery = \App\Models\QaFaiSummary::query();

        if ($day) {
            $statsQuery->whereDate('created_at', \Carbon\Carbon::parse($day)->toDateString());
        } elseif ($year && $month) {
            $statsQuery->whereYear('created_at', $year)->whereMonth('created_at', $month);
        } elseif ($year) {
            $statsQuery->whereYear('created_at', $year);
        } elseif ($month) {
            $statsQuery->whereYear('created_at', now()->year)->whereMonth('created_at', $month);
        } else {
            $statsQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            $year  = now()->year;
            $month = now()->month;
        }

        $monthTotal = (clone $statsQuery)->count();
        $monthPass  = (clone $statsQuery)->whereRaw('LOWER(TRIM(results)) = ?', ['pass'])->count();
        $monthFail  = (clone $statsQuery)->whereRaw('LOWER(TRIM(results)) IN ("fail","no pass","nopass","no_pass")')->count();
        $passRate   = $monthTotal ? round($monthPass * 100 / $monthTotal, 1) : 0;

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



    //===========================================================================================================================
    /*===========================================================================================================================
         Todo el tab relacionado a faicompleted Y GENERACION DE PDF
    ============================================================================================================================*/
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
                'pieces'  => (int) ($order->wo_qty ?? $order->qty ?? 0),
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



    //===========================================================================================================================

    /*===========================================================================================================================
         Todo el tab relacionado a faistatistics Y ESTADISTICAS DE LAS INSPECCIONES
    ============================================================================================================================*/


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
}
