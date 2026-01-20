<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'General Schedule')
{{--
@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-calendar-alt me-2" aria-hidden="true"></i>
            Order Schedule
        </h4>

        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">General Schedule</li>
            </ol>
        </nav>
    </div>
</div>
@endsection
--}}

@section('content')


{{-- Tab: By Active Schedules --}}
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4 shadow-sm border-0 rounded-3">
            <div class="card-body p-2">
                {{-- Filtros dinámicos --}}

                <div class="erp-filters-shell mb-3">
                    @unlessrole('Deburring|QCShipping')

                        <div class="row no-gutters erp-schedule-row">
                            <!-- Columna izquierda: primer filtro + botón + gráfica -->
                            <div class="col-12 col-lg-4 erp-schedule-split">
                            <div class="erp-pane erp-pane--import">
                                <form id="upload-form" action="{{ route('schedule.orders.import') }}" method="POST" enctype="multipart/form-data" class="position-relative erp-divider-after">
                                    @csrf

                                    <div class="form-group mb-0">
                                        <label for="csv_file" class="mb-1 erp-label">Field CSV <span class="text-danger">*</span></label>
                                        <div class="input-group erp-input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" title="Field CSV *"><i class="fas fa-file-csv text-success"></i></span>
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="csv_file" id="csv_file" accept=".csv,.xlsx,.xls" required>
                                                <label id="csv_file_label" class="custom-file-label" for="csv_file">Select File</label>
                                            </div>
                                            <div class="input-group-append">
                                                <button id="btn-upload" type="submit" class="btn btn-erp-primary px-3">
                                                    <i class="fas fa-upload mr-1" aria-hidden="true"></i> Upload
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Overlay de carga --}}
                                    <div id="loading-overlay" class="position-absolute w-100 h-100 d-none erp-upload-overlay" aria-live="polite" aria-atomic="true">
                                        <div class="d-flex h-100 w-100 align-items-center justify-content-center">
                                            <div class="erp-upload-overlay-inner">
                                                <i class="fas fa-spinner fa-spin mr-2" aria-hidden="true"></i> Uploading file, please wait…
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                </div>
                            </div>
                            <!-- Columna derecha: segundo filtro + botón + gráfica -->
                            <div class="col-12 col-lg-8 erp-schedule-right">
                                <div class="erp-pane erp-pane--filters">
                                <div class="form-group mb-0 col-12 col-md-2 erp-filter-search">
                                    <div class="input-group erp-input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search text-info"></i></span>
                                        </div>
                                        <input id="scheduleGlobalSearch" type="search" class="form-control erp-filter-control" placeholder="Search..." autocomplete="off">
                                    </div>
                                </div>
                                {{-- Location --}}
                                <form method="GET" action="{{ route('schedule.general') }}" id="filterForm" class="form-row">
                                    {{-- Location --}}
                                    <div class="form-group col-12 col-md-3">
                                        <label for="locationFilter" class="mb-1 erp-label">Location</label>
                                        <div class="input-group erp-input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-map-marker-alt text-danger"></i></span>
                                            </div>
                                            <select name="location" id="locationFilter" class="form-control auto-submit erp-filter-control">
                                                <option value="">— All —</option>
                                                @foreach($locations as $location)
                                                <option value="{{ strtolower($location) }}" {{ strtolower(request('location')) == strtolower($location) ? 'selected' : '' }}>
                                                    {{ $location ?? 'Sin asignar' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Status --}}
                                    <div class="form-group col-12 col-md-3 erp-filter-status">
                                        <label for="statusFilter" class="mb-1 erp-label">Status</label>
                                        <div class="input-group erp-input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-tasks text-primary"></i></span>
                                            </div>
                                            <select name="status" id="statusFilter" class="form-control auto-submit erp-filter-control">
                                                <option value="">— All —</option>
                                                @foreach($orders->pluck('status')->unique() as $status)
                                                    <option value="{{ strtolower($status) }}" {{ strtolower(request('status')) == strtolower($status) ? 'selected' : '' }}>
                                                        {{ \Illuminate\Support\Str::title(strtolower($status ?? 'Sin estado')) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Customer --}}
                                    <div class="form-group col-12 col-md-3">
                                        <label for="customerFilter" class="mb-1 erp-label">Customer</label>
                                        <div class="input-group erp-input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-user-tag text-success"></i></span>
                                            </div>
                                            <select name="customer" id="customerFilter" class="form-control auto-submit erp-filter-control">
                                                <option value="">— All —</option>
                                                @foreach($customers as $customer)
                                                <option value="{{ strtolower($customer) }}" {{ strtolower(request('customer')) == strtolower($customer) ? 'selected' : '' }}>
                                                    {{ \Illuminate\Support\Str::title(strtolower($customer)) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Priority & Delete --}}
                                    <div class="form-group col-12 col-md-2">
                                        <label for="actionMenuButton" class="mb-1 erp-label">Priority & Delete</label>
                                        <div class="input-group erp-input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-cogs text-secondary"></i>
                                                </span>
                                            </div>
                                            <button class="form-control text-left dropdown-toggle erp-filter-control"
                                                type="button"
                                                id="actionMenuButton"
                                                data-toggle="dropdown"
                                                aria-haspopup="true"
                                                aria-expanded="false">
                                                — Action —
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right w-100 p-2 erp-dropdown-menu" aria-labelledby="actionMenuButton">
                                                <button type="button"
                                                    class="erp-menu-item erp-menu-item--priority w-100 mb-2"
                                                    data-toggle="modal" data-target="#deleteModal" data-mode="priority">
                                                    <span class="erp-menu-icon" aria-hidden="true"><i class="fas fa-star"></i></span>
                                                    <span class="erp-menu-text">Priority</span>
                                                </button>

                                                <button type="button"
                                                    class="erp-menu-item erp-menu-item--danger w-100"
                                                    data-toggle="modal" data-target="#deleteModal" data-mode="delete">
                                                    <span class="erp-menu-icon" aria-hidden="true"><i class="fas fa-trash-alt"></i></span>
                                                    <span class="erp-menu-text">Delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Export (PDF / Excel / Print) - BS4 --}}
                                    <div class="form-group col-12 col-md-2">
                                        <label for="exportMenuBtn" class="mb-1 erp-label">Export</label>
                                        <div class="input-group erp-input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-file-export text-warning"></i>
                                                </span>
                                            </div>

                                            {{-- Contenedor dropdown BS4 --}}
                                            <div class="dropdown flex-grow-1">
                                                <button
                                                    class="form-control text-left dropdown-toggle erp-filter-control"
                                                    type="button"
                                                    id="exportMenuBtn"
                                                    data-toggle="dropdown" {{-- <- BS4 --}}
                                                    aria-haspopup="true"
                                                    aria-expanded="false">
                                                    — Select —
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-right p-2 w-100 erp-dropdown-menu" aria-labelledby="exportMenuBtn" style="min-width:100%;">
                                                    <button type="button" class="erp-menu-item erp-menu-item--excel w-100 mb-2 export-action" data-action="excel">
                                                        <span class="erp-menu-icon" aria-hidden="true"><i class="fas fa-file-excel"></i></span>
                                                        <span class="erp-menu-text">Excel</span>
                                                    </button>

                                                    <button type="button" class="erp-menu-item erp-menu-item--pdf w-100 mb-2 export-action" data-action="pdf">
                                                        <span class="erp-menu-icon" aria-hidden="true"><i class="fas fa-file-pdf"></i></span>
                                                        <span class="erp-menu-text">PDF</span>
                                                    </button>

                                                    <button type="button" class="erp-menu-item erp-menu-item--print w-100 export-action" data-action="print">
                                                        <span class="erp-menu-icon" aria-hidden="true"><i class="fas fa-print"></i></span>
                                                        <span class="erp-menu-text">Print</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                </form>
                                </div>
                            </div>

                        </div>
                    @endunlessrole
                </div>
                @php
                use Illuminate\Support\Facades\File;

                $logoRel = 'img/logo.png'; // ← usa .png o .jpg
                $logoAbs = public_path($logoRel);

                $mime = File::exists($logoAbs) ? (File::mimeType($logoAbs) ?? 'image/png') : null;
                $b64 = $mime ? base64_encode(file_get_contents($logoAbs)) : null;
                $dataUrl = ($mime && $b64) ? "data:$mime;base64,$b64" : null;
                @endphp
                <!--   <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="fas fa-plus"></i> New Order
                        </button> -->
                @include('orders.schedule_table')
            </div>
        </div>
    </div>
