<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>FAI / IPI Completed Report</title>
    <style>
        @page {
            margin: 20px 20px 30px 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 8px;
            padding-bottom: 6px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo {
            width: 110px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }

        .meta {
            text-align: right;
            font-size: 10px;
            line-height: 1.2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 4px 6px;
            text-align: center;
        }

        th {
            background: #f0f0f0;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    {{-- ===== CABECERA ===== --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 20%;">
                    @if(!empty($logoPath) && file_exists($logoPath))
                    <img src="{{ $logoPath }}" class="logo" alt="Logo">
                    @endif
                </td>
                <td class="title" style="width: 50%;">FAI / IPI Completed Report</td>
                <td class="meta" style="width: 25%;">
                    <strong>Date:</strong> {{ $generated_at }}<br>
                    <strong>User:</strong> {{ $user }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== TABLA ===== --}}
    <table>
        <thead>
            <tr>
                <th style="width: 80px;">DATE</th>
                <th style="width: 40px;">LOC.</th>
                <th style="width: 70px;">WORK ID</th>
                <th style="width: 90px;">PN</th>
                <th style="width: 220px;">DESCRIPTION</th>
                <th style="width: 50px;">SAMP. PLAN</th>
                <th style="width: 50px;">WO QTY</th>
                <th style="width: 60px;">SAMP.</th>
                <th style="width: 40px;">OPS.</th>
                <th style="width: 40px;">FAI</th>
                <th style="width: 40px;">IPI</th>
                <th style="width: 40px;">%</th>
            </tr>
        </thead>

        <tbody>
            @foreach($rows as $o)
              @php
            $faiReq = (int) ($o->total_fai ?? 0);
            $ipiReq = (int) ($o->total_ipi ?? 0);
            $faiOk  = (int) ($o->fai_pass_qty ?? 0);
            $ipiOk  = (int) ($o->ipi_pass_qty ?? 0);
            $faiPct = $faiReq > 0 ? (int) round($faiOk * 100 / $faiReq) : 100;
            $ipiPct = $ipiReq > 0 ? (int) round($ipiOk * 100 / $ipiReq) : 100;
                // ===== Overall PONDERADO =====
            $totalReq  = $faiReq + $ipiReq;
            $totalOk   = $faiOk  + $ipiOk;
            $overall = $totalReq > 0 ? (int) round(($totalOk / $totalReq) * 100) : 100;
            $overallDecimal = $overall / 100;  // Excel espera 0..1 para %
            $dateStr = $o->inspection_endate ? \Carbon\Carbon::parse($o->inspection_endate)->format('Y-m-d') : '';
        @endphp
            <tr>
                <td>{{ optional($o->inspection_endate)->format('Y-m-d') }}</td>
                <td>{{ $o->location }}</td>
                <td>{{ $o->work_id }}</td>
                <td>{{ $o->PN }}</td>
                <td>{{ $o->Part_description }}</td>
                <td class="text-center">{{ $o->sampling }}</td>
                <td class="text-center">{{ $o->wo_qty }}</td>
                <td>{{ $o->sampling_check }}</td>
                <td class="text-center">{{ $o->operation }}</td>
                <td>{{ $faiOk }}/{{ $faiReq }}</td>
                <td>{{ $ipiOk }}/{{ $ipiReq }}</td>
                <td class="text-center">{{ $overall }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>


    {{-- (Opcional) Pie con numeración de página --}}
    <script type="text/php">
        if (isset($pdf)) {
            $x = $pdf->get_width() - 100;
            $y = $pdf->get_height() - 24;
            $pdf->page_text($x, $y, "Page {PAGE_NUM} / {PAGE_COUNT}", null, 8, [0,0,0]);
        }
    </script>
</body>

</html>