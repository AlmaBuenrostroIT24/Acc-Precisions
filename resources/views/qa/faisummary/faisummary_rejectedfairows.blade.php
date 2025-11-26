@forelse($inspections as $insp)
    <tr>
        <td>
            {{ \Carbon\Carbon::parse($insp->date)->format('M-d-y') }}
            <span class="badge badge-light">{{ \Carbon\Carbon::parse($insp->date)->format('H:i') }}</span>
        </td>
        <td>{{ $insp->part_revision ?? $insp->PN ?? '-' }}</td>
        <td>{{ $insp->job ?? '-' }}</td>
        <td>
            <span class="badge badge-info">{{ $insp->insp_type }}</span>
        </td>
        <td>{{ $insp->operation ?? '-' }}</td>
        <td>{{ $insp->operator ?? '-' }}</td>
        <td>
            @php $res = strtolower(trim((string)$insp->results)); @endphp
            @if(in_array($res, ['pass']))
                <span class="badge badge-success">Pass</span>
            @else
                <span class="badge badge-danger">No pass</span>
            @endif
        </td>
        <td>{{ $insp->sb_is ?? '' }}</td>
        <td>{{ $insp->observation ?? '' }}</td>
        <td>{{ $insp->station ?? '' }}</td>
        <td>{{ $insp->method ?? '' }}</td>
        <td>{{ $insp->qty_insp ?? 1 }}</td>
        <td>{{ $insp->inspector ?? '' }}</td>
        <td>{{ $insp->location ?? '' }}</td>
    </tr>
@empty
    <tr>
        <td colspan="14" class="text-center text-muted py-3">
            No inspections found for this order.
        </td>
    </tr>
@endforelse
