@forelse($rows as $i => $r)
    @php
        $due = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->startOfDay() : null;
        $sent = $r->sent_at ? \Carbon\Carbon::parse($r->sent_at)->startOfDay() : null;
        $failOps = (int) ($r->fail_ops ?? 0);
        $failOperations = trim((string) ($r->fail_operations ?? ''));
        $monthEs = [1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic'];
        $fmtDate = function ($d) use ($monthEs) {
            if (!$d) return '';
            $m = (int) $d->format('n');
            return ($monthEs[$m] ?? strtolower($d->format('M'))) . '/' . $d->format('d/Y');
        };
    @endphp
    <tr class="table-warning">
        <td class="text-center fai-col-idx">{{ $i + 1 }}</td>
        <td class="text-left fai-col-workid">{{ $r->work_id }}</td>
        <td class="text-left fai-col-pn">{{ $r->PN }}</td>
        <td class="text-left fai-col-custpo">{{ $r->cust_po }}</td>
        <td class="text-left fai-col-co">{{ $r->co }}</td>
        <td class="fai-col-desc">{{ $r->Part_description }}</td>
        <td class="text-left fai-col-customer">{{ $r->costumer }}</td>
        <td class="text-center fai-col-due">{{ $fmtDate($due) }}</td>
        <td class="text-center fai-col-sent">{{ $fmtDate($sent) }}</td>
        <td class="text-left fai-col-failops">
            @if($failOps > 0)
                <span class="font-weight-bold">{{ $failOps }}</span>
                @if($failOperations !== '')
                    <span class="text-muted small ml-1 fai-failops-inline">Op: {{ $failOperations }}</span>
                @endif
            @else
                -
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center text-muted py-3">No results.</td>
    </tr>
@endforelse

