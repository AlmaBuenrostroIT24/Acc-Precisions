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
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>PART/DESCRIPCION</th>
                            <th>JOB</th>
                            <th>Acciones</th> {{-- Nueva columna --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
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
                @include('qa.faisummary.faisummary_modal')


            </div>
        </div>
    </div>
</div>


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
        });

        $('#edit-sampling-type, #edit-woqty').on('change input', updateSamplingQty);

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
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar operación'
                });
            });
        });

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

        $('#dynamicTable').on('click', '.removeRowBtn', function() {
            $(this).closest('tr').remove();
            updateInspectionMissing();
        });

        $(document).on('click', '.editRowBtn', function() {
            const row = $(this).closest('tr');
            row.find('input, select').prop('disabled', false);
            row.find('td:last').html(`
            <button type="button" class="btn btn-success btn-sm saveRowBtn me-1"><i class="fas fa-save"></i></button>
            <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
        `);
        });

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
            $samplingResult.val(data.sample_qty !== undefined ? data.sample_qty : '—');
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

            // 🔁 Si la fila no está guardada aún, solo quitarla
            if (!rowId) {
                row.remove();
                updateInspectionMissing();
                return;
            }

            // 🧠 Si tiene ID, borrar de DB también
            $.ajax({
                url: `/qa/faisummary/delete/${rowId}`,
                method: 'DELETE',
                data: {
                    _token: $('input[name="_token"]').val()
                },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'La fila ha sido eliminada',
                        timer: 1200,
                        showConfirmButton: false
                    });
                    row.remove();
                    updateInspectionMissing();
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



    //----------------------------------------------------------------------------------------------------------------------
</script>

@endpush