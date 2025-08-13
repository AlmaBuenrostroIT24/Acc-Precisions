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
    // === Variables comunes ===
    const $rowsContainer = $('#rowsContainer');
    const $samplingResult = $('#edit-sampling-result');
    const $operationInput = $('#operationInput');

    $(document).ready(function() {

        // ---------------- DataTables ----------------
        const dtOptions = {
            responsive: true,
            deferRender: true,
            stateSave: true,
            lengthMenu: [5, 10, 25, 50, 100],
            pageLength: 10,
            columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false
                }, // Acciones
                {
                    targets: 0,
                    width: "45%"
                }, // PART/DESCRIPCION
                {
                    targets: 1,
                    width: "15%"
                }, // JOB
                {
                    targets: 2,
                    width: "25%"
                } // Progreso
            ],
            order: [
                [0, 'asc']
            ]
        };

        ['#ordersTableEmpty', '#ordersTableProcess'].forEach(sel => {
            const $t = $(sel);
            if ($.fn.DataTable.isDataTable($t)) {
                $t.DataTable().columns.adjust().responsive.recalc();
            } else {
                $t.DataTable(dtOptions);
            }
        });

        // Mostrar datos al abrir modal
        $('#editModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const id = button.data('id');
            const operation = button.data('operation') === 'default_value' ? '' : (button.data('operation') || '');

            $('#edit-id, #order-id').val(id);
            $('#edit-workid').val(button.data('workid'));
            $('#edit-woqty').val(button.data('woqty'));
            $operationInput.val(operation);

            const pn = button.data('pn');
            const description = button.data('description') || '';
            $('#edit-fullpart').val(`${pn} - ${description.split(',')[0]}`);

            loadFaiRows(id, updateInspectionMissing);
            updateInspectionMissing(); // Asegura que se muestre el resumen aunque no haya filas cargadas
            updateSamplingQty();
            $('#dynamicTable tbody').empty().append(createRow());

            // Refrescar progreso para esta orden usando valores actuales del modal
            const opsNow = parseInt($operationInput.val()) || 0;
            const ipiNow = parseInt($samplingResult.val()) || 0;
            refreshProgress(id, opsNow, ipiNow);

        });



        // ----------- Cambios en sampling -----------
        $('#edit-sampling-type, #edit-woqty').on('change input', updateSamplingQty);

        // ----------- Guardar número de operaciones -----------
        $('#addOperationBtn').on('click', function() {
            const operation = $operationInput.val().trim();
            const orderId = $('#order-id').val();

            $.post(`/orders-schedule/${orderId}/update-operation`, {
                _token: '{{ csrf_token() }}',
                operation
            }).done(function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: 'Operación guardada correctamente',
                    timer: 1200,
                    showConfirmButton: false
                });
                $operationInput.val(operation);
                $(`button[data-id="${orderId}"]`).attr('data-operation', operation);

                // Refrescar progreso tras cambiar ops
                const ipiNow = parseInt($samplingResult.val()) || 0;
                refreshProgress(orderId, parseInt(operation) || 0, ipiNow);

            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar operación'
                });
            });
        });

        // ----------- Agregar fila (validando # ops) -----------
        $('#addRowBtn').on('click', function() {
            if ($operationInput.val().trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required information',
                    text: 'Please enter the number of operations before adding an inspection.'
                });
                return;
            }
            $('#dynamicTable tbody').append(createRow());
        });

        // ----------- Quitar fila nueva sin guardar -----------
        $('#dynamicTable').on('click', '.removeRowBtn', function() {
            $(this).closest('tr').remove();
            updateInspectionMissing();
        });

        // ----------- Editar fila guardada -----------
        $(document).on('click', '.editRowBtn', function() {
            const row = $(this).closest('tr');
            row.find('input, select').prop('disabled', false);
            row.find('td:last').html(`
            <button type="button" class="btn btn-success btn-sm saveRowBtn me-1"><i class="fas fa-save"></i></button>
            <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
        `);
        });

        // ----------- Guardar fila (create/update) -----------
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
                num_operation: $operationInput.val(),
                inspector: $('#edit-inspector').val(),
                part_rev: $('#edit-fullpart').val(),
                job: $('#edit-workid').val()
            };

            if (rowId) data.id = rowId;

            $.post('/qa/faisummary/store-single', data).done(function(response) {
                if (response.id) row.attr('data-id', response.id);

                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: 'La fila se ha guardado correctamente',
                    timer: 1500,
                    showConfirmButton: false
                });
                row.find('input, select, .saveRowBtn').prop('disabled', true);
                updateInspectionMissing(); // Asegura que se muestre el resumen aunque no haya filas cargadas

                row.find('td:last').html(`
                <span class="text-success me-2">✔️</span>
                <button type="button" class="btn btn-warning btn-sm editRowBtn me-1"><i class="fas fa-edit"></i></button>
                <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
            `);
                // Refrescar progreso
                const opsNow = parseInt($operationInput.val()) || 0;
                const ipiNow = parseInt($samplingResult.val()) || 0;
                refreshProgress(orderScheduleId, opsNow, ipiNow);
            }).fail(function(xhr) {
                const msg = xhr.responseJSON?.error ? 'Error: ' + xhr.responseJSON.error : 'Error al guardar la fila';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg
                });
            });
        });
    });

    function loadFaiRows(orderScheduleId, callback) {
        $.get(`/qa/faisummary/by-order/${orderScheduleId}`, function(rows) {
            $rowsContainer.empty();
            rows.forEach(row => $rowsContainer.append(createRowFromData(row)));
            if (typeof callback === 'function') callback();
        });
    }

    function updateSamplingQty() {
        const lotSize = parseInt($('#edit-woqty').val());
        const type = $('#edit-sampling-type').val();
        if (!lotSize || lotSize < 1) return $samplingResult.val('');

        $.getJSON(`/sampling-plan?lot_size=${lotSize}&sampling_type=${type}`, function(data) {
            const sample = (data.sample_qty !== undefined ? data.sample_qty : 0);
            $samplingResult.val(sample);

            // ✅ Refrescar progreso AHORA que ya tenemos ipiRequired correcto
            const currentOrderId = $('#order-id').val();
            const opsNow = parseInt($operationInput.val()) || 0;
            if (currentOrderId && opsNow) {
                refreshProgress(currentOrderId, opsNow, sample);
            }
        });
    }

    let faiDoneOps = new Set(); // global o accesible a createRow()
    let ipiCountMap = new Map();

    function updateInspectionMissing() {
        const sampling = parseInt($samplingResult.val());
        const operations = parseInt($operationInput.val());
        if (!operations) return $('#inspection-missing').text('');

        const faiMap = new Map();
        const ipiMap = new Map();
        faiDoneOps = new Set(); // ← Limpiar antes de procesar
        ipiCountMap = new Map(); // ← Nuevo mapa para conteo de IPI por operación

        $rowsContainer.find('tr').each(function() {
            const $row = $(this);
            const type = $row.find('select[name="insp_type[]"]').val();
            const operation = $row.find('select[name="operation[]"], input[name="operation[]"]').val();
            const result = $row.find('select[name="results[]"]').val();

            addToMapIfPass(faiMap, type, 'FAI', operation, result);
            addToMapIfPass(ipiMap, type, 'IPI', operation, result);

            // ✅ Si ya existe FAI aprobado, lo marcamos
            if (type === 'FAI' && result === 'pass') {
                faiDoneOps.add(operation);
            }
            if (type === 'IPI' && result === 'pass') {
                ipiCountMap.set(operation, (ipiCountMap.get(operation) || 0) + 1);
            }
        });

        let resumen = '';
        let hayFaltantes = false;

        for (let i = 1; i <= operations; i++) {
            const op = ordinalSuffix(i);
            const faiCount = faiMap.get(op) || 0;
            const ipiCount = ipiMap.get(op) || 0;

            const faiRequired = 1; // ✅ solo se necesita 1 FAI
            const ipiRequired = sampling || 0;

            const faiStatus = faiCount >= faiRequired ? `FAI: OK (${faiCount}/${faiRequired})` : `FAI: Need ${faiRequired - faiCount} (${faiCount}/${faiRequired})`;
            const ipiStatus = ipiCount >= ipiRequired ? `IPI: OK (${ipiCount}/${ipiRequired})` : `IPI: ❌ Need ${ipiRequired - ipiCount} (${ipiCount}/${ipiRequired})`;

            const linea = (faiCount >= faiRequired && ipiCount >= ipiRequired) ? `✔️ ${op} → ${faiStatus} | ${ipiStatus}` :
                (faiCount < faiRequired && ipiCount < ipiRequired) ? `❌ ${op} → ${faiStatus} | ${ipiStatus}` :
                `⚠️ ${op} → ${faiStatus} | ${ipiStatus}`;

            resumen += linea + '\n';
            if (faiCount < faiRequired || ipiCount < ipiRequired) hayFaltantes = true;
        }

        $('#inspection-missing').text(resumen.trim());
        const contenedor = $('#inspection-missing-container').removeClass('bg-light bg-success bg-danger');
        contenedor.addClass(hayFaltantes ? 'bg-light text-white' : 'bg-success text-white');
    }


    function addToMapIfPass(map, type, targetType, operation, result) {
        if (type === targetType && result === 'pass') {
            map.set(operation, (map.get(operation) || 0) + 1);
        }
    }

    function ordinalSuffix(n) {
        if (n === 1) return '1st Op';
        if (n === 2) return '2nd Op';
        if (n === 3) return '3rd Op';
        return `${n}th Op`;
    }

    function createOperationSelect(count, inspType = 'FAI') {
        const select = $('<select name="operation[]" class="form-control"></select>');
        const sampling = parseInt($samplingResult.val()) || 0;

        for (let i = 1; i <= count; i++) {
            const value = ordinalSuffix(i);

            if (inspType === 'FAI' && faiDoneOps.has(value)) continue;

            if (inspType === 'IPI') {
                const ipiCount = ipiCountMap.get(value) || 0;
                if (ipiCount >= sampling) continue; // ❌ Ya se cumplió el muestreo para esta operación
            }

            select.append(`<option value="${value}">${value}</option>`);
        }

        return select;
    }


    // Esta función genera una fila editable en blanco para una nueva inspección
    function createRow() {
        const today = new Date().toISOString().split('T')[0];
        const operationValue = parseInt($operationInput.val());
        const isNumber = !isNaN(operationValue) && operationValue > 0;

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
            // 🧠 Intentar crear primero FAI
            let operationSelect = createOperationSelect(operationValue, 'FAI');

            if (operationSelect.children().length === 0) {
                // 👉 Ya no hay FAI disponible → intentar IPI
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

            // Establecer el tipo por defecto según disponibilidad
            $inspType.val(defaultType);
            $operationCell.append(operationSelect);
        } else {
            $operationCell.append('<input type="text" name="operation[]" class="form-control">');
        }

        row.append($operationCell);
        row.append(`<td><input type="text" name="operator[]" class="form-control"></td>`);
        row.append(`<td><select name="results[]" class="form-control"><option value="pass">Pass</option><option value="no pass">No Pass</option></select></td>`);
        row.append(`<td><input type="text" name="sb_is[]" class="form-control"></td>`);
        row.append(`<td><input type="text" name="observation[]" class="form-control"></td>`);
        row.append(`<td><input type="text" name="station[]" class="form-control"></td>`);
        row.append(`<td><select name="method[]" class="form-control"><option value="Manual">Manual</option><option value="Vmm/Manual">Vmm/Manual</option><option value="Visual">Visual</option><option value="Vmm">Vmm</option><option value="Keyence">Keyence</option><option value="Keyence/Manual">Keyence/Manual</option></select></td>`);
        row.append(`<td><button type="button" class="btn btn-success btn-sm saveRowBtn me-1"><i class="fas fa-save"></i></button><button type="button" class="btn btn-danger btn-sm removeRowBtn">−</button></td>`);

        // 🔄 Cuando cambie tipo (FAI/IPI), actualizar operaciones válidas
        $inspType.on('change', function() {
            if (!isNumber) return;
            const newType = $(this).val();
            const newOpSelect = createOperationSelect(operationValue, newType);
            $operationCell.empty().append(newOpSelect);
        });

        return row;
    }



    function createRowFromData(data) {
        const row = $('<tr></tr>').attr('data-id', data.id);
        row.append(`<td><input type="date" name="date[]" class="form-control" value="${data.date}" disabled></td>`);
        row.append(`<td><select name="insp_type[]" class="form-control" disabled><option value="FAI" ${data.insp_type === 'FAI' ? 'selected' : ''}>FAI</option><option value="IPI" ${data.insp_type === 'IPI' ? 'selected' : ''}>IPI</option></select></td>`);
        row.append(`<td><input type="text" name="operation[]" class="form-control" value="${data.operation}" disabled></td>`);
        row.append(`<td><input type="text" name="operator[]" class="form-control" value="${data.operator}" disabled></td>`);
        row.append(`<td><select name="results[]" class="form-control" disabled><option value="pass" ${data.results === 'pass' ? 'selected' : ''}>Pass</option><option value="no pass" ${data.results === 'no pass' ? 'selected' : ''}>No Pass</option></select></td>`);
        row.append(`<td><input type="text" name="sb_is[]" class="form-control" value="${data.sb_is || ''}" disabled></td>`);
        row.append(`<td><input type="text" name="observation[]" class="form-control" value="${data.observation || ''}" disabled></td>`);
        row.append(`<td><input type="text" name="station[]" class="form-control" value="${data.station || ''}" disabled></td>`);
        row.append(`<td><select name="method[]" class="form-control" disabled><option value="Manual" ${data.method === 'Manual' ? 'selected' : ''}>Manual</option><option value="Vmm/Manual" ${data.method === 'Vmm/Manual' ? 'selected' : ''}>Vmm/Manual</option><option value="Visual" ${data.method === 'Visual' ? 'selected' : ''}>Visual</option><option value="Vmm" ${data.method === 'Vmm' ? 'selected' : ''}>Vmm</option><option value="Keyence" ${data.method === 'Keyence' ? 'selected' : ''}>Keyence</option><option value="Keyence/Manual" ${data.method === 'Keyence/Manual' ? 'selected' : ''}>Keyence/Manual</option></select></td>`);
        row.append(`<td><span class="text-success me-2">✔️</span><button type="button" class="btn btn-warning btn-sm editRowBtn me-1"><i class="fas fa-edit"></i></button><button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button></td>`);
        return row;
    }


    // ----------- Eliminar fila guardada -----------
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

            // Si no está guardada aún
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
                },
                success: function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'La fila ha sido eliminada',
                        timer: 1200,
                        showConfirmButton: false
                    });
                    row.remove();
                    updateInspectionMissing();

                    // Refrescar progreso
                    const currentOrderId = $('#order-id').val();
                    const opsNow = parseInt($operationInput.val()) || 0;
                    const ipiNow = parseInt($samplingResult.val()) || 0;
                    if (currentOrderId) refreshProgress(currentOrderId, opsNow, ipiNow);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar la fila'
                    });
                }
            });
        });
    });

    // ----------- (Opcional) Inicializar progreso al cargar la página -----------
    // Recorre cada botón de la tabla de proceso y calcula progreso inicial si tienes #ops y woqty
    $('#ordersTableProcess button[data-id]').each(function() {
        const $btn = $(this);
        const orderId = $btn.data('id');
        const operations = parseInt($btn.data('operation')) || 0; // si guardas # operaciones aquí
        const woqty = parseInt($btn.data('woqty')) || 0;
        if (!orderId || !operations || !woqty) return;

        const samplingType = 'Normal'; // Ajusta si tienes uno por orden (puedes poner data-sampling-type)
        $.getJSON(`/sampling-plan?lot_size=${woqty}&sampling_type=${samplingType}`, function(data) {
            const ipiRequired = (data.sample_qty !== undefined ? data.sample_qty : 0);
            refreshProgress(orderId, operations, ipiRequired);
        });
    });

    // ===================== Progreso por orden =====================

    // Calcula % progreso usando filas (rows) + #ops + IPI requerido
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

    // Pinta la barra de progreso
    function renderOrderProgress(orderId, percent) {
        const $wrap = $(`.progress[data-order-id="${orderId}"]`);
        const $bar = $wrap.find('.progress-bar');
        $bar.attr('aria-valuenow', percent).css('width', percent + '%').text(percent + '%');

        $bar.removeClass('bg-secondary bg-danger bg-warning bg-success');
        if (percent >= 100) $bar.addClass('bg-success');
        else if (percent >= 50) $bar.addClass('bg-warning');
        else $bar.addClass('bg-danger');
    }

    // Consulta filas y refresca barra
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


    //----------------------------------------------------------------------------------------------------------------------
</script>

@endpush