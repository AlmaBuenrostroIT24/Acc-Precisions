<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Schedule Hearst')
@section('meta') {{-- ✅ Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection


@section('content')


<div class="row">
    {{-- Tarjeta 1 --}}
    {{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
    <div class="col-md-12 col-sm-6 mb-3">
        <div class="card shadow-sm rounded-3 border-0 h-100">
            <div class="card-body">

                <div class="erp-dt-topbar d-flex align-items-center justify-content-between flex-wrap mb-2" data-dt="#workhearst_Table" style="gap:.5rem">
                    <div class="d-flex align-items-center">
                        <span class="erp-card-icon erp-card-icon--info mr-2">
                            <i class="fas fa-industry"></i>
                        </span>
                        <div class="erp-card-title">Orders In Process Machining</div>
                        <span class="badge erp-count-badge ml-2" id="countWorkhearst">0</span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap justify-content-end" style="gap:.5rem">
                        <div data-slot="length"></div>
                        <div data-slot="filter"></div>
                    </div>
                </div>
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="workhearst_Table"
                        class="table table-bordered table-sm table-hover erp-table nowrap small mb-0 text-nowrap align-middle"
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
                                <th style="width: 140px;">STATUS</th>
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
            <div class="card-body">
                <div class="erp-dt-topbar d-flex align-items-center justify-content-between flex-wrap mb-2" data-dt="#ordersReady_Table" style="gap:.5rem">
                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                        <div data-slot="length"></div>
                        <div class="d-flex align-items-center">
                            <span class="erp-card-icon erp-card-icon--success mr-2">
                                <i class="fas fa-box"></i>
                            </span>
                            <div class="erp-card-title">Ready To Deliver</div>
                            <span class="badge erp-count-badge ml-2" id="countReady">0</span>
                        </div>
                    </div>
                    <div data-slot="filter"></div>
                </div>
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="ordersReady_Table"
                        class="table table-bordered table-sm table-hover erp-table nowrap small mb-0 text-nowrap align-middle"
                        style="table-layout: fixed; width: 100%;">
                        <thead class="table-light text-center small">
                            <tr class="text-center">
                                <th style="width: 40px;">LOC.</th>
                                <th style="width: 50px;">WORK ID</th>
                                <th style="width: 60px;">PN</th>
                                <th style="width: 110px;">PART</th>
                                <th style="width: 70px;">CUSTOMER</th>
                                <th style="width: 30px;">C QTY</th>
                                <th style="width: 25px;">W QTY</th>
                                <th style="width: 130px;">STATUS</th>
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
            <div class="card-body">
                <div class="erp-dt-topbar d-flex align-items-center justify-content-between flex-wrap mb-2" data-dt="#ordersDeburring_Table" style="gap:.5rem">
                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                        <div data-slot="length"></div>
                        <div class="d-flex align-items-center">
                            <span class="erp-card-icon erp-card-icon--warn mr-2">
                                <i class="fas fa-tools"></i>
                            </span>
                            <div class="erp-card-title">Orders In Deburring</div>
                            <span class="badge erp-count-badge ml-2" id="countDeburring">0</span>
                        </div>
                    </div>
                    <div data-slot="filter"></div>
                </div>
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="ordersDeburring_Table"
                        class="table table-bordered table-sm table-hover erp-table nowrap small mb-0 text-nowrap align-middle"
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
                                <th style="width: 130px;">STATUS</th>
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
            <div class="card-body">
                <div class="erp-dt-topbar d-flex align-items-center justify-content-between flex-wrap mb-2" data-dt="#ordersOutsource_Table" style="gap:.5rem">
                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                        <div data-slot="length"></div>
                        <div class="d-flex align-items-center">
                            <span class="erp-card-icon erp-card-icon--purple mr-2">
                                <i class="fas fa-truck-loading"></i>
                            </span>
                            <div class="erp-card-title">Orders Out Source</div>
                            <span class="badge erp-count-badge ml-2" id="countOutsource">0</span>
                        </div>
                    </div>
                    <div data-slot="filter"></div>
                </div>
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="ordersOutsource_Table"
                        class="table table-bordered table-sm table-hover erp-table nowrap small mb-0 text-nowrap align-middle"
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
                                <th style="width: 130px;">STATUS</th>
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
            <div class="card-body">
                <div class="erp-dt-topbar d-flex align-items-center justify-content-between flex-wrap mb-2" data-dt="#ordersProcessend_Table" style="gap:.5rem">
                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                        <div data-slot="length"></div>
                        <div class="d-flex align-items-center">
                            <span class="erp-card-icon erp-card-icon--danger mr-2">
                                <i class="fas fa-flag-checkered"></i>
                            </span>
                            <div class="erp-card-title">Orders In The Process Of Completion</div>
                            <span class="badge erp-count-badge ml-2" id="countProcessend">0</span>
                        </div>
                    </div>
                    <div data-slot="filter"></div>
                </div>
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="ordersProcessend_Table"
                        class="table table-bordered table-sm table-hover erp-table nowrap small mb-0 text-nowrap align-middle"
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
                                <th style="width: 130px;">STATUS</th>
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
<style>
    .erp-card-header {
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        border-bottom: 1px solid rgba(15, 23, 42, 0.10);
        color: #0f172a;
        gap: .5rem;
        padding: 10px 12px;
    }

    .erp-card-title {
        font-weight: 900;
        font-size: 0.95rem;
        letter-spacing: .02em;
        text-transform: uppercase;
        color: #0f172a;
    }

    .erp-card-icon {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(148, 163, 184, 0.55);
        background: rgba(148, 163, 184, 0.12);
        color: #0f172a;
        flex-shrink: 0;
    }

    .erp-card-icon--info {
        border-color: rgba(14, 165, 233, 0.45);
        background: rgba(14, 165, 233, 0.12);
        color: #0c4a6e;
    }

    .erp-card-icon--success {
        border-color: rgba(34, 197, 94, 0.45);
        background: rgba(34, 197, 94, 0.12);
        color: #14532d;
    }

    .erp-card-icon--warn {
        border-color: rgba(245, 158, 11, 0.45);
        background: rgba(245, 158, 11, 0.12);
        color: #7c2d12;
    }

    .erp-card-icon--danger {
        border-color: rgba(239, 68, 68, 0.45);
        background: rgba(239, 68, 68, 0.12);
        color: #7f1d1d;
    }

    .erp-card-icon--purple {
        border-color: rgba(147, 51, 234, 0.45);
        background: rgba(147, 51, 234, 0.10);
        color: #2e1065;
    }

    .erp-count-badge {
        font-weight: 900;
        border-radius: 999px;
        padding: 6px 10px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(15, 23, 42, 0.05);
        color: #0f172a;
        font-size: 0.85rem;
    }

    .erp-dt-topbar {
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, 0.10);
        background: rgba(248, 250, 252, 0.70);
        padding: 6px 10px;
    }

    /* Solo mostrar "Show entries" en la tabla principal */
    .erp-dt-topbar:not([data-dt="#workhearst_Table"]) [data-slot="length"] {
        display: none;
    }

    .erp-dt-topbar .erp-card-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
    }

    .erp-dt-topbar .erp-card-title {
        font-size: 0.82rem;
        letter-spacing: .02em;
    }

    .erp-dt-topbar .erp-count-badge {
        padding: 4px 8px;
        font-size: 0.80rem;
    }

    .erp-dt-topbar .dataTables_filter,
    .erp-dt-topbar .dataTables_length {
        margin: 0;
    }

    .erp-dt-topbar .dataTables_filter,
    .erp-dt-topbar .dataTables_length,
    .erp-dt-topbar .dataTables_filter label,
    .erp-dt-topbar .dataTables_length label {
        display: flex;
        align-items: center;
        gap: .4rem;
        white-space: nowrap;
    }

    .erp-dt-topbar .dataTables_filter input {
        height: 32px;
        padding: 5px 8px;
        border-radius: 10px;
        min-width: 180px;
        width: 210px;
    }

    .erp-dt-topbar .dataTables_length select {
        min-width: 88px;
        font-size: 1.05rem;
        font-weight: 900;
        padding-right: 38px !important;
        background-image:
            linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%),
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%23475569' d='M5.5 7.5 10 12l4.5-4.5 1.4 1.4L10 14.8 4.1 8.9z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: 0 0, right 10px center !important;
        background-size: auto, 18px 18px !important;
    }

    @media (max-width: 576px) {
        .erp-dt-topbar .dataTables_filter input {
            width: 170px;
            min-width: 150px;
        }
    }

    /* Subir un poco las tablas */
    .card.shadow-sm > .card-body {
        padding-top: .25rem;
        padding-bottom: .6rem;
    }

    .erp-dt-topbar {
        margin-bottom: 0 !important;
    }

    .erp-dt-topbar + .table-responsive {
        margin-top: 0 !important;
    }

    .erp-table thead th {
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        color: #0f172a;
        font-weight: 800;
        font-size: 0.78rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-bottom: 1px solid #d5d8dd !important;
        vertical-align: middle;
    }

    .erp-table {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #d5d8dd;
        margin-bottom: 0;
    }

    .erp-table.table-bordered td,
    .erp-table.table-bordered th {
        border-color: #d5d8dd !important;
    }

    .erp-table thead th,
    .erp-table tbody td {
        padding: 6px 8px;
    }

    .erp-table tbody td {
        color: #111827;
        vertical-align: middle;
        font-size: 0.90rem;
    }

    /* Row hover (status accent, no heavy background) */
    .erp-table tbody tr.erp-row--info:hover td,
    .erp-table tbody tr.erp-row--success:hover td,
    .erp-table tbody tr.erp-row--warn:hover td,
    .erp-table tbody tr.erp-row--danger:hover td {
        box-shadow: inset 0 1px 0 var(--erp-row-accent, rgba(148, 163, 184, 0.55)),
            inset 0 -1px 0 var(--erp-row-accent, rgba(148, 163, 184, 0.55));
    }

    .erp-table tbody tr.erp-row--info:hover td:first-child,
    .erp-table tbody tr.erp-row--success:hover td:first-child,
    .erp-table tbody tr.erp-row--warn:hover td:first-child,
    .erp-table tbody tr.erp-row--danger:hover td:first-child {
        box-shadow: inset 4px 0 0 var(--erp-row-accent, rgba(148, 163, 184, 0.55)),
            inset 0 1px 0 var(--erp-row-accent, rgba(148, 163, 184, 0.55)),
            inset 0 -1px 0 var(--erp-row-accent, rgba(148, 163, 184, 0.55));
    }

    .erp-table tbody tr.erp-row--info { --erp-row-accent: rgba(14, 165, 233, 0.65); }
    .erp-table tbody tr.erp-row--success { --erp-row-accent: rgba(34, 197, 94, 0.65); }
    .erp-table tbody tr.erp-row--warn { --erp-row-accent: rgba(245, 158, 11, 0.65); }
    .erp-table tbody tr.erp-row--danger { --erp-row-accent: rgba(239, 68, 68, 0.65); }

    .erp-table td.erp-icon-cell {
        text-align: center;
    }

    .erp-table .toggle-report-btn,
    .erp-table .toggle-source-btn {
        margin-left: auto !important;
        margin-right: auto !important;
    }

    /* Ellipsis + tooltips */
    .erp-ellipsis-1,
    .erp-ellipsis-2 {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .erp-ellipsis-1 { -webkit-line-clamp: 1; }
    .erp-ellipsis-2 { -webkit-line-clamp: 2; }

    /* Location pill */
    .erp-location-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        height: 20px;
        padding: 2px 6px;
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, 0.25);
        background: #ffffff;
        color: #0f172a;
        font-weight: 900;
        font-size: 0.70rem;
        line-height: 1;
        white-space: nowrap;
    }

    .erp-location-pill i {
        font-size: 0.75rem;
    }

    .erp-location-pill--hearst {
        border-color: rgba(14, 165, 233, 0.75);
        background: rgba(14, 165, 233, 0.24);
        color: #0c4a6e;
    }

    .erp-location-pill--yarnell {
        border-color: rgba(245, 158, 11, 0.75);
        background: rgba(245, 158, 11, 0.28);
        color: #7c2d12;
    }

    .erp-location-pill--floor {
        border-color: rgba(34, 197, 94, 0.75);
        background: rgba(34, 197, 94, 0.24);
        color: #14532d;
    }

    .erp-location-pill--standby {
        border-color: rgba(147, 51, 234, 0.75);
        background: rgba(147, 51, 234, 0.24);
        color: #2e1065;
    }

    .erp-location-note {
        display: block;
        margin-top: 2px;
        font-size: 0.62rem;
        font-weight: 800;
        color: #64748b;
        line-height: 1.1;
        white-space: nowrap;
    }
    /* Botones REPORT / OUT tipo ERP */
    .erp-table .toggle-report-btn,
    .erp-table .toggle-source-btn {
        width: 34px;
        height: 34px;
        padding: 0 !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid rgba(15, 23, 42, 0.22) !important;
        background: #f8fafc !important;
        box-shadow: 0 1px 1px rgba(15, 23, 42, 0.10) !important;
        line-height: 1 !important;
    }

    .erp-table .toggle-report-btn i,
    .erp-table .toggle-source-btn i {
        font-size: 1.05rem;
    }

    .erp-table .toggle-report-btn.btn-primary,
    .erp-table .toggle-source-btn.btn-primary {
        border-color: rgba(11, 94, 215, 0.55) !important;
        background: #f8fafc !important;
        color: #0b5ed7 !important;
    }

    .erp-table .toggle-report-btn.btn-secondary,
    .erp-table .toggle-source-btn.btn-secondary {
        border-color: rgba(71, 85, 105, 0.45) !important;
        background: #f8fafc !important;
        color: #475569 !important;
    }

    .erp-table tbody tr:hover {
        background: rgba(2, 6, 23, 0.04);
    }

    /* Selects tipo ERP (DataTables + status dropdown) */
    .erp-table select,
    .dataTables_wrapper select,
    .dataTables_length select {
        border: 1px solid #c5c9d2 !important;
        border-radius: 10px !important;
        padding: 6px 34px 6px 10px !important;
        background-color: transparent !important;
        background-image:
            linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%),
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%23475569' d='M5.5 7.5 10 12l4.5-4.5 1.4 1.4L10 14.8 4.1 8.9z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 0 0, right 10px center;
        background-size: auto, 16px 16px;
        color: #0f172a !important;
        font-weight: 700;
        height: 34px;
        line-height: 1.2;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.08);
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    .erp-table select:focus,
    .dataTables_wrapper select:focus,
    .dataTables_length select:focus {
        border-color: #94a3b8 !important;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25) !important;
        outline: none !important;
    }

    /* DataTables: search + pagination tipo ERP */
    .dataTables_filter input {
        border: 1px solid #c5c9d2;
        border-radius: 10px;
        padding: 6px 10px;
        background: #fff;
        box-shadow: none;
        color: #0f172a;
        font-weight: 600;
        height: 34px;
        line-height: 1.2;
    }

    .dataTables_filter input:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
        outline: none;
    }

    .dataTables_filter label,
    .dataTables_length label {
        color: #475569;
        font-weight: 700;
        font-size: 0.80rem;
        letter-spacing: .02em;
        text-transform: uppercase;
        margin-bottom: 0;
    }

    /* Quitar solo el texto "Search" (mantener el input) */
    .dataTables_filter label {
        font-size: 0;
    }

    .dataTables_filter label input {
        font-size: 0.95rem;
    }

    .dataTables_wrapper .dataTables_info {
        color: #475569;
        font-weight: 600;
        font-size: 0.80rem;
        line-height: 1.1;
        padding-top: 0;
        margin-top: 0;
        margin-bottom: 0;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding-top: 0;
        margin-top: 0;
        margin-bottom: 0;
    }

    .dataTables_wrapper .dataTables_paginate {
        transform: translateY(-22px);
    }

    .dataTables_wrapper .dataTables_info {
        transform: none;
        padding-top: .6rem;
    }

    .dataTables_wrapper .pagination {
        margin-top: 0;
        margin-bottom: 0;
    }

    .dataTables_wrapper .pagination .page-link {
        border-radius: 10px;
        margin: 0 2px;
        border: 1px solid #d5d8dd;
        background: #f8fafc;
        color: #1f2937;
        font-weight: 800;
        box-shadow: none;
        padding: .35rem .6rem;
    }

    .dataTables_wrapper .pagination .page-link:focus {
        box-shadow: 0 0 0 2px rgba(11, 94, 215, 0.15);
    }

    .dataTables_wrapper .pagination .page-item.active .page-link {
        background: #0b5ed7;
        border-color: #0b5ed7;
        color: #fff;
    }

    /* Status select: mostrar selección tipo ERP */
    .erp-table .status-select,
    .dataTables_wrapper .status-select {
        width: 100% !important;
        min-width: 120px;
        border-radius: 999px !important;
        height: 34px !important;
        padding: 6px 34px 6px 12px !important;
        font-weight: 900 !important;
        letter-spacing: .01em;
        border: 1px solid rgba(148, 163, 184, 0.55) !important;
        background-color: #fff !important;
        background-image:
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%23475569' d='M5.5 7.5 10 12l4.5-4.5 1.4 1.4L10 14.8 4.1 8.9z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 16px 16px;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.08) !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
    }

    .erp-table .status-select:focus,
    .dataTables_wrapper .status-select:focus {
        border-color: #94a3b8 !important;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25) !important;
        outline: none !important;
    }

    .erp-status-select--info {
        border-color: rgba(14, 165, 233, 0.55) !important;
        background-image:
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%230c4a6e' d='M5.5 7.5 10 12l4.5-4.5 1.4 1.4L10 14.8 4.1 8.9z'/%3E%3C/svg%3E") !important;
        background-color: #fff !important;
        background-repeat: no-repeat !important;
        background-position: right 10px center !important;
        background-size: 16px 16px !important;
        color: #0c4a6e !important;
    }

    .erp-status-select--success {
        border-color: rgba(34, 197, 94, 0.55) !important;
        background-image:
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%2314532d' d='M5.5 7.5 10 12l4.5-4.5 1.4 1.4L10 14.8 4.1 8.9z'/%3E%3C/svg%3E") !important;
        background-color: #fff !important;
        background-repeat: no-repeat !important;
        background-position: right 10px center !important;
        background-size: 16px 16px !important;
        color: #14532d !important;
    }

    .erp-status-select--warn {
        border-color: rgba(245, 158, 11, 0.60) !important;
        background-image:
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%237c2d12' d='M5.5 7.5 10 12l4.5-4.5 1.4 1.4L10 14.8 4.1 8.9z'/%3E%3C/svg%3E") !important;
        background-color: #fff !important;
        background-repeat: no-repeat !important;
        background-position: right 10px center !important;
        background-size: 16px 16px !important;
        color: #7c2d12 !important;
    }

    .erp-status-select--danger {
        border-color: rgba(239, 68, 68, 0.60) !important;
        background-image:
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%237f1d1d' d='M5.5 7.5 10 12l4.5-4.5 1.4 1.4L10 14.8 4.1 8.9z'/%3E%3C/svg%3E") !important;
        background-color: #fff !important;
        background-repeat: no-repeat !important;
        background-position: right 10px center !important;
        background-size: 16px 16px !important;
        color: #7f1d1d !important;
    }
