@forelse ($ordenesSemana as $order)
<tr>

    <td>{{ $order->work_id }}</td>
    <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">{{ $order->PN }}</td>
    <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
        {{ $order->Part_description }}
    </td>
    <td>{{ ucfirst($order->costumer) }}</td>
    <td>{{ $order->qty }}</td>
    <td><span class="badge bg-info text-dark">{{ $order->status }}</span></td>
    @php $statusText = strtolower(trim($order->status ?? '')); @endphp
    <td>
        @if($order->due_date)
        <span class="text-primary fw-semibold">
            {{ \Carbon\Carbon::parse($order->due_date)->format('M/d/Y') }}
        </span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        @if(!empty($order->sent_at))
        <span class="text-muted fw-semibold">
            {{ \Carbon\Carbon::parse($order->sent_at)->format('M/d/Y') }}
        </span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-center">
        @if($statusText === 'sent')
        <i class="fas fa-check text-success" title="Sent"></i>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        @if($order->due_date)
        @php
        $due = \Carbon\Carbon::parse($order->due_date)->startOfDay();
        $isSent = ($statusText === 'sent');
        $baseDate = \Carbon\Carbon::now()->startOfDay();
        if ($isSent && !empty($order->sent_at)) {
            $baseDate = \Carbon\Carbon::parse($order->sent_at)->startOfDay();
        }
        $daysDiff = $baseDate->diffInDays($due, false);
        @endphp
        @if($isSent)
        <span class="badge {{ $daysDiff >= 0 ? 'bg-success' : 'bg-danger' }}">
            {{ abs($daysDiff) }}
        </span>
        @else
        <span class="badge {{ $daysDiff >= 0 ? 'bg-warning text-dark' : 'bg-danger' }}">
            {{ abs($daysDiff) }}
        </span>
        @endif
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
</tr>
@empty
{{-- DataTables: dejar tbody vacío para evitar warning tn/4 con colspan --}}
@endforelse
