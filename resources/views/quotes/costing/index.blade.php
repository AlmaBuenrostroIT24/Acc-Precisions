@extends('adminlte::page')

@section('title', 'Costing')

@section('plugins.Datatables', true)

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="costingTable" class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>work_id</th>
                            <th>co</th>
                            <th>cust_po</th>
                            <th>pn</th>
                            <th>Part_description</th>
                            <th>customer</th>
                            <th>qty</th>
                            <th>wo_qty</th>
                            <th>operation</th>
                            <th>due_date</th>
                            <th>pdf</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->work_id }}</td>
                                <td>{{ $order->co }}</td>
                                <td>{{ $order->cust_po }}</td>
                                <td>{{ $order->pn }}</td>
                                <td>{{ $order->Part_description }}</td>
                                <td>{{ $order->customer }}</td>
                                <td>{{ $order->qty }}</td>
                                <td>{{ $order->wo_qty }}</td>
                                <td>{{ $order->operation }}</td>
                                <td>{{ $order->due_date ? \Carbon\Carbon::parse($order->due_date)->format('Y-m-d') : '' }}</td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-outline-danger" target="_blank" href="{{ route('costing.pdf', $order->id) }}">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function () {
            $('#costingTable').DataTable({
                pageLength: 25,
                order: [[9, 'desc']],
            });
        });
    </script>
@stop
