<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

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
                'n.id',
                'n.ncar_no',
                'n.created_at',
                'n.status',
                'n.ncar_customer',
                'n.stage',
                'n.location',
                'n.ref',
                'n.nc_description',
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
                    'id' => (int) $x->id,
                    'number' => (string) ($x->ncar_no ?? ''),
                    'description' => (string) ($x->nc_description ?? ''),
                    'title' => $title !== '' ? $title : ((string) ($x->ref ?? '')),
                    'created' => $x->created_at ? (string) $x->created_at : null,
                    'customer' => (string) (($x->ncar_customer ?? '') ?: ($x->costumer ?? '')),
                    'ref_numbers' => implode(' | ', $refs),
                    'type' => (string) ($x->type_name ?? ''),
                    'parts' => (string) ($pn ?: ''),
                    'status' => (string) (($x->status ?? '') ?: 'New'),
                    'edit_url' => route('nonconformance.ncar.edit', ['id' => (int) $x->id]),
                    'pdf_url' => route('nonconformance.ncar.pdf', ['id' => (int) $x->id]),
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

    public function edit($id)
    {
        $ncar = DB::table('qa_ncar as n')
            ->leftJoin('qa_ncartype as t', 't.id', '=', 'n.ncartype_id')
            ->leftJoin('orders_schedule as o', 'o.id', '=', 'n.order_id')
            ->select(
                'n.*',
                't.name as type_name',
                'o.work_id',
                'o.co',
                'o.cust_po',
                'o.PN',
                'o.Part_description',
                'o.costumer as order_customer'
            )
            ->where('n.id', (int) $id)
            ->first();

        abort_unless($ncar, 404);

        $columns = Schema::hasTable('qa_ncar') ? Schema::getColumnListing('qa_ncar') : [];

        return view('qa.nonconformance.ncar_edit', compact('ncar', 'columns'));
    }

    public function update(Request $r, $id)
    {
        $id = (int) $id;
        abort_unless(DB::table('qa_ncar')->where('id', $id)->exists(), 404);

        $tableColumns = Schema::hasTable('qa_ncar') ? Schema::getColumnListing('qa_ncar') : [];
        $blocked = ['id', 'ncar_no', 'order_id', 'ncartype_id', 'created_at', 'updated_at', 'ncar_customer'];
        $allowed = array_values(array_diff($tableColumns, $blocked));

        $input = $r->except(['_token', '_method']);
        $input = array_intersect_key($input, array_flip($allowed));

        $rules = [];
        foreach (array_keys($input) as $field) {
            $rules[$field] = $this->ruleForNcarField($field);
        }

        $data = $rules ? $r->validate($rules) : [];

        if (!empty($data)) {
            DB::table('qa_ncar')->where('id', $id)->update($data);
        }

        return redirect()
            ->route('nonconformance.ncar.edit', ['id' => $id])
            ->with('status', 'NCAR updated.');
    }

    public function pdf($id)
    {
        $ncar = DB::table('qa_ncar as n')
            ->leftJoin('qa_ncartype as t', 't.id', '=', 'n.ncartype_id')
            ->leftJoin('orders_schedule as o', 'o.id', '=', 'n.order_id')
            ->select('n.*', 't.name as type_name', 'o.work_id', 'o.co', 'o.cust_po', 'o.PN', 'o.Part_description', 'o.costumer as order_customer')
            ->where('n.id', (int) $id)
            ->first();

        abort_unless($ncar, 404);

        $filename = 'NCAR-' . preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) ($ncar->ncar_no ?? $id)) . '.pdf';
        return Pdf::loadView('qa.nonconformance.ncar_pdf', compact('ncar'))
            ->setPaper('letter')
            ->download($filename);
    }

    private function ruleForNcarField(string $field): array
    {
        $f = strtolower($field);

        if ($f === 'status') {
            return ['nullable', 'string', 'max:50'];
        }

        if (str_contains($f, 'date')) {
            // Aceptar formatos comunes (YYYY-MM-DD o texto); no forzamos date por compatibilidad
            return ['nullable', 'string', 'max:25'];
        }

        if (preg_match('/(qty|quantity|delqty|rejqty|stkqty)$/', $f)) {
            return ['nullable', 'numeric'];
        }

        if (preg_match('/(copy|insp|sample|invalid|req)$/', $f)) {
            return ['nullable', 'string', 'max:10'];
        }

        if (in_array($f, ['nc_description', 'disposition', 'discrepancy', 'rootcause', 'corrective', 'verification', 'containment', 'noterpreroot', 'personnelaccounts', 'personnelinvolved', 'processaffected', 'relevantfunction', 'issuefoundbt', 'reqrootcause'], true)) {
            return ['nullable', 'string', 'max:4000'];
        }

        return ['nullable', 'string', 'max:255'];
    }
}
