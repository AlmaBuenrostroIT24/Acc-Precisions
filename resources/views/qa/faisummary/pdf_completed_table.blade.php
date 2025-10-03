<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>FAI Completed</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 4px;
        }

        th {
            background: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h3 style="text-align:center;">FAI/IPI Completed</h3>
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
            $faiReqPcs = (int) ($o->total_fai ?? 0);
            $ipiReqPcs = (int) ($o->total_ipi ?? 0);
            $faiPassQty = (int) ($o->fai_pass_qty ?? 0);
            $ipiPassQty = (int) ($o->ipi_pass_qty ?? 0);
            $faiPct = $faiReqPcs > 0 ? round($faiPassQty / $faiReqPcs * 100) : 100;
            $ipiPct = $ipiReqPcs > 0 ? round($ipiPassQty / $ipiReqPcs * 100) : 100;
            $overall = min($faiPct, $ipiPct);
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
                <td class="text-center">{{ $faiPassQty }}/{{ $faiReqPcs }}</td>
                <td class="text-center">{{ $ipiPassQty }}/{{ $ipiReqPcs }}</td>
                <td class="text-center">{{ $overall }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>