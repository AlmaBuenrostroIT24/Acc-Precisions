<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Costing {{ $order->work_id }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 12pt;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 9.5pt;
            margin: 74pt 0 34pt;
        }

        header {
            position: fixed;
            top: -2pt;
            left: 0;
            right: 0;
            height: 66pt;
        }

        footer {
            position: fixed;
            bottom: -6pt;
            left: 0;
            right: 0;
            height: 24pt;
            font-size: 8pt;
            color: #475569;
        }

        .footer-table td {
            border: 0;
            border-top: 1px solid #cbd5e1;
            padding: 6pt 0 0;
        }

        .header-table td {
            border: 0;
            padding: 0;
            vertical-align: middle;
        }

        .header-title {
            font-size: 15pt;
            font-weight: 900;
            text-align: center;
            color: #1f3a66;
        }

        .header-subtitle {
            font-size: 9pt;
            text-align: center;
            color: #1f3a66;
            font-weight: 800;
            margin-top: 2pt;
        }

        .header-meta {
            font-size: 8.3pt;
            text-align: right;
            color: #1f3a66;
            font-weight: 900;
            line-height: 1.25;
        }

        .logo {
            height: 36pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #1f2937;
            padding: 3pt 5pt;
            vertical-align: top;
        }

        .pdf-operations-table {
            table-layout: fixed;
            width: 100%;
        }

        .pdf-op-col-subop {
            width: 12%;
        }

        .pdf-op-col-specs {
            width: 34%;
        }

        .pdf-op-col-time {
            width: 10.8%;
            padding-left: 2pt;
            padding-right: 2pt;
            font-size: 8.5pt;
        }

        .title {
            font-size: 14pt;
            font-weight: 800;
            text-transform: uppercase;
            border: 0;
            padding: 0 0 8pt;
            color: #1f3a66;
        }

        .section {
            margin-bottom: 10pt;
        }

        .section-banner {
            width: 100%;
            background: #2b4f86;
            color: #fff;
            font-size: 10pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 4pt 10pt;
            border-radius: 2pt;
            margin-bottom: 10pt;
        }

        .meta-table td {
            height: 15pt;
        }

        .label {
            font-weight: 800;
            width: 18%;
            background: #f8fafc;
        }

        .value {
            width: 32%;
        }

        .header-cell {
            background: #e8f0fb;
            color: #1f3a66;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            font-size: 8.5pt;
        }

        .operation-pill {
            display: inline-block;
            min-width: 20pt;
            padding: 2pt 8pt;
            border-radius: 999pt;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #1f2937;
            font-weight: 800;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary-label {
            font-weight: 800;
            text-transform: uppercase;
            text-align: right;
            background: #f8fafc;
        }

        .summary-title {
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
            font-size: 12pt;
            background: #e8f0fb;
            color: #1f3a66;
        }

        .notes-box {
            min-height: 78pt;
            white-space: pre-wrap;
        }

        .costing-pdf-costpcs {
            background: #fef3c7;
        }

        .costing-pdf-difference {
            background: #dcfce7;
        }

        .costing-pdf-result {
            background: #dcfce7;
        }
    </style>
</head>
<body>
    @php
        $formatHours = function ($value) {
            $value = (float) ($value ?? 0);
            $totalSeconds = (int) round($value * 3600);
            $hours = intdiv($totalSeconds, 3600);
            $minutes = intdiv($totalSeconds % 3600, 60);

            if ($totalSeconds === 0) {
                return '';
            }

            return sprintf('%d:%02d', $hours, $minutes);
        };

        $formatMoney = function ($value, $blankWhenZero = false) {
            $value = (float) ($value ?? 0);

            if ($blankWhenZero && abs($value) < 0.00001) {
                return '';
            }

            return number_format($value, 2);
        };

        $operations = $operations->values();
        $sumProgramming = $operations->sum(fn ($operation) => (float) ($operation->time_programming ?? 0));
        $sumSetup = $operations->sum(fn ($operation) => (float) ($operation->time_setup ?? 0));
        $sumRuntimePcs = $operations->sum(fn ($operation) => (float) ($operation->runtime_pcs ?? 0));
        $sumRuntimeTotal = $operations->sum(fn ($operation) => (float) ($operation->runtime_total ?? 0));
        $sumTotalTimeOperation = $operations->sum(fn ($operation) => (float) ($operation->total_time_operation ?? 0));
        $difference = (float) ($costing->difference_cost ?? 0);
        $result = (float) ($costing->percentage ?? 0);
        $resolvedRevision = trim((string) ($order->revision ?? ''));

        if ($resolvedRevision === '' || strtolower($resolvedRevision) === 'default_value') {
            preg_match('/\bREV(?:ISION)?\.?\s*[:\-]?\s*([A-Z0-9\-]+)/i', (string) ($order->Part_description ?? ''), $revisionMatches);
            $resolvedRevision = isset($revisionMatches[1]) ? 'REV. ' . trim($revisionMatches[1]) : '';
        }
    @endphp

    <header>
        <table class="header-table">
            <tr>
                <td style="width: 18%; text-align: left;">
                    @if(file_exists(public_path('img/acc.png')))
                        <img src="{{ public_path('img/acc.png') }}" alt="ACC Logo" class="logo">
                    @endif
                </td>
                <td style="width: 56%; vertical-align: middle;">
                    <div class="header-title">Actual Cost Job Analysis</div>
                    <div class="header-subtitle">ACC Precision, Inc.</div>
                </td>
                <td style="width: 26%; vertical-align: middle;">
                    <div class="header-meta">WO {{ $order->work_id ?: 'N/A' }}</div>
                    <div class="header-meta">PN {{ $order->PN ?: 'N/A' }}</div>
                    <div class="header-meta">AS OF {{ optional($order->due_date)->format('F/d/Y') ?: now()->format('F/d/Y') }}</div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <table class="footer-table">
            <tr>
                <td style="width: 33%;">ACC Precision</td>
                <td style="width: 34%;" class="text-center">Generated {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }} by {{ $printedBy ?? 'N/A' }}</td>
                <td style="width: 33%;" class="text-right">Costing {{ $order->work_id }}</td>
            </tr>
        </table>
    </footer>

    <div class="section">
        <div class="section-banner">Report</div>
    </div>

    <div class="section">
        <table class="meta-table">
            <tr>
                <td class="label">Customer:</td>
                <td class="value">{{ $order->costumer ?: 'N/A' }}</td>
                <td class="label">PN:</td>
                <td class="value">{{ $order->PN ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">WO#:</td>
                <td class="value">{{ $order->work_id ?: 'N/A' }}</td>
                <td class="label">Revision:</td>
                <td class="value">{{ $resolvedRevision !== '' ? $resolvedRevision : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">CO:</td>
                <td class="value">{{ $order->co ?: 'N/A' }}</td>
                <td class="label">Date:</td>
                <td class="value">{{ optional($order->due_date)->format('Y-m-d') ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Cust PO:</td>
                <td class="value">{{ $order->cust_po ?: 'N/A' }}</td>
                <td class="label">Operation:</td>
                <td class="value">
                    @if(filled($order->operation))
                        <span class="operation-pill">{{ $order->operation }}</span>
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Qty:</td>
                <td class="value">{{ $order->qty ?? 'N/A' }}</td>
                <td class="label">Part Description:</td>
                <td class="value">{{ $order->Part_description ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">WO Qty:</td>
                <td class="value">{{ $order->wo_qty ?? 'N/A' }}</td>
                <td class="label">Material Type:</td>
                <td class="value">{{ $costing->type_material ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Setup:</td>
                <td class="value">{{ $faiPassSummary ?? '' }}</td>
                <td class="label">Qty Material:</td>
                <td class="value">{{ abs((float) ($costing->qty_material ?? 0)) > 0.00001 ? number_format((float) $costing->qty_material, 4) : '' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="pdf-operations-table">
            <thead>
                <tr>
                    <th class="header-cell pdf-op-col-subop">SUB/OP</th>
                    <th class="header-cell pdf-op-col-specs">OP Specs</th>
                    <th class="header-cell pdf-op-col-time">PROG.<br>TIME</th>
                    <th class="header-cell pdf-op-col-time">Setup</th>
                    <th class="header-cell pdf-op-col-time">Run Time<br>Pcs</th>
                    <th class="header-cell pdf-op-col-time">Run Time<br>Total</th>
                    <th class="header-cell pdf-op-col-time">Total<br>OP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($operations as $operation)
                    <tr>
                        <td class="pdf-op-col-subop">{{ $operation->name_operation }}</td>
                        <td class="pdf-op-col-specs">{{ $operation->resource_name ?: '-----' }}</td>
                        <td class="text-center pdf-op-col-time">{{ $formatHours($operation->time_programming ?? 0) }}</td>
                        <td class="text-center pdf-op-col-time">{{ $formatHours($operation->time_setup ?? 0) }}</td>
                        <td class="text-center pdf-op-col-time">{{ $formatHours($operation->runtime_pcs ?? 0) }}</td>
                        <td class="text-center pdf-op-col-time">{{ $formatHours($operation->runtime_total ?? 0) }}</td>
                        <td class="text-center pdf-op-col-time">{{ $formatHours($operation->total_time_operation ?? 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No operations registered.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2" class="summary-label">Total Times:</td>
                    <td class="text-center pdf-op-col-time">{{ $formatHours($sumProgramming) }}</td>
                    <td class="text-center pdf-op-col-time">{{ $formatHours($sumSetup) }}</td>
                    <td class="text-center pdf-op-col-time">{{ $formatHours($sumRuntimePcs) }}</td>
                    <td class="text-center pdf-op-col-time">{{ $formatHours($sumRuntimeTotal) }}</td>
                    <td class="text-center"></td>
                </tr>
                <tr>
                    <td colspan="6" class="summary-label" style="background:#fff;">Total Hours:</td>
                    <td class="text-center" style="background:#f1f5f9;"><strong>{{ $formatHours($costing->total_time_order ?? $sumTotalTimeOperation) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="6" class="summary-label" style="background:#fff;">Actual Hours:</td>
                    <td class="text-center" style="background:#f1f5f9;"><strong>{{ $formatHours($costing->hrs_actual ?? 0) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="6" class="summary-label" style="background:#fff;">Hours Variance:</td>
                    <td class="text-center" style="background:#f1f5f9;"><strong>{{ $formatHours($costing->hrs_variance ?? 0) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <table>
            <tr>
                <td style="width:56%; padding:0; border:0;">
                    <table>
                        <tr>
                            <td class="summary-label" style="width:48%;">Total Labor:</td>
                            <td style="width:6%;" class="text-center">$</td>
                            <td class="text-right">{{ $formatMoney($costing->total_labor ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Total Materials:</td>
                            <td class="text-center">$</td>
                            <td class="text-right">{{ $formatMoney($costing->total_material ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Total Outsource Process:</td>
                            <td class="text-center">$</td>
                            <td class="text-right">{{ $formatMoney($costing->total_outsource ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="notes-box">
                                <strong>Notes:</strong><br>
                                {{ $costing->notes ?? '' }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:44%; padding:0; border:0;">
                    <table>
                        <tr>
                            <td colspan="4" class="summary-title">Final Comparation</td>
                        </tr>
                        <tr>
                            <td class="summary-label" style="width:46%;">Sale Price:</td>
                            <td style="width:6%;" class="text-center">$</td>
                            <td colspan="2" class="text-right">{{ $formatMoney($costing->sale_price ?? 0, true) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Grandtotal Cost:</td>
                            <td class="text-center">$</td>
                            <td colspan="2" class="text-right">{{ $formatMoney($costing->grandtotal_cost ?? 0, true) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Cost Pcs:</td>
                            <td class="text-center">$</td>
                            <td colspan="2" class="text-right costing-pdf-costpcs">{{ $formatMoney($costing->price_pcs ?? 0, true) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Difference:</td>
                            <td class="text-center">$</td>
                            <td colspan="2" class="text-right costing-pdf-difference">{{ $formatMoney($difference, true) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Result:</td>
                            <td class="text-center">$</td>
                            <td class="text-right costing-pdf-result">{{ abs($result) > 0.00001 ? number_format($result, 2) : '' }}</td>
                            <td class="text-center">%</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
