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

        /* Keep class widths aligned with <colgroup> so Dompdf doesn't collapse TOT columns */
        th.col-type, td.col-type { width: 5% !important; }
        th.col-prcs, td.col-prcs { width: 4% !important; }
        th.col-name, td.col-name { width: 14% !important; }
        th.col-month, td.col-month { width: 4% !important; }
        th.col-qtotal, td.col-qtotal { width: 4% !important; }
        th.col-ytd, td.col-ytd { width: 4% !important; }
        th.col-r12, td.col-r12 { width: 5% !important; }
        th.col-goal, td.col-goal { width: 6% !important; }
        th.col-trend, td.col-trend { width: 5% !important; }

        .col-type { text-align: center; font-weight: 900; background: #f1f5f9; }
        .col-prcs { text-align: center; font-weight: 800; background: #f1f5f9; }
        .col-name { background: #f1f5f9; white-space: normal; word-wrap: break-word; overflow-wrap: anywhere; line-height: 1.15; font-weight: 800; }
        table.kpi tbody td.col-type,
        table.kpi tbody td.col-prcs,
        table.kpi tbody td.col-name { background: #f1f5f9 !important; }
        td.col-name { font-size: 9px; font-weight: 700; line-height: 1.2; }

        /* Type pill (QO / KPI) */
        .type-pill{
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: .4px;
            border: 1px solid #cbd5e1;
            background: #eef2f7;
            color: #0f172a;
        }
        .type-pill--kpi{
            border-color: #93c5fd;
            background: #dbeafe;
            color: #1e40af;
        }

        .col-month, .col-ytd, .col-r12, .col-goal, .col-trend { text-align: center; }
        .col-ytd, .col-r12, .col-trend { background: #f7fafc; font-weight: 800; }
        .col-goal { background: #fff7d6 !important; font-weight: 900; }
        .col-month { font-size: 9px; }
        .col-qtotal { background-color: #eef2ff; font-weight: 900; text-align: center; }
        thead th.col-qtotal { background-color: #dbeafe; letter-spacing: 0.10em; color: #1e40af; }
        .col-qtotal.qtotal-empty { background-color: #f1f5f9 !important; color: #94a3b8; font-weight: 800; }
        table.kpi th.col-qtotal, table.kpi td.col-qtotal {
            border-right: 2px solid #93c5fd !important;
            /* Reinforce divider without pseudo-elements (Dompdf can mis-position ::after on table cells) */
            box-shadow: inset -2px 0 0 #93c5fd;
        }
        /* Extend the quarter divider "above" TOT across the quarter band row */
        table.kpi thead tr.qhdr th.qhdr-sep {
            border-right: 2px solid #93c5fd !important;
            box-shadow: inset -2px 0 0 #93c5fd;
        }
        /* Let the Q4 quarter divider (blue) win in the header; keep a normal divider in the body */
        table.kpi thead th.col-ytd { border-left: 0 !important; }
        table.kpi tbody td.col-ytd { border-left: 1px solid #d1d9e6 !important; }
        .cell-total { display: block; font-size: 8px; color: #64748b; font-weight: 700; line-height: 1.1; }

        /* KPI validation colors (match dashboard: green/yellow/red) */
        .tone-good { background: #dcfce7 !important; }
        .tone-warn { background: #fef3c7 !important; }
        .tone-bad  { background: #fee2e2 !important; }
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

    $toneClass = function ($pct): string {
        if ($pct === null) {
            return '';
        }
        $pct = (float) $pct;
        if ($pct >= 90.0) {
            return 'tone-good';
        }
        if ($pct >= 85.0) {
            return 'tone-warn';
        }
        return 'tone-bad';
    };

    $toneClassLower = function ($pct, float $goal = 15.0): string {
        if ($pct === null) {
            return '';
        }
        $pct = (float) $pct;
        if ($pct <= $goal) {
            return 'tone-good';
        }
        if ($pct <= ($goal + 3.0)) {
            return 'tone-warn';
        }
        return 'tone-bad';
    };

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
        <col style="width:3%">
        <col style="width:3%">
        <col style="width:12%">
        @foreach($months as $m)
            <col style="width:4%">
            @if(in_array($m, [3, 6, 9, 12], true))
                <col style="width:4%">
            @endif
        @endforeach
        <col style="width:4%">
        <col style="width:4%">
        <col style="width:5%">
        <col style="width:5%">
    </colgroup>
    <thead>
        <tr class="qhdr">
            <th class="col-type" rowspan="2">Type</th>
            <th class="col-prcs" rowspan="2">Prcs.</th>
            <th class="col-name" rowspan="2">Name</th>
            @foreach($quarters as $q)
                <th colspan="{{ count($q['months']) + 1 }}" class="qhdr-sep">{{ $q['label'] }}</th>
            @endforeach
            <th class="col-ytd" rowspan="2">YTD</th>
            <th class="col-r12" rowspan="2">Rolling 12M</th>
            <th class="col-goal" rowspan="2">Goal/Per Term</th>
            <th class="col-trend" rowspan="2">Trend / NC Doc Ref.</th>
        </tr>
        <tr>
            @foreach($months as $m)
                <th class="col-month">{{ $m }}</th>
                @if(in_array($m, [3, 6, 9, 12], true))
                    <th class="col-qtotal {{ $m === 12 ? 'qtotal-last' : '' }}">TOT</th>
                @endif
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            @php
                $isOtd = ($row['key'] ?? '') === 'customer_otd';
                $isFaiRej = ($row['key'] ?? '') === 'fai_rej';
                $values = $row['values'] ?? [];
                $ytdPct = $isOtd ? ($otdYtd['pct'] ?? null) : null;
                $r12Pct = $isOtd ? ($otdR12['pct'] ?? null) : null;

                $quarterTotals = [1 => ['on_time' => 0, 'total' => 0], 2 => ['on_time' => 0, 'total' => 0], 3 => ['on_time' => 0, 'total' => 0], 4 => ['on_time' => 0, 'total' => 0]];
                if ($isOtd) {
                    foreach (range(1, 12) as $mm) {
                        $c = $values[$mm] ?? null;
                        if (!is_array($c) || empty($c['total'])) {
                            continue;
                        }
                        $qi = intdiv($mm - 1, 3) + 1;
                        $quarterTotals[$qi]['on_time'] += (int) ($c['on_time'] ?? 0);
                        $quarterTotals[$qi]['total'] += (int) ($c['total'] ?? 0);
                    }
                    foreach ([1, 2, 3, 4] as $qi) {
                        $t = (int) ($quarterTotals[$qi]['total'] ?? 0);
                        $o = (int) ($quarterTotals[$qi]['on_time'] ?? 0);
                        $quarterTotals[$qi]['pct'] = $t > 0 ? round(($o / $t) * 100, 1) : null;
                    }
                } elseif ($isFaiRej) {
                    $quarterTotals = [1 => ['rejects' => 0, 'total' => 0], 2 => ['rejects' => 0, 'total' => 0], 3 => ['rejects' => 0, 'total' => 0], 4 => ['rejects' => 0, 'total' => 0]];
                    foreach (range(1, 12) as $mm) {
                        $c = $values[$mm] ?? null;
                        if (!is_array($c) || empty($c['total'])) {
                            continue;
                        }
                        $qi = intdiv($mm - 1, 3) + 1;
                        $quarterTotals[$qi]['rejects'] += (int) ($c['rejects'] ?? 0);
                        $quarterTotals[$qi]['total'] += (int) ($c['total'] ?? 0);
                    }
                    foreach ([1, 2, 3, 4] as $qi) {
                        $t = (int) ($quarterTotals[$qi]['total'] ?? 0);
                        $r = (int) ($quarterTotals[$qi]['rejects'] ?? 0);
                        $quarterTotals[$qi]['pct'] = $t > 0 ? round(($r / $t) * 100, 1) : null;
                    }
                }
            @endphp
            <tr>
                <td class="col-type">
                    @php $t = strtoupper(trim((string) ($row['type'] ?? ''))); @endphp
                    <span class="type-pill {{ $t === 'KPI' ? 'type-pill--kpi' : '' }}">{{ $t }}</span>
                </td>
                <td class="col-prcs">{{ $row['prcs'] ?? '' }}</td>
                <td class="col-name">{!! $wrapTwoLines($row['name'] ?? '') !!}</td>
                @foreach($months as $m)
                    @php
                        $cell = $values[$m] ?? null;
                        $pct = $isOtd && is_array($cell) ? ($cell['pct'] ?? null) : null;
                        $total = $isOtd && is_array($cell) ? (int) ($cell['total'] ?? 0) : 0;
                        $faiPct = $isFaiRej && is_array($cell) ? ($cell['pct'] ?? null) : null;
                        $faiTotal = $isFaiRej && is_array($cell) ? (int) ($cell['total'] ?? 0) : 0;
                        $faiRejects = $isFaiRej && is_array($cell) ? (int) ($cell['rejects'] ?? 0) : 0;
                    @endphp
                    <td class="col-month {{ $isOtd ? $toneClass($pct) : '' }} {{ $isFaiRej ? $toneClassLower($faiPct, 15.0) : '' }}">
                        @if($isOtd)
                            @if($pct !== null)
                                {{ number_format($pct, 1) . '%' }}
                            @endif
                            @if($total)
                                <span class="cell-total">({{ $total }})</span>
                            @endif
                        @elseif($isFaiRej)
                            @if($faiPct !== null)
                                {{ number_format((float) $faiPct, 1) . '%' }}
                            @endif
                            @if($faiTotal)
                                <span class="cell-total">({{ $faiRejects }}/{{ $faiTotal }})</span>
                            @endif
                        @else
                            {{ is_array($cell) ? '' : ($cell ?? '') }}
                        @endif
                    </td>
                    @if(in_array($m, [3, 6, 9, 12], true))
                        @if($isOtd)
                            @php
                                $qi = intdiv($m - 1, 3) + 1;
                                $qt = $quarterTotals[$qi] ?? ['pct' => null, 'total' => 0];
                                $qtEmpty = ($qt['pct'] ?? null) === null && empty($qt['total']);
                            @endphp
                            <td class="col-qtotal {{ $toneClass($qt['pct'] ?? null) }} {{ $qtEmpty ? 'qtotal-empty' : '' }} {{ $m === 12 ? 'qtotal-last' : '' }}">
                                @if(($qt['pct'] ?? null) !== null)
                                    {{ number_format((float) $qt['pct'], 1) . '%' }}
                                @endif
                                @if(!empty($qt['total']))
                                    <span class="cell-total">({{ (int) $qt['total'] }})</span>
                                @endif
                            </td>
                        @elseif($isFaiRej)
                            @php
                                $qi = intdiv($m - 1, 3) + 1;
                                $qt = $quarterTotals[$qi] ?? ['pct' => null, 'rejects' => 0, 'total' => 0];
                                $qtEmpty = ($qt['pct'] ?? null) === null && empty($qt['total']);
                            @endphp
                            <td class="col-qtotal {{ $toneClassLower($qt['pct'] ?? null, 15.0) }} {{ $qtEmpty ? 'qtotal-empty' : '' }} {{ $m === 12 ? 'qtotal-last' : '' }}">
                                @if(($qt['pct'] ?? null) !== null)
                                    {{ number_format((float) $qt['pct'], 1) . '%' }}
                                @endif
                                @if(!empty($qt['total']))
                                    <span class="cell-total">({{ (int) ($qt['rejects'] ?? 0) }}/{{ (int) $qt['total'] }})</span>
                                @endif
                            </td>
                        @else
                            <td class="col-qtotal qtotal-empty {{ $m === 12 ? 'qtotal-last' : '' }}"></td>
                        @endif
                    @endif
                @endforeach
                <td class="col-ytd {{ $isOtd ? $toneClass($ytdPct) : '' }}">{{ $ytdPct !== null ? number_format($ytdPct, 1) . '%' : '' }}@if($isOtd && !empty($otdYtd['total'])) ({{ (int) $otdYtd['total'] }})@endif</td>
                <td class="col-r12 {{ $isOtd ? $toneClass($r12Pct) : '' }}">{{ $r12Pct !== null ? number_format($r12Pct, 1) . '%' : '' }}@if($isOtd && !empty($otdR12['total'])) ({{ (int) $otdR12['total'] }})@endif</td>
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
