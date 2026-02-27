<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

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
                    'status' => (string) (($x->status ?? '') ?: 'New'),
                    'edit_url' => route('nonconformance.ncar.edit', ['id' => (int) $x->id]),
                    'pdf_url' => route('nonconformance.ncar.pdf', ['id' => (int) $x->id]),
                    'excel_url' => route('nonconformance.ncar.excel', ['id' => (int) $x->id]),
                    'delete_url' => route('nonconformance.ncar.destroy', ['id' => (int) $x->id]),
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
        $ordersCols = Schema::hasTable('orders_schedule') ? Schema::getColumnListing('orders_schedule') : [];
        $woQtyCol = null;
        foreach (['group_wo_qty', 'wo_qty', 'WO_Qty', 'WO_QTY', 'WOQty', 'woQty'] as $c) {
            if (in_array($c, $ordersCols, true)) {
                $woQtyCol = $c;
                break;
            }
        }

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
                'o.costumer as order_customer',
                'o.operation as order_operation',
                'o.qty as order_qty',
                $woQtyCol ? DB::raw("o.`{$woQtyCol}` as wo_qty") : DB::raw('NULL as wo_qty'),
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
        $blocked = ['id', 'ncar_no', 'order_id', 'ncartype_id', 'created_at', 'updated_at', 'ncar_customer', 'contact'];
        $allowed = array_values(array_diff($tableColumns, $blocked));

        $input = $r->except(['_token', '_method']);
        $input = array_intersect_key($input, array_flip($allowed));

        $rules = [];
        foreach (array_keys($input) as $field) {
            $rules[$field] = $this->ruleForNcarField($field);
        }

        $data = $rules ? $r->validate($rules) : [];

        // Laravel converts empty strings to null (ConvertEmptyStringsToNull middleware).
        // Some NCAR columns are NOT NULL in the database, so normalize string nulls back to ''.
        foreach ($rules as $field => $rule) {
            if (!array_key_exists($field, $data) || $data[$field] !== null) {
                continue;
            }

            $ruleStr = is_array($rule) ? implode('|', $rule) : (string) $rule;
            if (str_contains($ruleStr, 'string')) {
                $data[$field] = '';
            }
        }

        // Allow empty selects (null) for boolean-ish fields when DB columns are nullable.
        // If a column is NOT NULL, fall back to 0 to avoid SQL errors.
        foreach (['jobpktcopy', 'travinsp', 'samplecompl', 'reqrootcause', 'containmentreq', 'contaimentreq'] as $f) {
            if (!array_key_exists($f, $data) || $data[$f] !== null) {
                continue;
            }

            $nullable = $this->columnIsNullable('qa_ncar', $f);
            if ($nullable === false) {
                $data[$f] = 0;
            }
        }

        if (!empty($data)) {
            DB::table('qa_ncar')->where('id', $id)->update($data);
        }

        return redirect()
            ->route('nonconformance.ncar.edit', ['id' => $id])
            ->with('status', 'NCAR updated.');
    }

    public function pdf($id)
    {
        $ordersCols = Schema::hasTable('orders_schedule') ? Schema::getColumnListing('orders_schedule') : [];
        $woQtyCol = null;
        foreach (['wo_qty', 'WO_Qty', 'WO_QTY', 'WOQty', 'woQty'] as $c) {
            if (in_array($c, $ordersCols, true)) {
                $woQtyCol = $c;
                break;
            }
        }

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
                'o.revision as order_revision',
                'o.qty as order_qty',
                'o.operation as order_operation',
                'o.costumer as order_customer',
                $woQtyCol ? DB::raw("o.`{$woQtyCol}` as wo_qty") : DB::raw('NULL as wo_qty'),
            )
            ->where('n.id', (int) $id)
            ->first();

        abort_unless($ncar, 404);

        $filename = 'NCAR-' . preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) ($ncar->ncar_no ?? $id)) . '.pdf';

        $pdf = Pdf::loadView('qa.nonconformance.ncar_pdf', compact('ncar'))
            ->setPaper('letter', 'portrait');

        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
        $size = 10;

        $w = $canvas->get_width();
        $h = $canvas->get_height();

        // These must match the view's @page margins.
        $pageMarginLeft = 12;
        $pageMarginRight = 12;
        $pageMarginBottom = 150;

        // Dompdf canvas size can be either the full page box or the content box (excluding margins),
        // depending on backend/version. Detect heuristically and compute coordinates accordingly.
        $canvasIsContentBox = ($w < 600) || ($h < 760);

        $y = $canvasIsContentBox
            ? ($h + $pageMarginBottom - 22)
            : ($h - 22);

        // Align footer text with the same left/right edges as the table in the view.
        // View CSS:
        // - @page { margin: 18pt 12pt 150pt; }
        // - table.grid { width: 96%; margin: 0 auto; }
        if ($canvasIsContentBox) {
            $tableWidth = $w * 0.96;
            $tableLeft = ($w - $tableWidth) / 2;
            $tableRight = $tableLeft + $tableWidth;
        } else {
            $contentWidth = $w - $pageMarginLeft - $pageMarginRight;
            $tableWidth = $contentWidth * 0.96;
            $tableLeft = $pageMarginLeft + (($contentWidth - $tableWidth) / 2);
            $tableRight = $tableLeft + $tableWidth;
        }

        $canvas->page_text($tableLeft, $y, 'F-870-001 Rev. D  LA Authorized', $font, $size, [0, 0, 0]);

        $rightText = 'Page {PAGE_NUM} of {PAGE_COUNT}';
        $rightTextMeasure = 'Page 999 of 999';
        // Use canvas text width to match the actual rendering backend.
        $rightWidth = $canvas->get_text_width($rightTextMeasure, $font, $size);
        $xRight = $tableRight - $rightWidth;
        $canvas->page_text($xRight, $y, $rightText, $font, $size, [0, 0, 0]);

        return $dompdf->stream($filename, ['Attachment' => false]);
    }

    public function excel($id)
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

        $safeNo = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) ($ncar->ncar_no ?? $id));
        $filename = 'NCAR-' . $safeNo . '.xlsx';

        $export = new class($ncar) implements FromArray, WithHeadings {
            public function __construct(private object $ncar)
            {
            }

            public function headings(): array
            {
                return [
                    'NCAR No',
                    'Created At',
                    'Status',
                    'Type',
                    'Stage',
                    'Location',
                    'Reference',
                    'NC Description',
                    'Order Customer',
                    'WorkID',
                    'CO',
                    'PO',
                    'PN',
                    'Part Description',
                ];
            }

            public function array(): array
            {
                $n = $this->ncar;
                return [[
                    (string) ($n->ncar_no ?? ''),
                    (string) ($n->created_at ?? ''),
                    (string) (($n->status ?? '') ?: 'New'),
                    (string) ($n->type_name ?? ''),
                    (string) ($n->stage ?? ''),
                    (string) ($n->location ?? ''),
                    (string) ($n->ref ?? ''),
                    (string) ($n->nc_description ?? ''),
                    (string) ($n->order_customer ?? ''),
                    (string) ($n->work_id ?? ''),
                    (string) ($n->co ?? ''),
                    (string) ($n->cust_po ?? ''),
                    (string) ($n->PN ?? ''),
                    (string) ($n->Part_description ?? ''),
                ]];
            }
        };

        return Excel::download($export, $filename);
    }

    public function destroy(Request $r, $id)
    {
        $id = (int) $id;
        abort_unless(DB::table('qa_ncar')->where('id', $id)->exists(), 404);

        try {
            DB::table('qa_ncar')->where('id', $id)->delete();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not delete NCAR.',
            ], 500);
        }

        return response()->json(['success' => true]);
    }

    private function ruleForNcarField(string $field): array 
    { 
        $f = strtolower($field);

        if ($f === 'status') {
            return ['nullable', 'string', 'max:50'];
        }

        if (in_array($f, ['jobpktcopy', 'travinsp', 'samplecompl', 'reqrootcause', 'containmentreq', 'contaimentreq', 'spprocsinvld', 'spprocinvld', 'spprocinvalid', 'sp_proc_invalid'], true)) { 
            return ['nullable', 'boolean']; 
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

        if (in_array($f, ['nc_description', 'disposition', 'discrepancy', 'rootcause', 'corrective', 'verification', 'containment', 'noterpreroot', 'personnelaccounts', 'personnelinvolved', 'processaffected', 'relevantfunction', 'issuefoundbt'], true)) {
            return ['nullable', 'string', 'max:4000'];
        }

        return ['nullable', 'string', 'max:255']; 
    } 

    private function columnIsNullable(string $table, string $column): ?bool
    {
        try {
            $db = DB::getDatabaseName();
            $row = DB::selectOne(
                'SELECT IS_NULLABLE as is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
                [$db, $table, $column]
            );
            if (!$row) return null;
            $v = strtoupper((string) ($row->is_nullable ?? $row->IS_NULLABLE ?? ''));
            if ($v === 'YES') return true;
            if ($v === 'NO') return false;
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
