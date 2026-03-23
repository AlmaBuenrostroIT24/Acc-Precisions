<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\OrderSchedule;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function pdf(OrderSchedule $order)
    {
        $pdf = Pdf::loadView('quotes.costing.pdf', [
            'order' => $order,
        ])->setPaper('letter', 'portrait');

        $cleanWorkId = preg_replace('/[\/\\\\]/', '_', (string) $order->work_id);

        return $pdf->stream("costing_{$cleanWorkId}.pdf");
    }
}
