<?php

namespace App\Http\Controllers;

use App\Models\QaFaiSummary;
use Illuminate\Http\Request;
use App\Models\OrderSchedule;
use Illuminate\Support\Facades\Log;
use App\Models\QaSamplingPlan;

class QaFaiSummaryController extends Controller
{
    // Mostrar listado de registros
    public function index()
    {


        return view('qa.faisummary.index_faisummary');
    }
    // Mostrar listado de registros
    public function partsrevision()
    {
        // Consulta para 'operation' con 'default_value' o NULL
        $ordersempty = OrderSchedule::select(
            'id',
            'work_id',
            'PN',
            'Part_description',
            'operation',
            'wo_qty',
            'was_work_id_null',
            'co'
        )
            ->where('status', '<>', 'sent')
            ->where(function ($query) {
                $query->where('was_work_id_null', 0)
                    ->orWhereNull('co');
            })
            ->where(function ($query) {
                $query->where('operation', 'default_value')
                    ->orWhereNull('operation');
            })
            ->get();

        // Consulta para 'orderprocess' diferente de NULL y 'default_value'
        $ordersprocess = OrderSchedule::select('id', 'work_id', 'PN', 'Part_description', 'operation', 'wo_qty')
            ->where('status', '<>', 'sent')  // Filtra las órdenes cuyo estado no sea 'sent'
            ->where(function ($query) {
                $query->where('operation', '<>', 'default_value')  // Filtra donde orderprocess no sea 'default_value'
                    ->whereNotNull('operation');  // Y también donde orderprocess no sea NULL
            })
            ->get();

        return view('qa.faisummary.faisummary_partsrevision', compact('ordersempty', 'ordersprocess'));
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

    public function updateOperation(Request $request, $id)
    {
        $request->validate([
            'operation' => 'required|string|max:255',
        ]);
        $order = OrderSchedule::findOrFail($id);
        $order->operation = $request->operation;
        $order->save();
        return response()->json(['success' => true, 'message' => 'Operation updated.']);
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
            'part_rev' => 'required|string',
            'job' => 'required|string',
            'num_operation' => 'required|string',
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


    public function getByOrder($orderScheduleId)
    {
        $rows = \App\Models\QaFaiSummary::where('order_schedule_id', $orderScheduleId)->get();
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
}