</style>
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

        const tableCountTargets = {
            '#workhearst_Table': '#countWorkhearst',
            '#ordersReady_Table': '#countReady',
            '#ordersDeburring_Table': '#countDeburring',
            '#ordersOutsource_Table': '#countOutsource',
            '#ordersProcessend_Table': '#countProcessend',
        };

        const dataTables = {};

        const STATUS_SELECT_THEME_CLASSES = [
            'erp-status-select--info',
            'erp-status-select--success',
            'erp-status-select--warn',
            'erp-status-select--danger',
        ];

        function applyStatusSelectTheme(selectEl) {
            const el = selectEl instanceof HTMLElement ? selectEl : (selectEl?.get?.(0) || null);
            if (!el) return;
            const $el = $(el);
            const v = ($el.val() || '').toString().trim().toLowerCase();
            $el.removeClass(STATUS_SELECT_THEME_CLASSES.join(' '));

            const info = ['shipping', 'machining', 'programming', 'setup', 'onrack'];
            const success = ['ready', 'sent'];
            const warn = ['pending', 'waitingformaterial', 'cutmaterial', 'grinding', 'deburring', 'qa', 'outsource', 'assembly', 'marking'];
            const danger = ['onhold'];

            if (danger.includes(v)) $el.addClass('erp-status-select--danger');
            else if (success.includes(v)) $el.addClass('erp-status-select--success');
            else if (info.includes(v)) $el.addClass('erp-status-select--info');
            else if (warn.includes(v)) $el.addClass('erp-status-select--warn');
        }

        function applyStatusSelectThemeIn(container) {
            const $c = container ? $(container) : $(document);
            $c.find('select.status-select').each(function() {
                applyStatusSelectTheme(this);
            });
        }

        function getRowTone(statusRaw) {
            const s = (statusRaw || '').toString().trim().toLowerCase();
            const info = ['shipping', 'machining', 'programming', 'setup', 'onrack'];
            const success = ['ready', 'sent'];
            const warn = ['pending', 'waitingformaterial', 'cutmaterial', 'grinding', 'deburring', 'qa', 'outsource', 'assembly', 'marking'];
            const danger = ['onhold', 'late', 'overdue'];

            if (danger.includes(s)) return 'danger';
            if (success.includes(s)) return 'success';
            if (info.includes(s)) return 'info';
            if (warn.includes(s)) return 'warn';
            return '';
        }

        function applyRowToneIn(container) {
            const $c = container ? $(container) : $(document);
            $c.find('tbody tr').each(function() {
                const tr = this;
                const st = (tr.getAttribute('data-status') || '').toString().trim().toLowerCase();
                const tone = getRowTone(st);
                tr.classList.remove('erp-row--info', 'erp-row--success', 'erp-row--warn', 'erp-row--danger');
                if (tone) tr.classList.add(`erp-row--${tone}`);
            });
        }

        function updateTableCount(selector) {
            const badgeSel = tableCountTargets[selector];
            if (!badgeSel) return;
            const dt = dataTables[selector];
            if (!dt) return;
            const $badge = $(badgeSel);
            if (!$badge.length) return;
            try {
                $badge.text(dt.rows({ search: 'applied' }).count());
            } catch (e) {}
        }

        function bindTableCount(selector) {
            const dt = dataTables[selector];
            if (!dt) return;
            if (dt.__erpCountBound) return;
            dt.__erpCountBound = true;
            dt.on('draw', function() {
                updateTableCount(selector);
                applyStatusSelectThemeIn(selector);
                applyRowToneIn(selector);
            });
            updateTableCount(selector);
            applyStatusSelectThemeIn(selector);
            applyRowToneIn(selector);
        }

        function attachDtControlsToTopbar(selector) {
            const $topbar = $(`.erp-dt-topbar[data-dt="${selector}"]`);
            if (!$topbar.length) return;

            const $wrapper = $(`${selector}_wrapper`);
            if (!$wrapper.length) return;

            const $len = $wrapper.find('.dataTables_length');
            const $filter = $wrapper.find('.dataTables_filter');

            if ($len.length) {
                $topbar.find('[data-slot="length"]').empty().append($len);
                $len.find('label').addClass('m-0');
            }

            if ($filter.length) {
                $topbar.find('[data-slot="filter"]').empty().append($filter);
                $filter.find('label').addClass('m-0');
                const $input = $filter.find('input[type="search"], input[type="text"]').first();
                if ($input.length) {
                    if (!($input.attr('placeholder') || '').trim()) {
                        $input.attr('placeholder', 'Search...');
                    }
                }
            }
        }

        function initTable(selector, orderIndex, orderDir) {
            return $(selector).DataTable({
                destroy: true,
                scrollX: false,
                autoWidth: false,
                pageLength: 10,
                dom: (selector === '#workhearst_Table') ? 'lfrtip' : 'frtip',
                order: [
                    [orderIndex, orderDir]
                ],
            });
        }

        for (const [selector, [orderIndex, orderDir]] of Object.entries(tablesConfig)) {
            dataTables[selector] = initTable(selector, orderIndex, orderDir);
            bindTableCount(selector);
            attachDtControlsToTopbar(selector);
        }

        applyStatusSelectThemeIn(document);
        applyRowToneIn(document);

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
                                bindTableCount(selector);
                                applyStatusSelectThemeIn(tbody);
                                applyRowToneIn(tbody);
                                attachDtControlsToTopbar(selector);
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
                applyStatusSelectTheme(select);

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
                            applyStatusSelectTheme(select);
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
