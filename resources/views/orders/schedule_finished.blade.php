<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Completed Orders')
@section('meta') {{-- ✅ Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-calendar-alt me-2" aria-hidden="true"></i>
            General Schedule
        </h4>
        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Completed Orders</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')

{{-- Tabs --}}
@include('orders.schedule_tab')

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                {{-- Filtros dinámicos --}}
                <div class="row mb-4">
                    <!-- Formulario de carga -->

                    <!-- Filtros -->
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-body row">
                                <div class="form-group col-md-12">
                                    <form method="GET" action="{{ route('schedule.finished') }}" id="filterForm" class="row g-3 mb-3">
                                        <div class="form-group col-md-4">
                                            <label for="locationFilter">Location</label>
                                            <select name="location" id="locationFilter" class="form-control auto-submit">
                                                <option value="">-- All --</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label for="customerFilter">Customer</label>
                                            <select id="customerFilter" class="form-control auto-submit">
                                                <option value="">-- All --</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--   <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="fas fa-plus"></i> New Order
                        </button> -->
                <div class="table-responsive">
                    {{-- Tabla --}}

                    <table id="orders_endscheduleTable" class="table table-bordered table-striped table-sm ">
                        <thead class="table-light thead-custom">
                            <tr class="text-center align-middle">
                                <th class="text-center align-middle">LOCATION</th>
                                <th class="text-center align-middle">WORKID</th>
                                <th>PN</th>
                                <th style="width: 220px; ">DESCRIPTION</th>
                                <th>CUSTOMER</th>
                                <th style="width: 55px; ">CO QTY</th>
                                <th style="width: 55px; ">WO QTY</th>
                                <th class="text-center align-middle">REPORT</th>
                                <th class="text-center align-middle">OUT/SRC</th>
                                <th style="width: 70px; " class="text-center align-middle">DUE DATE</th>
                                <th style="width: 70px;">END DATE</th>
                                <th class="text-center align-middle">TARGET</th>
                                <th class="text-center align-middle">NOTES</th>
                                <th class="text-center align-middle">STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="statusTable">
                            @foreach($orders as $order)
                            <tr data-status="{{ $order->status }}">
                                <td data-last-location="{{ $order->last_location }}">
                                    <span style="color: black; font-weight: bold;">{{ $order->location }}</span>

                                    @if ($order->last_location === 'Yarnell')
                                    <span class="badge bg-warning text-dark d-inline-flex align-items-center">
                                        <i class="fas fa-map-marker-alt me-1"></i> Yarnell
                                    </span>
                                    @endif
                                </td>
                                <td>{{ $order->work_id }}</td>
                                <td>{{ $order->PN }}</td>
                                <td style="font-size: 12px;">{{ $order->Part_description }}</td>
                                <td>{{ $order->costumer }}</td>
                                <td>{{ $order->qty }}</td>
                                <td>{{ $order->wo_qty }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm toggle-report-btn {{ $order->report ? 'btn-primary' : 'btn-secondary' }}"
                                        data-id="{{ $order->id }}" data-value="{{ $order->report ? 1 : 0 }}">
                                        <i class="fas {{ $order->report ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm toggle-source-btn {{ $order->our_source ? 'btn-primary' : 'btn-secondary' }}"
                                        data-id="{{ $order->id }}" data-value="{{ $order->our_source }}">
                                        <i class="fas {{ $order->our_source ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    </button>
                                </td>
                                <td>{{ optional($order->due_date)->format('M-d-y') }}</td>
                                <td data-order="{{ $order->sent_at ? $order->sent_at->format('Y-m-d H:i:s') : '' }}">
                                    {{ $order->sent_at ? $order->sent_at->format('M-d-y H:i') : '' }}
                                </td>
                                <td class="text-center">
                                    @if ($order->target_date < 0)
                                        <span class="badge bg-danger">{{ $order->target_date }} Late</span>
                                        @elseif ($order->target_date == 0)
                                        <span class="badge bg-success">{{ $order->target_date }} On time</span>
                                        @elseif ($order->target_date > 0)
                                        <span class="badge bg-info">{{ $order->target_date }} Early</span>
                                        @else
                                        <span>-</span> {{-- En caso de que target_date sea null --}}
                                        @endif
                                </td>
                                <td style="font-size: 12px;">
                                    <span class="open-notes-modal" data-id="{{ $order->id }}"
                                        data-notes="{{ e($order->notes) }}" title="{{ e($order->notes) }}">
                                        {{ Str::limit($order->notes, 130) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm toggle-status-btn btn-success"
                                        title="Return Order"
                                        data-id="{{ $order->id }}"
                                        data-status="sent">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')

@endsection

@push('js')

<script>
    $(document).ready(function() {
        const $tableElement = $('#orders_endscheduleTable');
        if (!$tableElement.length) return;

        // ---------------------- 1. FILTRO GLOBAL POR STATUS = "sent" ----------------------
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const row = settings.aoData[dataIndex].nTr;
            const status = $(row).data('status');
            return status === 'sent';
        });

        // ---------------------- 2. INICIALIZAR DATATABLE ----------------------
        const table = $tableElement.DataTable({
            scrollX: false,
            autoWidth: false,
            pageLength: 25,
            order: [
                [10, 'desc']
            ],
            columnDefs: [{
                targets: [6, 7, 11],
                orderable: false
            }]
        });

        window.table = table;

        // ---------------------- 3. POBLAR SELECTS DE FILTRO ----------------------
        populateFilterFromColumn(0, '#locationFilter'); // columna 0: location
        populateFilterFromColumn(4, '#customerFilter'); // columna 4: customer

        // ---------------------- 4. APLICAR FILTROS COMBINADOS ----------------------
        $('#locationFilter, #customerFilter').on("change", function() {
            table.draw();
        });

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const locationVal = $('#locationFilter').val();
            const customerVal = $('#customerFilter').val();

            const row = table.row(dataIndex).node();

            // ------------ LOCATION (columna 0) ------------
            const locationCell = $(row).find('td').eq(0);
            const location = locationCell.find('span').first().text().trim();
            const lastLocation = locationCell.data('last-location');

            let combinedLocation = location;
            if (lastLocation === 'Yarnell' && location !== 'Yarnell') {
                combinedLocation = `${location}-Yarnell`;
            }

            const locationMatch = !locationVal || combinedLocation.toLowerCase() === locationVal.toLowerCase();

            // ------------ CUSTOMER (columna 4) ------------
            const customerCell = $(row).find('td').eq(4);
            const customerText = customerCell.text().trim().toLowerCase();
            const customerMatch = !customerVal || customerText === customerVal.toLowerCase();

            return locationMatch && customerMatch;
        });

        // ---------------------- 5. FUNCIÓN PARA LLENAR LOS SELECTS ----------------------
        function populateFilterFromColumn(columnIndex, selectId) {
            const unique = new Set();

            $('#orders_endscheduleTable tbody tr').each(function() {
                const cell = $(this).find('td').eq(columnIndex);

                if (columnIndex === 0) {
                    const location = cell.find('span').first().text().trim();
                    const lastLocation = cell.data('last-location');

                    if (location && lastLocation === 'Yarnell' && location !== 'Yarnell') {
                        unique.add(`${location}-Yarnell`);
                    } else {
                        if (location) unique.add(location);
                        if (lastLocation === 'Yarnell') unique.add('Yarnell');
                    }
                } else {
                    const value = cell.text().trim();
                    if (value) unique.add(value);
                }
            });

            const $select = $(selectId);
            $select.find('option:not(:first)').remove();

            [...unique].sort().forEach(value => {
                $select.append(`<option value="${value}">${value}</option>`);
            });
        }

        // ---------------------- 6. BOTÓN: RETURN ORDER ----------------------
        $tableElement.on('click', '.toggle-status-btn', function() {
            const btn = $(this);
            const row = btn.closest('tr');
            const orderId = btn.data('id');

            Swal.fire({
                title: '¿Return order?',
                text: 'This order will return to its previous status.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "Yes, Return",
                cancelButtonText: "No, Cancel",
                reverseButtons: true,
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.post(`/orders/${orderId}/return-previous`, {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    })
                    .done(res => {
                        if (res.success) {
                            table.row(row).remove().draw(false);
                            Swal.fire('Done', `The order returned to: ${res.newStatus}`, 'success');
                        } else {
                            Swal.fire('Attention', res.message || 'The order could not be returned.', 'warning');
                        }
                    })
                    .fail(() => {
                        Swal.fire('Error', 'Ocurrió un error al devolver la orden.', 'error');
                    });
            });
        });
    });
</script>
@endpush