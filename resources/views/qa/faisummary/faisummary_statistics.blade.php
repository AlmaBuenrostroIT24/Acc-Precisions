<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary Statistics')

{{-- ✅ Un solo content_header --}}

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
<li class="breadcrumb-item active" aria-current="page">FAI Summary Statistics</li>
</ol>
</nav>
</div>
</div>
@endsection
--}}

@section('content')


{{-- Tabs --}}
@include('qa.faisummary.faisummary_tab')

<div class="card-body">
    {{-- Filtros + KPIs --}}
    <div class="row mb-3 align-items-center">
        {{-- Año --}}
        <div class="col-sm-6 col-md-2 d-flex">
            <div class="w-100 d-flex flex-column justify-content-center">
                <label for="yearSelect" class="form-label mb-1">Year</label>
                <div class="input-group" style="max-width: 280px">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-calendar-alt text-info"></i>
                        </span>
                    </div>
                    <select id="yearSelect" class="form-control">
                        @php $yNow = (int) now()->format('Y'); @endphp
                        @for($y=$yNow; $y>=$yNow-1; $y--)
                        <option value="{{ $y }}" @selected($y==$yNow)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="col-md-10">
            <div class="row no-gutters justify-content-end">
                <div class="col-12 col-sm-6 col-md-3 px-2">
                    <div class="small-box bg-info shadow-sm kpi-box">
                        <div class="inner">
                            <h3 id="k_total" class="mb-1">0</h3>
                            <p class="mb-0">Total inspections</p>
                        </div>
                        <div class="icon"><i class="fas fa-clipboard-check"></i></div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 px-2">
                    <div class="small-box bg-success shadow-sm kpi-box">
                        <div class="inner">
                            <h3 id="k_pass_pct" class="mb-1">0%</h3>
                            <p class="mb-0">% Pass</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 px-2">
                    <div class="small-box bg-danger shadow-sm kpi-box">
                        <div class="inner">
                            <h3 id="k_fail_pct" class="mb-1">0%</h3>
                            <p class="mb-0">% No Pass</p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- FILA 1: Global (gráfica + tabla en una sola card) --}}
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 d-flex align-items-center">
                    <strong class="mr-auto">
                        <i class="fas fa-chart-bar mr-1 text-info"></i>% Pass vs % No Pass — by Quarter (Q1–Q4)
                    </strong>

                    <small class="text-muted mr-3">
                        Year: <span id="lblYear">{{ request('year') ?? now()->year }}</span>
                    </small>
                    <div class="btn-group btn-group-sm ml-auto" role="group" aria-label="quarters actions">
                        <button id="btnExportQuartersCsv" type="button" class="btn btn-erp-gray">
                            <i class="fas fa-file-csv mr-1"></i>CSV
                        </button>
                        <button id="btnExportQuartersPdf" type="button" class="btn btn-erp-gray">
                            <i class="fas fa-file-pdf mr-1"></i>PDF
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row align-items-stretch">
                        <!-- Gráfica -->
                        <div class="col-md-5 d-flex mb-3 mb-md-0" style="border-right: 1px solid #ddd;">
                            <div class="w-100">
                                <div class="chart-wrapper" style="height: 420px;">
                                    <canvas id="quartersChart"
                                        aria-label="Bar chart pass vs fail per quarter" role="img"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="col-md-7 d-flex">
                            <div class="w-100">
                                <div class="table-responsive" style="max-height: 360px; overflow: auto; padding: .5rem;">
                                    <table class="table table-sm table-striped table-hover mb-0" id="quartersTable" aria-live="polite">
                                        <colgroup>
                                            <col style="width:18%">
                                            <col style="width:14%">
                                            <col style="width:14%">
                                            <col style="width:14%">
                                            <col style="width:20%">
                                            <col style="width:20%">
                                        </colgroup>
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th scope="col">Quarter</th>
                                                <th scope="col" class="text-center">Total</th>
                                                <th scope="col" class="text-center">Pass</th>
                                                <th scope="col" class="text-center">No Pass</th>
                                                <th scope="col" class="text-center">% Pass</th>
                                                <th scope="col" class="text-center">% No Pass</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="table-placeholder">
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="fas fa-spinner fa-spin mr-2"></i> Loading...
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="font-weight-bold">
                                                <td class="text-center">Total</td>
                                                <td class="text-center" id="q_sum_total">0</td>
                                                <td class="text-center" id="q_sum_pass">0</td>
                                                <td class="text-center" id="q_sum_fail">0</td>
                                                <td class="text-center" id="q_sum_pass_pct">0%</td>
                                                <td class="text-center" id="q_sum_fail_pct">0%</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div> {{-- /.row --}}
                </div> {{-- /.card-body --}}
            </div>
        </div>
        {{-- FILA 3: Por Inspector (tabla + gráfica lado a lado) --}}
       
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-2 d-flex align-items-center">
                        <strong class="mr-auto">
                            <i class="fas fa-user-check mr-1 text-primary"></i>By Inspector
                        </strong>
                        <div class="btn-group btn-group-sm ml-auto" role="group" aria-label="inspector actions">
                            <button id="btnExportInspectorCsv" type="button" class="btn btn-erp-gray">
                                <i class="fas fa-file-csv mr-1"></i>CSV
                            </button>
                            <button id="btnPrintInspector" type="button" class="btn btn-erp-gray">
                                <i class="fas fa-file-pdf mr-1"></i>PDF
                            </button>
                        </div>
                    </div>
                    {{-- Filtros SOLO para Inspector --}}
                    <div class="px-3 pt-3">
                        <div class="form-row align-items-end">
                            <div class="form-group col-6 col-md-3">
                                <label class="mb-1" for="insp_gran">Period</label>
                                <select id="insp_gran" class="form-control">
                                    <option value="year" selected>Year</option>
                                    <option value="quarter">Quarter</option>
                                    <option value="month">Month</option>
                                    <option value="week">Week</option>
                                    <option value="day">Day</option>
                                </select>
                            </div>

                            <div class="form-group col-6 col-md-2">
                                <label class="mb-1" for="insp_year">Year</label>
                                <select id="insp_year" class="form-control">
                                    @php $yNow = (int) now()->format('Y'); @endphp
                                    @for($y=$yNow; $y>=$yNow-5; $y--)
                                    <option value="{{ $y }}" @selected($y==$yNow)>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group col-6 col-md-2 d-none" data-insp-filter="quarter">
                                <label class="mb-1" for="insp_quarter">Quarter</label>
                                <select id="insp_quarter" class="form-control">
                                    <option value="1">Qtr1</option>
                                    <option value="2">Qtr2</option>
                                    <option value="3">Qtr3</option>
                                    <option value="4">Qtr4</option>
                                </select>
                            </div>

                            <div class="form-group col-6 col-md-2 d-none" data-insp-filter="month">
                                <label class="mb-1" for="insp_month">Month</label>
                                <select id="insp_month" class="form-control">
                                    @for($m=1;$m<=12;$m++)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                        @endfor
                                </select>
                            </div>

                            <div class="form-group col-6 col-md-3 d-none" data-insp-filter="week">
                                <label class="mb-1" for="insp_week">Week</label>
                                <input id="insp_week" type="week" class="form-control">
                            </div>

                            <div class="form-group col-6 col-md-3 d-none" data-insp-filter="day">
                                <label class="mb-1" for="insp_day">Day</label>
                                <input id="insp_day" type="date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row align-items-stretch">
                            <div class="col-md-7 mb-3 mb-md-0" style="border-right: 1px solid #ddd;">
                                <div class="table-responsive" style="max-height: 360px; overflow:auto;">
                                    <table class="table table-sm table-striped table-hover mb-0" id="tblInspector">
                                        <colgroup>
                                            <col style="width:30%">
                                            <col style="width:14%">
                                            <col style="width:14%">
                                            <col style="width:14%">
                                            <col style="width:14%">
                                            <col style="width:14%">
                                        </colgroup>
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Inspector</th>
                                                <th class="text-center">Total</th>
                                                <th class="text-center">Pass</th>
                                                <th class="text-center">No Pass</th>
                                                <th class="text-center">% Pass</th>
                                                <th class="text-center">% No Pass</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-md-5 d-flex">
                                <div class="w-100">
                                    <div class="chart-wrapper" style="height: 360px;">
                                        <canvas id="inspectorChart" aria-label="Gráfica por inspector" role="img"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div> {{-- /.row --}}
                    </div> {{-- /.card-body --}}
                </div>
            </div>
        </div>
    </div>



    {{-- Resumen por tipo (FAI / IPI) --}}
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 d-flex align-items-center">
                    <strong class="mr-auto">
                        <i class="fas fa-layer-group mr-1 text-primary"></i>FAI / IPI Overview
                    </strong>
                    <div class="btn-group btn-group-sm ml-auto" role="group" aria-label="types actions">
                        <button id="btnExportTypesCsv" type="button" class="btn btn-erp-gray">
                            <i class="fas fa-file-csv mr-1"></i>CSV
                        </button>
                        <button id="btnExportTypesPdf" type="button" class="btn btn-erp-gray">
                            <i class="fas fa-file-pdf mr-1"></i>PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-stretch">
                        <div class="col-md-5 mb-3 mb-md-0" style="border-right: 1px solid #ddd;">
                            <div class="table-responsive" style="max-height: 260px;">
                                <table class="table table-sm table-striped table-hover mb-0" id="tblTypes">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Type</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Pass</th>
                                            <th class="text-center">No Pass</th>
                                            <th class="text-center">% Pass</th>
                                            <th class="text-center">% No Pass</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-7 d-flex">
                            <div class="w-100">
                                <div class="chart-wrapper" style="height: 260px;">
                                    <canvas id="typeChart" aria-label="Chart FAI vs IPI" role="img"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- FILA 2: Por Operador (tabla + gráfica lado a lado) --}}
    <div class="row mt-3">
        <div class="col-lg-12 mb-3 mb-lg-0">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 d-flex align-items-center">
                    <strong class="mr-auto">
                        <i class="fas fa-user-cog mr-1 text-success"></i>By Operator
                    </strong>
                    <div class="btn-group btn-group-sm ml-auto" role="group" aria-label="operator actions">
                        <button id="btnExportOperatorCsv" type="button" class="btn btn-erp-gray">
                            <i class="fas fa-file-csv mr-1"></i>CSV
                        </button>
                        <button id="btnPrintOperator" type="button" class="btn btn-erp-gray">
                            <i class="fas fa-file-pdf mr-1"></i>PDF
                        </button>
                    </div>
                </div>
                {{-- Filtros SOLO para Operador --}}
                <div class="px-3 pt-3">
                    <div class="form-row align-items-end">
                        <div class="form-group col-6 col-md-3">
                            <label class="mb-1" for="op_gran">Period</label>
                            <select id="op_gran" class="form-control">
                                <option value="year" selected>Year</option>
                                <option value="quarter">Quarter</option>
                                <option value="month">Month</option>
                                <option value="week">Week</option>
                                <option value="day">Day</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-md-2">
                            <label class="mb-1" for="op_year">Year</label>
                            <select id="op_year" class="form-control">
                                @php $yNow = (int) now()->format('Y'); @endphp
                                @for($y=$yNow; $y>=$yNow-5; $y--)
                                <option value="{{ $y }}" @selected($y==$yNow)>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group col-6 col-md-2 d-none" data-op-filter="quarter">
                            <label class="mb-1" for="op_quarter">Quarter</label>
                            <select id="op_quarter" class="form-control">
                                <option value="1">Qtr1</option>
                                <option value="2">Qtr2</option>
                                <option value="3">Qtr3</option>
                                <option value="4">Qtr4</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-md-2 d-none" data-op-filter="month">
                            <label class="mb-1" for="op_month">Month</label>
                            <select id="op_month" class="form-control">
                                @for($m=1;$m<=12;$m++)
                                    <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                    @endfor
                            </select>
                        </div>

                        <div class="form-group col-6 col-md-3 d-none" data-op-filter="week">
                            <label class="mb-1" for="op_week">Week</label>
                            <input id="op_week" type="week" class="form-control">
                        </div>

                        <div class="form-group col-6 col-md-3 d-none" data-op-filter="day">
                            <label class="mb-1" for="op_day">Day</label>
                            <input id="op_day" type="date" class="form-control">
                        </div>
                    </div>
                </div>
                {{-- ✅ Estructura correcta dentro de la card --}}
                <div class="card-body">
                    <div class="row align-items-stretch">
                        <div class="col-md-5 mb-3 mb-md-0" style="border-right: 1px solid #ddd;">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover mb-0" id="tblOperator">
                                    <colgroup>
                                        <col style="width:30%">
                                        <col style="width:14%">
                                        <col style="width:14%">
                                        <col style="width:14%">
                                        <col style="width:14%">
                                        <col style="width:14%">
                                    </colgroup>
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Operador</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Pass</th>
                                            <th class="text-center">No Pass</th>
                                            <th class="text-center">% Pass</th>
                                            <th class="text-center">% No Pass</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-7 d-flex">
                            <div class="w-100">
                                <div class="chart-wrapper" style="height: 600px;">
                                    <canvas id="operatorChart" aria-label="Gráfica por operador" role="img"></canvas>
                                </div>
                            </div>
                        </div>
                    </div> {{-- /.row --}}
                </div> {{-- /.card-body --}}
            </div>
        </div>
    </div>

    {{-- FILA 4: Por Quarter y Operador --}}
    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 d-flex align-items-center">
                    <strong class="mr-auto">
                        <i class="fas fa-calendar-alt mr-1 text-warning"></i>By Operator and Quarter
                    </strong>
                    <div class="btn-group btn-group-sm ml-auto" role="group" aria-label="operator-quarter actions">
                        <button id="btnExportOpQuarterCsv" type="button" class="btn btn-erp-gray">
                            <i class="fas fa-file-csv mr-1"></i>CSV
                        </button>
                        <button id="btnExportOpQuarterPdf" type="button" class="btn btn-erp-gray">
                            <i class="fas fa-file-pdf mr-1"></i>PDF
                        </button>
                    </div>
                </div>
                {{-- Filtros SOLO para "Por Operador y Quarter" --}}
                <div class="px-3 pt-3">
                    <div class="form-row align-items-end">
                        {{-- Año --}}
                        <div class="form-group col-6 col-md-2">
                            <label class="mb-1" for="opq_year">Year</label>
                            <select id="opq_year" class="form-control">
                                @php $yNow = (int) now()->format('Y'); @endphp
                                @for($y=$yNow; $y>=$yNow-5; $y--)
                                <option value="{{ $y }}" @selected($y==$yNow)>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- Operador --}}
                        <div class="form-group col-6 col-md-4">
                            <label class="mb-1" for="opq_operator">Operator</label>
                            <select id="opq_operator" class="form-control">
                                <option value="">-- All --</option>
                                @foreach(($operators ?? []) as $op)
                                <option value="{{ $op->name }}">{{ $op->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>


                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover mb-0 text-nowrap align-middle" id="tblOpQuarter">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th rowspan="2" class="align-middle">Operador</th>
                                            <th colspan="3">Q1</th>
                                            <th colspan="3">Q2</th>
                                            <th colspan="3">Q3</th>
                                            <th colspan="3">Q4</th>
                                            <th colspan="5">Total Año</th>
                                        </tr>
                                        <tr>
                                            {{-- Q1 --}}
                                            <th>Total</th>
                                            <th>Pass</th>
                                            <th class="border-right-thick">No Pass</th>
                                            {{-- Q2 --}}
                                            <th>Total</th>
                                            <th>Pass</th>
                                            <th class="border-right-thick">No Pass</th>
                                            {{-- Q3 --}}
                                            <th>Total</th>
                                            <th>Pass</th>
                                            <th class="border-right-thick">No Pass</th>
                                            {{-- Q4 --}}
                                            <th>Total</th>
                                            <th>Pass</th>
                                            <th class="border-right-thick">No Pass</th>
                                            {{-- Totales anuales --}}
                                            <th>Total</th>
                                            <th>Pass</th>
                                            <th>No Pass</th>
                                            <th>% Pass</th>
                                            <th>% No Pass</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-end"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="chart-wrapper" style="height:460px;">
                                <canvas id="opQuarterChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




</div> {{-- /.card-body --}}

@endsection

@section('css')
<style>
    :root {
        --erp-primary: #4f6fad;
        --erp-amber: #cb8c40;
        --erp-surface: #f5f7fb;
        --erp-border: #d2d6e0;
        --erp-text: #0f172a;
    }

    body {
        background: #f3f4f6;
    }

    .card {
        border: 1px solid var(--erp-border);
        border-radius: .55rem;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
    }

    .card-header {
        background: linear-gradient(180deg, #f8fafc 0%, #e4e8ef 100%);
        border-bottom: 1px solid var(--erp-border);
        color: var(--erp-text);
        letter-spacing: .2px;
    }

    .card-header strong {
        font-weight: 700;
    }

    .chart-wrapper {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: .5rem;
        padding: .75rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .table thead {
        background: linear-gradient(180deg, #eef1f5 0%, #e1e6ee 100%);
        color: var(--erp-text);
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f8fafc;
    }

    .table-hover tbody tr:hover {
        background-color: #eef2f7;
    }

    .input-group-text {
        background: #f8fafc;
        border-color: var(--erp-border);
    }

    select.form-control,
    input.form-control {
        border-color: var(--erp-border);
        color: var(--erp-text);
    }

    /* Botones estilo ERP */
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

    .kpi-box {
        min-height: 110px;
        border-radius: .5rem;
        color: #0f172a;
        background: linear-gradient(180deg, #f8fafc 0%, #e5e7eb 100%);
        border: 1px solid #d1d5db;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
    }

    .kpi-box .inner h3 {
        font-weight: 800;
        letter-spacing: .5px;
    }

    .kpi-box .icon {
        right: 10px;
        top: 5px;
        opacity: .25;
    }
    .kpi-box p {
        color: #4b5563;
        font-weight: 600;
        letter-spacing: .2px;
    }

    /* Colores ERP para KPIs */
    .small-box.bg-info {
        background: linear-gradient(180deg, #e9f0fb 0%, #d7deeb 100%) !important;
        color: #0f172a;
    }

    .small-box.bg-success {
        background: linear-gradient(180deg, #e6f4ec 0%, #d5e7dc 100%) !important;
        color: #0f172a;
    }

    .small-box.bg-danger {
        background: linear-gradient(180deg, #f8e9e5 0%, #edd8d3 100%) !important;
        color: #0f172a;
    }

    /* Sticky header dentro de contenedores con overflow */
    .table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fa;
        /* aseguras fondo si hay transparencia */
    }

    /* Padding y alineación coherente */
    #quartersTable td,
    #quartersTable th,
    #tblOperator td,
    #tblOperator th,
    #tblInspector td,
    #tblInspector th {
        padding: .45rem .6rem;
        vertical-align: middle;
    }

    /* Alineación numérica a la derecha */
    #quartersTable td.text-center,
    #quartersTable th.text-center,
    #tblOperator td.text-center,
    #tblOperator th.text-center,
    #tblInspector td.text-center,
    #tblInspector th.text-center {
        text-align: center;
    }


    /* === PRINT STYLES === */
    @media print {

        /* Oculta todo lo que no sea el contenedor imprimible cuando abramos en misma ventana */
        .no-print {
            display: none !important;
        }

        /* Limpia fondos y sombras para papel */
        .card,
        .table,
        .small-box {
            box-shadow: none !important;
            background: #fff !important;
        }

        /* Evita cortes de fila en tablas */
        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        /* Tipografía un poco más compacta */
        body {
            font-size: 12px;
        }

        .table td,
        .table th {
            padding: .35rem .5rem !important;
        }
    }

    /* En pantalla, estos contenedores que imprimiremos los mantenemos ocultos */
    .print-only {
        display: none;
    }
</style>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>
<script>
    (() => {
        // ====== ESTADOS GLOBALES ======
        let chart; // quarters
        let operatorChart, inspectorChart, typeChart;

        // Estados para exportar
        let lastQuarters = [];
        let lastInspectors = [];
        let lastOperators = [];
        let lastOpQuarter = [];
        let lastTypes = [];

        // ====== SELECTORES GLOBALES ======
        const $year = document.getElementById('yearSelect');
        const $lblYear = document.getElementById('lblYear');
        const $tbodyQ = document.querySelector('#quartersTable tbody');
        const $typeTbody = document.querySelector('#tblTypes tbody');
        const $typeCanvas = document.getElementById('typeChart');

        // ====== HELPERS ======
        const fmtPct = v => (Math.round((v ?? 0) * 100) / 100).toFixed(2) + '%';
        const fmtNum = v => Number(v ?? 0).toLocaleString('en-US');

        // Plugin: mostrar valores sobre las barras
        const valueOnBarPlugin = {
            id: 'valueOnBar',
            afterDatasetsDraw(chart, args, pluginOptions) {
                const { ctx } = chart;
                const opts = pluginOptions || {};
                const formatter = opts.formatter || ((v) => v);
                ctx.save();
                ctx.fillStyle = opts.color || '#0f172a';
                ctx.font = opts.font || '600 11px "Segoe UI", Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                chart.data.datasets.forEach((dataset, di) => {
                    const meta = chart.getDatasetMeta(di);
                    meta.data.forEach((bar, i) => {
                        const val = dataset.data[i];
                        if (val === null || typeof val === 'undefined') return;
                        const text = formatter(val, dataset, i);
                        ctx.fillText(text, bar.x, bar.y - 4);
                    });
                });
                ctx.restore();
            }
        };
        if (typeof Chart !== 'undefined') Chart.register(valueOnBarPlugin);

        function debounce(fn, ms = 200) {
            let t;
            return (...a) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...a), ms);
            };
        }

        // Exportación CSV genérica
        function exportToCSV(rows, headers, filename) {
            const safeRows = Array.isArray(rows) ? rows : [];
            const csv = [headers.join(',')]
                .concat(safeRows.map(r => r.map(x => String(x ?? '')).join(',')))
                .join('\n');

            const blob = new Blob([csv], {
                type: 'text/csv;charset=utf-8;'
            });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = `${filename}.csv`;
            a.click();
            URL.revokeObjectURL(a.href);
        }

        // Para Quarters (global) usamos el año actual del select
        function currentYearRange() {
            const y = ($year?.value || new Date().getFullYear());
            return {
                start: `${y}-01-01`,
                end: `${y}-12-31`
            };
        }

        // ====== TABLA QUARTERS ======
        function renderQuartersTable(rows) {
            if (!$tbodyQ) return;
            if (!rows?.length) {
                $tbodyQ.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No data</td></tr>`;
                updateQuarterTotals([]);
                return;
            }
            $tbodyQ.innerHTML = rows.map(q => `
      <tr>
        <td>${q.quarter}</td>
        <td class="text-center">${fmtNum(q.total)}</td>
        <td class="text-center">${fmtNum(q.pass)}</td>
        <td class="text-center">${fmtNum(q.fail)}</td>
        <td class="text-center">${fmtPct(q.pass_pct)}</td>
        <td class="text-center">${fmtPct(q.fail_pct)}</td>
      </tr>
    `).join('');
            updateQuarterTotals(rows);
        }

        function updateQuarterTotals(rows) {
            const sum = (rows || []).reduce((a, r) => ({
                total: a.total + (r.total || 0),
                pass: a.pass + (r.pass || 0),
                fail: a.fail + (r.fail || 0)
            }), {
                total: 0,
                pass: 0,
                fail: 0
            });

            const passPct = sum.total ? (sum.pass * 100 / sum.total) : 0;
            const failPct = sum.total ? (sum.fail * 100 / sum.total) : 0;

            const set = (id, v) => {
                const el = document.getElementById(id);
                if (el) el.textContent = v;
            };
            set('q_sum_total', sum.total);
            set('q_sum_pass', sum.pass);
            set('q_sum_fail', sum.fail);
            set('q_sum_pass_pct', passPct.toFixed(2) + '%');
            set('q_sum_fail_pct', failPct.toFixed(2) + '%');
        }

        function renderTypeTable(rows) {
            if (!$typeTbody) return;
            if (!rows?.length) {
                $typeTbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">No data</td></tr>`;
                return;
            }
            $typeTbody.innerHTML = rows.map(r => `
      <tr>
        <td>${r.type}</td>
        <td class="text-center">${fmtNum(r.total)}</td>
        <td class="text-center">${fmtNum(r.pass)}</td>
        <td class="text-center">${fmtNum(r.fail)}</td>
        <td class="text-center">${fmtPct(r.pass_pct)}</td>
        <td class="text-center">${fmtPct(r.fail_pct)}</td>
      </tr>
    `).join('');
        }

        function renderTypeChart(rows) {
            if (!$typeCanvas) return;
            const ctx = $typeCanvas.getContext('2d');
            if (typeChart) typeChart.destroy();
            const labels = rows.map(r => r.type);
            const pass = rows.map(r => r.pass);
            const fail = rows.map(r => r.fail);
            typeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Pass',
                        data: pass,
                        backgroundColor: 'rgba(79,111,173,0.75)'
                    }, {
                        label: 'No Pass',
                        data: fail,
                        backgroundColor: 'rgba(203,140,64,0.75)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        valueOnBar: {
                            formatter: (v) => fmtNum(v)
                        }
                    },
                    scales: {
                        x: { stacked: false },
                        y: { beginAtZero: true, stacked: false }
                    }
                }
            });
        }

        // Export Quarters
        function exportQuartersCSV(rows) {
            const data = rows?.length ? rows : lastQuarters;
            const headers = ['Quarter', 'Total', 'Pass', 'No Pass', '% Pass', '% No Pass'];
            const table = (data || []).map(q => [
                q.quarter, q.total, q.pass, q.fail, fmtPct(q.pass_pct), fmtPct(q.fail_pct)
            ]);
            exportToCSV(table, headers, `quarters_${$year?.value || 'year'}`);
        }

        function exportTypesCSV(rows) {
            const data = rows?.length ? rows : lastTypes;
            const headers = ['Type', 'Total', 'Pass', 'No Pass', '% Pass', '% No Pass'];
            const table = (data || []).map(t => [
                t.type, t.total, t.pass, t.fail, fmtPct(t.pass_pct), fmtPct(t.fail_pct)
            ]);
            exportToCSV(table, headers, `types_${$year?.value || 'year'}`);
        }

        // ====== CARGA GLOBAL ======
        async function loadGlobal() {
            const url = "{{ route('faisummary.statistics.data') }}" + `?year=${$year.value}`;
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });
            const data = await res.json();

            document.getElementById('k_total').textContent = data.global.total;
            document.getElementById('k_pass_pct').textContent = fmtPct(data.global.pass_pct);
            document.getElementById('k_fail_pct').textContent = fmtPct(data.global.fail_pct);
            if ($lblYear) $lblYear.textContent = $year.value;

            lastQuarters = data.quarters || [];
            renderQuartersTable(lastQuarters);
            lastTypes = data.types || [];
            renderTypeTable(lastTypes);
            renderTypeChart(lastTypes);

            const $canvas = document.getElementById('quartersChart');
            if ($canvas) {
                const ctx = $canvas.getContext('2d');
                if (chart) chart.destroy();
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: lastQuarters.map(q => q.quarter),
                        datasets: [{
                            label: '% Pass',
                            data: lastQuarters.map(q => q.pass_pct),
                            backgroundColor: 'rgba(79,111,173,0.7)'
                        },
                        {
                            label: '% No Pass',
                            data: lastQuarters.map(q => q.fail_pct),
                            backgroundColor: 'rgba(203,140,64,0.7)'
                        }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: v => v + '%'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${ctx.dataset.label}: ${fmtPct(ctx.parsed.y)}`
                                }
                            },
                            valueOnBar: {
                                formatter: (v) => `${Number(v ?? 0).toFixed(1)}%`
                            }
                        }
                    }
                });
            }
        }

        // ====== TABLAS PERSONAS (Inspector / Operator) ======
        function fillPeopleTable(tbodySel, rows) {
            const tbody = document.querySelector(tbodySel);
            if (!tbody) return;
            tbody.innerHTML = (rows || []).length ? rows.map(r => `
      <tr>
        <td>${r.name}</td>
        <td class="text-center">${r.total}</td>
        <td class="text-right">${r.pass}</td>
        <td class="text-right">${r.fail}</td>
        <td class="text-right">${fmtPct(r.pass_pct)}</td>
        <td class="text-right">${fmtPct(r.fail_pct)}</td>
      </tr>
    `).join('') : `<tr><td colspan="6" class="text-center text-muted py-3">No data</td></tr>`;
        }

        function drawBarChart(canvasId, labels, passPct, failPct) {
            const el = document.getElementById(canvasId);
            if (!el) return;
            const ctx = el.getContext('2d');
            if (canvasId === 'operatorChart' && operatorChart) operatorChart.destroy();
            if (canvasId === 'inspectorChart' && inspectorChart) inspectorChart.destroy();

            const instance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: '% Pass',
                        data: passPct,
                        backgroundColor: 'rgba(79,111,173,0.7)'
                    },
                    {
                        label: '% No Pass',
                        data: failPct,
                        backgroundColor: 'rgba(203,140,64,0.7)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: v => v + '%'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.dataset.label}: ${fmtPct(ctx.parsed.y)}`
                            }
                        },
                        valueOnBar: {
                            formatter: (v) => `${Number(v ?? 0).toFixed(1)}%`
                        }
                    }
                }
            });
            if (canvasId === 'operatorChart') operatorChart = instance;
            else inspectorChart = instance;
        }

        async function loadByInspectorYearOnly() {
            const url = "{{ route('faisummary.statistics.by') }}" + `?year=${$year.value}&group=inspector`;
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });
            const data = await res.json();
            return data.rows || [];
        }

        async function reloadGlobalAndInspector() {
            await loadGlobal();
            await reloadInspectorOnly(); // respeta filtros de Inspector
        }
        const debouncedReloadGlobalInspector = debounce(reloadGlobalAndInspector, 200);

        // ====== OPERADOR: filtros exclusivos (op_*) ======
        const $opGran = document.getElementById('op_gran');
        const $opYear = document.getElementById('op_year');
        const $opQ = document.getElementById('op_quarter');
        const $opM = document.getElementById('op_month');
        const $opW = document.getElementById('op_week');
        const $opD = document.getElementById('op_day');
        const $opTbody = document.querySelector('#tblOperator tbody');
        const $opCanvas = document.getElementById('operatorChart');

        const ENDPOINT_BY = @json(route('faisummary.statistics.by'));

        // util fechas compartidas
        const pad = n => String(n).padStart(2, '0');
        const now = new Date();

        function getIsoWeekInputValue(d = new Date()) {
            const tmp = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
            tmp.setUTCDate(tmp.getUTCDate() + 4 - (tmp.getUTCDay() || 7));
            const yearStart = new Date(Date.UTC(tmp.getUTCFullYear(), 0, 1));
            const weekNo = Math.ceil((((tmp - yearStart) / 86400000) + 1) / 7);
            return `${tmp.getUTCFullYear()}-W${pad(weekNo)}`;
        }

        function quarterOfMonth(m) {
            return Math.ceil(m / 3);
        }

        function rangeQuarter(year, q) {
            const starts = [
                [1, 1],
                [4, 1],
                [7, 1],
                [10, 1]
            ];
            const [sm, sd] = starts[q - 1];
            const start = `${year}-${pad(sm)}-${pad(sd)}`;
            const end = (q === 1) ? `${year}-03-31` : (q === 2) ? `${year}-06-30` : (q === 3) ? `${year}-09-30` : `${year}-12-31`;
            return {
                start,
                end
            };
        }

        function rangeMonth(year, m) {
            const start = `${year}-${pad(m)}-01`;
            const last = new Date(year, m, 0).getDate();
            const end = `${year}-${pad(m)}-${pad(last)}`;
            return {
                start,
                end
            };
        }

        function rangeIsoWeek(iso) {
            if (!iso || !/^\d{4}-W\d{2}$/.test(iso)) return null;
            const [yStr, wStr] = iso.split('-W');
            const y = +yStr,
                w = +wStr;
            const simple = new Date(Date.UTC(y, 0, 4));
            const dow = simple.getUTCDay() || 7;
            const mondayWeek1 = new Date(simple);
            mondayWeek1.setUTCDate(simple.getUTCDate() - (dow - 1));
            const mondayTarget = new Date(mondayWeek1);
            mondayTarget.setUTCDate(mondayWeek1.getUTCDate() + (w - 1) * 7);
            const sundayTarget = new Date(mondayTarget);
            sundayTarget.setUTCDate(mondayTarget.getUTCDate() + 6);
            return {
                start: mondayTarget.toISOString().slice(0, 10),
                end: sundayTarget.toISOString().slice(0, 10)
            };
        }

        function opSyncVisibilityAndDefaults() {
            const g = $opGran.value;
            document.querySelectorAll('[data-op-filter]').forEach(el => el.classList.add('d-none'));
            if (g === 'quarter') {
                document.querySelector('[data-op-filter="quarter"]').classList.remove('d-none');
                if (!$opQ.value) $opQ.value = String(quarterOfMonth(now.getMonth() + 1));
            }
            if (g === 'month') {
                document.querySelector('[data-op-filter="month"]').classList.remove('d-none');
                if (!$opM.value) $opM.value = String(now.getMonth() + 1);
            }
            if (g === 'week') {
                document.querySelector('[data-op-filter="week"]').classList.remove('d-none');
                if (!$opW.value) $opW.value = getIsoWeekInputValue(now);
            }
            if (g === 'day') {
                document.querySelector('[data-op-filter="day"]').classList.remove('d-none');
                if (!$opD.value) $opD.value = now.toISOString().slice(0, 10);
            }
        }

        function currentOperatorRange() {
            const g = $opGran?.value || 'year';
            const year = parseInt($opYear?.value || now.getFullYear(), 10);
            if (g === 'year') return {
                start: `${year}-01-01`,
                end: `${year}-12-31`
            };
            if (g === 'quarter') return rangeQuarter(year, parseInt($opQ?.value || quarterOfMonth(now.getMonth() + 1), 10));
            if (g === 'month') return rangeMonth(year, parseInt($opM?.value || (now.getMonth() + 1), 10));
            if (g === 'week') return rangeIsoWeek($opW?.value) || {
                start: `${year}-01-01`,
                end: `${year}-12-31`
            };
            if (g === 'day') return $opD?.value ? {
                start: $opD.value,
                end: $opD.value
            } : {
                start: `${year}-01-01`,
                end: `${year}-12-31`
            };
            return {
                start: `${year}-01-01`,
                end: `${year}-12-31`
            };
        }

        async function reloadOperatorOnly() {
            if (!$opTbody) return;
            const {
                start,
                end
            } = currentOperatorRange();
            const url = `${ENDPOINT_BY}?${new URLSearchParams({ group:'operator', start, end }).toString()}`;
            let rows = [];
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    cache: 'no-store'
                });
                if (!res.ok) console.warn('[Operator] HTTP', res.status, res.statusText);
                const data = await res.json();
                rows = Array.isArray(data?.rows) ? data.rows : [];
            } catch (e) {
                console.error('[Operator] Fetch error', e);
                $opTbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-3">Error loading data</td></tr>`;
                return;
            }

            // guarda para export
            lastOperators = rows.map(r => ({
                name: r.name ?? '(N/A)',
                total: +r.total || 0,
                pass: +r.pass || 0,
                fail: +r.fail || 0,
                pass_pct: +r.pass_pct || 0,
                fail_pct: +r.fail_pct || 0
            }));

            // render
            $opTbody.innerHTML = rows.length ? rows.map(r => `
        <tr>
          <td>${r.name ?? '(N/A)'}</td>
          <td class="text-center">${fmtNum(r.total)}</td>
          <td class="text-center">${fmtNum(r.pass)}</td>
          <td class="text-center">${fmtNum(r.fail)}</td>
          <td class="text-center">${Number(r.pass_pct ?? 0).toFixed(2)}%</td>
          <td class="text-center">${Number(r.fail_pct ?? 0).toFixed(2)}%</td>
        </tr>
    `).join('') : `<tr><td colspan="6" class="text-center text-muted py-3">No data</td></tr>`;

            if ($opCanvas) {
                drawBarChart(
                    'operatorChart',
                    rows.map(r => r.name ?? '(N/A)'),
                    rows.map(r => Number(r.pass_pct || 0)),
                    rows.map(r => Number(r.fail_pct || 0))
                );
            }
        }
        const debouncedReloadOperator = debounce(reloadOperatorOnly, 250);

        // ====== INSPECTOR: filtros exclusivos (insp_*) ======
        const $inspGran = document.getElementById('insp_gran');
        const $inspYear = document.getElementById('insp_year');
        const $inspQ = document.getElementById('insp_quarter');
        const $inspM = document.getElementById('insp_month');
        const $inspW = document.getElementById('insp_week');
        const $inspD = document.getElementById('insp_day');
        const $inspTbody = document.querySelector('#tblInspector tbody');
        const $inspCanvas = document.getElementById('inspectorChart');

        function inspSyncVisibilityAndDefaults() {
            const g = $inspGran.value;
            document.querySelectorAll('[data-insp-filter]').forEach(el => el.classList.add('d-none'));
            if (g === 'quarter') {
                document.querySelector('[data-insp-filter="quarter"]').classList.remove('d-none');
                if (!$inspQ.value) $inspQ.value = String(quarterOfMonth(now.getMonth() + 1));
            }
            if (g === 'month') {
                document.querySelector('[data-insp-filter="month"]').classList.remove('d-none');
                if (!$inspM.value) $inspM.value = String(now.getMonth() + 1);
            }
            if (g === 'week') {
                document.querySelector('[data-insp-filter="week"]').classList.remove('d-none');
                if (!$inspW.value) $inspW.value = getIsoWeekInputValue(now);
            }
            if (g === 'day') {
                document.querySelector('[data-insp-filter="day"]').classList.remove('d-none');
                if (!$inspD.value) $inspD.value = now.toISOString().slice(0, 10);
            }
        }

        function currentInspectorRange() {
            const g = $inspGran?.value || 'year';
            const year = parseInt($inspYear?.value || now.getFullYear(), 10);
            if (g === 'year') return {
                start: `${year}-01-01`,
                end: `${year}-12-31`
            };
            if (g === 'quarter') return rangeQuarter(year, parseInt($inspQ?.value || quarterOfMonth(now.getMonth() + 1), 10));
            if (g === 'month') return rangeMonth(year, parseInt($inspM?.value || (now.getMonth() + 1), 10));
            if (g === 'week') return rangeIsoWeek($inspW?.value) || {
                start: `${year}-01-01`,
                end: `${year}-12-31`
            };
            // ✅ FIX aquí: cubrir todo el día
            if (g === 'day') {
                if ($inspD?.value) {
                    const d = $inspD.value; // 'YYYY-MM-DD'
                    return {
                        start: `${d} 00:00:00`,
                        end: `${d} 23:59:59`
                    };
                }
                return {
                    start: `${year}-01-01`,
                    end: `${year}-12-31`
                };
            }
            return {
                start: `${year}-01-01`,
                end: `${year}-12-31`
            };
        }

        async function reloadInspectorOnly() {
            if (!$inspTbody) return;
            const {
                start,
                end
            } = currentInspectorRange();
            const url = `${ENDPOINT_BY}?${new URLSearchParams({ group:'inspector', start, end }).toString()}`;
            let rows = [];
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    cache: 'no-store'
                });
                if (!res.ok) console.warn('[Inspector] HTTP', res.status, res.statusText);
                const data = await res.json();
                rows = Array.isArray(data?.rows) ? data.rows : [];
            } catch (e) {
                console.error('[Inspector] Fetch error', e);
                $inspTbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-3">Error loading data</td></tr>`;
                return;
            }

            // guarda para export
            lastInspectors = rows.map(r => ({
                name: r.name ?? '(N/A)',
                total: +r.total || 0,
                pass: +r.pass || 0,
                fail: +r.fail || 0,
                pass_pct: +r.pass_pct || 0,
                fail_pct: +r.fail_pct || 0
            }));

            // render
            $inspTbody.innerHTML = rows.length ? rows.map(r => `
        <tr>
          <td>${r.name ?? '(N/A)'}</td>
          <td class="text-center">${fmtNum(r.total)}</td>
          <td class="text-center">${fmtNum(r.pass)}</td>
          <td class="text-center">${fmtNum(r.fail)}</td>
          <td class="text-center">${Number(r.pass_pct ?? 0).toFixed(2)}%</td>
          <td class="text-center">${Number(r.fail_pct ?? 0).toFixed(2)}%</td>
        </tr>
    `).join('') : `<tr><td colspan="6" class="text-center text-muted py-3">No data</td></tr>`;

            if ($inspCanvas) {
                drawBarChart(
                    'inspectorChart',
                    rows.map(r => r.name ?? '(N/A)'),
                    rows.map(r => Number(r.pass_pct || 0)),
                    rows.map(r => Number(r.fail_pct || 0))
                );
            }
        }
        const debouncedReloadInspector = debounce(reloadInspectorOnly, 250);

        // ====== PRINT HELPERS (gráfica + tabla con filtros) ======
        function formatRangeForTitle({
            start,
            end
        }) {
            return (start === end) ? start : `${start} → ${end}`;
        }

        function canvasToImgData(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return null;
            try {
                return canvas.toDataURL('image/png');
            } catch {
                return null;
            }
        }

        function buildPrintableHtml({
            title,
            subtitle,
            imgDataUrl,
            tableHtml
        }) {
            const stamp = new Date().toLocaleString();
            const imgTag = imgDataUrl ?
                `<img src="${imgDataUrl}" alt="chart" style="max-width:100%; height:auto;"/>` :
                `<div style="color:#999;">(Chart unavailable)</div>`;
            return `
<!doctype html>
<html>
<head>
<meta charset="utf-8"><title>${title}</title>
<style>
* { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
img { image-rendering: -webkit-optimize-contrast; }
body { font-family: system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans"; margin: 16px; color:#222; }
h1 { margin: 0 0 4px; font-size: 20px; }
h2 { margin: 0 0 16px; font-size: 14px; color: #555; }
.meta { font-size: 11px; color:#777; margin-bottom: 12px; }
.chart { margin: 8px 0 16px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #ddd; padding: .4rem .5rem; text-align: right; }
th:first-child, td:first-child { text-align: left; }
thead { background: #f7f7f7; }
@media print {
  table { page-break-inside: auto; }
  tr    { page-break-inside: avoid; page-break-after: auto; }
  thead { display: table-header-group; }
  tfoot { display: table-footer-group; }
}
</style>
</head>
<body>
  <h1>${title}</h1>
  <h2>${subtitle}</h2>
  <div class="meta">Generated: ${stamp}</div>
  <div class="chart">${imgTag}</div>
  ${tableHtml || '<div style="color:#999;">(Table unavailable)</div>'}
</body>
</html>`;
        }

        function printSection({
            who,
            rangeGetter,
            canvasId,
            tableSelector
        }) {
            const range = rangeGetter();
            const imgData = canvasToImgData(canvasId);
            const tableEl = document.querySelector(tableSelector);
            const tableHtml = tableEl ?
                (tableEl.closest('.table-responsive')?.outerHTML || tableEl.outerHTML) :
                '<div style="color:#999;">(Table unavailable)</div>';

            const title = `FAI Summary — ${who}`;
            const subtitle = `Range: ${formatRangeForTitle(range)}`;
            const html = buildPrintableHtml({
                title,
                subtitle,
                imgDataUrl: imgData,
                tableHtml
            });

            const frame = document.createElement('iframe');
            Object.assign(frame.style, {
                position: 'fixed',
                right: '0',
                bottom: '0',
                width: '0',
                height: '0',
                border: '0'
            });
            document.body.appendChild(frame);
            const doc = frame.contentDocument || frame.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();
            frame.addEventListener('load', () => {
                try {
                    frame.contentWindow.focus();
                } catch {}
                frame.contentWindow.print();
                setTimeout(() => frame.remove(), 1000);
            });
        }

        // ====== OPERATOR × QUARTER ======
        const ENDPOINT_BY_QOP = @json(route('faisummary.statistics.byQuarterOperator'));
        const nz = v => Number(v || 0);

        async function loadOpQuarterFull(year, operator = '') {
            const params = new URLSearchParams({
                year
            });
            if (operator) params.append('operator', operator);
            const url = `${ENDPOINT_BY_QOP}?${params.toString()}`;

            let rows = {};
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    cache: 'no-store'
                });
                const data = await res.json();

                // Poblar <select> operadores
                const $sel = document.getElementById('opq_operator');
                if ($sel && Array.isArray(data.operators)) {
                    const current = $sel.value;
                    $sel.innerHTML = '<option value="">-- All --</option>';
                    for (const op of data.operators) {
                        const opt = document.createElement('option');
                        opt.value = op;
                        opt.textContent = op;
                        $sel.appendChild(opt);
                    }
                    if (data.operator) $sel.value = data.operator;
                    else if (current && data.operators.includes(current)) $sel.value = current;
                }

                rows = data.rows || {};
                window.lastOpQuarterMap = rows; // (global simple)
            } catch (e) {
                console.error('[OpQuarterFull] Fetch error', e);
            }

            // Tabla
            const $tbody = document.querySelector('#tblOpQuarter tbody');
            if ($tbody) {
                const html = Object.keys(rows).length ?
                    Object.entries(rows).map(([op, q]) => {
                        const q1 = q[1] || {},
                            q2 = q[2] || {},
                            q3 = q[3] || {},
                            q4 = q[4] || {};
                        const total = nz(q1.total) + nz(q2.total) + nz(q3.total) + nz(q4.total);
                        const pass = nz(q1.pass) + nz(q2.pass) + nz(q3.pass) + nz(q4.pass);
                        const fail = nz(q1.fail) + nz(q2.fail) + nz(q3.fail) + nz(q4.fail);
                        const passPct = total ? (pass * 100 / total).toFixed(2) : '0.00';
                        const failPct = total ? (fail * 100 / total).toFixed(2) : '0.00';
                        return `
              <tr>
                <td>${op}</td>
                <td class="text-center font-weight-bold">${nz(q1.total)}</td>
                <td class="text-center text-success">${nz(q1.pass)}</td>
                <td class="text-center text-danger">${nz(q1.fail)}</td>
                <td class="text-center font-weight-bold">${nz(q2.total)}</td>
                <td class="text-center text-success">${nz(q2.pass)}</td>
                <td class="text-center text-danger">${nz(q2.fail)}</td>
                <td class="text-center font-weight-bold">${nz(q3.total)}</td>
                <td class="text-center text-success">${nz(q3.pass)}</td>
                <td class="text-center text-danger">${nz(q3.fail)}</td>
                <td class="text-center font-weight-bold">${nz(q4.total)}</td>
                <td class="text-center text-success">${nz(q4.pass)}</td>
                <td class="text-center text-danger">${nz(q4.fail)}</td>
                <td class="text-center font-weight-bold">${total}</td>
                <td class="text-center font-weight-bold text-success">${pass}</td>
                <td class="text-center font-weight-bold text-danger">${fail}</td>
                <td class="text-center ">${passPct}%</td>
                <td class="text-center ">${failPct}%</td>
              </tr>`;
                    }).join('') :
                    `<tr><td colspan="13" class="text-center text-muted py-3">No data</td></tr>`;
                $tbody.innerHTML = html;
            }

            // Flatten para export
            lastOpQuarter = Object.entries(rows).flatMap(([op, q]) => {
                return [1, 2, 3, 4].map(ix => {
                    const qi = q[ix] || {};
                    const total = +qi.total || 0,
                        pass = +qi.pass || 0,
                        fail = +qi.fail || 0;
                    const passPct = total ? (pass * 100 / total) : 0;
                    const failPct = total ? (fail * 100 / total) : 0;
                    return {
                        operator: op,
                        quarter: `Q${ix}`,
                        total,
                        pass,
                        fail,
                        pass_pct: passPct,
                        fail_pct: failPct
                    };
                });
            });

            // Gráfica
            const canvas = document.getElementById('opQuarterChart');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                if (window.opQuarterChart && typeof window.opQuarterChart.destroy === 'function') {
                    window.opQuarterChart.destroy();
                }
                const quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
                const palette = [
                    'rgba(79,111,173,0.75)', 'rgba(125,135,150,0.75)', 'rgba(203,140,64,0.75)', 'rgba(98,156,129,0.75)',
                    'rgba(157,119,190,0.75)', 'rgba(118,131,148,0.75)', 'rgba(206,183,132,0.75)', 'rgba(91,122,149,0.75)',
                    'rgba(140,160,180,0.75)', 'rgba(122,167,141,0.75)'
                ];
                const datasets = Object.entries(rows).map(([op, q], i) => ({
                    label: op,
                    data: [1, 2, 3, 4].map(ix => nz(q[ix]?.total)),
                    backgroundColor: palette[i % palette.length],
                    borderWidth: 0
                }));
                window.opQuarterChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: quarters,
                        datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 10
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y}`
                                }
                            }
                        },
                        datasets: {
                            bar: {
                                categoryPercentage: 0.7,
                                barPercentage: 0.9
                            }
                        }
                    }
                });
            }
        }

        // ====== EXPORT ESPECÍFICAS ======
        function exportInspectorCSV(rows) {
            const data = rows?.length ? rows : lastInspectors;
            const headers = ['Inspector', 'Total', 'Pass', 'No Pass', '% Pass', '% No Pass'];
            const table = (data || []).map(i => [i.name, i.total, i.pass, i.fail, fmtPct(i.pass_pct), fmtPct(i.fail_pct)]);
            exportToCSV(table, headers, `inspector_${$inspYear?.value || $year?.value || 'year'}`);
        }

        function exportOperatorCSV(rows) {
            const data = rows?.length ? rows : lastOperators;
            const headers = ['Operator', 'Total', 'Pass', 'No Pass', '% Pass', '% No Pass'];
            const table = (data || []).map(o => [o.name, o.total, o.pass, o.fail, fmtPct(o.pass_pct), fmtPct(o.fail_pct)]);
            exportToCSV(table, headers, `operator_${$opYear?.value || $year?.value || 'year'}`);
        }

        function exportOpQuarterCSVWide() {
            const m = lastOpQuarterMap || {};
            const headers = [
                'Operator',
                'Q1 Total', 'Q1 Pass', 'Q1 Fail',
                'Q2 Total', 'Q2 Pass', 'Q2 Fail',
                'Q3 Total', 'Q3 Pass', 'Q3 Fail',
                'Q4 Total', 'Q4 Pass', 'Q4 Fail',
                'Year Total', 'Year Pass', 'Year Fail', '% Pass', '% No Pass'
            ];

            // ordena por operador
            const ops = Object.keys(m).sort((a, b) => a.localeCompare(b));

            const table = ops.map(op => {
                const q = m[op] || {};
                const q1 = q[1] || {},
                    q2 = q[2] || {},
                    q3 = q[3] || {},
                    q4 = q[4] || {};
                const t1 = +q1.total || 0,
                    p1 = +q1.pass || 0,
                    f1 = +q1.fail || 0;
                const t2 = +q2.total || 0,
                    p2 = +q2.pass || 0,
                    f2 = +q2.fail || 0;
                const t3 = +q3.total || 0,
                    p3 = +q3.pass || 0,
                    f3 = +q3.fail || 0;
                const t4 = +q4.total || 0,
                    p4 = +q4.pass || 0,
                    f4 = +q4.fail || 0;

                const total = t1 + t2 + t3 + t4;
                const pass = p1 + p2 + p3 + p4;
                const fail = f1 + f2 + f3 + f4;
                const passPct = total ? (pass * 100 / total) : 0;
                const failPct = total ? (fail * 100 / total) : 0;

                return [
                    op,
                    t1, p1, f1,
                    t2, p2, f2,
                    t3, p3, f3,
                    t4, p4, f4,
                    total, pass, fail,
                    passPct.toFixed(2) + '%',
                    failPct.toFixed(2) + '%'
                ];
            });

            const y = document.getElementById('opq_year')?.value || (typeof $year !== 'undefined' && $year?.value) || 'year';
            exportToCSV(table, headers, `operator_quarter_${y}`);
        }


        // ====== INIT ======
        document.addEventListener('DOMContentLoaded', () => {
            // Global + Inspector (por año)
            if ($year) {
                reloadGlobalAndInspector();
                $year.addEventListener('change', debouncedReloadGlobalInspector);
                $year.addEventListener('input', debouncedReloadGlobalInspector);
            }

            // === Listeners CSV/PDF (Quarters) ===
            const $btnExportQuartersCsv = document.getElementById('btnExportQuartersCsv');
            if ($btnExportQuartersCsv) {
                $btnExportQuartersCsv.addEventListener('click', () => exportQuartersCSV(lastQuarters));
            }
            const $btnExportQuartersPdf = document.getElementById('btnExportQuartersPdf');
            if ($btnExportQuartersPdf) {
                $btnExportQuartersPdf.addEventListener('click', () => {
                    printSection({
                        who: '% Pass vs % No Pass - by Quarter (Q1-Q4)',
                        rangeGetter: currentYearRange,
                        canvasId: 'quartersChart',
                        tableSelector: '#quartersTable'
                    });
                });
            }

            const $btnExportTypesCsv = document.getElementById('btnExportTypesCsv');
            if ($btnExportTypesCsv) {
                $btnExportTypesCsv.addEventListener('click', () => exportTypesCSV());
            }
            const $btnExportTypesPdf = document.getElementById('btnExportTypesPdf');
            if ($btnExportTypesPdf) {
                $btnExportTypesPdf.addEventListener('click', () => {
                    printSection({
                        who: 'FAI / IPI Overview',
                        rangeGetter: currentYearRange,
                        canvasId: 'typeChart',
                        tableSelector: '#tblTypes'
                    });
                });
            }

            // Operador
            if ($opGran && $opYear) {
                opSyncVisibilityAndDefaults();
                [$opGran, $opYear, $opQ, $opM, $opW, $opD].forEach(el => {
                    if (!el) return;
                    el.addEventListener('change', () => {
                        opSyncVisibilityAndDefaults();
                        debouncedReloadOperator();
                    });
                    el.addEventListener('input', () => {
                        debouncedReloadOperator();
                    });
                });
                debouncedReloadOperator();
            }

            // Inspector
            if ($inspGran && $inspYear) {
                inspSyncVisibilityAndDefaults();
                [$inspGran, $inspYear, $inspQ, $inspM, $inspW, $inspD].forEach(el => {
                    if (!el) return;
                    el.addEventListener('change', () => {
                        inspSyncVisibilityAndDefaults();
                        debouncedReloadInspector();
                    });
                    el.addEventListener('input', () => {
                        debouncedReloadInspector();
                    });
                });
                debouncedReloadInspector();
            }

            // === Print (Operator / Inspector) ===
            const $btnExportOperatorCsv = document.getElementById('btnExportOperatorCsv');
            if ($btnExportOperatorCsv) {
                $btnExportOperatorCsv.addEventListener('click', () => exportOperatorCSV());
            }
            const $btnPrintOperator = document.getElementById('btnPrintOperator');
            if ($btnPrintOperator) {
                $btnPrintOperator.addEventListener('click', () => {
                    printSection({
                        who: 'By Operator',
                        rangeGetter: currentOperatorRange,
                        canvasId: 'operatorChart',
                        tableSelector: '#tblOperator'
                    });
                });
            }

            const $btnExportInspectorCsv = document.getElementById('btnExportInspectorCsv');
            if ($btnExportInspectorCsv) {
                $btnExportInspectorCsv.addEventListener('click', () => exportInspectorCSV());
            }
            const $btnPrintInspector = document.getElementById('btnPrintInspector');
            if ($btnPrintInspector) {
                $btnPrintInspector.addEventListener('click', () => {
                    printSection({
                        who: 'By Inspector',
                        rangeGetter: currentInspectorRange,
                        canvasId: 'inspectorChart',
                        tableSelector: '#tblInspector'
                    });
                });
            }

            // === Operator × Quarter: PDF y CSV ===
            const $btnExportOpQuarterCsv = document.getElementById('btnExportOpQuarterCsv');
            if ($btnExportOpQuarterCsv) {
                $btnExportOpQuarterCsv.addEventListener('click', () => exportOpQuarterCSVWide());
            }
            const $btnExportOpQuarterPdf = document.getElementById('btnExportOpQuarterPdf');
            if ($btnExportOpQuarterPdf) {
                $btnExportOpQuarterPdf.addEventListener('click', () => {
                    const ySel = document.getElementById('opq_year')?.value || $year?.value;
                    printSection({
                        who: 'By Operator and Quarter',
                        rangeGetter: () => ({
                            start: `${ySel}-01-01`,
                            end: `${ySel}-12-31`
                        }),
                        canvasId: 'opQuarterChart',
                        tableSelector: '#tblOpQuarter'
                    });
                });
            }

            // === Carga Operator × Quarter por selects ===
            const $opqYear = document.getElementById('opq_year');
            const $opqOper = document.getElementById('opq_operator');
            const reloadOpq = () => loadOpQuarterFull($opqYear.value, $opqOper.value);
            if ($opqYear && $opqOper) {
                reloadOpq();
                $opqYear.addEventListener('change', reloadOpq);
                $opqOper.addEventListener('change', reloadOpq);
            }
        });
    })();
</script>



@endpush
