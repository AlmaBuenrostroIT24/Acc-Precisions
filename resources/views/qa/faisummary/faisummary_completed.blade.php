<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary Completed')
{{--
@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-clipboard-list me-2" aria-hidden="true"></i>
            FAI Summary
        </h4>

        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active" aria-current="page">FAI Summary Completed</li>
</ol>
</nav>
</div>
</div>
@endsection
--}}



@section('content')
@php
    $completedTotal = (int) data_get($kpis ?? [], 'total', 0);
    $completedDone = (int) data_get($kpis ?? [], 'completed', 0);
    $completedNoInspection = (int) data_get($kpis ?? [], 'no_inspection', 0);
    $completedIncomplete = (int) data_get($kpis ?? [], 'incomplete', 0);
@endphp

<div class="row">


    {{-- Columna derecha: Tabla --}}
    <div class="col-lg-12">
        <div class="row mb-3">
            <div class="col-md-6 col-xl-3 mb-2 mb-xl-0">
                <div class="info-box info-box-sm bg-info mb-0" id="kpiBoxTotal">
                    <span class="info-box-icon"><i class="fas fa-clipboard-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Closed inspections</span>
                        <h5 class="mb-0" id="kpiTotal">{{ $completedTotal }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-2 mb-xl-0">
                <div class="info-box info-box-sm bg-success mb-0">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Completed</span>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0" id="kpiPass">{{ $completedDone }}</h5>
                            <small class="text-black-50">100%</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-2 mb-md-0">
                <div class="info-box info-box-sm bg-danger mb-0">
                    <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Incomplete</span>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0" id="kpiFail">{{ $completedIncomplete }}</h5>
                            <small class="text-black-50">&lt; 100%</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="info-box info-box-sm bg-secondary mb-0" id="kpiBoxNoInspection">
                    <span class="info-box-icon"><i class="fas fa-minus-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">No Inspection</span>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0" id="kpiNoInspection">{{ $completedNoInspection }}</h5>
                            <small class="text-black-50">ops/samp 0</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">

            <div class="card-body p-2">
                <div class="fai-table-toolbar mb-2" id="faicompleteToolbar">
                    <div class="toolbar-top">
                        <div class="toolbar-left toolbar-filters">
                            <div class="form-group mb-0" id="filterLocationGroup">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-danger"></i></span>
                                    </div>
                                    <select id="locationFilter" class="form-control dt-filter" name="location" form="filtersForm">
                                        <option value="">— All —</option>
                                        @foreach(($locations ?? []) as $loc)
                                            <option value="{{ $loc }}" {{ request('location') === $loc ? 'selected' : '' }}>{{ ucfirst($loc) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-0" id="filterYearGroup">
                                <div class="input-group input-group-sm date" id="yearPickerWrapper" data-target-input="nearest" data-initial-year="{{ request('year', now()->year) }}" style="width:132px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-success"></i></span>
                                    </div>
                                    <input type="text" id="year" name="year" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#yearPickerWrapper" value="{{ request('year', now()->year) }}" placeholder="Year" autocomplete="off" form="filtersForm">
                                </div>
                            </div>
                            <div class="form-group mb-0" id="filterMonthGroup">
                                <div class="input-group input-group-sm date" id="monthPickerWrapper" data-target-input="nearest" style="width:132px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-danger"></i></span>
                                    </div>
                                    <input type="text" id="monthDisplay" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#monthPickerWrapper" placeholder="Month" autocomplete="off">
                                </div>
                                <input type="hidden" id="month" name="month" value="{{ request('month') }}" form="filtersForm">
                            </div>
                            <div class="form-group mb-0" id="filterDayGroup">
                                <div class="input-group input-group-sm date" id="dayPickerWrapper" data-target-input="nearest" style="width:148px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-day text-warning"></i></span>
                                    </div>
                                    <input type="text" id="day" name="day" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#dayPickerWrapper" value="{{ request('day') ? \Carbon\Carbon::parse(request('day'))->format('Y-m-d') : '' }}" placeholder="Day" autocomplete="off" form="filtersForm">
                                </div>
                            </div>
                            <a href="{{ route('faisummary.completed') }}" class="btn btn-sm btn-erp-gray">
                                <i class="fas fa-eraser mr-1"></i> Clean
                            </a>
                            @php
                                $reqDay = trim((string) request('day', ''));
                                $reqMonth = trim((string) request('month', ''));
                                $reqYear = trim((string) request('year', ''));
                                $nowDate = now()->toDateString();
                                $nowMonth = (string) now()->month;
                                $nowYear = (string) now()->year;
                                $isTodayActive = ($reqDay !== '' && $reqDay === $nowDate);
                                $isCleanDefault = ($reqDay === '' && $reqMonth === '' && ($reqYear === '' || $reqYear === $nowYear));
                                $isCurrentMonthFilter = ($reqDay === '' && $reqMonth === $nowMonth && $reqYear === $nowYear);
                                $isMonthActive = $isCurrentMonthFilter;
                                $isYearOnlyFilter = ($reqDay === '' && $reqMonth === '' && $reqYear === $nowYear);
                                $isYearActive = ($isCleanDefault || $isYearOnlyFilter);
                            @endphp
                            <a class="btn btn-sm {{ $isTodayActive ? 'btn-erp-active' : 'btn-outline-secondary' }}" href="{{ route('faisummary.completed', array_merge(request()->except(['day','month','year','page']), ['day'=>now()->toDateString()])) }}">
                                <i class="fas fa-bolt mr-1"></i> Today
                            </a>
                            <a class="btn btn-sm {{ $isMonthActive ? 'btn-erp-active' : 'btn-outline-secondary' }}" href="{{ route('faisummary.completed', array_merge(request()->except(['day','page']), ['year'=>now()->year,'month'=>now()->month])) }}">
                                <i class="far fa-calendar-alt mr-1"></i> Month
                            </a>
                            <a class="btn btn-sm {{ $isYearActive ? 'btn-erp-active' : 'btn-outline-secondary' }}" href="{{ route('faisummary.completed', array_merge(request()->except(['day','month','page']), ['year'=>now()->year])) }}">
                                <i class="far fa-calendar mr-1"></i> Year
                            </a>
                            <button id="toolbarExportExcel" type="button" class="btn btn-sm btn-erp-gray">
                                <i class="fas fa-file-excel mr-1 text-success"></i> Excel
                            </button>
                            <button id="toolbarExportPdf" type="button" class="btn btn-sm btn-erp-gray">
                                <i class="fas fa-file-pdf mr-1 text-danger"></i> PDF
                            </button>
                        </div>
                        <div class="input-group input-group-sm toolbar-search">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control" id="dtToolbarSearch" placeholder="Search table..." autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="dtToolbarClear"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fai-table-stage" id="faicompleteStage">
                    <div class="table-responsive fai-erp-wrap" id="faicompleteWrap">
                    <table id="faicompleteTable"
                        class="table table-sm align-middle mb-0 fai-erp-table">
                        <thead class="sticky-thead">
                            <tr>
                                <th style="width: 100px;">DATE</th>
                                <th style="width: 70px;">LOC.</th>
                                <th style="width: 100px;">WORK ID</th>
                                <th style="width: 100px;">PN</th>
                                <th style="width: 90px;">CO</th>
                                <th style="width: 120px;">CUST PO</th>
                                <th style="width: 200px;">DESCRIPTION</th>
                                <th style="width: 100px;">SAMP. PLAN</th>
                                <th style="width: 70px;">WO QTY</th>
                                <th style="width: 70px;">SAMP.</th>
                                <th style="width: 50px;">OPS.</th>
                                <th style="width: 40px;">FAI</th>
                                <th style="width: 40px;">IPI</th>
                                <th style="width: 100px;">PROGRESS</th>
                                <th style="width: 150px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
            </div>
        </div>

    </div>
    <div class="d-none">
        <form method="GET" action="{{ route('faisummary.completed') }}" id="filtersForm"></form>

        {{-- Formularios ocultos para enviar ids[] por POST --}}
        <form id="exportExcelForm" action="{{ route('faisummary.completed.export.excel') }}"
            method="POST" target="_blank" class="d-none">
            @csrf
        </form>
        <form id="exportPdfForm" action="{{ route('faisummary.completed.export.pdf') }}" method="POST"
            target="_blank" class="d-none">
            @csrf
        </form>
    </div>
</div>


<!--  {{-- Tab: By End Schedule --}}-->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    PDF Preview
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-0" style="height:80vh;">
                <embed id="pdfEmbed" src="" type="application/pdf" width="100%" height="100%">
            </div>
        </div>
    </div>
</div>



@endsection


@section('css')

<style>
    html {
        overflow-y: scroll;
        scrollbar-gutter: stable;
    }

    /* KPI cards ERP */
    .info-box-sm {
        min-height: 74px;
        padding: .42rem .58rem;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: .5rem;
        background: #fff !important;
        border: 1px solid #dbe3ee !important;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    }

    .info-box-sm .info-box-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        font-size: 1.12rem;
        line-height: 42px;
        border-radius: 13px;
        background: linear-gradient(180deg, #f8fafc 0%, #e8eef6 100%);
        border: 1px solid #d7dfeb;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255,255,255,.82);
    }

    .info-box-sm .info-box-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
    }

    .info-box-sm .info-box-text {
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: .08rem;
    }

    .info-box-sm h5 {
        font-weight: 900;
        font-size: 1.48rem;
        line-height: 1;
        color: #0f172a;
        margin-bottom: 0;
    }

    /* Color de íconos por variante */
    .info-box-sm.bg-secondary .info-box-icon {
        color: #495057 !important;
    }

    .info-box-sm.bg-success .info-box-icon {
        color: #0f7a48 !important;
    }

    .info-box-sm.bg-danger .info-box-icon {
        color: #c82333 !important;
    }

    .info-box-sm.bg-info .info-box-icon {
        color: #0d6efd !important;
    }

    /* Neutralizar fondo sólido de clases bg-* para usar el estilo ERP */
    /* Neutralizar fondo sólido de clases bg-* para usar el estilo ERP */
    .info-box.bg-secondary {
        background: rgba(108, 117, 125, 0.16) !important;
        border-color: rgba(108, 117, 125, 0.25) !important;
        color: #1f2937 !important;
    }

    .info-box.bg-success {
        background: rgba(25, 135, 84, 0.16) !important;
        border-color: rgba(25, 135, 84, 0.25) !important;
        color: #0f172a !important;
        cursor: pointer;
        user-select: none;
    }

    .info-box.bg-danger {
        background: rgba(220, 53, 69, 0.16) !important;
        border-color: rgba(220, 53, 69, 0.25) !important;
        color: #0f172a !important;
        cursor: pointer;
        user-select: none;
    }

    .info-box.bg-info {
        background: rgba(13, 110, 253, 0.16) !important;
        border-color: rgba(13, 110, 253, 0.25) !important;
        color: #0f172a !important;
    }

    /* Mantener fondo blanco aunque tengan bg-* por defecto */
    .info-box-sm.bg-success,
    .info-box-sm.bg-danger,
    .info-box-sm.bg-secondary,
    .info-box-sm.bg-info {
        background: #fff !important;
        border-color: #dbe3ee !important;
        color: #0f172a !important;
    }
    .info-box-sm::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: transparent;
    }
    .info-box-sm::before {
        content: '';
        position: absolute;
        right: 12px;
        top: 12px;
        width: 44px;
        height: 4px;
        border-radius: 999px;
        background: transparent;
    }
    .info-box-sm.bg-secondary::after,
    .info-box-sm.bg-secondary::before { background: rgba(108, 117, 125, 0.78); }
    .info-box-sm.bg-success::after,
    .info-box-sm.bg-success::before   { background: rgba(25, 135, 84, 0.82); }
    .info-box-sm.bg-danger::after,
    .info-box-sm.bg-danger::before    { background: rgba(220, 53, 69, 0.82); }
    .info-box-sm.bg-info::after,
    .info-box-sm.bg-info::before      { background: rgba(13, 110, 253, 0.82); }

    /* Activo: pinta el fondo suave al aplicar filtro */
    .info-box-sm.fai-filter-active.bg-success {
        background: linear-gradient(180deg, rgba(25, 135, 84, 0.10) 0%, rgba(25, 135, 84, 0.04) 100%) !important;
        border-color: rgba(25, 135, 84, 0.45) !important;
    }
    .info-box-sm.fai-filter-active.bg-danger {
        background: linear-gradient(180deg, rgba(220, 53, 69, 0.10) 0%, rgba(220, 53, 69, 0.04) 100%) !important;
        border-color: rgba(220, 53, 69, 0.45) !important;
    }
    .info-box-sm.fai-filter-active.bg-secondary {
        background: linear-gradient(180deg, rgba(108, 117, 125, 0.10) 0%, rgba(108, 117, 125, 0.04) 100%) !important;
        border-color: rgba(108, 117, 125, 0.45) !important;
    }
    .info-box-sm.fai-filter-active.bg-info {
        background: linear-gradient(180deg, rgba(13, 110, 253, 0.10) 0%, rgba(13, 110, 253, 0.04) 100%) !important;
        border-color: rgba(13, 110, 253, 0.45) !important;
    }

    .content-wrapper,
    .content {
        overflow: visible !important;
    }

    /* Evitar recorte en cards */
    .card,
    .card-body {
        overflow: visible;
    }

    /* Elevar el popup del datetimepicker (forzar sobre todo) */
    .bootstrap-datetimepicker-widget {
        z-index: 30000 !important;
    }

    .bootstrap-datetimepicker-widget.dropdown-menu {
        z-index: 30000 !important;
    }

    /* Forzar el widget fuera de contenedores para que no se recorte */
    body>.bootstrap-datetimepicker-widget {
        position: absolute !important;
    }

    /* Contenedor tabla estilo ERP */
    .fai-erp-wrap {
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0;
        box-shadow: none;
    }

    .fai-table-stage {
        position: relative;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        min-height: 24px;
    }

    /* Tabla ERP */
    #faicompleteTable {
        width: 100%;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
    }

    #faicompleteTable th,
    #faicompleteTable td {
        word-break: break-word;
    }

    /* Encabezado gris estilo ERP (como summary) */
    #faicompleteTable thead th {
        font-weight: 800;
        letter-spacing: 0.05em;
        color: #1f2937;
        background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
        border-bottom: 1px solid rgba(15, 23, 42, 0.14);
        padding: 0.55rem 0.7rem;
        vertical-align: middle;
        font-size: 0.95rem;
        text-transform: uppercase;
    }

    #faicompleteTable thead th:first-child {
        border-top-left-radius: 10px;
    }

    #faicompleteTable thead th:last-child {
        border-top-right-radius: 10px;
    }

    #faicompleteTable tbody td {
        padding: 0.45rem 0.7rem;
        vertical-align: middle;
        font-size: 0.95rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }

    #faicompleteTable tbody tr:hover {
        background: rgba(13, 110, 253, 0.05);
    }

    /* Zebra suave */
    .fai-erp-table tbody tr:nth-child(even) {
        background: rgba(249, 250, 251, 0.9);
    }

    /* Alineaciones */
    #faicompleteTable tbody td:nth-child(1),
    #faicompleteTable tbody td:nth-child(2),
    #faicompleteTable tbody td:nth-child(3),
    #faicompleteTable tbody td:nth-child(8),
    #faicompleteTable tbody td:nth-child(9),
    #faicompleteTable tbody td:nth-child(10),
    #faicompleteTable tbody td:nth-child(11),
    #faicompleteTable tbody td:nth-child(12),
    #faicompleteTable tbody td:nth-child(13),
    #faicompleteTable tbody td:nth-child(14) {
        text-align: center;
    }

    #faicompleteTable tbody td:nth-child(4),
    #faicompleteTable tbody td:nth-child(5),
    #faicompleteTable tbody td:nth-child(6),
    #faicompleteTable tbody td:nth-child(7),
    #faicompleteTable tbody td:nth-child(15) {
        text-align: left;
    }
    #faicompleteTable thead th:nth-child(15),
    #faicompleteTable tbody td:nth-child(15) {
        width: 150px !important;
        min-width: 150px !important;
        white-space: nowrap !important;
        overflow: visible !important;
    }

    /* Progress bar estilo ERP */
    .fai-erp-table .progress {
        height: 20px !important;
        border-radius: 10px;
        background: #eef2f7;
        border: 1px solid #d8e0ea;
        box-shadow: none;
        overflow: hidden;
    }
    .fai-erp-table .progress .progress-bar {
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: 0;
        box-shadow: none;
        background-image: none;
    }
    .fai-erp-table .progress .progress-bar.bg-success { background: #22c55e !important; }
    .fai-erp-table .progress .progress-bar.bg-info    { background: #38bdf8 !important; }
    .fai-erp-table .progress .progress-bar.bg-warning { background: #facc15 !important; }
    .fai-erp-table .progress .progress-bar.bg-danger  { background: #ef4444 !important; }

    /* Botones de acción estilo ERP */
    .fai-erp-table .btn-group.btn-group-sm .btn {
        background: #f8fafc !important;
        border: 1px solid #d5dbe3 !important;
        color: #1f2937 !important;
        border-radius: 9px !important;
        min-width: 34px;
        height: 32px;
        padding: 0 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        transition: transform .08s ease, box-shadow .12s ease, filter .12s ease;
    }
    .fai-erp-table .btn-group.btn-group-sm .btn i {
        font-size: 0.95rem;
        line-height: 1;
    }
    .fai-erp-table .btn-group.btn-group-sm .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.12);
        filter: brightness(1.02);
    }
    .fai-erp-table .btn-group.btn-group-sm .btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.10);
    }
    .fai-erp-table .btn-group.btn-group-sm .btn-danger i { color: #dc2626; }
    .fai-erp-table .btn-group.btn-group-sm .btn-info i   { color: #0ea5e9; }
    .fai-erp-table .btn-group.btn-group-sm .btn-warning i{ color: #f59e0b; }
    .fai-erp-table .btn-group.btn-group-sm .btn-edit-pdf i { color: #7c3aed !important; }
    .fai-erp-table .btn-group.btn-group-sm .btn-edit-row i { color: #2563eb !important; }

    /* Paginado estilo ERP */
    .dataTables_wrapper .dataTables_paginate {
        margin-top: -10px !important;
        padding-top: 0 !important;
        border-top: 0 !important;
        margin-left: auto !important;
        text-align: right !important;
    }

    .dataTables_wrapper .dataTables_paginate .pagination {
        margin: 0 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid rgba(15, 23, 42, 0.18) !important;
        background: rgba(241, 245, 249, 0.95) !important;
        color: #0f172a !important;
        margin: 0 0.12rem !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
        transition: background-color .12s ease, transform .08s ease, box-shadow .12s ease;
        border-radius: 0.55rem !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button .page-link {
        padding: 0.34rem 0.68rem !important;
        font-size: 0.95rem !important;
        line-height: 1.4 !important;
        border: none !important;
        background: transparent !important;
        color: inherit !important;
        border-radius: 0.5rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(226, 232, 240, 1) !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(16, 24, 40, 0.10);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.active,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0b5ed7 !important;
        border-color: #0b5ed7 !important;
        color: #fff !important;
        font-weight: 700;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.active .page-link,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current .page-link {
        color: #fff !important;
        background: transparent !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.active:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #0a58ca !important;
        border-color: #0a58ca !important;
        transform: none;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        opacity: 0.5;
        transform: none;
        box-shadow: none;
        cursor: default !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    /* Alinear info y paginado en la misma línea */
    .dataTables_wrapper .row:last-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: -12px !important;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        float: none !important;
    }

    .dataTables_wrapper .row:last-child>div {
        display: flex;
        align-items: center;
        flex: 1 1 auto;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .dataTables_wrapper .row:last-child>div:first-child {
        justify-content: flex-start;
    }

    .dataTables_wrapper .row:last-child>div:last-child {
        justify-content: flex-end;
        margin-left: auto !important;
        flex: 0 0 auto !important;
    }

    /* Alinear fila superior (Show entries + Search) */
    .dataTables_wrapper .row:first-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: nowrap;
        gap: 0.4rem;
        margin-bottom: 0.35rem;
        width: 100%;
    }
    .dataTables_wrapper .row:first-child > div {
        flex: 0 0 auto;
        width: auto !important;
    }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        float: none !important;
        margin: 0 !important;
        padding: 0 !important;
        width: auto !important;
        display: flex;
        align-items: center;
        flex: 0 0 auto;
    }
    .dataTables_wrapper .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin: 0;
        white-space: nowrap;
        flex-wrap: nowrap;
    }
    .dataTables_wrapper .dataTables_filter {
        margin-left: auto !important;
        text-align: right !important;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    .dataTables_wrapper .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin: 0;
    }
    .dataTables_wrapper .dataTables_filter input {
        height: 32px;
        padding: .25rem .5rem;
        border-radius: 8px;
    }
    .dataTables_wrapper .dataTables_length select {
        height: 32px;
        padding: .25rem .4rem;
        border-radius: 8px;
        min-width: 90px;
        width: auto;
    }

    /* Botones gris ERP para export */
    .btn-erp-gray {
        background: linear-gradient(180deg, #eef1f5 0%, #d9dde3 100%) !important;
        border: 1px solid #c5c9d2 !important;
        color: #1f2937 !important;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
    }
    .btn-erp-gray:hover {
        background: linear-gradient(180deg, #e2e6ed 0%, #cfd4db 100%) !important;
        color: #0f172a !important;
    }
    .btn-erp-gray:active,
    .btn-erp-gray:focus {
        box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.12);
    }

    .fai-table-toolbar {
        display: flex;
        flex-direction: column;
        gap: .45rem;
    }

    .fai-table-toolbar .toolbar-top,
    .fai-table-toolbar .toolbar-bottom,
    .fai-table-toolbar .toolbar-left {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .fai-table-toolbar .toolbar-top {
        align-items: center;
        justify-content: space-between;
    }

    .fai-table-toolbar .toolbar-left {
        align-items: center;
    }

    .fai-table-toolbar .toolbar-bottom {
        align-items: center;
        justify-content: flex-start;
    }

    .fai-table-toolbar .toolbar-filters .form-group {
        margin-bottom: 0 !important;
        min-width: 160px;
    }

    .fai-table-toolbar .toolbar-filters label {
        display: none !important;
    }

    .fai-table-toolbar .input-group,
    .fai-table-toolbar .btn-group,
    .fai-table-toolbar .dataTables_length {
        margin-bottom: 0 !important;
    }

    .fai-table-toolbar .dataTables_length label {
        margin-bottom: 0 !important;
    }

    .fai-table-toolbar .toolbar-search {
        width: 320px;
        max-width: 100%;
    }

    .fai-table-toolbar .btn-erp-active {
        background: linear-gradient(180deg, #e7f6ee 0%, #d4eddf 100%) !important;
        border: 1px solid #9fd0b1 !important;
        color: #1f5d3f !important;
        box-shadow: 0 1px 3px rgba(31, 93, 63, 0.14);
    }

    .fai-table-toolbar .btn-erp-active:hover,
    .fai-table-toolbar .btn-erp-active:focus,
    .fai-table-toolbar .btn-erp-active:active {
        background: linear-gradient(180deg, #dcf1e5 0%, #cbe7d8 100%) !important;
        color: #174b32 !important;
        border-color: #96c9aa !important;
        box-shadow: 0 0 0 2px rgba(52, 168, 83, 0.2) !important;
    }

    .fai-table-toolbar .btn.btn-sm {
        min-height: 36px;
        padding: .3rem .68rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        line-height: 1.1;
    }

    .fai-table-toolbar .btn-outline-secondary {
        color: #0f172a;
        border-color: #bfc9d6;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }

    .fai-table-toolbar .btn-outline-secondary:hover {
        background: rgba(13, 110, 253, 0.08);
        color: #0d6efd;
        border-color: #9fb7d9;
    }

    .fai-table-toolbar #filterLocationGroup .input-group,
    .fai-table-toolbar #yearPickerWrapper,
    .fai-table-toolbar #monthPickerWrapper,
    .fai-table-toolbar #dayPickerWrapper {
        height: 36px;
        border: 1px solid #bfc9d6;
        border-radius: 10px;
        overflow: visible;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        align-items: stretch;
        padding: 0;
    }

    .fai-table-toolbar #filterLocationGroup .input-group:focus-within,
    .fai-table-toolbar #yearPickerWrapper:focus-within,
    .fai-table-toolbar #monthPickerWrapper:focus-within,
    .fai-table-toolbar #dayPickerWrapper:focus-within {
        border-color: #5b8ee6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.16);
    }

    .fai-table-toolbar #filterLocationGroup .input-group-text,
    .fai-table-toolbar #yearPickerWrapper .input-group-text,
    .fai-table-toolbar #monthPickerWrapper .input-group-text,
    .fai-table-toolbar #dayPickerWrapper .input-group-text {
        border: 0 !important;
        border-right: 1px solid #d8e0ea !important;
        background: #eef2f7 !important;
        color: #334155 !important;
        min-width: 36px;
        height: 100%;
        justify-content: center;
        align-items: center;
        display: inline-flex;
        margin: 0 !important;
        border-radius: 10px 0 0 10px !important;
    }

    .fai-table-toolbar #filterLocationGroup .form-control,
    .fai-table-toolbar #yearPickerWrapper .form-control,
    .fai-table-toolbar #monthPickerWrapper .form-control,
    .fai-table-toolbar #dayPickerWrapper .form-control {
        border: 0 !important;
        box-shadow: none !important;
        height: 100% !important;
        padding: .3rem .55rem;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.2;
        background: #fff !important;
        margin: 0 !important;
        border-radius: 0 10px 10px 0 !important;
        outline: none !important;
    }

    .fai-table-toolbar #locationFilter,
    .fai-table-toolbar #year,
    .fai-table-toolbar #monthDisplay,
    .fai-table-toolbar #day {
        border: 0 !important;
        background-image: none !important;
        -webkit-appearance: none;
    }

    .fai-table-toolbar .toolbar-search {
        border: 1px solid #bfc9d6;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        transition: border-color .15s ease, box-shadow .15s ease;
        min-height: 36px;
    }

    .fai-table-toolbar .toolbar-search:focus-within {
        border-color: #5b8ee6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.16);
    }

    .fai-table-toolbar .toolbar-search .input-group-text {
        border: 0 !important;
        border-right: 1px solid #d8e0ea !important;
        background: #eef2f7 !important;
        color: #334155 !important;
        min-width: 36px;
        min-height: 36px;
        justify-content: center;
    }

    .fai-table-toolbar .toolbar-search .form-control {
        border: 0 !important;
        box-shadow: none !important;
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        padding: .3rem .58rem;
        min-height: 36px;
        background: #fff !important;
    }

    .fai-table-toolbar .toolbar-search .form-control::placeholder {
        color: #64748b;
        font-weight: 500;
    }

    .fai-table-toolbar .toolbar-search #dtToolbarClear {
        border: 0 !important;
        border-left: 1px solid #d8e0ea !important;
        background: #f8fafc !important;
        color: #475569 !important;
        min-width: 36px;
        min-height: 36px;
        padding: .28rem .52rem;
    }

    .fai-table-toolbar .toolbar-search #dtToolbarClear:hover {
        background: #eef2f7 !important;
        color: #0f172a !important;
    }

    /* Texto de encabezados en negro (consistente) */
    #faicompleteTable thead th,
    .fai-erp-table thead th,
    .table thead th {
        color: #0b0b0b !important;
    }
</style>

@endsection


@push('js')
<script src="{{ asset('vendor/js/date-filters.js') }}"></script>
<script>
    /* ===========================
     *  Modal PDF
     * =========================== */
    $(document)
        .on('click', '.btn-open-pdf', function(e) {
            e.preventDefault();
            const url = $(this).data('pdf-url');
            $('#pdfEmbed').attr('src', url + '#zoom=page-width');
            $('#pdfModal').modal('show');
        });
    $('#pdfModal').on('hidden.bs.modal', function() {
        $('#pdfEmbed').attr('src', '');
    });

    /* ===========================
     *  DataTable + Filtros + Export + KPIs
     * =========================== */
    $(function() {
        $.fn.dataTable.ext.errMode = 'throw';


        const $tbl = $('#faicompleteTable');
        if (!$tbl.length) return;
        let filterOnlyCompleted = false;
        let filterOnlyIncomplete = false;
        let filterOnlyNoInspection = false;

        // Destruye si existía
        if ($.fn.DataTable.isDataTable($tbl)) $tbl.DataTable().destroy();

        const dt = $tbl.DataTable({
            processing: false,
            serverSide: true,
            searching: true,
            ordering: false,
            pageLength: 10,
            searchDelay: 250,
            scrollX: false,
            autoWidth: false,
            rowId: 'row_id',
            dom: 'rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
            ajax: {
                url: '{{ route('faisummary.completed.data') }}',
                type: 'GET',
                data: function(d) {
                    d.year = ($('#year').val() || '').trim();
                    d.month = ($('#month').val() || '').trim();
                    d.day = ($('#day').val() || '').trim();
                    d.location = ($('#locationFilter').val() || '').trim();
                    d.only_completed = filterOnlyCompleted ? 1 : 0;
                    d.only_incomplete = filterOnlyIncomplete ? 1 : 0;
                    d.only_no_inspection = filterOnlyNoInspection ? 1 : 0;
                }
            },
            columns: [
                { data: 'date', name: 'inspection_endate' },
                { data: 'location', name: 'location' },
                { data: 'work_id', name: 'work_id' },
                { data: 'pn', name: 'PN' },
                { data: 'co', name: 'co' },
                { data: 'cust_po', name: 'cust_po' },
                { data: 'description', name: 'Part_description' },
                { data: 'sampling_check', name: 'sampling_check' },
                { data: 'group_wo_qty', name: 'group_wo_qty', className: 'text-center' },
                { data: 'sampling', name: 'sampling', className: 'text-center' },
                { data: 'operation', name: 'operation', className: 'text-center' },
                { data: 'total_fai', name: 'total_fai', className: 'text-center' },
                { data: 'total_ipi', name: 'total_ipi', className: 'text-center' },
                { data: 'progress', name: 'progress', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            initComplete: function(settings, json) {
                applyKpis(json?.meta || null);
                syncKpiActive();
            },
            drawCallback: function() {
                const json = this.api().ajax.json();
                applyKpis(json?.meta || null);
                syncKpiActive();
            }
        });
        $('#locationFilter option[value=""]').text('- All -');

        const serverYear = @json((string) request('year', now()->year));
        const serverMonth = @json((string) request('month', ''));
        const serverDay = @json(request('day') ? \Carbon\Carbon::parse(request('day'))->format('Y-m-d') : '');

        function syncVisibleDateInputs() {
            if (serverYear || $('#year').val()) {
                $('#year').val(serverYear || $('#year').val());
            }
            if (serverMonth || $('#month').val()) {
                const mm = serverMonth || $('#month').val();
                if (mm) $('#monthDisplay').val(moment(mm, 'M').format('MMM'));
            }
            if (serverDay || $('#day').val()) {
                $('#day').val(serverDay || $('#day').val());
            }
        }

        syncVisibleDateInputs();

        $('#dtToolbarSearch').on('input', function() {
            dt.search(this.value || '').page('first').draw('page');
        });
        $('#dtToolbarClear').on('click', function() {
            $('#dtToolbarSearch').val('');
            dt.search('').page('first').draw('page');
            $('#dtToolbarSearch').trigger('focus');
        });
        $('#toolbarExportExcel').on('click', function() { submitExport('exportExcelForm'); });
        $('#toolbarExportPdf').on('click', function() { submitExport('exportPdfForm'); });

        function initToolbarDateFilters() {
            ['#yearPickerWrapper', '#monthPickerWrapper', '#dayPickerWrapper'].forEach(function(sel) {
                const $w = $(sel);
                if (!$w.length) return;
                $w.removeData('df-initialized');
                try {
                    $w.datetimepicker('destroy');
                } catch (_) {}
            });

            const $form = $('#filtersForm');
            const $yearW = $('#yearPickerWrapper');
            const $monthW = $('#monthPickerWrapper');
            const $dayW = $('#dayPickerWrapper');
            const $year = $('#year');
            const $month = $('#month');
            const $monthDisplay = $('#monthDisplay');
            const $day = $('#day');

            if ($yearW.length) {
                $yearW.datetimepicker({
                    format: 'YYYY',
                    viewMode: 'years',
                    useCurrent: false,
                    keepOpen: false
                });

                if (($year.val() || '').trim()) {
                    $yearW.datetimepicker('date', moment($year.val(), 'YYYY'));
                    $year.val(moment($year.val(), 'YYYY').format('YYYY'));
                }

                $yearW.off('.erpYear')
                    .on('change.datetimepicker.erpYear', function(e) {
                        if (!e.date) {
                            $year.val('');
                            return;
                        }
                        $year.val(e.date.format('YYYY'));
                        $(this).find('input').val(e.date.format('YYYY'));
                        $day.val('');
                    })
                    .on('hide.datetimepicker.erpYear', function() {
                        $(this).find('input').val($year.val());
                        $form.trigger('submit');
                    });
            }

            if ($monthW.length) {
                $monthW.datetimepicker({
                    format: 'MMM',
                    viewMode: 'months',
                    useCurrent: false,
                    keepOpen: false
                });

                if (($month.val() || '').trim()) {
                    const y = ($year.val() || moment().format('YYYY'));
                    const monthMoment = moment(`${y}-${String($month.val()).padStart(2, '0')}-01`, 'YYYY-MM-DD');
                    $monthW.datetimepicker('date', monthMoment);
                    $monthDisplay.val(monthMoment.format('MMM'));
                } else {
                    $monthDisplay.val('');
                }

                $monthW.off('.erpMonth')
                    .on('change.datetimepicker.erpMonth', function(e) {
                        if (!e.date) {
                            $month.val('');
                            $monthDisplay.val('');
                            return;
                        }
                        $month.val(e.date.format('M'));
                        $monthDisplay.val(e.date.format('MMM'));
                        $(this).find('input').val(e.date.format('MMM'));
                        $year.val(e.date.format('YYYY'));
                        $day.val('');
                    })
                    .on('hide.datetimepicker.erpMonth', function() {
                        $(this).find('input').val($monthDisplay.val());
                        $form.trigger('submit');
                    });
            }

            if ($dayW.length) {
                $dayW.datetimepicker({
                    format: 'YYYY-MM-DD',
                    viewMode: 'days',
                    useCurrent: false,
                    keepOpen: false
                });

                if (($day.val() || '').trim()) {
                    const dayMoment = moment($day.val(), 'YYYY-MM-DD');
                    $dayW.datetimepicker('date', dayMoment);
                    $day.val(dayMoment.format('YYYY-MM-DD'));
                }

                $dayW.off('.erpDay')
                    .on('change.datetimepicker.erpDay', function(e) {
                        if (!e.date) {
                            $day.val('');
                            return;
                        }
                        $day.val(e.date.format('YYYY-MM-DD'));
                        $(this).find('input').val(e.date.format('YYYY-MM-DD'));
                        $year.val(e.date.format('YYYY'));
                        $month.val(e.date.format('M'));
                        $monthDisplay.val(e.date.format('MMM'));
                    })
                    .on('hide.datetimepicker.erpDay', function() {
                        $(this).find('input').val($day.val());
                        $form.trigger('submit');
                    });
            }

            syncVisibleDateInputs();
        }

        initToolbarDateFilters();

        window.addEventListener('load', function() {
            try {
                const cleanUrl = new URL(`{{ route('faisummary.completed') }}`, window.location.origin);
                cleanUrl.searchParams.set('year', '{{ now()->year }}');
                window.history.replaceState({}, '', cleanUrl.toString());
            } catch (_) {}
        }, { once: true });

        window.faiDT = dt; // útil en consola

        function bindExactFilter(selectId) {
            const el = document.getElementById(selectId);
            if (!el) return;
            el.addEventListener('change', function() {
                dt.page('first').draw('page');
            });
        }

        bindExactFilter('locationFilter');



        /* ---------------------------
         * Fechas (opcional)
         * --------------------------- */
        // Al mover los filtros al toolbar, forzamos la apertura manual del calendario
        ['#yearPickerWrapper', '#monthPickerWrapper', '#dayPickerWrapper'].forEach(function(sel) {
            $(document).on('click', `${sel}, ${sel} .input-group-text, ${sel} input`, function(e) {
                e.preventDefault();
                e.stopPropagation();
                try {
                    $(sel).datetimepicker('show');
                } catch (_) {}
            });
        });

        /* ---------------------------
         * KPIs: 100% completados vs <100% (incompletos)
         * --------------------------- */
        const $kpiTotal = $('#kpiTotal'); // visibles
        const $kpiPass = $('#kpiPass'); // 100%
        const $kpiFail = $('#kpiFail'); // <100%
        const $kpiNoInspection = $('#kpiNoInspection'); // sin inspección

        function applyKpis(meta) {
            const k = meta?.kpis;
            if (!k) return;
            $kpiTotal.text(k.total ?? 0);
            $kpiPass.text(k.completed ?? 0);
            $kpiFail.text(k.incomplete ?? 0);
            $kpiNoInspection.text(k.no_inspection ?? 0);
        }

        function syncKpiActive() {
            $('#kpiBoxTotal').toggleClass('fai-filter-active', !filterOnlyCompleted && !filterOnlyIncomplete && !filterOnlyNoInspection);
            $('.info-box-sm.bg-success').toggleClass('fai-filter-active', filterOnlyCompleted);
            $('.info-box-sm.bg-danger').toggleClass('fai-filter-active', filterOnlyIncomplete);
            $('#kpiBoxNoInspection').toggleClass('fai-filter-active', filterOnlyNoInspection);
        }




        // Toggles KPI filters: Completed 100%, Incomplete, and No Inspection
        const $kpiBoxTotal = $('#kpiBoxTotal');
        const $kpiBoxCompleted = $('.info-box-sm.bg-success');
        const $kpiBoxIncomplete = $('.info-box-sm.bg-danger');
        const $kpiBoxNoInspection = $('#kpiBoxNoInspection');


        function toggleCompleted() {
            filterOnlyCompleted = !filterOnlyCompleted;
            if (filterOnlyCompleted) {
                filterOnlyNoInspection = false;
                filterOnlyIncomplete = false;
                $kpiBoxNoInspection.removeClass('fai-filter-active');
                $kpiBoxIncomplete.removeClass('fai-filter-active');
                $kpiBoxTotal.removeClass('fai-filter-active');
            }
            $kpiBoxCompleted.toggleClass('fai-filter-active', filterOnlyCompleted);
            dt.draw();
        }

        function toggleIncomplete() {
            filterOnlyIncomplete = !filterOnlyIncomplete;
            if (filterOnlyIncomplete) {
                filterOnlyCompleted = false;
                filterOnlyNoInspection = false;
                $kpiBoxCompleted.removeClass('fai-filter-active');
                $kpiBoxNoInspection.removeClass('fai-filter-active');
                $kpiBoxTotal.removeClass('fai-filter-active');
            }
            $kpiBoxIncomplete.toggleClass('fai-filter-active', filterOnlyIncomplete);
            dt.draw();
        }

        function toggleNoInspection() {
            filterOnlyNoInspection = !filterOnlyNoInspection;
            if (filterOnlyNoInspection) {
                filterOnlyCompleted = false;
                filterOnlyIncomplete = false;
                $kpiBoxCompleted.removeClass('fai-filter-active');
                $kpiBoxIncomplete.removeClass('fai-filter-active');
                $kpiBoxTotal.removeClass('fai-filter-active');
            }
            $kpiBoxNoInspection.toggleClass('fai-filter-active', filterOnlyNoInspection);
            dt.draw();
        }

        function showAllFromTotal() {
            filterOnlyCompleted = false;
            filterOnlyIncomplete = false;
            filterOnlyNoInspection = false;
            $kpiBoxCompleted.removeClass('fai-filter-active');
            $kpiBoxIncomplete.removeClass('fai-filter-active');
            $kpiBoxNoInspection.removeClass('fai-filter-active');
            $kpiBoxTotal.addClass('fai-filter-active');
            dt.draw();
        }

        if ($kpiBoxCompleted.length) {
            $kpiBoxCompleted.css('cursor', 'pointer').on('click', toggleCompleted);
        }
        if ($kpiBoxIncomplete.length) {
            $kpiBoxIncomplete.css('cursor', 'pointer').on('click', toggleIncomplete);
        }
        if ($kpiBoxNoInspection.length) {
            $kpiBoxNoInspection.css('cursor', 'pointer').on('click', toggleNoInspection);
        }
        if ($kpiBoxTotal.length) {
            $kpiBoxTotal.css('cursor', 'pointer').on('click', showAllFromTotal);
            // Estado inicial: mostrar todos y resaltar "Closed inspections"
            showAllFromTotal();
        }

        /* ---------------------------
         * Export (Excel / PDF) con filtros aplicados
         * --------------------------- */

        function submitExport(formId) {
            const $form = $('#' + formId);
            $form.find('input[name="ids[]"], input[name="q"], input[name="year"], input[name="month"], input[name="day"], input[name="location"], input[name="only_completed"], input[name="only_incomplete"], input[name="only_no_inspection"]')
                .remove();

            $form.append($('<input>', {
                type: 'hidden',
                name: 'q',
                value: dt.search() || ''
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'year',
                value: $('#year').val() || ''
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'month',
                value: $('#month').val() || ''
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'day',
                value: $('#day').val() || ''
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'location',
                value: $('#locationFilter').val() || ''
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'only_completed',
                value: filterOnlyCompleted ? 1 : 0
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'only_incomplete',
                value: filterOnlyIncomplete ? 1 : 0
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'only_no_inspection',
                value: filterOnlyNoInspection ? 1 : 0
            }));

            $form.trigger('submit');
        }

    });

    /* ===========================
     *  Botón "Move to progress"
     * =========================== */
    $(document).on('click', '.btn-edit-pdf', function(e) {
        e.preventDefault();

        const orderId = $(this).data('id');
        const $btn = $(this); // para identificar la fila

        Swal.fire({
            title: '¿Move to progress?',
            text: "The inspection will change status to 'In Progress'.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Yes, Continue'
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch(`/orders-schedule/${orderId}/status-inspection`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status_inspection: 'in_progress'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Updated!',
                        text: 'Inspection moved to In Progress.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // === 1️⃣ Eliminar la fila de la tabla manualmente ===
                    const dt = $('#faicompleteTable').DataTable();

                    // Detecta la fila (por si DataTables usa modo responsive)
                    const tr = $btn.closest('tr');
                    const row = dt.row(tr.hasClass('child') ? tr.prev() : tr);
                    row.remove().draw(false);


                    // === 3️⃣ Sincronizar con otras pestañas (opcional) ===
                    try {
                        localStorage.setItem('faisummary_update', JSON.stringify({
                            id: orderId,
                            status: 'in_progress',
                            timestamp: Date.now()
                        }));
                    } catch (e) {}
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Hubo un problema en el servidor', 'error');
                });
        });
    });

    // Mover los widgets datetimepicker al body para evitar recortes
    $(document).on('dp.show', function() {
        const $widget = $('.bootstrap-datetimepicker-widget').last();
        if ($widget.length) {
            $widget.appendTo('body');
        }
    });
</script>





@endpush
