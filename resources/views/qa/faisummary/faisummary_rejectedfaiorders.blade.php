<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Rejected FAI Orders')
{{--
@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-clipboard-list me-2" aria-hidden="true"></i>
            Rejected FAI Orders
        </h4>

        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active" aria-current="page">Rejected FAI Orders</li>
</ol>
</nav>
</div>
</div>
@endsection
--}}



@section('content')



{{-- Tabs --}}
@include('qa.faisummary.faisummary_tab')


<div class="card shadow">
    <div class="card-header  d-flex align-items-center">
        <strong><i class="fas fa-exclamation-triangle mr-2"></i>Rejected FAI Orders</strong>
    </div>

    <div class="card-body">
        <table id="rejectedFaiTable" class="table table-bordered table-striped table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Work ID</th>
                    <th>PN</th>
                    <th>Description</th>
                    <th>Customer</th>
                    <th>Qty</th>
                    <th>Inspection Status</th>
                    <th>FAI Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($failedOrders as $insp)
                @php
                $order = $insp->orderSchedule;
                $statusInspection = strtolower($order->status_inspection ?? '');
                @endphp
                <tr>
                    <td>{{ $order->work_id ?? '-' }}</td>
                    <td>{{ $order->PN ?? '-' }}</td>
                    <td>{{ $order->Part_description ?? '-' }}</td>
                    <td>{{ $order->costumer ?? '-' }}</td>
                    <td>{{ $order->qty ?? '-' }}</td>
                    <td>
                        @if($statusInspection === 'completed')
                        <span class="badge badge-success">
                            <i class="fas fa-check-circle"></i>
                            Completed
                        </span>
                        @elseif($statusInspection === 'in_progress')
                        <span class="badge badge-info">
                            <i class="fas fa-hourglass-half"></i>
                            In Progress
                        </span>
                        @elseif($statusInspection === 'pending')
                        <span class="badge badge-warning">
                            <i class="fas fa-clock"></i>
                            Pending
                        </span>
                        @else
                        <span class="badge badge-secondary">
                            <i class="fas fa-question-circle"></i>
                            {{ $order->status_inspection ?? 'N/A' }}
                        </span>
                        @endif
                    </td>
                    <td>
                        {{ $insp->date ? \Carbon\Carbon::parse($insp->date)->format('M-d-y H:i') : '—' }}
                    </td>
                    {{-- ✔ BOTÓN VIEW --}}
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning js-view-inspections"
                            data-order-id="{{ $insp->order_schedule_id }}"
                            data-work-id="{{ $order->work_id }}">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">
                        No se encontraron órdenes que hayan tenido FAI No Pass.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>



{{-- Modal: Detalle de inspecciones de la orden --}}
<div class="modal fade" id="orderInspectionsModal" tabindex="-1" role="dialog" aria-labelledby="orderInspectionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="orderInspectionsLabel">
                    Inspections for order
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive mb-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>DATE</th>
                                <th>PART/REVISION</th>
                                <th>JOB</th>
                                <th>TYPE</th>
                                <th>OPET</th>
                                <th>OPERATOR</th>
                                <th>RESULT</th>
                                <th>SB/IS</th>
                                <th>OBSERVATION</th>
                                <th>STATION</th>
                                <th>METHOD</th>
                                <th>QTY INSP.</th>
                                <th>INSPECTOR</th>
                                <th>LOCATION</th>
                            </tr>
                        </thead>
                        <tbody id="orderInspectionsBody">
                            {{-- Aquí se inyectan las filas por AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection


@section('css')
<style>

</style>

@endsection


@push('js')

<script>
    $(function() {
        // DataTable
        $('#rejectedFaiTable').DataTable({
            pageLength: 25,
            order: [
                [6, 'desc']
            ]
        });

        // Botón View
        $(document).on('click', '.js-view-inspections', function() {
            const orderId = $(this).data('order-id');
            const workId = $(this).data('work-id');

            $('#orderInspectionsLabel').text('Inspections for Order ' + workId);

            // Loading
            $('#orderInspectionsBody').html(
                '<tr><td colspan="14" class="text-center py-3">Loading...</td></tr>'
            );

            $.get("{{ url('/qa/rejectedfaiorders') }}/" + orderId + "/inspections", function(resp) {
                $('#orderInspectionsBody').html(resp.html);
                $('#orderInspectionsModal').modal('show');
            }).fail(function() {
                $('#orderInspectionsBody').html(
                    '<tr><td colspan="14" class="text-center text-danger py-3">Error loading inspections.</td></tr>'
                );
                $('#orderInspectionsModal').modal('show');
            });
        });
    });
</script>


@endpush