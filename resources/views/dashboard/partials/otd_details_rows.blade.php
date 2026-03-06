@forelse($rows as $r)
    @php
        $due = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->startOfDay() : null;
        $sent = $r->sent_at ? \Carbon\Carbon::parse($r->sent_at)->startOfDay() : null;
        $delta = ($due && $sent) ? $sent->diffInDays($due, false) : null; // negative => late
        $isOnTime = $delta !== null ? ($delta >= 0) : false;
    @endphp
    <tr class="{{ $isOnTime ? '' : 'table-danger' }}">
        <td class="text-left otd-col-workid">{{ $r->work_id }}</td>
        <td class="text-left otd-col-pn">{{ $r->PN }}</td>
        <td>{{ $r->Part_description }}</td>
        <td class="text-left otd-col-customer">{{ $r->costumer }}</td>
        <td class="text-center otd-col-due">{{ $due ? $due->format('Y-m-d') : '' }}</td>
        <td class="text-center otd-col-sent">{{ $sent ? $sent->format('Y-m-d') : '' }}</td>
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
