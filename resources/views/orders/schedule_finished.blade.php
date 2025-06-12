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
                {{-- Tabla --}}

                <table id="orders_endscheduleTable" class="table table-bordered table-striped table-sm nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>LOCATION</th>
                            <th>Work ID</th>
                            <th>PN</th>
                            <th>PART/DESCRIPTION</th>
                            <th>CUSTOMER</th>
                            <th>Qty</th>
                            <th>Report</th>
                            <th>Out Source</th>
                            <th>Due Date</th>
                            <th>End Date</th>
                            <th>Target Date</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody id="statusTable">
                        @foreach($orders as $order)
                        <tr data-status="{{ $order->status }}">
                            <td>{{ $order->location }}</td>
                            <td>{{ $order->work_id }}</td>
                            <td style="min-width: 120px;">{{ $order->PN }}</td>
                            <td style="font-size: 12px;">{{ $order->Part_description }}</td>
                            <td>{{ $order->costumer }}</td>
                            <td>{{ $order->qty }}</td>
                            <td>
                                <button class="btn btn-sm toggle-report-btn {{ $order->report ? 'btn-primary' : 'btn-secondary' }}"
                                    data-id="{{ $order->id }}" data-value="{{ $order->report ? 1 : 0 }}">
                                    <i class="fas {{ $order->report ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-sm toggle-source-btn {{ $order->our_source ? 'btn-primary' : 'btn-secondary' }}"
                                    data-id="{{ $order->id }}" data-value="{{ $order->our_source }}">
                                    <i class="fas {{ $order->our_source ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                </button>
                            </td>
                            <td>{{ optional($order->due_date)->format('M-d-y') }}</td>
                            <td>
                                {{ $order->sent_at ? $order->sent_at->format('M-d-y H:i') : '' }}
                            </td>
                            <td>
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
                            <td>
                                <span class="open-notes-modal" data-id="{{ $order->id }}"
                                    data-notes="{{ e($order->notes) }}" title="{{ e($order->notes) }}">
                                    {{ Str::limit($order->notes, 30) ?: 'Add Note' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
=
@endsection

@push('js')

<script>
    $(document).ready(function() {
        // Agrega un filtro personalizado para mostrar solo status = "sent"
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const row = settings.aoData[dataIndex].nTr;
            const status = $(row).data('status');
            return status === 'sent';
        });


        // Inicializa la tabla con DataTables
        // Guarda la instancia en una variable global o local
        window.table = $('#orders_endscheduleTable').DataTable({
            scrollX: false,
            autoWidth: false,
            pageLength: 25,
            order: [
                [9, 'desc'] // corregí 'des' por 'desc'
            ],
            columnDefs: [{
                targets: [6, 7, 11],
                orderable: false
            }],
        });

        /**
         * Extrae valores únicos de una columna específica y los usa para llenar un <select>
         * @param {number} columnIndex - Índice de la columna en la tabla
         * @param {string} selectId - ID del <select> para llenar (ej: #locationFilter)
         */
        function populateFilterFromColumn(columnIndex, selectId) {
            const unique = new Set();

            $('#orders_endscheduleTable tbody tr').each(function() {
                const value = $(this).find('td').eq(columnIndex).text().trim().toLowerCase();
                if (value) unique.add(value);
            });

            const values = [...unique].sort();
            const $select = $(selectId);
            $select.find('option:not(:first)').remove(); // mantener "-- All --"

            values.forEach(value => {
                const capitalized = value.charAt(0).toUpperCase() + value.slice(1);
                $select.append(`<option value="${value}">${capitalized}</option>`);
            });
        }

        /**
         * Aplica el filtro exacto con regex a una columna
         * @param {string} selector - Selector del <select>
         * @param {number} columnIndex - Índice de la columna a filtrar
         */
        function applyFilter(selector, columnIndex) {
            $(selector).on("change", function() {
                const val = $(this).val()?.toLowerCase() || "";
                window.table
                    .column(columnIndex)
                    .search(val ? `^${val}$` : "", true, false)
                    .draw();
            });
        }
        // Aplica filtros para location y customer
        populateFilterFromColumn(0, '#locationFilter'); // columna 0 = location
        applyFilter('#locationFilter', 0);

        populateFilterFromColumn(4, '#customerFilter'); // columna 4 = customer
        applyFilter('#customerFilter', 4);
    });
</script>
@endpush