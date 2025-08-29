<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary')

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

@section('content')

{{-- Tabs --}}
@include('qa.faisummary.faisummary_tab')

<div class="card mb-4">
  <div class="card-header">
    <h5>Parts in Inspection</h5>
  </div>
  <div class="card-body">
    <div class="row">
      <!-- Columna izquierda: primer filtro + botón + gráfica -->
      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header">Pendientes</div>
          <div class="card-body">
            <table id="ordersTableEmpty" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>PART/DESCRIPCIÓN</th>
                  <th>JOB</th>
                  <th>ACTIONS</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
      <!-- Columna derecha: segundo filtro + botón + gráfica -->
      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header">En proceso</div>
          <div class="card-body">
            <table id="ordersTableProcess" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>PART/DESCRIPCIÓN</th>
                  <th>JOB</th>
                  <th>Progress</th>
                  <th>ACTIONS</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



@include('qa.faisummary.faisummary_modal')

<!--  {{-- Tab: By End Schedule --}}-->

@endsection


@section('css')

@endsection


@push('js')
<script src="{{ asset('vendor/js/faisummary-ui.js') }}"></script>
<script>
  (() => {
    // ================== Config & Utils ==================
    const ROUTES = {
      partsData: "{{ route('faisummary.partsrevision.data') }}", // GET ?bucket=empty|process
      samplingPlan: (lot, type = 'Normal') => `/sampling-plan?lot_size=${lot}&sampling_type=${encodeURIComponent(type)}`,
      faibyOrder: id => `/qa/faisummary/by-order/${id}`, // GET
      validateOps: (id, ops) => `/orders-schedule/${id}/validate-ops?ops=${encodeURIComponent(ops)}`,
      updateOps: id => `/orders-schedule/${id}/update-operation`, // POST
      statusInspection: id => `/orders-schedule/${id}/status-inspection`, // PUT
      storeSingle: `/qa/faisummary/store-single`, // POST
      deleteRow: id => `/qa/faisummary/delete/${id}`, // DELETE
      stationsByOrder: id => `/stations/by-order/${id}`, // GET
      operatorsByOrder: id => `/operators/by-order/${id}` // GET
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

    // ================== Estado de módulo ==================
    const ctx = {
      // DataTables
      dtEmpty: null,
      dtProcess: null,
      // cache sampling por order (tabla)
      tableSamplingCache: new Map(), // orderId -> sample_qty

      // Modal “activo”
      modal: {
        $rowsContainer: null,
        $samplingResult: null,
        $operationInput: null,
        $reportPre: null,
        $reportBox: null,
        $woqty: null
      },

      // Contadores dentro del modal
      faiDoneOps: new Set(), // operaciones con FAI OK
      ipiCountMap: new Map() // op -> suma qty_pcs (solo filas GUARDADAS con PASS)
    };

    // ================== DataTables ==================
    const COLUMNS = {
      process: [{
          data: 'part'
        },
        {
          data: 'work_id'
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          render: (data, type, row) => `
        <div class="progress" data-order-id="${row.id}" style="height:18px;">
          <div class="progress-bar bg-secondary" role="progressbar"
               style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <small class="text-muted d-block">
          <span class="badge bg-light text-dark me-1">FAI + IPI</span>
        </small>`
        },
        {
          data: 'actions',
          orderable: false,
          searchable: false
        }
      ],
      empty: [{
          data: 'part'
        },
        {
          data: 'work_id'
        },
        {
          data: 'actions',
          orderable: false,
          searchable: false
        }
      ]
    };

    function makeDT(bucket) {
      return {
        responsive: true,
        deferRender: true,
        stateSave: true,
        lengthMenu: [5, 10, 25, 50, 100],
        pageLength: 10,
        order: [
          [1, 'desc']
        ],
        ajax: {
          url: ROUTES.partsData,
          data: {
            bucket
          },
          dataSrc: 'data'
        },
        columns: COLUMNS[bucket],
        rowId: 'id',
        drawCallback: function() {
          if (bucket !== 'process') return;
          const api = this.api();
          api.rows({
            page: 'current'
          }).every(function() {
            const row = this.data();
            const orderId = row?.id;
            const ops = parseInt(row?.ops) || 0;
            const lotSize = parseInt(row?.wo_qty) || 0;
            if (!orderId || !ops || !lotSize) return;

            const useProgress = (ipiReq) => refreshProgress(orderId, ops, ipiReq);

            if (ctx.tableSamplingCache.has(orderId)) {
              useProgress(ctx.tableSamplingCache.get(orderId));
            } else {
              $.getJSON(ROUTES.samplingPlan(lotSize, 'Normal')).done(resp => {
                const ipiReq = (resp && resp.sample_qty !== undefined) ? resp.sample_qty : 0;
                ctx.tableSamplingCache.set(orderId, ipiReq);
                useProgress(ipiReq);
              });
            }
          });
        }
      };
    }

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

      const pn = button.data('pn');
      const desc = button.data('description') || '';
      $modal.find('#edit-fullpart').val(`${pn} - ${desc.split(',')[0]}`);

      // Limpiar tbody
      ctx.modal.$rowsContainer.empty();

      // Cargar filas guardadas + armar reporte y progreso
      const orderId = id;
      loadFaiRows(orderId, () => {
        updateInspectionMissing();
      });

      updateSamplingQty();
      updateInspectionMissing();

      const opsNow = parseInt(ctx.modal.$operationInput.val()) || 0;
      const ipiNow = parseInt(ctx.modal.$samplingResult.val()) || 0;

      // Habilitar / deshabilitar Agregar fila
      $('#addRowBtn').prop('disabled', opsNow === 0);
      refreshProgress(orderId, opsNow, ipiNow);
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

    // ================== Inicialización DTs ==================
    $(document).ready(() => {
      ctx.dtEmpty = $('#ordersTableEmpty').DataTable(makeDT('empty'));
      ctx.dtProcess = $('#ordersTableProcess').DataTable(makeDT('process'));
    });

    // ================== Eventos del modal ==================
    // Cambios en sampling (tipo/cantidad) y WO_QTY
    $('#editModal').on('change input', '#edit-sampling-type, #edit-woqty', () => {
      updateSamplingQty();
    });

    /*=================== Guardar # de operaciones============================*/
    $('#editModal').on('click', '#addOperationBtn', function() {
      const orderId = $('#order-id').val();
      const operation = parseInt(ctx.modal.$operationInput.val().trim()) || 0;
      const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;
      const samplingType = $('#edit-sampling-type').val();

      const total_fai = operation * 1;
      const total_ipi = operation * sampling;

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
              refreshProgress(orderId, operation, sampling);
              updateInspectionMissing();

              if (ctx.dtEmpty) ctx.dtEmpty.ajax.reload(null, false);
              if (ctx.dtProcess) ctx.dtProcess.ajax.reload(null, false);
              swalOk('¡Upodated!', 'Operation saved successfully');
            });
        })
        .fail(() => swalError('Error', 'The operation could not be updated.'));
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
          swalError('WO Qty requerido', 'Captura un WO Qty válido para calcular el muestreo.');
          return;
        }
        $.get(ROUTES.samplingPlan(lotSize, samplingType))
          .then((resp) => {
            const n = toSamplingNumber(resp);
            if (!Number.isFinite(n)) {
              swalError('Plan inválido', 'No se pudo calcular el tamaño de muestra para ese tipo.');
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
            if (typeof refreshProgress === 'function') refreshProgress(orderId, operation, sampling);
            if (typeof updateInspectionMissing === 'function') updateInspectionMissing();
            if (ctx.dtEmpty) ctx.dtEmpty.ajax.reload(null, false);
            if (ctx.dtProcess) ctx.dtProcess.ajax.reload(null, false);
            swalOk('¡Updated!', `Sampling type & sampling saved successfully`);
          })
          .fail((xhr) => {
            if (xhr !== 'invalid-sampling') {
              console.warn('Sampling-type change failed:', xhr?.status, xhr?.responseText);
              swalError('Error', 'No se pudo guardar el cambio de sampling.');
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
      refreshAllSamplingSelects(); // recalcular límites tras remover
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

      $.post(ROUTES.storeSingle, payload)
        .done(resp => {
          if (resp?.id) $row.attr('data-id', resp.id);

          // mantener qty de la fila para futuras ediciones
          if (inspType === 'IPI') $row.attr('data-qty_pcs', sampleIdx);

          $row.find('input, select, .saveRowBtn').prop('disabled', true);
          $row.find('input.sample-fixed').prop('disabled', true);

          updateInspectionMissing();
          refreshAllSamplingSelects(); // recalcular límites tras guardar

          $row.find('td:last').html(`
          <button type="button" class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
          <button type="button" class="btn btn-warning btn-sm editRowBtn me-1"><i class="fas fa-edit"></i></button>
          <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
        `);

          const opsNow = parseInt(ctx.modal.$operationInput.val()) || 0;
          const ipiNow = parseInt(ctx.modal.$samplingResult.val()) || 0;

          $.get(ROUTES.faibyOrder(orderId)).done(rows => {
            const pct = computeProgressFromRows(rows, opsNow, ipiNow);
            renderOrderProgress(orderId, pct);

            if (pct >= 100) {
              Swal.fire({
                icon: 'success',
                title: '¡Inspection completed!',
                text: `1 FAI was completed and ${ipiNow} IPI for each of the ${opsNow} operations.`,
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
              if (Array.isArray(rows) && rows.length > 0) {
                setInspectionStatus(orderId, 'in_progress').fail(xhr => console.warn('status process fail:', xhr?.status));
              }
              swalOk('¡Saved!', 'The inspection was saved successfully');
              if (ctx.dtEmpty) ctx.dtEmpty.ajax.reload(null, false);
              if (ctx.dtProcess) ctx.dtProcess.ajax.reload(null, false);
            }
          });
        })
        .fail(xhr => {
          const msg = xhr.responseJSON?.error ? `Error: ${xhr.responseJSON.error}` : 'Error al guardar la fila';
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
            const ipiNow = parseInt(ctx.modal.$samplingResult.val()) || 0;

            if (orderId) {
              $.get(ROUTES.faibyOrder(orderId)).done(rows => {
                const pct = computeProgressFromRows(rows, opsNow, ipiNow);
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
    function updateSamplingQty() {
      const lotSize = parseInt($('#edit-woqty').val());
      const type = $('#edit-sampling-type').val();
      if (!lotSize || lotSize < 1) {
        ctx.modal.$samplingResult.val('');
        refreshAllSamplingSelects(); // deshabilita inputs si aplica
        return;
      }
      $.getJSON(ROUTES.samplingPlan(lotSize, type)).done(data => {
        const sample = (data?.sample_qty ?? 0);
        ctx.modal.$samplingResult.val(sample);

        // refrescar inputs IPI (no FAI)
        refreshAllSamplingSelects();
        // recalcular pendientes por operación en borradores
        refreshPendingIpiOptions();

        const orderId = $('#order-id').val();
        const opsNow = parseInt(ctx.modal.$operationInput.val()) || 0;
        if (orderId && opsNow) {
          refreshProgress(orderId, opsNow, sample);
          updateInspectionMissing();
        }
      });
    }

    function updateInspectionMissing() {
      const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;
      const operations = parseInt(ctx.modal.$operationInput.val()) || 0;

      const $box = ctx.modal.$reportBox;
      const $pre = ctx.modal.$reportPre;

      if (!operations) {
        $pre.text('');
        $box.removeClass('bg-success bg-warning text-white');
        return;
      }

      // Mapas separados para PASS y NO PASS
      const faiPassMap = new Map(),
        faiFailMap = new Map(),
        ipiPassMap = new Map(),
        ipiFailMap = new Map();

      ctx.faiDoneOps.clear();
      ctx.ipiCountMap.clear();

      // Contar filas guardadas (pass y no pass)
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

      // Actualiza caches SOLO con PASS
      for (const [op, sum] of faiPassMap.entries())
        if (sum >= 1) ctx.faiDoneOps.add(op);
      for (const [op, sum] of ipiPassMap.entries())
        ctx.ipiCountMap.set(op, sum);

      // Reporte por operación contra requeridos
      let resumen = '';
      let faltantes = false;

      for (let i = 1; i <= operations; i++) {
        const op = ordinalSuffix(i);
        const faiPass = faiPassMap.get(op) || 0;
        const faiFail = faiFailMap.get(op) || 0;
        const ipiPass = ipiPassMap.get(op) || 0;
        const ipiFail = ipiFailMap.get(op) || 0;

        const faiReq = 1;
        const ipiReq = sampling;

        const faiRealizadosOp = faiPass + faiFail; // ✅ por operación
        const ipiRealizadosOp = ipiPass + ipiFail; // ✅ por operación

        const faiStatus = (faiPass >= faiReq) ?
          `✔️ <strong>FAI:</strong> P:(${faiPass}/${faiReq}), NP:${faiFail}, Done:${faiRealizadosOp})` :
          `❌ <strong>FAI:</strong>P:(${faiPass}/${faiReq}), NP:${faiFail}, Done:${faiRealizadosOp})`;

        const ipiStatus = (ipiPass >= ipiReq) ?
          `✔️ <strong>IPI:</strong> P:(${ipiPass}/${ipiReq}), NP:${ipiFail}, Done:${ipiRealizadosOp})` :
          `❌ <strong>IPI:</strong> P:(${ipiPass}/${ipiReq}), NP:${ipiFail}, Done:${ipiRealizadosOp})`;

        // Ícono global
        let globalIcon = '⚠️';
        if (faiPass >= faiReq && ipiPass >= ipiReq) globalIcon = '✔️';
        else if (faiPass < faiReq && ipiPass < ipiReq) globalIcon = '❌';

        const line = `${globalIcon} ${op} | ${faiStatus} | ${ipiStatus}`;

        resumen += line + '\n';
        if (faiPass < faiReq || ipiPass < ipiReq) faltantes = true;

      }

      // ====== Totales generales ======
      const sumMap = m => Array.from(m.values()).reduce((a, b) => a + b, 0);

      const faiPassTotal = sumMap(faiPassMap);
      const faiFailTotal = sumMap(faiFailMap);
      const ipiPassTotal = sumMap(ipiPassMap);
      const ipiFailTotal = sumMap(ipiFailMap);

      const faiRealizados = faiPassTotal + faiFailTotal;
      const ipiRealizados = ipiPassTotal + ipiFailTotal;

      const faiReqTotal = operations * 1; // 1 FAI por operación
      const ipiReqTotal = operations * sampling; // Sampling por operación

      // ✅ Porcentaje SOLO con PASS
      const faiPct = faiReqTotal ? ((faiPassTotal / faiReqTotal) * 100).toFixed(1) : '0.0';
      const ipiPct = ipiReqTotal ? ((ipiPassTotal / ipiReqTotal) * 100).toFixed(1) : '0.0';

      resumen += '\n<strong>— FAI/IPI Inspection Packet Summary —</strong>\n';
      resumen += `FAI → P:${faiPassTotal}, NP:${faiFailTotal}, Need:${faiReqTotal}, Done:${faiRealizados} (${faiPct}%)\n`;
      resumen += `IPI → P:${ipiPassTotal}, NP:${ipiFailTotal}, Need:${ipiRealizados}, Done:${ipiReqTotal} (${ipiPct}%)\n`;

      // Pintar caja según faltantes por operación
      $pre.html(resumen.trim().replace(/\n/g, "<br>"));
      $box.removeClass('bg-success bg-warning text-white');
      if (faltantes) $box.addClass('bg-warning text-white');
      else $box.addClass('bg-success text-white');

      // al finalizar, refresca pendientes IPI en borradores
      refreshPendingIpiOptions();
    }


    // ================== Helpers varios ==================
    function ordinalSuffix(n) {
      if (n === 1) return '1st Op';
      if (n === 2) return '2nd Op';
      if (n === 3) return '3rd Op';
      return `${n}th Op`;
    }

    // Lee la cantidad de piezas de la fila (prioriza qty_pcs; cae a sample_idx; default 1)
    function getRowQty($row) {
      const attr = parseInt(
        $row.attr('data-qty_pcs') ?? $row.data('qty_pcs') ?? $row.data('qty') ?? '', 10
      );
      if (!isNaN(attr)) return attr;

      const q1 = parseInt($row.find('input[name="qty_pcs[]"]').val() ?? '', 10);
      if (!isNaN(q1)) return q1;

      const q2 = parseInt($row.find('input[name="sample_idx[]"]').val() ?? '', 10);
      if (!isNaN(q2)) return q2;

      const q3 = parseInt($row.find('select[name="sample_idx[]"]').val() ?? '', 10);
      if (!isNaN(q3)) return q3;

      return 1;
    }

    // === Util: obtener WO_QTY del modal ===
    function getWoQty() {
      const raw = (ctx?.modal?.$woqty?.length ? ctx.modal.$woqty.val() : $('#edit-woqty').val());
      const n = parseInt(raw, 10);
      return Number.isFinite(n) && n > 0 ? n : 0;
    }

    // ====== Helpers para restante dinámico por operación (WO_QTY - guardadas - borradores) ======
    function getDraftIpiSumForOp(op, $excludeRow = null) {
      let sum = 0;
      ctx.modal.$rowsContainer.find('tr').each(function() {
        const $r = $(this);
        if ($excludeRow && $r[0] === $excludeRow[0]) return; // excluye esta fila
        const isSaved = !!$r.attr('data-id');
        if (isSaved) return; // solo borradores
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

      const saved = ctx.ipiCountMap.get(op) || 0; // sumatoria de GUARDADAS (pass)
      const drafts = getDraftIpiSumForOp(op, $rowCtx); // sumatoria de borradores EXCLUYENDO $rowCtx
      const isSavedRow = $rowCtx && !!$rowCtx.attr('data-id');

      // Usado excepto esta fila:
      const usedExceptThis = saved + drafts - (isSavedRow ? (parseInt(currentRowQty, 10) || 0) : 0);

      return Math.max(0, wo - usedExceptThis);
    }


    // === MOD: input de muestra con tope dinámico (remaining) por operación ===
    function buildSamplingInput(sampling, currentVal = null, maxAllowed = null) {
      const woMax = getWoQty();
      const s = Math.max(0, parseInt(sampling) || 0);
      const upper = Math.max(0, maxAllowed ?? woMax ?? s); // prioridad: maxAllowed (remaining) > WO_QTY > sampling

      if (upper === 0) {
        return $(`<input type="number" name="sample_idx[]" class="form-control" disabled>`);
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

    // === MOD: usa restante dinámico por operación (WO_QTY - guardadas - borradores) como tope ===
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

      // 👇 restante dinámico por operación (WO_QTY − guardadas − borradores)
      const remaining = getIpiRemainingForOpByWo(op, curQty, $row);

      if (remaining === 0) {
        $cell.append($(`<input type="number" name="sample_idx[]" class="form-control" min="0" max="0" value="0" disabled>`));
        return;
      }

      let initVal = curQty || 1;
      if (initVal > remaining) initVal = remaining;
      if (initVal < 1) initVal = 1;

      const $inpNow = buildSamplingInput(sampling, initVal, remaining);
      // (opcional) útil para depurar:
      $inpNow.attr('data-live-max', remaining);
      $cell.append($inpNow);

      // 🔁 Recalcula límites SOLO en las otras filas borrador de la misma operación
      $inpNow.off('input.__dynmax change.__dynmax')
        .on('input.__dynmax change.__dynmax', debounce(() => {
          const opLocal = op;
          const sNow = parseInt(ctx.modal.$samplingResult.val()) || 0;

          ctx.modal.$rowsContainer.find('tr').each(function() {
            const $r = $(this);

            // ⛔ No tocar la fila actual para no romper el tipeo
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


    // === MOD: considerar operación para pendientes en cada fila borrador
    function refreshAllSamplingSelects() {
      const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;

      ctx.modal.$rowsContainer.find('tr').each(function() {
        const $row = $(this);
        const isSaved = !!$row.attr('data-id'); // filas guardadas no se tocan
        if (isSaved) return;

        const type = String($row.find('select[name="insp_type[]"]').val() || '').toUpperCase();
        const $cell = $row.find('td.col-sample');
        if (!type || !$cell.length) return;

        if (type === 'FAI') {
          renderSampleCell($cell.empty(), 'FAI', sampling);
          return;
        }

        // IPI
        const op = $row.find('select[name="operation[]"], input[name="operation[]"]').val() || null;

        // Valor actual desde INPUT
        const current = (function() {
          const v = $cell.find('input[name="sample_idx[]"]').val();
          return v !== undefined ? v : null;
        })();

        // Render con restante dinámico por operación
        renderSampleCell($cell.empty(), 'IPI', sampling, current, op);
      });
    }

    // Devuelve cuánto IPI queda pendiente para una operación concreta (contra sampling plan) - usado solo para reporte
    function getIpiRemainingForOp(op, sampling) {
      const done = ctx.ipiCountMap.get(op) || 0;
      const rem = Math.max(0, (parseInt(sampling, 10) || 0) - done);
      return rem;
    }

    // Reconstruye TODAS las celdas de muestra IPI (pendientes) en borradores (apoya al reporte)
    function refreshPendingIpiOptions() {
      const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;

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

    // Select de operación con prioridad a una operación sugerida (preferredOp)
    function createOperationSelect(totalOps, inspType = 'FAI', preferredOp = null) {
      const $sel = $('<select name="operation[]" class="form-control"></select>');
      const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;

      const ops = [];
      for (let i = 1; i <= totalOps; i++) ops.push(ordinalSuffix(i));

      if (preferredOp && ops.includes(preferredOp)) {
        const idx = ops.indexOf(preferredOp);
        if (idx > -1) ops.splice(idx, 1);
        ops.unshift(preferredOp);
      }

      for (const value of ops) {
        if (inspType === 'FAI' && ctx.faiDoneOps.has(value)) continue;
        if (inspType === 'IPI') {
          const ipiCount = ctx.ipiCountMap.get(value) || 0;
          if (ipiCount >= sampling) continue;
        }
        $sel.append(`<option value="${value}">${value}</option>`);
      }
      return $sel;
    }

    // Determina el siguiente par {type, op} en secuencia: FAI(1), IPI(1), FAI(2), IPI(2), ...
    function getNextInspectionPair(totalOps) {
      const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;

      const faiSum = new Map(); // op -> suma qty
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
        if ((ipiSum.get(op) || 0) < sampling) return {
          type: 'IPI',
          op
        };
      }
      return null;
    }

    function renderOrderProgress(orderId, percent) {
      const $wrap = $(`.progress[data-order-id="${orderId}"]`);
      const $bar = $wrap.find('.progress-bar');
      $bar.attr('aria-valuenow', percent).css('width', percent + '%').text(percent + '%');
      $bar.removeClass('bg-secondary bg-danger bg-warning bg-success');
      if (percent >= 100) $bar.addClass('bg-success');
      else if (percent >= 50) $bar.addClass('bg-warning');
      else $bar.addClass('bg-danger');
    }

    function computeProgressFromRows(rows, operations, ipiRequired) {
      if (!operations || operations < 1) return 0;

      const faiMap = new Map(),
        ipiMap = new Map(); // op -> suma qty

      (rows || []).forEach(r => {
        const type = (r.insp_type || '').toUpperCase();
        const op = r.operation;
        const res = (r.results || '').toLowerCase();
        if (!op || res !== 'pass') return;

        const qty = parseInt(r.qty_pcs ?? r.sample_idx ?? 1, 10) || 0;

        if (type === 'FAI') faiMap.set(op, (faiMap.get(op) || 0) + qty);
        if (type === 'IPI') ipiMap.set(op, (ipiMap.get(op) || 0) + qty);
      });

      const perOpReq = 1 + (parseInt(ipiRequired, 10) || 0);
      const totalReq = operations * perOpReq;

      let done = 0;
      for (let i = 1; i <= operations; i++) {
        const op = ordinalSuffix(i);
        const faiSum = faiMap.get(op) || 0;
        const ipiSum = ipiMap.get(op) || 0;
        done += Math.min(faiSum, 1) + Math.min(ipiSum, ipiRequired || 0);
      }

      const pct = totalReq > 0 ? Math.round((done / totalReq) * 100) : 0;
      return Math.max(0, Math.min(pct, 100));
    }

    function refreshProgress(orderId, operations, ipiRequired) {
      if (!operations) operations = parseInt($('#operationInput').val()) || 0;
      if (ipiRequired === undefined || ipiRequired === null) {
        ipiRequired = parseInt($('#edit-sampling-result').val()) || 0;
      }
      $.get(ROUTES.faibyOrder(orderId)).done(rows => {
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
        // Sugerir siguiente par FAI/IPI por operación
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
        <button type="button" class="btn btn-danger btn-sm removeRowBtn">−</button>
      </td>`);

      // Al cambiar FAI/IPI, re-sugerir operación y refrescar "Muestra"
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

        // Cuando cambie la operación, recalcula restante para esa op
        newOpSel.on('change', function() {
          const opX = $(this).val() || null;
          const sNow = parseInt(ctx.modal.$samplingResult.val()) || 0;
          const cur = $sampleCell.find('input[name="sample_idx[]"]').val() || null;
          renderSampleCell($sampleCell.empty(), newType, sNow, cur, opX);
        });
      });

      // Si ya existe opSel (cuando isNumber), también enlaza su change:
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

    function createRowFromData(data) {
      const $row = $('<tr></tr>').attr('data-id', data.id);

      // guarda qty en data-attr para otras funciones (edición)
      const savedQty = parseInt(data.qty_pcs ?? data.sample_idx ?? 1, 10) || 1;
      $row.attr('data-qty_pcs', savedQty);

      $row.append(`<td><input type="date" name="date[]" class="form-control" value="${data.date || ''}" disabled></td>`);
      $row.append(`
      <td>
        <select name="insp_type[]" class="form-control" disabled>
          <option value="FAI" ${data.insp_type === 'FAI' ? 'selected' : ''}>FAI</option>
          <option value="IPI" ${data.insp_type === 'IPI' ? 'selected' : ''}>IPI</option>
        </select>
      </td>`);
      $row.append(`<td><input type="text" name="operation[]"  class="form-control" value="${data.operation || ''}"  disabled></td>`);
      $row.append(`<td><input type="text" name="operator[]"   class="form-control" value="${data.operator  || ''}" disabled></td>`);

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
      $row.append(`<td><input type="text" name="station[]"     class="form-control" value="${data.station || ''}"     disabled></td>`);
      $row.append(`
      <td>
        <select name="method[]" class="form-control" disabled>
          ${['Manual','Vmm/Manual','Visual','Vmm','Keyence','Keyence/Manual'].map(m =>
            `<option value="${m}" ${data.method === m ? 'selected' : ''}>${m}</option>`).join('')}
        </select>
      </td>`);

      // --- QTY PCS / sample_idx ---
      const sampling = parseInt(ctx.modal.$samplingResult.val()) || 0;
      const $sampleCell = $('<td class="col-sample"></td>');

      // Para guardadas: render y deja disabled, asegurando valor
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

    function loadFaiRows(orderId, cb) {
      $.getJSON(ROUTES.faibyOrder(orderId)).done(rows => {
        (Array.isArray(rows) ? rows : []).forEach(r => ctx.modal.$rowsContainer.append(createRowFromData(r)));
        if (typeof cb === 'function') cb();
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
          const uniq = [...new Set(arr.map(r => (r[field] || '').trim()))]
            .filter(Boolean).sort(COLLATOR.compare);
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
      const uniq = [...new Set(raw.map(r => (r[field] || '').trim()))]
        .filter(Boolean).sort(COLLATOR.compare);
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
</script>






@endpush