<?php

namespace App\Http\Controllers;

use App\Models\QaFaiSummary;
use Illuminate\Http\Request;
use App\Models\OrderSchedule;

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

        $orders = OrderSchedule::select('id', 'work_id', 'PN', 'Part_description','operation')->get();


        return view('qa.faisummary.faisummary_partsrevision', compact('orders'));
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
}
