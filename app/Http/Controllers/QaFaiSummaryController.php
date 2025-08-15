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

class QaFaiSummaryController extends Controller
{
    /*===========================================================================================================================
                                            Todo el tab relacionado a parts revision
    ============================================================================================================================*/
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
            'location'
        ];
        // Log para ver el array de select
        Log::info('Campos SELECT en partsrevision:', $select);
        // Base común con la condición:
        // (was_work_id_null = 0 AND co NOT NULL) OR (was_work_id_null = 1 AND co IS NULL)
        $base = OrderSchedule::select($select)
            ->where('status', '<>', 'sent')
            ->where(function ($q) {
                $q->where(function ($x) {
                    $x->where('was_work_id_null', 0)
                        ->whereNotNull('co');
                })->orWhere(function ($x) {
                    $x->where('was_work_id_null', 1)
                        ->whereNull('co');
                });
            })
            // Filtro por ubicación según rol (Admin no filtra)
            ->when($user && $user->hasRole('QAdmin'), fn($q) => $q->where('location', 'yarnell'))
            ->when($user && $user->hasRole('QA'),     fn($q) => $q->where('location', 'hearst'));
        // Admin no filtra
        $ordersempty = (clone $base)
            ->where(function ($q) {
                $q->where('operation', '0')
                    ->orWhereNull('operation');
            })
            ->get();
        $ordersprocess = (clone $base)
            ->whereNotNull('work_id')
            ->where(function ($q) {
                $q->where('operation', '<>', '0')
                    ->whereNotNull('operation');
            })
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

    // Mostrar listado de registros
    public function faicompleted()
    {


        return view('qa.faisummary.faisummary_completed');
    }

    // Mostrar listado de registros
    public function faistatistics()
    {


        return view('qa.faisummary.faisummary_statistics');
    }
}
