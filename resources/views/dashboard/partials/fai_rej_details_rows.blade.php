@forelse($rows as $r)
    @php
        $due = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->startOfDay() : null;
        $sent = $r->sent_at ? \Carbon\Carbon::parse($r->sent_at)->startOfDay() : null;
        $failOps = (int) ($r->fail_ops ?? 0);
        $firstOp = trim((string) ($r->first_operation ?? ''));
    @endphp
    <tr class="table-warning">
        <td class="text-center">{{ $r->work_id }}</td>
        <td class="text-center">{{ $r->PN }}</td>
        <td>{{ $r->Part_description }}</td>
        <td class="text-center">{{ $r->costumer }}</td>
        <td class="text-center">{{ $due ? $due->format('Y-m-d') : '' }}</td>
        <td class="text-center">{{ $sent ? $sent->format('Y-m-d') : '' }}</td>
        <td class="text-center">
            @if($failOps > 0)
                <span class="font-weight-bold">{{ $failOps }}</span>
                @if($firstOp !== '')
                    <div class="text-muted small">Op: {{ $firstOp }}</div>
                @endif
            @else
                -
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center text-muted py-3">No results.</td>
    </tr>
@endforelse

