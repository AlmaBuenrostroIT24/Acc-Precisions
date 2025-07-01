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
    $(document).ready(function() {

        // Mostrar datos al abrir modal
        $('#editModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            console.log('Datos del botón:', button.data());
            const id = button.data('id');
            let operation = button.data('operation') || '';

            // Si el valor es 'default_value', mostrar campo vacío para que agregue uno nuevo
            if (operation === 'default_value') {
                operation = '';
            }

            $('#edit-id').val(id);
            $('#order-id').val(id);
            $('#edit-workid').val(button.data('workid'));
            $('#operationInput').val(operation);

            const pn = button.data('pn');
            const description = button.data('description') || '';
            const descriptionShort = description.split(',')[0];
            $('#edit-fullpart').val(`${pn} - ${descriptionShort}`);

            // 🔁 ✅ Llama la función para cargar las filas existentes del QA
            loadFaiRows(id);

            // Limpiar tabla y agregar una fila vacía
            $('#dynamicTable tbody').empty().append(createRow());

        });
        //----------------------------------------------------------------------------------------------
        // Botón para guardar operación individual
        $('#addOperationBtn').on('click', function() {
            const operation = $('#operationInput').val().trim();
            const orderId = $('#order-id').val();
            $.ajax({
                url: `/orders-schedule/${orderId}/update-operation`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    operation: operation
                },
                success: function(response) {
                    alert('Operación actualizada correctamente');
                    $('#operationInput').val(operation); // Aquí actualizas el input con el valor guardado           
                    $(`[data-id="${orderId}"]`).data('operation', operation); // También puedes actualizar el atributo data del botón para mantener todo sincronizado  
                    $(`button[data-id="${orderId}"]`).attr('data-operation', operation); // Opcional: también puedes actualizar el atributo HTML para que sea consistente
                },
                error: function() {
                    alert('Error al actualizar operación');
                }
            });
        });
    });
    //----------------------------------------------------------------------------------------------


    function createRow() {
        const today = new Date().toISOString().split('T')[0];
        const operationValue = parseInt($('#operationInput').val());
        const isNumber = !isNaN(operationValue) && operationValue > 0;

        const row = $('<tr></tr>');

        row.append(`<td><input type="date" name="date[]" class="form-control" value="${today}"></td>`);
        row.append(`
        <td>
            <select name="insp_type[]" class="form-control">
                <option value="FAI">FAI</option>
                <option value="IPI">IPI</option>
            </select>
        </td>
    `);

        if (isNumber) {
            row.append($('<td></td>').append(createOperationSelect(operationValue)));
        } else {
            row.append(`<td><input type="text" name="operation[]" class="form-control"></td>`);
        }

        row.append(`<td><input type="text" name="operator[]" class="form-control"></td>`);
        row.append(`
        <td>
            <select name="results[]" class="form-control">
                <option value="pass">Pass</option>
                <option value="no pass">No Pass</option>
            </select>
        </td>
    `);
        row.append(`<td><input type="text" name="sb_is[]" class="form-control"></td>`);
        row.append(`<td><input type="text" name="observation[]" class="form-control"></td>`);
        row.append(`<td><input type="text" name="station[]" class="form-control"></td>`);
        row.append(`
        <td>
            <select name="method[]" class="form-control">
                <option value="Manual">Manual</option>
                <option value="Vmm/Manual">Vmm/Manual</option>
                <option value="Visual">Visual</option>
                <option value="Vmm">Vmm</option>
                <option value="Keyence">Keyence</option>
                <option value="Keyence/Manual">Keyence/Manual</option>
            </select>
        </td>
    `);
        row.append(`
        <td>
            <button type="button" class="btn btn-success btn-sm saveRowBtn me-1">  <i class="fas fa-save"></i></button>
            <button type="button" class="btn btn-danger btn-sm removeRowBtn">−</button>
        </td>
    `);

        return row;
    }
    //-----------------------------------------------------------------------------------------------------------------------

    function createRowFromData(data) {
        const row = $('<tr></tr>').attr('data-id', data.id); // 👈 muy importante

        row.append(`<td><input type="date" name="date[]" class="form-control" value="${data.date}" disabled></td>`);

        row.append(`
        <td>
            <select name="insp_type[]" class="form-control" disabled>
                <option value="FAI" ${data.insp_type === 'FAI' ? 'selected' : ''}>FAI</option>
                <option value="IPI" ${data.insp_type === 'IPI' ? 'selected' : ''}>IPI</option>
            </select>
        </td>
    `);

        row.append(`<td><input type="text" name="operation[]" class="form-control" value="${data.operation}" disabled></td>`);
        row.append(`<td><input type="text" name="operator[]" class="form-control" value="${data.operator}" disabled></td>`);

        row.append(`
        <td>
            <select name="results[]" class="form-control" disabled>
                <option value="pass" ${data.results === 'pass' ? 'selected' : ''}>Pass</option>
                <option value="no pass" ${data.results === 'no pass' ? 'selected' : ''}>No Pass</option>
            </select>
        </td>
    `);

        row.append(`<td><input type="text" name="sb_is[]" class="form-control" value="${data.sb_is || ''}" disabled></td>`);
        row.append(`<td><input type="text" name="observation[]" class="form-control" value="${data.observation || ''}" disabled></td>`);
        row.append(`<td><input type="text" name="station[]" class="form-control" value="${data.station || ''}" disabled></td>`);

        row.append(`
        <td>
            <select name="method[]" class="form-control" disabled>
                <option value="Manual" ${data.method === 'Manual' ? 'selected' : ''}>Manual</option>
                <option value="Vmm/Manual" ${data.method === 'Vmm/Manual' ? 'selected' : ''}>Vmm/Manual</option>
                <option value="Visual" ${data.method === 'Visual' ? 'selected' : ''}>Visual</option>
                <option value="Vmm" ${data.method === 'Vmm' ? 'selected' : ''}>Vmm</option>
                <option value="Keyence" ${data.method === 'Keyence' ? 'selected' : ''}>Keyence</option>
                <option value="Keyence/Manual" ${data.method === 'Keyence/Manual' ? 'selected' : ''}>Keyence/Manual</option>
            </select>
        </td>
    `);

        row.append(`
    <td>
        <span class="text-success me-2">✔️</span>
        <button type="button" class="btn btn-warning btn-sm editRowBtn me-1"><i class="fas fa-edit"></i></button>
        <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
    </td>
`);


        return row;
    }
    //-----------------------------------------------------------------------------------------------------------------------

    $(document).on('click', '.saveRowBtn', function() {
        const row = $(this).closest('tr');
        const token = $('input[name="_token"]').val(); // CSRF token
        const orderScheduleId = $('#order-id').val();
        const rowId = row.data('id');

        // Recopilar datos de la fila
        const data = {
            _token: token,
            order_schedule_id: orderScheduleId, // ✅ Se envía al backend
            date: row.find('input[name="date[]"]').val()?.trim(),
            insp_type: row.find('select[name="insp_type[]"]').val(),
            operation: row.find('select[name="operation[]"]').val() || row.find('input[name="operation[]"]').val(),
            operator: row.find('input[name="operator[]"]').val()?.trim(),
            results: row.find('select[name="results[]"]').val(),
            sb_is: row.find('input[name="sb_is[]"]').val()?.trim(),
            observation: row.find('input[name="observation[]"]').val()?.trim(),
            station: row.find('input[name="station[]"]').val()?.trim(),
            method: row.find('select[name="method[]"]').val(),

            // Campos extra fuera de la tabla
            num_operation: $('#operationInput').val(),
            inspector: $('#edit-inspector').val(),
            part_rev: $('#edit-fullpart').val(),
            job: $('#edit-workid').val()
        };

        // Si la fila ya tiene id (editar)
        if (rowId) {
            data.id = rowId; // importante para update
        }
        console.log('Datos para enviar:', data);
        // Enviar AJAX para guardar o actualizar
        $.ajax({
            url: '/qa/faisummary/store-single',
            method: 'POST',
            data: data,
            success: function(response) {
                // Guardar o actualizar id en la fila
                if (response.id) {
                    row.attr('data-id', response.id);
                }
                //alert('Fila guardada correctamente');
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: 'La fila se ha guardado correctamente',
                    timer: 1500,
                    showConfirmButton: false
                });

                // Guardar el ID devuelto en el <tr> (nuevo o editado)
                if (response.id) {
                    row.attr('data-id', response.id);
                }

                // Opcional: Deshabilitar inputs y botones para esa fila
                row.find('input, select, .saveRowBtn').prop('disabled', true);

                // Opcional: cambiar el botón + por un ✔️ o texto de éxito
                // $(this).replaceWith('<span class="text-success">✔️ Guardado</span>');
                // Reemplazar última celda con ✔️ y botón de editar
                row.find('td:last').html(`
    <span class="text-success me-2">✔️</span>
   <button type="button" class="btn btn-warning btn-sm editRowBtn me-1"><i class="fas fa-edit"></i></button>
        <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
`);
            },
            error: function(xhr) {
                let msg = 'Error al guardar la fila';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg += ': ' + xhr.responseJSON.error;
                }
                alert(msg);
                console.error(xhr.responseText);
            }
        });
    });
    //-----------------------------------------------------------------------------------------------------------------------

    function loadFaiRows(orderScheduleId) {
        $.get(`/qa/faisummary/by-order/${orderScheduleId}`, function(rows) {
            //console.log('Filas recibidas:', rows);
            const container = $('#rowsContainer'); // Cambia esto al `tbody` real
            container.empty();

            rows.forEach(row => {
                const newRow = createRowFromData(row);
                container.append(newRow);
            });
        });
    }
    //-----------------------------------------------------------------------------------------------------------------------

    // Agregar nueva fila
    $('#addRowBtn').on('click', function() {
        const operationValue = $('#operationInput').val().trim();

        if (operationValue === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Required information',
                text: 'Please enter the number of operations before adding an inspection.',
                confirmButtonText: 'OK'
            });
            return;
            return;
        }
        $('#dynamicTable tbody').append(createRow());
    });

    // Eliminar fila
    $('#dynamicTable').on('click', '.removeRowBtn', function() {
        $(this).closest('tr').remove();
    });

    // Evento para editar la fila
    $(document).on('click', '.editRowBtn', function() {
        const row = $(this).closest('tr');

        // Habilitar todos los inputs y selects de esa fila
        row.find('input, select').prop('disabled', false);

        // Reemplazar botones por el botón de guardar

        row.find('td:last').html(`
        <button type="button" class="btn btn-success btn-sm saveRowBtn me-1"><i class="fas fa-save"></i></button>
        <button type="button" class="btn btn-danger btn-sm deleteRowBtn"><i class="fas fa-trash-alt"></i></button>
    `);
    });


    //-----------------------------------------------------------------------------------------------------------------------


    function createOperationSelect(count) {
        const select = $('<select name="operation[]" class="form-control"></select>');
        const ordinalSuffix = (n) => {
            if (n === 1) return '1st Op';
            if (n === 2) return '2nd Op';
            if (n === 3) return '3rd Op';
            return `${n}th Op`;
        };
        for (let i = 1; i <= count; i++) {
            const value = ordinalSuffix(i);
            select.append(`<option value="${value}">${value}</option>`);
        }
        return select;
    }

    //-----------------------------------------------------------------------------------------------------------------------
</script>

@endpush