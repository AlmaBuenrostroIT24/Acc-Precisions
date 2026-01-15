<!-- resources/views/qa/faisummary/faisummary_partsrevision.blade.php -->
@extends('adminlte::page')

@section('title', 'QA/Parts Revision')


@section('content')

<div class="row row-cols-1 row-cols-md-2 g-3 mt-0 pt-0">
  <!-- Pending -->
  <div class="col">
    <div class="card  shadow-sm rounded-3 border-left-warning">
      <div class="card-body fai-compact-body">
        <div class="card-title-mini fai-card-title">
          <div class="d-flex align-items-center">
            <span class="fai-title-icon bg-warning text-dark">
              <i class="fas fa-hourglass-half"></i>
            </span>
            <div class="ml-2">
              <div class="fai-title-text">Pending</div>
              <small class="text-muted fai-title-sub">Orders waiting to start</small>
            </div>
          </div>
          {{-- 2025-12-17: tools a la altura del título (contador + search) --}}
          <div class="fai-dt-tools d-flex align-items-center ml-auto">
            <span class="badge badge-pill badge-light border mr-2 d-none" id="badgePending"></span>
            <div class="dt-filter-slot" data-dt-filter-slot="empty"></div>
          </div>
        </div>
        <div class="table-responsive">
          <table id="ordersTableEmpty" class="table table-sm table-hover align-middle w-100 fai-dt-table">
            <thead class="table-light">
              <tr>
                <th>PART/DESCRIPCIÓN</th>
                <th>JOB</th>
                <th>DUE DATE</th>
                <th class="actions-col">ACTIONS</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- In Process -->
  <div class="col">
    <div class="card shadow-sm rounded-3 border-left-success">
      <div class="card-body fai-compact-body">
        <div class="card-title-mini fai-card-title">
          <div class="d-flex align-items-center">
            <span class="fai-title-icon bg-success text-white" id="processPdfBtn" role="button" title="Exporting summary in process" style="cursor: pointer;">
              <i class="fas fa-cogs"></i>
            </span>
            <div class="ml-2">
              <div class="fai-title-text">In Process</div>
              <small class="text-muted fai-title-sub">FAI / IPI progress</small>
            </div>
          </div>
          {{-- 2025-12-17: tools a la altura del título (contador + search) --}}
          <div class="fai-dt-tools d-flex align-items-center ml-auto">
            <span class="badge badge-pill badge-light border mr-2 d-none" id="badgeProcess"></span>
            <div class="dt-filter-slot" data-dt-filter-slot="process"></div>
          </div>
        </div>
        <div class="table-responsive">
          <table id="ordersTableProcess" class="table table-sm table-hover align-middle w-100 fai-dt-table">
            <thead class="table-light">
              <tr>
                <th>PART/DESCRIPCIÓN</th>
                <th>JOB</th>
                <th>(WIP) FAI + IPI</th>
                <th class="actions-col">ACTIONS</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@include('qa.faisummary.faisummary_modal')

<!--  {{-- Tab: By End Schedule --}}-->
@endsection


@section('css')
<!-- CSS complementario (puedes ponerlo en tu .css) -->
<style>
  /* Bordes laterales neutrales (sin franjas de color) */
  .card.border-left-success,
  .card.border-left-warning {
    border-left: 2px solid rgba(0, 0, 0, 0.08);
  }

  .table thead th {
    white-space: normal; /* permitir salto y evitar overflow */
  }

  .card-title-mini {
    font-size: .95rem;
    font-weight: 700;
    margin-bottom: .45rem;
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-wrap: wrap;
    padding-bottom: .28rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  }

  /* 2025-12-17: encabezado moderno para Pending/In Process */
  .fai-card-title {
    justify-content: space-between;
    margin-bottom: 0.25rem;
    padding-bottom: 0.22rem;
  }

  .fai-title-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.10);
  }

  .fai-title-icon i {
    font-size: 1.05rem;
    line-height: 1;
  }

  .fai-title-text {
    font-size: 0.95rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    line-height: 1.1;
  }

  .fai-title-sub {
    font-size: 0.78rem;
    line-height: 1.1;
  }

  /* 2025-12-17: hacer el card-body más compacto en Pending/Process */
  .fai-compact-body {
    padding: 0.6rem 0.6rem 0.2rem;
  }

  .fai-compact-body .card-title-mini {
    margin-bottom: 0.35rem;
  }

  .actions-col {
    width: 1%;
    white-space: nowrap;
  }

  .card-title-mini .badge {
    border-radius: 999px;
    padding: 0.35rem 0.55rem;
    font-weight: 700;
  }

  /* 2025-12-17: si el badge está vacío, no mostrarlo */
  .badge:empty {
    display: none !important;
  }

  /* 2025-12-17: Search alineado con el título del card */
  .fai-dt-tools .dataTables_filter {
    margin: 0 !important;
  }

  .fai-dt-tools .dataTables_filter label {
    margin: 0 !important;
  }

  .fai-dt-tools .dataTables_filter input {
    width: 180px;
  }

  /* 2025-12-17: evitar que el título se “apachurre” cuando el search ocupa espacio */
  .card-title-mini > span {
    white-space: nowrap;
  }

  .fai-dt-tools {
    flex: 1 1 auto;
    justify-content: flex-end;
    gap: 0.4rem;
  }

  .dt-filter-slot {
    display: flex;
    align-items: center;
  }

  @media (max-width: 575.98px) {
    .fai-dt-tools {
      width: 100%;
      justify-content: flex-start;
    }

    .fai-dt-tools .dataTables_filter input {
      width: 100%;
      max-width: 220px;
    }
  }

  /* 2025-12-17: mejoras visuales de tablas (AdminLTE/BS4 + DataTables) */
  .table-responsive {
    /* 2025-12-17: quitar efecto "cuadro" alrededor de la tabla */
    border: 0;
    border-radius: 0;
    overflow-x: auto;
    overflow-y: hidden; /* evita scroll vertical interno (doble barra) */
    background: transparent;
    box-shadow: none;
  }

  .table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
  }

  /* 2025-12-17: layout auto para evitar overflow horizontal */
  .fai-dt-table {
    table-layout: auto;
  }

  /* 2025-12-17: truncado elegante en celdas largas */
  .fai-cell-ellipsis {
    white-space: normal;
    overflow: visible;
    text-overflow: unset;
    word-break: break-word;
  }

  /* DUE DATE más “tabular” y alineado */
  #ordersTableEmpty td:nth-child(3),
  #ordersTableEmpty th:nth-child(3) {
    text-align: right;
    font-variant-numeric: tabular-nums;
  }

  #ordersTableProcess td:nth-child(2),
  #ordersTableProcess th:nth-child(2) {
    font-variant-numeric: tabular-nums;
  }

  /* Columna ACTIONS centrada */
  #ordersTableEmpty td:last-child,
  #ordersTableProcess td:last-child {
    text-align: center;
  }

  /* 2025-12-17: líneas suaves (sin borde pesado) */
  .table thead th,
  .table tbody td {
    border-top: 0;
    border-left: 0;
    border-right: 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  }

  .table thead th {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #334155;
    padding: 0.6rem 0.75rem;
    border-bottom: 1px solid rgba(15, 23, 42, 0.10);
    vertical-align: middle;
  }

  .table tbody td {
    font-size: 0.85rem;
    color: #0f172a;
  }

  /* Sticky header dentro del contenedor scroll */
  .table-responsive thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f8fafc;
    box-shadow: inset 0 -1px 0 rgba(15, 23, 42, 0.08);
  }

  /* Encabezados por tipo (Pending / In Process) */
  #ordersTableEmpty thead th {
    background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
    border-bottom-color: rgba(15, 23, 42, 0.12);
    box-shadow: inset 0 -2px 0 rgba(15, 23, 42, 0.06);
  }

  #ordersTableProcess thead th {
    background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
    border-bottom-color: rgba(15, 23, 42, 0.12);
    box-shadow: inset 0 -2px 0 rgba(15, 23, 42, 0.06);
  }

  /* 2025-12-17: encabezado estilo ERP (más contraste y sombra inferior) */
  .fai-dt-table thead th {
    font-weight: 800;
    letter-spacing: 0.02em;
    color: #1f2937;
    font-size: 0.86rem;
    padding: 0.48rem 0.68rem;
    background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
    border-bottom-color: rgba(15, 23, 42, 0.12);
    box-shadow: inset 0 -2px 0 rgba(15, 23, 42, 0.06);
    text-transform: uppercase;
  }

  /* Ancho mínimo para la columna de progreso en In Process */
  #ordersTableProcess thead th:nth-child(3),
  #ordersTableProcess tbody td:nth-child(3) {
    width: 18%;
    min-width: 140px;
    text-align: center;
  }

  /* 2025-12-17: bordes redondeados en el encabezado */
  #ordersTableEmpty thead th:first-child,
  #ordersTableProcess thead th:first-child {
    border-top-left-radius: 10px;
  }

  #ordersTableEmpty thead th:last-child,
  #ordersTableProcess thead th:last-child {
    border-top-right-radius: 10px;
  }

  /* 2025-12-17: hover elegante */
  .table tbody tr:hover {
    background: rgba(13, 110, 253, 0.04);
  }

  /* Hover sin acento lateral de color */
  .fai-dt-table tbody tr {
    transition: background-color .12s ease;
  }

  .fai-dt-table tbody tr:hover {
    background: rgba(13, 110, 253, 0.05);
  }

  .fai-dt-table tbody td {
    padding: 0.42rem 0.6rem;
  }

  /* Centrar contenido y alinear verticalmente */
  .fai-dt-table thead th,
  .fai-dt-table tbody td {
    text-align: center;
    vertical-align: middle;
  }

  /* Alinear a la izquierda columnas de PART/DESCRIPCIÓN */
  #ordersTableEmpty thead th:first-child,
  #ordersTableEmpty tbody td:first-child,
  #ordersTableProcess thead th:first-child,
  #ordersTableProcess tbody td:first-child {
    text-align: left;
  }

  /* Tabla: ancho completo sin forzar overflow */
  .table {
    width: 100% !important;
    min-width: 0;
  }

  /* Compactar el footer de DataTables para evitar huecos */
  .dataTables_wrapper .row:last-child {
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
  }
  .dataTables_wrapper .dataTables_info {
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    margin-top: 0 !important;
  }
  .dataTables_wrapper .dataTables_paginate {
    margin-top: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
  }

  /* 2025-12-17: botones en ACTIONS */
  .table .btn {
    border-radius: 0.5rem;
    box-shadow: 0 1px 1px rgba(16, 24, 40, 0.06);
    transition: transform .08s ease, box-shadow .12s ease, filter .12s ease;
  }

  .table .btn.btn-sm {
    padding: 0.2rem 0.45rem;
  }

  /* 2025-12-17: icon-buttons cuadrados (más pro y consistentes) */
  .table .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(16, 24, 40, 0.10);
    filter: brightness(1.02);
  }

  .table .btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(16, 24, 40, 0.10);
  }

  .table .btn:focus {
    outline: none;
    box-shadow: 0 0 0 .2rem rgba(13, 110, 253, 0.18), 0 2px 10px rgba(16, 24, 40, 0.10);
  }

  /* 2025-12-17: iconos centrados dentro del botón */
  .table .btn i,
  .table .btn .fas,
  .table .btn .far {
    display: inline-block;
    line-height: 1;
    vertical-align: middle;
  }

  /* 2025-12-17: íconos de acciones un poco más grandes */
  .table .btn i,
  .table .btn .fas,
  .table .btn .far {
    font-size: 0.95rem;
  }

