@forelse($rows as $r)
    @php
        $due = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->startOfDay() : null;
        $sent = $r->sent_at ? \Carbon\Carbon::parse($r->sent_at)->startOfDay() : null;
        $delta = ($due && $sent) ? $sent->diffInDays($due, false) : null; // negative => late
        $isOnTime = $delta !== null ? ($delta >= 0) : false;
    @endphp
    <tr class="{{ $isOnTime ? '' : 'table-danger' }}">
        <td class="text-center">{{ $r->work_id }}</td>
        <td class="text-center">{{ $r->PN }}</td>
        <td>{{ $r->Part_description }}</td>
        <td class="text-center">{{ $r->costumer }}</td>
        <td class="text-center">{{ $due ? $due->format('Y-m-d') : '' }}</td>
        <td class="text-center">{{ $sent ? $sent->format('Y-m-d') : '' }}</td>
        <td class="text-center">
            @if($delta === null)
                -
            @elseif($isOnTime)
                <span class="text-success font-weight-bold">{{ $delta }}</span>
            @else
                <span class="text-danger font-weight-bold">{{ $delta }}</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center text-muted py-3">No results.</td>
    </tr>
@endforelse

