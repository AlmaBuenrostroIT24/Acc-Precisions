<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NonConformanceController extends Controller
{
    public function ncarparts()
    {
        return view('qa.nonconformance.nonconformance_ncarparts');
    }

    public function data(Request $r)
    {
        $rows = DB::table('qa_ncar as n')
            ->leftJoin('qa_ncartype as t', 't.id', '=', 'n.ncartype_id')
            ->leftJoin('orders_schedule as o', 'o.id', '=', 'n.order_id')
            ->select([
                'n.ncar_no',
                'n.created_at',
                'n.status',
                'n.ncar_customer',
                'n.stage',
                'n.location',
                'n.ref',
                't.name as type_name',
                'o.work_id',
                'o.co',
                'o.cust_po',
                'o.PN',
                'o.Part_description',
                'o.costumer',
            ])
            ->orderByDesc('n.id')
            ->limit(500)
            ->get()
            ->map(function ($x) {
                $pn = (string) ($x->PN ?? '');
                $desc = trim((string) Str::before((string) ($x->Part_description ?? ''), ','));
                $title = trim($pn . ($desc ? (' - ' . $desc) : ''));

                $refs = [];
                if (!empty($x->work_id)) $refs[] = 'WorkID: ' . $x->work_id;
                if (!empty($x->co)) $refs[] = 'CO: ' . $x->co;
                if (!empty($x->cust_po)) $refs[] = 'PO: ' . $x->cust_po;

                return [
                    'number' => (string) ($x->ncar_no ?? ''),
                    'title' => $title !== '' ? $title : ((string) ($x->ref ?? '')),
                    'created' => $x->created_at ? (string) $x->created_at : null,
                    'customer' => (string) (($x->ncar_customer ?? '') ?: ($x->costumer ?? '')),
                    'ref_numbers' => implode(' | ', $refs),
                    'type' => (string) ($x->type_name ?? ''),
                    'parts' => (string) ($pn ?: ''),
                    'status' => (string) (($x->status ?? '') ?: 'New'),
                ];
            })
            ->values()
            ->all();

        return response()->json($rows);
    }

    public function stats(Request $r)
    {
        $base = DB::table('qa_ncar');

        $total = (clone $base)->count();
        $new = (clone $base)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '')->orWhereRaw('LOWER(status) = ?', ['new']);
            })
            ->count();

        $quality = (clone $base)->whereRaw('LOWER(status) = ?', ['quality review'])->count();
        $eng = (clone $base)->whereRaw('LOWER(status) = ?', ['engineering review'])->count();

        $stages = (clone $base)
            ->selectRaw("COALESCE(stage,'') as stage, COUNT(*) as c")
            ->groupBy('stage')
            ->pluck('c', 'stage');

        $byCauseLabels = ['Equipment', 'Human', 'Customer', 'Vendor', 'Material', 'Fixturing', 'Process', 'Other', 'QA'];
        $byCause = [];
        foreach ($byCauseLabels as $lbl) {
            $byCause[] = ['label' => $lbl, 'value' => (int) ($stages[$lbl] ?? 0)];
        }

        $trendRaw = (clone $base)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-01') as m, COUNT(*) as c")
            ->whereNotNull('created_at')
            ->groupBy('m')
            ->orderBy('m')
            ->get();

        $trend = $trendRaw->map(function ($x) {
            return ['x' => $x->m, 'y' => (int) $x->c];
        })->values();

        return response()->json([
            'kpis' => [
                'new' => (int) $new,
                'quality_review' => (int) $quality,
                'engineering_review' => (int) $eng,
                'total' => (int) $total,
            ],
            'by_cause' => $byCause,
            'trend' => $trend,
        ]);
    }
}

