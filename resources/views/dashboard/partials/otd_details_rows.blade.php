@forelse($rows as $r)
    @php
        $due = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->startOfDay() : null;
        $sent = $r->sent_at ? \Carbon\Carbon::parse($r->sent_at)->startOfDay() : null;
        $delta = ($due && $sent) ? $sent->diffInDays($due, false) : null; // negative => late
        $isOnTime = $delta !== null ? ($delta >= 0) : false;
        $monthEs = [1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic'];
        $fmtDate = function ($d) use ($monthEs) {
            if (!$d) return '';
            $m = (int) $d->format('n');
            return ($monthEs[$m] ?? strtolower($d->format('M'))) . '/' . $d->format('d/Y');
        };
    @endphp
    <tr class="{{ $isOnTime ? '' : 'table-danger' }}">
        <td class="text-left otd-col-workid">{{ $r->work_id }}</td>
        <td class="text-left otd-col-pn">{{ $r->PN }}</td>
        <td>{{ $r->Part_description }}</td>
        <td class="text-left otd-col-customer">{{ $r->costumer }}</td>
        <td class="text-center otd-col-due">{{ $fmtDate($due) }}</td>
        <td class="text-center otd-col-sent">{{ $fmtDate($sent) }}</td>
        <td class="text-center">
            @if($delta === null)
                <span class="otd-days-badge otd-days-badge--na">-</span>
            @elseif($isOnTime)
                <span class="otd-days-badge otd-days-badge--ontime">{{ $delta }}</span>
            @else
                <span class="otd-days-badge otd-days-badge--late">{{ $delta }}</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center text-muted py-3">No results.</td>
    </tr>
@endforelse
