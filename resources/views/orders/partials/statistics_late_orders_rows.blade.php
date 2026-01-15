@forelse ($orders as $order)
    @php
        $due = !empty($order->due_date) ? \Carbon\Carbon::parse($order->due_date) : null;
    @endphp
    <tr>
        <td>{{ $order->work_id }}</td>
        <td>{{ $order->PN }}</td>
        <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
            {{ $order->Part_description }}
        </td>
        <td>{{ ucfirst($order->costumer) }}</td>
        <td>{{ $order->qty }}</td>
        <td>
            <span class="badge bg-warning text-dark text-truncate d-inline-block" style="max-width: 70px;" title="{{ $order->status }}">
                {{ $order->status }}
            </span>
        </td>
        <td class="text-nowrap">
            @if ($due)
                {{ $due->format('M/d/Y') }}
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td class="text-center">
            <span class="badge bg-danger">
                {{ $due ? $due->diffInDays(now()) : '-' }}
            </span>
        </td>
        <td style="white-space: normal !important; font-size: 12px !important; word-break: break-word;" title="{{ $order->notes }}">
            {{ $order->notes }}
        </td>
    </tr>
@empty
    {{-- DataTables: dejar tbody vacío para evitar warning tn/4 con colspan --}}
@endforelse
