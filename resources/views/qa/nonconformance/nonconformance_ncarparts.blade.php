<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Non-Conformance Reports')

@section('meta')
  <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

{{--
@section('content_header')
  <div class="d-flex align-items-center justify-content-between">
    <div class="ncar-title-pill">
      <span class="ncar-title-pill-icon" aria-hidden="true">
        <i class="fas fa-clipboard-list"></i>
      </span>
      <span class="ncar-title-pill-text">Non-Conformance Reports</span>
    </div>
  </div>
@endsection
--}}


@section('content')

<div class="ncar-page">

{{-- Tabs --}}




<div class="row">
  {{-- Tabla arriba (8) --}}
  <div class="col-lg-9 order-1 order-lg-2" id="ncarTableCol">
    <div class="card mb-3">
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

              <div class="d-flex align-items-center" style="gap:.5rem">
                <button type="button" class="ncar-top-action ncar-top-secondary" id="btnManageStages" title="Manage stages">
                  <i class="fas fa-layer-group"></i><span>Stages</span>
                </button>
                <button type="button" class="ncar-top-action ncar-top-primary" id="btnCreateNcar" title="Create NCR">
                  <i class="fas fa-plus"></i><span>NCR</span>
                </button>
                <div class="dt-filter-slot" data-dt-filter-slot="ncr"></div>
                <button type="button" class="btn btn-light btn-sm ncar-wide-btn" id="btnToggleTableWide" title="Expand table">
                  <i class="fas fa-expand"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body p-1"> {{-- ✅ tabla dentro de card-body (poquito padding) --}}
        <div class="table-responsive position-relative fai-table-shell">
          <table id="ncrTable" class="table table-sm table-hover align-middle w-100 fai-dt-table">
            <thead class="table-light">
              <tr>
                <th>Code</th>
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
  </div>

  {{-- KPIs + gráficos (donde estaba la columna izquierda) --}}
  <div class="col-lg-3 order-2 order-lg-1" id="ncarSidebarCol">
    <div class="card erp-side-card mb-3">
      <div class="card-header erp-side-card-header">
        <span class="erp-side-title"><i class="fas fa-chart-line mr-2"></i>Overview</span>
      </div>
      <div class="card-body p-0">
        <div class="erp-kpi-row">
          <div class="d-flex align-items-center">
            <span class="erp-kpi-icon erp-kpi-new"><i class="fas fa-info-circle"></i></span>
            <div class="erp-kpi-label">New</div>
          </div>
          <div class="erp-kpi-value" id="kpiNew">0</div>
        </div>

        <div class="erp-kpi-row">
          <div class="d-flex align-items-center">
            <span class="erp-kpi-icon erp-kpi-qa"><i class="fas fa-user"></i></span>
            <div class="erp-kpi-label">Quality Review</div>
          </div>
          <div class="erp-kpi-value" id="kpiQA">0</div>
        </div>

        <div class="erp-kpi-row">
          <div class="d-flex align-items-center">
            <span class="erp-kpi-icon erp-kpi-eng"><i class="fas fa-wrench"></i></span>
            <div class="erp-kpi-label">Engineering Review</div>
          </div>
          <div class="erp-kpi-value" id="kpiEng">0</div>
        </div>
      </div>
    </div>

    <div class="card erp-side-card mb-3 w-100">
      <div class="card-header erp-side-card-header d-flex justify-content-between align-items-center">
        <span class="erp-side-title">Total By Cause</span>
        <span class="erp-side-metric" id="kpiTotalCause">0</span>
      </div>
      <div class="card-body">
        <canvas id="chartByCause" height="130"></canvas>
      </div>
    </div>

    <div class="card erp-side-card mb-3 w-100">
      <div class="card-header erp-side-card-header d-flex justify-content-between align-items-center">
        <span class="erp-side-title">Total</span>
        <span class="erp-side-chip">Trend</span>
      </div>
      <div class="card-body">
        <canvas id="chartTrend" height="130"></canvas>
      </div>
    </div>
  </div>
</div>



