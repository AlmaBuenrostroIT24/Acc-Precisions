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
                @php
                $hasAlerts = isset($failedOrders) && $failedOrders->count() > 0;
                $progressHeight = $hasAlerts ? '12px' : '22px';
                // Si hay alertas => KPIs col-lg-2, si no => col-lg-3
                $kpiColClass = $hasAlerts ? 'col-sm-6 col-lg-2 mb-2' : 'col-sm-6 col-lg-3 mb-2';
                @endphp

                <div class="row mb-1">
                    {{-- Total Inspections --}}
                    <div class="{{ $kpiColClass }}">
                        <div class="info-box bg-secondary">
                            <span class="info-box-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </span>
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
                    <div class="{{ $kpiColClass }}">
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
                    <div class="{{ $kpiColClass }}">
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
                    <div class="{{ $kpiColClass }}">
                        <div class="info-box bg-info">
                            <span class="info-box-icon">
                                <i class="fas fa-percentage"></i>
                            </span>
                            <div class="info-box-content">
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <h3 class="mb-0 font-weight-bold">{{ $monthStats['rate'] }}%</h3>
                                    <small class="text-white-50">Meta ≥ 95%</small>
                                </div>
                                <div class="progress mt-2" style="height: {{ $progressHeight }};">
                                    <div class="progress-bar bg-light" style="width: {{ $monthStats['rate'] }}%"></div>
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
                                <th>Qty Insp.</th>
                                <th>Inspector</th>
                                <th>Location</th>
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
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ route('faisummary.export.excel', request()->query()) }}"
                            class="btn btn-success btn-sm mr-2">
                            <i class="far fa-file-excel mr-1"></i>Excel
                        </a>

                        <a href="{{ route('faisummary.export.pdf', request()->query()) }}"
                            class="btn btn-danger btn-sm"
                            target="_blank">
                            <i class="far fa-file-pdf mr-1"></i>PDF
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
            inspector: 12,
            location: 13,
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

        // =========================
        //  Click en chips: filtrar historial de esa orden (toggle)
        // =========================
        $(document).on('click', '.fai-chip', function() {
            if (!$.fn.DataTable.isDataTable('#faiTable')) {
                return;
            }

            const dt = $('#faiTable').DataTable(); // instancia segura
            const $chip = $(this);

            // 🧲 Si este chip YA está activo ⇒ quitar filtro y salir
            if ($chip.hasClass('fai-chip-active')) {
                // Limpia búsquedas de DataTables
                dt.search('');
                dt.columns().search('');
                dt.draw();

                // Quita marcado visual
                $('.fai-chip').removeClass('fai-chip-active');
                return;
            }

            // 🔄 Si es un chip NUEVO ⇒ aplicar filtro
            let pn = $chip.data('pn') || '';
            let workId = $chip.data('work-id') || '';

            pn = pn.toString().trim();
            workId = workId.toString().trim();

            // Limpia búsquedas previas
            dt.search('');
            dt.columns().search('');

            // Índices: 0=DATE, 1=PART/REVISION, 2=JOB
            const PN_COL = 1;
            const JOB_COL = 2;

            // Filtrar por JOB (work_id)
            if (workId) {
                dt.column(JOB_COL).search(workId, false, false);
            }

            // Filtrar también por PN
            if (pn) {
                dt.column(PN_COL).search(pn, false, false);
            }

            dt.draw();

            // Marcar este chip como activo y limpiar los demás
            $('.fai-chip').removeClass('fai-chip-active');
            $chip.addClass('fai-chip-active');
        });




    });
</script>


@endpush