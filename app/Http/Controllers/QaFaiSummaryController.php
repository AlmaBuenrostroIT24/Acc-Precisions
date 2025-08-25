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

class QaFaiSummaryController extends Controller
{
    /*===========================================================================================================================
                                            Todo el tab relacionado a parts revision
    ============================================================================================================================*/
    // Mostrar listado de registros
    // Mostrar listado de registros
    public function partsrevision()
    {
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
            // Filtro por ubicación según rol (Admin no filtra)
            ->when($user && $user->hasRole('QAdmin'), fn($q) => $q->whereRaw('LOWER(location) = ?', ['yarnell']))
            ->when($user && $user->hasRole('QA'),     fn($q) => $q->whereRaw('LOWER(location) = ?', ['hearst']));

        // Pendientes: null o 'pending'
        $ordersempty = (clone $base)
            ->where(function ($q) {
                $q->whereNull('status_inspection')
                    ->orWhere('status_inspection', 'pending');
            })
            ->orderByDesc('id')
            ->get();

        // En proceso
        $ordersprocess = (clone $base)
            ->where('status_inspection', 'in_progress')
            ->orderByDesc('id')
            ->get();

        return view('qa.faisummary.faisummary_partsrevision', compact('ordersempty', 'ordersprocess'));
    }



    public function updateOperation(Request $request, $id)
    {
        $request->validate([
            'operation' => 'required|numeric|min:0', // numérico, al menos 1
            'sampling'  => 'required|numeric|min:0', // valor de sampling
        ]);
        $order = OrderSchedule::findOrFail($id);
        $operation = (int) $request->operation;
        $sampling  = (int) $request->sampling;
        // Cálculos
        $total_fai = $operation * 1;
        $total_ipi = $operation * $sampling;
        // Guardar
        $order->operation  = $operation;
        $order->sampling  = $sampling;
        $order->total_fai  = $total_fai;
        $order->total_ipi  = $total_ipi;
        $order->save();
        return response()->json([
            'success' => true,
            'message' => 'Operation and totals updated.',
            'total_fai' => $total_fai,
            'total_ipi' => $total_ipi
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
        $order->save();

        return response()->json(['success' => true]);
    }

    //===========================================================================================================================
    /*===========================================================================================================================
         Todo el tab relacionado a parts summary
    ============================================================================================================================*/

    // Mostrar listado de registros
    public function summary(Request $request)
    {

        // Rango del mes actual
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $q = QaFaiSummary::query()
            ->with(['orderSchedule:id,work_id,location,PN'])
            ->whereBetween('date', [$start, $end])

            ->when($request->filled('search'), function ($qq) use ($request) {
                $s = (string) $request->string('search');
                $qq->where(function ($w) use ($s) {
                    $w->where('operation', 'like', "%{$s}%")
                        ->orWhere('operator', 'like', "%{$s}%")
                        ->orWhere('station',  'like', "%{$s}%")
                        ->orWhere('insp_type', 'like', "%{$s}%")
                        ->orWhere('inspector', 'like', "%{$s}%")
                        ->orWhere('results', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('location'), function ($qq) use ($request) {
                $loc = (string) $request->string('location');
                $qq->whereHas('orderSchedule', fn($os) => $os->where('location', $loc));
            });

        $inspections = $q->orderBy('date', 'desc')->orderBy('id', 'desc')
            ->paginate(100)->withQueryString();

        return view('qa.faisummary.faisummary_summary', compact('inspections'));
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
            'sampling'
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
                'station',
                'method',
                'inspector'
            ]);

        Log::debug('Datos de QA FAI Summary:', $rows->toArray());

        $generatedAt = now('America/Los_Angeles');

        // 1) Crea el PDF
        $pdf = PDF::loadView('qa.faisummary.faisummary_pdf', [
            'order'       => $order,
            'header'      => $header,
            'rows'        => $rows,
            'generatedAt' => $generatedAt,
        ])->setPaper('letter', 'landscape');

        // 2) Obtén Dompdf y RENDER explícitamente (clave)
        $dompdf = $pdf->getDomPDF();
        $dompdf->render(); // <-- importante: inicializa canvas y páginas

        // 3) Ahora SÍ: dibuja los folios con page_text en el canvas
        $canvas      = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();

        Log::debug('PDF canvas size', ['w' => $canvas->get_width(), 'h' => $canvas->get_height()]);

        $font = $fontMetrics->get_font('DejaVu Sans', 'normal'); // o 'helvetica'
        $size = 10;

        // Texto de prueba ARRIBA-IZQ para confirmar que pinta
        //$canvas->page_text(12, 24, 'TEST TOP {PAGE_NUM}/{PAGE_COUNT}', $font, $size, [255, 0, 0]); // rojo

        // Folio centrado abajo
        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $text = 'Page {PAGE_NUM} of {PAGE_COUNT}';
        $textWidth = $fontMetrics->get_text_width($text, $font, $size);
        $x = $w-70  ; // 20 pts desde el borde derecho
        $y = 5;                   // 20 pts desde arriba

        $canvas->page_text($x, $y, $text, $font, $size, [0, 0, 0]);

        // 4) Envía el PDF (usa dompdf->stream para evitar re-render)
        $filename = 'FAI_' . str_replace(['/', '\\'], '-', (string)$order->work_id) . '.pdf';
        return $dompdf->stream($filename, ['Attachment' => false]); // inline
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