/* Controles DataTables */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
  font-size: 0.85rem;
}

/* Fondo gris suave en toda la vista */
body .content-wrapper,
body .content {
  background: #f5f7fa !important;
}

  .dataTables_wrapper .dataTables_filter input,
  .dataTables_wrapper .dataTables_length select {
    border-radius: 0.5rem !important;
    border: 1px solid rgba(0, 0, 0, 0.12) !important;
    padding: 0.25rem 0.5rem !important;
    height: auto !important;
    background: #fff;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 0.55rem !important;
  }

  /* 2025-12-17: paginado más llamativo / moderno */
  .dataTables_wrapper .dataTables_paginate {
    /* 2025-12-17: footer (info + paginate) más compacto */
    margin-top: 0.1rem !important;
    padding-top: 0.1rem !important;
    border-top: 0;
  }

  /* 2025-12-17: compactar el contenedor UL de paginación (Bootstrap) */
  .dataTables_wrapper .dataTables_paginate .pagination {
    margin: 0 !important;
  }

  /* Nota: con integración Bootstrap4, el padding real vive en .page-link */
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    border: 1px solid rgba(15, 23, 42, 0.18) !important;
    background: rgba(241, 245, 249, 0.95) !important;
    color: #0f172a !important;
    margin: 0 0.12rem !important;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
    transition: background-color .12s ease, transform .08s ease, box-shadow .12s ease;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button .page-link {
    /* 2025-12-17: botones de paginación un poco más grandes */
    padding: 0.18rem 0.60rem !important;
    font-size: 0.94rem !important;
    line-height: 1.12 !important;
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

  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #94a3b8 !important;
    border-color: #94a3b8 !important;
    color: #fff !important;
    font-weight: 700;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button.current .page-link {
    color: #fff !important;
  }

  /* 2025-12-17: hacer más pequeño el texto de "Showing X to Y..." */
  .dataTables_wrapper .dataTables_info {
    padding: 0 !important;
    margin: 0 !important;
    /* 2025-12-17: footer más pequeño (texto info) */
    font-size: 0.82rem !important;
    line-height: 1.2 !important;
    color: rgba(15, 23, 42, 0.80);
  }

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
  opacity: 0.5;
  transform: none;
  box-shadow: none;
  cursor: default !important;
}

/* Reducir padding inferior de la página para evitar franjas grises */
.content-wrapper,
.content {
  padding-bottom: 0 !important;
  background: #f5f7fa !important;
  min-height: 0 !important;
  height: auto !important;
  overflow: visible;
}

/* (se removieron overrides de content-wrapper para evitar saltos de fondo) */

.fai-compact-body {
  /* ya se redujo padding; evitar agregar espacio extra abajo */
  margin-bottom: 0;
}

/* Ajuste: encabezados con texto negro definido */
#ordersTableEmpty thead th,
#ordersTableProcess thead th,
.fai-dt-table thead th,
.table thead th {
  color: #0b0b0b !important;
}

</style>
@endsection


@push('js')

