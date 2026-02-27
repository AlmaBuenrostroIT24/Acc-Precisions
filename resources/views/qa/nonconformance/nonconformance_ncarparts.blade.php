<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Non-Conformance Reports')

@section('meta')
  <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content_header')
  <div class="d-flex align-items-center justify-content-between">
    <h1 class="mb-0">
      <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
      Non-Conformance Reports
    </h1>
    <div>
      <button type="button" class="btn btn-outline-secondary btn-sm mr-2" id="btnManageStages">
        <i class="fas fa-layer-group mr-1"></i> Stages
      </button>
      <button type="button" class="btn btn-dark btn-sm" id="btnCreateNcar">
        <i class="fas fa-plus mr-1"></i> NCR
      </button>
    </div>
  </div>
@endsection


@section('content')

<div class="ncar-page">

{{-- Tabs --}}




<div class="row">
  {{-- Columna izquierda: KPIs --}}
  <div class="col-lg-3">
    <div class="card kpi-card kpi-new mb-3">
      <div class="card-body d-flex align-items-center">
        <div class="kpi-icon mr-3"><i class="fas fa-info-circle"></i></div>
        <div>
          <div class="small text-muted">New</div>
          <div class="h3 mb-0" id="kpiNew">0</div>
        </div>
      </div>
    </div>

    <div class="card kpi-card kpi-qa mb-3">
      <div class="card-body d-flex align-items-center">
        <div class="kpi-icon mr-3"><i class="fas fa-user"></i></div>
        <div>
          <div class="small text-muted">Quality Review</div>
          <div class="h3 mb-0" id="kpiQA">0</div>
        </div>
      </div>
    </div>

    <div class="card kpi-card kpi-eng mb-3">
      <div class="card-body d-flex align-items-center">
        <div class="kpi-icon mr-3"><i class="fas fa-wrench"></i></div>
        <div>
          <div class="small text-muted">Engineering Review</div>
          <div class="h3 mb-0" id="kpiEng">0</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Columna derecha: gráficos --}}
  <div class="col-lg-9"> {{-- o col-lg-12 si quieres todo el ancho --}}
    <div class="row">
      <div class="col-md-6 d-flex">
        <div class="card mb-3 w-100 h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Total By Cause</strong>
            <span class="h5 mb-0" id="kpiTotalCause">0</span>
          </div>
          <div class="card-body">
            <canvas id="chartByCause" height="110"></canvas>
          </div>
        </div>
      </div>

      <div class="col-md-6 d-flex">
        <div class="card mb-3 w-100 h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Total</strong>
            <button class="btn btn-sm btn-light" disabled>Total</button>
          </div>
          <div class="card-body">
            <canvas id="chartTrend" height="110"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> {{-- ✅ cerramos el .row grande --}}

{{-- Card de filtros + buscador + tabla --}}
<div class="card mt-3">
  <div class="card-body pb-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="w-100">
        <div class="erp-filters-layout d-flex align-items-end justify-content-between flex-wrap" style="gap:.5rem">
          <div class="erp-filters-fields d-flex flex-wrap align-items-end" style="gap:.5rem">
            <div class="form-group mb-0">
              <label for="fltType" class="mb-1 sr-only">Type</label>
              <div class="input-group input-group" style="min-width:200px">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-light">
                    <i class="fas fa-tag text-warning"></i>
                  </span>
                </div>
                <select id="fltType" class="form-control form-control-sm erp-filter-control dt-filter">
                  <option value="">-- All --</option>
                </select>
              </div>
            </div>

            <div class="form-group mb-0">
              <label for="fltCustomer" class="mb-1 sr-only">Customer</label>
              <div class="input-group input-group" style="min-width:200px">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-light">
                    <i class="fas fa-user-tag text-primary"></i>
                  </span>
                </div>
                <select id="fltCustomer" class="form-control form-control-sm erp-filter-control dt-filter">
                  <option value="">-- All --</option>
                </select>
              </div>
            </div>

            <div class="form-group mb-0">
              <label for="fltStatus" class="mb-1 sr-only">Status</label>
              <div class="input-group input-group" style="min-width:190px">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-light">
                    <i class="fas fa-tasks text-info"></i>
                  </span>
                </div>
                <select id="fltStatus" class="form-control form-control-sm erp-filter-control dt-filter">
                  <option value="">-- All --</option>
                </select>
              </div>
            </div>
          </div>

          <div class="dt-filter-slot" data-dt-filter-slot="ncr"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="card-body p-1"> {{-- ✅ tabla dentro de card-body (poquito padding) --}}
    <div class="table-responsive position-relative fai-table-shell">
      <table id="ncrTable" class="table table-sm table-hover align-middle w-100 fai-dt-table">
        <thead class="table-light">
          <tr>
            <th>Number</th>
            <th class="col-desc-h">Description</th>
            <th>Title</th>
            <th>Created</th>
            <th>Customers</th>
            <th>Reference Numbers</th>
            <th>Type</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>



<!--  {{-- Tab: By End Schedule --}}-->

 </div>

@include('qa.faisummary.ncr_modal')

{{-- Stage Manager (qa_ncar_stage) --}}
<div class="modal fade" id="ncarStageModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header py-2 erp-stage-modal-header">
        <div class="d-flex align-items-center">
          <span class="erp-stage-title-icon mr-2"><i class="fas fa-layer-group"></i></span>
          <div>
            <h5 class="modal-title mb-0">NCAR Stages</h5>
            <small class="text-muted erp-stage-subtitle">Manage stage options per NCAR type</small>
          </div>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body erp-stage-modal-body">
        <div class="form-row">
          <div class="form-group col-12 col-md-4">
            <label class="mb-1 erp-stage-label">NCAR Type</label>
            <select id="stageNcarType" class="form-control form-control-sm">
              <option value="">Select...</option>
            </select>
          </div>
          <div class="form-group col-12 col-md-5">
            <label class="mb-1 erp-stage-label">Stage</label>
            <input id="stageName" type="text" class="form-control form-control-sm" maxlength="120" placeholder="e.g. Material" autocomplete="off">
            <input type="hidden" id="stageId" value="">
          </div>
          <div class="form-group col-12 col-md-1 d-flex align-items-end justify-content-center" id="stageActiveWrap">
            <div class="custom-control custom-checkbox mb-1">
              <input type="checkbox" class="custom-control-input" id="stageActive" checked>
              <label class="custom-control-label" for="stageActive">Active</label>
            </div>
          </div>
          <div class="form-group col-12 col-md-2 d-flex align-items-end justify-content-end">
            <button type="button" class="btn btn-primary btn-sm erp-stage-primary" id="btnSaveStage">
              <i class="fas fa-plus mr-1"></i> Add
            </button>
          </div>
        </div>

        <hr class="my-2">

        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0 erp-stage-table">
            <thead class="thead-light">
              <tr>
                <th>Stage</th>
                <th class="text-right">Active</th>
              </tr>
            </thead>
            <tbody id="stageListBody">
              <tr><td colspan="2" class="text-muted text-center py-2">Select NCAR Type.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer py-2 erp-stage-modal-footer">
        <button type="button" class="btn btn-secondary btn-sm erp-stage-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection



