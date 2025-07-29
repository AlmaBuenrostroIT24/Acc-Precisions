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
                <li class="breadcrumb-item active" aria-current="page">Orders Yarnell</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')

{{-- Tabs --}}
@include('orders.schedule_tab')

<div class="row">
    {{-- Tarjeta 1 --}}
    {{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
    <div class="col-md-12 col-sm-6 mb-3">
        <div class="card shadow-sm rounded-3 border-0 h-100">
            {{-- Header --}}
            <div class="card-header bg-secondary text-white d-flex align-items-center font-weight-semibold">
                <i class="fas fa-calendar-week mr-2"></i> ORDERS IN PROCESS MACHINING
            </div>
            <div class="card-body">
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="workhearst_Table"
                        class="table table-bordered table-sm nowrap small mb-0 text-nowrap align-middle"
                        style="table-layout: fixed; width: 100%;">
                        <thead class="table-light text-center small">
                            <tr>
                                <th style="width: 80px;">LOCATION</th>
                                <th style="width: 40px;">WORK ID</th>
                                <th style="width: 60px;">PN</th>
                                <th style="width: 300px;">PART/DESCRIPTION</th>
                                <th style="width: 85px;">CUSTOMER</th>
                                <th style="width: 40px;">CO QTY</th>
                                <th style="width: 40px;">WO QTY</th>
                                <th style="width: 110px;">STATUS</th>
                                <th style="width: 60px;">REPORT</th>
                                <th style="width: 40px;">OUT</th>
                                <th style="width: 70px;">MACH DATE</th>
                                <th style="width: 70px;">DUE DATE</th>
                                <th style="width: 100px;">NOTES</th>
                            </tr>
                        </thead>
                        <tbody id="workhearst_TableBody">
                            @include('orders.partials.workhearst_table_body')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Tarjeta 1 --}}
    {{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
    <div class="col-md-6 col-sm-6 mb-3">
        <div class="card shadow-sm rounded-3 border-0 h-100">
            {{-- Header --}}
            <div class="card-header bg-secondary text-white d-flex align-items-center font-weight-semibold">
                <i class="fas fa-calendar-week mr-2"></i> READY TO DELIVER
            </div>
            <div class="card-body">
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="ordersReady_Table"
                        class="table table-bordered table-sm nowrap small mb-0 text-nowrap align-middle"
                        style="table-layout: fixed; width: 100%;">
                        <thead class="table-light text-center small">
                            <tr class="text-center">
                                <th style="width: 40px;">LOC.</th>
                                <th style="width: 50px;">WORK ID</th>
                                <th style="width: 80px;">PN</th>
                                <th style="width: 110px;">PART</th>
                                <th style="width: 70px;">CUSTOMER</th>
                                <th style="width: 30px;">C QTY</th>
                                <th style="width: 25px;">W QTY</th>
                                <th style="width: 65px;">STATUS</th>
                                <th style="width: 60px;">DUE DATE</th>
                            </tr>
                        </thead>
                        <tbody id="readyTableBody">
                            @include('orders.partials.ready_table_body')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
    <div class="col-md-6 col-sm-6 mb-3">
        <div class="card shadow-sm rounded-3 border-0 h-100">
            {{-- Header --}}
            <div class="card-header bg-secondary text-white d-flex align-items-center font-weight-semibold">
                <i class="fas fa-calendar-week mr-2"></i> ORDERS IN DEBURRING
            </div>
            <div class="card-body">
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="ordersDeburring_Table"
                        class="table table-bordered table-sm nowrap small mb-0 text-nowrap align-middle"
                        style="table-layout: fixed; width: 100%;">
                        <thead class="table-light text-center small">
                            <tr class="text-center">
                                <th style="width: 40px;">LOC</th>
                                <th style="width: 50px;">WORK ID</th>
                                <th style="width: 80px;">PN</th>
                                <th style="width: 100px;">PART</th>
                                <th style="width: 70px;">CUSTOMER</th>
                                <th style="width: 30px;">C QTY</th>
                                <th style="width: 25px;">W QTY</th>
                                <th style="width: 85px;">STATUS</th>
                                <th style="width: 50px;">DUE DATE</th>
                            </tr>
                        </thead>
                        <tbody id="deburringTableBody">
                            @include('orders.partials.deburring_table_body')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Tarjeta 1 --}}
    {{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
    <div class="col-md-6 col-sm-6 mb-3">
        <div class="card shadow-sm rounded-3 border-0 h-100">
            {{-- Header --}}
            <div class="card-header bg-secondary text-white d-flex align-items-center font-weight-semibold">
                <i class="fas fa-calendar-week mr-2"></i> Orders Out Source
            </div>
            <div class="card-body">
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="ordersOutsource_Table"
                        class="table table-bordered table-sm nowrap small mb-0 text-nowrap align-middle"
                        style="table-layout: fixed; width: 100%;">
                        <thead class="table-light text-center small">
                            <tr class="text-center">
                                <th style="width: 40px;">LOC</th>
                                <th style="width: 50px;">WORK ID</th>
                                <th style="width: 60px;">PN</th>
                                <th style="width: 60px;">PART</th>
                                <th style="width: 65px;">CUSTOMER</th>
                                <th style="width: 20px;">C QTY</th>
                                <th style="width: 20px;">W QTY</th>
                                <th style="width: 90px;">STATUS</th>
                                <th style="width: 45px;">DUE DATE</th>
                                <th style="width: 50px;">NOTES</th>
                            </tr>
                        </thead>
                        <tbody id="outsourceTableBody">
                            @include('orders.partials.outsource_table_body')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
    <div class="col-md-6 col-sm-6 mb-3">
        <div class="card shadow-sm rounded-3 border-0 h-100">
            {{-- Header --}}
            <div class="card-header bg-secondary text-white d-flex align-items-center font-weight-semibold">
                <i class="fas fa-calendar-week mr-2"></i> ORDERS IN THE PROCESS OF COMPLETION
            </div>
            <div class="card-body">
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="ordersProcessend_Table"
                        class="table table-bordered table-sm nowrap small mb-0 text-nowrap align-middle"
                        style="table-layout: fixed; width: 100%;">
                        <thead class="table-light text-center small">
                            <tr class="text-center">
                                <th style="width: 35px;">LOC</th>
                                <th style="width: 45px;">WORK ID</th>
                                <th style="width: 60px;">PN</th>
                                <th style="width: 100px;">PART</th>
                                <th style="width: 100px;">CUSTOMER</th>
                                <th style="width: 25px;">C QTY</th>
                                <th style="width: 25px;">W QTY</th>
                                <th style="width: 85px;">STATUS</th>
                                <th style="width: 50px;">DUE DATE</th>
                            </tr>
                        </thead>
                        <tbody id="processendTableBody">
                            @include('orders.partials.processend_table_body')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>




@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/css/orders-schedule.css') }}">
@endsection


@push('js')

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // 🔁 Inicializar tablas
        const tablesConfig = {
            '#workhearst_Table': [11, 'asc'],
            '#ordersReady_Table': [8, 'asc'],
            '#ordersDeburring_Table': [8, 'asc'],
            '#ordersOutsource_Table': [8, 'asc'],
            '#ordersProcessend_Table': [8, 'asc'],
        };

        const dataTables = {};

        function initTable(selector, orderIndex, orderDir) {
            return $(selector).DataTable({
                destroy: true,
                scrollX: false,
                autoWidth: false,
                pageLength: 10,
                order: [
                    [orderIndex, orderDir]
                ],
            });
        }

        for (const [selector, [orderIndex, orderDir]] of Object.entries(tablesConfig)) {
            dataTables[selector] = initTable(selector, orderIndex, orderDir);
        }

        // 🔁 Mapas para recarga de tablas por estado
        const statusToTableMap = new Map([
            ['deburring', ['#ordersDeburring_Table', '#deburringTableBody', '/workhearst/deburring/partial', 7]],
            ['ready', ['#ordersReady_Table', '#readyTableBody', '/workhearst/ready/partial', 7]],
            ['outsource', ['#ordersOutsource_Table', '#outsourceTableBody', '/workhearst/outsource/partial', 8]],
            ['assembly', ['#ordersProcessend_Table', '#processendTableBody', '/workhearst/processend/partial', 7]],
            ['shipping', ['#ordersProcessend_Table', '#processendTableBody', '/workhearst/processend/partial', 7]],
            ['onhold', ['#ordersProcessend_Table', '#processendTableBody', '/workhearst/processend/partial', 7]],
            ['pending', ['#workhearst_Table', '#workhearst_TableBody', '/workhearst/workinprocess/partial', 11]],
            ['waitingformaterial', ['#workhearst_Table', '#workhearst_TableBody', '/workhearst/workinprocess/partial', 11]],
            ['cutmaterial', ['#workhearst_Table', '#workhearst_TableBody', '/workhearst/workinprocess/partial', 11]],
            ['grinding', ['#workhearst_Table', '#workhearst_TableBody', '/workhearst/workinprocess/partial', 11]],
            ['programming', ['#workhearst_Table', '#workhearst_TableBody', '/workhearst/workinprocess/partial', 11]],
            ['setup', ['#workhearst_Table', '#workhearst_TableBody', '/workhearst/workinprocess/partial', 11]],
            ['onrack', ['#workhearst_Table', '#workhearst_TableBody', '/workhearst/workinprocess/partial', 11]],
            ['machining', ['#workhearst_Table', '#workhearst_TableBody', '/workhearst/workinprocess/partial', 11]],
            ['marking', ['#workhearst_Table', '#workhearst_TableBody', '/workhearst/workinprocess/partial', 11]],
        ]);

        // 🧠 Actualiza estado
        function actualizarStatus(orderId, status, row, select, token, currentTableSelector) {
            $.ajax({
                url: `/orders/${orderId}/update-status`,
                method: 'POST',
                data: {
                    status,
                    _token: token
                },
                success: function(response) {
                    if (response.success) {
                        row.removeClass(function(i, cls) {
                            return (cls.match(/(^|\s)bg-status-\S+/g) || []).join(' ');
                        }).addClass(`bg-status-${response.status}`);

                        // 🔁 Eliminar esa orden de TODAS las tablas
                        for (const [selector, dt] of Object.entries(dataTables)) {
                            const table = dt;
                            table.rows().every(function() {
                                const rowNode = $(this.node());
                                const rowId = rowNode.find('.status-select').data('id');
                                if (rowId == orderId) {
                                    this.remove();
                                }
                            });
                            table.draw(false);
                        }

                        // 🔁 Recargar tabla destino si aplica
                        const reloadConfig = statusToTableMap.get(response.status);
                        if (reloadConfig) {
                            const [selector, tbody, url, orderIndex] = reloadConfig;
                            dataTables[selector].destroy();
                            $(tbody).load(url, function() {
                                dataTables[selector] = initTable(selector, orderIndex, 'desc');
                            });
                        }
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('❌ Error al actualizar el estado.');
                }
            });
        }


        // 🧠 Manejo de eventos genérico
        Object.keys(tablesConfig).forEach((selector) => {
            const table = $(selector);

            table.on('focus', '.status-select', function() {
                $(this).data('old-status', $(this).val());
            });

            table.on('change', '.status-select', function() {
                const select = $(this);
                const orderId = select.data('id');
                const newStatus = select.val().toLowerCase();
                const oldStatus = select.data('old-status');
                const row = select.closest('tr');
                const token = '{{ csrf_token() }}';

                if (newStatus === 'sent') {
                    Swal.fire({
                        title: "¿Are you sure?",
                        text: `Changing the status to '${newStatus}' .It will be moved to 'Completed Orders'.`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, Completed",
                        cancelButtonText: "No, cancel",
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            actualizarStatus(orderId, newStatus, row, select, token, selector);
                        } else {
                            select.val(oldStatus);
                        }
                    });
                } else {
                    actualizarStatus(orderId, newStatus, row, select, token, selector);
                }
            });
        });
    });
</script>
@endpush