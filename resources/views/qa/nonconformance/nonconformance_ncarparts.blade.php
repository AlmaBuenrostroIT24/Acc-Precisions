<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary')

@section('content_header')
  <div class="d-flex align-items-center justify-content-between">
    <h1 class="mb-0">
      <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
      Non-Conformance Reports
    </h1>
    <div>
      <a href="#" class="btn btn-dark btn-sm">
        <i class="fas fa-plus mr-1"></i> NCR
      </a>
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
            <th>Parts</th>
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
@endsection



@section('css')
<!-- CSS complementario (puedes ponerlo en tu .css) -->
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
    #ncrTable td:nth-child(2) { min-width: 240px; } /* Title */
    #ncrTable td:nth-child(5) { min-width: 260px; } /* Reference Numbers */
  }
</style>
@endsection


@push('js')

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
      { targets: [9], orderable: false, searchable: false, className: 'text-nowrap text-center', width: '120px' }
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
      {data:'parts'},
      {data:'status', render:s=>{
        const closed = String(s).toLowerCase()==='closed';
        return `<span class="badge badge-status ${closed?'badge-closed':'badge-open'}">${s}</span>`;
      }},
      {data:null, render:(d, t, row)=>{
        const editUrl = row?.edit_url || '#';
        const pdfUrl = row?.pdf_url || '#';
        return `
          <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
            <a class="btn btn-outline-primary" href="${editUrl}" title="Edit">
              <i class="fas fa-edit"></i>
            </a>
            <a class="btn btn-outline-danger" href="${pdfUrl}" target="_blank" rel="noopener" title="PDF">
              <i class="fas fa-file-pdf"></i>
            </a>
          </div>
        `;
      }}
    ]
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
    fillSelect($('#fltStatus'), dt.column(8, { search: 'none' }).data().toArray());
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
    dt.column(8).search(v ? ('^' + escapeRegex(v) + '$') : '', true, false).draw();
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

@endpush
