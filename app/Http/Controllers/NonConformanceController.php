<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NonConformanceController extends Controller
{
    //


    public function ncarparts()
    {
        return view('qa.nonconformance.nonconformance_ncarparts');
    }

    // === Demo: devuelve datos de la tabla (adáptalo a tu modelo) ===
    public function data(Request $r)
    {
        // Estructura mínima para DataTables (serverSide=false en este ejemplo)
        return response()->json([
            [
                'number' => 1024,
                'title' => 'Ion Pump',
                'created' => '2025-09-18',
                'customer' => 'JB JOHN',
                'ref_numbers' => 'Return',
                'type' => 'Return',
                'parts' => '4E3LKARD',
                'status' => 'Closed'
            ],
            // ... agrega más rows
        ]);
    }

    // === Demo: KPIs y series de los charts ===
    public function stats(Request $r)
    {
        return response()->json([
            'kpis' => [
                'new' => 20,
                'quality_review' => 2,
                'engineering_review' => 1,
                'total' => 24,
            ],
            // barras por causa
            'by_cause' => [
                ['label' => 'Equipment', 'value' => 1],
                ['label' => 'Human',     'value' => 2],
                ['label' => 'Customer',  'value' => 1],
                ['label' => 'Vendor',    'value' => 1],
                ['label' => 'Material',  'value' => 0],
                ['label' => 'Fixturing', 'value' => 0],
                ['label' => 'Process',   'value' => 1],
                ['label' => 'Other',     'value' => 0],
            ],
            // serie del área (ej. totales por mes)
            'trend' => [
                ['x' => '2025-01-31', 'y' => 18],
                ['x' => '2025-02-28', 'y' => 19],
                ['x' => '2025-03-31', 'y' => 15],
                ['x' => '2025-04-30', 'y' => 14],
                ['x' => '2025-05-31', 'y' => 16],
                ['x' => '2025-06-30', 'y' => 9],
                ['x' => '2025-07-31', 'y' => 8],
                ['x' => '2025-08-31', 'y' => 8],
                ['x' => '2025-09-30', 'y' => 8],
            ],
        ]);
    }
}
