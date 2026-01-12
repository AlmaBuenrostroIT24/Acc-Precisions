@forelse($orders as $item)
    @php
        $year = substr($item->week, 0, 4);
        $week = substr($item->week, 4);
        $startOfWeek = \Carbon\Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = \Carbon\Carbon::now()->setISODate($year, $week)->endOfWeek();

        $completed = $item->completed ?? 0;
        $late = $item->late ?? 0;
        $total = $item->total ?: 1;

        $completedPercent = round(($completed / $total) * 100);
        $latePercent = round(($late / $total) * 100);
    @endphp

    <tr class="text-center">
        <td>{{ $year }} W{{ $week }}</td>
        <td>{{ $startOfWeek->format('M d') }} - {{ $endOfWeek->format('M d') }}</td>
        <td>{{ $item->total }}</td>
        <td><span class="badge bg-success text-white">{{ $completed }}</span></td>
        <td><span class="badge bg-danger text-white">{{ $late }}</span></td>
        <td>
            <span class="badge badge-pill {{ $completedPercent >= 90 ? 'badge-success' : ($completedPercent >= 70 ? 'badge-warning' : 'badge-danger') }}">
                {{ $completedPercent }}%
            </span>
        </td>
    </tr>
    <tr>
        <td colspan="6" class="p-1">
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" style="width: {{ $completedPercent }}%;"></div>
                <div class="progress-bar bg-danger" style="width: {{ $latePercent }}%;"></div>
            </div>
        </td>
    </tr>
@empty
@endforelse

