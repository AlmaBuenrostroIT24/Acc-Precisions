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
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ===== Dashboard KPI Cards (Full width) ===== --}}
                @php
                $hasAlerts = isset($failedOrders) && $failedOrders->count() > 0;
                // Barra de KPI más delgada
                $progressHeight = $hasAlerts ? '6px' : '7px';
                // Mantener tamaño fijo de las 4 KPI.
                $kpiColClass = $hasAlerts ? 'col-sm-6 col-lg-2 mb-2' : 'col-sm-6 col-lg-3 mb-2';
                // ALERT siempre largo y fijo, independiente de jobs.
                $alertColClass = 'col-sm-12 col-lg-4 mb-2';

                // Etiqueta de periodo según filtros seleccionados (month/year/day).
                $now = now();
                $reqYearRaw = trim((string) request('year', ''));
                $reqMonthRaw = trim((string) request('month', ''));
                $reqDayRaw = trim((string) request('day', ''));
                $periodYear = ctype_digit($reqYearRaw) ? (int) $reqYearRaw : (int) $now->year;
                $periodMonth = (ctype_digit($reqMonthRaw) && (int) $reqMonthRaw >= 1 && (int) $reqMonthRaw <= 12)
                    ? (int) $reqMonthRaw
                    : null;

                if ($reqDayRaw !== '') {
                    try {
                        $periodLabel = \Carbon\Carbon::parse($reqDayRaw)->format('M d, Y');
                    } catch (\Throwable $e) {
                        $periodLabel = ($periodMonth !== null)
                            ? \Carbon\Carbon::createFromDate($periodYear, $periodMonth, 1)->format('M Y')
                            : (string) $periodYear;
                    }
                } else {
                    $periodLabel = ($periodMonth !== null)
                        ? \Carbon\Carbon::createFromDate($periodYear, $periodMonth, 1)->format('M Y')
                        : (string) $periodYear;
                }
                @endphp

                <div class="row mb-1 kpi-row mt-n3">
                                        {{-- Total Inspections --}}
                    <div class="{{ $kpiColClass }}">
                        <div class="info-box kpi-card kpi-clean bg-secondary">
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
                                <small class="text-muted text-uppercase inspections-period">{{ $periodLabel }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- Pass --}}
                    <div class="{{ $kpiColClass }}">
                        <div class="info-box kpi-card kpi-clean bg-success">
                            <span class="info-box-icon">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <div class="info-box-content passfail-kpi-stack">
                                <span class="info-box-text mb-0">Pass</span>
                                <h3 id="kpi-pass" class="mb-0 font-weight-bold">{{ number_format($monthStats['pass']) }}</h3>
                                <div class="kpi-type-breakdown">
                                    <span class="badge badge-light border text-dark">FAI <span>{{ number_format($monthStats['pass_fai'] ?? 0) }}</span></span>
                                    <span class="badge badge-light border text-dark">IPI <span>{{ number_format($monthStats['pass_ipi'] ?? 0) }}</span></span>
                                </div>
                                <small class="text-muted text-uppercase kpi-period">Approved {{ $periodLabel }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- No Pass --}}
                    <div class="{{ $kpiColClass }}">
                        <div class="info-box kpi-card kpi-clean bg-danger">
                            <span class="info-box-icon">
                                <i class="fas fa-times-circle"></i>
                            </span>
                            <div class="info-box-content passfail-kpi-stack">
                                <span class="info-box-text mb-0">No Pass</span>
                                <h3 id="kpi-fail" class="mb-0 font-weight-bold">{{ number_format($monthStats['fail']) }}</h3>
                                <div class="kpi-type-breakdown">
                                    <span class="badge badge-light border text-dark">FAI <span>{{ number_format($monthStats['fail_fai'] ?? 0) }}</span></span>
                                    <span class="badge badge-light border text-dark">IPI <span>{{ number_format($monthStats['fail_ipi'] ?? 0) }}</span></span>
                                </div>
                                <small class="text-muted text-uppercase kpi-period">Rejected {{ $periodLabel }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- % Pass --}}
                    <div class="{{ $kpiColClass }}">
                        <div class="info-box kpi-card kpi-clean bg-info">
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

                    {{-- Failed FAI Alerts (misma zona de KPIs) --}}
                    @if($hasAlerts)
                    <div class="{{ $alertColClass }}">
                        <div class="fai-alert-box fai-alert-kpi mb-0">
                            <div class="fai-alert-header d-flex align-items-center">
                                <div class="fai-alert-icon mr-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fai-alert-title">FAILED FAI ALERTS</span>
                                    <small class="fai-alert-subtitle">Click on a chip to filter the inspection history.</small>
                                </div>
                                <span class="ml-auto fai-alert-count">{{ $failedOrders->count() }} jobs</span>
                            </div>
                            <div class="fai-alert-body mt-2">
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
                                        <small class="text-muted">JOB: {{ $fail->orderSchedule->work_id ?? '?' }} - {{ $fail->operation }}</small>
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
                <div class="fai-table-toolbar mb-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-1">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="input-group input-group-sm mr-2 mb-1 date" id="yearPickerWrapper"
                                data-target-input="nearest"
                                data-initial-year="{{ request('year') ?? '' }}"
                                style="width:132px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-calendar-alt text-success"></i>
                                    </span>
                                </div>
                                <input type="text" id="year" name="year" class="form-control datetimepicker-input"
                                    data-toggle="datetimepicker" data-target="#yearPickerWrapper"
                                    value="{{ request('year') }}" placeholder="Year" autocomplete="off">
                            </div>

                            <div class="input-group input-group-sm mr-2 mb-1 date" id="monthPickerWrapper"
                                data-target-input="nearest" style="width:132px;">
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

                            <div class="input-group input-group-sm mr-2 mb-1 date" id="dayPickerWrapper"
                                data-target-input="nearest" style="width:148px;">
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

                            <a href="{{ route('faisummary.general') }}" class="btn btn-sm btn-erp-gray mr-2 mb-1">
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
                                // Month activo solo cuando es mes actual (o estado clean/default), sin dia.
                                $isCleanDefault = ($reqDay === '' && $reqMonth === '' && $reqYear === '');
                                $isCurrentMonthFilter = ($reqDay === '' && $reqMonth === $nowMonth && $reqYear === $nowYear);
                                $isMonthActive = ($isCleanDefault || $isCurrentMonthFilter);
                                // Year activo solo cuando es anio actual (sin mes, sin dia).
                                $isYearActive = ($reqDay === '' && $reqMonth === '' && $reqYear === $nowYear);
                            @endphp

                            <a class="btn btn-sm {{ $isTodayActive ? 'btn-erp-active' : 'btn-outline-secondary' }} mr-2 mb-1"
                                href="{{ route('faisummary.general', array_merge(request()->except(['day','month','year','page']), ['day'=>now()->toDateString()])) }}">
                                <i class="fas fa-bolt mr-1"></i> Today
                            </a>
                            <a class="btn btn-sm {{ $isMonthActive ? 'btn-erp-active' : 'btn-outline-secondary' }} mr-2 mb-1"
                                href="{{ route('faisummary.general', array_merge(request()->except(['day','page']), ['year'=>now()->year,'month'=>now()->month])) }}">
                                <i class="far fa-calendar-alt mr-1"></i> Month
                            </a>
                            <a class="btn btn-sm {{ $isYearActive ? 'btn-erp-active' : 'btn-outline-secondary' }} mr-2 mb-1"
                                href="{{ route('faisummary.general', array_merge(request()->except(['day','month','page']), ['year'=>now()->year])) }}">
                                <i class="far fa-calendar mr-1"></i> Year
                            </a>

                            <a href="{{ route('faisummary.export.excel', request()->query()) }}"
                                class="btn btn-sm btn-erp-gray mr-2 mb-1">
                                <i class="far fa-file-excel mr-1 text-success"></i> Excel
                            </a>
                            <a href="{{ route('faisummary.export.pdf', request()->query()) }}"
                                class="btn btn-sm btn-erp-gray mr-2 mb-1"
                                target="_blank">
                                <i class="far fa-file-pdf mr-1 text-danger"></i> PDF
                            </a>

                            <div class="dropdown mr-2 mb-1">
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

                        <div class="input-group input-group-sm mb-1 ml-md-auto fai-search-erp" style="width: 320px;">
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

                </div>
                <div id="activeFilterChips" class="fai-active-filters mb-2 d-none"></div>
                <div class="mt-n2 table-responsive fai-erp-wrap">
                    <table id="faiTable" class="table table-sm align-middle mb-0 fai-erp-table">
                        <colgroup>
                            <col style="width:135px">  {{-- Date --}}
                            <col style="width:185px">  {{-- Part/Revision --}}
                            <col style="width:110px">  {{-- Job --}}
                            <col style="width:80px">   {{-- Type --}}
                            <col style="width:90px">   {{-- Operation --}}
                            <col style="width:130px">  {{-- Operator --}}
                            <col style="width:105px">  {{-- Result --}}
                            <col style="width:100px">  {{-- SB/IS --}}
                            <col style="width:200px">  {{-- Observation --}}
                            <col style="width:100px">  {{-- Station --}}
                            <col style="width:145px">  {{-- Method --}}
                            <col style="width:90px">   {{-- Qty Insp. --}}
                            <col style="width:160px">  {{-- Inspector --}}
                            <col style="width:120px">  {{-- Location --}}
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
                                <th>Qty</th>
                                <th>Inspector</th>
                                <th>Location</th>
                            </tr>
                            <tr class="dt-head-filters">
                                <th><select id="headDayFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th></th>
                                <th></th>
                                <th><select id="headTypeFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th><select id="headOperationFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th><select id="headOperatorFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th><select id="headResultFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th></th>
                                <th></th>
                                <th><select id="headStationFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th><select id="headMethodFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th><select id="headQtyFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th><select id="headInspectorFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                                <th><select id="headLocationFilter" class="form-control form-control-sm"><option value="">All</option></select></th>
                            </tr>
                        </thead>
                        <tbody>
                        <tbody></tbody>
                    </table>
                </div>
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
    /* Alinear info y paginado en una sola linea */
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

    /* Boton activo estilo ERP (verde suave) */
    .fai-table-toolbar .btn-erp-active {
        background: linear-gradient(180deg, #e7f6ee 0%, #d4eddf 100%) !important;
        border: 1px solid #9fd0b1 !important;
        color: #1f5d3f !important;
        box-shadow: 0 1px 3px rgba(31, 93, 63, 0.14);
    }
    .fai-table-toolbar .btn-erp-active:hover {
        background: linear-gradient(180deg, #dcf1e5 0%, #cbe7d8 100%) !important;
        color: #174b32 !important;
    }
    .fai-table-toolbar .btn-erp-active:focus,
    .fai-table-toolbar .btn-erp-active:active {
        box-shadow: 0 0 0 2px rgba(52, 168, 83, 0.2) !important;
    }

    /* Year / Month / Day mas altos en toolbar */
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
    .fai-table-toolbar #yearPickerWrapper:focus-within,
    .fai-table-toolbar #monthPickerWrapper:focus-within,
    .fai-table-toolbar #dayPickerWrapper:focus-within {
        border-color: #5b8ee6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.16);
    }
    .fai-table-toolbar #yearPickerWrapper .input-group-text,
    .fai-table-toolbar #monthPickerWrapper .input-group-text,
    .fai-table-toolbar #dayPickerWrapper .input-group-text {
        border: 0 !important;
        border-right: 1px solid #d8e0ea !important;
        background: #eef2f7 !important;
        min-width: 36px;
        height: 100%;
        justify-content: center;
        align-items: center;
        display: inline-flex;
        margin: 0 !important;
        border-radius: 10px 0 0 10px !important;
    }
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
    .fai-table-toolbar #year,
    .fai-table-toolbar #monthDisplay,
    .fai-table-toolbar #day {
        border: 0 !important;
        border-top: 0 !important;
        border-bottom: 0 !important;
        box-shadow: none !important;
        background-image: none !important;
        -webkit-appearance: none;
    }

    /* Botones del toolbar mas altos */
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

    /* Search ERP (solo visual, misma funcionalidad) */
    .fai-table-toolbar .fai-search-erp {
        border: 1px solid #bfc9d6;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        transition: border-color .15s ease, box-shadow .15s ease;
        min-height: 36px;
    }
    .fai-table-toolbar .fai-search-erp:focus-within {
        border-color: #5b8ee6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.16);
    }
    .fai-table-toolbar .fai-search-erp .input-group-text {
        border: 0 !important;
        border-right: 1px solid #d8e0ea !important;
        background: #eef2f7 !important;
        color: #334155 !important;
        min-width: 36px;
        min-height: 36px;
        justify-content: center;
    }
    .fai-table-toolbar .fai-search-erp .form-control {
        border: 0 !important;
        box-shadow: none !important;
        font-weight: 600;
        color: #0f172a;
        padding: .3rem .58rem;
        min-height: 36px;
        background: #fff !important;
    }
    .fai-table-toolbar .fai-search-erp .form-control::placeholder {
        color: #64748b;
        font-weight: 500;
    }
    .fai-table-toolbar .fai-search-erp #clearGlobalSearch {
        border: 0 !important;
        border-left: 1px solid #d8e0ea !important;
        background: #f8fafc !important;
        color: #475569 !important;
        min-width: 36px;
        min-height: 36px;
        padding: .28rem .52rem;
    }
    .fai-table-toolbar .fai-search-erp #clearGlobalSearch:hover {
        background: #eef2f7 !important;
        color: #0f172a !important;
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

    /* Variante compacta en la zona KPI: 4 jobs por fila */
    .fai-alert-kpi .fai-alert-body {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 4px;
        width: 100%;
        overflow-x: auto;
    }
    .fai-alert-kpi {
        width: 100%;
        height: 100%;
    }
    .fai-alert-kpi .fai-chip {
        flex: 0 0 calc(25% - 4px);
        min-width: 0;
        margin: 0;
        padding: 3px 6px;
        border-radius: 10px;
    }
    .fai-alert-kpi .fai-chip-text {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
        font-size: 0.72rem;
    }
    .fai-alert-kpi .fai-chip-text small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.62rem;
    }
    /* Compactar card FAILED FAI ALERTS */
    .fai-alert-kpi {
        padding: 0.36rem 0.58rem 0.3rem !important;
        min-height: 78px !important;
    }
    .fai-alert-kpi .fai-alert-header {
        padding-bottom: 0.18rem;
    }
    .fai-alert-kpi .fai-alert-icon {
        width: 22px;
        height: 22px;
        font-size: 1.25rem;
    }
    .fai-alert-kpi .fai-alert-title {
        font-size: 0.8rem;
        line-height: 1.05;
    }
    .fai-alert-kpi .fai-alert-subtitle {
        font-size: 0.66rem;
        line-height: 1.05;
    }
    .fai-alert-kpi .fai-alert-count {
        font-size: 0.68rem;
        padding: 1px 7px;
    }
    .fai-alert-kpi .fai-alert-body {
        padding-top: 0.18rem;
        gap: 3px;
    }
    .fai-alert-kpi .fai-chip {
        padding: 2px 5px;
        border-radius: 8px;
    }
    .fai-alert-kpi .fai-chip-text {
        font-size: 0.68rem;
    }
    .fai-alert-kpi .fai-chip-text small {
        font-size: 0.58rem;
    }
    @media (max-width: 1199.98px) {
        .fai-alert-kpi .fai-chip { flex-basis: calc(50% - 4px); }
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
        word-break: normal;
    }
    /* Regla base: una linea por celda para mantener estabilidad ERP */
    #faiTable tbody td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    /* Columnas de texto largo: permitir salto de linea */
    #faiTable tbody td:nth-child(2),
    #faiTable tbody td:nth-child(8),
    #faiTable tbody td:nth-child(9) {
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
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
        font-size: 0.95rem;
        text-transform: uppercase;
    }
    #faiTable thead tr:first-child th {
        position: sticky;
        top: 0;
        z-index: 8;
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
        position: sticky;
        top: 42px;
        z-index: 7;
    }

    #faiTable thead tr.dt-head-filters .form-control {
        height: 34px;
        min-height: 34px;
        font-size: 0.86rem;
        padding: 0.2rem 0.5rem;
        border-radius: 7px;
    }
    #faiTable thead tr.dt-head-filters select.form-control {
        border: 1px solid #c2cedb;
        background-color: #ffffff;
        color: #1f2937;
        font-weight: 600;
        box-shadow: 0 1px 1px rgba(15, 23, 42, 0.05);
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-right: 1.6rem;
        background-image: linear-gradient(45deg, transparent 50%, #64748b 50%), linear-gradient(135deg, #64748b 50%, transparent 50%);
        background-position: calc(100% - 12px) calc(50% - 1px), calc(100% - 8px) calc(50% - 1px);
        background-size: 5px 5px, 5px 5px;
        background-repeat: no-repeat;
    }
    #faiTable thead tr.dt-head-filters select.form-control:focus {
        border-color: #5b8ee6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.16);
        outline: 0;
    }

    #faiTable tbody td {
        padding: 0.45rem 0.7rem;
        vertical-align: middle;
        font-size: 0.95rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }

    #faiTable tbody tr:hover {
        background: rgba(13, 110, 253, 0.05);
    }

    /* Chips de filtros activos */
    .fai-active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        padding: 0.25rem 0;
    }
    .fai-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #eef2f7;
        border: 1px solid #cdd7e3;
        border-radius: 999px;
        padding: 6px 12px;
        min-height: 30px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #334155;
        line-height: 1;
    }
    .fai-filter-chip .chip-remove {
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: 0.92rem;
        line-height: 1;
        padding: 0;
        cursor: pointer;
    }
    .fai-filter-chip .chip-remove:hover {
        color: #0f172a;
    }
    .fai-clear-filters {
        border: 1px dashed #9fb0c5;
        background: #f8fafc;
        color: #334155;
        border-radius: 999px;
        padding: 6px 12px;
        min-height: 30px;
        font-size: 0.76rem;
        font-weight: 800;
        cursor: pointer;
    }

    /* Zebra suave */
    .fai-erp-table tbody tr:nth-child(even) {
        background: rgba(249, 250, 251, 0.9);
    }

    /* Alineaciones: fechas/nums centrados, textos a la izquierda */
    #faiTable tbody td:nth-child(1),
    #faiTable tbody td:nth-child(4),
    #faiTable tbody td:nth-child(12) {
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
    /* Clickable result column */
    #faiTable tbody td:nth-child(7) {
        cursor: pointer;
    }
    /* Result badge estilo ERP (solo visual) */
    #faiTable .erp-result-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 74px;
        padding: 0.25rem 0.58rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: .01em;
        border: 1px solid transparent;
    }
    #faiTable .erp-result-pass {
        background: #e8f6ee;
        border-color: #9fd7b3;
        color: #1f6a43;
    }
    #faiTable .erp-result-fail {
        background: #fdeaea;
        border-color: #efb6b6;
        color: #9f1f1f;
    }
    /* Type badge estilo ERP (FAI / IPI) */
    #faiTable .erp-type-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 52px;
        padding: 0.18rem 0.46rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        border: 1px solid transparent;
        box-shadow: inset 0 -1px 0 rgba(15, 23, 42, 0.05);
    }
    #faiTable .erp-type-fai {
        background: #dbeafe;
        border-color: #60a5fa;
        color: #0b3a75;
    }
    #faiTable .erp-type-ipi {
        background: #e2e8f0;
        border-color: #94a3b8;
        color: #334155;
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

    /* Paginado (faiTable) mismo tamaño que partsrevision */
    #faiTable_wrapper .pagination .page-link {
        padding: 0.34rem 0.68rem !important;
        font-size: 0.95rem !important;
        line-height: 1.4 !important;
        border-radius: 6px !important;
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

    /* ===== KPI cards (clean single source of truth) ===== */
    .kpi-row > [class*="col-"] {
        display: flex;
    }
    .kpi-row > [class*="col-"] > .kpi-card,
    .kpi-row > [class*="col-"] > .fai-alert-box {
        width: 100%;
    }
    .kpi-row {
        align-items: flex-start !important;
    }

    .kpi-row .kpi-card.kpi-clean {
        --kpi-accent: #69cbc6;
        background: #ffffff !important;
        border: 1px solid #ccd6e2 !important;
        border-radius: 14px !important;
        box-shadow: 0 3px 8px rgba(15, 23, 42, 0.08) !important;
        min-height: 86px !important;
        padding: 0.42rem 0.62rem 0.34rem 0.72rem !important;
        position: relative !important;
        overflow: hidden !important;
        align-self: flex-start !important;
        height: auto !important;
    }
    .kpi-row .kpi-card.kpi-clean.bg-secondary { --kpi-accent: #63c9c4 !important; }
    .kpi-row .kpi-card.kpi-clean.bg-success { --kpi-accent: #45b467 !important; }
    .kpi-row .kpi-card.kpi-clean.bg-danger { --kpi-accent: #df6f75 !important; }
    .kpi-row .kpi-card.kpi-clean.bg-info { --kpi-accent: #6a98ef !important; }
    .kpi-row .kpi-card.kpi-clean.bg-secondary,
    .kpi-row .kpi-card.kpi-clean.bg-success,
    .kpi-row .kpi-card.kpi-clean.bg-danger,
    .kpi-row .kpi-card.kpi-clean.bg-info {
        background: #ffffff !important;
        background-image: none !important;
        border-color: #ccd6e2 !important;
        color: #0f172a !important;
        display: flex !important;
        align-items: center !important;
    }
    .kpi-row .kpi-card.kpi-clean::before {
        content: "" !important;
        position: absolute !important;
        left: 0 !important;
        top: 12px !important;
        bottom: 12px !important;
        width: 4px !important;
        border-radius: 0 999px 999px 0 !important;
        background: var(--kpi-accent) !important;
        display: block !important;
        z-index: 2 !important;
    }
    .kpi-row .kpi-card.kpi-clean::after {
        content: "" !important;
        position: absolute !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        height: 3px !important;
        border-radius: 0 0 14px 14px !important;
        background: var(--kpi-accent) !important;
        display: block !important;
    }

    .kpi-row .kpi-card.kpi-clean .info-box-icon {
        width: 50px !important;
        min-width: 50px !important;
        height: 50px !important;
        border-radius: 12px !important;
        margin-right: 10px !important;
        background: #e8edf3 !important;
        border: 1px solid #d1d9e3 !important;
        box-shadow: none !important;
        color: #0f766e !important;
        font-size: 1.32rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .kpi-row .kpi-card.kpi-clean .info-box-icon i {
        font-size: 1.32rem !important;
        line-height: 1 !important;
    }
    .kpi-row .kpi-card.kpi-clean.bg-secondary .info-box-icon { color: #0f766e !important; }
    .kpi-row .kpi-card.kpi-clean.bg-success .info-box-icon { color: #198754 !important; }
    .kpi-row .kpi-card.kpi-clean.bg-danger .info-box-icon { color: #dc3545 !important; }
    .kpi-row .kpi-card.kpi-clean.bg-info .info-box-icon { color: #0d6efd !important; }
    .kpi-row .kpi-card.kpi-clean .info-box-content,
    .kpi-row .kpi-card.kpi-clean .info-box-inline,
    .kpi-row .kpi-card.kpi-clean .inspections-kpi,
    .passfail-kpi-stack {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        justify-content: flex-start !important;
        width: calc(100% - 60px) !important;
        padding-right: 62px !important;
        gap: 0.12rem !important;
        margin: 0 !important;
    }
    .kpi-row .kpi-card.kpi-clean .info-box-text {
        margin: 0 !important;
        font-size: 0.74rem !important;
        letter-spacing: .05em !important;
        color: #0f172a !important;
        white-space: normal !important;
        text-transform: uppercase !important;
    }
    .kpi-row .kpi-card.kpi-clean h3 {
        margin: 0 !important;
        font-size: 1.74rem !important;
        line-height: 1 !important;
        color: #0f172a !important;
    }
    .kpi-row .kpi-card.kpi-clean .kpi-period,
    .kpi-row .kpi-card.kpi-clean .inspections-period,
    .kpi-row .kpi-card.kpi-clean .kpi-goal {
        position: absolute !important;
        top: 8px !important;
        right: 10px !important;
        margin: 0 !important;
        padding: 2px 8px !important;
        border-radius: 999px !important;
        border: 1px solid #cfd8e3 !important;
        background: #e8edf3 !important;
        color: #334155 !important;
        font-size: 0.64rem !important;
        font-weight: 800 !important;
        letter-spacing: .04em !important;
        white-space: nowrap !important;
        text-transform: uppercase !important;
    }
    .kpi-row .kpi-card.kpi-clean .kpi-type-breakdown {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 0.2rem !important;
    }
    .kpi-row .kpi-card.kpi-clean .kpi-type-breakdown .badge {
        border-radius: 4px !important;
        font-size: 0.58rem !important;
        font-weight: 800 !important;
    }
    .kpi-row .kpi-card.kpi-clean .kpi-rate-progress {
        height: 4px !important;
        border-radius: 3px !important;
    }
    .kpi-row .kpi-card.kpi-clean.fai-filter-active {
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.22), 0 3px 8px rgba(15, 23, 42, 0.08) !important;
    }
    .kpi-row .kpi-card.kpi-clean.bg-success.fai-filter-active {
        background: #edf8f1 !important;
        border-color: #8fcda5 !important;
        box-shadow: 0 0 0 2px rgba(69, 180, 103, 0.25), 0 3px 8px rgba(15, 23, 42, 0.08) !important;
    }
    .kpi-row .kpi-card.kpi-clean.bg-danger.fai-filter-active {
        background: #fdf0f1 !important;
        border-color: #e4a1a7 !important;
        box-shadow: 0 0 0 2px rgba(223, 111, 117, 0.24), 0 3px 8px rgba(15, 23, 42, 0.08) !important;
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
                processing: true,
                serverSide: true,
                searchDelay: 350,
                pageLength: savedPageLen,
                dom: "rt<'erp-dt-footer d-flex align-items-center justify-content-between flex-wrap'<'dataTables_info'i><'dataTables_paginate'p>>", // <- sin buscador global nativo, con info
                orderCellsTop: true,
                ajax: {
                    url: "{{ route('faisummary.general.data') }}",
                    type: 'GET',
                    data: function(d) {
                        d.year = ($('#year').val() || '').trim();
                        d.month = ($('#month').val() || '').trim();
                        d.day = ($('#day').val() || '').trim();
                        const p = new URLSearchParams(window.location.search);
                        ['focus_order_id', 'focus_work_id', 'focus_pn', 'focus_months', 'operator', 'inspector', 'location'].forEach(k => {
                            const v = (p.get(k) || '').trim();
                            if (v !== '') d[k] = v;
                        });
                    }
                },
                fixedHeader: hasFixedHeader ? {
                    header: true,
                    headerOffset: 56
                } : false,
                order: [
                    [0, 'desc']
                ],
                columns: [
                    { data: 'date', name: 'date', render: { _: 'display', sort: 'sort', filter: 'filter' } },
                    { data: 'part_revision', name: 'part_revision' },
                    { data: 'job', name: 'job' },
                    { data: 'type', name: 'type' },
                    { data: 'opet', name: 'opet' },
                    { data: 'operator', name: 'operator' },
                    { data: 'result', name: 'result' },
                    { data: 'sb_is', name: 'sb_is' },
                    { data: 'observation', name: 'observation' },
                    { data: 'station', name: 'station' },
                    { data: 'method', name: 'method' },
                    { data: 'qty_insp', name: 'qty_insp' },
                    { data: 'inspector', name: 'inspector' },
                    { data: 'location', name: 'location' }
                ],
                columnDefs: [
                    { targets: [7, 8], orderable: false },
                    { targets: [3, 6], orderable: true, searchable: true }
                ],
            });
        } else {
            window.faiDT = $('#faiTable').DataTable();
        }

        // Filtro inicial: mes actual (solo si no hay mes ni año) o el mes elegido
        function applyMonthFilter() {
            if (!$.fn.DataTable.isDataTable('#faiTable')) return;
            if (faiDT.settings()[0]?.oFeatures?.bServerSide) return;
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
                faiDT.search(val).draw();
            }, 300);
            $input.on('input', handler);
            $input.on('keydown', function(e) {
                if (e.key === 'Enter') e.preventDefault();
            });
            if (clearId) {
                $(clearId).on('click', function() {
                    $input.val('').trigger('input');
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
            if (v && typeof v === 'object') {
                if (typeof v.filter === 'string') return $('<div>').html(v.filter).text();
                if (typeof v.display === 'string') return $('<div>').html(v.display).text();
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
        let serverHeaderOptions = null;

        function setHeaderSelectOptions(selectId, values, formatter) {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            const current = sel.value || '';
            const isObjectList = Array.isArray(values) && values.length > 0 && typeof values[0] === 'object' && values[0] !== null;
            const list = isObjectList
                ? values
                    .map(v => ({
                        value: String((v && v.value) || '').trim(),
                        count: Number((v && v.count) || 0),
                    }))
                    .filter(v => v.value !== '')
                : uniqueSorted((values || []).map(v => String(v || '').trim()).filter(Boolean)).map(v => ({
                    value: v,
                    count: null,
                }));

            while (sel.options.length > 1) sel.remove(1);
            const frag = document.createDocumentFragment();
            list.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.value;
                const labelBase = typeof formatter === 'function' ? formatter(item.value) : item.value;
                opt.textContent = (item.count !== null && !Number.isNaN(item.count))
                    ? `${labelBase} (${item.count})`
                    : labelBase;
                frag.appendChild(opt);
            });
            sel.appendChild(frag);
            if (current && list.some(x => x.value === current)) sel.value = current;
        }

        function bindHeaderExactFilter(selectId, colIndex) {
            const el = document.getElementById(selectId);
            if (!el) return;
            el.addEventListener('change', function() {
                const v = (this.value || '').trim();
                faiDT.column(colIndex).search(v).draw();
            });
        }

        function bindHeaderDayFilter() {
            const el = document.getElementById('headDayFilter');
            if (!el) return;
            el.addEventListener('change', function() {
                const v = (this.value || '').trim();
                faiDT.column(0).search(v).draw();
            });
        }

        function populateHeaderDayFilter() {
            if (faiDT.settings()[0]?.oFeatures?.bServerSide && serverHeaderOptions) {
                setHeaderSelectOptions('headDayFilter', serverHeaderOptions.date || []);
                return;
            }
            const applied = faiDT.column(0, { search: 'applied' }).data().toArray();
            const removed = faiDT.column(0, { search: 'removed' }).data().toArray();
            setHeaderSelectOptions('headDayFilter', applied.concat(removed).map(extractDayLabel));
        }

        bindHeaderDayFilter();
        bindHeaderExactFilter('headTypeFilter', COLS.type);
        bindHeaderExactFilter('headOperationFilter', COLS.operation);
        bindHeaderExactFilter('headOperatorFilter', COLS.operator);
        bindHeaderExactFilter('headResultFilter', COLS.result);
        bindHeaderExactFilter('headStationFilter', COLS.station);
        bindHeaderExactFilter('headMethodFilter', COLS.method);
        bindHeaderExactFilter('headQtyFilter', 11);
        bindHeaderExactFilter('headInspectorFilter', COLS.inspector);
        bindHeaderExactFilter('headLocationFilter', COLS.location);

        function repopulateHeaderFilters() {
            if (faiDT.settings()[0]?.oFeatures?.bServerSide && serverHeaderOptions) {
                setHeaderSelectOptions('headDayFilter', serverHeaderOptions.date || []);
                setHeaderSelectOptions('headTypeFilter', serverHeaderOptions.type || []);
                setHeaderSelectOptions('headOperationFilter', serverHeaderOptions.operation || []);
                setHeaderSelectOptions('headOperatorFilter', serverHeaderOptions.operator || []);
                setHeaderSelectOptions('headResultFilter', ['pass', 'no pass'], function(v) {
                    const s = String(v || '').trim().toLowerCase();
                    if (!s) return '';
                    return s === 'pass' ? 'Pass' : (s === 'no pass' ? 'No pass' : s.charAt(0).toUpperCase() + s.slice(1));
                });
                setHeaderSelectOptions('headStationFilter', serverHeaderOptions.station || []);
                setHeaderSelectOptions('headMethodFilter', serverHeaderOptions.method || []);
                setHeaderSelectOptions('headQtyFilter', serverHeaderOptions.qty_insp || []);
                setHeaderSelectOptions('headInspectorFilter', serverHeaderOptions.inspector || []);
                setHeaderSelectOptions('headLocationFilter', serverHeaderOptions.location || []);
                return;
            }

            populateHeaderDayFilter();
            setHeaderSelectOptions('headTypeFilter', faiDT.column(COLS.type, { search: 'applied' }).data().toArray().concat(
                faiDT.column(COLS.type, { search: 'removed' }).data().toArray()
            ).map(getText));
            setHeaderSelectOptions('headOperationFilter', faiDT.column(COLS.operation, { search: 'applied' }).data().toArray().concat(
                faiDT.column(COLS.operation, { search: 'removed' }).data().toArray()
            ).map(getText));
            setHeaderSelectOptions('headOperatorFilter', faiDT.column(COLS.operator, { search: 'applied' }).data().toArray().concat(
                faiDT.column(COLS.operator, { search: 'removed' }).data().toArray()
            ).map(getText));
            setHeaderSelectOptions('headResultFilter', ['pass', 'no pass'], function(v) {
                const s = String(v || '').trim().toLowerCase();
                return s === 'pass' ? 'Pass' : (s === 'no pass' ? 'No pass' : s);
            });
            setHeaderSelectOptions('headStationFilter', faiDT.column(COLS.station, { search: 'applied' }).data().toArray().concat(
                faiDT.column(COLS.station, { search: 'removed' }).data().toArray()
            ).map(getText));
            setHeaderSelectOptions('headMethodFilter', faiDT.column(COLS.method, { search: 'applied' }).data().toArray().concat(
                faiDT.column(COLS.method, { search: 'removed' }).data().toArray()
            ).map(getText));
            setHeaderSelectOptions('headQtyFilter', faiDT.column(11, { search: 'applied' }).data().toArray().concat(
                faiDT.column(11, { search: 'removed' }).data().toArray()
            ).map(getText));
            setHeaderSelectOptions('headInspectorFilter', faiDT.column(COLS.inspector, { search: 'applied' }).data().toArray().concat(
                faiDT.column(COLS.inspector, { search: 'removed' }).data().toArray()
            ).map(getText));
            setHeaderSelectOptions('headLocationFilter', faiDT.column(COLS.location, { search: 'applied' }).data().toArray().concat(
                faiDT.column(COLS.location, { search: 'removed' }).data().toArray()
            ).map(getText));
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
                const val = (this.value || '').trim();
                faiDT.column(colIndex).search(val).draw();
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
        faiDT.on('xhr.dt', function(e, settings, json) {
            serverHeaderOptions = (json && json.filterOptions) ? json.filterOptions : null;
            repopulateHeaderFilters();
        });
        faiDT.on('draw', function() {
            repopulateAllFilters();
            repopulateHeaderFilters();
            renderActiveFilterChips();
        });

        function renderActiveFilterChips() {
            const box = document.getElementById('activeFilterChips');
            if (!box) return;

            const chips = [];
            const pushIf = (id, label) => {
                const el = document.getElementById(id);
                if (!el) return;
                const raw = (el.value || '').trim();
                if (!raw) return;
                const val = (id === 'headResultFilter')
                    ? (raw.toLowerCase() === 'pass' ? 'Pass' : (raw.toLowerCase() === 'no pass' ? 'No pass' : raw))
                    : raw;
                chips.push({ id, label, value: val });
            };

            pushIf('headDayFilter', 'Date');
            pushIf('headTypeFilter', 'Type');
            pushIf('headOperationFilter', 'Operation');
            pushIf('headOperatorFilter', 'Operator');
            pushIf('headResultFilter', 'Result');
            pushIf('headStationFilter', 'Station');
            pushIf('headMethodFilter', 'Method');
            pushIf('headQtyFilter', 'Qty');
            pushIf('headInspectorFilter', 'Inspector');
            pushIf('headLocationFilter', 'Location');

            const q = (document.getElementById('globalSearch')?.value || '').trim();
            if (q) chips.push({ id: 'globalSearch', label: 'Search', value: q });

            if (!chips.length) {
                box.classList.add('d-none');
                box.innerHTML = '';
                return;
            }

            const html = chips.map(c => (
                `<span class="fai-filter-chip" data-id="${c.id}">
                    ${c.label}: ${$('<div>').text(c.value).html()}
                    <button type="button" class="chip-remove" data-id="${c.id}" title="Remove">x</button>
                </span>`
            )).join('') + `<button type="button" class="fai-clear-filters" id="clearAllTableFilters">Clear filters</button>`;

            box.classList.remove('d-none');
            box.innerHTML = html;
        }

        $(document).on('click', '.fai-filter-chip .chip-remove', function() {
            const id = String($(this).data('id') || '').trim();
            if (!id) return;
            const el = document.getElementById(id);
            const colById = {
                headDayFilter: 0,
                headTypeFilter: COLS.type,
                headOperationFilter: COLS.operation,
                headOperatorFilter: COLS.operator,
                headResultFilter: COLS.result,
                headStationFilter: COLS.station,
                headMethodFilter: COLS.method,
                headQtyFilter: 11,
                headInspectorFilter: COLS.inspector,
                headLocationFilter: COLS.location
            };

            if (id === 'globalSearch') {
                $('#globalSearch').val('');
                faiDT.search('').draw();
                return;
            }
            if (!el) return;
            if (typeof el.selectedIndex === 'number') {
                el.selectedIndex = 0;
            }
            el.value = '';
            if (Object.prototype.hasOwnProperty.call(colById, id)) {
                faiDT.column(colById[id]).search('').draw();
            } else {
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        $(document).on('click', '#clearAllTableFilters', function() {
            const ids = [
                'headDayFilter', 'headTypeFilter', 'headOperationFilter', 'headOperatorFilter', 'headResultFilter',
                'headStationFilter', 'headMethodFilter', 'headQtyFilter',
                'headInspectorFilter', 'headLocationFilter'
            ];
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.value = '';
            });
            $('#globalSearch').val('');

            faiDT.columns().every(function() {
                this.search('');
            });
            faiDT.search('').draw();
            renderActiveFilterChips();
        });

        function updateKpisFromDT() {
            if (!$('#kpi-total').length) return;
            if (faiDT.settings()[0]?.oFeatures?.bServerSide) return;
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

        // Pickers visibles del toolbar: aplicar filtro por URL (year/month/day)
        (function bindToolbarDateFilters() {
            const $year = $('#year');
            const $month = $('#month');
            const $day = $('#day');
            if (!$year.length || !$month.length || !$day.length) return;

            let lastKey = null;
            const apply = function() {
                const y = String($year.val() || '').trim();
                const m = String($month.val() || '').trim();
                const d = String($day.val() || '').trim();
                const key = [y, m, d].join('|');
                if (key === lastKey) return;
                lastKey = key;

                const url = new URL(window.location.href);
                url.searchParams.delete('page');
                url.searchParams.delete('focus_order_id');
                url.searchParams.delete('focus_work_id');
                url.searchParams.delete('focus_pn');
                url.searchParams.delete('focus_months');

                if (y) url.searchParams.set('year', y);
                else url.searchParams.delete('year');

                if (m) url.searchParams.set('month', m);
                else url.searchParams.delete('month');

                if (d) url.searchParams.set('day', d);
                else url.searchParams.delete('day');

                const next = url.toString();
                if (next !== window.location.href) window.location.assign(next);
            };

            const debouncedApply = debounce(apply, 150);
            $('#yearPickerWrapper,#monthPickerWrapper,#dayPickerWrapper').on('change.datetimepicker', debouncedApply);
            $year.on('change blur', debouncedApply);
            $month.on('change', debouncedApply);
            $day.on('change blur', debouncedApply);
        })();

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
                faiDT.column(COLS.result).search('pass').draw();
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
                faiDT.column(COLS.result).search('no pass').draw();
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
