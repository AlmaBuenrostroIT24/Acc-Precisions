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



{{-- Tabs --}}
@include('qa.faisummary.faisummary_tab')

{{-- Tab: By Active Schedules --}}

<div class="row">

    {{-- Columna izquierda: KPI / otro contenido --}}
    <div class="col-lg-3">
        <div class="row">
            {{-- Columna A: Filtros --}}
            <div class="col-md-12">
                <div class="card mb-3 sticky-top" style="top: 10px;">
                    <div class="card-header py-2">
                        <strong><i class="fas fa-filter mr-2"></i>Filters</strong>
                    </div>
                    <div class="card-body">
                        {{-- ======= TU FORMULARIO ======= --}}
                        <form method="GET" action="{{ route('faisummary.general') }}" id="filtersForm">
                            {{-- Global Search --}}
                            <div class="form-group mb-2">
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


                            {{-- Location --}}
                            <div class="form-group mb-2">
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

                            {{-- MONTH --}}
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

                            {{-- Clean + Total --}}
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

            {{-- Columna B: KPI/Resumen --}}
            <div class="col-md-12">
                <div class="card mb-3 sticky-top" style="top: 10px;">
                    <div class="card-header py-2">
                        <strong><i class="fas fa-chart-bar mr-2"></i>Summary</strong>
                    </div>
                    <div class="card-body p-2">
                        {{-- KPI compactos --}}
                        <div class="info-box info-box-sm bg-light mb-2">
                            <span class="info-box-icon"><i class="fas fa-clipboard-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Inspections Completed</span>
                                <h5 class="mb-0" id="kpiTotal">0</h5>
                            </div>
                        </div>

                        <div class="info-box info-box-sm bg-secondary mb-2">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Completed</span>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0" id="kpiPass">0</h5>
                                    <small class="text-white-50">Approved</small>
                                </div>
                            </div>
                        </div>

                        <div class="info-box info-box-sm bg-info mb-2">
                            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">No Pass</span>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0" id="kpiFail">0</h5>
                                    <small class="text-white-50">Rejected</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Columna derecha: Tabla --}}
    <div class="col-lg-9">
        <div class="card mb-4">
            {{-- Header de la tabla --}}
            <div class="card-header py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-list-alt mr-2 text-primary"></i>
                    <strong>FAI Completed</strong>
                    <span class="badge badge-primary ml-2">
                        {{ isset($orderscompleted) ? $orderscompleted->count() : 0 }} rows
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sticky" style="table-layout: fixed; width: 100%;">
                        <thead class="table-light thead-custom">
                            <tr>
                                <th style="width: 50px;">DATE</th>
                                <th style="width: 30px;">LOC.</th>
                                <th style="width: 40px;">WORK ID</th>
                                <th style="width: 50px;">PN</th>
                                <th style="width: 90px;">DESCRIPTION</th>
                                <th style="width: 50px;">SAMP. PLAN</th>
                                <th style="width: 40px;">WO QTY</th>
                                <th style="width: 28px;">SAMP.</th>
                                <th style="width: 20px;">OPS.</th>
                                <th style="width: 20px;">FAI</th>
                                <th style="width: 20px;">IPI</th>
                                {{-- Nueva columna --}}
                                <th style="width: 60px;">PROG.</th>
                                <th style="width: 35px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderscompleted as $o)
                            @php
                            // Requerimientos
                            $faiReqPcs = (int) ($o->total_fai ?? 0); // piezas requeridas para IPI
                            $ipiReqPcs = (int) ($o->total_ipi ?? 0); // piezas requeridas para IPI

                            // Piezas aprobadas desde qa_faisummary (withSum)
                            $faiPassQty = (int) ($o->fai_pass_qty ?? 0);
                            $ipiPassQty = (int) ($o->ipi_pass_qty ?? 0);

                            // % de avance
                            $faiPct = $faiReqPcs > 0 ? min(100, (int) round(($faiPassQty / $faiReqPcs) * 100)) : 100;
                            $ipiPct = $ipiReqPcs > 0 ? min(100, (int) round(($ipiPassQty / $ipiReqPcs) * 100)) : 100;

                            // Overall = el más bajo (para que no se marque 100% si uno quedó incompleto)
                            $overall = min($faiPct, $ipiPct);

                            // Se completa cuando ambas metas se alcanzan
                            $completed = ($faiReqPcs === 0 || $faiPassQty >= $faiReqPcs)
                            && ($ipiReqPcs === 0 || $ipiPassQty >= $ipiReqPcs);

                            $barClass = $completed ? 'bg-success'
                            : ($overall >= 75 ? 'bg-info'
                            : ($overall >= 50 ? 'bg-warning' : 'bg-danger'));
                            @endphp
                            <tr id="row-{{ $o->id }}">
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
                                <td>{{ ucfirst($o->sampling_check) }}</td>
                                <td>{{ $o->group_wo_qty }}</td>
                                <td>{{ $o->sampling }}</td>
                                <td>{{ $o->operation }}</td>
                                <td>{{ $o->total_fai }}</td>
                                <td>{{ $o->total_ipi }}</td>
                                {{-- PROGRESO desde BD --}}
                                {{-- Columna PROGRESO --}}
                                <td>
                                    <div class="progress" style="height:18px;"
                                        title="FAI {{ $faiPassQty }}/{{ $faiReqPcs }} ({{ $faiPct }}%) • IPI {{ $ipiPassQty }}/{{ $ipiReqPcs }} ({{ $ipiPct }}%)">
                                        <div class="progress-bar {{ $barClass }}" style="width: {{ $overall }}%;" aria-valuenow="{{ $overall }}" aria-valuemin="0" aria-valuemax="100">
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
                                    <a href="#"
                                        class="btn btn-sm btn-primary btn-open-pdf"
                                        data-pdf-url="{{ route('qa.faisummary.pdf', $o->id) }}">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="{{ route('qa.faisummary.pdf', $o->id) }}?download=1"
                                        class="btn btn-sm btn-warning">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="text-center">No hay registros completados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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

@endsection


@push('js')
<script>
    $(document).on('click', '.btn-open-pdf', function(e) {
        e.preventDefault();
        const url = $(this).data('pdf-url'); // ← usa la URL del botón
        $('#pdfEmbed').attr('src', url + '#zoom=page-width');
        $('#pdfModal').modal('show');
    });
    $('#pdfModal').on('hidden.bs.modal', function() {
        $('#pdfEmbed').attr('src', '');
    });

    document.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById('faiTable');
        const btnDensity = document.getElementById('btnDensity');
        const btnReload = document.getElementById('btnReload');

        if (btnDensity && table) {
            btnDensity.addEventListener('click', () => {
                table.classList.toggle('table-compact');
            });
        }

        if (btnReload) {
            btnReload.addEventListener('click', () => {
                // Si usas DataTables con AJAX:
                // const dt = $('#faiTable').DataTable();
                // dt.ajax.reload();

                // Si NO usas AJAX:
                window.location.reload();
            });
        }
    });
</script>
@endpush