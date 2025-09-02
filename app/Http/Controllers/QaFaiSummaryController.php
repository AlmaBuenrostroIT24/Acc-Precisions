<?php

namespace App\Http\Controllers;

use App\Models\QaFaiSummary;
use Illuminate\Http\Request;
use App\Models\OrderSchedule;
use App\Models\Stations;
use Illuminate\Support\Facades\Log;
use App\Models\QaSamplingPlan;
use Illuminate\Support\Facades\DB; // si no tienes modelo Status
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PDF;
use App\Services\InspectionSummary;

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

    public function partsrevisionData(Request $request)
    {
        $bucket = $request->query('bucket'); // 'empty' o 'process'
        if (!in_array($bucket, ['empty', 'process'])) {
            return response()->json(['data' => []]);
        }

        $user = auth()->user();

        $select = [
            'id',
            'work_id',
            'PN',
            'Part_description',
            'operation',
            'wo_qty',
            'location',
            'status_inspection'
        ];

        $base = \App\Models\OrderSchedule::query()
            ->select($select)
            ->where('status', '<>', 'sent')
            ->where(function ($q) {
                $q->where(function ($x) {
                    $x->where('was_work_id_null', 0)->whereNotNull('co');
                })->orWhere(function ($x) {
                    $x->where('was_work_id_null', 1)->whereNull('co');
                });
            })
            // Filtro por rol/ubicación (como ya lo tenías)
            ->when($user && $user->hasRole('QAdmin'), fn($q) => $q->whereRaw('LOWER(location) = ?', ['yarnell']))
            ->when($user && $user->hasRole('QA'),     fn($q) => $q->whereRaw('LOWER(location) = ?', ['hearst']));

        if ($bucket === 'empty') {
            $base->where(function ($q) {
                $q->whereNull('status_inspection')->orWhere('status_inspection', 'pending');
            });
        } else { // process
            $base->where('status_inspection', 'in_progress');
        }

        $rows = $base->orderByDesc('id')->get();

        // Mapea a lo que DataTables espera
        $data = $rows->map(function ($r) {
            $partDesc = Str::before((string)$r->Part_description, ',');
            $part = e($r->PN) . ' - ' . e($partDesc);
            $ops = is_numeric($r->operation) ? (int)$r->operation : 0;
            $progressHtml =
                '<div class="progress" data-order-id="' . e($r->id) . '" style="height:18px;">' .
                '<div class="progress-bar bg-secondary" role="progressbar" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>' .
                '</div>' .
                '<small class="text-muted d-block"><span class="badge bg-light text-dark me-1">FAI + IPI</span></small>';


            // Botón para abrir modal con data-* necesarios
            $btn = '<button class="btn btn-sm btn-primary"
                    data-toggle="modal" data-target="#editModal"
                    data-id="' . e($r->id) . '"
                    data-workid="' . e($r->work_id) . '"
                    data-woqty="' . e($r->wo_qty) . '"
                    data-operation="' . e($r->operation) . '"
                    data-pn="' . e($r->PN) . '"
                    data-description="' . e($r->Part_description) . '">
                     <i class="fas fa-edit"></i>
                </button>';

            return [
                'id'        => (int) $r->id,            // ← FALTABA
                'part'     => $part,
                'ops'       => $ops,                 // <- # operaciones numérico
                'work_id'  => $r->work_id,
                'wo_qty'   => $r->wo_qty,
                'progress'  => $progressHtml,        // <- la celda con la barra
                'operation' => $r->operation,
                'actions'  => $btn,
            ];
        });

        // DataTables (client‑side) usa "data" como key
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
            ->orderBy('date', 'desc')   // más recientes primero
            ->orderBy('id', 'desc')     // desempate estable
            ->get();
        return response()->json($rows);
    }

    public function get(Request $request)
    {
        $lotSize = (int) $request->query('lot_size');
        $type = $request->query('sampling_type', 'normal');

        $plan = QaSamplingPlan::where('min_qty', '<=', $lotSize)
            ->where(function ($q) use ($lotSize) {
                $q->where('max_qty', '>=', $lotSize)
                    ->orWhereNull('max_qty');
            })
            ->first();
        if (!$plan) {
            return response()->json(['error' => 'No se encontró plan de muestreo para este lote.'], 404);
        }
        $qtyField = $type === 'tightened' ? 'tightened_qty' : 'normal_qty';
        $sampleQty = $plan->is_percent
            ? ceil($lotSize * ($plan->$qtyField / 100))
            : (int) $plan->$qtyField;
        $surfaceQty = $plan->is_percent
            ? ceil($lotSize * ($plan->surface_qty / 100))
            : (int) $plan->surface_qty;
        return response()->json([
            'lot_size' => $lotSize,
            'sampling_type' => $type,
            'sample_qty' => $sampleQty,
            'surface_qty' => $surfaceQty,
            'plan_id' => $plan->id,
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

    public function byOrderStation($orderScheduleId)
    {
        $order = OrderSchedule::select('id', 'location')->findOrFail($orderScheduleId);
        $loc = strtolower($order->location ?? '');

        // usando query builder por si no tienes modelo:
        $rows = DB::table('gen_stations as s')
            ->join('gen_location as l', 'l.id', '=', 's.location_id')
            ->select('s.id', 's.station', 'l.location as location')
            ->whereRaw('LOWER(l.location) = ?', [strtolower($loc)])
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
        // === Lectura de filtros ===
        $year      = $request->integer('year');
        $month     = $request->integer('month');
        $day       = $request->input('day');            // YYYY-MM-DD
        $inspector = $request->input('inspector');      // select
        $operator  = $request->input('operator');       // select
        $location  = $request->input('location');       // select

        // === Query base ===
        $q = QaFaiSummary::query()
            ->with(['orderSchedule:id,work_id,location,PN']);

        // === Filtro de fechas ===
        if ($day) {
            $q->whereDate('date', Carbon::parse($day)->toDateString());
        } elseif ($year && $month) {
            $q->whereYear('date', $year)->whereMonth('date', $month);
        } elseif ($year) {
            $q->whereYear('date', $year);
        } elseif ($month) {
            $q->whereYear('date', now()->year)->whereMonth('date', $month);
        } else {
            $q->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
        }
$table = (new QaFaiSummary)->getTable(); // e.g. 'qa_faisummary' o el que corresponda
        // === Búsqueda general (libre) ===
      if ($request->filled('search')) {
    $s = (string) $request->string('search');

    $q->where(function ($w) use ($s, $table) {
        $like = "%{$s}%";
        $w->where($table.'.operation',  'like', $like)
          ->orWhere($table.'.operator',  'like', $like)
          ->orWhere($table.'.station',   'like', $like)
          ->orWhere($table.'.insp_type', 'like', $like)
          ->orWhere($table.'.inspector', 'like', $like)
          ->orWhere($table.'.results',   'like', $like);
    });
        }

        // === Filtros exactos por selects ===
        if ($inspector) {
            $q->where('inspector', $inspector);
        }

        if ($operator) {
            $q->where('operator', $operator);
        }

        if ($location) {
            // location vive en la relación orderSchedule
            $q->whereHas('orderSchedule', fn($os) => $os->where('location', $location));
        }

        // === Tabla principal (orden + paginación) ===
        $inspections = $q->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(100)
            ->withQueryString();

        // === Distincts dinámicos para poblar selects, basados en la MISMA query filtrada ===
        // Usamos clone + reorder() para limpiar cualquier ORDER BY previo (evita error 3065).

        $inspectors = (clone $q)
            ->reorder()
            ->select('inspector')
            ->whereNotNull('inspector')
            ->distinct()
            ->orderBy('inspector')
            ->pluck('inspector');

        $operators = (clone $q)
            ->reorder()
            ->select('operator')
            ->whereNotNull('operator')
            ->distinct()
            ->orderBy('operator')
            ->pluck('operator');

        // Locations desde la misma query filtrada, usando join para hacerlo 100% SQL
        $locations = (clone $q)
            ->reorder()
            ->join('orders_schedule as os', 'os.id', '=', 'qa_faisummary.order_schedule_id')
            ->whereNotNull('os.location')
            ->distinct()
            ->orderBy('os.location')
            ->pluck('os.location');

        // === Auxiliares para selects de año/mes ===
        $currentYear = now()->year;
        $years = range($currentYear, $currentYear - 5);
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        ];

        return view('qa.faisummary.faisummary_summary', compact(
            'inspections',
            'years',
            'months',
            'year',
            'month',
            'day',
            'inspectors',
            'operators',
            'locations'
        ));
    }



    //===========================================================================================================================
    /*===========================================================================================================================
         Todo el tab relacionado a faicompleted Y GENERACION DE PDF
    ============================================================================================================================*/
    public function faicompleted()
    {
        $select = [
            'id',
            'work_id',
            'PN',
            'Part_description',
            'operation',
            'wo_qty',
            'location',
            'status_inspection',
            'total_fai',
            'total_ipi',
            'sampling',
            'sampling_check',
            'inspection_endate'
        ];

        $orderscompleted = \App\Models\OrderSchedule::query()
            ->select($select)
            ->where(function ($q) {
                $q->where(function ($x) {
                    $x->where('was_work_id_null', 0)->whereNotNull('co');
                })->orWhere(function ($x) {
                    $x->where('was_work_id_null', 1)->whereNull('co');
                });
            })
            ->where('status_inspection', 'completed') // 👈 Único filtro importante
            ->orderByDesc('id')
            ->get();

        return view('qa.faisummary.faisummary_completed', compact('orderscompleted'));
    }





    public function pdf(OrderSchedule $order)
    {
        $header = [
            'work_id'           => $order->work_id,
            'pn'                => $order->PN,
            'description'       => $order->Part_description,
            'location'          => ucfirst($order->location ?? ''),
            'co'                => $order->co ?? '',
            'cust_po'           => $order->cust_po ?? '',
            'costumer'          => $order->costumer ?? '',
            'qty'               => $order->qty ?? 0,
            'wo_qty'            => $order->wo_qty ?? 0,
            'due_date'          => $order->due_date,
            'operation'         => (int)($order->operation === 'default_value' ? 0 : ($order->operation ?? 0)),
            'sampling'          => (int)($order->sampling ?? 0),
            'sampling_check'    => $order->sampling_check ?? '',
            'total_fai'         => (int)($order->total_fai ?? 0),
            'total_ipi'         => (int)($order->total_ipi ?? 0),
            'status_inspection' => $order->status_inspection,
        ];

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
                // si tienes cantidad por fila para el resumen, inclúyela p.ej.:
                // 'qty_pcs',
            ]);

        Log::debug('Datos de QA FAI Summary:', $rows->toArray());

        $generatedAt = now('America/Los_Angeles');

        // === Resumen FAI/IPI (servidor) ===
        // El service carga de BD por defecto (modelo QaFai). Si tu tabla es QaFaiSummary,
        // puedes 1) adaptar el service a ese modelo, o 2) crear un método summarizeFromRows().
        // Opción rápida: usa summarize($order, ops, sampling) y adapta el service al modelo real.
        $summary = app(InspectionSummary::class)->summarize(
            $order,
            $header['operation'],
            $header['sampling']
        );

        // 1) Crea el PDF (pasa $summary al Blade)
        $pdf = PDF::loadView('qa.faisummary.faisummary_pdf', [
            'order'       => $order,
            'header'      => $header,
            'rows'        => $rows,
            'summary'     => $summary,
            'generatedAt' => $generatedAt,
        ])->setPaper('letter', 'landscape');

        // 2) Render explícito
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        // 3) Canvas para folio arriba-derecha
        $canvas      = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();

        Log::debug('PDF canvas size', ['w' => $canvas->get_width(), 'h' => $canvas->get_height()]);

        $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
        $size = 10;

        $w = $canvas->get_width();
        $h = $canvas->get_height();

        // Folio arriba-derecha (pegado casi al borde respetando margen)
        $text = 'Page {PAGE_NUM} of {PAGE_COUNT}';
        $textWidth = $fontMetrics->get_text_width($text, $font, $size);

        // Márgenes superiores/laterales de tu @page: 30px top, 18px right/left => usa ~12pt de padding visual
        $padRight = 70;
        $padTop   = 5; // un poco debajo del margen superior para no pisar

        $x = $w - $padRight;
        $y = $padTop;

        $canvas->page_text($x, $y, $text, $font, $size, [0, 0, 0]);

        // 4) Stream sin re-render
        $filename = 'FAI_' . str_replace(['/', '\\'], '-', (string)$order->work_id) . '.pdf';
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
}
