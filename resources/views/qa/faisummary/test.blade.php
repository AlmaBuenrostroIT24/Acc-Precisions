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


<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <table id="ordersTableEmpty" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>PART/DESCRIPCION</th>
                            <th>JOB</th>
                            <th>Acciones</th> {{-- Nueva columna --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ordersempty as $order)
                        <tr>
                            <td>{{ $order->PN }} - {{ Str::before($order->Part_description, ',') }}</td>
                            <td>{{ $order->work_id }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal"
                                    data-id="{{ $order->id }}"
                                    data-pn="{{ $order->PN }}"
                                    data-description="{{ $order->Part_description }}"
                                    data-workid="{{ $order->work_id }}"
                                    data-woqty="{{ $order->wo_qty }}"
                                    data-operation="{{ $order->operation ?? '' }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!--   <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="fas fa-plus"></i> New Order
                        </button> -->

                <!-- Modal de edición -->



            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <table id="ordersTableProcess" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>PART/DESCRIPCION</th>
                            <th>JOB</th>
                            <th>Progress</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ordersprocess as $order)
                        <tr>
                            <td>{{ $order->PN }} - {{ Str::before($order->Part_description, ',') }}</td>
                            <td>{{ $order->work_id }}</td>

                            {{-- Placeholder progreso (JS lo llenará) --}}
                            <td>
                                <div class="progress" data-order-id="{{ $order->id }}" style="height: 18px;">
                                    <div class="progress-bar bg-secondary" role="progressbar"
                                        style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        0%
                                    </div>
                                </div>
                                <small class="text-muted d-block">
                                    <span class="badge bg-light text-dark me-1">FAI + IPI</span>
                                </small>
                            </td>

                            <td>
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal"
                                    data-id="{{ $order->id }}"
                                    data-pn="{{ $order->PN }}"
                                    data-description="{{ $order->Part_description }}"
                                    data-workid="{{ $order->work_id }}"
                                    data-woqty="{{ $order->wo_qty }}"
                                    data-operation="{{ $order->operation ?? '' }}"> {{-- aquí guardas el # de ops --}}
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>


                <!--   <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="fas fa-plus"></i> New Order
                        </button> -->

                <!-- Modal de edición -->



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
<script>
    // ================== FAI Summary UI Script (globals + modal activo) ==================

    // Globals re-asignables por cada modal abierto
    let $rowsContainer; // tbody del modal actual
    let $samplingResult; // #edit-sampling-result del modal actual
    let $operationInput; // #operationInput del modal actual
    let $reportPre; // #inspection-missing del modal actual
    let $reportBox; // #inspection-missing-container del modal actual

    let faiDoneOps = new Set();
    let ipiCountMap = new Map();

    // DataTables (AJAX) refs para poder recargar desde cualquier handler
    let dtEmpty = null;
    let dtProcess = null;

    $(document).ready(function() {
        // ---------------- DataTables AJAX ----------------
        const dtFactory = bucket => ({
            responsive: true,
            deferRender: true,
            stateSave: true,
            lengthMenu: [5, 10, 25, 50, 100],
            pageLength: 10,
            order: [
                [1, 'desc']
            ], // ordenar por JOB desc (ajusta si quieres)
            ajax: {
                url: "{{ route('faisummary.partsrevision.data') }}", // <-- Asegúrate de tener esta ruta
                data: {
                    bucket
                }, // 'empty' | 'process'
                dataSrc: 'data'
            },
            columns: [{
                    data: 'part'
                }, // PART/DESCRIPCIÓN
                {
                    data: 'work_id'
                }, // JOB
                {
                    data: 'wo_qty'
                }, // WO Qty
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                } // Acciones (botón Editar)
            ]
        });

        dtEmpty = $('#ordersTableEmpty').DataTable(dtFactory('empty'));
        dtProcess = $('#ordersTableProcess').DataTable(dtFactory('process'));

        // ---------------- Abrir modal ----------------
        $('#editModal').on('show.bs.modal', function(event) {
            const $modal = $(this);
            const button = $(event.relatedTarget);
            const id = button.data('id');
            const operation = button.data('operation') === 'default_value' ? '' : (button.data('operation') || '');

            // 🔗 Re-enlazar globals al DOM del modal activo
            $rowsContainer = $modal.find('#dynamicTable tbody');
            $samplingResult = $modal.find('#edit-sampling-result');
            $operationInput = $modal.find('#operationInput');
            $reportPre = $modal.find('#inspection-missing');
            $reportBox = $modal.find('#inspection-missing-container');

            // Campos base
            $modal.find('#edit-id, #order-id').val(id);
            $modal.find('#edit-workid').val(button.data('workid'));
            $modal.find('#edit-woqty').val(button.data('woqty'));
            $operationInput.val(operation);

            const pn = button.data('pn');
            const description = button.data('description') || '';
            $modal.find('#edit-fullpart').val(`${pn} - ${description.split(',')[0]}`);

            // 1) Limpiar filas del modal (deja espacio para “borrador” arriba si lo usas)
            $rowsContainer.empty();

            // 2) Cargar filas guardadas
            loadFaiRows(id, function() {
                updateInspectionMissing(); // al terminar de cargar
            });

            // 3) Recalcular sampling + progreso + reporte
            updateSamplingQty();
            updateInspectionMissing();

            const opsNow = parseInt($operationInput.val()) || 0;
            const ipiNow = parseInt($samplingResult.val()) || 0;
            refreshProgress(id, opsNow, ipiNow);
        });

        // ---------------- Eventos (globales, usan las globals re-enlazadas) ----------------

        // Cambios en sampling (tipo/cantidad)
        $('#edit-sampling-type, #edit-woqty').on('change input', function() {
            updateSamplingQty();
        });

        // Guardar # de operaciones (con refresco AJAX)
        $('#addOperationBtn').on('click', function() {
            const operation = parseInt($operationInput.val().trim()) || 0;
            const orderId = $('#order-id').val();
            const sampling = parseInt($samplingResult.val()) || 0;

            const total_fai = operation * 1; // 1 FAI por operación
            const total_ipi = operation * sampling; // IPI = ops * sampling

            $.post(`/orders-schedule/${orderId}/update-operation`, {
                    _token: $('input[name="_token"]').val(),
                    operation,
                    sampling,
                    total_fai,
                    total_ipi
                })
                .done(() => {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: 'Operación y totales guardados correctamente',
                        timer: 1200,
                        showConfirmButton: false
                    });

                    // Habilitar Add Row
                    $('#addRowBtn').prop('disabled', false);

                    // IMPORTANTE: que devuelva jqXHR para poder .always()
                    const p = setInspectionStatus(orderId, 'in_progress');
                    (p && typeof p.always === 'function' ? p : $.Deferred().resolve()).always(() => {
                        // Sincroniza UI local
                        $operationInput.val(operation);
                        $(`button[data-id="${orderId}"]`).attr('data-operation', operation);
                        refreshProgress(orderId, operation, sampling);
                        updateInspectionMissing();

                        // 🔁 Recarga ambas tablas AJAX
                        if (dtEmpty) dtEmpty.ajax.reload(null, false);
                        if (dtProcess) dtProcess.ajax.reload(null, false);
                    });
                })
                .fail(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al actualizar operación'
                    });
                });
        });

        // Agregar fila (validando #ops en BD)
        $('#addRowBtn').on('click', function() {
            const opsVal = ($operationInput.val() || '').trim();
            const orderId = $('#order-id').val();

            if (!opsVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required information',
                    text: 'Please enter the number of operations before adding an inspection.'
                });
                return;
            }

            $.get(`/orders-schedule/${orderId}/validate-ops`, {
                    ops: opsVal
                })
                .done(function(resp) {
                    if (!resp.saved) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Not saved yet',
                            text: 'You must save the number of operations before adding inspections.'
                        });
                        return;
                    }
                    const newRow = createRow();
                    if (!newRow) return;
                    $rowsContainer.prepend(newRow);
                    newRow.find('input,select').filter(':visible:not([disabled])').first().focus();
                })
                .fail(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server error',
                        text: 'Unable to validate operations. Try again later.'
                    });
                });
        });

        // Quitar fila nueva sin guardar
        $('#dynamicTable').on('click', '.removeRowBtn', function() {
            $(this).closest('tr').remove();
            updateInspectionMissing();
        });

        // Editar fila guardada
        $(document).on('click', '.editRowBtn', function() {
            const row = $(this).closest('tr');
            row.find('input, select').prop('disabled', false);
            row.find('td:last').html(`
      <button type="button" class="btn btn-success btn-sm saveRowBtn me-1"><i class="fas fa-save"></i></button>
      <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
    `);
        });

        /**==========================GUARDAR FILA (CREATE/UPDATE)============================================= */
        $(document).on('click', '.saveRowBtn', function() {
            const row = $(this).closest('tr');
            const token = $('input[name="_token"]').val();
            const orderScheduleId = $('#order-id').val();
            const rowId = row.data('id');

            const data = {
                _token: token,
                order_schedule_id: orderScheduleId,
                date: row.find('input[name="date[]"]').val()?.trim(),
                insp_type: row.find('select[name="insp_type[]"]').val(),
                operation: row.find('select[name="operation[]"]').val() || row.find('input[name="operation[]"]').val(),
                operator: row.find('input[name="operator[]"]').val()?.trim(),
                results: row.find('select[name="results[]"]').val(),
                sb_is: row.find('input[name="sb_is[]"]').val()?.trim(),
                observation: row.find('input[name="observation[]"]').val()?.trim(),
                station: row.find('input[name="station[]"]').val()?.trim(),
                method: row.find('select[name="method[]"]').val(),
                inspector: $('#edit-inspector').val(),
            };
            if (rowId) data.id = rowId;

            $.post('/qa/faisummary/store-single', data)
                .done(function(resp) {
                    if (resp.id) row.attr('data-id', resp.id);

                    // Bloquea edición
                    row.find('input, select, .saveRowBtn').prop('disabled', true);
                    updateInspectionMissing();

                    // Acciones
                    row.find('td:last').html(`
          <button type="button" class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
          <button type="button" class="btn btn-warning btn-sm editRowBtn me-1"><i class="fas fa-edit"></i></button>
          <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
        `);

                    // ---- Progreso y status ----
                    const opsNow = parseInt($operationInput.val()) || 0;
                    const ipiNow = parseInt($samplingResult.val()) || 0;

                    $.get(`/qa/faisummary/by-order/${orderScheduleId}`, function(rows) {
                        const pct = computeProgressFromRows(rows, opsNow, ipiNow);
                        renderOrderProgress(orderScheduleId, pct);

                        if (pct >= 100) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Inspección completada!',
                                text: `Se cumplieron 1 FAI y ${ipiNow} IPI por cada una de las ${opsNow} operaciones.`,
                                confirmButtonText: 'Aceptar',
                                showCancelButton: false,
                                showCloseButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                setInspectionStatus(orderScheduleId, 'completed')
                                    .done(() => {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Orden marcada como completada',
                                            timer: 1700,
                                            showConfirmButton: false
                                        });
                                        $('#editModal').modal('hide');
                                        // 🔁 Recarga tablas AJAX (puede mover de "process" a otra vista si lo decides)
                                        if (dtEmpty) dtEmpty.ajax.reload(null, false);
                                        if (dtProcess) dtProcess.ajax.reload(null, false);
                                    })
                                    .fail(xhr => {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'No se pudo marcar como completada',
                                            text: xhr.responseJSON?.message || 'Error inesperado'
                                        });
                                    });
                            });
                        } else {
                            if (Array.isArray(rows) && rows.length > 0) {
                                setInspectionStatus(orderScheduleId, 'in_progress')
                                    .fail(xhr => console.warn('No se pudo marcar process:', xhr?.status, xhr?.responseText));
                            }
                            Swal.fire({
                                icon: 'success',
                                title: '¡Guardado!',
                                text: 'La fila se ha guardado correctamente',
                                timer: 1200,
                                showConfirmButton: false
                            });

                            // 🔁 Recarga por si cambia algo visible en la tabla
                            if (dtEmpty) dtEmpty.ajax.reload(null, false);
                            if (dtProcess) dtProcess.ajax.reload(null, false);
                        }
                    });
                })
                .fail(function(xhr) {
                    const msg = xhr.responseJSON?.error ? 'Error: ' + xhr.responseJSON.error : 'Error al guardar la fila';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg
                    });
                });
        });

        /**
         * PUT status_inspection (debe devolver jqXHR)
         */
        function setInspectionStatus(orderId, status) {
            return $.ajax({
                url: `/orders-schedule/${orderId}/status-inspection`,
                method: 'PUT',
                data: {
                    _token: $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content'),
                    status_inspection: status
                }
            });
        }

        // Eliminar fila guardada
        $(document).on('click', '.deleteRowBtn', function() {
            const row = $(this).closest('tr');
            const rowId = row.data('id');

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar fila?',
                text: 'Esta acción no se puede deshacer',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;

                // Si no está guardada
                if (!rowId) {
                    row.remove();
                    updateInspectionMissing();
                    return;
                }

                $.ajax({
                        url: `/qa/faisummary/delete/${rowId}`,
                        method: 'DELETE',
                        data: {
                            _token: $('input[name="_token"]').val()
                        }
                    })
                    .done(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: 'La fila ha sido eliminada',
                            timer: 1200,
                            showConfirmButton: false
                        });

                        // Quitar del modal + actualizar reporte
                        row.remove();
                        updateInspectionMissing();

                        // Recalcular progreso y status + recargar tablas
                        const currentOrderId = $('#order-id').val();
                        const opsNow = parseInt($operationInput.val()) || 0;
                        const ipiNow = parseInt($samplingResult.val()) || 0;

                        if (currentOrderId) {
                            $.get(`/qa/faisummary/by-order/${currentOrderId}`, function(rows) {
                                const pct = computeProgressFromRows(rows, opsNow, ipiNow);
                                renderOrderProgress(currentOrderId, pct);

                                const newStatus = (rows.length === 0) ?
                                    'pending' :
                                    (pct >= 100 ? 'completed' : 'in_progress');

                                setInspectionStatus(currentOrderId, newStatus)
                                    .always(() => {
                                        if (dtEmpty) dtEmpty.ajax.reload(null, false);
                                        if (dtProcess) dtProcess.ajax.reload(null, false);
                                    });
                            });
                        } else {
                            if (dtEmpty) dtEmpty.ajax.reload(null, false);
                            if (dtProcess) dtProcess.ajax.reload(null, false);
                        }
                    })
                    .fail(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo eliminar la fila'
                        });
                    });
            });
        });

        // (Opcional) refrescar tablas al cerrar modal para ver cambios al instante
        $('#editModal').on('hidden.bs.modal', function() {
            if (dtEmpty) dtEmpty.ajax.reload(null, false);
            if (dtProcess) dtProcess.ajax.reload(null, false);
        });
    }); // document.ready

    // ================== Funciones (usan las globals re-enlazadas) ==================

    // Cargar filas guardadas (JSON) SIN vaciar el tbody (preserva borrador arriba)
    function loadFaiRows(orderScheduleId, callback) {
        $.getJSON(`/qa/faisummary/by-order/${orderScheduleId}`, function(rows) {
            rows = Array.isArray(rows) ? rows : [];
            rows.forEach(row => $rowsContainer.append(createRowFromData(row)));
            if (typeof callback === 'function') callback();
        });
    }

    // Recalcular sampling y refrescar progreso + reporte
    function updateSamplingQty() {
        const lotSize = parseInt($('#edit-woqty').val());
        const type = $('#edit-sampling-type').val();
        if (!lotSize || lotSize < 1) return $samplingResult.val('');

        $.getJSON(`/sampling-plan?lot_size=${lotSize}&sampling_type=${type}`, function(data) {
            const sample = (data.sample_qty !== undefined ? data.sample_qty : 0);
            $samplingResult.val(sample);

            const currentOrderId = $('#order-id').val();
            const opsNow = parseInt($operationInput.val()) || 0;
            if (currentOrderId && opsNow) {
                refreshProgress(currentOrderId, opsNow, sample);
                updateInspectionMissing();
            }
        });
    }

    // Construye el resumen/report y mapas para selects
    function updateInspectionMissing() {
        const sampling = parseInt($samplingResult.val()) || 0;
        const operations = parseInt($operationInput.val()) || 0;

        if (!operations) {
            $reportPre.text('');
            $reportBox.removeClass('bg-light bg-success bg-danger text-white');
            return;
        }

        const faiMap = new Map(),
            ipiMap = new Map();
        faiDoneOps = new Set();
        ipiCountMap = new Map();

        // Contar SOLO filas guardadas (evita la fila borrador)
        $rowsContainer.find('tr[data-id]').each(function() {
            const $row = $(this);
            const type = String($row.find('select[name="insp_type[]"]').val() || '').trim().toUpperCase(); // FAI | IPI
            const operation = $row.find('select[name="operation[]"], input[name="operation[]"]').val() || '';
            const result = String($row.find('select[name="results[]"]').val() || '').trim().toLowerCase(); // pass | no pass
            if (!operation) return;

            if (type === 'FAI' && result === 'pass') {
                faiMap.set(operation, (faiMap.get(operation) || 0) + 1);
                faiDoneOps.add(operation);
            }
            if (type === 'IPI' && result === 'pass') {
                ipiMap.set(operation, (ipiMap.get(operation) || 0) + 1);
                ipiCountMap.set(operation, (ipiCountMap.get(operation) || 0) + 1);
            }
        });

        let resumen = '';
        let hayFaltantes = false;

        for (let i = 1; i <= operations; i++) {
            const op = ordinalSuffix(i);
            const faiCount = faiMap.get(op) || 0;
            const ipiCount = ipiMap.get(op) || 0;

            const faiRequired = 1; // 1 FAI por operación
            const ipiRequired = sampling; // IPI requeridos por operación

            const faiStatus = (faiCount >= faiRequired) ?
                `FAI: OK (${faiCount}/${faiRequired})` :
                `FAI: Need ${faiRequired - faiCount} (${faiCount}/${faiRequired})`;

            const ipiStatus = (ipiCount >= ipiRequired) ?
                `IPI: OK (${ipiCount}/${ipiRequired})` :
                `IPI: ❌ Need ${Math.max(ipiRequired - ipiCount, 0)} (${ipiCount}/${ipiRequired})`;

            const linea =
                (faiCount >= faiRequired && ipiCount >= ipiRequired) ? `✔️ ${op} → ${faiStatus} | ${ipiStatus}` :
                (faiCount < faiRequired && ipiCount < ipiRequired) ? `❌ ${op} → ${faiStatus} | ${ipiStatus}` :
                `⚠️ ${op} → ${faiStatus} | ${ipiStatus}`;

            resumen += linea + '\n';
            if (faiCount < faiRequired || ipiCount < ipiRequired) hayFaltantes = true;
        }

        // Pintar en el REPORT del modal ACTIVO
        $reportPre.text(resumen.trim());
        $reportBox.removeClass('bg-light bg-success bg-danger text-white');
        $reportBox.addClass(hayFaltantes ? 'bg-light text-white' : 'bg-success text-white');
    }

    // Helpers
    function ordinalSuffix(n) {
        if (n === 1) return '1st Op';
        if (n === 2) return '2nd Op';
        if (n === 3) return '3rd Op';
        return `${n}th Op`;
    }

    // Select de operaciones disponibles según FAI/IPI ya cumplidos
    function createOperationSelect(count, inspType = 'FAI') {
        const select = $('<select name="operation[]" class="form-control"></select>');
        const sampling = parseInt($samplingResult.val()) || 0;

        for (let i = 1; i <= count; i++) {
            const value = ordinalSuffix(i);
            if (inspType === 'FAI' && faiDoneOps.has(value)) continue;
            if (inspType === 'IPI') {
                const ipiCount = ipiCountMap.get(value) || 0;
                if (ipiCount >= sampling) continue;
            }
            select.append(`<option value="${value}">${value}</option>`);
        }
        return select;
    }

    // Fila editable (borrador)
    function createRow() {
        const today = new Date().toISOString().split('T')[0];
        const operationValue = parseInt($operationInput.val());
        const isNumber = !isNaN(operationValue) && operationValue > 0;
        const orderId = $('#order-id').val();

        const row = $('<tr></tr>');
        row.append(`<td><input type="date" name="date[]" class="form-control" value="${today}"></td>`);

        const $inspType = $(`
    <select name="insp_type[]" class="form-control">
      <option value="FAI">FAI</option>
      <option value="IPI">IPI</option>
    </select>
  `);
        row.append($('<td></td>').append($inspType));

        const $operationCell = $('<td></td>');
        let defaultType = 'FAI';

        if (isNumber) {
            let operationSelect = createOperationSelect(operationValue, 'FAI');
            if (operationSelect.children().length === 0) {
                operationSelect = createOperationSelect(operationValue, 'IPI');
                defaultType = 'IPI';
                if (operationSelect.children().length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No operations available',
                        text: 'All inspections for FAI and IPI have now been completed.'
                    });
                    return null;
                }
            }
            $inspType.val(defaultType);
            $operationCell.append(operationSelect);
        } else {
            $operationCell.append('<input type="text" name="operation[]" class="form-control">');
        }

        row.append($operationCell);
        row.append(buildOperatorInputCell(orderId));
        row.append(`<td>
    <select name="results[]" class="form-control">
      <option value="pass">Pass</option>
      <option value="no pass">No Pass</option>
    </select>
  </td>`);
        row.append(`<td><input type="text" name="sb_is[]" class="form-control"></td>`);
        row.append(`<td><input type="text" name="observation[]" class="form-control"></td>`);
        row.append(buildStationInputCell(orderId));
        row.append(`<td>
    <select name="method[]" class="form-control">
      <option value="Manual">Manual</option>
      <option value="Vmm/Manual">Vmm/Manual</option>
      <option value="Visual">Visual</option>
      <option value="Vmm">Vmm</option>
      <option value="Keyence">Keyence</option>
      <option value="Keyence/Manual">Keyence/Manual</option>
    </select>
  </td>`);
        row.append(`<td>
    <button type="button" class="btn btn-success btn-sm saveRowBtn me-1"><i class="fas fa-save"></i></button>
    <button type="button" class="btn btn-danger btn-sm removeRowBtn">−</button>
  </td>`);

        $inspType.on('change', function() {
            if (!isNumber) return;
            const newType = $(this).val();
            const newOpSelect = createOperationSelect(operationValue, newType);
            $operationCell.empty().append(newOpSelect);
        });

        return row;
    }

    // Fila solo-lectura desde DB
    function createRowFromData(data) {
        const row = $('<tr></tr>').attr('data-id', data.id);
        row.append(`<td><input type="date" name="date[]" class="form-control" value="${data.date || ''}" disabled></td>`);
        row.append(`<td>
    <select name="insp_type[]" class="form-control" disabled>
      <option value="FAI" ${data.insp_type === 'FAI' ? 'selected' : ''}>FAI</option>
      <option value="IPI" ${data.insp_type === 'IPI' ? 'selected' : ''}>IPI</option>
    </select>
  </td>`);
        row.append(`<td><input type="text" name="operation[]" class="form-control" value="${data.operation || ''}" disabled></td>`);
        row.append(`<td><input type="text" name="operator[]" class="form-control" value="${data.operator || ''}" disabled></td>`);
        const results = (data.results || '').toLowerCase();
        row.append(`<td>
    <select name="results[]" class="form-control" disabled>
      <option value="pass" ${results === 'pass' ? 'selected' : ''}>Pass</option>
      <option value="no pass" ${results === 'no pass' ? 'selected' : ''}>No Pass</option>
    </select>
  </td>`);
        row.append(`<td><input type="text" name="sb_is[]" class="form-control" value="${data.sb_is || ''}" disabled></td>`);
        row.append(`<td><input type="text" name="observation[]" class="form-control" value="${data.observation || ''}" disabled></td>`);
        row.append(`<td><input type="text" name="station[]" class="form-control" value="${data.station || ''}" disabled></td>`);
        row.append(`<td>
    <select name="method[]" class="form-control" disabled>
      ${['Manual','Vmm/Manual','Visual','Vmm','Keyence','Keyence/Manual'].map(m =>
        `<option value="${m}" ${data.method === m ? 'selected' : ''}>${m}</option>`).join('')}
    </select>
  </td>`);
        row.append(`
    <td>
      <button type="button" class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
      <button type="button" class="btn btn-warning btn-sm editRowBtn me-1"><i class="fas fa-edit"></i></button>
      <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
    </td>
  `);
        return row;
    }

    // ================== Progreso (lista + modal) ==================
    function computeProgressFromRows(rows, operations, ipiRequired) {
        if (!operations || operations < 1) return 0;

        const faiMap = new Map(); // op -> FAI pass
        const ipiMap = new Map(); // op -> IPI pass

        rows.forEach(r => {
            const type = (r.insp_type || '').toUpperCase();
            const op = r.operation;
            const res = (r.results || '').toLowerCase();
            if (res !== 'pass') return;

            if (type === 'FAI') {
                faiMap.set(op, (faiMap.get(op) || 0) + 1);
            } else if (type === 'IPI') {
                ipiMap.set(op, (ipiMap.get(op) || 0) + 1);
            }
        });

        const perOpRequired = 1 + (parseInt(ipiRequired, 10) || 0); // 1 FAI + N IPI
        const totalRequired = operations * perOpRequired;

        let done = 0;
        for (let i = 1; i <= operations; i++) {
            const op = ordinalSuffix(i);
            const faiCount = faiMap.get(op) || 0;
            const ipiCount = ipiMap.get(op) || 0;
            done += Math.min(faiCount, 1) + Math.min(ipiCount, ipiRequired || 0);
        }

        const pct = totalRequired > 0 ? Math.round((done / totalRequired) * 100) : 0;
        return Math.max(0, Math.min(pct, 100));
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

    function refreshProgress(orderId, operations, ipiRequired) {
        if (!operations) operations = parseInt($('#operationInput').val()) || 0;
        if (ipiRequired === undefined || ipiRequired === null) {
            ipiRequired = parseInt($('#edit-sampling-result').val()) || 0;
        }
        $.get(`/qa/faisummary/by-order/${orderId}`, function(rows) {
            const percent = computeProgressFromRows(rows, operations, ipiRequired);
            renderOrderProgress(orderId, percent);
        });
    }

    // ===== Helpers para Station (input + datalist) =====
    /***************** Config *****************/
    const ROUTES_BY_KIND = {
        stations: '/stations/by-order', // ajusta si tu endpoint es otro
        operators: '/operators/by-order' // ajusta si tu endpoint es otro
    };
    const FIELD_BY_KIND = {
        stations: 'station',
        operators: 'operator'
    };
    const INPUT_NAME_BY_KIND = {
        stations: 'station[]',
        operators: 'operator[]'
    };
    const MAX_OPTIONS = 50;
    const COLLATOR = new Intl.Collator('es', {
        sensitivity: 'base',
        numeric: true
    });

    /*************** Utilidades ***************/
    let __DL_COUNTER = 0;
    const debounce = (fn, ms = 150) => {
        let t;
        return (...a) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...a), ms);
        };
    };

    /*************** Cachés por tipo ***************/
    const RAW_CACHE = {
        stations: new Map(),
        operators: new Map()
    }; // orderId -> [{...}]
    const UNIQ_CACHE = {
        stations: new Map(),
        operators: new Map()
    }; // orderId -> ['A',...]
    const INFLIGHT = {
        stations: new Map(),
        operators: new Map()
    }; // orderId -> Promise

    function fetchListByOrder(kind, orderId) {
        if (!orderId) return Promise.resolve([]);
        const raw = RAW_CACHE[kind],
            inflight = INFLIGHT[kind];
        if (raw.has(orderId)) return Promise.resolve(raw.get(orderId));
        if (inflight.has(orderId)) return inflight.get(orderId);

        const url = `${ROUTES_BY_KIND[kind]}/${encodeURIComponent(orderId)}`;

        const p = $.ajax({
                url,
                method: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then((list) => {
                const arr = Array.isArray(list) ? list : [];
                raw.set(orderId, arr);
                const field = FIELD_BY_KIND[kind];
                const uniq = [...new Set(arr.map(r => (r[field] || '').trim()))]
                    .filter(Boolean).sort(COLLATOR.compare);
                UNIQ_CACHE[kind].set(orderId, uniq);
                return arr;
            })
            .catch((xhr) => {
                console.error(`fetchListByOrder(${kind}) error:`, {
                    url,
                    orderId,
                    status: xhr?.status,
                    responseText: xhr?.responseText
                });
                raw.set(orderId, []);
                UNIQ_CACHE[kind].set(orderId, []);
                return [];
            })
            .always(() => {
                inflight.delete(orderId);
            });

        inflight.set(orderId, p);
        return p;
    }

    function getUniqStrings(kind, orderId) {
        const uniqCache = UNIQ_CACHE[kind];
        if (uniqCache.has(orderId)) return uniqCache.get(orderId);
        const raw = RAW_CACHE[kind].get(orderId) || [];
        const field = FIELD_BY_KIND[kind];
        const uniq = [...new Set(raw.map(r => (r[field] || '').trim()))]
            .filter(Boolean).sort(COLLATOR.compare);
        uniqCache.set(orderId, uniq);
        return uniq;
    }

    /**
     * Fábrica de celdas <td> con <input list=...> + <datalist>
     */
    function makeDatalistCellFactory(kind) {
        return function buildDatalistCell(orderId, value = '', disabled = false) {
            const dlId = `${kind}-${orderId}-${++__DL_COUNTER}`;
            const $td = $('<td></td>');
            const $in = $(`<input name="${INPUT_NAME_BY_KIND[kind]}" class="form-control" list="${dlId}">`)
                .val(value || '').prop('disabled', !!disabled);
            const $dl = $(`<datalist id="${dlId}"></datalist>`);
            $td.append($in, $dl);

            if (!orderId) {
                $in.prop('disabled', true).attr('placeholder', 'Sin orden');
                return $td;
            }

            const renderList = (arr) => {
                const frag = document.createDocumentFragment();
                (arr || []).slice(0, MAX_OPTIONS).forEach(s => {
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

    /*************** Constructores específicos ***************/
    const buildStationInputCell = makeDatalistCellFactory('stations');
    const buildOperatorInputCell = makeDatalistCellFactory('operators');
    // -----------------------------------------------------------------------------------------------------------------
</script>

@endpush