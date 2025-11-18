<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Part/Revision</th>
            <th>Job</th>
            <th>Type</th>
            <th>Opet</th>
            <th>Operator</th>
            <th>Result</th>
            <th>SB/IS</th>
            <th>Observation</th>
            <th>Station</th>
            <th>Method</th>
            <th>Qty Insp.</th>
            <th>Inspector</th>
            <th>Location</th>
        </tr>
    </thead>
    <tbody>
        @foreach($inspections as $inspection)
            @php
                $tz = config('app.timezone', 'UTC');
                $dtCreated = $inspection->created_at
                    ? $inspection->created_at->copy()->setTimezone($tz)
                    : null;
            @endphp
            <tr>
                <td>{{ $dtCreated?->format('Y-m-d H:i') }}</td>
                <td>{{ $inspection->orderSchedule->PN ?? '' }}</td>
                <td>{{ $inspection->orderSchedule->work_id ?? '' }}</td>
                <td>{{ $inspection->insp_type }}</td>
                <td>{{ $inspection->operation }}</td>
                <td>{{ $inspection->operator }}</td>
                <td>{{ $inspection->results }}</td>
                <td>{{ $inspection->sb_is }}</td>
                <td>{{ $inspection->observation }}</td>
                <td>{{ $inspection->station }}</td>
                <td>{{ $inspection->method }}</td>
                <td>{{ $inspection->qty_pcs }}</td>
                <td>{{ $inspection->inspector }}</td>
                <td>{{ $inspection->loc_inspection }}</td>
            </tr>
        @endforeach
    </tbody>
</table>