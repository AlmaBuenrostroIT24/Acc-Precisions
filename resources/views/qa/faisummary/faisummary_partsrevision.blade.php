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

            // Limpiar tabla y agregar una fila vacía
            $('#dynamicTable tbody').empty().append(createRow());

        });



        function createRow() {
            const today = new Date().toISOString().split('T')[0]; // Asegura que la fecha de hoy esté disponible
            return $(`<tr>
        <td><input type="date" name="date[]" class="form-control" value="${today}"></td>
        <td>
            <select name="insp_type[]" class="form-control">
                <option value="FAI">FAI</option>
                <option value="IPI">IPI</option>
            </select>
        </td>
        <td><input type="text" name="operation[]" class="form-control"></td>
        <td><input type="text" name="operator[]" class="form-control"></td>
        <td>
            <select name="results[]" class="form-control">
                <option value="Pass">Pass</option>
                <option value="No Pass">No Pass</option>
            </select>
        </td>
        <td><input type="text" name="sb_is[]" class="form-control"></td>
        <td><input type="text" name="observation[]" class="form-control"></td>
        <td><input type="text" name="station[]" class="form-control"></td>
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
        <td><button type="button" class="btn btn-danger removeRowBtn">−</button></td>
    </tr>`);
        }

        // Agregar nueva fila
        $('#addRowBtn').on('click', function() {
            $('#dynamicTable tbody').append(createRow());
        });

        // Eliminar fila
        $('#dynamicTable').on('click', '.removeRowBtn', function() {
            $(this).closest('tr').remove();
        });



        // Botón para guardar operación individual
        $('#addOperationBtn').on('click', function() {
            const operation = $('#operationInput').val().trim();
            const orderId = $('#order-id').val();

            if (!operation) {
                alert('Por favor ingresa una operación');
                return;
            }
            if (!orderId) {
                alert('No se encontró el ID del pedido');
                return; // detiene la ejecución para evitar error en la URL
            }

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
                    // También puedes actualizar el atributo data del botón para mantener todo sincronizado
                    $(`[data-id="${orderId}"]`).data('operation', operation);
                    // Opcional: también puedes actualizar el atributo HTML para que sea consistente
                    $(`button[data-id="${orderId}"]`).attr('data-operation', operation);
                },
                error: function() {
                    alert('Error al actualizar operación');
                }
            });
        });

    });
</script>

@endpush