<script>
  (() => {
    // ===== Rutas =====
    const ROUTES = {
      partsData: document.querySelector('meta[name="route-parts-data"]')?.content || '/qa/partsrevision/data',
      samplingPlan: (lot, type = 'Normal') => `/sampling-plan?lot_size=${lot}&sampling_type=${encodeURIComponent(type)}`,
      faibyOrder: (id) => `/qa/faisummary/by-order/${id}`, // GET
      validateOps: (id, ops) => `/orders-schedule/${id}/validate-ops?ops=${encodeURIComponent(ops)}`,
      updateOps: (id) => `/orders-schedule/${id}/update-operation`, // POST
      statusInspection: (id) => `/orders-schedule/${id}/status-inspection`, // PUT
      storeSingle: `/qa/faisummary/store-single`, // POST
      deleteRow: (id) => `/qa/faisummary/delete/${id}`, // DELETE
      stationsByOrder: (id) => `/stations/by-order/${id}`, // GET
      operatorsByOrder: (id) => `/operators/by-order/${id}` // GET
    };

    const COLLATOR = new Intl.Collator('es', {
      sensitivity: 'base',
      numeric: true
    });

    const getCsrf = () =>
      $('input[name="_token"]').val() ||
      $('meta[name="csrf-token"]').attr('content') ||
      '';

    const swalOk = (title = '¡Saved!', text = 'Operation performed') =>
      Swal.fire({
        icon: 'success',
        title,
        text,
        timer: 1300,
        showConfirmButton: false
      });

    const swalError = (title = 'Error', text = 'Ocurrió un error') =>
      Swal.fire({
        icon: 'error',
        title,
        text
      });

    const swalWarn = (title = 'Attention', text = 'Check the fields') =>
      Swal.fire({
        icon: 'warning',
        title,
        text
      });

    const debounce = (fn, ms = 150) => {
      let t;
      return (...a) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...a), ms);
      };
    };

    // ===== AJAX robusto (no truena UI en 500/404) =====
    function fetchJson(url, opts = {}) {
      return $.ajax(Object.assign({
        url,
        method: 'GET',
        dataType: 'json',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 12000
      }, opts)).catch((err) => {
        console.warn('fetchJson failed:', url, err?.status, err?.responseText);
        return null;
      });
    }

    /* FIX: helper seguro para recargar DataTables solo si es AJAX */
    function reloadIfAjax($t) {
      if ($.fn.DataTable.isDataTable($t)) {
        const api = $t.DataTable();
        const hasAjax = !!api.settings()[0].ajax;
        if (hasAjax && api.ajax) api.ajax.reload(null, false);
      }
    }

    // ================== Estado ==================
    const ctx = {
      dtEmpty: null,
      dtProcess: null,
      tableSamplingCache: new Map(), // orderId -> sample_qty
      modal: {
        $rowsContainer: null,
        $samplingResult: null,
        $operationInput: null,
        $reportPre: null,
        $reportBox: null,
        $woqty: null
      },
      faiDoneOps: new Set(),
      ipiCountMap: new Map()
    };

    // ================== DataTables ==================
    const TEXT = $.fn.dataTable.render.text();
    const COLUMNS = {
      empty: [{
          data: 'part',
          render: TEXT
        },
        {
          data: 'work_id',
          render: TEXT
        },
        {
          data: 'due_date',
          // 2025-12-17: ordenar por YYYY-MM-DD desde el backend (due_date_sort)
          render: function (data, type, row) {
            if (type === 'sort' || type === 'type') return row?.due_date_sort || data || '';
            return data || '';
          }
        },
        {
          data: 'actions',
          orderable: false,
          searchable: false
        }
      ],
      process: [{
          data: 'part',
          render: TEXT
        },
        {
          data: 'work_id',
          render: TEXT
        },
        {
          data: 'progress',
          orderable: false,
          searchable: false
        },
        {
          data: 'actions',
          orderable: false,
          searchable: false
        }
      ]
    };

    function makeDT(bucket, badgeSelector) {
      return $('#ordersTable' + (bucket === 'empty' ? 'Empty' : 'Process')).DataTable({
        responsive: true,
        deferRender: true,
        pageLength: 17,
        // 2025-12-17: ocultar "Show entries" (selector de longitud) para un look más limpio
        lengthChange: false,
        // 2025-12-17: search sin label y placeholder elegante
        language: {
          search: '',
          searchPlaceholder: 'Search…'
        },
        // 2025-12-17: ordenar por due_date en Pending (bucket=empty), mantener JOB en Process
        order: bucket === 'empty' ? [[2, 'asc']] : [[1, 'desc']],
        rowId: 'id',
        // 2025-12-17: truncar Part/Descripción y dejar tooltip con el texto completo
        createdRow: function(row, data) {
          const partText = String(data?.part || '').trim();
          if (partText) $('td:eq(0)', row).attr('title', partText).addClass('fai-cell-ellipsis');
        },
        // 2025-12-17: mover el search al header del card (a la altura de PENDING / IN PROCESS)
        initComplete: function() {
          const api = this.api();
          const $container = $(api.table().container());
          const $filter = $container.find('.dataTables_filter');
          const $slot = $(`.dt-filter-slot[data-dt-filter-slot="${bucket}"]`);
          if ($slot.length && $filter.length) $filter.appendTo($slot);
        },
        ajax: {
          url: ROUTES.partsData,
          data: {
            bucket
          },
          dataType: 'json',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          dataSrc: (json) => (Array.isArray(json?.data) ? json.data : [])
        },
        columns: COLUMNS[bucket],
        drawCallback: function() {
          const api = this.api();
          const total = api.page.info().recordsDisplay;
          // Mostrar badge solo cuando haya datos; ocultar si es 0 o vacío
          $(badgeSelector)
            .text(total > 0 ? total : '')
            .toggleClass('d-none', !total);

          if (bucket !== 'process') return;

          api.rows({
            page: 'current'
          }).every(function() {
            const row = this.data();
            const id = row?.id;
            const pct = parseInt(row?.progress_pct, 10) || 0;

            const $wrap = $(`.progress[data-order-id="${id}"]`);
            const $bar = $wrap.find('.progress-bar');
            if (!$wrap.length || !$bar.length) return;

            $bar.attr('aria-valuenow', pct).css('width', pct + '%').text(pct + '%');
            $bar.removeClass('bg-secondary bg-danger bg-warning bg-success');
            if (pct >= 100) $bar.addClass('bg-success');
            else if (pct >= 50) $bar.addClass('bg-warning');
            else $bar.addClass('bg-danger');
          });
        }
      });
    }

    // Init
    $(function() {
      ctx.dtEmpty = makeDT('empty', '#badgePending');
      ctx.dtProcess = makeDT('process', '#badgeProcess');
    });

    // --------------------------
    // Export resumen PDF (In Process)
    // --------------------------
    function parseProgressVal(raw) {
      if (raw === undefined || raw === null) return null;
      if (typeof raw === 'number') return raw;
      const str = String(raw);
      const m = str.match(/-?\d+(?:[.,]\d+)?/);
      if (!m) return null;
      const num = parseFloat(m[0].replace(',', '.'));
      return Number.isNaN(num) ? null : num;
    }

    function computeSummaryFromRows(rows) {
      if (!rows || !rows.length) return null;
      const list = [];
      rows.forEach(r => {
        const val = parseProgressVal(r?.progress_pct ?? r?.inspection_progress ?? r?.progress);
        if (val === null) return; // si no hay número, no se cuenta
        list.push({
          val,
          id: r?.id ?? '',
          part: r?.part ?? '',
          work_id: r?.work_id ?? ''
        });
      });

      const total = list.length;
      if (total === 0) return null;

      let over50 = 0,
        mid = 0,
        below0 = 0,
        sum = 0;

      list.forEach(({ val }) => {
        sum += val;
        if (val > 50) over50++;
        else if (val > 0) mid++;
        else below0++;
      });

      const sorted = [...list].sort((a, b) => a.val - b.val);
      const median = (sorted.length % 2 === 1)
        ? sorted[(sorted.length - 1) / 2].val
        : (sorted[sorted.length / 2 - 1].val + sorted[sorted.length / 2].val) / 2;
      const topLow = sorted.slice(0, 5);
      const topHigh = sorted.slice(-5).reverse();

      return {
        total,
        over50,
        mid,
        below0,
        avg: sum / total,
        median,
        topLow,
        topHigh
      };
    }

    function buildProcessSummaryFromTable() {
      if (!ctx.dtProcess) return null;
      const rows = ctx.dtProcess.rows().data().toArray();
      return computeSummaryFromRows(rows);
    }

    function buildProcessSummaryFromServer() {
      return fetchJson(ROUTES.partsData, {
        data: {
          bucket: 'process'
        }
      }).then(resp => {
        const rows = Array.isArray(resp?.data) ? resp.data : [];
        return computeSummaryFromRows(rows);
      });
    }

    function openProcessPdf(summary) {
      const {
        total,
        over50,
        mid,
        below0,
        avg,
        median,
        topLow = [],
        topHigh = []
      } = summary;
      const pct = (v) => total ? (v / total * 100).toFixed(1) + '%' : '0%';
      const now = new Date().toLocaleString();
      const chartSvg = (() => {
        const data = [
          { label: '> 50%', value: over50, color: '#10b981' },
          { label: '0% to 50%', value: mid, color: '#f59e0b' },
          { label: '<= 0%', value: below0, color: '#ef4444' }
        ];
        const max = Math.max(...data.map(d => d.value), 1);
        const width = 320;
        const barH = 26;
        const gap = 10;
        const height = data.length * (barH + gap);
        const bars = data.map((d, idx) => {
          const w = Math.max(4, (d.value / max) * width);
          const y = idx * (barH + gap);
          return `
            <g>
              <rect x="0" y="${y}" width="${w}" height="${barH}" rx="6" fill="${d.color}" opacity="0.85"></rect>
              <text x="${w + 6}" y="${y + barH / 2 + 4}" font-size="12" fill="#111827">${d.label}: ${d.value}</text>
            </g>
          `;
        }).join('');
        return `<svg width="${width + 140}" height="${height}" xmlns="http://www.w3.org/2000/svg">${bars}</svg>`;
      })();
      const topRows = topLow.map((r, idx) =>
        `<tr><td>${idx + 1}</td><td>${r.work_id || ''}</td><td>${r.part || ''}</td><td>${r.val}%</td></tr>`
      ).join('') || '<tr><td colspan="4">Sin datos</td></tr>';
      const topHighRows = topHigh.map((r, idx) =>
        `<tr><td>${idx + 1}</td><td>${r.work_id || ''}</td><td>${r.part || ''}</td><td>${r.val}%</td></tr>`
      ).join('') || '<tr><td colspan="4">Sin datos</td></tr>';

      const html = `
        <html>
          <head>
            <title>FAI/IPI In Process - Summary</title>
            <style>
              body { font-family: Arial, sans-serif; padding: 20px; color: #111827; }
              h1 { font-size: 20px; margin-bottom: 4px; }
              h2 { font-size: 13px; margin: 0 0 16px; color: #6b7280; }
              table { border-collapse: collapse; width: 100%; margin-top: 12px; }
              th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: center; }
              th { background: #f3f4f6; font-weight: 700; letter-spacing: 0.02em; font-size: 12px; }
              td { font-size: 12px; }
              tr:nth-child(even) td { background: #f9fafb; }
              .muted { color: #6b7280; font-size: 11px; }
            </style>
          </head>
          <body>
            <h1>FAI/IPI - In Process Summary</h1>
            <h2>Total orders: ${total} <span class="muted">| Generated: ${now}</span></h2>
            <table>
              <thead>
                <tr>
                  <th>Range</th>
                  <th>Count</th>
                  <th>% of total</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>> 50%</td><td>${over50}</td><td>${pct(over50)}</td></tr>
                <tr><td>0% to 50%</td><td>${mid}</td><td>${pct(mid)}</td></tr>
                <tr><td><= 0%</td><td>${below0}</td><td>${pct(below0)}</td></tr>
              </tbody>
            </table>

            <div style="margin-top:12px;">${chartSvg}</div>

            <table>
              <thead>
                <tr>
                  <th>Average (%)</th>
                  <th>Median (%)</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>${avg?.toFixed ? avg.toFixed(1) : ''}</td><td>${median?.toFixed ? median.toFixed(1) : ''}</td></tr>
              </tbody>
            </table>

            <table>
              <caption style="text-align:left; font-weight:700; padding:6px 0 2px; color:#111827;">Top 5 lowest progress</caption>
              <thead>
                <tr>
                  <th>#</th>
                  <th>JOB</th>
                  <th>PART/DESC</th>
                  <th>Progress (%)</th>
                </tr>
              </thead>
              <tbody>
                ${topRows}
              </tbody>
            </table>

            <table>
              <caption style="text-align:left; font-weight:700; padding:6px 0 2px; color:#111827;">Top 5 highest progress</caption>
              <thead>
                <tr>
                  <th>#</th>
                  <th>JOB</th>
                  <th>PART/DESC</th>
                  <th>Progress (%)</th>
                </tr>
              </thead>
              <tbody>
                ${topHighRows}
              </tbody>
            </table>
          </body>
        </html>
      `;
      const blob = new Blob([html], { type: 'text/html' });
      const url = URL.createObjectURL(blob);
      $('#processPdfFrame').attr('src', url);
      $('#processPdfModal').data('pdfUrl', url).modal('show');
    }

    $('#processPdfBtn').on('click', function() {
      // Prioriza obtener datos frescos desde el backend
      buildProcessSummaryFromServer()
        .then(summary => summary || buildProcessSummaryFromTable())
        .then(summary => {
          if (!summary) {
            Swal.fire('Sin datos', 'No hay órdenes en proceso para generar el PDF.', 'info');
            return;
          }
          openProcessPdf(summary);
        })
        .catch(() => {
          Swal.fire('Error', 'No fue posible obtener los datos.', 'error');
        });
    });

    // Limpia el recurso del iframe al cerrar el modal
    $('#processPdfModal').on('hidden.bs.modal', function() {
      const url = $(this).data('pdfUrl');
      if (url) URL.revokeObjectURL(url);
      $(this).data('pdfUrl', null);
      $('#processPdfFrame').attr('src', 'about:blank');
    });

    // Imprimir desde el iframe al presionar el botón en el modal
    $('#printProcessPdfBtn').on('click', function() {
      const frame = document.getElementById('processPdfFrame');
      if (frame && frame.contentWindow) {
        frame.contentWindow.focus();
        frame.contentWindow.print();
      }
    });

    // ================== Modal: show/hidden ==================
    $('#editModal').on('show.bs.modal', function(event) {
      const $modal = $(this);
      const button = $(event.relatedTarget);

      // Re-enlazar referencias del modal activo
      ctx.modal.$rowsContainer = $modal.find('#dynamicTable tbody');
      ctx.modal.$samplingResult = $modal.find('#edit-sampling-result');
      ctx.modal.$operationInput = $modal.find('#operationInput');
      ctx.modal.$reportPre = $modal.find('#inspection-missing');
      ctx.modal.$reportBox = $modal.find('#inspection-missing-container');
      ctx.modal.$woqty = $modal.find('#edit-woqty');

      // Campos base
      const id = button.data('id');
      const opIn = (button.data('operation') === 'default_value') ? '' : (button.data('operation') || '');
      $modal.find('#edit-id, #order-id').val(id);
      $modal.find('#edit-workid').val(button.data('workid'));
      $modal.find('#edit-woqty').val(button.data('woqty'));
      ctx.modal.$operationInput.val(opIn);

      const pn = button.attr('data-pn') || button.data('pn') || '';
      const desc = button.attr('data-description') || ''; // 👈 siempre string (raw attribute)
      $modal.find('#edit-fullpart').val(`${pn} - ${String(desc).split(',')[0]}`);

      // ✅ Traer sampling desde atributos del botón (lo que viene de BD)
      const samplingFromBtn = parseInt(button.data('sampling'), 10) || 0; // número
      const samplingCheckFromBtn = (button.data('sampling_check') || 'normal').toString().toLowerCase(); // 'normal' | 'tightened'

      // Pintar en el modal SIN trigger
      $modal.find('#edit-sampling-type').val(samplingCheckFromBtn);
      if (samplingFromBtn > 0) {
        $modal.find('#edit-sampling-result').val(samplingFromBtn);
      } else {
        $modal.find('#edit-sampling-result').val(''); // deja vacío para que se calcule
      }

      // Limpiar tbody y cargar filas guardadas
      ctx.modal.$rowsContainer.empty();
      const orderId = id;
      loadFaiRows(orderId, () => {
        updateInspectionMissing();
      });

      // Si BD ya trae sampling (>0), NO recalcular; si no, calcula con tu endpoint
      if (samplingFromBtn > 0) {
        const opsNow = parseInt(ctx.modal.$operationInput.val(), 10) || 0;
        $('#addRowBtn').prop('disabled', opsNow === 0);
        refreshProgress(orderId, opsNow, null); // usa sampling-1 internamente
      } else {
        // aquí calculará vía /sampling-plan y luego refrescará
        updateSamplingQty();
        const opsNow = parseInt(ctx.modal.$operationInput.val(), 10) || 0;
        $('#addRowBtn').prop('disabled', opsNow === 0);
        refreshProgress(orderId, opsNow, null);
      }
    });


    $('#editModal').on('hidden.bs.modal', function() {
      if (ctx.dtEmpty) ctx.dtEmpty.ajax.reload(null, false);
      if (ctx.dtProcess) ctx.dtProcess.ajax.reload(null, false);

      // liberar referencias del modal
      ctx.modal.$rowsContainer = null;
      ctx.modal.$samplingResult = null;
      ctx.modal.$operationInput = null;
      ctx.modal.$reportPre = null;
      ctx.modal.$reportBox = null;
      ctx.modal.$woqty = null;
      ctx.faiDoneOps.clear();
      ctx.ipiCountMap.clear();
    });

    // ================== Eventos del modal ==================
    // Cambios en sampling (tipo/cantidad) y WO_QTY
    $('#editModal').on('change input', '#edit-sampling-type, #edit-woqty', () => {
      updateSamplingQty();
    });

    /* FIX: si cambia #operationInput, habilitar/deshabilitar botón agregar fila */
    $('#editModal').on('input change', '#operationInput', () => {
      const ops = parseInt(ctx.modal.$operationInput.val(), 10) || 0;
      $('#addRowBtn').prop('disabled', ops === 0);
    });

    /*=================== Guardar # de operaciones============================*/
    $('#editModal').on('click', '#addOperationBtn', function() {
      const orderId = $('#order-id').val();
      const operation = parseInt(ctx.modal.$operationInput.val().trim(), 10) || 0;
      const sampling = parseInt(ctx.modal.$samplingResult.val(), 10) || 0;
      const samplingType = $('#edit-sampling-type').val();

      if (!orderId) {
        return swalError('Error', 'No order has been selected.');
      }

      if (operation <= 0) {
        return swalWarn('Required field', 'Records the number of operations');
      }

      if (sampling <= 0 || Number.isNaN(sampling)) {
        return swalError('Sampling required', 'Calculate the sampling before saving the operations.');
      }

      const total_fai = operation * 1;
      const total_ipi = Math.max(operation * sampling - total_fai, 0);

      const $btn = $(this).prop('disabled', true);

      $.post(ROUTES.updateOps(orderId), {
          _token: getCsrf(),
          operation,
          sampling,
          total_fai,
          total_ipi,
          sampling_check: samplingType
        })
        .done(() => {
          $('#addRowBtn').prop('disabled', operation === 0);

          setInspectionStatus(orderId, 'in_progress')
            .always(() => {
              ctx.modal.$operationInput.val(operation);
              $(`button[data-id="${orderId}"]`).attr('data-operation', operation);
              refreshProgress(orderId, operation, null); // 👈 usa sampling-1 internamente
              updateInspectionMissing();

              if (ctx.dtEmpty) ctx.dtEmpty.ajax.reload(null, false);
              if (ctx.dtProcess) ctx.dtProcess.ajax.reload(null, false);
              swalOk('¡Updated!', 'Operation saved successfully');
            });
        })
        .fail(() => swalError('Error', 'The operation could not be updated.'))
        .always(() => {
          $btn.prop('disabled', false);
        });
    });

    /*=====Guardar automáticamente cuando cambie el tipo de sampling===========*/
    function toSamplingNumber(resp) {
      const raw = resp?.sample_qty ?? resp?.sample_size ?? resp?.n ?? resp?.sampling ?? resp?.size ?? resp;
      const n = parseInt(raw, 10);
      return Number.isFinite(n) && n >= 1 ? n : NaN;
    }

    $('#editModal')
      .off('change.sampling', '#edit-sampling-type')
      .on('change.sampling', '#edit-sampling-type', function() {
        const orderId = $('#order-id').val();
        const samplingType = ($(this).val() || '').trim();
        const lotSize = parseInt($('#edit-woqty').val(), 10) || 0;

        const $samplingRes = (ctx?.modal?.$samplingResult?.length ? ctx.modal.$samplingResult : $('#edit-sampling-result'));
        const $opInput = (ctx?.modal?.$operationInput?.length ? ctx.modal.$operationInput : $('#operationInput'));

        if (!lotSize) {
          swalError('WO Qty Required', 'Enter a valid WO Quantity to calculate the sample.');
          return;
        }
        fetchJson(ROUTES.samplingPlan(lotSize, samplingType))
          .then((resp) => {
            const n = toSamplingNumber(resp);
            if (!Number.isFinite(n)) {
              swalError('Invalid plan', 'The sample size could not be calculated for that type.');
              return $.Deferred().reject('invalid-sampling').promise();
            }
            if ($samplingRes.length) $samplingRes.val(n);
            return $.post(ROUTES.updateOps(orderId), {
              _token: getCsrf(),
              sampling_check: samplingType,
              sampling: n
            });
          })
          .done((saveResp) => {
            const operation = parseInt(($opInput.val() || saveResp?.operation || 0), 10) || 0;
            const sampling = parseInt((saveResp?.sampling ?? $samplingRes.val() ?? 0), 10) || 0;
            if (typeof refreshProgress === 'function') refreshProgress(orderId, operation, null); // 👈
            if (typeof updateInspectionMissing === 'function') updateInspectionMissing();
            if (ctx.dtEmpty) ctx.dtEmpty.ajax.reload(null, false);
            if (ctx.dtProcess) ctx.dtProcess.ajax.reload(null, false);
            swalOk('¡Updated!', `Sampling type & sampling saved successfully`);
          })
          .fail((xhr) => {
            if (xhr !== 'invalid-sampling') {
              console.warn('Sampling-type change failed:', xhr?.status, xhr?.responseText);
              swalError('Error', 'The change to the sampling could not be saved.');
            }
          });
      });

    // Agregar fila (verifica que la operación esté guardada)
    $('#editModal').on('click', '#addRowBtn', function() {
      const orderId = $('#order-id').val();
      const opsVal = (ctx.modal.$operationInput.val() || '').trim();
      if (!opsVal) return swalWarn('Required information', 'Enter the number of operations first');

      $.get(ROUTES.validateOps(orderId, opsVal))
        .done(resp => {
          if (!resp?.saved) {
            return swalError('Not saved yet', 'Save the number of operations before adding inspections.');
          }
          const row = createDraftRow();
          if (!row) return;
          ctx.modal.$rowsContainer.prepend(row);
          row.find('input,select').filter(':visible:not([disabled])').first().focus();
        })
        .fail(() => swalError('Server error', 'Unable to validate operations. Try again later.'));
    });

    // Eliminar borrador
    $('#editModal').on('click', '.removeRowBtn', function() {
      $(this).closest('tr').remove();
      updateInspectionMissing();
      refreshAllSamplingSelects();
    });

    // Editar fila guardada
    $('#editModal').on('click', '.editRowBtn', function() {
      const $row = $(this).closest('tr');
      $row.find('input, select').prop('disabled', false);
      $row.find('td:last').html(`
        <button type="button" class="btn btn-success btn-sm saveRowBtn me-1"><i class="fas fa-save"></i></button>
        <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
      `);

      // Si es IPI y habilitamos edición, recalcula pendientes de esa fila
      const type = String($row.find('select[name="insp_type[]"]').val() || '').toUpperCase();
      if (type === 'IPI') {
        const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;
        const op = $row.find('select[name="operation[]"], input[name="operation[]"]').val() || null;
        const $cell = $row.find('td.col-sample');
        const cur = $cell.find('input[name="sample_idx[]"]').val() || null;
        renderSampleCell($cell.empty(), 'IPI', sampling, cur, op);
      }
    });

    // Guardar fila (create/update)
    $('#editModal').on('click', '.saveRowBtn', function() {
      const $row = $(this).closest('tr');
      const orderId = $('#order-id').val();
      const rowId = $row.data('id');

      const inspType = String($row.find('select[name="insp_type[]"]').val() || '').toUpperCase();

      // sample_idx: FAI => 1; IPI => input
      let sampleIdx = null;
      if (inspType === 'FAI') {
        sampleIdx = 1;
      } else {
        const $inp = $row.find('input[name="sample_idx[]"]').not('.sample-fixed');
        const $hid = $row.find('input[name="sample_idx[]"].sample-fixed');
        sampleIdx = $inp.length ? parseInt($inp.val(), 10) : ($hid.length ? parseInt($hid.val(), 10) : null);
      }

      // ==== VALIDACIÓN dinámica por restante en la OPERACIÓN (WO_QTY - guardadas - borradores) ====
      if (inspType === 'IPI') {
        const opVal = $row.find('select[name="operation[]"]').val() || $row.find('input[name="operation[]"]').val() || '';
        const curSavedQty = $row.attr('data-id') ?
          (parseInt($row.attr('data-qty_pcs') || $row.data('qty_pcs') || $row.data('qty') || 0, 10) || 0) :
          0;

        const remainingWo = getIpiRemainingForOpByWo(opVal, curSavedQty, $row);
        if (!remainingWo) {
          return swalWarn('No remaining', `No remaining IPI pieces for ${opVal}.`);
        }
        if (!sampleIdx || sampleIdx < 1 || sampleIdx > remainingWo) {
          return swalWarn('Invalid sample', `You can enter between 1 and ${remainingWo} pieces for ${opVal}.`);
        }
      }

      const payload = {
        _token: getCsrf(),
        order_schedule_id: orderId,
        date: $row.find('input[name="date[]"]').val()?.trim(),
        insp_type: $row.find('select[name="insp_type[]"]').val(),
        operation: $row.find('select[name="operation[]"]').val() || $row.find('input[name="operation[]"]').val(),
        operator: $row.find('input[name="operator[]"]').val()?.trim(),
        results: $row.find('select[name="results[]"]').val(),
        sb_is: $row.find('input[name="sb_is[]"]').val()?.trim(),
        observation: $row.find('input[name="observation[]"]').val()?.trim(),
        station: $row.find('input[name="station[]"]').val()?.trim(),
        method: $row.find('select[name="method[]"]').val(),
        inspector: $('#edit-inspector').val(),
        qty_pcs: sampleIdx
      };
      if (rowId) payload.id = rowId;

      /* FIX: fuerza dataType json para evitar HTML inesperado */
      $.ajax({
          url: ROUTES.storeSingle,
          method: 'POST',
          data: payload,
          dataType: 'json'
        })
        .done(resp => {
          if (resp?.id) $row.attr('data-id', resp.id);

          // mantener qty de la fila para futuras ediciones
          if (inspType === 'IPI') $row.attr('data-qty_pcs', sampleIdx);

          $row.find('input, select, .saveRowBtn').prop('disabled', true);
          $row.find('input.sample-fixed').prop('disabled', true);

          // Actualiza mapas y selects dependientes
          updateInspectionMissing();
          refreshAllSamplingSelects();

          // Acciones de la última celda
          $row.find('td:last').html(`
            <button type="button" class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
            <button type="button" class="btn btn-warning btn-sm editRowBtn me-1"><i class="fas fa-edit"></i></button>
            <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
          `);

          // ======= Progreso con ipiReq = sampling - 1 =======
          const opsNow = parseInt(ctx.modal.$operationInput.val(), 10) || 0;
          const samplingNow = parseInt(ctx.modal.$samplingResult.val(), 10) || 0;
          const ipiReqNow = Math.max(0, samplingNow - 1); // 👈 requisito real de IPI

          fetchJson(ROUTES.faibyOrder(orderId)).then(rows => {
            rows = Array.isArray(rows) ? rows : [];

            // Progreso: computeProgressFromRows espera "ipiRequired" (= sampling-1)
            const pct = computeProgressFromRows(rows, opsNow, ipiReqNow);
            renderOrderProgress(orderId, pct);

            // ===== [MOD IPI → saltar a siguiente] =====
            try {
              if (typeof updateInspectionMissing === 'function') updateInspectionMissing();

              if (String(inspType).toUpperCase() === 'IPI') {
                const opJustSaved = $row.find('select[name="operation[]"], input[name="operation[]"]').val() || '';
                const ipiDoneForOp = ctx.ipiCountMap.get(opJustSaved) || 0;

                // Cuando cumpla ipiReqNow para esa op, sugiere la siguiente
                if (ipiReqNow > 0 && ipiDoneForOp >= ipiReqNow) {
                  const opsTotal = parseInt(ctx.modal.$operationInput.val(), 10) || 0;
                  const nextPair = getNextInspectionPair(opsTotal); // esta ya usa (sampling-1) internamente
                  if (nextPair) {
                    const draft = createDraftRow();
                    if (draft) {
                      ctx.modal.$rowsContainer.prepend(draft);
                      draft.find('input,select').filter(':visible:not([disabled])').first().focus();
                    }
                  }
                }
              }
            } catch (e) {
              console.warn('auto-next IPI suggestion skipped:', e);
            }

            // ======= Completed / In progress =======
            if (pct >= 100) {
              Swal.fire({
                icon: 'success',
                title: '¡Inspection completed!',
                text: `1 FAI was completed and ${ipiReqNow} IPI for each of the ${opsNow} operations.`,
                confirmButtonText: 'Accept',
                allowOutsideClick: false,
                allowEscapeKey: false
              }).then(() => {
                setInspectionStatus(orderId, 'completed')
                  .done(() => {
                    swalOk('¡Ready!', 'Inspection is complete');
                    $('#editModal').modal('hide');
                    if (ctx.dtEmpty) ctx.dtEmpty.ajax.reload(null, false);
                    if (ctx.dtProcess) ctx.dtProcess.ajax.reload(null, false);
                  })
                  .fail(xhr => swalError('Could not be completed', xhr.responseJSON?.message || 'Error inesperado'));
              });
            } else {
              if (rows.length > 0) {
                setInspectionStatus(orderId, 'in_progress')
                  .fail(xhr => console.warn('status process fail:', xhr?.status));
              }
              swalOk('¡Saved!', 'The inspection was saved successfully');
            }
          });
        })
        .fail(xhr => {
          const msg = xhr.responseJSON?.error ? `Error: ${xhr.responseJSON.error}` : 'Error saving inspection';
          swalError('Error', msg);
        });
    });

    // Eliminar fila guardada
    $('#editModal').on('click', '.deleteRowBtn', function() {
      const $row = $(this).closest('tr');
      const rowId = $row.data('id');

      Swal.fire({
        icon: 'warning',
        title: '¿Delete inspection?',
        text: 'This action cannot be undone',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
      }).then(result => {
        if (!result.isConfirmed) return;

        // Si es borrador
        if (!rowId) {
          $row.remove();
          updateInspectionMissing();
          refreshAllSamplingSelects();
          return;
        }

        $.ajax({
            url: ROUTES.deleteRow(rowId),
            method: 'DELETE',
            data: {
              _token: getCsrf()
            }
          })
          .done(() => {
            swalOk('Deleted', 'The inspection has been eliminated');
            $row.remove();
            updateInspectionMissing();
            refreshAllSamplingSelects();

            const orderId = $('#order-id').val();
            const opsNow = parseInt(ctx.modal.$operationInput.val()) || 0;
            const samplingNow = parseInt(ctx.modal.$samplingResult.val()) || 0;
            const ipiReqNow = Math.max(0, samplingNow - 1);

            if (orderId) {
              fetchJson(ROUTES.faibyOrder(orderId)).then(rows => {
                rows = Array.isArray(rows) ? rows : [];
                const pct = computeProgressFromRows(rows, opsNow, ipiReqNow); // 👈 usa sampling-1
                renderOrderProgress(orderId, pct);
                const newStatus =
                  (rows.length === 0) ? 'pending' :
                  (pct >= 100 ? 'completed' : 'in_progress');

                setInspectionStatus(orderId, newStatus)
                  .always(() => {
                    if (ctx.dtEmpty) ctx.dtEmpty.ajax.reload(null, false);
                    if (ctx.dtProcess) ctx.dtProcess.ajax.reload(null, false);
                  });
              });
            } else {
              if (ctx.dtEmpty) ctx.dtEmpty.ajax.reload(null, false);
              if (ctx.dtProcess) ctx.dtProcess.ajax.reload(null, false);
            }
          })
          .fail(() => swalError('Error', 'No se pudo eliminar la fila'));
      });
    });

    // ================== Lógica de Sampling & Reporte ==================
    /* FIX: robusto si refs del modal no existen aún */
    function updateSamplingQty() {
      const $sampling = (ctx.modal.$samplingResult && ctx.modal.$samplingResult.length) ?
        ctx.modal.$samplingResult : $('#edit-sampling-result');

      const lotSize = parseInt($('#edit-woqty').val(), 10);
      const type = $('#edit-sampling-type').val();

      if (!lotSize || lotSize < 1) {
        $sampling.val('');
        refreshAllSamplingSelects();
        return;
      }
      fetchJson(ROUTES.samplingPlan(lotSize, type)).then(data => {
        const sample = parseInt(
          data?.sample_qty ?? data?.sample_size ?? data?.n ?? data?.sampling ?? data?.size ?? 0, 10
        ) || 0;
        $sampling.val(sample);

        refreshAllSamplingSelects();
        refreshPendingIpiOptions();

        const orderId = $('#order-id').val();
        const opsNow = parseInt(ctx.modal.$operationInput?.val?.() || 0, 10) || 0;
        if (orderId && opsNow) {
          refreshProgress(orderId, opsNow, null); // 👈 calcula sampling-1 dentro
          updateInspectionMissing();
        }
      });
    }

    function updateInspectionMissing() {
      const sampling = parseInt(ctx.modal.$samplingResult?.val?.() || 0, 10) || 0;
      const operations = parseInt(ctx.modal.$operationInput?.val?.() || 0, 10) || 0;

      const $box = ctx.modal.$reportBox;
      const $pre = ctx.modal.$reportPre;

      if (!operations) {
        if ($pre?.length) $pre.text('');
        if ($box?.length) $box.removeClass('bg-success bg-warning text-white');
        return;
      }

      const faiPassMap = new Map(),
        faiFailMap = new Map(),
        ipiPassMap = new Map(),
        ipiFailMap = new Map();

      ctx.faiDoneOps.clear();
      ctx.ipiCountMap.clear();

      ctx.modal.$rowsContainer.find('tr[data-id]').each(function() {
        const $r = $(this);
        const type = String($r.find('select[name="insp_type[]"]').val() || '').toUpperCase();
        const op = $r.find('select[name="operation[]"], input[name="operation[]"]').val() || '';
        const res = String($r.find('select[name="results[]"]').val() || '').toLowerCase();
        if (!op || !['pass', 'no pass'].includes(res)) return;

        const qty = getRowQty($r);

        if (type === 'FAI') {
          if (res === 'pass') faiPassMap.set(op, (faiPassMap.get(op) || 0) + qty);
          if (res === 'no pass') faiFailMap.set(op, (faiFailMap.get(op) || 0) + qty);
        }
        if (type === 'IPI') {
          if (res === 'pass') ipiPassMap.set(op, (ipiPassMap.get(op) || 0) + qty);
          if (res === 'no pass') ipiFailMap.set(op, (ipiFailMap.get(op) || 0) + qty);
        }
      });

      for (const [op, sum] of faiPassMap.entries())
        if (sum >= 1) ctx.faiDoneOps.add(op);
      for (const [op, sum] of ipiPassMap.entries()) ctx.ipiCountMap.set(op, sum);

      let resumen = '<table class="table table-sm fai-summary-table mb-0"><thead><tr>'
        + '<th class="text-center">Status</th><th class="text-center">Op</th><th class="text-center">FAI</th><th class="text-center">NP FAI</th><th class="text-center">IPI</th><th class="text-center">NP IPI</th><th class="text-center">Done</th>'
        + '</tr></thead><tbody>';
      let faltantes = false;

      for (let i = 1; i <= operations; i++) {
        const op = ordinalSuffix(i);
        const faiPass = faiPassMap.get(op) || 0;
        const faiFail = faiFailMap.get(op) || 0;
        const ipiPass = ipiPassMap.get(op) || 0;
        const ipiFail = ipiFailMap.get(op) || 0;

        const faiReq = 1;
        const ipiReq = Math.max(0, sampling - 1); // 👈 clamp

        const faiRealizadosOp = faiPass + faiFail;
        const ipiRealizadosOp = ipiPass + ipiFail;

        // Icono con badge para mejor visibilidad
        const globalBadgeClass = (faiPass >= faiReq && ipiPass >= ipiReq)
          ? 'badge badge-success fai-icon-badge'
          : (faiPass < faiReq && ipiPass < ipiReq ? 'badge badge-danger fai-icon-badge' : 'badge badge-warning text-dark fai-icon-badge');
        // Font Awesome 5 friendly icons
        const globalLabel = (faiPass >= faiReq && ipiPass >= ipiReq)
          ? '<i class="fas fa-check-circle"></i>'
          : (faiPass < faiReq && ipiPass < ipiReq
            ? '<i class="fas fa-times-circle"></i>'
            : '<i class="fas fa-exclamation-triangle"></i>');

        let faiBadgeClass = 'badge badge-secondary'; // gris cuando no hay avances
        if (faiPass >= faiReq) {
          faiBadgeClass = 'badge badge-success';
        } else if (faiPass > 0) {
          faiBadgeClass = 'badge badge-warning text-dark'; // amarillo si tiene al menos 1
        }
        let ipiBadgeClass = 'badge badge-secondary';
        if (ipiPass >= ipiReq) {
          ipiBadgeClass = 'badge badge-success';
        } else if (ipiPass > 0) {
          ipiBadgeClass = 'badge badge-warning text-dark';
        }

        const line = `<tr class="text-center fai-summary-row" data-op="${op}">
            <td><span class="${globalBadgeClass}">${globalLabel}</span></td>
            <td><strong>${op}</strong></td>
            <td><span class="${faiBadgeClass} fai-filter" data-op="${op}" data-type="FAI">FAI ${faiPass}/${faiReq}</span></td>
            <td class="text-muted small">${faiFail}</td>
            <td><span class="${ipiBadgeClass} fai-filter" data-op="${op}" data-type="IPI">IPI ${ipiPass}/${ipiReq}</span></td>
            <td class="text-muted small">${ipiFail}</td>
            <td class="text-muted small">${faiRealizadosOp + ipiRealizadosOp}</td>
          </tr>`;

        resumen += line;
        if (faiPass < faiReq || ipiPass < ipiReq) faltantes = true;
      }

      resumen += '</tbody></table>';

      if ($pre?.length) $pre.html(resumen.trim());
      if ($box?.length) {
        $box.removeClass('bg-success bg-warning text-white');
        if (faltantes) $box.addClass('bg-warning text-white');
        else $box.addClass('bg-success text-white');
      }

      refreshPendingIpiOptions();
    }

    // ================== Helpers varios ==================
    function ordinalSuffix(n) {
      if (n === 1) return '1st Op';
      if (n === 2) return '2nd Op';
      if (n === 3) return '3rd Op';
      return `${n}th Op`;
    }

    function getRowQty($row) {
      const attr = parseInt($row.attr('data-qty_pcs') ?? $row.data('qty_pcs') ?? $row.data('qty') ?? '', 10);
      if (!isNaN(attr)) return attr;

      const q1 = parseInt($row.find('input[name="qty_pcs[]"]').val() ?? '', 10);
      if (!isNaN(q1)) return q1;

      const q2 = parseInt($row.find('input[name="sample_idx[]"]').val() ?? '', 10);
      if (!isNaN(q2)) return q2;

      const q3 = parseInt($row.find('select[name="sample_idx[]"]').val() ?? '', 10);
      if (!isNaN(q3)) return q3;

      return 1;
    }

    function getWoQty() {
      const raw = (ctx?.modal?.$woqty?.length ? ctx.modal.$woqty.val() : $('#edit-woqty').val());
      const n = parseInt(raw, 10);
      return Number.isFinite(n) && n > 0 ? n : 0;
    }

    function getDraftIpiSumForOp(op, $excludeRow = null) {
      let sum = 0;
      ctx.modal.$rowsContainer.find('tr').each(function() {
        const $r = $(this);
        if ($excludeRow && $r[0] === $excludeRow[0]) return;
        const isSaved = !!$r.attr('data-id');
        if (isSaved) return;
        const t = String($r.find('select[name="insp_type[]"]').val() || '').toUpperCase();
        if (t !== 'IPI') return;
        const opVal = $r.find('select[name="operation[]"], input[name="operation[]"]').val() || '';
        if (opVal !== op) return;

        const q = parseInt($r.find('input[name="sample_idx[]"]').val() || 0, 10);
        if (Number.isFinite(q) && q > 0) sum += q;
      });
      return sum;
    }

    function getIpiRemainingForOpByWo(op, currentRowQty = 0, $rowCtx = null) {
      const wo = getWoQty();
      if (!wo) return 0;

      const saved = ctx.ipiCountMap.get(op) || 0;
      const drafts = getDraftIpiSumForOp(op, $rowCtx);
      const isSavedRow = $rowCtx && !!$rowCtx.attr('data-id');

      const usedExceptThis = saved + drafts - (isSavedRow ? (parseInt(currentRowQty, 10) || 0) : 0);

      return Math.max(0, wo - usedExceptThis);
    }

    function buildSamplingInput(sampling, currentVal = null, maxAllowed = null) {
      const woMax = getWoQty();
      const s = Math.max(0, parseInt(sampling) || 0);
      const upper = Math.max(0, maxAllowed ?? woMax ?? s);

      if (upper === 0) {
        return $(`<input type="number" name="sample_idx[]" class="form-control" disabled placeholder="Sin piezas pendientes">`);
      }

      const $input = $(`<input type="number" name="sample_idx[]" class="form-control">`)
        .attr({
          min: 1,
          max: upper,
          step: 1
        });

      const cur = parseInt(currentVal, 10);
      if (Number.isFinite(cur) && cur >= 1 && cur <= upper) $input.val(cur);
      else $input.val(1);

      $input.on('input change', function() {
        const max = parseInt($(this).attr('max'), 10) || upper;
        const min = parseInt($(this).attr('min'), 10) || 1;
        let val = parseInt($(this).val(), 10);
        if (!Number.isFinite(val)) return;
        if (val > max) $(this).val(max);
        if (val < min) $(this).val(min);
      });

      return $input;
    }

    function renderSampleCell($cell, type, sampling, currentVal = null, opForPending = null) {
      $cell.empty();
      const t = String(type).toUpperCase();

      if (t === 'FAI') {
        const $fixed = $(`<input type="number" class="form-control sample-fixed" value="1" readonly>`);
        $cell.append($fixed);
        $cell.append(`<input type="hidden" name="sample_idx[]" value="1">`);
        return;
      }

      // IPI
      const $row = $cell.closest('tr');
      const op = opForPending || $row.find('select[name="operation[]"], input[name="operation[]"]').val() || '';
      const woMax = getWoQty();

      if (!woMax || !op) {
        const $inpDisabled = $(`<input type="number" name="sample_idx[]" class="form-control" disabled
                           placeholder="${!woMax ? 'WO_QTY required' : 'Operation required'}">`);
        $cell.append($inpDisabled);
        return;
      }

      const curQty = Number.isFinite(parseInt(currentVal, 10)) ? parseInt(currentVal, 10) : 0;
      const remaining = getIpiRemainingForOpByWo(op, curQty, $row);

      if (remaining === 0) {
        $cell.append($(`<input type="number" name="sample_idx[]" class="form-control" min="0" max="0" value="0" disabled placeholder="Sin piezas pendientes">`));
        return;
      }

      let initVal = curQty || 1;
      if (initVal > remaining) initVal = remaining;
      if (initVal < 1) initVal = 1;

      const $inpNow = buildSamplingInput(sampling, initVal, remaining);
      $inpNow.attr('data-live-max', remaining);
      $cell.append($inpNow);

      $inpNow.off('input.__dynmax change.__dynmax')
        .on('input.__dynmax change.__dynmax', debounce(() => {
          const opLocal = op;
          const sNow = parseInt(ctx.modal.$samplingResult.val()) || 0;

          ctx.modal.$rowsContainer.find('tr').each(function() {
            const $r = $(this);
            if ($r[0] === $row[0]) return;

            const isSaved = !!$r.attr('data-id');
            if (isSaved) return;

            const tRow = String($r.find('select[name="insp_type[]"]').val() || '').toUpperCase();
            if (tRow !== 'IPI') return;

            const opRow = $r.find('select[name="operation[]"], input[name="operation[]"]').val() || '';
            if (opRow !== opLocal) return;

            const $c = $r.find('td.col-sample');
            const cur = $c.find('input[name="sample_idx[]"]').val() || null;
            renderSampleCell($c.empty(), 'IPI', sNow, cur, opLocal);
          });
        }, 120));
    }

    function createOperationSelect(totalOps, inspType = 'FAI', preferredOp = null) {
      const $sel = $('<select name="operation[]" class="form-control"></select>');
      const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;
      const ipiReq = Math.max(0, sampling - 1);

      const ops = [];
      for (let i = 1; i <= totalOps; i++) ops.push(ordinalSuffix(i));

      if (preferredOp && ops.includes(preferredOp)) {
        const idx = ops.indexOf(preferredOp);
        if (idx > -1) ops.splice(idx, 1);
        ops.unshift(preferredOp);
      }

      const isFAI = String(inspType).toUpperCase() === 'FAI';
      for (const value of ops) {
        let label = value;
        let isDone = false;

        if (isFAI) {
          if (ctx.faiDoneOps.has(value)) {
            label += ' (done)';
            isDone = true;
          }
        } else {
          const ipiCount = ctx.ipiCountMap.get(value) || 0;
          if (ipiCount >= ipiReq) {
            label += ' (done)';
            isDone = true;
          }
        }

        // Ya NO deshabilitamos si está done
        $sel.append(`<option value="${value}" ${isDone ? 'data-done="1"' : ''}>${label}</option>`);
      }

      return $sel;
    }

    function getNextInspectionPair(totalOps) {
      const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;
      const ipiReq = Math.max(0, sampling - 1);

      const faiSum = new Map();
      const ipiSum = new Map();

      ctx.modal.$rowsContainer.find('tr[data-id]').each(function() {
        const $r = $(this);
        const type = String($r.find('select[name="insp_type[]"]').val() || '').toUpperCase();
        const theOp = $r.find('select[name="operation[]"], input[name="operation[]"]').val() || '';
        const res = String($r.find('select[name="results[]"]').val() || '').toLowerCase();
        if (!theOp || res !== 'pass') return;

        const qty = getRowQty($r);

        if (type === 'FAI') faiSum.set(theOp, (faiSum.get(theOp) || 0) + qty);
        if (type === 'IPI') ipiSum.set(theOp, (ipiSum.get(theOp) || 0) + qty);
      });

      for (let i = 1; i <= totalOps; i++) {
        const op = ordinalSuffix(i);
        if ((faiSum.get(op) || 0) < 1) return {
          type: 'FAI',
          op
        };
        if ((ipiSum.get(op) || 0) < ipiReq) return {
          type: 'IPI',
          op
        };
      }
      return null;
    }

    function renderOrderProgress(orderId, percent) {
      const $wrap = $(`.progress[data-order-id="${orderId}"]`);
      if (!$wrap.length) return;
      const $bar = $wrap.find('.progress-bar');
      if (!$bar.length) return;
      $bar.attr('aria-valuenow', percent).css('width', percent + '%').text(percent + '%');
      $bar.removeClass('bg-secondary bg-danger bg-warning bg-success');
      if (percent >= 100) $bar.addClass('bg-success');
      else if (percent >= 50) $bar.addClass('bg-warning');
      else $bar.addClass('bg-danger');
    }

    function computeProgressFromRows(rows, operations, ipiRequired) {
      if (!operations || operations < 1) return 0;

      const faiMap = new Map(),
        ipiMap = new Map();

      (rows || []).forEach(r => {
        const type = (r.insp_type || '').toUpperCase();
        const op = r.operation;
        const res = (r.results || '').toLowerCase();
        if (!op || res !== 'pass') return;

        const qty = parseInt(r.qty_pcs ?? r.sample_idx ?? 1, 10) || 0;

        if (type === 'FAI') faiMap.set(op, (faiMap.get(op) || 0) + qty);
        if (type === 'IPI') ipiMap.set(op, (ipiMap.get(op) || 0) + qty);
      });

      const need = Math.max(0, parseInt(ipiRequired, 10) || 0); // aquí llegará sampling-1
      const perOpReq = 1 + need; // = sampling total por op
      const totalReq = operations * perOpReq;

      let done = 0;
      for (let i = 1; i <= operations; i++) {
        const op = ordinalSuffix(i);
        const faiSum = faiMap.get(op) || 0;
        const ipiSum = ipiMap.get(op) || 0;
        done += Math.min(faiSum, 1) + Math.min(ipiSum, need);
      }

      const pct = totalReq > 0 ? Math.round((done / totalReq) * 100) : 0;
      return Math.max(0, Math.min(pct, 100));
    }

    function refreshProgress(orderId, operations, ipiRequired) {
      if (!operations) operations = parseInt($('#operationInput').val()) || 0;

      if (ipiRequired === undefined || ipiRequired === null) {
        const sampling = parseInt($('#edit-sampling-result').val()) || 0;
        ipiRequired = Math.max(0, sampling - 1); // 👈 enviar sampling-1
      }

      fetchJson(ROUTES.faibyOrder(orderId)).then(rows => {
        rows = Array.isArray(rows) ? rows : [];
        renderOrderProgress(orderId, computeProgressFromRows(rows, operations, ipiRequired));
      });
    }

    // ================== Fila: borrador y desde DB ==================
    function createDraftRow() {
      const today = new Date().toISOString().split('T')[0];
      const totalOps = parseInt(ctx.modal.$operationInput.val());
      const isNumber = !isNaN(totalOps) && totalOps > 0;
      const orderId = $('#order-id').val();
      const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;

      const $row = $('<tr></tr>');
      $row.append(`<td><input type="date" name="date[]" class="form-control" value="${today}"></td>`);

      const $inspType = $(`
        <select name="insp_type[]" class="form-control">
          <option value="FAI">FAI</option>
          <option value="IPI">IPI</option>
        </select>
      `);
      $row.append($('<td></td>').append($inspType));

      const $opCell = $('<td></td>');
      const $sampleCell = $('<td class="col-sample"></td>');
      let defaultType = 'FAI';
      let preferredOp = null;

      if (isNumber) {
        const suggestion = getNextInspectionPair(totalOps);
        if (suggestion) {
          defaultType = suggestion.type;
          preferredOp = suggestion.op;
        }

        $inspType.val(defaultType);
        const opSel = createOperationSelect(totalOps, defaultType, preferredOp);
        if (opSel.children().length === 0) {
          Swal.fire({
            icon: 'info',
            title: 'No operations available',
            text: 'All inspections for FAI and IPI have now been completed.'
          });
          return null;
        }
        $opCell.append(opSel);
      } else {
        $opCell.append('<input type="text" name="operation[]" class="form-control">');
      }

      $row.append($opCell);
      $row.append(buildOperatorInputCell(orderId));
      $row.append(`
        <td>
          <select name="results[]" class="form-control">
            <option value="pass">Pass</option>
            <option value="no pass">No Pass</option>
          </select>
        </td>`);
      $row.append(`<td><input type="text" name="sb_is[]" class="form-control"></td>`);
      $row.append(`<td><input type="text" name="observation[]" class="form-control"></td>`);
      $row.append(buildStationInputCell(orderId));
      $row.append(`
        <td>
          <select name="method[]" class="form-control">
            ${['Manual','Vmm/Manual','Visual','Vmm','Keyence','Keyence/Manual'].map(m=>`<option value="${m}">${m}</option>`).join('')}
          </select>
        </td>`);

      renderSampleCell($sampleCell, $inspType.val(), sampling, null, preferredOp);
      $row.append($sampleCell);

      $row.append(`
        <td>
          <button type="button" class="btn btn-success btn-sm saveRowBtn me-1"><i class="fas fa-save"></i></button>
          <button type="button" class="btn btn-danger btn-sm removeRowBtn"><i class="fas fa-minus"></i></button>
        </td>`);

      $inspType.on('change', function() {
        if (!isNumber) return;
        const newType = $(this).val();

        let preferredOpForType = null;
        const suggestion = getNextInspectionPair(totalOps);
        if (suggestion && suggestion.type === newType) preferredOpForType = suggestion.op;

        const newOpSel = createOperationSelect(totalOps, newType, preferredOpForType);
        $opCell.empty().append(newOpSel);

        const samplingNow = parseInt(ctx.modal.$samplingResult.val()) || 0;
        const opNow = newOpSel.val() || preferredOpForType || null;
        renderSampleCell($sampleCell.empty(), newType, samplingNow, null, opNow);

        newOpSel.on('change', function() {
          const opX = $(this).val() || null;
          const sNow = parseInt(ctx.modal.$samplingResult.val()) || 0;
          const cur = $sampleCell.find('input[name="sample_idx[]"]').val() || null;
          renderSampleCell($sampleCell.empty(), newType, sNow, cur, opX);
        });
      });

      const $opSel = $opCell.find('select[name="operation[]"]');
      $opSel.on('change', function() {
        const tNow = $inspType.val();
        const sNow = parseInt(ctx.modal.$samplingResult.val()) || 0;
        const opNow = $(this).val() || null;
        const cur = $sampleCell.find('input[name="sample_idx[]"]').val() || null;
        renderSampleCell($sampleCell.empty(), tNow, sNow, cur, opNow);
      });

      return $row;
    }

    function createRowFromData(data, orderId) {
      const $row = $('<tr></tr>').attr('data-id', data.id);

      const savedQty = parseInt(data.qty_pcs ?? data.sample_idx ?? 1, 10) || 1;
      $row.attr('data-qty_pcs', savedQty);

      const dateOnly = data.date ? data.date.split(' ')[0] : '';
      $row.append(`
  <td>
    <input type="date" name="date[]" class="form-control" value="${dateOnly}" disabled>
  </td>
`);

      $row.append(`
        <td>
          <select name="insp_type[]" class="form-control" disabled>
            <option value="FAI" ${data.insp_type === 'FAI' ? 'selected' : ''}>FAI</option>
            <option value="IPI" ${data.insp_type === 'IPI' ? 'selected' : ''}>IPI</option>
          </select>
        </td>`);
      $row.append(`<td><input type="text" name="operation[]"  class="form-control" value="${data.operation || ''}"  disabled></td>`);
      $row.append(buildOperatorInputCell(orderId, data.operator || '', true));

      const results = (data.results || '').toLowerCase();
      $row.append(`
        <td>
          <select name="results[]" class="form-control" disabled>
            <option value="pass" ${results === 'pass' ? 'selected' : ''}>Pass</option>
            <option value="no pass" ${results === 'no pass' ? 'selected' : ''}>No Pass</option>
          </select>
        </td>`);
      $row.append(`<td><input type="text" name="sb_is[]"       class="form-control" value="${data.sb_is || ''}"       disabled></td>`);
      $row.append(`<td><input type="text" name="observation[]" class="form-control" value="${data.observation || ''}" disabled></td>`);
      $row.append(buildStationInputCell(orderId, data.station || '', true));
      $row.append(`
        <td>
          <select name="method[]" class="form-control" disabled>
            ${['Manual','Vmm/Manual','Visual','Vmm','Keyence','Keyence/Manual'].map(m =>
              `<option value="${m}" ${data.method === m ? 'selected' : ''}>${m}</option>`).join('')}
          </select>
        </td>`);

      const sampling = parseInt(ctx.modal.$samplingResult?.val?.() || 0, 10) || 0;
      const $sampleCell = $('<td class="col-sample"></td>');

      renderSampleCell($sampleCell, data.insp_type, sampling, savedQty, data.operation);
      $row.append($sampleCell);

      const $inp = $sampleCell.find('input[name="sample_idx[]"]').not('.sample-fixed');
      if ($inp.length) {
        $inp.val(String(savedQty)).prop('disabled', true);
      } else {
        $sampleCell.find('input.sample-fixed').prop('disabled', true);
      }

      $row.append(`
        <td>
          <button type="button" class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
          <button type="button" class="btn btn-warning btn-sm editRowBtn me-1"><i class="fas fa-edit"></i></button>
          <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
        </td>`);

      return $row;
    }

    /* FIX: siempre llama cb en .always() */
    function loadFaiRows(orderId, cb) {
      fetchJson(ROUTES.faibyOrder(orderId)).then(rows => {
        (Array.isArray(rows) ? rows : []).forEach(r =>
          ctx.modal.$rowsContainer.append(createRowFromData(r, orderId))
        );
      }).always(() => {
        if (typeof cb === 'function') cb();
      });
    }

    function refreshAllSamplingSelects() {
      const sampling = parseInt(ctx.modal.$samplingResult?.val?.() || $('#edit-sampling-result').val() || 0, 10) || 0;
      if (!ctx.modal.$rowsContainer || !ctx.modal.$rowsContainer.length) return;

      ctx.modal.$rowsContainer.find('tr').each(function() {
        const $row = $(this);
        const isSaved = !!$row.attr('data-id');
        if (isSaved) return;

        const type = String($row.find('select[name="insp_type[]"]').val() || '').toUpperCase();
        const $cell = $row.find('td.col-sample');
        if (!type || !$cell.length) return;

        if (type === 'FAI') {
          renderSampleCell($cell.empty(), 'FAI', sampling);
          return;
        }

        const op = $row.find('select[name="operation[]"], input[name="operation[]"]').val() || null;
        const current = (function() {
          const v = $cell.find('input[name="sample_idx[]"]').val();
          return v !== undefined ? v : null;
        })();

        renderSampleCell($cell.empty(), 'IPI', sampling, current, op);
      });
    }

    function refreshPendingIpiOptions() {
      const sampling = parseInt(ctx.modal.$samplingResult?.val?.() || $('#edit-sampling-result').val() || 0, 10) || 0;
      if (!ctx.modal.$rowsContainer || !ctx.modal.$rowsContainer.length) return;

      ctx.modal.$rowsContainer.find('tr').each(function() {
        const $row = $(this);
        const isSaved = !!$row.attr('data-id');
        const type = String($row.find('select[name="insp_type[]"]').val() || '').toUpperCase();
        if (isSaved || type !== 'IPI') return;

        const op = $row.find('select[name="operation[]"], input[name="operation[]"]').val() || '';
        const $cell = $row.find('td.col-sample');
        const current = $cell.find('input[name="sample_idx[]"]').val() || null;

        renderSampleCell($cell.empty(), type, sampling, current, op);
      });
    }

    // ================== Datalist (stations/operators) ==================
    const RAW_CACHE = {
      stations: new Map(),
      operators: new Map()
    }; // orderId -> raw[]
    const UNIQ_CACHE = {
      stations: new Map(),
      operators: new Map()
    }; // orderId -> [string]
    const INFLIGHT = {
      stations: new Map(),
      operators: new Map()
    }; // orderId -> Promise
    let __DL_COUNTER = 0;

    function fetchListByOrder(kind, orderId) {
      if (!orderId) return Promise.resolve([]);
      const raw = RAW_CACHE[kind],
        inflight = INFLIGHT[kind];
      if (raw.has(orderId)) return Promise.resolve(raw.get(orderId));
      if (inflight.has(orderId)) return inflight.get(orderId);

      const url = (kind === 'stations') ? ROUTES.stationsByOrder(orderId) : ROUTES.operatorsByOrder(orderId);
      const p = $.ajax({
          url,
          method: 'GET',
          dataType: 'json',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(list => {
          const arr = Array.isArray(list) ? list : [];
          raw.set(orderId, arr);
          const field = (kind === 'stations') ? 'station' : 'operator';
          const uniq = [...new Set(arr.map(r => (r[field] || '').trim()))].filter(Boolean).sort(COLLATOR.compare);
          UNIQ_CACHE[kind].set(orderId, uniq);
          return arr;
        })
        .catch(() => {
          raw.set(orderId, []);
          UNIQ_CACHE[kind].set(orderId, []);
          return [];
        })
        .always(() => inflight.delete(orderId));

      inflight.set(orderId, p);
      return p;
    }

    function getUniqStrings(kind, orderId) {
      if (UNIQ_CACHE[kind].has(orderId)) return UNIQ_CACHE[kind].get(orderId);
      const raw = RAW_CACHE[kind].get(orderId) || [];
      const field = (kind === 'stations') ? 'station' : 'operator';
      const uniq = [...new Set(raw.map(r => (r[field] || '').trim()))].filter(Boolean).sort(COLLATOR.compare);
      UNIQ_CACHE[kind].set(orderId, uniq);
      return uniq;
    }

    function makeDatalistCellFactory(kind) {
      const inputName = (kind === 'stations') ? 'station[]' : 'operator[]';
      return function buildDatalistCell(orderId, value = '', disabled = false) {
        const dlId = `${kind}-${orderId}-${++__DL_COUNTER}`;
        const $td = $('<td></td>');
        const $in = $(`<input name="${inputName}" class="form-control" list="${dlId}">`)
          .val(value || '').prop('disabled', !!disabled);
        const $dl = $(`<datalist id="${dlId}"></datalist>`);
        $td.append($in, $dl);

        if (!orderId) {
          $in.prop('disabled', true).attr('placeholder', 'Sin orden');
          return $td;
        }
        const renderList = (arr = []) => {
          const frag = document.createDocumentFragment();
          arr.slice(0, 50).forEach(s => {
            const opt = document.createElement('option');
            opt.value = s;
            frag.appendChild(opt);
          });
          $dl.empty()[0].appendChild(frag);
        };

        const cached = getUniqStrings(kind, orderId);
        if (cached.length) renderList(cached);

        fetchListByOrder(kind, orderId).then(() => {
          const all = getUniqStrings(kind, orderId);
          renderList(all);
          const onInput = debounce(() => {
            const term = ($in.val() || '').toLowerCase();
            if (!term) return renderList(all);
            renderList(all.filter(s => s.toLowerCase().includes(term)));
          }, 120);
          $in.off(`input.__${kind}`).on(`input.__${kind}`, onInput);
        });

        return $td;
      };
    }

    const buildStationInputCell = makeDatalistCellFactory('stations');
    const buildOperatorInputCell = makeDatalistCellFactory('operators');

    // ================== API: setInspectionStatus ==================
    function setInspectionStatus(orderId, status) {
      return $.ajax({
        url: ROUTES.statusInspection(orderId),
        method: 'PUT',
        data: {
          _token: getCsrf(),
          status_inspection: status
        }
      });
    }
  })();

  /* ===== Botón Finish Inspection (fuera del IIFE para mantener tu organización) ===== */
  $(document).on('click', '#btnFinishInspection', function(e) {
    e.preventDefault();

    const orderId = $('#edit-id').val();
    if (!orderId) {
      Swal.fire('Error', 'No order selected.', 'error');
      return;
    }

    Swal.fire({
      title: 'Finish Inspection?',
      text: "The inspection will change status to 'Completed'.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#aaa',
      confirmButtonText: 'Yes, Complete'
    }).then((result) => {
      if (!result.isConfirmed) return;

      /* FIX: usa getCsrf() para consistencia */
      fetch(`/orders-schedule/${orderId}/status-inspection`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': ($('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content') || '')
          },
          body: JSON.stringify({
            status_inspection: 'completed'
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Updated!', 'Inspection marked as Completed.', 'success')
              .then(() => {
                $('#editModal').modal('hide');
                // 🔄 Refresca la tabla SOLO si es DataTable AJAX
                const $t = $('#faicompleteTable');
                if ($.fn.DataTable && $.fn.DataTable.isDataTable($t)) {
                  const api = $t.DataTable();
                  const hasAjax = !!api.settings()[0].ajax;
                  if (hasAjax && api.ajax) api.ajax.reload(null, false);
                }
              });
          } else {
            Swal.fire('Error', 'Could not update status.', 'error');
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error', 'There was a server problem.', 'error');
        });
    });
  });
</script>






@endpush

{{-- Modal para mostrar PDF de resumen In Process --}}
<div class="modal fade" id="processPdfModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title mb-0">Resumen In Process (PDF)</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-2">
        <iframe id="processPdfFrame" src="about:blank" style="width: 100%; height: 78vh; border: none;" title="Resumen In Process"></iframe>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm btn-erp" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Close
        </button>
        <button type="button" class="btn btn-primary btn-sm btn-erp" id="printProcessPdfBtn">
          <i class="fas fa-print mr-1"></i> Print
        </button>
      </div>
    </div>
  </div>
</div>