@section('css')
<!-- CSS complementario (puedes ponerlo en tu .css) -->
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<style>
  /* Forzar 14px en TODO el blade (texto, filtros, KPIs, paginado, etc.) */
  .ncar-page,
  .ncar-page * {
    font-size: 14px !important;
  }

  /* Mantener iconos con tamaño decente */
  .ncar-page i.fas,
  .ncar-page i.far,
  .ncar-page i.fab {
    font-size: 14px !important;
  }

  .kpi-card { border: 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.05); }
  .kpi-icon { width: 42px; height: 42px; border-radius: 10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
  .kpi-new .kpi-icon { background: #e8f6e8; color:#2e7d32; }
  .kpi-qa  .kpi-icon { background: #fff1e5; color:#ef6c00; }
  .kpi-eng .kpi-icon { background: #efe9ff; color:#6a1b9a; }
  .badge-status { border-radius: 999px; padding: .35rem .6rem; font-weight: 600; }
  .badge-closed { background: #e9f5ee; color:#1b5e20; }
  .badge-open   { background: #fff3f3; color:#b71c1c; }

  .dt-filter-slot { display: flex; align-items: center; justify-content: flex-end; flex: 1 1 auto; }
  /* Filtros estilo "schedule yarnell" (sin contenedor) */
  .ncar-page .erp-filter-control {
    border: 1px solid #c5c9d2;
    border-radius: 8px;
    padding: 6px 10px;
    background: #fff;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.06);
    color: #0f172a;
    font-weight: 600;
    height: 34px;
    line-height: 1.2;
  }

  .ncar-page .input-group-text {
    height: 34px;
    border: 1px solid #c5c9d2;
    border-right: 0;
    border-radius: 10px 0 0 10px;
    background: #fff !important;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
    color: #0f172a;
  }

  .ncar-page .input-group > .erp-filter-control {
    border-left: 0;
    border-radius: 0 10px 10px 0;
  }

  .ncar-page select.erp-filter-control {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 34px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%23475569' d='M5.5 7.5 10 12l4.5-4.5 1.4 1.4L10 14.8 4.1 8.9z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 14px 14px;
  }

  .ncar-page .erp-filter-control:focus {
    border-color: #94a3b8;
    box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
    outline: none;
  }

  /* Estilo ERP para tabla (similar a QA/FAI) */
  .dataTables_wrapper,
  .dataTables_wrapper .row,
  .dataTables_wrapper .col-sm-12,
  .dataTables_wrapper .col-md-6,
  .dataTables_wrapper .col-md-12 {
    max-width: 100% !important;
  }

  .table thead th {
    white-space: normal;
  }

  .fai-dt-table {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #d5d8dd;
    margin-bottom: 0;
  }

  .fai-dt-table thead th {
    background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
    color: #0f172a;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: .04em;
    text-transform: uppercase;
    border-bottom: 1px solid #d5d8dd !important;
    vertical-align: middle;
    padding: 6px 8px;
  }

  .fai-dt-table tbody td {
    font-size: 14px;
    color: #111827;
    vertical-align: middle;
    padding: 6px 8px;
    white-space: normal;
    word-break: break-word;
  }

  /* Columna Description: ancho fijo pero mostrando todo el texto */
  .fai-dt-table thead th.col-desc-h {
    white-space: nowrap;
  }

  .fai-dt-table tbody td.col-desc {
    white-space: normal;
    word-break: break-word;
    vertical-align: top;
  }

  .ncar-page .cell-desc {
    display: block;
    max-width: 320px;
    white-space: normal;
  }

  .fai-dt-table tbody tr:nth-child(odd) { background: #fff !important; }
  .fai-dt-table tbody tr:nth-child(even) { background: rgba(248, 250, 252, 0.85) !important; }
  .fai-dt-table tbody tr:hover { background: rgba(2, 6, 23, 0.04) !important; }

  #ncrTable_wrapper .erp-dt-footer {
    margin-top: 2px;
    padding: 2px 0 6px;
  }

  /* Paginado tipo ERP (DataTables/Bootstrap) */
  #ncrTable_wrapper .dataTables_info {
    color: rgba(15, 23, 42, 0.80);
    font-weight: 600;
    line-height: 1.2;
    padding: 0 !important;
    margin: 0 !important;
  }

  #ncrTable_wrapper .dataTables_paginate {
    margin-top: 0.1rem !important;
    padding-top: 0.1rem !important;
  }

  #ncrTable_wrapper .dataTables_paginate .pagination {
    margin: 0 !important;
  }

  #ncrTable_wrapper .pagination .page-link {
    border-radius: 8px;
    margin: 0 2px;
    border: 1px solid rgba(15, 23, 42, 0.18);
    background: rgba(241, 245, 249, 0.95);
    color: #0f172a;
    font-weight: 700;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
    font-size: 14px;
    line-height: 1.5;
    padding: 0.375rem 0.75rem;
    transition: background-color .12s ease, transform .08s ease, box-shadow .12s ease;
  }

  #ncrTable_wrapper .pagination .page-link:hover {
    background: rgba(226, 232, 240, 1);
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(16, 24, 40, 0.10);
  }

  #ncrTable_wrapper .pagination .page-item.active .page-link {
    background: #0b5ed7;
    border-color: #0b5ed7;
    color: #fff;
  }

  #ncrTable_wrapper .pagination .page-item.disabled .page-link {
    opacity: .55;
    transform: none;
    box-shadow: none;
  }

  /* Solo forzar scroll en pantallas chicas */
  @media (max-width: 992px) {
    #ncrTable td:nth-child(3) { min-width: 240px; } /* Title */
    #ncrTable td:nth-child(6) { min-width: 260px; } /* Reference Numbers */
  }

  /* Buttons tipo ERP (icon-only) */
  .ncar-page .erp-table-btn {
    height: 30px;
    width: 34px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
  }

  .ncar-page .btn-erp-primary,
  .ncar-page .btn-erp-success,
  .ncar-page .btn-erp-danger,
  .ncar-page .btn-erp-warning {
    background: #f8fafc;
    border: 1px solid #d5d8dd;
    color: #1f2937;
    box-shadow: none;
    font-weight: 700;
  }

  .ncar-page .btn-erp-primary i { color: #0b5ed7; }
  .ncar-page .btn-erp-success i { color: #0f5132; }
  .ncar-page .btn-erp-danger i { color: #b91c1c; }
  .ncar-page .btn-erp-warning i { color: #f59e0b; }

  .ncar-page .btn-erp-primary:hover,
  .ncar-page .btn-erp-success:hover,
  .ncar-page .btn-erp-danger:hover,
  .ncar-page .btn-erp-warning:hover {
    filter: brightness(0.97);
    color: #111827;
  }

  /* NCR modal (ERP style) */
  #ncrModal .modal-content {
    border-radius: 12px;
    border: 1px solid rgba(15, 23, 42, 0.14);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
    overflow: hidden;
  }

  #ncrModal .modal-dialog {
    max-width: 1120px;
    width: calc(100% - 1rem);
  }

  #ncrModal .erp-ncr-modal-header {
    background: #fff !important;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08) !important;
    padding: 14px 16px !important;
  }

  #ncrModal .erp-ncr-title-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(245, 158, 11, 0.40);
    background: rgba(245, 158, 11, 0.12);
    color: #b45309;
  }

  #ncrModal .erp-ncr-title-icon i { font-size: 16px; }
  #ncrModal .erp-ncr-chip { display: none !important; }

  #ncrModal .erp-ncr-subtitle {
    display: block !important;
    margin-top: 2px;
    font-size: 0.82rem;
    color: #6b7280;
    font-weight: 600;
    line-height: 1.1;
  }

  #ncrModal .erp-ncr-modal-body {
    background: #fff;
    padding: 14px 16px !important;
    max-height: calc(100vh - 190px) !important;
    overflow: auto;
  }

  #ncrModal .erp-ncr-modal-footer {
    background: #fff !important;
    border-top: 1px solid rgba(15, 23, 42, 0.08) !important;
    padding: 14px 16px !important;
  }

  #ncrModal .erp-ncr-label {
    display: block !important;
    margin: 0 0 6px !important;
    color: #6b7280 !important;
    font-weight: 700 !important;
    font-size: 0.78rem !important;
    letter-spacing: .02em !important;
    text-transform: none !important;
  }

  #ncrModal .erp-ncr-input-group .input-group-text { display: none !important; }

  #ncrModal .erp-ncr-control {
    height: 46px !important;
    border-radius: 8px !important;
    border: 1px solid rgba(15, 23, 42, 0.12) !important;
    background: #fff !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06) !important;
    color: #111827 !important;
    font-weight: 600 !important;
    padding: 10px 12px !important;
  }

  #ncrModal .erp-ncr-control[readonly] {
    background: rgba(241, 245, 249, 0.85) !important;
    color: #0f172a !important;
    box-shadow: none !important;
  }

  /* Order search (inside NCR modal) */
  #ncrModal .ncr-order-searchbar .input-group-text {
    display: flex !important;
    height: 46px !important;
    border-radius: 10px 0 0 10px !important;
    border: 1px solid rgba(15, 23, 42, 0.12) !important;
    border-right: 0 !important;
    background: rgba(234, 242, 255, 0.85) !important;
    color: #0b5ed7 !important;
  }

  #ncrModal .ncr-order-searchbar input.erp-ncr-control {
    border-left: 0 !important;
    border-radius: 0 10px 10px 0 !important;
    background: rgba(234, 242, 255, 0.55) !important;
    font-weight: 700 !important;
  }

  #ncrModal .ncr-order-results {
    border: 1px solid rgba(15, 23, 42, 0.10);
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
  }

  #ncrModal .ncr-order-table thead th {
    background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
    border-bottom: 1px solid rgba(15, 23, 42, 0.10) !important;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-size: 0.74rem;
    color: #334155;
    font-weight: 800;
    white-space: nowrap;
  }

  #ncrModal .ncr-order-table tbody td {
    vertical-align: middle;
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    font-weight: 600;
    color: #0f172a;
  }

  #ncrModal .ncr-order-table tbody tr:hover td {
    background: rgba(13, 110, 253, 0.04);
  }

  #ncrModal .ncr-order-table tbody tr.is-selected td {
    background: rgba(13, 110, 253, 0.10) !important;
  }

  #ncrModal .ncr-order-table tbody tr.is-selected td:first-child {
    box-shadow: inset 3px 0 0 rgba(11, 94, 215, 0.65);
  }

  #ncrModal .ncr-order-desc {
    max-width: 520px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    vertical-align: bottom;
  }

  #ncrModal .ncr-order-action {
    height: 30px;
    width: 34px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: 1px solid rgba(15, 23, 42, 0.14);
    background: rgba(241, 245, 249, 0.95);
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
    color: #334155;
  }

  #ncrModal .ncr-order-action i {
    color: #16a34a;
  }

  #ncrModal .ncr-order-action:hover {
    filter: brightness(0.98);
  }

  #ncrModal textarea.erp-ncr-control {
    height: auto;
    min-height: 86px !important;
    resize: vertical;
  }

  #ncrModal .erp-ncr-control:focus {
    border-color: rgba(59, 130, 246, 0.55) !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
    outline: none !important;
  }

  #ncrModal .select2-container { width: 100% !important; }
  #ncrModal .select2-container--default .select2-selection--single {
    height: 46px !important;
    border-radius: 8px !important;
    border: 1px solid rgba(15, 23, 42, 0.12) !important;
    background: #fff !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06) !important;
    display: flex !important;
    align-items: center !important;
  }

  #ncrModal .select2-container--default .select2-selection--single .select2-selection__rendered {
    height: 46px !important;
    line-height: 46px !important;
    padding: 0 40px 0 12px !important;
    flex: 1 1 auto;
    color: #111827 !important;
  }

  /* Stage manager: hide by default; show only when JS adds .is-visible */
  #ncarStageModal #stageActiveWrap {
    display: none !important;
  }
  #ncarStageModal #stageActiveWrap.is-visible {
    display: flex !important;
  }

  /* Stage modal (ERP style, like NCR modal) */
  #ncarStageModal .modal-content {
    border-radius: 12px;
    border: 1px solid rgba(15, 23, 42, 0.14);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
    overflow: hidden;
  }

  #ncarStageModal .modal-dialog {
    max-width: 860px;
    width: calc(100% - 1rem);
  }

  #ncarStageModal .erp-stage-modal-header {
    background: #fff !important;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08) !important;
    padding: 14px 16px !important;
  }

  #ncarStageModal .erp-stage-title-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(148, 163, 184, 0.45);
    background: rgba(148, 163, 184, 0.14);
    color: #334155;
  }

  #ncarStageModal .erp-stage-title-icon i { font-size: 16px; }

  #ncarStageModal .erp-stage-subtitle {
    display: block;
    margin-top: 2px;
    font-size: 0.82rem;
    color: #6b7280;
    font-weight: 600;
    line-height: 1.1;
  }

  #ncarStageModal .erp-stage-modal-body {
    background: #fff;
    padding: 14px 16px !important;
    max-height: calc(100vh - 210px) !important;
    overflow: auto;
  }

  #ncarStageModal .erp-stage-modal-footer {
    background: #fff !important;
    border-top: 1px solid rgba(15, 23, 42, 0.08) !important;
    padding: 14px 16px !important;
  }

  #ncarStageModal .erp-stage-label {
    display: block;
    margin: 0 0 6px !important;
    color: #6b7280 !important;
    font-weight: 700 !important;
    font-size: 0.78rem !important;
    letter-spacing: .02em !important;
    text-transform: none !important;
  }

  #ncarStageModal .form-control,
  #ncarStageModal .form-control-sm,
  #ncarStageModal select.form-control-sm,
  #ncarStageModal input.form-control-sm {
    height: 38px !important;
    border-radius: 8px !important;
    border: 1px solid rgba(15, 23, 42, 0.12) !important;
    background: #fff !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06) !important;
    color: #111827 !important;
    font-weight: 600 !important;
    padding: 6px 10px !important;
  }

  #ncarStageModal .form-control:focus {
    border-color: rgba(59, 130, 246, 0.55) !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
    outline: none !important;
  }

  #ncarStageModal .custom-control-label {
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
  }

  #ncarStageModal .erp-stage-primary {
    height: 36px;
    border-radius: 10px;
    font-weight: 800;
    box-shadow: 0 10px 18px rgba(11, 94, 215, 0.18);
  }

  #ncarStageModal .erp-stage-secondary {
    height: 36px;
    border-radius: 10px;
    font-weight: 800;
    background: rgba(241, 245, 249, 0.9);
    border: 1px solid rgba(15, 23, 42, 0.12);
    color: #0f172a;
  }

  #ncarStageModal .erp-stage-table {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(15, 23, 42, 0.10);
  }

  #ncarStageModal .erp-stage-table thead th {
    background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
    border-bottom: 1px solid rgba(15, 23, 42, 0.10) !important;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-size: 0.74rem;
    color: #334155;
    font-weight: 800;
    white-space: nowrap;
  }

  #ncarStageModal .erp-stage-table tbody td {
    vertical-align: middle;
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    font-weight: 600;
    color: #0f172a;
  }

  #ncarStageModal .erp-stage-table tbody tr:hover td {
    background: rgba(13, 110, 253, 0.04);
    cursor: pointer;
  }

  #ncarStageModal .erp-stage-table tbody tr.table-active td {
    background: rgba(13, 110, 253, 0.10) !important;
  }

  #ncarStageModal .erp-stage-table tbody tr.table-active td:first-child {
    box-shadow: inset 3px 0 0 rgba(11, 94, 215, 0.65);
  }
