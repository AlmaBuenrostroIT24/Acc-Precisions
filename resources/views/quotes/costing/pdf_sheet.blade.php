<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Costing {{ $order->work_id }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 10pt;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12pt;
            margin: 34pt 0 28pt;
        }

        header {
            position: fixed;
            top: -2pt;
            left: 0;
            right: 0;
            height: 58pt;
        }

        footer {
            position: fixed;
            bottom: -6pt;
            left: 0;
            right: 0;
            height: 20pt;
            font-size: 8pt;
            color: #475569;
        }

        .footer-table td {
            border: 0;
            border-top: 1px solid #cbd5e1;
            padding: 4pt 0 0;
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
            height: 32pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #1f2937;
            padding: 2pt 4pt;
            vertical-align: top;
        }

        .section {
            margin-bottom: 2pt;
        }

        .section-banner {
            width: 100%;
            background: #2b4f86;
            color: #fff;
            font-size: 10pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 3pt 10pt;
            border-radius: 2pt;
            margin-bottom: 4pt;
        }

        .meta-table td {
            height: 12pt;
        }

        .meta-split-table,
        .meta-split-table td,
        .meta-inner-table,
        .meta-inner-table td {
            border: 0;
            padding: 0;
        }

        .meta-split-table {
            table-layout: fixed;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .meta-inner-table td {
            border: 1px solid #1f2937;
            padding: 2pt 4pt;
            height: 18pt;
            vertical-align: top;
        }

        .meta-inner-left td:last-child {
            border-right: 0;
        }

        .meta-inner-left .label {
            width: 28%;
        }

        .meta-inner-left .value {
            width: 72%;
        }

        .meta-inner-right .label {
            width: 24%;
        }

        .meta-inner-right .value {
            width: 76%;
        }

        .meta-inner-right tr:nth-child(5) td,
        .meta-inner-right tr:nth-child(6) td {
            height: 22pt;
        }

        .meta-inner-left tr:nth-child(5) td,
        .meta-inner-left tr:nth-child(6) td {
            height: 22pt;
        }

        .label {
            font-weight: 800;
            background: #f8fafc;
        }

        .value {
        }

        .header-cell {
            background: #e8f0fb;
            color: #1f3a66;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            font-size: 10.5pt;
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

        .pdf-operations-table {
            table-layout: fixed;
        }

        .pdf-op-col-subop {
            width: 12%;
        }

        .pdf-op-col-specs {
            width: 34%;
            font-size: 11pt;
        }

        .pdf-op-col-time {
            width: 10.8%;
            padding-left: 2pt;
            padding-right: 2pt;
            font-size: 10pt;
        }

        .operations-row td {
            height: 15pt;
            vertical-align: middle;
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
            padding-top: 2pt;
            padding-bottom: 2pt;
        }

        .summary-title {
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
            font-size: 12pt;
            background: #e8f0fb;
            color: #1f3a66;
            padding-top: 2pt;
            padding-bottom: 2pt;
        }

        .notes-box {
            height: 44pt;
            white-space: pre-wrap;
            padding-top: 2pt;
            padding-bottom: 2pt;
        }

        .pdf-summary-table td {
            padding-top: 2pt;
            padding-bottom: 2pt;
        }
    </style>
</head>
<body>
    @php
        $operations = collect([
            (object) ['name_operation' => 'Travel Proc.', 'resource_name' => '-----'],
            (object) ['name_operation' => '0 /', 'resource_name' => null],
            (object) ['name_operation' => '0 /', 'resource_name' => null],
            (object) ['name_operation' => '0 /', 'resource_name' => null],
            (object) ['name_operation' => '0 /', 'resource_name' => null],
            (object) ['name_operation' => '0 /', 'resource_name' => null],
            (object) ['name_operation' => '0 /', 'resource_name' => null],
            (object) ['name_operation' => null, 'resource_name' => null],
            (object) ['name_operation' => null, 'resource_name' => null],
            (object) ['name_operation' => null, 'resource_name' => null],
            (object) ['name_operation' => null, 'resource_name' => null],
        ]);
        $resolvedRevision = trim((string) ($order->revision ?? ''));

        if ($resolvedRevision === '' || strtolower($resolvedRevision) === 'default_value') {
            preg_match('/\bREV(?:ISION)?\.?\s*[:\-]?\s*([A-Z0-9\-]+)/i', (string) ($order->Part_description ?? ''), $revisionMatches);
            $resolvedRevision = isset($revisionMatches[1]) ? 'REV. ' . trim($revisionMatches[1]) : '';
        }

        $partDescription = (string) ($order->Part_description ?? '');
        $partDescriptionStyle = 'white-space: nowrap;';
        $pnValue = (string) ($order->PN ?? '');
        $pnValueStyle = 'white-space: nowrap;';

        if (mb_strlen($pnValue) > 12) {
            $pnValueStyle .= ' font-size: 9pt;';
        }

        if (mb_strlen($pnValue) > 18) {
            $pnValueStyle .= ' font-size: 8pt;';
        }

        if (mb_strlen($pnValue) > 24) {
            $pnValueStyle .= ' font-size: 7pt; white-space: normal; word-break: break-word; line-height: 1.1;';
        }

        $customerValue = (string) ($order->costumer ?? '');
        $customerValueStyle = 'white-space: nowrap;';

        if (mb_strlen($customerValue) > 12) {
            $customerValueStyle .= ' font-size: 9pt;';
        }

        if (mb_strlen($customerValue) > 18) {
            $customerValueStyle .= ' font-size: 8pt;';
        }

        if (mb_strlen($customerValue) > 24) {
            $customerValueStyle .= ' font-size: 7pt; white-space: normal; word-break: break-word; line-height: 1.1;';
        }

        if (mb_strlen($partDescription) > 50) {
            $partDescriptionStyle .= ' font-size: 10pt;';
        }

        if (mb_strlen($partDescription) > 70) {
            $partDescriptionStyle .= ' font-size: 9pt;';
        }

        if (mb_strlen($partDescription) > 90) {
            $partDescriptionStyle .= ' font-size: 8pt;';
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
                <td style="width: 34%;" class="text-center">Generated {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</td>
                <td style="width: 33%;" class="text-right">Costing {{ $order->work_id }}</td>
            </tr>
        </table>
    </footer>

    <div class="section">
        <div class="section-banner">Report</div>
    </div>

    <div class="section">
        <table class="meta-inner-table" style="width: 100%; table-layout: fixed;">
            <tr>
                <td class="label" style="width: 11%;">Customer:</td>
                <td class="value" style="width: 21%; {{ $customerValueStyle }}">{{ $order->costumer ?: 'N/A' }}</td>
                <td class="label" style="width: 12%;">PN:</td>
                <td class="value" style="width: 18%; {{ $pnValueStyle }}">{{ $order->PN ?: 'N/A' }}</td>
                <td class="label" style="width: 13%;">Revision:</td>
                <td class="value" style="width: 25%;">{{ $resolvedRevision !== '' ? $resolvedRevision : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">WO#:</td>
                <td class="value">{{ $order->work_id ?: 'N/A' }}</td>
                <td class="label">CO:</td>
                <td class="value">{{ $order->co ?: 'N/A' }}</td>
                <td class="label">Cust PO:</td>
                <td class="value">{{ $order->cust_po ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Qty:</td>
                <td class="value">{{ $order->qty ?? 'N/A' }}</td>
                <td class="label">WO Qty:</td>
                <td class="value">{{ $order->wo_qty ?? 'N/A' }}</td>
                <td class="label">Qty Costing:</td>
                <td class="value">{{ (int) ($costing->qty_costing ?? 0) > 0 ? (int) $costing->qty_costing : '' }}</td>
            </tr>
            <tr>
                <td class="label">Setup:</td>
                <td class="value">{{ $faiPassSummary ?? '' }}</td>
                <td class="label">Operation:</td>
                <td class="value">
                    @if(filled($order->operation))
                        <span class="operation-pill">{{ $order->operation }}</span>
                    @else
                        N/A
                    @endif
                </td>
                <td class="label">Date:</td>
                <td class="value">
                    @if($order->due_date)
                        {{ \Illuminate\Support\Str::ucfirst(str_replace('.', '', \Carbon\Carbon::parse($order->due_date)->locale('es')->translatedFormat('M-d-Y'))) }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Qty Material:</td>
                <td class="value"></td>
                <td class="label">Material Type:</td>
                <td class="value" colspan="3"></td>
            </tr>
            <tr>
                <td class="label">Part Desc.</td>
                <td class="value" colspan="5" style="{{ $partDescriptionStyle }}">{{ $partDescription ?: 'N/A' }}</td>
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
                    <tr class="operations-row">
                        <td class="pdf-op-col-subop">{{ $operation->name_operation }}</td>
                        <td class="pdf-op-col-specs">{{ $operation->resource_name ?: '' }}</td>
                        <td class="text-center pdf-op-col-time"></td>
                        <td class="text-center pdf-op-col-time"></td>
                        <td class="text-center pdf-op-col-time"></td>
                        <td class="text-center pdf-op-col-time"></td>
                        <td class="text-center pdf-op-col-time"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No operations registered.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2" class="summary-label">Total Times:</td>
                    <td class="text-center pdf-op-col-time"></td>
                    <td class="text-center pdf-op-col-time"></td>
                    <td class="text-center pdf-op-col-time"></td>
                    <td class="text-center pdf-op-col-time"></td>
                    <td class="text-center"><strong></strong></td>
                </tr>
                <tr>
                    <td colspan="6" class="summary-label" style="background:#fff;">Total Hours:</td>
                    <td class="text-center" style="background:#f1f5f9;"><strong></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <table>
            <tr>
                <td style="width:56%; padding:0; border:0;">
                    <table class="pdf-summary-table">
                        <tr>
                            <td class="summary-label" style="width:48%;">Total Labor:</td>
                            <td style="width:6%;" class="text-center">$</td>
                            <td class="text-right"></td>
                        </tr>
                        <tr>
                            <td class="summary-label">Total Materials:</td>
                            <td class="text-center">$</td>
                            <td class="text-right"></td>
                        </tr>
                        <tr>
                            <td class="summary-label">Total Outsource Process:</td>
                            <td class="text-center">$</td>
                            <td class="text-right"></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="notes-box">
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:44%; padding:0; border:0;">
                    <table class="pdf-summary-table">
                        <tr>
                            <td colspan="4" class="summary-title">Final Comparation</td>
                        </tr>
                        <tr>
                            <td class="summary-label" style="width:46%;">Sale Price:</td>
                            <td style="width:6%;" class="text-center">$</td>
                            <td colspan="2" class="text-right"></td>
                        </tr>
                        <tr>
                            <td class="summary-label">Cost:</td>
                            <td class="text-center">$</td>
                            <td colspan="2" class="text-right"></td>
                        </tr>
                        <tr>
                            <td class="summary-label">Cost Pcs:</td>
                            <td class="text-center">$</td>
                            <td colspan="2" class="text-right"></td>
                        </tr>
                        <tr>
                            <td class="summary-label">Difference:</td>
                            <td class="text-center">$</td>
                            <td colspan="2" class="text-right"></td>
                        </tr>
                        <tr>
                            <td class="summary-label">Result:</td>
                            <td class="text-center">$</td>
                            <td class="text-right"></td>
                            <td class="text-center">%</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
