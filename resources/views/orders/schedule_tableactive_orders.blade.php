@forelse ($orders as $order)
<tr>
    <td>{{ $order->work_id }}</td>
    <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word; overflow-wrap:anywhere;">
        {{ $order->PN }}
    </td>
    <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
        {{ $order->Part_description }}
    </td>
    <td>{{ ucfirst($order->costumer) }}</td>
    <td class="text-center">{{ $order->qty }}</td>
    <td class="text-center">
        <span class="badge bg-info text-dark">{{ $order->status }}</span>
    </td>
    <td class="text-center">
        {{ ucfirst($order->location ?? '-') }}
    </td>
    <td class="text-center">
        @if(!empty($order->created_at))
        <span class="text-muted fw-semibold">
            {{ \Carbon\Carbon::parse($order->created_at)->format('M/d/Y') }}
        </span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-center" data-order="{{ !empty($order->due_date) ? \Carbon\Carbon::parse($order->due_date)->format('Y-m-d') : '' }}">
        @if(!empty($order->due_date))
        <span class="text-primary fw-semibold">
            {{ \Carbon\Carbon::parse($order->due_date)->format('M/d/Y') }}
        </span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    @php $statusText = strtolower(trim($order->status ?? '')); @endphp
    <td class="text-center">
        @if(!empty($order->due_date) && !empty($order->created_at))
        @php
        $due = \Carbon\Carbon::parse($order->due_date)->startOfDay();
        $uploaded = \Carbon\Carbon::parse($order->created_at)->startOfDay();
        $isBusinessDay = function (\Carbon\Carbon $date) {
            return $date->isWeekday();
        };
        $daysDiffUploaded = $uploaded->diffInDaysFiltered($isBusinessDay, $due, false);
        @endphp
        <span class="badge {{ $daysDiffUploaded >= 0 ? 'bg-secondary' : 'bg-danger' }}">{{ abs($daysDiffUploaded) }}</span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-center">
        @if(!empty($order->due_date))
        @php
        $due = \Carbon\Carbon::parse($order->due_date)->startOfDay();
        $today = \Carbon\Carbon::now()->startOfDay();
        $isBusinessDay = function (\Carbon\Carbon $date) {
            return $date->isWeekday();
        };
        $daysDiffToday = $today->diffInDaysFiltered($isBusinessDay, $due, false);
        @endphp
        <span class="badge {{ $daysDiffToday >= 0 ? 'bg-warning text-dark' : 'bg-danger' }}">{{ abs($daysDiffToday) }}</span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td style="max-width:170px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $order->notes }}">{{ $order->notes }}</td>
</tr>
@empty
{{-- DataTables: dejar tbody vacío para evitar warning tn/4 con colspan --}}
@endforelse
