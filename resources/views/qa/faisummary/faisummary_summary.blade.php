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



{{-- Tabs --}}
@include('qa.faisummary.faisummary_tab')

{{-- Tab: By Active Schedules --}}

<div class="row">
    {{-- === Columna izquierda: TABLA === --}}
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ===== Dashboard KPI Cards (Full width) ===== --}}
                <div class="row mb-1">
                    {{-- Total Inspections --}}
                    <div class="col-sm-6 col-lg-3 mb-2">
                        <div class="info-box bg-info">
                            {{-- Icono lateral --}}
                            <span class="info-box-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </span>

                            {{-- Contenido --}}
                            <div class="info-box-content">
                                <span class="info-box-text">Inspections</span>
                                <h3 class="mb-1">{{ number_format($monthStats['total']) }}</h3>
                                <div class="d-flex justify-content-between">
                                    <small>{{ \Carbon\Carbon::create()->month($monthStats['month'])->format('M') }}</small>
                                    <small>{{ $monthStats['year'] }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pass --}}
                    <div class="col-sm-6 col-lg-3 mb-2">
                        <div class="info-box bg-success">
                            <span class="info-box-icon">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pass</span>
                                <h3 class="mb-1">{{ number_format($monthStats['pass']) }}</h3>
                                <div class="d-flex justify-content-between">
                                    <small>Approved</small>
                                    <small>{{ \Carbon\Carbon::create()->month($monthStats['month'])->format('M') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- No Pass --}}
                    <div class="col-sm-6 col-lg-3 mb-2">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon">
                                <i class="fas fa-times-circle"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">No Pass</span>
                                <h3 class="mb-1">{{ number_format($monthStats['fail']) }}</h3>
                                <div class="d-flex justify-content-between">
                                    <small>Rejected</small>
                                    <small>{{ \Carbon\Carbon::create()->month($monthStats['month'])->format('M') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- % Pass --}}
                    <div class="col-sm-6 col-lg-3 mb-2">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon">
                                <i class="fas fa-percentage"></i>
                            </span>
                            <div class="info-box-content">

                                {{-- Número y meta en la misma fila --}}
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <b>
                                        <h3 class="mb-0">{{ $monthStats['rate'] }}%</h3>
                                    </b>
                                    <small class="text-white-50">Meta ≥ 95%</small>
                                </div>

                                {{-- Barra de progreso más gruesa --}}
                                <div class="progress mt-2" style="height: 22px;">
                                    <div class="progress-bar bg-light" style="width: {{ $monthStats['rate'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="faiTable" class="table table-sm table-striped table-bordered align-middle mb-0">
                        <colgroup>
                            <col style="width:200px">
                            <col style="width:140px">
                            <col style="width:100px">
                            <col style="width:70px">
                            <col style="width:90px">
                            <col style="width:110px">
                            <col style="width:90px">
                            <col style="width:160px">
                            <col style="width:160px">
                            <col style="width:90px">
                            <col style="width:100px">
                            <col style="width:120px">
                            <col style="width:100px">
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
                                <th>Inspector</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspections as $inspection)
                            @php
                            $tz = config('app.timezone', 'UTC'); // cambia si usas otra zona
                            $dtCreated = $inspection->created_at
                            ? $inspection->created_at->copy()->setTimezone($tz)
                            : null;

                            $isPass = strcasecmp(trim((string)$inspection->results), 'pass') === 0;
                            $isFAI = strcasecmp(trim((string)$inspection->insp_type), 'FAI') === 0;
                            @endphp
                            <tr>
                                <td data-order="{{ $dtCreated?->format('Y-m-d H:i:s') }}">
                                    {{ $dtCreated?->format('M-d-y') }}
                                    @if($dtCreated)
                                    <span class="badge badge-light">{{ $dtCreated->format('H:i') }}</span>
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
                                <td class="truncate" title="{{ $inspection->inspector }}">{{ $inspection->inspector }}</td>
                                <td>{{ strtoupper($inspection->orderSchedule->location ?? '') }}</td>
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
        <div class="card shadow-sm mb-3 filters-card-fixed">
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
                        <a href="{{ route('faisummary.general') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-eraser mr-1"></i> Clean
                        </a>

                        <span class="badge badge-info py-2 px-3" style="font-size: 1rem;">
                            <i class="fas fa-list-ol mr-1"></i>
                            Total: <span id="badgeFinished">{{ isset($orders) ? count($orders) : 0 }}</span>
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
    /* Compactar info-box para KPIs */
    .info-box {
        min-height: 70px;
        /* por defecto ~120px */
        padding: .5rem;
    }

    .info-box .info-box-icon {
        width: 60px;
        height: 60px;
        font-size: 58px;
        line-height: 60px;
    }

    .info-box .info-box-content {
        margin-left: .5rem;
        line-height: 1.2;
    }

    .info-box .info-box-text {
        font-size: .8rem;
        font-weight: 600;
    }

    .info-box .info-box-number {
        font-size: 1.1rem;
        font-weight: 700;
    }
</style>

@endsection


@push('js')
<script src="{{ asset('vendor/js/date-filters.js') }}"></script>
<script>
    $(document).ready(function() {

        // =========================
        //  DataTable
        // =========================
        if (!$.fn.DataTable.isDataTable('#faiTable')) {
            window.faiDT = $('#faiTable').DataTable({
                scrollX: false,
                autoWidth: false,
                pageLength: 15,
                dom: 'rtip', // <- sin buscador global nativo
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
        $('#globalSearch').on('input', debounce(function() {
            const val = this.value || '';
            faiDT.search(val).draw();
        }, 200));
        $('#clearGlobalSearch').on('click', function() {
            $('#globalSearch').val('');
            faiDT.search('').draw();
        });
        // Evita submit del form al presionar Enter
        $('#globalSearch').on('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });


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
            inspector: 11,
            location: 12,
        };

        // =========================
        //  Helpers filtros exactos
        // =========================
        function getText(v) {
            if (typeof v === 'string') return v;
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
                    const esc = $.fn.dataTable.util.escapeRegex(val);
                    faiDT.column(colIndex).search('^' + esc + '$', true, false).draw();
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
            }, // solo si existe en tu HTML
            {
                id: 'methodFilter',
                col: COLS.method
            }, // solo si existe en tu HTML
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
        faiDT.on('draw', repopulateAllFilters);

        // Badge Total
        const $badge = $('#badgeFinished');

        function refreshBadge() {
            $badge.text(faiDT.rows({
                search: 'applied'
            }).count());
        }
        refreshBadge();
        faiDT.on('draw.dt search.dt order.dt page.dt', refreshBadge);

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
    });
</script>


@endpush