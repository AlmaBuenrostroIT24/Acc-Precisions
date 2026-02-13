<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Costing</title>
    <style>
        @page { size: letter portrait; margin: 0; }
        html, body { width: 100%; height: 100%; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.05; color: #111; margin: 0; padding: 10pt; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 2pt 3pt; vertical-align: middle; }
        .no-bg { background: #fff; font-weight: 700; }
        .shade { background: #e9e9e9; font-weight: 700; text-align: center; }
        .center { text-align: center; }
        .left { text-align: left; }
        .small { font-size: 9pt; }

        .title-row td { border: none; padding: 0 0 6pt 0; font-weight: 700; font-size: 12pt; }

        .row-hdr td, .row-hdr th { height: 18pt; }
        .row-op td { height: 18pt; }
        .row-total td { height: 18pt; }
        .row-anal td { height: 18pt; }

        .diag { padding: 0; }
        .diag svg { display: block; width: 100%; height: 100%; }
    </style>
</head>

<body>
    @php
    $today = \Carbon\Carbon::now()->format('m/d/Y');
    $opCount = (int) preg_replace('/[^0-9]/', '', (string) ($order->operation ?? ''));
    if ($opCount <= 0) $opCount=5;
        $opCount=min($opCount, 30);
        $opRows = 15; // fixed grid to mimic template height
        @endphp

    <table>
        <colgroup>
            <col style="width: 16%">
            <col style="width: 24%">
            <col style="width: 12%">
            <col style="width: 8%">
            <col style="width: 8%">
            <col style="width: 8%">
            <col style="width: 8%">
            <col style="width: 8%">
            <col style="width: 8%">
        </colgroup>

        <tr class="title-row">
            <td colspan="9">COSTING</td>
        </tr>

        {{-- Header --}}
        <tr class="row-hdr">
            <td class="no-bg">CO Name</td>
            <td colspan="2">{{ $order->costumer ?? '' }}</td>
            <td class="no-bg center">Quote#/WO#</td>
            <td class="center">{{ $order->work_id ?? '' }}</td>
            <td class="no-bg center">WOQTY</td>
            <td class="center">{{ $order->wo_qty ?? '' }}</td>
            <td class="no-bg center">COQTY</td>
            <td class="center">{{ $order->qty ?? '' }}</td>
        </tr>
        <tr class="row-hdr">
            <td class="no-bg">PN</td>
            <td colspan="2">{{ $order->PN ?? '' }}</td>
            <td class="no-bg center">Rev</td>
            <td class="center">{{ $order->revision ?? '' }}</td>
            <td class="no-bg center" colspan="3">Family/Kit/Assy.</td>
            <td></td>
        </tr>
        <tr class="row-hdr">
            <td class="no-bg">Material:</td>
            <td colspan="2">{{ $order->material_type ?? '' }}</td>
            <td class="no-bg center">Type</td>
            <td colspan="2">{{ $order->machines ?? '' }}</td>
            <td class="no-bg center">Qty</td>
            <td class="center">{{ $order->qty ?? '' }}</td>
            <td class="no-bg center">Date: <span style="font-weight:400">{{ $today }}</span></td>
        </tr>

        {{-- Operation note --}}
        <tr>
            <td colspan="9" class="small left">
                Operation "Op". *Ensure sequence and appropriate OP description, "OUT-" for outside process
            </td>
        </tr>

        {{-- Column headers --}}
        <tr>
            <th class="no-bg left">Traveler Process</th>
            <th class="no-bg center">RESOURCE ID</th>
            <th class="no-bg center">PRG Est.</th>
            <th class="no-bg center">Actual</th>
            <th class="no-bg center">SetUp Est.</th>
            <th class="no-bg center">Actual</th>
            <th class="no-bg center">Runtime x Pcs</th>
            <th class="no-bg center">Runtime Total Est.</th>
            <th class="no-bg center">Actual</th>
        </tr>

        {{-- OP rows --}}
        @for ($i = 1; $i <= $opRows; $i++)
            <tr class="row-op">
                <td class="left">OP{{ $i }}</td>
                <td class="center">{{ $i === 1 ? ($order->machines ?? '') : '' }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor

        {{-- Totals block --}}
        <tr class="row-total">
            <td class="shade" colspan="3">Total Times</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="row-total">
            <td class="shade" colspan="3">Total Labor</td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="3" rowspan="4" style="vertical-align: top;">
                <div style="font-weight:700;">Top Notes</div>
            </td>
        </tr>
        <tr class="row-total">
            <td class="shade" colspan="3">Total Material</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="row-total">
            <td class="shade" colspan="3">Total Outside Process Cost</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="row-total">
            <td class="shade" colspan="3">Cost</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <tr class="row-anal">
            <td colspan="4"></td>
            <td class="shade small left">Analyzed<br>By</td>
            <td class="shade center" colspan="4">Date</td>
        </tr>
        <tr class="row-anal">
            <td colspan="4"></td>
            <td></td>
            <td colspan="4"></td>
        </tr>
    </table>
</body>

</html>