</div>


<!--  {{-- Tab: By End Schedule --}}-->


<!-- Modal -->
@include('orders.schedule_modaltable')
@include('orders.schedule_deleteprioritymodalorder')
@endsection


@section('css')
<link rel="stylesheet" href="{{ asset('vendor/css/orders-schedule.css') }}">
<style>
    .erp-filters-shell {
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.96));
        border-radius: 14px;
        padding: 8px;
    }

    @media (min-width: 992px) {
        .erp-divider-after {
            padding-right: 10px;
            margin-right: 10px;
            border-right: 1px solid rgba(15, 23, 42, 0.12);
        }
    }

    .erp-schedule-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 8px;
        margin: 0;
    }

    .erp-schedule-split {
        border-right: 0;
        padding-right: 14px;
    }

    .erp-schedule-right {
        padding-left: 14px;
    }

    .erp-pane {
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.96));
        border-radius: 14px;
        padding: 10px 10px 8px;
    }

    /* Compact toolbar mode: put upload + filters + search in one row */
    .erp-schedule-split,
    .erp-schedule-right,
    .erp-pane {
        display: contents;
    }

    #upload-form {
        order: 1;
        flex: 1 1 320px;
        min-width: 240px;
    }

    .erp-filter-search {
        order: 3;
        margin: 0 0 0 auto;
        flex: 0 1 320px;
        align-self: flex-end;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    #filterForm.form-row {
        margin: 0;
        order: 2;
        flex: 2 1 720px;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 8px;
    }

    #filterForm.form-row > .form-group {
        padding: 0;
        margin: 0;
    }

    #filterForm.form-row > .form-group > label {
        margin-bottom: 2px !important;
        line-height: 1.05;
    }

    #filterForm.form-row > .form-group.col-md-2 {
        flex: 0 0 155px;
        max-width: none;
    }

    #filterForm.form-row > .form-group.col-md-3 {
        flex: 0 0 195px;
        max-width: none;
    }

    #filterForm.form-row > .form-group.erp-filter-status {
        flex-basis: 235px;
    }

    .erp-label {
        font-weight: 800;
        color: #0f172a;
        font-size: 0.95rem;
    }

    .erp-label-icon {
        width: 26px;
        height: 26px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(34, 197, 94, 0.28);
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
        flex: 0 0 auto;
        font-size: 0.92rem;
    }

    .erp-filter-control {
        border: 1px solid #d5d8dd;
        border-radius: 10px;
        background: #fff;
        color: #0f172a;
        font-weight: 700;
        height: 36px;
        line-height: 1.2;
        padding: 6px 10px;
    }

    /* Filtros (selects): sin bold */
    select.erp-filter-control {
        font-weight: 400;
    }

    /* Dropdowns de Priority/Delete/Export: sin bold */
    button.erp-filter-control {
        font-weight: 400;
    }

    .erp-filter-control:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
        outline: none;
    }

    .erp-input-group .custom-file-label,
    .erp-input-group .custom-file-input {
        height: 36px;
    }

    .erp-input-group .input-group-text {
        height: 36px;
        border: 1px solid #d5d8dd;
        border-right: 0;
        border-radius: 10px 0 0 10px;
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.06);
        color: #0f172a;
    }

    .erp-input-group > .erp-filter-control,
    .erp-input-group > .dropdown > .erp-filter-control {
        border-left: 0;
        border-radius: 0 10px 10px 0;
    }

    .erp-input-group .custom-file-label {
        border-radius: 10px 0 0 10px;
        border: 1px solid #d5d8dd;
        background: #fff;
        font-weight: 400;
        color: #0f172a;
        padding-top: 7px;
    }

    .erp-input-group .custom-file-input:focus~.custom-file-label {
        border-color: #94a3b8;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
    }

    .erp-input-group .input-group-append .btn-erp-primary {
        height: 36px;
        border-radius: 0 10px 10px 0;
        font-weight: 900;
        letter-spacing: .02em;
    }

    .btn-erp-primary {
        background: #0b5ed7;
        border: 1px solid #0b5ed7;
        color: #fff;
        box-shadow: 0 0 0 2px rgba(11, 94, 215, 0.08);
    }

    .btn-erp-primary:hover {
        background: #0a58ca;
        border-color: #0a58ca;
        color: #fff;
    }

    .erp-dropdown-menu {
        border-radius: 12px;
        border: 1px solid #d5d8dd;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
        background: #fff;
    }

    .erp-menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, 0.10);
        background: rgba(248, 250, 252, 0.95);
        padding: 9px 10px;
        color: #0f172a;
        font-weight: 400;
        font-size: 0.9rem;
        cursor: pointer;
        text-align: left;
        box-shadow: none;
    }

    .erp-menu-item:hover {
        background: rgba(241, 245, 249, 0.95);
        border-color: rgba(15, 23, 42, 0.16);
    }

    .erp-menu-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: rgba(148, 163, 184, 0.14);
        color: #334155;
        flex: 0 0 auto;
    }

    /* Modal header icon (Priority/Delete) */
    .erp-pane-icon {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(248, 250, 252, 0.95);
        color: #334155;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
        flex: 0 0 auto;
    }

    .erp-pane-icon--priority {
        border-color: rgba(147, 51, 234, 0.30);
        background: rgba(147, 51, 234, 0.12);
        color: #6d28d9;
    }

    .erp-pane-icon--danger {
        border-color: rgba(220, 53, 69, 0.30);
        background: rgba(220, 53, 69, 0.12);
        color: #b91c1c;
    }

    .erp-menu-item--priority .erp-menu-icon {
        border-color: rgba(147, 51, 234, 0.30);
        background: rgba(147, 51, 234, 0.12);
        color: #6d28d9;
    }

    .erp-menu-item--danger .erp-menu-icon {
        border-color: rgba(220, 53, 69, 0.30);
        background: rgba(220, 53, 69, 0.12);
        color: #b91c1c;
    }

    .erp-menu-item--excel .erp-menu-icon {
        border-color: rgba(34, 197, 94, 0.30);
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }

    .erp-menu-item--pdf .erp-menu-icon {
        border-color: rgba(220, 53, 69, 0.30);
        background: rgba(220, 53, 69, 0.12);
        color: #b91c1c;
    }

    .erp-menu-item--print .erp-menu-icon {
        border-color: rgba(71, 85, 105, 0.28);
        background: rgba(71, 85, 105, 0.12);
        color: #334155;
    }

    .erp-menu-text {
        flex: 1 1 auto;
    }

    .erp-modal-header {
        border-bottom: 1px solid rgba(15, 23, 42, 0.10);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 12px 14px;
    }

    .erp-modal-title {
        font-weight: 900;
        color: #0f172a;
        font-size: 1.05rem;
        line-height: 1.2;
    }

    .erp-modal-subtitle {
        font-weight: 700;
        font-size: 0.85rem;
        margin-top: 2px;
    }

    .erp-modal-close {
        color: #334155;
        opacity: 0.85;
    }

    .erp-modal-close:hover {
        opacity: 1;
        color: #0f172a;
    }

    .erp-modal-footer {
        border-top: 1px solid rgba(15, 23, 42, 0.10);
        background: rgba(248, 250, 252, 0.70);
        padding: 10px 14px;
    }

    .erp-modal-table thead th {
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        color: #000 !important;
        font-weight: 900;
        font-size: 0.78rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-bottom: 1px solid #d5d8dd !important;
        vertical-align: middle;
        white-space: nowrap;
    }

    .erp-modal-table-wrap {
        border: 1px solid rgba(15, 23, 42, 0.12);
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .erp-modal-table {
        margin-bottom: 0;
        table-layout: fixed;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .erp-modal-table td {
        vertical-align: middle;
        font-weight: 500;
        color: #111827;
        padding: 10px 10px;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .erp-modal-table thead th {
        padding: 10px 10px;
        border-top: 0;
    }

    .erp-modal-table tbody tr:nth-child(even) td {
        background: rgba(248, 250, 252, 0.60);
    }

    .erp-modal-table tbody tr:hover td {
        background: rgba(2, 6, 23, 0.04);
    }

    .erp-modal-table td:nth-child(1),
    .erp-modal-table td:nth-child(2),
    .erp-modal-table td:nth-child(4),
    .erp-modal-table td:nth-child(5) {
        white-space: nowrap;
    }

    .erp-modal-table td:nth-child(3) {
        white-space: nowrap;
    }

    .erp-modal-table td:nth-child(6) {
        text-align: center;
        white-space: nowrap;
    }

    .erp-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(248, 250, 252, 0.95);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        box-shadow: none;
    }

    .erp-action-btn:hover {
        background: rgba(241, 245, 249, 0.95);
        border-color: rgba(15, 23, 42, 0.18);
    }

    .erp-action-btn--danger {
        border-color: rgba(220, 53, 69, 0.30);
        background: rgba(220, 53, 69, 0.12);
        color: #b91c1c;
    }

    .erp-action-btn--priority {
        border-color: rgba(147, 51, 234, 0.30);
        background: rgba(147, 51, 234, 0.12);
        color: #6d28d9;
    }

    .erp-action-btn--muted {
        border-color: rgba(100, 116, 139, 0.25);
        background: rgba(100, 116, 139, 0.10);
        color: #334155;
    }

    .erp-star--purple {
        color: #6d28d9;
    }

    .erp-upload-overlay {
        top: 0;
        left: 0;
        z-index: 10;
        background: rgba(248, 250, 252, 0.86);
        border-radius: 14px;
        backdrop-filter: blur(2px);
    }

    .erp-upload-overlay-inner {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(11, 94, 215, 0.10);
        border: 1px solid rgba(11, 94, 215, 0.18);
        color: #0b5ed7;
        font-weight: 800;
        font-size: 0.88rem;
    }

    @media (max-width: 991.98px) {
        .erp-schedule-split {
            border-right: 0;
            padding-right: 0;
            margin-bottom: 12px;
        }

        .erp-schedule-right {
            padding-left: 0;
        }
    }
</style>

@endsection

@push('js')

<script src="{{ asset('vendor/js/orders-schedule.js') }}"></script>


<script>

     // Queda null si no existe o no se pudo leer
  window.LOGO_BASE64 =   'data:image/png;base64,{{ base64_encode(file_get_contents(public_path("img/acc.png"))) }}';

    let currentActionMode = 'delete'; // default

    // 🟢 Detectar qué botón abre el modal y actualizar contenido
    document.querySelectorAll('[data-toggle="modal"][data-target="#deleteModal"]').forEach(button => {
        button.addEventListener('click', function() {
            currentActionMode = this.getAttribute('data-mode');

            const modalTitle = document.getElementById('modalTitle');
            const modalSubtitle = document.getElementById('modalSubtitle');
            const modeIconWrap = document.getElementById('modalModeIconWrap');
            const modeIcon = document.getElementById('modalModeIcon');
            const searchInput = document.getElementById('searchInput');

            if (currentActionMode === 'delete') {
                modalTitle.textContent = 'Search and delete Order';
                if (modalSubtitle) modalSubtitle.textContent = 'Find an order and mark it as deleted.';
                searchInput.placeholder = 'Search by Work ID, PN, Description, Customer...';
                if (modeIconWrap) modeIconWrap.className = 'erp-pane-icon erp-pane-icon--danger mr-2';
                if (modeIcon) modeIcon.className = 'fas fa-trash-alt';
            } else if (currentActionMode === 'priority') {
                modalTitle.textContent = 'Search and prioritize Order';
                if (modalSubtitle) modalSubtitle.textContent = 'Find an order and toggle priority.';
                searchInput.placeholder = 'Search to mark as priority...';
                if (modeIconWrap) modeIconWrap.className = 'erp-pane-icon erp-pane-icon--priority mr-2';
                if (modeIcon) modeIcon.className = 'fas fa-star';
            }

            // Limpiar campo y tabla
            searchInput.value = '';
            document.querySelector('#searchTable tbody').innerHTML = '';
        });
    });

    // 🔍 Buscar órdenes según input
    document.getElementById('searchInput').addEventListener('input', function() {
        const term = this.value.trim();

        if (term.length < 2) {
            document.querySelector('#searchTable tbody').innerHTML = '';
            return;
        }

        fetch(`/orders/search?term=${encodeURIComponent(term)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#searchTable tbody');
                tbody.innerHTML = '';

                if (data.length === 0) {
                    tbody.innerHTML =
                        `<tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>`;
                    return;
                }

                data.forEach(order => {
                    const row = document.createElement('tr');
                    const dueDate = order.due_date ? new Date(order.due_date) : null;

                    const formattedDueDate = dueDate ?
                        dueDate.toLocaleDateString('en-US', {
                            month: 'short',
                            day: '2-digit',
                            year: '2-digit'
                        }) : '';

                    const orderDueDate = dueDate ? dueDate.toISOString().slice(0, 10) : '';

                    row.innerHTML = `
                    <td>${order.work_id ?? ''}</td>
                    <td>${order.PN ?? ''}</td>
                    <td>${order.Part_description ?? ''}</td>
                    <td>${order.costumer ?? ''}</td>
                    <td data-order="${orderDueDate}">${formattedDueDate}</td>
                `;

                    // 🔁 Acción dinámica según modo
                    if (currentActionMode === 'delete') {
                        row.innerHTML += `
                        <td>
                            <form method="POST" action="/orders/${order.id}/deactivate" class="form-action">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="button" class="btn btn-sm erp-action-btn erp-action-btn--danger btn-action" data-confirm="This will mark the order as deleted.">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    `;
                    } else if (currentActionMode === 'priority') {
                        const isPrioritized = order.priority === 'yes';

                        const formAction = `/orders/${order.id}/toggle-priority`; // Nueva ruta toggle
                        const buttonIcon = isPrioritized ?
                            '<i class="fas fa-star erp-star--purple"></i>' // llena
                            :
                            '<i class="far fa-star erp-star--purple"></i>'; // vacía

                        const confirmText = isPrioritized ?
                            'This will remove priority from the order.' :
                            'This will mark the order as priority.';

                        row.innerHTML += `
        <td>
            <form method="POST" action="${formAction}" class="form-action">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button type="button" class="btn btn-sm erp-action-btn ${isPrioritized ? 'erp-action-btn--priority' : 'erp-action-btn--muted'} btn-action" data-confirm="${confirmText}">
                    ${buttonIcon}
                </button>
            </form>
        </td>
    `;
                    }

                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error al buscar órdenes:', error);
            });
    });

    // ✅ Delegación única de eventos para confirmaciones
    document.getElementById('searchTable').addEventListener('click', function(e) {
        const button = e.target.closest('.btn-action');
        if (!button) return;

        const form = button.closest('form');
        if (!form) return;

        const confirmText = button.getAttribute('data-confirm') || 'Are you sure?';

        Swal.fire({
            title: 'Are you sure?',
            text: confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, confirm',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: '¡Success!',
            text: "{{ session('success') }}",
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}",
            confirmButtonColor: '#b91c1c',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif

@if($errors && $errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const msgs = @json($errors->all());
        const first = (msgs && msgs.length) ? msgs[0] : 'Something went wrong.';
        Swal.fire({
            icon: 'error',
            title: 'Upload Failed',
            text: first,
            confirmButtonColor: '#b91c1c',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif

@endpush
