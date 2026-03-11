<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary')
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
<li class="breadcrumb-item active" aria-current="page">FAI Summary</li>
</ol>
</nav>
</div>
</div>
@endsection
--}}



@section('content')

<div class="row">
    {{-- === Columna izquierda: TABLA === --}}
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ===== Dashboard KPI Cards (Full width) ===== --}}
                @php
                $hasAlerts = isset($failedOrders) && $failedOrders->count() > 0;
                // Barra de KPI más delgada
                $progressHeight = $hasAlerts ? '6px' : '7px';
                // Si hay alertas => KPIs col-lg-2, si no => col-lg-3
                $kpiColClass = $hasAlerts ? 'col-sm-6 col-lg-2 mb-2' : 'col-sm-6 col-lg-3 mb-2';
                @endphp

                <div class="row mb-1 kpi-row">
                                        {{-- Total Inspections --}}
                    <div class="{{ $kpiColClass }}">
                        <div class="info-box kpi-card bg-secondary">
                            <span class="info-box-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </span>
                            <div class="info-box-content info-box-inline inspections-kpi">
                                <span class="info-box-text mb-0">Inspections</span>
                                <h3 id="kpi-total" class="mb-0 font-weight-bold">{{ number_format($monthStats['total']) }}</h3>
                                <div id="kpi-type-breakdown" class="kpi-type-breakdown">
                                    <span class="badge badge-light border text-dark">FAI <span id="kpi-fai">{{ number_format($monthStats['fai'] ?? 0) }}</span></span>
                                    <span class="badge badge-light border text-dark">IPI <span id="kpi-ipi">{{ number_format($monthStats['ipi'] ?? 0) }}</span></span>
                                </div>
                                <small class="text-muted text-uppercase inspections-period">{{ \Carbon\Carbon::create()->month($monthStats['month'])->format('M') }} {{ $monthStats['year'] }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- Pass --}}
                    <div class="{{ $kpiColClass }}">
                        <div class="info-box kpi-card bg-success">
                            <span class="info-box-icon">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <div class="info-box-content info-box-inline">
                                <span class="info-box-text mb-0">Pass</span>
                                <h3 id="kpi-pass" class="mb-0 font-weight-bold">{{ number_format($monthStats['pass']) }}</h3>
                                <small class="text-muted text-uppercase ml-auto kpi-period">Approved {{ \Carbon\Carbon::create()->month($monthStats['month'])->format('M') }} {{ $monthStats['year'] }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- No Pass --}}
                    <div class="{{ $kpiColClass }}">
                        <div class="info-box kpi-card bg-danger">
                            <span class="info-box-icon">
                                <i class="fas fa-times-circle"></i>
                            </span>
                            <div class="info-box-content info-box-inline">
                                <span class="info-box-text mb-0">No Pass</span>
                                <h3 id="kpi-fail" class="mb-0 font-weight-bold">{{ number_format($monthStats['fail']) }}</h3>
                                <small class="text-muted text-uppercase ml-auto kpi-period">Rejected {{ \Carbon\Carbon::create()->month($monthStats['month'])->format('M') }} {{ $monthStats['year'] }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- % Pass --}}
                    <div class="{{ $kpiColClass }}">
                        <div class="info-box kpi-card bg-info">
                            <span class="info-box-icon">
                                <i class="fas fa-percentage"></i>
                            </span>
                            <div class="info-box-content kpi-rate-content">
                                @php
                                    $totalVal = $monthStats['total'] ?? 0;
                                    $passVal  = $monthStats['pass'] ?? 0;
                                    // Evitar redondeo hacia arriba: truncar a 2 decimales
                                    $rateVal  = $totalVal > 0
                                        ? number_format(floor((($passVal * 100) / $totalVal) * 100) / 100, 2, '.', '')
                                        : '0.00';
                                @endphp
                                <span class="info-box-text mb-0">% Pass</span>
                                <div class="d-flex justify-content-between align-items-baseline kpi-rate-head">
                                    <h3 id="kpi-rate" class="mb-0 font-weight-bold">{{ $rateVal }}%</h3>
                                </div>
                                <small class="text-primary kpi-goal">Meta >= 95%</small>
                                <div class="progress mt-2 kpi-rate-progress" style="height: {{ $progressHeight }};">
                                    <div id="kpi-rate-bar" class="progress-bar kpi-rate-bar" style="width: {{ $rateVal }}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Caja de FAILED FAI ALERTS solo si hay alertas --}}
                    @if($hasAlerts)
                    <div class="col-sm-12 col-lg-4 mb-2">
                        <div class="fai-alert-box mb-0">

                            {{-- Header --}}
                            <div class="fai-alert-header d-flex align-items-center">
                                <div class="fai-alert-icon mr-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>

                                <div class="d-flex flex-column">
                                    <span class="fai-alert-title">
                                        FAILED FAI ALERTS
                                    </span>
                                    <small class="fai-alert-subtitle">
                                        Click on a chip to filter the inspection history.
                                    </small>
                                </div>

                                <span class="ml-auto fai-alert-count">
                                    {{ $failedOrders->count() }} jobs
                                </span>
                            </div>

                            {{-- Chips --}}
                            <div class="fai-alert-body d-flex flex-wrap mt-2">
                                @foreach($failedOrders as $fail)
                                <div class="fai-chip"
                                    data-order-id="{{ (int) ($fail->order_schedule_id ?? 0) }}"
                                    data-work-id="{{ trim($fail->orderSchedule->work_id ?? '') }}"
                                    data-pn="{{ trim($fail->orderSchedule->PN ?? '') }}"
                                    title="Last FAI: {{ \Carbon\Carbon::parse($fail->date)->format('M d, Y H:i') }}">

                                    <i class="fas fa-times-circle mr-1"></i>
                                    <span class="fai-chip-text">
                                        PN: {{ $fail->orderSchedule->PN ?? 'N/A' }}
                                        <br>
                                        <small class="text-muted">
                                            JOB: {{ $fail->orderSchedule->work_id ?? '?' }} · OP: {{ $fail->operation }}
                                        </small>
                                    </span>
                                </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                    @endif
                </div>


                {{-- Filtros bajo KPIs (formulario completo) --}}
                @if(false)
                <div class="card mb-2 fai-filters-erp">
                    <div class="card-body">
                        <form method="GET" action="{{ route('faisummary.general') }}" id="filtersForm">
                            {{-- Global Search (lado filtros) --}}
                            <div class="form-group mb-2">
                                <label for="globalSearch">Search</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="globalSearch" class="form-control" placeholder="Search in table…" autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="button" id="clearGlobalSearch" class="btn btn-outline-secondary" title="Clear">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Operator --}}
                            <div class="form-group mb-2">
                                <label for="operatorFilter">Operator</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-user-tag text-primary"></i></span>
                                    </div>
                                    <select id="operatorFilter" class="form-control dt-filter" name="operator">
                                        <option value="">— All —</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Inspector --}}
                            <div class="form-group mb-2">
                                <label for "inspectorFilter">Inspector</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-user-check text-success"></i></span>
                                    </div>
                                    <select id="inspectorFilter" class="form-control dt-filter" name="inspector">
                                        <option value="">— All —</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Location --}}
                            <div class="form-group mb-2">
                                <label for="locationFilter">Location</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-danger"></i></span>
                                    </div>
                                    <select id="locationFilter" class="form-control dt-filter" name="location">
                                        <option value="">— All —</option>
                                    </select>
                                </div>
                            </div>

                            {{-- YEAR --}}
                            <div class="form-group mb-2">
                                <label for="year">Date</label>
                                <div class="input-group input-group date" id="yearPickerWrapper"
                                    data-target-input="nearest"
                                    data-initial-year="{{ request('year') ?? '' }}"
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

                            {{-- MONTH (display + hidden) --}}
                            <div class="form-group mb-2">
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
                            <div class="form-group mb-2">
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

                            {{-- Clean + Total en la misma fila --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <a href="{{ route('faisummary.general') }}" class="btn btn-secondary btn-sm btn-erp-gray">
                                    <i class="fas fa-eraser mr-1"></i> Clean
                                </a>

                                <span class="badge badge-info py-2 px-3" style="font-size: 1rem;">
                                    <i class="fas a-list-ol mr-1"></i>
                                    Total: <span id="badgeFinished">{{ isset($inspections) ? $inspections->count() : 0 }}</span>
                                </span>
                            </div>

                            {{-- Quick actions --}}
                            <div class="btn-group btn-group-sm d-flex mb-2">
                                <a class="btn btn-outline-secondary flex-fill"
                                    href="{{ route('faisummary.general', array_merge(request()->except(['day','month','year','page']), ['day'=>now()->toDateString()])) }}">
                                    <i class="fas fa-bolt mr-1"></i> Today
                                </a>
                                <a class="btn btn-outline-secondary flex-fill"
                                    href="{{ route('faisummary.general', array_merge(request()->except(['day','page']), ['year'=>now()->year,'month'=>now()->month])) }}">
                                    <i class="far fa-calendar-alt mr-1"></i> Month
                                </a>
                                <a class="btn btn-outline-secondary flex-fill"
                                    href="{{ route('faisummary.general', array_merge(request()->except(['day','month','page']), ['year'=>now()->year])) }}">
                                    <i class="far fa-calendar mr-1"></i> Year
                                </a>
                            </div>

                            {{-- Botones de exportación --}}
                            <div class="btn-group btn-group-sm d-flex mb-2">
                                <a href="{{ route('faisummary.export.excel', request()->query()) }}"
                                    class="btn btn-erp-gray flex-fill">
                                    <i class="far fa-file-excel mr-1 text-success"></i> Excel
                                </a>

                                <a href="{{ route('faisummary.export.pdf', request()->query()) }}"
                                    class="btn btn-erp-gray flex-fill"
                                    target="_blank">
                                    <i class="far fa-file-pdf mr-1 text-danger"></i> PDF
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
                <div class="d-flex justify-content-end align-items-center mb-1">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="colVisToggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Columns
                        </button>
                        <div class="dropdown-menu dropdown-menu-right p-2" id="colVisMenu" aria-labelledby="colVisToggle" style="min-width: 220px;">
                            <label class="dropdown-item mb-0 py-1"><input type="checkbox" data-col="7" checked> SB/IS</label>
                            <label class="dropdown-item mb-0 py-1"><input type="checkbox" data-col="8" checked> Observation</label>
                            <label class="dropdown-item mb-0 py-1"><input type="checkbox" data-col="9" checked> Station</label>
                            <label class="dropdown-item mb-0 py-1"><input type="checkbox" data-col="10" checked> Method</label>
                            <label class="dropdown-item mb-0 py-1"><input type="checkbox" data-col="11" checked> Qty Insp.</label>
                            <label class="dropdown-item mb-0 py-1"><input type="checkbox" data-col="12" checked> Inspector</label>
                            <label class="dropdown-item mb-0 py-1"><input type="checkbox" data-col="13" checked> Location</label>
                        </div>
                    </div>
                </div>
                <div class="mt-n1 table-responsive fai-erp-wrap">
                    <table id="faiTable" class="table table-sm align-middle mb-0 fai-erp-table">
                        <colgroup>
                            <col style="width:140px">
                            <col style="width:150px">
                            <col style="width:90px">
                            <col style="width:70px">
                            <col class="opet-col">
                            <col style="width:110px">
                            <col style="width:80px">
                            <col style="width:130px">
                            <col style="width:140px">
                            <col style="width:90px">
                            <col style="width:90px">
                            <col style="width:90px">
                            <col style="width:110px">
                            <col style="width:110px">
                        </colgroup>
                        <thead class="thead-light sticky-thead">
                            <tr class="text-uppercase ">
                                <th>Date</th>
                                <th>Part/Revision</th>
                                <th>Job</th>
                                <th>Type</th>
                                <th>Opet</th>
                                <th>Operator</th>
                                <th>Result</th>
                                <th>SB/IS</th>
                                <th>Observation</th>
                                <th>Station</th>
                                <th>Method</th>
                                <th>Qty Insp.</th>
                                <th>Inspector</th>
                                <th>Location</th>
                            </tr>
                            <tr class="dt-head-filters">
                                <th><select id="headDayFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th></th>
                                <th></th>
                                <th><select id="headTypeFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th></th>
                                <th></th>
                                <th><select id="headResultFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th></th>
                                <th></th>
                                <th><select id="headStationFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th><select id="headMethodFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th></th>
                                <th><select id="headInspectorFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th><select id="headLocationFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspections as $inspection)
                            @php
                            $tz = config('app.timezone', 'UTC'); // cambia si usas otra zona
                            $dtDate = $inspection->date
                            ? \Carbon\Carbon::parse($inspection->date)->setTimezone($tz)
                            : null;

                            $isPass = strcasecmp(trim((string)$inspection->results), 'pass') === 0;
                            $isFAI = strcasecmp(trim((string)$inspection->insp_type), 'FAI') === 0;
                            @endphp
                            <tr>
                                <td data-order="{{ $dtDate?->format('Y-m-d H:i:s') }}">
                                    {{ $dtDate?->format('M-d-y') }}
                                    @if($dtDate)
                                    <span class="badge badge-light">{{ $dtDate->format('H:i') }}</span>
                                    @endif
                                </td>
                                <td class="truncate" title="{{ $inspection->orderSchedule->PN ?? '' }}">
                                    {{ $inspection->orderSchedule->PN ?? '' }}
                                </td>
                                <td>{{ $inspection->orderSchedule->work_id ?? '' }}</td>
                                <td>
                                    <span class="badge {{ $isFAI ? 'badge-info' : 'badge-secondary' }}">
                                        {{ $inspection->insp_type }}
                                    </span>
                                </td>
                                <td>{{ $inspection->operation }}</td>
                                <td class="truncate" title="{{ $inspection->operator }}">{{ $inspection->operator }}</td>
                                <td>
                                    <span class="badge {{ $isPass ? 'badge-success' : 'badge-danger' }}">
                                        {{ ucfirst($inspection->results) }}
                                    </span>
                                </td>
                                <td class="cell-paragraph" data-toggle="tooltip" title="{{ $inspection->sb_is }}">
                                    {{ $inspection->sb_is }}
                                </td>
                                <td class="cell-paragraph" data-toggle="tooltip" title="{{ $inspection->observation }}">
                                    {{ $inspection->observation }}
                                </td>
                                <td>{{ $inspection->station }}</td>
                                <td>{{ $inspection->method }}</td>
                                <td>{{ $inspection->qty_pcs }}</td>
                                <td class="truncate" title="{{ $inspection->inspector }}">{{ $inspection->inspector }}</td>
                                <td>{{ $inspection->loc_inspection }}</td>
                            </tr>
                            @empty
                            {{-- vacío: DataTables muestra su mensaje --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- === Columna derecha: FILTROS === --}}
    <div class="col-md-2">
        <div class="card shadow-sm mb-3 filters-card-fixed fai-filters-erp">
            <div class="card-body">
                <form method="GET" action="{{ route('faisummary.general') }}" id="filtersForm">
                    {{-- Global Search --}}
                    <div class="form-group mb-2">
                        <label for="globalSearch">Search</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" id="globalSearch" name="q" class="form-control" placeholder="Search in table..." autocomplete="off" value="{{ request('q') }}">
                            <div class="input-group-append">
                                <button type="button" id="clearGlobalSearch" class="btn btn-outline-secondary" title="Clear">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Operator --}}
                    <div class="form-group mb-2">
                        <label for="operatorFilter">Operator</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light"><i class="fas fa-user-tag text-primary"></i></span>
                            </div>
                            <select id="operatorFilter" class="form-control dt-filter" name="operator">
                                <option value="">— All —</option>
                            </select>
                        </div>
                    </div>

                    {{-- Inspector --}}
                    <div class="form-group mb-2">
                        <label for="inspectorFilter">Inspector</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light"><i class="fas fa-user-check text-success"></i></span>
                            </div>
                            <select id="inspectorFilter" class="form-control dt-filter" name="inspector">
                                <option value="">— All —</option>
                            </select>
                        </div>
                    </div>



                    {{-- Location --}}
                    <div class="form-group mb-2">
                        <label for="locationFilter">Location</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-danger"></i></span>
                            </div>
                            <select id="locationFilter" class="form-control dt-filter" name="location">
                                <option value="">— All —</option>
                            </select>
                        </div>
                    </div>

                    {{-- YEAR --}}
                    <div class="form-group mb-2">
                        <label for="year">Date</label>
                        <div class="input-group input-group date" id="yearPickerWrapper"
                            data-target-input="nearest"
                            data-initial-year="{{ request('year') ?? '' }}"
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

                    {{-- MONTH (display + hidden) --}}
                    <div class="form-group mb-2">
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
                    <div class="form-group mb-2">
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

                    {{-- Clean + Total en la misma fila --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <a href="{{ route('faisummary.general') }}" class="btn btn-secondary btn-sm btn-erp-gray">
                            <i class="fas fa-eraser mr-1"></i> Clean
                        </a>

                        <span class="badge badge-info py-2 px-3" style="font-size: 1rem;">
                            <i class="fas fa-list-ol mr-1"></i>
                            Total: <span id="badgeFinished">{{ isset($inspections) ? $inspections->count() : 0 }}</span>
                        </span>
                    </div>

                    {{-- Quick actions --}}
                    <div class="btn-group btn-group-sm d-flex mb-2">
                        <a class="btn btn-outline-secondary flex-fill"
                            href="{{ route('faisummary.general', array_merge(request()->except(['day','month','year','page']), ['day'=>now()->toDateString()])) }}">
                            <i class="fas fa-bolt mr-1"></i> Today
                        </a>
                        <a class="btn btn-outline-secondary flex-fill"
                            href="{{ route('faisummary.general', array_merge(request()->except(['day','page']), ['year'=>now()->year,'month'=>now()->month])) }}">
                            <i class="far fa-calendar-alt mr-1"></i> Month
                        </a>
                        <a class="btn btn-outline-secondary flex-fill"
                            href="{{ route('faisummary.general', array_merge(request()->except(['day','month','page']), ['year'=>now()->year])) }}">
                            <i class="far fa-calendar mr-1"></i> Year
                        </a>
                    </div>

                    {{-- Botones de exportación --}}
                    <div class="btn-group btn-group-sm d-flex mb-2">
                        <a href="{{ route('faisummary.export.excel', request()->query()) }}"
                            class="btn btn-erp-gray flex-fill">
                            <i class="far fa-file-excel mr-1 text-success"></i> Excel
                        </a>

                        <a href="{{ route('faisummary.export.pdf', request()->query()) }}"
                            class="btn btn-erp-gray flex-fill"
                            target="_blank">
                            <i class="far fa-file-pdf mr-1 text-danger"></i> PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>



<!--   <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="fas fa-plus"></i> New Order
                        </button> -->



<!--  {{-- Tab: By End Schedule --}}-->




@endsection


@section('css')
<style>
    /* Tarjeta KPI estilo ERP (similar a referencia) */
    .info-box {
        min-height: 36px;
        padding: .2rem .35rem;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #fff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        gap: .25rem;
        margin-bottom: 0.15rem;
    }

    /* Estado activo al filtrar */
    /* Acento inferior */
    .info-box::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        background: #d65a50;
    }
    /* Línea plana con el color que antes era el fondo de cada variante */
    .info-box.bg-success::after {
        background: rgba(25, 135, 84, 0.55);
    }
    .info-box.bg-danger::after {
        background: rgba(220, 53, 69, 0.55);
    }
    .info-box.bg-info::after {
        background: rgba(13, 110, 253, 0.55);
    }
    .info-box.bg-secondary::after {
        background: rgba(108, 117, 125, 0.55);
    }

    /* Fondo blanco; color solo en la línea inferior */
    .info-box.bg-secondary,
    .info-box.bg-success,
    .info-box.bg-danger,
    .info-box.bg-info {
        background: #fff !important;
        border-color: #e5e7eb !important;
        color: #1f2937 !important;
    }

    .info-box .info-box-icon {
        width: 32px;
        height: 32px;
        font-size: 18px;
        line-height: 32px;
        border-radius: 10px;
        background: #f2f4f7;
        color: #cbd5e1;
        box-shadow: none;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: .12rem;
    }
    

    /* Color de íconos por variante */
    /* Iconos con color según el contexto */
    .info-box.bg-secondary .info-box-icon { color: #495057 !important; }
    .info-box.bg-success .info-box-icon   { color: #198754 !important; }
    .info-box.bg-danger .info-box-icon    { color: #dc3545 !important; }
    .info-box.bg-info .info-box-icon      { color: #0d6efd !important; }

    .info-box .info-box-content {
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        gap: .12rem;
        line-height: 1;
    }

    .info-box .info-box-text {
        font-size: .60rem;
        font-weight: 800;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .info-box .info-box-number {
        font-size: 0.68rem;
        font-weight: 800;
        line-height: 1.05;
        margin: 0;
    }

    /* Color destacado para la barra de progreso en los KPIs */
    .info-box .progress-bar {
        background: linear-gradient(90deg, #0d6efd 0%, #0b5ed7 100%) !important;
        height: 100%;
    }

    .info-box .progress {
        background: rgba(255, 255, 255, 0.4);
    }

    .info-box .progress,
    .info-box .progress * {
        color: #0f172a;
    }

/* KPI % progress bar */
.kpi-rate-progress {
    display: block !important;
    visibility: visible !important;
    height: 6px !important;
    min-height: 4px !important;
    background: #edf0f4 !important;
    border-radius: 6px !important;
    overflow: hidden !important;
    width: 100% !important;
    flex: 1 1 100%;
    margin-top: 2px;
    position: relative;
    border: none;
}
.kpi-rate-progress .kpi-rate-bar,
#kpi-rate-bar {
    background: linear-gradient(90deg, #0d6efd 0%, #0b5ed7 100%) !important;
    min-width: 12px !important;
    height: 100% !important;
    opacity: 1 !important;
    position: absolute;
    left: 0;
    top: 0;
    display: block !important;
    border-radius: 6px;
}

/* Alinear contenido del KPI % en columna para dar espacio a la barra */
.info-box.bg-info .info-box-content {
    flex-direction: column;
    align-items: stretch;
    gap: 0.2rem;
    width: 100%;
}

/* Alinear info y paginado en una sola línea */
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
    float: none !important;
    display: flex;
    align-items: center;
    padding-top: 0.35rem;
    margin: 0;
}
.dataTables_wrapper .dataTables_info {
    margin-right: auto;
    font-size: 0.82rem;
}
.dataTables_wrapper .dataTables_paginate {
    margin-left: 0.5rem;
    justify-content: flex-end;
}

/* Tarjeta de filtros estilo ERP (fondo suave y borde ligero) */
    .filters-card-fixed.fai-filters-erp {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
    }
    .filters-card-fixed.fai-filters-erp .card-body {
        padding: 0.75rem;
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
    /* Layout en linea para KPIs */
    .info-box-inline {
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.08rem;
        flex-wrap: nowrap;
    }
    .info-box-inline .info-box-text {
        margin-right: 0.14rem;
    }
    .info-box-inline h3 {
        margin-bottom: 0;
    }
    .info-box-inline small {
        white-space: nowrap;
        margin-left: 0.18rem !important;
        margin-right: 0 !important;
        display: inline-flex;
        align-items: center;
        align-self: center;
        line-height: 1;
        font-size: 0.52rem;
        flex-shrink: 0;
    }
    .inspections-kpi {
        align-items: flex-start;
        justify-content: center;
        flex-direction: column;
        gap: 0.12rem;
    }
    .inspections-kpi .info-box-text {
        margin-right: 0;
    }
    .kpi-type-breakdown {
        display: flex;
        align-items: center;
        gap: 0.22rem;
    }
    .kpi-type-breakdown .badge {
        font-size: 0.58rem;
        line-height: 1.1;
        padding: 0.2rem 0.36rem;
        font-weight: 700;
    }
    .inspections-period {
        margin-left: 0 !important;
        margin-right: 0 !important;
        align-self: flex-start !important;
    }

    /* ===== Caja grande de alertas FAI ===== */
    .fai-alert-box {
        background: #fff;
        border-radius: 10px;
        border: 2px solid #df0c0cff;
        border-left: 5px solid #df0c0cff;
        padding: 0.5rem 0.75rem 0.4rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
    }

    /* Header */
    .fai-alert-header {
        border-bottom: 1px dashed #df0c0cff;
        padding-bottom: .25rem;
    }

    .fai-alert-icon {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #c00d1fff;
        font-size: 1.6rem;
    }

    .fai-alert-title {
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: .06em;
        color: #db2525ff;
    }

    .fai-alert-subtitle {
        font-size: 0.72rem;
        color: #a66;
    }

    .fai-alert-count {
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 999px;
        background: #ffe5e5;
        color: #b00000;
        font-weight: 600;
    }

    /* Body (contenedor de chips) */
    .fai-alert-body {
        padding-top: .3rem;
    }

    /* Chips (puedes combinar con lo que ya tienes) */
    .fai-chip {
        display: inline-flex;
        align-items: flex-start;
        background: #ffecec;
        border: 1px solid #f4b6b6;
        border-radius: 12px;
        padding: 4px 8px;
        margin: 3px 6px 3px 0;
        font-size: 0.78rem;
        color: #b00000;
        cursor: pointer;
        transition: background-color .15s ease, box-shadow .15s ease, transform .05s ease;
    }

    .fai-chip:hover {
        background: #ffdede;
        box-shadow: 0 1px 3px rgba(220, 53, 69, 0.25);
        transform: translateY(-1px);
    }

    .fai-chip i {
        font-size: 0.75rem;
        margin-top: 1px;
    }

    .fai-chip-text {
        line-height: 1.15;
    }

    /* chip activo cuando filtra */
    .fai-chip-active {
        border-color: #c82333;
        box-shadow: 0 0 4px rgba(200, 35, 51, 0.4);
    }

    /* ===================== */
    /* Tabla ERP: faisummary_summary */
    /* ===================== */
    .fai-erp-wrap {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.4rem;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    }

    /* Evitar scrollbar horizontal en la página */
    body,
    .content-wrapper {
        overflow-x: hidden;
    }

    #faiTable {
        width: 100%;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
    }

    #faiTable th,
    #faiTable td {
        word-break: break-word;
    }

    /* Columna OPET ajustada a contenido mínimo */
    #faiTable col.opet-col {
        width: auto !important;
    }
    #faiTable th:nth-child(5),
    #faiTable td:nth-child(5) {
        white-space: nowrap;
        width: auto !important;
        min-width: 70px;
    }

    /* Encabezado gris estilo ERP (como modal/parts) */
    #faiTable thead th {
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

    #faiTable thead th:first-child {
        border-top-left-radius: 10px;
    }

    #faiTable thead th:last-child {
        border-top-right-radius: 10px;
    }

    /* Header filters row */
    #faiTable thead tr.dt-head-filters th {
        background: #eef2f7 !important;
        padding: 0.25rem 0.3rem;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
    }

    #faiTable thead tr.dt-head-filters .form-control {
        height: 28px;
        min-height: 28px;
        font-size: 0.78rem;
        padding: 0.1rem 0.35rem;
        border-radius: 6px;
    }

    #faiTable tbody td {
        padding: 0.45rem 0.7rem;
        vertical-align: middle;
        font-size: 0.9rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }

    #faiTable tbody tr:hover {
        background: rgba(13, 110, 253, 0.05);
    }

    /* Zebra suave */
    .fai-erp-table tbody tr:nth-child(even) {
        background: rgba(249, 250, 251, 0.9);
    }

    /* Alineaciones: fechas/nums centrados, textos a la izquierda */
    #faiTable tbody td:nth-child(1),
    #faiTable tbody td:nth-child(3),
    #faiTable tbody td:nth-child(4),
    #faiTable tbody td:nth-child(6),
    #faiTable tbody td:nth-child(7),
    #faiTable tbody td:nth-child(10),
    #faiTable tbody td:nth-child(11),
    #faiTable tbody td:nth-child(12),
    #faiTable tbody td:nth-child(13) {
        text-align: center;
    }

    #faiTable tbody td:nth-child(2),
    #faiTable tbody td:nth-child(5),
    #faiTable tbody td:nth-child(8),
    #faiTable tbody td:nth-child(9),
    #faiTable tbody td:nth-child(14) {
        text-align: left;
    }

    /* Estilo ERP filas con acento lateral */
    .fai-erp-table tbody tr {
        position: relative;
        box-shadow: inset 0 0 0 0 rgba(13, 110, 253, 0.75);
        transition: box-shadow .12s ease, background-color .12s ease;
    }

    .fai-erp-table tbody tr:hover {
        background: rgba(13, 110, 253, 0.05);
        box-shadow: inset 4px 0 0 0 rgba(13, 110, 253, 0.85);
    }

    /* === KPIs estilo tarjeta referencia (compacto, alineado a la izquierda) === */
    .info-box {
        min-height: 58px;
        padding: 0.45rem 0.65rem;
        border-radius: 12px;
        border: 1px solid #d7e3f7;
        background: #ffffff;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.08);
        display: flex;
        align-items: center;
        gap: 0.35rem;
        position: relative;
    }
    /* Sin línea inferior, aspecto limpio */
    .info-box::after,
    .info-box.bg-secondary::after,
    .info-box.bg-success::after,
    .info-box.bg-danger::after,
    .info-box.bg-info::after {
        content: none !important;
    }

    .info-box .info-box-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #f0f2f5;
        color: #3aa76d;
        border: 1px solid #e4eaf6;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .info-box.bg-secondary .info-box-icon { background: #f0f2f5 !important; color: #9ca3af !important; }
    .info-box.bg-success   .info-box-icon { background: #f0f2f5 !important; color: #3aa76d !important; }
    .info-box.bg-danger    .info-box-icon { background: #f0f2f5 !important; color: #ef4444 !important; }
    .info-box.bg-info      .info-box-icon { background: #f0f2f5 !important; color: #3b82f6 !important; }

    .info-box .info-box-content,
    .info-box-inline {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.08rem;
        line-height: 1.2;
        padding: 0;
        margin: 0;
    }

    .info-box .info-box-text {
        font-size: 0.90rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: #111827;
        margin: 0;
    }

    .info-box .info-box-number,
    .info-box h3 {
        font-size: 1.18rem;
        font-weight: 800;
        line-height: 1.1;
        margin: 0;
        color: #111827;
    }

    .info-box-inline small,
    .info-box small {
        white-space: nowrap;
        margin-left: 0 !important;
        margin-right: 0 !important;
        display: inline-flex;
        align-items: center;
        line-height: 1.1;
        font-size: 0.82rem;
        color: #64748b;
    }

    /* Línea inferior de color (inset shadow) */
    .info-box {
        overflow: visible !important;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06), inset 0 -3px rgba(13, 110, 253, 0.18);
    }
    .info-box::after { content: none !important; }
    .info-box.bg-secondary { box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06), inset 0 -3px rgba(108, 117, 125, 0.25); }
    .info-box.bg-success   { box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06), inset 0 -3px rgba(40, 167, 69, 0.25); }
    .info-box.bg-danger    { box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06), inset 0 -3px rgba(220, 53, 69, 0.25); }
    .info-box.bg-info      { box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06), inset 0 -3px rgba(13, 110, 253, 0.25); }

    /* Estado seleccionado al filtrar (Pass / No Pass) */
    .info-box.fai-filter-active {
        box-shadow: 0 3px 12px rgba(13, 110, 253, 0.18), inset 0 -3px rgba(13, 110, 253, 0.28) !important;
        background: #f4f7ff !important;
    }
    .info-box.fai-filter-active.bg-success {
        box-shadow: 0 3px 12px rgba(40, 167, 69, 0.18), inset 0 -3px rgba(40, 167, 69, 0.28) !important;
        background: #f2fbf6 !important;
    }
    .info-box.fai-filter-active.bg-danger {
        box-shadow: 0 3px 12px rgba(220, 53, 69, 0.18), inset 0 -3px rgba(220, 53, 69, 0.28) !important;
        background: #fff5f5 !important;
    }

    /* Igualar altura/espacio de los 4 KPI boxes */
    .info-box {
        min-height: 50px;
        align-items: center;
    }

    /* Ajuste final de alineación para KPIs (todo a la izquierda y en columna) */
    .info-box-inline {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        align-items: baseline !important;
        justify-content: flex-start !important;
        gap: 0.25rem;
        width: 100%;
    }
    .info-box-inline .info-box-text {
        flex: 0 0 100%;
        margin: 0;
        line-height: 1.1;
        text-align: left;
    }
    .info-box-inline h3,
    .info-box-inline small {
        margin: 0;
        line-height: 1.1;
        text-align: left;
        flex: 0 0 auto;
    }
    .info-box-inline h3 {
        margin-right: 0.35rem;
    }
    .info-box-inline small {
        margin-left: auto;
        text-align: right;
    }

    /* Clickable result column */
    #faiTable tbody td:nth-child(7) {
        cursor: pointer;
    }

    /* Fila activa al filtrar por PN/JOB */
    .fai-row-active {
        background: rgba(13, 110, 253, 0.12) !important;
        box-shadow: inset 4px 0 0 0 rgba(13, 110, 253, 0.85);
    }

    /* Paginado estilo ERP (igual que partsrevision) */
    .erp-dt-footer {
        margin-top: 2px;
        padding: 0 0 8px;
    }

    .dataTables_wrapper .dataTables_paginate {
        margin: 0 !important;
        padding: 0 !important;
        border-top: 0 !important;
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
        padding: 0.375rem 0.75rem !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
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

    /* Forzar mismo tamaÃ±o de paginado que partsrevision */
    #faiTable_wrapper .pagination .page-link {
        padding: 0.375rem 0.75rem !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
        border-radius: 8px !important;
    }

    #faiTable_wrapper .pagination .page-item.active .page-link {
        background: #0b5ed7 !important;
        border-color: #0b5ed7 !important;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding: 0 !important;
        margin: 0 !important;
        font-size: 0.82rem !important;
        line-height: 1.2 !important;
        color: rgba(15, 23, 42, 0.80);
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
    .dataTables_wrapper .row:last-child > div {
        display: flex;
        align-items: center;
        flex: 1 1 auto;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .dataTables_wrapper .row:last-child > div:first-child {
        justify-content: flex-start;
    }
    .dataTables_wrapper .row:last-child > div:last-child {
        justify-content: flex-end;
    }

    /* Texto de encabezados en negro (consistente) */
    #faiTable thead th,
    .fai-erp-table thead th,
    .table thead th {
        color: #0b0b0b !important;
    }

    /* === Filtros ERP === */
    .fai-filters-erp {
        background: linear-gradient(180deg, #f1f5f9 0%, #e7ecf5 100%);
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
    .fai-filters-erp .btn-secondary {
        background: #0f172a;
        border-color: #0f172a;
    }
    .fai-filters-erp .btn-group .btn {
        padding: 0.35rem 0.5rem;
    }
    .fai-filters-erp .badge-info {
        background: #0d6efd;
        color: #fff;
        border-radius: 10px;
    }


    /* Contenedor tabla: variante ligera (opción 2) */
    .fai-erp-wrap {
        background: transparent;
        border: none;
        box-shadow: none;
        padding: 0.25rem;
    }

    /* ===== KPI cards - ERP style ===== */
    .kpi-row > [class*="col-"] {
        display: flex;
    }

    .kpi-card {
        width: 100%;
        min-height: 98px !important;
        padding: 0.56rem 0.7rem !important;
        border-radius: 12px !important;
        border: 1px solid #cfd8e3 !important;
        box-shadow: none !important;
        background: #f3f4f6 !important;
        position: relative;
        overflow: hidden;
        align-items: flex-start !important;
        gap: 0.5rem !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .kpi-card:hover {
        border-color: #b9c6d6 !important;
        box-shadow: 0 1px 0 rgba(15, 23, 42, 0.08) !important;
    }

    .kpi-card::after {
        content: none !important;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #64748b;
    }

    .kpi-card.bg-secondary {
        background: #e9edf1 !important;
        border-color: #c5d0dc !important;
    }
    .kpi-card.bg-secondary::before { background: #64748b; }

    .kpi-card.bg-success {
        background: #e9f3ee !important;
        border-color: #b8d8c7 !important;
    }
    .kpi-card.bg-success::before { background: #1e8b3f; }

    .kpi-card.bg-danger {
        background: #f7ecee !important;
        border-color: #e2b8bf !important;
    }
    .kpi-card.bg-danger::before { background: #d83333; }

    .kpi-card.bg-info {
        background: #e9eef7 !important;
        border-color: #c2d3ee !important;
    }
    .kpi-card.bg-info::before { background: #2a62d9; }

    .kpi-card .info-box-icon {
        width: 32px !important;
        height: 32px !important;
        border-radius: 10px !important;
        border: 1px solid #bcc8d7 !important;
        margin: 0 !important;
        font-size: 15px !important;
        line-height: 1 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #dbe3ec !important;
        color: #334155 !important;
    }

    .kpi-card.bg-success .info-box-icon {
        background: #d8eadf !important;
        border-color: #b4d1bf !important;
        color: #1f7a3f !important;
    }
    .kpi-card.bg-danger .info-box-icon {
        background: #f2dfe2 !important;
        border-color: #ddbcc3 !important;
        color: #c13247 !important;
    }
    .kpi-card.bg-info .info-box-icon {
        background: #dce6f6 !important;
        border-color: #bccde8 !important;
        color: #2d64d6 !important;
    }

    .kpi-card .info-box-content {
        width: calc(100% - 38px);
        padding: 0 !important;
        margin-top: 0.03rem;
    }

    .kpi-card .info-box-text {
        margin: 0 0 0.06rem 0 !important;
        color: #1e3a5f !important;
        font-size: 0.74rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.04em !important;
        text-transform: uppercase;
    }

    .kpi-card h3 {
        margin: 0 !important;
        color: #0f172a !important;
        font-size: 2rem !important;
        font-weight: 800 !important;
        line-height: 1 !important;
    }

    .kpi-card small {
        font-size: 0.62rem !important;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #4d6380 !important;
    }

    .kpi-card .info-box-inline {
        display: grid !important;
        grid-template-columns: 1fr auto;
        grid-template-areas:
            "label period"
            "value value";
        row-gap: 0.18rem;
        column-gap: 0.45rem;
        align-items: center !important;
        width: calc(100% - 38px);
    }

    .kpi-card .info-box-inline .info-box-text {
        grid-area: label;
        align-self: center;
    }

    .kpi-card .info-box-inline h3 {
        grid-area: value;
        justify-self: start;
    }

    .kpi-card .info-box-inline small {
        grid-area: period;
        justify-self: end;
        margin: 0 !important;
    }

    .kpi-card .inspections-kpi {
        grid-template-areas:
            "label period"
            "value value"
            "break break";
        row-gap: 0.16rem;
    }

    .kpi-card .inspections-kpi #kpi-type-breakdown {
        grid-area: break;
    }

    .kpi-card .inspections-kpi .inspections-period {
        grid-area: period;
        justify-self: end;
    }

    .kpi-rate-content {
        display: grid !important;
        grid-template-columns: 1fr auto;
        grid-template-areas:
            "label goal"
            "value value"
            "bar bar";
        row-gap: 0.18rem;
        align-items: center;
        width: calc(100% - 38px);
    }

    .kpi-rate-content .info-box-text {
        grid-area: label;
    }

    .kpi-rate-content .kpi-rate-head {
        grid-area: value;
        width: 100%;
        margin: 0 !important;
    }

    .kpi-rate-content .kpi-goal {
        grid-area: goal;
        justify-self: end;
    }

    .kpi-rate-content .kpi-rate-progress {
        grid-area: bar;
        width: 100%;
        margin-top: 0.08rem !important;
    }

    .kpi-period,
    .inspections-period {
        display: inline-flex !important;
        align-items: center;
        padding: 0.13rem 0.42rem;
        border-radius: 4px;
        border: 1px solid #c3cfdd;
        background: #dfe5ec;
        color: #4d6380 !important;
        font-weight: 800;
        text-transform: uppercase;
        line-height: 1.1;
    }

    .kpi-goal {
        display: inline-flex;
        align-items: center;
        padding: 0.12rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #b9cced;
        background: #e3ebfa;
        color: #2d64d6 !important;
        font-weight: 800;
        line-height: 1.1;
    }

    .kpi-type-breakdown .badge {
        border-radius: 4px !important;
        padding: 0.1rem 0.32rem !important;
        font-size: 0.58rem !important;
        font-weight: 800 !important;
        border-color: #becbdb !important;
        background: #eef2f7 !important;
        color: #334155 !important;
    }

    .kpi-rate-progress {
        background: #d2dceb !important;
        border-radius: 3px !important;
        height: 4px !important;
    }

    .kpi-rate-progress .kpi-rate-bar,
    #kpi-rate-bar {
        border-radius: 3px !important;
        background: #2d64d6 !important;
    }

    .kpi-card.fai-filter-active {
        border-color: #8eb0df !important;
        box-shadow: 0 0 0 2px rgba(45, 100, 214, 0.15) !important;
    }

    @media (max-width: 768px) {
        .kpi-card {
            min-height: 90px !important;
            padding: 0.5rem 0.56rem !important;
        }

        .kpi-card h3 {
            font-size: 1.65rem !important;
        }
    }


</style>

@endsection


@push('js')
<script src="{{ asset('vendor/js/date-filters.js') }}"></script>
<script>
    $(document).ready(function() {
        // En refresh (F5), volver al periodo vigente y limpiar foco de chips.
        const navEntry = performance.getEntriesByType('navigation')[0];
        if (navEntry && navEntry.type === 'reload') {
            const url = new URL(window.location.href);
            const now = new Date();
            const yearNow = String(now.getFullYear());
            const monthNow = String(now.getMonth() + 1);

            const prev = url.toString();
            url.searchParams.delete('focus_order_id');
            url.searchParams.delete('focus_work_id');
            url.searchParams.delete('focus_pn');
            url.searchParams.delete('focus_months');
            url.searchParams.delete('day');
            url.searchParams.set('year', yearNow);
            url.searchParams.set('month', monthNow);

            if (url.toString() !== prev) {
                window.location.replace(url.toString());
                return;
            }
        }

        // =========================
        //  DataTable
        // =========================
        const savedPageLen = 14;
        if (!$.fn.DataTable.isDataTable('#faiTable')) {
            const hasFixedHeader = !!($.fn.dataTable && $.fn.dataTable.FixedHeader);
            window.faiDT = $('#faiTable').DataTable({
                scrollX: false,
                autoWidth: false,
                pageLength: savedPageLen,
                dom: "rt<'erp-dt-footer d-flex align-items-center justify-content-between flex-wrap'<'dataTables_info'i><'dataTables_paginate'p>>", // <- sin buscador global nativo, con info
                orderCellsTop: true,
                fixedHeader: hasFixedHeader ? {
                    header: true,
                    headerOffset: 56
                } : false,
                order: [
                    [0, 'desc']
                ],
                columnDefs: [{
                    targets: [7, 8],
                    orderable: false
                }, ],
            });
        } else {
            window.faiDT = $('#faiTable').DataTable();
        }

        // Filtro inicial: mes actual (solo si no hay mes ni año) o el mes elegido
        function applyMonthFilter() {
            if (!$.fn.DataTable.isDataTable('#faiTable')) return;
            const params = new URLSearchParams(window.location.search);
            const hasFocusFilter =
                (params.get('focus_order_id') || '').trim() !== '' ||
                (params.get('focus_work_id') || '').trim() !== '' ||
                (params.get('focus_pn') || '').trim() !== '';
            const hasGlobalSearch = (params.get('q') || '').trim() !== '';
            if (hasFocusFilter || hasGlobalSearch) {
                // Si estamos filtrando por chip FAILED FAI ALERTS, no limitar por mes en cliente.
                faiDT.column(0).search('', true, false).draw();
                return;
            }

            const monthVal = ($('#month').val() || '').trim();
            const yearVal = ($('#year').val() || '').trim();

            // Si se filtra por año pero no se eligió mes, no aplicar filtro de mes
            if (!monthVal && yearVal) {
                faiDT.column(0).search('', true, false).draw();
                return;
            }

            const monthNum = monthVal ? parseInt(monthVal, 10) : (new Date().getMonth() + 1);
            if (isNaN(monthNum) || monthNum < 1 || monthNum > 12) {
                faiDT.column(0).search('', true, false).draw();
                return;
            }
            // Si no había mes ni año, fija el hidden al mes actual para consistencia
            if (!monthVal && !yearVal) {
                $('#month').val(monthNum);
                $('#year').val(new Date().getFullYear());
            }
            const abbr = new Date(2000, monthNum - 1, 1).toLocaleString('en', { month: 'short' });
            const regex = '^' + $.fn.dataTable.util.escapeRegex(abbr) + '\\-';
            faiDT.column(0).search(regex, true, false).draw();
        }
        applyMonthFilter();
        $('#month').on('change', applyMonthFilter);

        // =========================
        //  Search global (custom)
        // =========================
        function debounce(fn, ms) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), ms);
            };
        }
        function bindSearch(inputId, clearId) {
            const $input = $(inputId);
            if (!$input.length) return;
            const handler = debounce(function() {
                const val = (this.value || '').trim();
                const url = new URL(window.location.href);

                if (val) {
                    url.searchParams.set('q', val);
                    // Search global: sin limitar por fecha/foco para buscar en todos los aÃ±os.
                    url.searchParams.delete('year');
                    url.searchParams.delete('month');
                    url.searchParams.delete('day');
                    url.searchParams.delete('focus_order_id');
                    url.searchParams.delete('focus_work_id');
                    url.searchParams.delete('focus_pn');
                    url.searchParams.delete('focus_months');
                } else {
                    url.searchParams.delete('q');
                }

                if (url.toString() !== window.location.href) {
                    window.location.assign(url.toString());
                }
            }, 400);
            $input.on('input', handler);
            $input.on('keydown', function(e) {
                if (e.key === 'Enter') e.preventDefault();
            });
            if (clearId) {
                $(clearId).on('click', function() {
                    $input.val('');
                    const url = new URL(window.location.href);
                    const now = new Date();
                    url.searchParams.delete('q');
                    url.searchParams.delete('day');
                    url.searchParams.set('year', String(now.getFullYear()));
                    url.searchParams.set('month', String(now.getMonth() + 1));
                    window.location.assign(url.toString());
                });
            }
        }
        bindSearch('#globalSearch', '#clearGlobalSearch');


        // =========================
        //  Mapeo de columnas
        // =========================
        const COLS = {
            type: 3,
            operation: 4,
            operator: 5,
            result: 6,
            station: 9,
            method: 10,
            inspector: 12,
            location: 13,
        };

        // =========================
        //  Helpers filtros exactos
        // =========================
        function getText(v) {
            if (typeof v === 'string') {
                // DataTables can return HTML from badge cells; convert to plain text for filter options
                return $('<div>').html(v).text();
            }
            try {
                return $(v).text();
            } catch {
                return String(v ?? '');
            }
        }

        function uniqueSorted(values) {
            const cleaned = values.map(s => s.trim()).filter(Boolean);
            return [...new Set(cleaned)].sort((a, b) => a.localeCompare(b, undefined, {
                sensitivity: 'base'
            }));
        }

        function extractDayLabel(v) {
            const txt = getText(v).replace(/\s+/g, ' ').trim();
            if (!txt) return '';
            // First token from "Mar-11-26 07:27" -> "Mar-11-26"
            return txt.split(' ')[0] || '';
        }

        // Regex exacto tolerante a HTML (ej. badges <span>Pass</span>)
        function exactTextRegex(val) {
            const esc = $.fn.dataTable.util.escapeRegex(val);
            return '^\\s*(?:<[^>]+>\\s*)*' + esc + '\\s*(?:<[^>]+>\\s*)*$';
        }

        // =========================
        //  Column visibility (dropdown)
        // =========================
        (function initColumnVisibilityMenu() {
            const $menu = $('#colVisMenu');
            if (!$menu.length) return;

            $menu.find('input[data-col]').each(function() {
                const idx = parseInt(this.getAttribute('data-col') || '-1', 10);
                if (idx >= 0) this.checked = faiDT.column(idx).visible();
            });

            $menu.off('change.colvis').on('change.colvis', 'input[data-col]', function() {
                const idx = parseInt(this.getAttribute('data-col') || '-1', 10);
                if (idx < 0) return;
                faiDT.column(idx).visible(this.checked, false);
                faiDT.columns.adjust().draw(false);
            });
        })();

        // =========================
        //  Header column filters (Type/Result/Location)
        // =========================
        function populateHeaderSelect(selectId, colIndex) {
            const sel = document.getElementById(selectId);
            if (!sel) return;

            const applied = faiDT.column(colIndex, { search: 'applied' }).data().toArray();
            const removed = faiDT.column(colIndex, { search: 'removed' }).data().toArray();
            const unique = uniqueSorted(applied.concat(removed).map(getText));
            const current = sel.value || '';

            while (sel.options.length > 1) sel.remove(1);
            const frag = document.createDocumentFragment();
            unique.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                frag.appendChild(opt);
            });
            sel.appendChild(frag);
            if (current && unique.includes(current)) sel.value = current;
        }

        function bindHeaderExactFilter(selectId, colIndex) {
            const el = document.getElementById(selectId);
            if (!el) return;
            el.addEventListener('change', function() {
                const v = (this.value || '').trim();
                if (!v) {
                    faiDT.column(colIndex).search('', true, false).draw();
                } else {
                    faiDT.column(colIndex).search(exactTextRegex(v), true, false).draw();
                }
            });
        }

        function bindHeaderDayFilter() {
            const el = document.getElementById('headDayFilter');
            if (!el) return;
            el.addEventListener('change', function() {
                const v = (this.value || '').trim();
                if (!v) {
                    faiDT.column(0).search('', true, false).draw();
                } else {
                    const esc = $.fn.dataTable.util.escapeRegex(v);
                    faiDT.column(0).search('^' + esc + '\\b', true, false).draw();
                }
            });
        }

        function populateHeaderDayFilter() {
            const sel = document.getElementById('headDayFilter');
            if (!sel) return;
            const applied = faiDT.column(0, { search: 'applied' }).data().toArray();
            const removed = faiDT.column(0, { search: 'removed' }).data().toArray();
            const unique = uniqueSorted(applied.concat(removed).map(extractDayLabel));
            const current = sel.value || '';

            while (sel.options.length > 1) sel.remove(1);
            const frag = document.createDocumentFragment();
            unique.forEach(v => {
                if (!v) return;
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                frag.appendChild(opt);
            });
            sel.appendChild(frag);
            if (current && unique.includes(current)) sel.value = current;
        }

        bindHeaderDayFilter();
        bindHeaderExactFilter('headTypeFilter', COLS.type);
        bindHeaderExactFilter('headResultFilter', COLS.result);
        bindHeaderExactFilter('headStationFilter', COLS.station);
        bindHeaderExactFilter('headMethodFilter', COLS.method);
        bindHeaderExactFilter('headInspectorFilter', COLS.inspector);
        bindHeaderExactFilter('headLocationFilter', COLS.location);

        function repopulateHeaderFilters() {
            populateHeaderDayFilter();
            populateHeaderSelect('headTypeFilter', COLS.type);
            populateHeaderSelect('headResultFilter', COLS.result);
            populateHeaderSelect('headStationFilter', COLS.station);
            populateHeaderSelect('headMethodFilter', COLS.method);
            populateHeaderSelect('headInspectorFilter', COLS.inspector);
            populateHeaderSelect('headLocationFilter', COLS.location);
        }

        function populateSelectFromDT(selectId, colIndex) {
            const sel = document.getElementById(selectId);
            if (!sel) return;

            // 1) Datos visibles (applied) + 2) Datos filtrados (removed)
            const applied = faiDT.column(colIndex, {
                search: 'applied'
            }).data().toArray();
            const removed = faiDT.column(colIndex, {
                search: 'removed'
            }).data().toArray();

            const colData = applied.concat(removed).map(getText);

            const unique = uniqueSorted(colData);

            const current = sel.value || '';
            while (sel.options.length > 1) sel.remove(1);

            const frag = document.createDocumentFragment();
            for (const v of unique) {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                frag.appendChild(opt);
            }
            sel.appendChild(frag);

            if (current && unique.includes(current)) sel.value = current;
        }


        function bindExactFilter(selectId, colIndex) {
            const el = document.getElementById(selectId);
            if (!el) return;
            el.addEventListener('change', function() {
                const val = this.value;
                if (!val) faiDT.column(colIndex).search('', true, false).draw();
                else {
                    faiDT.column(colIndex).search(exactTextRegex(val), true, false).draw();
                }
            });
        }

        const FILTERS = [{
                id: 'operatorFilter',
                col: COLS.operator
            },
            {
                id: 'inspectorFilter',
                col: COLS.inspector
            },
            {
                id: 'stationFilter',
                col: COLS.station
            }, // solo si existe en HTML
            {
                id: 'methodFilter',
                col: COLS.method
            }, // solo si existe en HTML
            {
                id: 'locationFilter',
                col: COLS.location
            },
            {
                id: 'operationFilter',
                col: COLS.operation
            }, // idem
            {
                id: 'typeFilter',
                col: COLS.type
            }, // idem
            {
                id: 'resultFilter',
                col: COLS.result
            }, // idem
        ];

        FILTERS.forEach(f => bindExactFilter(f.id, f.col));

        function repopulateAllFilters() {
            FILTERS.forEach(f => populateSelectFromDT(f.id, f.col));
        }
        repopulateAllFilters();
        repopulateHeaderFilters();
        faiDT.on('draw', function() {
            repopulateAllFilters();
            repopulateHeaderFilters();
        });

        // Badge Total
        const $badge = $('#badgeFinished');

        function refreshBadge() {
            $badge.text(faiDT.rows({
                search: 'applied'
            }).count());
        }
        function updateKpisFromDT() {
            if (!$('#kpi-total').length) return;
            const nodes = faiDT.rows({ search: 'applied' }).nodes().toArray();
            let total = nodes.length;
            let pass = 0;
            let fail = 0;
            let fai = 0;
            let ipi = 0;
            nodes.forEach(n => {
                const typ = ($(n).find('td').eq(COLS.type).text() || '').trim().toLowerCase();
                const res = ($(n).find('td').eq(COLS.result).text() || '').trim().toLowerCase();
                if (typ === 'fai') fai++;
                else if (typ === 'ipi') ipi++;
                if (res === 'pass') pass++;
                else if (res === 'no pass' || res === 'nopass' || res === 'no  pass') fail++;
            });
            const rate = total > 0 ? Math.floor((pass / total) * 10000) / 100 : 0;
            $('#kpi-total').text(total.toLocaleString());
            $('#kpi-fai').text(fai.toLocaleString());
            $('#kpi-ipi').text(ipi.toLocaleString());
            $('#kpi-pass').text(pass.toLocaleString());
            $('#kpi-fail').text(fail.toLocaleString());
            const rateStr = rate.toFixed(2);
            $('#kpi-rate').text(rateStr + '%');
            $('#kpi-rate-bar').css('width', rateStr + '%');
        }
        function refreshUIFromDT() {
            refreshBadge();
            updateKpisFromDT();
        }
        refreshUIFromDT();
        faiDT.on('draw.dt search.dt order.dt page.dt', refreshUIFromDT);

        // =========================
        //  Tempus Dominus (si usas pickers)
        // =========================
        if (window.initTempusFilters) {
            window.initTempusFilters({
                form: '#filtersForm', // <-- corregido
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

        // =========================
        //  Click en chips: filtrar historial de esa orden (toggle)
        // =========================
        (function markActiveChipFromQuery() {
            const params = new URLSearchParams(window.location.search);
            const focusOrderId = String(params.get('focus_order_id') || '').trim();
            const focusWorkId = (params.get('focus_work_id') || '').trim();
            const focusPn = (params.get('focus_pn') || '').trim();
            if (!focusOrderId && !focusWorkId && !focusPn) return;

            $('.fai-chip').each(function() {
                const $chip = $(this);
                const chipOrderId = String($chip.data('order-id') || '').trim();
                const chipWork = String($chip.data('work-id') || '').trim();
                const chipPn = String($chip.data('pn') || '').trim();
                const matchOrder = !focusOrderId || chipOrderId === focusOrderId;
                const matchWork = !focusWorkId || chipWork === focusWorkId;
                const matchPn = !focusPn || chipPn === focusPn;
                if (matchOrder && matchWork && matchPn) {
                    $chip.addClass('fai-chip-active');
                    return false;
                }
            });
        })();

        $(document).on('click', '.fai-chip', function() {
            const $chip = $(this);
            const orderId = parseInt($chip.data('order-id') || 0, 10);
            const workId = String($chip.data('work-id') || '').trim();
            const pn = String($chip.data('pn') || '').trim();
            const url = new URL(window.location.href);

            if ($chip.hasClass('fai-chip-active')) {
                url.searchParams.delete('focus_order_id');
                url.searchParams.delete('focus_work_id');
                url.searchParams.delete('focus_pn');
                url.searchParams.delete('focus_months');
                url.searchParams.delete('day');
                const now = new Date();
                url.searchParams.set('year', String(now.getFullYear()));
                url.searchParams.set('month', String(now.getMonth() + 1));
            } else {
                if (!Number.isNaN(orderId) && orderId > 0) url.searchParams.set('focus_order_id', String(orderId));
                else url.searchParams.delete('focus_order_id');

                if (workId) url.searchParams.set('focus_work_id', workId);
                else url.searchParams.delete('focus_work_id');

                if (pn) url.searchParams.set('focus_pn', pn);
                else url.searchParams.delete('focus_pn');

                // El chip debe mostrar historial completo, sin limitarse al mes/dia actual.
                url.searchParams.delete('year');
                url.searchParams.delete('month');
                url.searchParams.delete('day');
                url.searchParams.set('focus_months', '12');
            }

            window.location.href = url.toString();
        });


        // =========================
        //  Click en columna Result: filtrar por mismo Part/Revision + Job
        // =========================
        let activeResultFilter = null;
        $('#faiTable tbody').on('click', 'td:nth-child(7)', function() {
            if (!$.fn.DataTable.isDataTable('#faiTable')) return;
            const $row = $(this).closest('tr');
            const pn = ($row.find('td').eq(1).text() || '').trim();
            const job = ($row.find('td').eq(2).text() || '').trim();
            const hasPn = !!pn;
            const hasJob = !!job;
            if (!hasPn && !hasJob) return;

            const isSame = activeResultFilter
                && activeResultFilter.pn === pn
                && activeResultFilter.job === job;

            const pnEsc = hasPn ? '^' + $.fn.dataTable.util.escapeRegex(pn) + '$' : '';
            const jobEsc = hasJob ? '^' + $.fn.dataTable.util.escapeRegex(job) + '$' : '';

            faiDT.column(1).search(isSame ? '' : pnEsc, true, false);
            faiDT.column(2).search(isSame ? '' : jobEsc, true, false);
            faiDT.draw();

            $('#faiTable tbody tr').removeClass('fai-row-active');
            if (!isSame) {
                $row.addClass('fai-row-active');
                activeResultFilter = { pn, job };
            } else {
                activeResultFilter = null;
            }
        });


        // =========================
        //  Click en KPI "Pass": filtrar columna Result = "Pass"
        // =========================
        const $kpiPass = $('.info-box.bg-success');
        $kpiPass.on('click', function() {
            if (!$.fn.DataTable.isDataTable('#faiTable')) return;
            const isActive = $(this).hasClass('fai-filter-active');
            if (isActive) {
                faiDT.column(COLS.result).search('', true, false).draw();
                $(this).removeClass('fai-filter-active');
            } else {
                faiDT.column(COLS.result).search('^\\s*pass\\s*$', true, false).draw();
                $('.info-box').removeClass('fai-filter-active');
                $(this).addClass('fai-filter-active');
            }
        });


        // =========================
        //  Click en KPI "No Pass": filtrar columna Result = "No Pass"
        // =========================
        const $kpiNoPass = $('.info-box.bg-danger');
        $kpiNoPass.on('click', function() {
            if (!$.fn.DataTable.isDataTable('#faiTable')) return;
            const isActive = $(this).hasClass('fai-filter-active');
            if (isActive) {
                faiDT.column(COLS.result).search('', true, false).draw();
                $(this).removeClass('fai-filter-active');
            } else {
                faiDT.column(COLS.result).search('^\\s*no\\s*pass\\s*$', true, false).draw();
                $('.info-box').removeClass('fai-filter-active');
                $(this).addClass('fai-filter-active');
            }
        });

        // =========================
        //  Click en KPI "Inspections": cargar todo el año actual (limpia filtros de fecha)
        // =========================
        const $kpiTotal = $('.info-box.bg-secondary');
        const initialFilters = {
            year: $('#year').val() || '',
            month: $('#month').val() || '',
            monthDisplay: $('#monthDisplay').val() || '',
            day: $('#day').val() || ''
        };
        // (sin toggle especial en Inspections)

    });
</script>


@endpush




