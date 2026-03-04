<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard KPIs</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }

        @page { margin: 86px 24px 54px 24px; }

        header.pdf-header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 56px;
        }

        footer.pdf-footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 28px;
            color: #64748b;
            font-size: 9px;
        }

        .header-shell {
            width: 100%;
            border-collapse: collapse;
        }
        .header-shell td { vertical-align: middle; padding: 0; }

        .header-left { width: 104px; padding-right: 2px; }
        .header-mid { text-align: left; }
        .header-right { width: 180px; text-align: right; }

        .logo {
            height: 52px;
            width: auto;
            display: inline-block;
        }

        .hdr-title {
            font-size: 16px;
            font-weight: 900;
            color: #1f3a66;
            margin: 0;
            line-height: 1.1;
        }

        .hdr-sub {
            font-size: 9px;
            font-weight: 800;
            color: #475569;
            margin: 2px 0 0;
            line-height: 1.1;
        }

        .hdr-meta {
            font-size: 9px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.15;
        }
        .hdr-meta .k { color: #64748b; font-weight: 900; text-transform: uppercase; letter-spacing: .35px; font-size: 8px; }

        .footer-line {
            width: 100%;
            border-top: 1px solid #d9e2ef;
            padding-top: 6px;
        }

        .report-title {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: .2px;
            margin: 0 0 10px;
            color: #1f3a66;
        }

        .section-bar {
            background: #2b4f86;
            color: #ffffff;
            font-weight: 900;
            padding: 8px 10px;
            border-radius: 3px;
            margin: 0 0 10px;
            text-transform: uppercase;
            letter-spacing: .6px;
            font-size: 11px;
        }

        .muted { color: #64748b; font-weight: 700; }
        .notes-text { color: #0f172a; font-weight: 400; line-height: 1.25; }

        table.meta {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 10px;
        }
        table.meta td {
            padding: 6px 8px;
            border: 1px solid #d9e2ef;
            background: #f7fafc;
            vertical-align: top;
        }
        table.meta td.k {
            width: 90px;
            font-weight: 900;
            background: #edf2f7;
            text-transform: uppercase;
            letter-spacing: .4px;
            font-size: 9px;
        }
        table.meta td.v { font-weight: 700; }

        table.kpi {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #d1d9e6;
        }
        table.kpi th, table.kpi td {
            border: 1px solid #d1d9e6;
            padding: 4px 4px;
            vertical-align: middle;
        }
        table.kpi thead th {
            background: #eef3f9;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 9px;
            color: #0f172a;
        }
        table.kpi tbody td { height: 32px; }
        table.kpi tbody tr { page-break-inside: avoid; }
        table.kpi tbody tr:nth-child(even) td { background: #f8fafc; }
        table.kpi th.col-month { background: #ffffff; }
        table.kpi td.col-month { background: #ffffff; }
        table.kpi tbody tr:nth-child(even) td.col-month { background: #ffffff; }

        /* Dompdf can ignore px widths; percentages tend to be more reliable */
        th.col-type, td.col-type { width: 6% !important; }
        th.col-prcs, td.col-prcs { width: 6% !important; }
        th.col-name, td.col-name { width: 20% !important; }
        th.col-month, td.col-month { width: 5% !important; }
        th.col-ytd, td.col-ytd { width: 7% !important; }
        th.col-r12, td.col-r12 { width: 7% !important; }
        th.col-goal, td.col-goal { width: 8% !important; }
        th.col-trend, td.col-trend { width: 14% !important; }

        .col-type { text-align: center; font-weight: 900; background: #edf2f7; }
        .col-prcs { text-align: center; font-weight: 800; background: #edf2f7; }
        .col-name { background: #f1f5fb; white-space: normal; word-wrap: break-word; overflow-wrap: anywhere; line-height: 1.15; font-weight: 800; }
        td.col-name { font-size: 9px; font-weight: 700; line-height: 1.2; }

        .col-month, .col-ytd, .col-r12, .col-goal, .col-trend { text-align: center; }
        .col-ytd, .col-r12, .col-goal, .col-trend { background: #f7fafc; font-weight: 800; }
        .col-month { font-size: 9px; }
        .cell-total { display: block; font-size: 8px; color: #64748b; font-weight: 700; line-height: 1.1; }
    </style>
</head>
<body>
@php
    $logoPath = public_path(config('adminlte.logo_img', ''));
    $logoDataUri = null;
    if ($logoPath && is_file($logoPath)) {
        $mime = function_exists('mime_content_type') ? mime_content_type($logoPath) : 'image/png';
        $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    $asOfFmt = \Carbon\Carbon::parse($dashboardEndDate)->format('F/d/Y');

    $yearShort = substr((string) $dashboardYear, -2);
    $quarters = [
        ['label' => "Q1 {$yearShort}", 'months' => [1, 2, 3]],
        ['label' => "Q2 {$yearShort}", 'months' => [4, 5, 6]],
        ['label' => "Q3 {$yearShort}", 'months' => [7, 8, 9]],
        ['label' => "Q4 {$yearShort}", 'months' => [10, 11, 12]],
    ];
    $months = range(1, 12);
    $rows = $kpiRows ?? [];

    $wrapTwoLines = function (?string $text, int $max1 = 48, int $max2 = 48): string {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/\\s+/u', ' ', $text);
        $words = preg_split('/\\s+/u', $text) ?: [];

        $line1 = '';
        $line2 = '';

        foreach ($words as $word) {
            $candidate = trim(($line1 !== '' ? $line1 . ' ' : '') . $word);
            if (mb_strlen($candidate, 'UTF-8') <= $max1) {
                $line1 = $candidate;
                continue;
            }

            if ($line1 === '') {
                $line1 = mb_substr($candidate, 0, $max1, 'UTF-8');
            }

            $candidate2 = trim(($line2 !== '' ? $line2 . ' ' : '') . $word);
            if (mb_strlen($candidate2, 'UTF-8') <= $max2) {
                $line2 = $candidate2;
                continue;
            }

            $line2 = mb_substr($candidate2, 0, max(0, $max2 - 1), 'UTF-8') . '…';
            break;
        }

        if ($line2 === '') {
            return e($line1);
        }

        return e($line1) . '<br>' . e($line2);
    };
@endphp

<header class="pdf-header">
    <table class="header-shell">
        <tr>
            <td class="header-left">
                @if($logoDataUri)
                    <img class="logo" src="{{ $logoDataUri }}" alt="Logo">
                @endif
            </td>
            <td class="header-mid">
                <div class="hdr-title">Quality Objectives &amp; Key Performance Indicators (KPIs)</div>
                <div class="hdr-sub">ACC Precision, Inc.</div>
            </td>
        </tr>
    </table>
</header>

<footer class="pdf-footer">
    <div class="footer-line">
        <span style="margin-left:10px;" class="muted">F-620-001 Rev. B LA Authorized</span>
    </div>
</footer>

<table class="meta">
    <tr>
        <td class="k">Year</td>
        <td class="v">{{ (int) $dashboardYear }}</td>
        <td class="k">As of</td>
        <td class="v">{{ $asOfFmt }}</td>
    </tr>
    <tr>
        <td class="k">Notes</td>
        <td class="v" colspan="3">
            <span class="notes-text">
                To achieve the Quality Policy, the following QOs and KPIs are set forth by ACC Precision, Inc. and measured/analyzed/evaluated; they may be updated as needed.
            </span>
        </td>
    </tr>
</table>

<div class="section-bar">Report</div>

<table class="kpi">
    <colgroup>
        <col style="width:4%">
        <col style="width:4%">
        <col style="width:30%">
        @foreach($months as $m)
            <col style="width:2%">
        @endforeach
        <col style="width:7%">
        <col style="width:7%">
        <col style="width:10%">
        <col style="width:14%">
    </colgroup>
    <thead>
        <tr>
            <th class="col-type" rowspan="2">Type</th>
            <th class="col-prcs" rowspan="2">Prcs.</th>
            <th class="col-name" rowspan="2">Name</th>
            @foreach($quarters as $q)
                <th colspan="{{ count($q['months']) }}">{{ $q['label'] }}</th>
            @endforeach
            <th class="col-ytd" rowspan="2">YTD</th>
            <th class="col-r12" rowspan="2">Rolling 12M</th>
            <th class="col-goal" rowspan="2">Goal/Per Term</th>
            <th class="col-trend" rowspan="2">Trend / NC Doc Ref.</th>
        </tr>
        <tr>
            @foreach($months as $m)
                <th class="col-month">{{ $m }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            @php
                $isOtd = ($row['key'] ?? '') === 'customer_otd';
                $values = $row['values'] ?? [];
                $ytdPct = $isOtd ? ($otdYtd['pct'] ?? null) : null;
                $r12Pct = $isOtd ? ($otdR12['pct'] ?? null) : null;
            @endphp
            <tr>
                <td class="col-type">{{ $row['type'] ?? '' }}</td>
                <td class="col-prcs">{{ $row['prcs'] ?? '' }}</td>
                <td class="col-name">{!! $wrapTwoLines($row['name'] ?? '') !!}</td>
                @foreach($months as $m)
                    @php
                        $cell = $values[$m] ?? null;
                        $pct = $isOtd && is_array($cell) ? ($cell['pct'] ?? null) : null;
                        $total = $isOtd && is_array($cell) ? (int) ($cell['total'] ?? 0) : 0;
                    @endphp
                    <td class="col-month">
                        @if($isOtd)
                            @if($pct !== null)
                                {{ number_format($pct, 1) . '%' }}
                            @endif
                            @if($total)
                                <span class="cell-total">({{ $total }})</span>
                            @endif
                        @else
                            {{ is_array($cell) ? '' : ($cell ?? '') }}
                        @endif
                    </td>
                @endforeach
                <td class="col-ytd">{{ $ytdPct !== null ? number_format($ytdPct, 1) . '%' : '' }}@if($isOtd && !empty($otdYtd['total'])) ({{ (int) $otdYtd['total'] }})@endif</td>
                <td class="col-r12">{{ $r12Pct !== null ? number_format($r12Pct, 1) . '%' : '' }}@if($isOtd && !empty($otdR12['total'])) ({{ (int) $otdR12['total'] }})@endif</td>
                <td class="col-goal">{{ $row['goal'] ?? '' }}</td>
                <td class="col-trend">{{ $row['trend'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<script type="text/php">
if (isset($pdf)) {
    $pdf->page_script('
        $font = $fontMetrics->get_font("Helvetica", "normal");
        $pdf->text(720, 570, "Page $PAGE_NUM of $PAGE_COUNT", $font, 9, array(100/255, 116/255, 139/255));
    ');
}
</script>
</body>
</html>