<!--  {{-- Tab: By End Schedule --}}-->

 </div>

@include('qa.faisummary.ncr_modal')

{{-- PDF Preview (uploaded PDFs) --}}
<div class="modal fade" id="ncarPdfPreviewModal" tabindex="-1" role="dialog" aria-labelledby="ncarPdfPreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header py-2 ncar-pdf-modal-header">
        <strong id="ncarPdfPreviewModalLabel">PDF Preview</strong>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0">
        <div class="ncar-pdf-preview">
          <iframe id="ncarPdfPreviewFrame" title="PDF Preview" src="about:blank"></iframe>
        </div>
      </div>
    </div>
  </div>
</div>

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

  /* Title pill (like ERP "Summary") */
  .ncar-title-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px 8px 10px;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, 0.10);
    background: rgba(241, 245, 249, 0.92);
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
  }

  .ncar-title-pill-icon {
    width: 34px;
    height: 34px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid rgba(15, 23, 42, 0.10);
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
    color: #0f172a;
  }

  .ncar-title-pill-text {
    font-weight: 900;
    font-size: 1.05rem;
    letter-spacing: 0.01em;
    color: #0f172a;
    line-height: 1.1;
    white-space: nowrap;
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

  /* Sidebar ERP cards */
  .ncar-page .erp-side-card {
    border-radius: 12px;
    border: 1px solid rgba(15, 23, 42, 0.12);
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
    overflow: hidden;
    background: #fff;
  }

  .ncar-page .erp-side-card-header {
    background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
    border-bottom: 1px solid rgba(15, 23, 42, 0.10);
    color: #0f172a;
    font-weight: 800;
    letter-spacing: .02em;
    padding: 10px 12px;
  }

  .ncar-page .erp-side-title {
    display: inline-flex;
    align-items: center;
    font-size: 0.86rem;
    text-transform: uppercase;
  }

  .ncar-page .erp-side-metric {
    font-weight: 900;
    font-size: 1.05rem;
    color: #0f172a;
  }

  .ncar-page .erp-side-chip {
    border: 1px solid rgba(15, 23, 42, 0.16);
    background: rgba(241, 245, 249, 0.95);
    color: rgba(15, 23, 42, 0.78);
    padding: 2px 8px;
    border-radius: 999px;
    font-weight: 800;
    font-size: 0.75rem;
    letter-spacing: .02em;
    text-transform: uppercase;
  }

  .ncar-page .erp-kpi-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
  }

  .ncar-page .erp-kpi-row:last-child {
    border-bottom: 0;
  }

  .ncar-page .erp-kpi-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    border: 1px solid rgba(15, 23, 42, 0.10);
    background: rgba(241, 245, 249, 0.8);
    color: #334155;
  }

  .ncar-page .erp-kpi-new { background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.25); color: #15803d; }
  .ncar-page .erp-kpi-qa { background: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.28); color: #b45309; }
  .ncar-page .erp-kpi-eng { background: rgba(124, 58, 237, 0.12); border-color: rgba(124, 58, 237, 0.28); color: #5b21b6; }

  .ncar-page .erp-kpi-label {
    font-weight: 800;
    color: rgba(15, 23, 42, 0.70);
    font-size: 0.78rem;
    letter-spacing: .02em;
    text-transform: uppercase;
    line-height: 1.1;
  }

  .ncar-page .erp-kpi-value {
    font-weight: 900;
    font-size: 1.25rem;
    color: #0f172a;
    line-height: 1;
  }

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
    line-height: 1.2;
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

  /* Mantener altura uniforme por fila: limitar a 2 líneas con ellipsis */
  .ncar-page .cell-desc,
  .ncar-page .cell-title {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 4;
    overflow: hidden;
    max-height: calc(1.2em * 4);
    min-height: calc(1.2em * 4);
  }

  .ncar-page .cell-title { max-width: 320px; }

  .ncar-page .cell-refs {
    display: block;
    white-space: pre-line;
    overflow: hidden;
    max-height: calc(1.2em * 4);
    min-height: calc(1.2em * 4);
    max-width: 320px;
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

  /* Actions: 2 filas (3 arriba / 3 abajo) */
  .ncar-page .ncar-actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 34px);
    gap: 6px;
    justify-content: center;
    width: 114px; /* 34*3 + 6*2 */
    margin: 0 auto;
  }

  /* Columna Actions: que se compacte al ancho real de botones */
  .ncar-page td.col-actions,
  .ncar-page th.col-actions {
    width: 130px !important;
    min-width: 130px !important;
    max-width: 130px !important;
    padding-left: 4px !important;
    padding-right: 4px !important;
  }

  .ncar-page .ncar-wide-btn {
    height: 34px;
    width: 38px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: 1px solid rgba(15, 23, 42, 0.18);
    background: rgba(241, 245, 249, 0.95);
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
  }

  .ncar-page .ncar-wide-btn i,
  .ncar-page .ncar-wide-btn svg {
    color: #0f172a;
    fill: currentColor;
  }

  .ncar-page .ncar-wide-btn:hover {
    filter: brightness(0.98);
  }

  /* Top actions (Stages/NCR) - ERP style */
  .ncar-page .ncar-top-action {
    height: 34px;
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-radius: 10px;
    border: 1px solid rgba(15, 23, 42, 0.18);
    background: rgba(241, 245, 249, 0.95);
    color: #0f172a;
    font-weight: 800;
    letter-spacing: .02em;
    text-transform: uppercase;
    font-size: 0.75rem;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
    white-space: nowrap;
  }

  .ncar-page .ncar-top-action i,
  .ncar-page .ncar-top-action svg {
    color: currentColor;
    fill: currentColor;
  }

  .ncar-page .ncar-top-action:hover {
    filter: brightness(0.98);
    text-decoration: none;
  }

  .ncar-page .ncar-top-action.ncar-top-primary {
    border-color: rgba(11, 94, 215, 0.35);
    background: rgba(11, 94, 215, 0.08);
    color: #0b5ed7;
  }

  .ncar-page .ncar-top-action.ncar-top-secondary {
    border-color: rgba(71, 85, 105, 0.28);
    background: rgba(148, 163, 184, 0.10);
    color: #334155;
  }

  .ncar-page .btn-erp-primary,
  .ncar-page .btn-erp-success,
  .ncar-page .btn-erp-danger,
  .ncar-page .btn-erp-warning,
  .ncar-page .btn-erp-info {
    background: #f8fafc;
    border: 1px solid #d5d8dd;
    color: #1f2937;
    box-shadow: none;
    font-weight: 700;
  }

  .ncar-page .btn-erp-primary i,
  .ncar-page .btn-erp-primary svg { color: #0b5ed7; fill: currentColor; }
  .ncar-page .btn-erp-success i,
  .ncar-page .btn-erp-success svg { color: #0f5132; fill: currentColor; }
  .ncar-page .btn-erp-danger i,
  .ncar-page .btn-erp-danger svg { color: #b91c1c; fill: currentColor; }
  .ncar-page .btn-erp-warning i,
  .ncar-page .btn-erp-warning svg { color: #f59e0b; fill: currentColor; }
  .ncar-page .btn-erp-info i,
  .ncar-page .btn-erp-info svg { color: #0ea5e9; fill: currentColor; }

  .ncar-page .erp-table-btn.is-uploaded {
    background: rgba(22, 163, 74, 0.12);
    border-color: rgba(22, 163, 74, 0.40);
  }

  .ncar-page .erp-table-btn.is-uploaded i,
  .ncar-page .erp-table-btn.is-uploaded svg {
    color: #16a34a !important;
    fill: currentColor !important;
  }

  .ncar-page .erp-table-btn.is-uploaded:hover,
  .ncar-page .erp-table-btn.is-uploaded:focus {
    background: rgba(22, 163, 74, 0.12) !important;
    border-color: rgba(22, 163, 74, 0.40) !important;
  }

  .ncar-page .erp-table-btn.is-uploaded:hover i,
  .ncar-page .erp-table-btn.is-uploaded:focus i,
  .ncar-page .erp-table-btn.is-uploaded:hover svg,
  .ncar-page .erp-table-btn.is-uploaded:focus svg {
    color: #16a34a !important;
    fill: currentColor !important;
  }

  /* PDF Preview modal */
  #ncarPdfPreviewModal .modal-content {
    border-radius: 12px;
    border: 1px solid rgba(15, 23, 42, 0.14);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
    overflow: hidden;
  }

  #ncarPdfPreviewModal .modal-dialog {
    max-width: 1120px;
    width: calc(100% - 1rem);
  }

  #ncarPdfPreviewModal .ncar-pdf-modal-header {
    background: #fff;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    padding: 14px 16px;
  }

  #ncarPdfPreviewModal .ncar-pdf-preview {
    height: calc(100vh - 220px);
    min-height: 520px;
    background: #f8fafc;
  }

  #ncarPdfPreviewModal iframe#ncarPdfPreviewFrame {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
    background: #fff;
  }

  .ncar-page .btn-erp-primary:hover,
  .ncar-page .btn-erp-success:hover,
  .ncar-page .btn-erp-danger:hover,
  .ncar-page .btn-erp-warning:hover,
  .ncar-page .btn-erp-info:hover {
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
    background:
      linear-gradient(180deg, rgba(148, 163, 184, 0.18) 0%, rgba(148, 163, 184, 0.08) 100%),
      repeating-linear-gradient(135deg, rgba(255,255,255,0.35) 0px, rgba(255,255,255,0.35) 6px, rgba(255,255,255,0.16) 6px, rgba(255,255,255,0.16) 12px) !important;
    border-bottom: 1px solid rgba(71, 85, 105, 0.18) !important;
    padding: 14px 16px !important;
    position: relative;
  }

  #ncrModal .erp-ncr-modal-header::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, rgba(71, 85, 105, 0.95) 0%, rgba(148, 163, 184, 0.95) 100%);
  }

  #ncrModal .erp-ncr-title-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(71, 85, 105, 0.22);
    background: rgba(148, 163, 184, 0.14);
    color: #334155;
  }

  #ncrModal .erp-ncr-title-icon i { font-size: 16px; }
  #ncrModal .erp-ncr-chip { display: none !important; }

  #ncrModal .modal-title {
    font-weight: 900;
    letter-spacing: .01em;
    color: #0f172a;
  }

  #ncrModal .close {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid rgba(15, 23, 42, 0.12);
    background: rgba(241, 245, 249, 0.95);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    opacity: 1;
    color: rgba(15, 23, 42, 0.75);
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
    line-height: 1;
    padding: 0;
  }

  #ncrModal .close:hover {
    filter: brightness(0.98);
    color: rgba(15, 23, 42, 0.85);
  }

  #ncrModal .close:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
  }

  #ncrModal .erp-ncr-subtitle {
    display: block !important;
    margin-top: 2px;
    font-size: 0.82rem;
    color: rgba(15, 23, 42, 0.65);
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

  #ncrModal .erp-ncr-btn {
    height: 40px;
    padding: 0 14px;
    border-radius: 10px;
    font-weight: 800;
    letter-spacing: .02em;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
  }

  #ncrModal .erp-ncr-btn.btn-light {
    background: rgba(241, 245, 249, 0.95);
    border: 1px solid rgba(15, 23, 42, 0.16);
    color: #0f172a;
  }

  #ncrModal .erp-ncr-btn.btn-primary {
    background: #0b5ed7;
    border: 1px solid rgba(11, 94, 215, 0.65);
  }

  #ncrModal .erp-ncr-btn:hover {
    filter: brightness(0.98);
  }

  #ncrModal .erp-ncr-btn:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
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
    background: transparent !important;
    color: rgba(15, 23, 42, 0.70) !important;
  }

  #ncrModal .ncr-order-searchbar input.erp-ncr-control {
    border-left: 0 !important;
    border-radius: 0 10px 10px 0 !important;
    background: transparent !important;
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
      { targets: [8], orderable: false, searchable: false, className: 'text-nowrap text-center col-actions', width: '130px' }
    ],
    dom: "frt<'erp-dt-footer d-flex align-items-center justify-content-between flex-wrap'<'dataTables_info'i><'dataTables_paginate'p>>",
    columns: [
      {data:'number'},
      {data:'description', render:(d)=>{
        const v = (d ?? '').toString();
        const esc = escapeHtml(v);
        return `<div class="cell-desc" title="${esc}">${esc}</div>`;
      }},
      {data:'title', render:(d)=>{
        const v = (d ?? '').toString();
        const esc = escapeHtml(v);
        return `<div class="cell-title" title="${esc}">${esc}</div>`;
      }},
      {data:'created', render:d=> d? new Date(d).toLocaleDateString():''},
      {data:'customer'},
      {data:'ref_numbers', render:(d)=>{
        const v = (d ?? '').toString().trim();
        if (!v) return '';
        const parts = v
          .split('|')
          .map(s => s.trim())
          .filter(Boolean);
        const multi = parts.join('\n');
        const esc = escapeHtml(multi);
        return `<div class="cell-refs" title="${esc}">${esc}</div>`;
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
        const id = row?.id || '';
        const hasPdfUp = (row?.has_pdf_upload === true) || !!(row?.pdf_upload_path || '').toString().trim();
        const hasEmailUp = (row?.has_email_upload === true) || !!(row?.email_upload_path || '').toString().trim();
        const viewPdf1Url = `${UPLOAD_BASE}/${id}/uploaded-pdf/1`;
        const viewPdf2Url = `${UPLOAD_BASE}/${id}/uploaded-pdf/2`;
        return `
          <div class="ncar-actions-grid" role="group" aria-label="Actions">
            <a class="btn btn-sm btn-erp-warning erp-table-btn" href="${editUrl}" title="Edit">
              <i class="fas fa-edit"></i>
            </a>
            <a class="btn btn-sm btn-erp-primary erp-table-btn" href="${pdfUrl}" target="_blank" rel="noopener" title="PDF">
              <i class="fas fa-file-pdf"></i>
            </a>
            <button type="button"
              class="btn btn-sm btn-erp-info erp-table-btn btn-ncar-upload ${hasPdfUp ? 'is-uploaded' : ''}"
              data-id="${id}" data-slot="1" data-label="PDF" data-has="${hasPdfUp ? 1 : 0}" data-view-url="${viewPdf1Url}"
              title="${hasPdfUp ? 'View PDF (Shift+click to replace)' : 'Upload PDF'}">
              <i class="fas fa-upload"></i>
            </button>
            <button type="button"
              class="btn btn-sm btn-erp-info erp-table-btn btn-ncar-upload ${hasEmailUp ? 'is-uploaded' : ''}"
              data-id="${id}" data-slot="2" data-label="Email" data-has="${hasEmailUp ? 1 : 0}" data-view-url="${viewPdf2Url}"
              title="${hasEmailUp ? 'View Email PDF (Shift+click to replace)' : 'Upload Email'}">
              <i class="fas fa-envelope"></i>
            </button>
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
  const UPLOAD_BASE = @json(url('/QA/NonConformace/ncar'));

  // Expand/collapse table to full width (hide sidebar)
  const $page = $('.ncar-page');
  const $tableCol = $('#ncarTableCol');
  const $sidebarCol = $('#ncarSidebarCol');
  const $wideBtn = $('#btnToggleTableWide');

  const setTableWide = function (wide) {
    const isWide = !!wide;
    $page.toggleClass('is-table-wide', isWide);

    if (isWide) {
      $sidebarCol.addClass('d-none');
      $tableCol.removeClass('col-lg-9').addClass('col-lg-12');
      $wideBtn.attr('title', 'Collapse table').find('i').removeClass('fa-expand').addClass('fa-compress');
    } else {
      $sidebarCol.removeClass('d-none');
      $tableCol.removeClass('col-lg-12').addClass('col-lg-9');
      $wideBtn.attr('title', 'Expand table').find('i').removeClass('fa-compress').addClass('fa-expand');
    }

    try { localStorage.setItem('ncarTableWide', isWide ? '1' : '0'); } catch (e) {}
    setTimeout(() => {
      try { dt.columns.adjust(); } catch (e) {}
      try { dt.responsive && dt.responsive.recalc && dt.responsive.recalc(); } catch (e) {}
    }, 80);
  };

  $wideBtn.on('click', function () {
    setTableWide(!$page.hasClass('is-table-wide'));
  });

  try { setTableWide(localStorage.getItem('ncarTableWide') === '1'); } catch (e) {}

  const $pdfUpload = $('<input type="file" accept=\"application/pdf\" style=\"position:fixed; left:-9999px; width:1px; height:1px;\" />');
  $(document.body).append($pdfUpload);
  let pendingUpload = { id: null, slot: null, label: '', $btn: null };

  $('#ncrTable').on('click', '.btn-ncar-upload', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const id = ($(this).data('id') || '').toString();
    const slot = ($(this).data('slot') || '').toString();
    const label = ($(this).data('label') || '').toString();
    const has = String($(this).data('has') || '') === '1';
    const viewUrl = ($(this).data('viewUrl') || '').toString();
    if (!id || !slot) return;

    // If already uploaded: click = view. Shift+click = replace (upload again).
    if (has && !e.shiftKey && viewUrl) {
      const what = (label || (slot === '2' ? 'Email' : 'PDF')).toString();
      $('#ncarPdfPreviewModalLabel').text(`${what} Preview`);
      $('#ncarPdfPreviewFrame').attr('src', viewUrl);
      $('#ncarPdfPreviewModal').modal('show');
      return;
    }

    pendingUpload = { id, slot, label, $btn };
    $pdfUpload.val('');
    $pdfUpload.trigger('click');
  });

  $('#ncarPdfPreviewModal').on('hidden.bs.modal', function () {
    $('#ncarPdfPreviewFrame').attr('src', 'about:blank');
  });

  $pdfUpload.on('change', function () {
    const file = this.files && this.files[0] ? this.files[0] : null;
    if (!file || !pendingUpload.id || !pendingUpload.slot) return;

    const url = `${UPLOAD_BASE}/${pendingUpload.id}/upload-pdf/${pendingUpload.slot}`;
    const fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('pdf', file);

    $.ajax({
      url,
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .done((res) => {
        if (!res || res.success !== true) {
          const msg = (res && res.message) ? res.message : 'Could not upload PDF.';
          if (window.Swal) return Swal.fire('Error', msg, 'error');
          alert(msg);
          return;
        }

        // Update button style immediately and refresh table data (keeps paging).
        try {
          if (pendingUpload.$btn && pendingUpload.$btn.length) {
            const what = (pendingUpload.label || (pendingUpload.slot === '2' ? 'Email' : 'PDF')).toString();
            pendingUpload.$btn
              .addClass('is-uploaded')
              .attr('data-has', '1')
              .data('has', 1)
              .attr('title', `View ${what} (Shift+click to replace)`);
          }
        } catch (e) {}
        try { dt.ajax.reload(null, false); } catch (e) {}

        const what = (pendingUpload.label || (pendingUpload.slot === '2' ? 'Email' : 'PDF')).toString();
        if (window.Swal) return Swal.fire('Uploaded', `${what} uploaded.`, 'success');
        alert(`${what} uploaded.`);
      })
      .fail((xhr) => {
        const msg = xhr?.responseJSON?.message || 'Could not upload PDF.';
        if (window.Swal) return Swal.fire('Error', msg, 'error');
        alert(msg);
      })
      .always(() => {
        pendingUpload = { id: null, slot: null, label: '', $btn: null };
      });
  });

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
        $('#ncrPostImpactFields').addClass('d-none');
        $('#ncrImpactBox').addClass('d-none');

        // Order search box stays hidden until Stage is selected.
        $('#ncrOrderSearchBox').addClass('d-none');
        $('#ncrOrderId').val('');
        $('#ncrOrderSearch').val('');
        $('#ncrOrderResultsBody').empty().append(`
          <tr>
            <td colspan="6" class="text-muted text-center py-2">Select Stage to search an order.</td>
          </tr>
        `);

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
      const stageVal = ($('#ncrStage').val() || '').toString().trim();
      const prompt = stageVal ? 'Search an order.' : 'Select Stage to search an order.';

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
      $('#ncrImpactBox').addClass('d-none');
      setPostImpactVisible(false);
      $('#ncrOrderResultsBody').empty().append(`
        <tr>
          <td colspan="6" class="text-muted text-center py-2">${prompt}</td>
        </tr>
      `);
    }

    function setOrderSearchVisible(show) {
      const visible = !!show;
      $('#ncrOrderSearchBox').toggleClass('d-none', !visible);
      if (!visible) {
        $('#ncrOrderSearch').val('');
        $('#ncrOrderResultsBody').empty().append(`
          <tr>
            <td colspan="6" class="text-muted text-center py-2">Select Stage to search an order.</td>
          </tr>
        `);
      }
    }

    function setPostImpactVisible(show) {
      const visible = !!show;
      $('#ncrPostImpactFields').toggleClass('d-none', !visible);
      if (!visible) {
        $('#ncrNotes').val('');
      }
    }

    function clearOrderSelectionAndImpact() {
      $('#ncrOrderId').val('');
      $('#ncrWorkId, #ncrCo, #ncrCustPo, #ncrPn, #ncrCustomer, #ncrOperation, #ncrQty, #ncrWoQty').val('');
      $('#ncrDescription').val('');
      $('#ncrHeaderWorkId').text('—');
      $('#ncrHeaderCustomer').text('—');
      $('#ncrImpactBox').addClass('d-none');
      setPostImpactVisible(false);
      $('#ncrOrderResultsBody').empty().append(`
        <tr>
          <td colspan="6" class="text-muted text-center py-2">Select Stage to search an order.</td>
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

          $('#ncrImpactBox').removeClass('d-none');
          setPostImpactVisible(true);

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
      setOrderSearchVisible(false);
      clearOrderSelectionAndImpact();
    });

    $('#ncrStage')
      .off('change.ncarpartsStage')
      .on('change.ncarpartsStage', function () {
        const stageVal = ($('#ncrStage').val() || '').toString().trim();
        const show = stageVal.length > 0;
        setOrderSearchVisible(show);
        setPostImpactVisible(false);

        if (!show) {
          clearOrderSelectionAndImpact();
          return;
        }

        // Stage selected: show prompt until user searches.
        $('#ncrOrderResultsBody').empty().append(`
          <tr>
            <td colspan="6" class="text-muted text-center py-2">Search an order.</td>
          </tr>
        `);
      });

    function refreshOrderSearchResults() {
      const code = ($('#ncrNcarType option:selected').attr('data-code') || '').toString().toUpperCase();
      const term = ($('#ncrOrderSearch').val() || '').toString().trim();
      const stageVal = ($('#ncrStage').val() || '').toString().trim();

      if (stageVal.length === 0) {
        setOrderSearchVisible(false);
        return;
      }

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
              <td colspan="6" class="text-muted text-center py-2">Search an order.</td>
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
        setOrderSearchVisible(false);
        setPostImpactVisible(false);
        $('#ncrImpactBox').addClass('d-none');
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
      if (!ncarStage) {
        if (window.Swal) return Swal.fire('Required', 'Select Stage.', 'warning');
        alert('Select Stage.');
        return;
      }

      const ncarDate = ($('#ncrDate').val() || '').toString().trim();
      const ncrNotes = ($('#ncrNotes').val() || '').toString().trim();
      if (!ncrNotes) {
        if (window.Swal) return Swal.fire('Required', 'Notes are required.', 'warning');
        alert('Notes are required.');
        return;
      }

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
          stage: ncarStage,
          ncar_date: ncarDate || null,
          ncar_customer: (($('#ncrCustomer').val() || '').toString().trim() || null),
          nc_description: ncrNotes,
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
