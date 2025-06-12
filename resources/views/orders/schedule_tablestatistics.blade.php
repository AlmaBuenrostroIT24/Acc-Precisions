<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>WORK ID</th>
            <th>PN</th>
            <th>DESCRIPTION</th>
            <th>CUSTOMER</th>
            <th>QTY</th>
            <th>DUE DATE</th>

            <!-- otros campos -->
        </tr>
    </thead>
    <tbody>
        @foreach ($ordenesSemana as $order)
        <tr>
            <td>{{ $order->id }}</td>
            <td>{{ $order->PN }}</td>
            <td> {{ $order->work_id }}</td>
            <td>{{ $order->Part_description }}</td>
            <td>{{ $order->costumer }}</td>
            <td>{{ $order->qty }}</td>
            <td>{{ $order->due_date->format('d/m/Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>