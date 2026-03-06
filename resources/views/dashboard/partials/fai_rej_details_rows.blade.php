@forelse($rows as $r)
    @php
        $due = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->startOfDay() : null;
        $sent = $r->sent_at ? \Carbon\Carbon::parse($r->sent_at)->startOfDay() : null;
        $failOps = (int) ($r->fail_ops ?? 0);
        $failOperations = trim((string) ($r->fail_operations ?? ''));
    @endphp
    <tr class="table-warning">
        <td class="text-left fai-col-workid">{{ $r->work_id }}</td>
        <td class="text-left fai-col-pn">{{ $r->PN }}</td>
        <td class="fai-col-desc">{{ $r->Part_description }}</td>
        <td class="text-left fai-col-customer">{{ $r->costumer }}</td>
        <td class="text-center fai-col-due">{{ $due ? $due->format('Y-m-d') : '' }}</td>
        <td class="text-center fai-col-sent">{{ $sent ? $sent->format('Y-m-d') : '' }}</td>
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
        <td colspan="7" class="text-center text-muted py-3">No results.</td>
    </tr>
@endforelse
