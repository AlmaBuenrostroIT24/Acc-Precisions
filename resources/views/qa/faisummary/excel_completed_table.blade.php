<table>
    <thead>
        {{-- Fila 1 reservada para logo (A1) y título (B1:L1) que ponemos desde styles() --}}
        <tr>
            {{-- 12 celdas vacías para alinear con A..L (¡importante que sean 12!) --}}
            <th></th><th></th><th></th><th></th><th></th><th></th>
            <th></th><th></th><th></th><th></th><th></th><th></th>
        </tr>

        {{-- Fila 2: aquí sí van los encabezados reales --}}
        <tr>
            <th>DATE</th>
            <th>LOC.</th>
            <th>WORK ID</th>
            <th>PN</th>
            <th>DESCRIPTION</th>
            <th>SAMP. PLAN</th>
            <th>WO QTY</th>
            <th>SAMP.</th>
            <th>OPS.</th>
            <th>FAI</th>
            <th>IPI</th>
            <th>PROG.</th>
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
            <td>{{ $dateStr }}</td>
            <td>{{ $o->location }}</td>
            <td>{{ $o->work_id }}</td>
            <td>{{ $o->PN }}</td>
            <td>{{ $o->Part_description }}</td>
            <td>{{ $o->sampling }}</td>
            <td>{{ $o->wo_qty }}</td>
            <td>{{ $o->sampling_check }}</td>
            <td>{{ $o->operation }}</td>
            <td>{{ $faiOk }}/{{ $faiReq }}</td>
            <td>{{ $ipiOk }}/{{ $ipiReq }}</td>
            <td>{{ $overallDecimal }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
