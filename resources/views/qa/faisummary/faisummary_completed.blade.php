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

<div class="row">


    {{-- Columna derecha: Tabla --}}
    <div class="col-lg-12">
        <div class="card mb-3 fai-summary-card fai-summary-card-top">
            <div class="card-body p-2">
                <div class="row">
                    <div class="col-md-6 col-xl-3 mb-2 mb-xl-0">
                        <div class="info-box info-box-sm bg-info mb-0" id="kpiBoxTotal">
                            <span class="info-box-icon"><i class="fas fa-clipboard-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Closed inspections</span>
                                <h5 class="mb-0" id="kpiTotal">0</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-2 mb-xl-0">
                        <div class="info-box info-box-sm bg-success mb-0">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Completed</span>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0" id="kpiPass">0</h5>
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
                                    <h5 class="mb-0" id="kpiFail">0</h5>
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
                                    <h5 class="mb-0" id="kpiNoInspection">0</h5>
                                    <small class="text-black-50">ops/samp 0</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">

            <div class="card-body p-2">
                <div id="dtToolbarContainer" class="mb-2"></div>
                <div class="table-responsive fai-erp-wrap">
                    <table id="faicompleteTable"
                        class="table table-sm align-middle mb-0 fai-erp-table"
                        style="table-layout: fixed; width: 100%;">
                        <thead class="sticky-thead">
                            <tr>
                                <th style="width: 100px;">DATE</th>
                                <th style="width: 70px;">LOC.</th>
                                <th style="width: 100px;">WORK ID</th>
                                <th style="width: 100px;">PN</th>
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
                            @forelse($orderscompleted as $o)
                            @php
                            // Requerimientos
                            $faiReqPcs = (int) ($o->total_fai ?? 0);
                            $ipiReqPcs = (int) ($o->total_ipi ?? 0);

                            // Piezas aprobadas
                            $faiPassQty = (int) ($o->fai_pass_qty ?? 0);
                            $ipiPassQty = (int) ($o->ipi_pass_qty ?? 0);

                            // % individuales (solo si hay requerimiento)
                            $faiPct = $faiReqPcs > 0 ? (int) round(($faiPassQty / $faiReqPcs) * 100) : null;
                            $ipiPct = $ipiReqPcs > 0 ? (int) round(($ipiPassQty / $ipiReqPcs) * 100) : null;

                            // ===== Overall por PROMEDIO PONDERADO =====
                            // (pasa_totales / requeridos_totales) * 100
                            $totalReq = $faiReqPcs + $ipiReqPcs;
                            $overall = $totalReq > 0
                            ? (int) round((($faiPassQty + $ipiPassQty) / $totalReq) * 100)
                            : 100;

                            // Completado = ambas metas alcanzadas (mantengo tu regla actual)
                            $completed = ($faiReqPcs === 0 || $faiPassQty >= $faiReqPcs)
                            && ($ipiReqPcs === 0 || $ipiPassQty >= $ipiReqPcs);

                            // Clase de la barra según el overall
                            $barClass = $completed ? 'bg-success'
                            : ($overall >= 75 ? 'bg-info'
                            : ($overall >= 50 ? 'bg-warning' : 'bg-danger'));
                            @endphp
                            <tr id="row-{{ $o->id }}" data-progress="{{ $overall }}"
                                data-completed="{{ $completed ? 1 : 0 }}">
                                <td>
                                    {{ optional($o->inspection_endate)->format('M-d-y') }}
                                    @if($o->inspection_endate)
                                    <span class="badge badge-light">
                                        {{ $o->inspection_endate->format('H:i') }}
                                    </span>
                                    @endif
                                </td>
                                <td>{{ ucfirst($o->location) }}</td>
                                <td>{{ $o->work_id }}</td>
                                <td>{{ $o->PN }}</td>
                                <td class="td-ellipsis" title="{{ $o->Part_description }}">
                                    {{ \Illuminate\Support\Str::before($o->Part_description, ',') }}
                                </td>
                                <td class="text-center">{{ ucfirst($o->sampling_check) }}</td>
                                <td class="text-center">{{ $o->group_wo_qty }}</td>
                                <td class="text-center">{{ $o->sampling }}</td>
                                <td class="text-center">{{ $o->operation }}</td>
                                <td class="text-center">{{ $o->total_fai }}</td>
                                <td class="text-center">{{ $o->total_ipi }}</td>
                                {{-- Columna PROGRESO --}}
                                <td>
                                    <div class="progress" style="height:18px;"
                                        title="FAI {{ $faiPassQty }}/{{ $faiReqPcs }} ({{ $faiPct !== null ? $faiPct : 100 }}%) • IPI {{ $ipiPassQty }}/{{ $ipiReqPcs }} ({{ $ipiPct !== null ? $ipiPct : 100 }}%)">
                                        <div class="progress-bar {{ $barClass }}" style="width: {{ $overall }}%;"
                                            aria-valuenow="{{ $overall }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ $overall }}%
                                        </div>
                                    </div>

                                    @if($completed)
                                    <span class="badge badge-success mt-1"><i class="fas fa-check"></i> Done</span>
                                    @else
                                    <small class="text-muted d-block mt-1">
                                        FAI {{ $faiPassQty }}/{{ $faiReqPcs }} • IPI {{ $ipiPassQty }}/{{ $ipiReqPcs }}
                                    </small>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="#" class="btn btn-danger btn-open-pdf"
                                            data-pdf-url="{{ route('qa.faisummary.pdf', $o->id) }}">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="{{ route('qa.faisummary.pdf', $o->id) }}?download=1"
                                            class="btn btn-info">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="#" class="btn btn-warning btn-edit-pdf" data-id="{{ $o->id }}">
                                            <i class="fas fa-reply"></i>
                                        </a>
                                        <a href="{{ route('faisummary.completed.events', $o->id) }}" class="btn btn-primary btn-edit-row" data-id="{{ $o->id }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="d-none">
        <form method="GET" action="{{ route('faisummary.completed') }}" id="filtersForm">
                            {{-- Global Search --}}

                            {{-- Location --}}
                            <div class="form-group mb-2" id="filterLocationGroup">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i
                                                class="fas fa-map-marker-alt text-danger"></i></span>
                                    </div>
                                    <select id="locationFilter" class="form-control dt-filter" name="location">
                                        <option value="">— All —</option>
                                    </select>
                                </div>
                            </div>

                            {{-- YEAR --}}
                            <div class="form-group mb-2" id="filterYearGroup">
                                <label for="year">Date</label>
                                <div class="input-group input-group date" id="yearPickerWrapper"
                                    data-target-input="nearest" data-initial-year="{{ request('year') ?? '' }}"
                                    style="min-width:160px">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-calendar-alt text-success"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="year" name="year" class="form-control datetimepicker-input"
                                        data-toggle="datetimepicker" data-target="#yearPickerWrapper"
                                        value="{{ request('year') }}" placeholder="Year" autocomplete="off">
                                </div>
                            </div>

                            {{-- MONTH --}}
                            <div class="form-group mb-2" id="filterMonthGroup">
                                <label for="monthDisplay" class="mb-1 sr-only">Month</label>
                                <div class="input-group input-group date" id="monthPickerWrapper"
                                    data-target-input="nearest" style="min-width:160px">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-calendar-alt text-danger"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="monthDisplay" class="form-control datetimepicker-input"
                                        data-toggle="datetimepicker" data-target="#monthPickerWrapper"
                                        placeholder="Month" autocomplete="off">
                                </div>
                                <input type="hidden" id="month" name="month" value="{{ request('month') }}">
                            </div>

                            {{-- DAY --}}
                            <div class="form-group mb-2" id="filterDayGroup">
                                <label for="day" class="mb-1 sr-only">Day</label>
                                <div class="input-group input-group date" id="dayPickerWrapper"
                                    data-target-input="nearest" style="min-width:180px">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-calendar-day text-warning"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="day" name="day" class="form-control datetimepicker-input"
                                        data-toggle="datetimepicker" data-target="#dayPickerWrapper"
                                        value="{{ request('day') ? \Carbon\Carbon::parse(request('day'))->format('Y-m-d') : '' }}"
                                        placeholder="Day" autocomplete="off">
                                </div>
                            </div>

                            {{-- Clean + Total --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <a href="{{ route('faisummary.completed') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eraser mr-1"></i> Clean
                                </a>
                                <span class="badge badge-info py-2 px-3" style="font-size: 1rem;">
                                    <i class="fas fa-list-ol mr-1"></i>
                                    Total: <span
                                        id="badgeFinished">{{ isset($orderscompleted) ? $orderscompleted->count() : 0 }}</span>

                                </span>
                            </div>

                            {{-- Quick actions --}}
                            <div class="btn-group btn-group-sm d-flex mb-2">
                                <a class="btn btn-outline-secondary flex-fill"
                                    href="{{ route('faisummary.completed', array_merge(request()->except(['day','month','year','page']), ['day'=>now()->toDateString()])) }}">
                                    <i class="fas fa-bolt mr-1"></i> Today
                                </a>
                                <a class="btn btn-outline-secondary flex-fill"
                                    href="{{ route('faisummary.completed', array_merge(request()->except(['day','page']), ['year'=>now()->year,'month'=>now()->month])) }}">
                                    <i class="far fa-calendar-alt mr-1"></i> Month
                                </a>
                                <a class="btn btn-outline-secondary flex-fill"
                                    href="{{ route('faisummary.completed', array_merge(request()->except(['day','month','page']), ['year'=>now()->year])) }}">
                                    <i class="far fa-calendar mr-1"></i> Year
                                </a>
                            </div>

                            {{-- Export botones debajo de los quick filters --}}
                            <div class="btn-group btn-group-sm d-flex mb-2">
                                <button id="btnExportExcel" type="button" class="btn btn-erp-gray flex-fill">
                                    <i class="fas fa-file-excel mr-1 text-success"></i> Excel
                                </button>
                                <button id="btnExportPdf" type="button" class="btn btn-erp-gray flex-fill">
                                    <i class="fas fa-file-pdf mr-1 text-danger"></i> PDF
                                </button>
                            </div>
        </form>

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
    /* Summary KPI compact */
    .info-box-sm {
        min-height: 48px;
        padding: .3rem .45rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
    }

    .info-box-sm .info-box-icon {
        width: 36px;
        height: 36px;
        font-size: 22px;
        line-height: 36px;
        border-radius: 7px;
        background: rgba(255, 255, 255, 0.7);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    .info-box-sm .info-box-text {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: .01em;
    }

    .info-box-sm h5 {
        font-weight: 800;
        font-size: 1.05rem;
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

    /* Overrides: info-box-sm sin fondo de color, solo línea inferior; se colorea al activar */
    .info-box-sm {
        background: #fff !important;
        border: 1px solid #e5e7eb !important;
        position: relative;
        overflow: hidden;
    }
    /* Mantener fondo blanco aunque tengan bg-* por defecto */
    .info-box-sm.bg-success,
    .info-box-sm.bg-danger,
    .info-box-sm.bg-secondary,
    .info-box-sm.bg-info {
        background: #fff !important;
        border-color: #e5e7eb !important;
        color: #0f172a !important;
    }
    .info-box-sm::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        background: transparent;
    }
    .info-box-sm.bg-secondary::after { background: rgba(108, 117, 125, 0.7); }
    .info-box-sm.bg-success::after   { background: rgba(25, 135, 84, 0.7); }
    .info-box-sm.bg-danger::after    { background: rgba(220, 53, 69, 0.7); }
    .info-box-sm.bg-info::after      { background: rgba(13, 110, 253, 0.7); }

    /* Activo: pinta el fondo suave al aplicar filtro */
    .info-box-sm.fai-filter-active.bg-success {
        background: rgba(25, 135, 84, 0.12) !important;
        border-color: rgba(25, 135, 84, 0.45) !important;
    }
    .info-box-sm.fai-filter-active.bg-danger {
        background: rgba(220, 53, 69, 0.12) !important;
        border-color: rgba(220, 53, 69, 0.45) !important;
    }
    .info-box-sm.fai-filter-active.bg-secondary {
        background: rgba(108, 117, 125, 0.12) !important;
        border-color: rgba(108, 117, 125, 0.45) !important;
    }
    .info-box-sm.fai-filter-active.bg-info {
        background: rgba(13, 110, 253, 0.12) !important;
        border-color: rgba(13, 110, 253, 0.45) !important;
    }

    /* Filtros ERP */
    .fai-filters-erp {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 12px;
    }

    .fai-filters-erp .card-body {
        padding: 0.75rem 0.75rem 0.6rem;
    }

    .fai-filters-erp label {
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: #0f172a;
    }

    .fai-filters-erp .input-group-text {
        background: #fff;
        border-color: rgba(15, 23, 42, 0.12);
        color: #0d6efd;
        font-weight: 700;
    }

    .fai-filters-erp .form-control,
    .fai-filters-erp select {
        border-color: rgba(15, 23, 42, 0.12);
        background: #fff;
        border-radius: 10px;
        font-size: 0.9rem;
        padding: .35rem .5rem;
    }

    .fai-filters-erp .form-control:focus,
    .fai-filters-erp select:focus {
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.18);
        border-color: rgba(13, 110, 253, 0.5);
    }

    .fai-filters-erp .btn {
        border-radius: 10px;
        font-weight: 700;
    }

    .fai-filters-erp .btn-outline-secondary {
        color: #0f172a;
        border-color: rgba(15, 23, 42, 0.12);
        background: #fff;
    }

    .fai-filters-erp .btn-outline-secondary:hover {
        background: rgba(13, 110, 253, 0.08);
        color: #0d6efd;
    }

    /* Asegurar que los pickers no queden ocultos bajo cards sticky */
    /* Visibilidad de pickers sobre cards sticky */
    /* Evitar que las cards sticky tapen el datepicker */
    .sticky-top {
        position: static !important;
        z-index: auto !important;
        overflow: visible;
    }

    .fai-summary-card,
    .fai-filters-erp,
    .fai-summary-card .card,
    .fai-filters-erp .card {
        overflow: visible;
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

    /* Header Summary card */
    .fai-summary-card .card-header {
        background: linear-gradient(135deg, #e0f2fe 0%, #d1fae5 100%);
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    }

    .fai-summary-card-top {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 12px;
        background: #fff;
    }

    .fai-summary-card-top .card-body {
        padding: .6rem .7rem !important;
    }

    /* Contenedor tabla estilo ERP */
    .fai-erp-wrap {
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0;
        box-shadow: none;
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
        font-size: 0.9rem;
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
        font-size: 0.9rem;
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
    #faicompleteTable tbody td:nth-child(6),
    #faicompleteTable tbody td:nth-child(7),
    #faicompleteTable tbody td:nth-child(8),
    #faicompleteTable tbody td:nth-child(9),
    #faicompleteTable tbody td:nth-child(10),
    #faicompleteTable tbody td:nth-child(11),
    #faicompleteTable tbody td:nth-child(12) {
        text-align: center;
    }

    #faicompleteTable tbody td:nth-child(4),
    #faicompleteTable tbody td:nth-child(5),
    #faicompleteTable tbody td:nth-child(13) {
        text-align: left;
    }
    #faicompleteTable thead th:nth-child(13),
    #faicompleteTable tbody td:nth-child(13) {
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
        font-size: 0.78rem;
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
        margin-top: 0 !important;
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
        overflow: hidden;
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

        // Índices de columnas (ajusta si cambias el <thead>)
        const COLS = {
            date: 0,
            location: 1,
            work_id: 2,
            pn: 3,
            description: 4,
            samp_plan: 5,
            wo_qty: 6,
            sampling: 7,
            ops: 8,
            fai: 9,
            ipi: 10,
            prog: 11,
            action: 12
        };

        const $tbl = $('#faicompleteTable');
        if (!$tbl.length) return;

        // Destruye si existía
        if ($.fn.DataTable.isDataTable($tbl)) $tbl.DataTable().destroy();

        // Inicializa (si usas AJAX/serverSide, agrégalo aquí)
        const dt = $tbl.DataTable({
            searching: true,
            ordering: false,
            pageLength: 11,
            lengthMenu: [
                [11, 25, 50, -1],
                [11, 25, 50, 'All']
            ],
            scrollX: false,
            autoWidth: false,
            dom: 'rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
            columnDefs: [{
                    // PROGRESS
                    targets: COLS.prog,
                    orderable: false,
                    render: function(data, type, row, meta) {
                        // row es un ARREGLO: usa índices
                        const sampText = row[COLS.sampling] ?? '0'; // columna SAMP.
                        const opsText = row[COLS.ops] ?? '0'; // columna OPS.

                        const sampling = parseInt(String(sampText).replace(/\D/g, ''), 10) || 0;
                        const ops = parseInt(String(opsText).replace(/\D/g, ''), 10) || 0;

                        // 1️⃣ Si NO hay operaciones y NO hay sampling → solo "Done"
                        if (ops === 0 && sampling === 0) {
                            return `
<span class="badge" style="
    background:#6c757d;   /* Bootstrap secondary */
    color:white;
    padding:4px 10px;
    border-radius:6px;
    font-weight:600;
    display:inline-flex;
    align-items:center;
    gap:6px;
">
    <i class="fas fa-exclamation"></i>
    No Inspection
</span>
                    `;
                        }

                        // 2️⃣ Para el resto, deja lo que venga del backend (barra 100%, etc.)
                        return data;
                    }
                },
                {
                    targets: COLS.action,
                    orderable: false
                }
            ]
        });

        // Ajustes de texto en filtros (placeholders/labels)
        $('#tableSearch').attr('placeholder', 'Type to filter the table...');
        $('#locationFilter option[value=\"\"]').text('- All -');
        $('label[for=\"year\"]').text('Date');

        // Toolbar tipo summary: filtros a la izquierda, search a la derecha, length abajo
        const $wrapper = $tbl.closest('.dataTables_wrapper');

        // Buscador inline para la tabla
        const $toolbarSearch = $(`
            <div class="input-group input-group-sm mb-0">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                </div>
                <input type="text" class="form-control" id="dtToolbarSearch" placeholder="Search table..." autocomplete="off">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="dtToolbarClear"><i class="fas fa-times"></i></button>
                </div>
            </div>
        `);
        $toolbarSearch.on('input', '#dtToolbarSearch', function() {
            dt.search(this.value || '').page('first').draw('page');
        });
        $toolbarSearch.on('click', '#dtToolbarClear', function() {
            $('#dtToolbarSearch').val('');
            dt.search('').page('first').draw('page');
            $('#dtToolbarSearch').trigger('focus');
        });

        // Botones de export en la misma barra (reutilizan handlers existentes)
        const $exportGroup = $(`
            <div class="btn-group btn-group-sm mb-0">
                <button id="toolbarExportExcel" type="button" class="btn btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </button>
                <button id="toolbarExportPdf" type="button" class="btn btn-danger">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </button>
            </div>
        `);
        $exportGroup.on('click', '#toolbarExportExcel', () => submitExport('exportExcelForm'));
        $exportGroup.on('click', '#toolbarExportPdf', () => submitExport('exportPdfForm'));

        const $loc = $('#filterLocationGroup').detach().addClass('mb-0');
        const $year = $('#filterYearGroup').detach().addClass('mb-0');
        const $month = $('#filterMonthGroup').detach().addClass('mb-0');
        const $day = $('#filterDayGroup').detach().addClass('mb-0');
        const $clean = $('#filtersForm a.btn-secondary').first().detach().removeClass('btn-secondary').addClass('btn-erp-gray');
        const $quickActions = $('#filtersForm .btn-group').eq(0).detach().removeClass('d-flex');
        const $exportButtons = $('#filtersForm .btn-group').eq(0).detach().removeClass('d-flex');

        $loc.find('.input-group, select').addClass('input-group-sm');
        $year.find('.input-group').addClass('input-group-sm');
        $month.find('.input-group').addClass('input-group-sm');
        $day.find('.input-group').addClass('input-group-sm');

        const now = new Date();
        const reqDay = '{{ trim((string) request("day", "")) }}';
        const reqMonth = '{{ trim((string) request("month", "")) }}';
        const reqYear = '{{ trim((string) request("year", "")) }}';
        const nowDate = '{{ now()->toDateString() }}';
        const nowMonth = String(now.getMonth() + 1);
        const nowYear = String(now.getFullYear());
        const isTodayActive = (reqDay !== '' && reqDay === nowDate);
        const isCleanDefault = (reqDay === '' && reqMonth === '' && reqYear === '');
        const isCurrentMonthFilter = (reqDay === '' && reqMonth === nowMonth && reqYear === nowYear);
        const isMonthActive = (isCleanDefault || isCurrentMonthFilter);
        const isYearActive = (reqDay === '' && reqMonth === '' && reqYear === nowYear);

        const todayHref = `{{ route('faisummary.completed', array_merge(request()->except(['day','month','year','page']), ['day'=>now()->toDateString()])) }}`;
        const monthHref = `{{ route('faisummary.completed', array_merge(request()->except(['day','page']), ['year'=>now()->year,'month'=>now()->month])) }}`;
        const yearHref = `{{ route('faisummary.completed', array_merge(request()->except(['day','month','page']), ['year'=>now()->year])) }}`;

        const $quickLinks = $(`
            <div class="d-flex flex-wrap align-items-center">
                <a class="btn btn-sm ${isTodayActive ? 'btn-erp-active' : 'btn-outline-secondary'}" href="${todayHref}">
                    <i class="fas fa-bolt mr-1"></i> Today
                </a>
                <a class="btn btn-sm ${isMonthActive ? 'btn-erp-active' : 'btn-outline-secondary'}" href="${monthHref}">
                    <i class="far fa-calendar-alt mr-1"></i> Month
                </a>
                <a class="btn btn-sm ${isYearActive ? 'btn-erp-active' : 'btn-outline-secondary'}" href="${yearHref}">
                    <i class="far fa-calendar mr-1"></i> Year
                </a>
            </div>
        `);
        $quickLinks.find('.btn').addClass('mr-2');
        $exportButtons.find('.btn').addClass('mr-2');

        const $toolbar = $('<div class="fai-table-toolbar mb-2"></div>');
        const $toolbarTop = $('<div class="toolbar-top"></div>');
        const $toolbarLeft = $('<div class="toolbar-left toolbar-filters"></div>');
        const $toolbarBottom = $('<div class="toolbar-bottom"></div>');

        $toolbarLeft
            .append($loc)
            .append($year)
            .append($month)
            .append($day)
            .append($clean.addClass('btn-sm mb-0'))
            .append($quickLinks)
            .append($exportButtons);

        $toolbarSearch.addClass('toolbar-search');
        $toolbarTop.append($toolbarLeft).append($toolbarSearch);

        const $toolbarContainer = $('#dtToolbarContainer');
        $toolbarContainer.empty().append($toolbar);
        $toolbar.append($toolbarTop);

        window.faiDT = dt; // útil en consola

        /* ---------------------------
         * Helpers
         * --------------------------- */
        const nzText = v => (typeof v === 'string' ? v : ($(v).text?.() ?? String(v ?? '')).trim());
        const uniqueSorted = arr => [...new Set(arr.map(nzText).filter(Boolean))]
            .sort((a, b) => a.localeCompare(b, undefined, {
                sensitivity: 'base'
            }));

        /* ---------------------------
         * Buscador global
         * --------------------------- */
        const $search = $('#tableSearch');
        const $clear = $('#clearTableSearch');

        $search.off('.faic').on('input.faic', function() {
            dt.search(this.value || '').page('first').draw('page');
        }).on('keydown.faic', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });

        $clear.off('.faic').on('click.faic', function() {
            $search.val('');
            dt.search('').page('first').draw('page');
            $search.trigger('focus');
        });

        /* ---------------------------
         * Filtros exactos via <select>
         * --------------------------- */
        const FILTERS = [{
                id: 'locationFilter',
                col: COLS.location
            },
            // { id: 'operationFilter', col: COLS.ops },
        ];

        function populateSelectFromDT(selectId, colIndex) {
            const sel = document.getElementById(selectId);
            if (!sel) return;

            const values = dt.column(colIndex, {
                    search: 'applied'
                }).data().toArray()
                .concat(dt.column(colIndex, {
                    search: 'removed'
                }).data().toArray());
            const list = uniqueSorted(values);
            const keep = sel.value || '';

            while (sel.options.length > 1) sel.remove(1);
            const frag = document.createDocumentFragment();
            for (const v of list) {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                frag.appendChild(opt);
            }
            sel.appendChild(frag);
            if (keep && list.includes(keep)) sel.value = keep;
        }

        function bindExactFilter(selectId, colIndex) {
            const el = document.getElementById(selectId);
            if (!el) return;
            el.addEventListener('change', function() {
                if (!this.value) {
                    dt.column(colIndex).search('', true, false);
                } else {
                    const re = $.fn.dataTable.util.escapeRegex(this.value);
                    dt.column(colIndex).search('^' + re + '$', true, false);
                }
                dt.page('first').draw('page');
            });
        }

        FILTERS.forEach(f => bindExactFilter(f.id, f.col));

        function repopulateAll() {
            FILTERS.forEach(f => populateSelectFromDT(f.id, f.col));
        }
        repopulateAll();
        dt.on('search.dt', repopulateAll);

        /* ---------------------------
         * Badge: total visibles
         * --------------------------- */
        const $badge = $('#badgeFinished');

        function refreshBadge() {
            $badge.text(dt.rows({
                search: 'applied'
            }).count());
        }
        refreshBadge();
        dt.on('draw.dt search.dt page.dt', refreshBadge);

        /* ---------------------------
         * Fechas (opcional)
         * --------------------------- */
        if (window.initTempusFilters) {
            window.initTempusFilters({
                form: '#filtersForm',
                yearWrapper: '#yearPickerWrapper',
                monthWrapper: '#monthPickerWrapper',
                dayWrapper: '#dayPickerWrapper',
                yearInput: '#year',
                monthHiddenInput: '#month',
                monthDisplayInput: '#monthDisplay',
                dayInput: '#day',
                initialYear: document.querySelector('#yearPickerWrapper')?.dataset.initialYear || '',
            });
        }

        /* ---------------------------
         * KPIs: 100% completados vs <100% (incompletos)
         * --------------------------- */
        const $kpiTotal = $('#kpiTotal'); // visibles
        const $kpiPass = $('#kpiPass'); // 100%
        const $kpiFail = $('#kpiFail'); // <100%
        const $kpiNoInspection = $('#kpiNoInspection'); // sin inspección

        function getProgressFromCell(cellVal) {
            const txt = (typeof cellVal === 'string' ? cellVal : ($(cellVal).text?.() || '')).toString();
            const m = txt.match(/(\d{1,3})\s*%/);
            return m ? Number(m[1]) : NaN;
        }

        function isNoInspection(rowData) {
            // Detecta filas sin inspección (ops y sampling en cero)
            const sampText = rowData?.[COLS.sampling] ?? '';
            const opsText = rowData?.[COLS.ops] ?? '';
            const sampling = parseInt(String(sampText).replace(/\D/g, ''), 10) || 0;
            const ops = parseInt(String(opsText).replace(/\D/g, ''), 10) || 0;
            return sampling === 0 && ops === 0;
        }

        function isCompleted100(tr) {
            // 1) data-completed (ideal)
            const dc = tr.dataset.completed;
            if (dc !== undefined) return Number(dc) === 1;

            // 2) progress en data-progress
            const dp = tr.dataset.progress;
            if (dp !== undefined && !Number.isNaN(Number(dp))) return Number(dp) >= 100;

            // 3) .progress-bar[aria-valuenow]
            const aria = Number($(tr).find('.progress-bar').attr('aria-valuenow'));
            if (!Number.isNaN(aria)) return aria >= 100;

            // 4) parsear % de la columna prog
            try {
                const data = dt.row(tr).data();
                const pct = getProgressFromCell(data?.[COLS.prog]);
                if (!Number.isNaN(pct)) return pct >= 100;
            } catch (_) {}

            // 5) fallback por texto (si marca "Done" o "Completed")
            const rowTxt = $(tr).text().toLowerCase();
            if (/\bdone\b|\bcompleted\b/.test(rowTxt)) return true;

            return false;
        }

        function updateKpisCompletion() {
            const rows = dt.rows({
                search: 'applied'
            });
            const nodes = rows.nodes().toArray();

            let done100 = 0;
            let noInspection = 0;
            for (const tr of nodes)
                if (isNoInspection(dt.row(tr).data())) {
                    noInspection++;
                } else if (isCompleted100(tr)) {
                    done100++;
                }

            const total = rows.count();
            const not100 = Math.max(0, total - done100 - noInspection);

            $kpiTotal.text(total);
            $kpiPass.text(done100);
            $kpiFail.text(not100);
            $kpiNoInspection.text(noInspection);
        }

        updateKpisCompletion();
        dt.on('draw.dt search.dt page.dt', updateKpisCompletion);

        // Si tienes filtros externos:
        $(document).on('change', '.filtro-kpi, #year, #month, #day, #location, #operator, #inspector', function() {
            dt.draw(false);
        });

        // Toggles KPI filters: Completed 100%, Incomplete, and No Inspection
        let filterOnlyCompleted = false;
        let filterOnlyIncomplete = false;
        let filterOnlyNoInspection = false;
        const $kpiBoxTotal = $('#kpiBoxTotal');
        const $kpiBoxCompleted = $('.info-box-sm.bg-success');
        const $kpiBoxIncomplete = $('.info-box-sm.bg-danger');
        const $kpiBoxNoInspection = $('#kpiBoxNoInspection');

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable !== $tbl[0]) return true;
            const row = dt.row(dataIndex);
            const tr = row.node();
            const rowData = row.data();

            if (filterOnlyCompleted) {
                if (isNoInspection(rowData) || !isCompleted100(tr)) return false;
            }
            if (filterOnlyIncomplete) {
                if (isNoInspection(rowData) || isCompleted100(tr)) return false;
            }
            if (filterOnlyNoInspection) {
                if (!isNoInspection(rowData)) return false;
            }
            return true;
        });

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
        function getFilteredIds() {
            let ids = dt.rows({
                    search: 'applied'
                }).ids().toArray()
                .map(id => String(id).replace(/^row-/, ''));
            if (!ids.length) {
                const $nodes = dt.rows({
                    search: 'applied',
                    page: 'all'
                }).nodes().to$();
                ids = $nodes.map(function() {
                    return (this.id || '').replace(/^row-/, '');
                }).get();
            }
            return ids;
        }

        function submitExport(formId) {
            dt.draw(false);

            const $form = $('#' + formId);
            $form.find('input[name="ids[]"]').remove();
            $form.find('input[name="year"], input[name="month"], input[name="day"], input[name="location"]')
                .remove();

            const ids = getFilteredIds();
            if (!ids.length) {
                alert('No hay filas para exportar con el filtro actual.');
                return;
            }

            ids.forEach(id => {
                $form.append($('<input>', {
                    type: 'hidden',
                    name: 'ids[]',
                    value: id
                }));
            });

            $form.append($('<input>', {
                type: 'hidden',
                name: 'year',
                value: '{{ request("year") }}'
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'month',
                value: '{{ request("month") }}'
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'day',
                value: '{{ request("day") }}'
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'location',
                value: '{{ request("location") }}'
            }));

            $form.trigger('submit');
        }

        $('#btnExportExcel').on('click', () => submitExport('exportExcelForm'));
        $('#btnExportPdf').on('click', () => submitExport('exportPdfForm'));
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
