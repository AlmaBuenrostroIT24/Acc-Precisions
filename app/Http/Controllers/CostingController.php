<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\OrderSchedule;

class CostingController extends Controller
{
    public function index()
    {
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

        return view('quotes.costing.index', compact('orders'));
    }

    public function pdf(OrderSchedule $order)
    {
        $pdf = Pdf::loadView('quotes.costing.pdf', [
            'order' => $order,
        ])->setPaper('letter', 'portrait');

        $cleanWorkId = preg_replace('/[\/\\\\]/', '_', (string) $order->work_id);

        return $pdf->stream("costing_{$cleanWorkId}.pdf");
    }
}
