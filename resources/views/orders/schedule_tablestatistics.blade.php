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
    <td>
        @if($order->due_date)
        <span class="text-primary fw-semibold">
            {{ \Carbon\Carbon::parse($order->due_date)->format('M/d/Y') }}
        </span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center text-muted py-3">No orders found.</td>
</tr>
@endforelse