</style>
@endsection


@push('js')

<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
<script>
$(function(){
  // Charts: forzar fuentes a 14px también dentro del canvas
  if (window.Chart && Chart.defaults && Chart.defaults.font) {
    Chart.defaults.font.size = 14;
  }

  // ===== DataTable =====
  const dt = $('#ncrTable').DataTable({
    ajax: {
      url: '{{ route('nonconformance.data') }}',
      dataSrc: '' // el endpoint devuelve un array directo
    },
    autoWidth: false,
    responsive: true,
    deferRender: true,
    pageLength: 10,
    order: [[0,'desc']],
    columnDefs: [
      { targets: [1], className: 'col-desc', width: '320px' },
      { targets: [2,5], className: 'text-wrap' },
      { targets: [8], orderable: false, searchable: false, className: 'text-nowrap text-center', width: '170px' }
    ],
    dom: "frt<'erp-dt-footer d-flex align-items-center justify-content-between flex-wrap'<'dataTables_info'i><'dataTables_paginate'p>>",
    columns: [
      {data:'number'},
      {data:'description', render:(d)=>{
        const v = (d ?? '').toString();
        const esc = escapeHtml(v);
        return `<div class="cell-desc">${esc}</div>`;
      }},
      {data:'title'},
      {data:'created', render:d=> d? new Date(d).toLocaleDateString():''},
      {data:'customer'},
      {data:'ref_numbers', render:(d)=>{
        const v = (d ?? '').toString().trim();
        if (!v) return '';
        return v
          .split('|')
          .map(s => escapeHtml(s.trim()))
          .filter(Boolean)
          .join('<br>');
      }},
      {data:'type'},
      {data:'status', render:s=>{
        const closed = String(s).toLowerCase()==='closed';
        return `<span class="badge badge-status ${closed?'badge-closed':'badge-open'}">${s}</span>`;
      }},
      {data:null, render:(d, t, row)=>{
        const editUrl = row?.edit_url || '#';
        const pdfUrl = row?.pdf_url || '#';
        const excelUrl = row?.excel_url || '#';
        const deleteUrl = row?.delete_url || '#';
        return `
          <div class="d-inline-flex align-items-center" style="gap:6px" role="group" aria-label="Actions">
            <a class="btn btn-sm btn-erp-warning erp-table-btn" href="${editUrl}" title="Edit">
              <i class="fas fa-edit"></i>
            </a>
            <a class="btn btn-sm btn-erp-primary erp-table-btn" href="${pdfUrl}" target="_blank" rel="noopener" title="PDF">
              <i class="fas fa-file-pdf"></i>
            </a>
            <a class="btn btn-sm btn-erp-success erp-table-btn" href="${excelUrl}" title="Excel">
              <i class="fas fa-file-excel"></i>
            </a>
            <button type="button" class="btn btn-sm btn-erp-danger erp-table-btn btn-ncar-delete" data-url="${deleteUrl}" title="Delete">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        `;
      }}
    ]
  });

  const getCsrf = () => $('meta[name="csrf-token"]').attr('content') || '';

  $('#ncrTable').on('click', '.btn-ncar-delete', function(e) {
    e.preventDefault();
    const url = ($(this).data('url') || '').toString();
    if (!url || url === '#') return;

    const doDelete = () => $.ajax({
      url,
      method: 'DELETE',
      dataType: 'json',
      data: { _token: getCsrf() },
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .done((res) => {
        if (!res || res.success !== true) {
          const msg = (res && res.message) ? res.message : 'Could not delete NCAR.';
          if (window.Swal) return Swal.fire('Error', msg, 'error');
          alert(msg);
          return;
        }
        dt.ajax.reload(null, false);
        if (window.Swal) return Swal.fire('Deleted', 'NCAR deleted.', 'success');
      })
      .fail((xhr) => {
        const msg = xhr?.responseJSON?.message || 'Could not delete NCAR.';
        if (window.Swal) return Swal.fire('Error', msg, 'error');
        alert(msg);
      });

    if (window.Swal) {
      Swal.fire({
        icon: 'warning',
        title: 'Delete NCAR?',
        text: 'This action cannot be undone.',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
      }).then((r) => {
        if (!r.isConfirmed) return;
        doDelete();
      });
      return;
    }

    if (confirm('Delete NCAR? This action cannot be undone.')) doDelete();
  });

  // Acomodar search estilo ERP (igual que otras vistas)
  const $slot = $('.dt-filter-slot[data-dt-filter-slot="ncr"]');
  const $filter = $(dt.table().container()).find('.dataTables_filter');
  const $input = $filter.find('input[type="search"], input[type="text"]').first();
  if ($slot.length && $filter.length && $input.length) {
    $input.attr('placeholder', 'Search...').addClass('form-control');
    $input.addClass('erp-filter-control');
    const $group = $('<div class="input-group input-group" style="min-width:240px; max-width:420px"></div>');
    $group.append('<div class="input-group-prepend"><span class="input-group-text bg-light"><i class="fas fa-search text-secondary"></i></span></div>');
    $group.append($input);
    $filter.empty().append($group);
    $slot.empty().append($filter);
  }

  const escapeRegex = (v) => String(v).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const escapeHtml = (v) => String(v)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  function fillSelect($sel, values) {
    const current = ($sel.val() || '').toString();
    $sel.find('option:not(:first)').remove();
    values
      .filter(v => v !== null && v !== undefined && String(v).trim() !== '')
      .map(v => String(v).trim())
      .filter((v, i, a) => a.indexOf(v) === i)
      .sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base', numeric: true }))
      .forEach(v => $sel.append($('<option>', { value: v, text: v })));
    if (current) $sel.val(current);
  }

  function refreshFilters() {
    // Mantener todas las opciones disponibles aunque haya filtros activos
    // (para poder cambiar de opción sin tener que regresar a "-- All --").
    fillSelect($('#fltType'), dt.column(6, { search: 'none' }).data().toArray());
    fillSelect($('#fltCustomer'), dt.column(4, { search: 'none' }).data().toArray());
    fillSelect($('#fltStatus'), dt.column(7, { search: 'none' }).data().toArray());
  }

  // Inicial + cada vez que cambie el dataset
  dt.on('xhr.dt', refreshFilters);
  dt.on('draw.dt', refreshFilters);
  refreshFilters();

  $('#fltType').on('change', function() {
    const v = (this.value || '').toString();
    dt.column(6).search(v ? ('^' + escapeRegex(v) + '$') : '', true, false).draw();
  });
  $('#fltCustomer').on('change', function() {
    const v = (this.value || '').toString();
    dt.column(4).search(v ? ('^' + escapeRegex(v) + '$') : '', true, false).draw();
  });
  $('#fltStatus').on('change', function() {
    const v = (this.value || '').toString();
    dt.column(7).search(v ? ('^' + escapeRegex(v) + '$') : '', true, false).draw();
  });

  // ===== Charts =====
  let causeChart, trendChart;

  function gradient(ctx, top, bottom){
    const g = ctx.createLinearGradient(0,0,0,180);
    g.addColorStop(0, top); g.addColorStop(1, bottom); return g;
  }

  function initCharts(p){
    $('#kpiNew').text(p.kpis?.new ?? 0);
    $('#kpiQA').text(p.kpis?.quality_review ?? 0);
    $('#kpiEng').text(p.kpis?.engineering_review ?? 0);
    $('#kpiTotalCause').text((p.by_cause||[]).reduce((a,c)=>a+(c.value||0),0));

    const ctx1 = document.getElementById('chartByCause').getContext('2d');
    causeChart?.destroy();
    causeChart = new Chart(ctx1,{
      type:'bar',
      data:{
        labels:(p.by_cause||[]).map(i=>i.label),
        datasets:[{label:'Total', data:(p.by_cause||[]).map(i=>i.value),
          backgroundColor: gradient(ctx1,'rgba(96,165,250,.9)','rgba(96,165,250,.25)'), borderRadius:10}]
      },
      options:{
        plugins:{legend:{display:false}},
        scales:{
          x:{grid:{display:false},ticks:{precision:0,font:{size:14}}},
          y:{beginAtZero:true,ticks:{precision:0,font:{size:14}}}
        }
      }
    });

    const ctx2 = document.getElementById('chartTrend').getContext('2d');
    trendChart?.destroy();
    trendChart = new Chart(ctx2,{
      type:'line',
      data:{
        labels:(p.trend||[]).map(i=>new Date(i.x).toLocaleDateString(undefined,{month:'2-digit',day:'2-digit'})),
        datasets:[{label:'Total', data:(p.trend||[]).map(i=>i.y), fill:true, tension:.35,
          backgroundColor: gradient(ctx2,'rgba(124,58,237,.55)','rgba(124,58,237,.08)'),
          borderColor:'rgba(124,58,237,.8)', pointRadius:0}]
      },
      options:{
        plugins:{legend:{display:false}},
        scales:{
          x:{grid:{display:false},ticks:{precision:0,font:{size:14}}},
          y:{beginAtZero:true,ticks:{precision:0,font:{size:14}}}
        }
      }
    });
  }

  fetch('{{ route('nonconformance.stats') }}')
    .then(r=>r.json()).then(initCharts).catch(console.error);
});
</script>

  <script>
  $(function () {
    const ROUTES = {
      ncarTypes: `/qa/ncar/types`,
      ncarStages: `/qa/ncar/stages`,
      nextNcarNumber: `/qa/ncar/next-number`,
      storeNcar: `/qa/ncar`,
      storeNcarStage: `/qa/ncar/stages`,
      updateNcarStageBase: `/qa/ncar/stages/`,
    };

    const getCsrf = () =>
      $('meta[name="csrf-token"]').attr('content') ||
      $('input[name="_token"]').val() ||
      '';

    const fetchJson = (url, opts = {}) =>
      $.ajax(Object.assign({
        url,
        method: 'GET',
        dataType: 'json',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        timeout: 12000
      }, opts)).catch(() => null);

    let __ncarTypesPromise = null;
    const fillNcarTypeSelect = function ($sel, list) {
      if (!$sel || !$sel.length) return;
      const current = ($sel.val() || '').toString();
      $sel.empty().append($('<option>', { value: '', text: 'Select...' }));
      list.forEach(t => {
        const id = (t?.id ?? '').toString();
        if (!id) return;
        const name = (t?.name ?? t?.code ?? id).toString();
        const $opt = $('<option>', { value: id, text: name });
        if (t?.code) $opt.attr('data-code', String(t.code));
        $sel.append($opt);
      });
      if (current) $sel.val(current);
    };

    const loadNcarTypes = function () {
      if (__ncarTypesPromise) return __ncarTypesPromise;
      __ncarTypesPromise = fetchJson(ROUTES.ncarTypes).then(res => {
        const list = Array.isArray(res?.data) ? res.data : (Array.isArray(res) ? res : []);
        fillNcarTypeSelect($('#ncrNcarType'), list);
        fillNcarTypeSelect($('#stageNcarType'), list);
        return list;
      });
      return __ncarTypesPromise;
    };

    const STAGE_CACHE = new Map(); // key -> [{stage}]
    const stageCacheKey = (ncartypeId, includeInactive) => `${String(ncartypeId)}|${includeInactive ? 1 : 0}`;
    const fetchStagesForType = function (ncartypeId, code = '', includeInactive = false) {
      const id = (ncartypeId || '').toString().trim();
      if (!id) return Promise.resolve([]);
      const key = stageCacheKey(id, includeInactive);
      if (STAGE_CACHE.has(key)) return Promise.resolve(STAGE_CACHE.get(key));
      return fetchJson(ROUTES.ncarStages, { data: { ncartype_id: id, code: (code || '').toString().toUpperCase(), include_inactive: includeInactive ? 1 : 0 } })
        .then(res => {
          const list = Array.isArray(res?.data) ? res.data : (Array.isArray(res) ? res : []);
          STAGE_CACHE.set(key, list);
          return list;
        });
    };

	    const syncNcarStageOptions = function () {
	      const code = ($('#ncrNcarType option:selected').attr('data-code') || '').toString().toUpperCase();
	      const $stage = $('#ncrStage');
	      const $col = $('#ncrStageCol');
	      const $refCol = $('#ncrRefCol');
	      const $ref = $('#ncrRef');
	      const $dateCol = $('#ncrDateCol');
	      const $customerCol = $('#ncrCustomerCol');
	      const $numberCol = $('#ncrNumberCol');
	      const $typeCol = $('#ncrNcarTypeCol');
	      const ncartypeId = ($('#ncrNcarType').val() || '').toString().trim();
	      const currentStage = ($stage.val() || '').toString();
	      const isCustomer = code === 'CUSTOMER';

	      const stripMdCols = function ($el) {
	        $el
	          .removeClass('col-md-1')
	          .removeClass('col-md-2')
	          .removeClass('col-md-3')
	          .removeClass('col-md-4')
	          .removeClass('col-md-5')
	          .removeClass('col-md-6');
	      };

	      const applyMdCol = function ($el, md) {
	        if (!$el || !$el.length) return;
	        stripMdCols($el);
	        $el.addClass('col-md-' + md);
	      };

	      const applyTopLayout = function (shouldShowStage) {
	        // Keep fields on one line:
	        // - Default (INTERNAL/EXTERNAL): Date2 + Customer3 + NCR2 + Type2 + Stage3 = 12 (Ref hidden)
	        // - CUSTOMER: Date2 + Customer2 + NCR2 + Type2 + Ref2 + Stage2 = 12
	        if (shouldShowStage && isCustomer) {
	          applyMdCol($dateCol, 2);
	          applyMdCol($customerCol, 2);
	          applyMdCol($numberCol, 2);
	          applyMdCol($typeCol, 2);
	          applyMdCol($refCol, 2);
	          applyMdCol($col, 2);
	          return;
	        }

	        if (shouldShowStage) {
	          applyMdCol($dateCol, 2);
	          applyMdCol($customerCol, 3);
	          applyMdCol($numberCol, 2);
	          applyMdCol($typeCol, 2);
	          applyMdCol($refCol, 3);
	          applyMdCol($col, 3);
	          return;
	        }

	        // No stage: give more space to customer/number/type
	        applyMdCol($dateCol, 2);
	        applyMdCol($customerCol, 4);
	        applyMdCol($numberCol, 3);
	        applyMdCol($typeCol, 3);
	        applyMdCol($refCol, 3);
	        applyMdCol($col, 3);
	      };

	      // Customer NCAR: show Reference field
	      if ($refCol.length) {
	        $refCol.toggleClass('d-none', !isCustomer);
	        if (!isCustomer && $ref.length) $ref.val('');
	      }

	      // Apply layout immediately when possible (avoid wrapping while async stage list loads).
	      // - INTERNAL/EXTERNAL/CUSTOMER always show Stage
	      // - other types: Stage depends on whether there are stages in DB
	      const wantsStage = !!ncartypeId && (code === 'INTERNAL' || code === 'EXTERNAL' || code === 'CUSTOMER');
	      if (wantsStage) {
	        $col.removeClass('d-none');
	        applyTopLayout(true);
	      } else {
	        $col.addClass('d-none');
	        applyTopLayout(false);
	      }

      // Clear + (re)load stages from DB (qa_ncar_stage). If empty, keep ability to type (tags).
      $stage.empty().append($('<option>', { value: '', text: 'Select...' }));

	      if (!ncartypeId) {
	        $col.addClass('d-none');
	        applyTopLayout(false);
	        if ($stage.data('select2')) {
	          try { $stage.select2('destroy'); } catch (e) {}
	        }
	        return;
	      }

      fetchStagesForType(ncartypeId, code).then(list => {
        const stages = Array.isArray(list) ? list : [];
        stages.forEach(s => {
          const val = (s?.stage ?? '').toString();
          if (!val) return;
          const txt = val;
          $stage.append($('<option>', { value: val, text: txt }));
        });

	        // show stage for INTERNAL/EXTERNAL/CUSTOMER always; for others only if there are stages
	        const shouldShow = (code === 'INTERNAL' || code === 'EXTERNAL' || code === 'CUSTOMER') || stages.length > 0;
	        $col.toggleClass('d-none', !shouldShow);
	        applyTopLayout(shouldShow);

	        if (currentStage) {
	          const exists = $stage.find('option').toArray().some(o => (o.value || '') === currentStage);
	          if (!exists) $stage.append($('<option>', { value: currentStage, text: currentStage }));
	          $stage.val(currentStage);
        }

        if (shouldShow && $.fn && $.fn.select2 && !$stage.data('select2')) {
          $stage.select2({
            tags: true,
            width: '100%',
            dropdownParent: $('#ncrModal'),
            placeholder: 'Select or type...',
            allowClear: false
          });
        }
        if (!shouldShow && $stage.data('select2')) {
          try { $stage.select2('destroy'); } catch (e) {}
        }
      });
    };

    const applyNextNcarNumber = function (force = false) {
      const ncartypeId = ($('#ncrNcarType').val() || '').toString().trim();
      if (!ncartypeId) return;

      const $field = $('#ncrNumber');
      const current = (($field.val() || '').toString()).trim();
      const lastAuto = (($field.data('autoNcarNo') || '').toString()).trim();
      if (!force && current && current !== lastAuto) return;

      fetchJson(ROUTES.nextNcarNumber, { data: { ncartype_id: ncartypeId } }).then(res => {
        const no = (res && (res.ncar_no || res.number || res.next || res.no)) ? (res.ncar_no || res.number || res.next || res.no) : '';
        if (!no) return;
        $field.val(String(no));
        $field.data('autoNcarNo', String(no));
      });
    };

    const openNewNcarModal = function () {
      loadNcarTypes().then(() => {
        $('#ncrOrderSearchBox').removeClass('d-none');
        $('#ncrOrderId').val('');
        $('#ncrOrderResultsBody').empty().append(`
          <tr>
            <td colspan="6" class="text-muted text-center py-2">Select NCAR Type and search an order.</td>
          </tr>
        `);
        $('#ncrOrderSearch').val('');

        const today = new Date().toISOString().split('T')[0];
        $('#ncrDate').val(today);

        $('#ncrOrderId').val('');
        $('#ncrPostUrl').val('');

        $('#ncrWorkId, #ncrCo, #ncrCustPo, #ncrPn, #ncrCustomer, #ncrOperation, #ncrQty, #ncrWoQty').val('');
        $('#ncrDescription').val('');

        $('#ncrHeaderWorkId').text('—');
        $('#ncrHeaderCustomer').text('—');

        $('#ncrNumber').val('').data('autoNcarNo', '');
        $('#ncrNotes').val('');

        $('#ncrNcarType').val('');
        $('#ncrStage').val('');
        $('#ncrRef').val('');
        syncNcarStageOptions();

        const defaultReviewer = ($('#ncrReviewer').data('default') || '').toString();
        $('#ncrReviewer').val(defaultReviewer);

        $('#ncrSaveBtn').prop('disabled', false);
        $('#ncrModal').data('btn', null);
        $('#ncrModal').modal('show');
      });
    };

    $('#btnCreateNcar').off('click.ncr').on('click.ncr', function (e) {
      e.preventDefault();
      openNewNcarModal();
    });

    function updateStageActiveVisibility() {
      const hasStageText = (($('#stageName').val() || '').toString().trim().length > 0);
      const isEditing = (($('#stageId').val() || '').toString().trim().length > 0);
      const show = (hasStageText || isEditing);
      $('#stageActiveWrap').toggleClass('is-visible', show);
    }

    // Ensure default hidden on load (avoid flashing visible due to cached DOM/classes)
    $('#stageActiveWrap').removeClass('is-visible');
    updateStageActiveVisibility();

    function renderStageList(list) {
      const $body = $('#stageListBody');
      if (!$body.length) return;
      const rows = Array.isArray(list) ? list : [];
      $body.empty();
      if (!rows.length) {
        $body.append('<tr><td colspan="2" class="text-muted text-center py-2">No stages.</td></tr>');
        return;
      }
      rows.forEach(s => {
        const id = (s?.id ?? '').toString();
        const ncartypeId = (s?.ncartype_id ?? '').toString();
        const stage = (s?.stage ?? '').toString();
        const active = (s?.is_active ?? 1) ? 'Yes' : 'No';
        const $tr = $(`
          <tr data-id="${$('<div>').text(id).html()}" data-ncartype-id="${$('<div>').text(ncartypeId).html()}">
            <td>${$('<div>').text(stage).html()}</td>
            <td class="text-right">${$('<div>').text(active).html()}</td>
          </tr>
        `);

        $tr.on('click', function () {
          const $row = $(this);
          const rowId = ($row.data('id') || '').toString();
          const rowTypeId = ($row.data('ncartype-id') || '').toString();
          const already = ($('#stageId').val() || '').toString() === rowId;

          // toggle off selection
          if (already) {
            $('#stageId').val('');
            $('#stageName').val('');
            $('#stageActive').prop('checked', true);
            $('#btnSaveStage').html('<i class="fas fa-plus mr-1"></i> Add Stage');
            $row.removeClass('table-active');
            updateStageActiveVisibility();
            return;
          }

          $('#stageListBody tr').removeClass('table-active');
          $row.addClass('table-active');

          $('#stageId').val(rowId);
          $('#stageNcarType').val(rowTypeId);
          $('#stageName').val(stage);
          $('#stageActive').prop('checked', String(s?.is_active ?? 1) === '1');
          $('#btnSaveStage').html('<i class="fas fa-save mr-1"></i> Save Stage');
          updateStageActiveVisibility();
        });

        $body.append($tr);
      });
    }

    function refreshStageManagerList() {
      const ncartypeId = ($('#stageNcarType').val() || '').toString().trim();
      const code = ($('#stageNcarType option:selected').attr('data-code') || '').toString().toUpperCase();
      if (!ncartypeId) {
        $('#stageListBody').empty().append('<tr><td colspan="2" class="text-muted text-center py-2">Select NCAR Type.</td></tr>');
        return;
      }
      $('#stageId').val('');
      $('#stageName').val('');
      $('#stageActive').prop('checked', true);
      $('#btnSaveStage').html('<i class="fas fa-plus mr-1"></i> Add Stage');
      updateStageActiveVisibility();

      STAGE_CACHE.delete(stageCacheKey(ncartypeId, true));
      fetchStagesForType(ncartypeId, code, true).then(renderStageList);
    }

    $('#btnManageStages').off('click.stages').on('click.stages', function (e) {
      e.preventDefault();
      loadNcarTypes().then(() => {
        $('#stageNcarType').val('');
        $('#stageName').val('').trigger('input');
        $('#stageId').val('');
        $('#stageActive').prop('checked', true);
        $('#btnSaveStage').html('<i class="fas fa-plus mr-1"></i> Add Stage');
        $('#stageActiveWrap').removeClass('is-visible');
        updateStageActiveVisibility();
        $('#stageListBody').empty().append('<tr><td colspan="2" class="text-muted text-center py-2">Select NCAR Type.</td></tr>');
        $('#ncarStageModal').modal('show');
      });
    });

    $('#ncarStageModal')
      .off('shown.bs.modal.stages')
      .on('shown.bs.modal.stages', function () {
        $('#stageActiveWrap').removeClass('is-visible');
        updateStageActiveVisibility();
        // Some browsers may autofill inputs after the modal is shown; re-check once.
        setTimeout(updateStageActiveVisibility, 0);
      });

    $('#stageNcarType').off('change.stages').on('change.stages', function () {
      refreshStageManagerList();
    });

    $('#stageName').off('input.stages').on('input.stages', function () {
      updateStageActiveVisibility();
    });

    $('#btnSaveStage').off('click.stages').on('click.stages', function () {
      const ncartypeId = ($('#stageNcarType').val() || '').toString().trim();
      const stage = ($('#stageName').val() || '').toString().trim();
      const isActive = $('#stageActive').is(':checked') ? 1 : 0;
      const stageId = ($('#stageId').val() || '').toString().trim();

      if (!ncartypeId) {
        if (window.Swal) return Swal.fire('Required', 'Select NCAR Type.', 'warning');
        alert('Select NCAR Type.');
        return;
      }
      if (!stage) {
        if (window.Swal) return Swal.fire('Required', 'Enter Stage.', 'warning');
        alert('Enter Stage.');
        return;
      }

      const $btn = $('#btnSaveStage');
      $btn.prop('disabled', true);

      const isEdit = !!stageId;
      $.ajax({
        url: isEdit ? (ROUTES.updateNcarStageBase + encodeURIComponent(stageId)) : ROUTES.storeNcarStage,
        method: isEdit ? 'PUT' : 'POST',
        dataType: 'json',
        data: {
          _token: getCsrf(),
          ncartype_id: ncartypeId,
          stage,
          is_active: isActive
        }
      }).done((res) => {
        if (!res || res.success !== true) {
          const msg = (res && res.message) ? res.message : 'Could not save stage.';
          if (window.Swal) return Swal.fire('Error', msg, 'error');
          alert(msg);
          return;
        }

        $('#stageId').val('');
        $('#stageName').val('');
        $('#stageActive').prop('checked', true);
        $('#btnSaveStage').html('<i class="fas fa-plus mr-1"></i> Add Stage');
        updateStageActiveVisibility();

        refreshStageManagerList();

        // If NCR modal is open and using same type, refresh stage options there too
        const currentType = ($('#ncrNcarType').val() || '').toString().trim();
        if (currentType && currentType === ncartypeId) syncNcarStageOptions();

        if (window.Swal) return Swal.fire('Saved', isEdit ? 'Stage updated.' : 'Stage added.', 'success');
      }).fail((xhr) => {
        const msg = xhr?.responseJSON?.message || 'Could not save stage.';
        if (window.Swal) return Swal.fire('Error', msg, 'error');
        alert(msg);
      }).always(() => {
        $btn.prop('disabled', false);
      });
    });

    function clearNcarModalAll() {
      // Reset selection
      $('#ncrOrderId').val('');

      // Reset Impact fields
      $('#ncrWorkId, #ncrCo, #ncrCustPo, #ncrPn, #ncrCustomer, #ncrOperation, #ncrQty, #ncrWoQty').val('');
      $('#ncrDescription').val('');
      $('#ncrHeaderWorkId').text('—');
      $('#ncrHeaderCustomer').text('—');

      // Reset Order search UI
      $('#ncrOrderSearch').val('');
      $('#ncrRef').val('');
      $('#ncrOrderResultsBody').empty().append(`
        <tr>
          <td colspan="6" class="text-muted text-center py-2">Select NCAR Type and search an order.</td>
        </tr>
      `);
    }

    function renderOrderResults(list) {
      const $body = $('#ncrOrderResultsBody');
      $body.empty();
      if (!Array.isArray(list) || list.length === 0) {
        $body.append(`
          <tr>
            <td colspan="6" class="text-muted text-center py-2">No results</td>
          </tr>
        `);
        return;
      }

      list.forEach(o => {
        const id = o?.id ?? '';
        const work = (o?.work_id ?? '').toString();
        const pn = (o?.PN ?? '').toString();
        const desc = (o?.Part_description ?? '').toString();
        const cust = (o?.costumer ?? '').toString();
        const isPri = String(o?.priority || '').toLowerCase() === 'yes';
        const dueRaw = (o?.due_date ?? '').toString();
        const due = dueRaw ? new Date(dueRaw) : null;
        const dueTxt = (due && !Number.isNaN(due.getTime()))
          ? due.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: '2-digit' })
          : '';

        const workEsc = $('<div>').text(work).html();
        const pnEsc = $('<div>').text(pn).html();
        const descEsc = $('<div>').text(desc).html();
        const custEsc = $('<div>').text(cust).html();

        const $tr = $(`
          <tr>
            <td>${workEsc}${isPri ? ' <span class="badge badge-warning ml-1">Priority</span>' : ''}</td>
            <td>${pnEsc}</td>
            <td><span class="ncr-order-desc" title="${descEsc}">${descEsc}</span></td>
            <td>${custEsc}</td>
            <td class="text-nowrap">${$('<div>').text(dueTxt).html()}</td>
            <td class="text-center">
              <button type="button" class="ncr-order-action" title="Select">
                <i class="far fa-star"></i>
              </button>
            </td>
          </tr>
        `);

        $tr.find('.ncr-order-action').on('click', function () {
          const alreadySelected = $tr.hasClass('is-selected') || ($('#ncrOrderId').val() || '').toString() === String(id);

          // Toggle off
          if (alreadySelected) {
            clearNcarModalAll();
            return;
          }

          // Select (and keep only this row visible)
          $('#ncrOrderId').val(String(id));
          $('#ncrOrderResultsBody tr').removeClass('is-selected');
          $tr.addClass('is-selected');
          $tr.find('.ncr-order-action i').removeClass('far').addClass('fas');
          $tr.siblings('tr').remove();

          $('#ncrWorkId').val(work);
          $('#ncrPn').val(pn);
          $('#ncrCustomer').val(cust);
          $('#ncrDescription').val(desc);

          if (o?.co !== undefined) $('#ncrCo').val((o.co ?? '').toString());
          if (o?.cust_po !== undefined) $('#ncrCustPo').val((o.cust_po ?? '').toString());
          if (o?.qty !== undefined) {
            const total = parseInt(o?.qty_total ?? '', 10);
            if (Number.isFinite(total) && total > 0) {
              $('#ncrQty').val(String(total));
            } else {
              const parent = parseInt(o?.qty ?? '', 10);
              const childSum = parseInt(o?.qty_children_sum ?? '', 10);
              const hasParent = Number.isFinite(parent);
              const hasChild = Number.isFinite(childSum) && childSum > 0;
              const qtyVal = (hasParent ? parent : 0) + (hasChild ? childSum : 0);
              $('#ncrQty').val(qtyVal ? String(qtyVal) : '');
            }
          }
          if (o?.wo_qty !== undefined) $('#ncrWoQty').val((o.wo_qty ?? '').toString());
          if (o?.operation !== undefined) $('#ncrOperation').val((o.operation ?? '').toString());

          $('#ncrHeaderWorkId').text(work || '—');
          $('#ncrHeaderCustomer').text(cust || '—');

          // Mantener la tabla visible y el término de búsqueda intacto
        });

        $body.append($tr);
      });
    }

    function debounce(fn, ms = 180) {
      let t;
      return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), ms);
      };
    }

    $('#ncrNcarType').off('change.ncarparts').on('change.ncarparts', function () {
      syncNcarStageOptions();
      $('#ncrStage').val('');
      applyNextNcarNumber(true);
      refreshOrderSearchResults();
    });

    function refreshOrderSearchResults() {
      const code = ($('#ncrNcarType option:selected').attr('data-code') || '').toString().toUpperCase();
      const term = ($('#ncrOrderSearch').val() || '').toString().trim();

      if (code !== 'INTERNAL' && code !== 'EXTERNAL' && code !== 'CUSTOMER') {
        renderOrderResults([]);
        return;
      }

      // If user clears the search (X in input[type=search]), reset selection + Impact
      if (term.length === 0) {
        const selected = ($('#ncrOrderId').val() || '').toString().trim();
        if (selected) {
          clearNcarModalAll();
        } else {
          $('#ncrOrderResultsBody').empty().append(`
            <tr>
              <td colspan="6" class="text-muted text-center py-2">Select NCAR Type and search an order.</td>
            </tr>
          `);
        }
        return;
      }

      if (term.length < 2) {
        const $body = $('#ncrOrderResultsBody');
        $body.empty();
        $body.append(`
          <tr>
            <td colspan="6" class="text-muted text-center py-2">Type at least 2 characters to search.</td>
          </tr>
        `);
        return;
      }

      fetchJson(`/orders/search`, { data: { term, ncar_code: code } }).then(list => {
        renderOrderResults(Array.isArray(list) ? list : []);
      });
    }

    $('#ncrOrderSearch')
      .off('input.ncarparts')
      .on('input.ncarparts', debounce(function () {
        refreshOrderSearchResults();
      }, 220));

    // If user closes the modal via "X" or backdrop, clear everything
    $('#ncrModal')
      .off('hidden.bs.modal.ncarpartsClear')
      .on('hidden.bs.modal.ncarpartsClear', function () {
        clearNcarModalAll();
        const defaultReviewer = ($('#ncrReviewer').data('default') || '').toString();
        $('#ncrReviewer').val(defaultReviewer);
      });

    $('#ncrForm').off('submit.ncarparts').on('submit.ncarparts', function (e) {
      e.preventDefault();

      const ncartypeId = ($('#ncrNcarType').val() || '').toString().trim();
      if (!ncartypeId) {
        if (window.Swal) return Swal.fire('Required', 'Select NCAR Type.', 'warning');
        alert('Select NCAR Type.');
        return;
      }

      const ncarStage = ($('#ncrStage').val() || '').toString().trim();
      const ncarDate = ($('#ncrDate').val() || '').toString().trim();
      const ncrNotes = ($('#ncrNotes').val() || '').toString().trim();

      const $saveBtn = $('#ncrSaveBtn');
      $saveBtn.prop('disabled', true);

      $.ajax({
        url: ROUTES.storeNcar,
        method: 'POST',
        dataType: 'json',
        data: {
          _token: getCsrf(),
          order_id: (($('#ncrOrderId').val() || '').toString().trim() || null),
          ncartype_id: ncartypeId || null,
          ncar_class: (function () {
            const txt = ($('#ncrNcarType option:selected').text() || '').toString().trim();
            return txt || null;
          })(),
          ref: (($('#ncrRef').val() || '').toString().trim() || null),
          stage: ncarStage || null,
          ncar_date: ncarDate || null,
          nc_description: ncrNotes || null,
          contact: (($('#ncrReviewer').val() || '').toString().trim() || null)
        }
      }).done(function (res) {
        if (!res || !res.success) {
          const msg = (res && res.message) ? res.message : 'Could not save NCAR.';
          if (window.Swal) return Swal.fire('Attention', msg, 'warning');
          alert(msg);
          return;
        }

        $('#ncrModal').modal('hide');

        const editUrl = (res.edit_url || '').toString();
        if (editUrl) {
          window.location.href = editUrl;
          return;
        }

        if (window.Swal) return Swal.fire('Saved', 'NCAR saved.', 'success');
        alert('NCAR saved.');
      }).fail(function (xhr) {
        let msg = 'Error saving NCAR.';
        try {
          if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
        } catch (e) {}
        if (window.Swal) return Swal.fire('Error', msg, 'error');
        alert(msg);
      }).always(function () {
        $saveBtn.prop('disabled', false);
      });
    });
  });
</script>

@endpush
