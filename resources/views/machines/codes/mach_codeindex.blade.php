<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Completed Orders')
@section('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-calendar-alt mr-2" aria-hidden="true"></i>
            Codes Machines
        </h4>

        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Codes Machines</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')

{{-- 🔹 Botón único para agregar código --}}
<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-dark btn-sm" data-toggle="modal" data-target="#selectCodeTypeModal">
        <i class="fas fa-plus"></i> Code
    </button>
</div>

<div class="row">
    {{-- ===================== CODE MACHINE ===================== --}}
    <div class="col-md-6">
        <div class="card card-outline card-secondary shadow-sm mb-4 h-100">
            <div class="card-header py-2">
                <strong><i class="fas fa-microchip mr-1"></i> Code Machine</strong>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-borderless mb-0 table-codes"
                        id="codemachineTable">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Created At</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($machines as $code)
                            <tr class="{{ $code->status !== 'active' ? 'row-inactive' : '' }}">
                                <td>{{ $code->code }}</td>
                                <td>{{ $code->name }}</td>
                                <td>{{ $code->brand }}</td>
                                <td>{{ $code->location }}</td>
                                <td>{{ $code->type_work }}</td>
                                <td>{{ $code->created_at->format('M-d-y') }}</td>
                                <td>
                                    @if($code->status === 'active')
                                    <span class="badge badge-success badge-status">Active</span>
                                    @elseif($code->status === 'maintenance')
                                    <span class="badge badge-warning badge-status">Maintenance</span>
                                    @else
                                    <span class="badge badge-secondary badge-status">Inactive</span>
                                    @endif
                                </td>
                                <td class="col-actions">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- Editar --}}
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary editMachineBtn"
                                            data-url="{{ route('codes.update', $code->id) }}"
                                            data-code="{{ $code->code }}"
                                            data-name="{{ $code->name }}"
                                            data-brand="{{ $code->brand }}"
                                            data-type_work="{{ $code->type_work }}"
                                            data-location="{{ $code->location }}"
                                            data-status="{{ $code->status }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {{-- Activar / desactivar --}}
                                        <button type="button"
                                            class="btn btn-sm toggle-status
                                            {{ $code->status === 'active' ? 'btn-success' : 'btn-secondary' }}"
                                            data-id="{{ $code->id }}"
                                            data-status="{{ $code->status }}"
                                            data-url="{{ route('codes.toggle-status', $code->id) }}">
                                            <i class="fas fa-star {{ $code->status === 'active' ? 'text-white' : 'text-white' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== CODE EQUIPMENT ===================== --}}
    <div class="col-md-6">
        <div class="card card-outline card-secondary shadow-sm mb-4 h-100">
            <div class="card-header py-2">
                <strong><i class="fas fa-tools mr-1"></i> Code Equipment</strong>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-borderless mb-0 table-codes"
                        id="codeequipmentTable">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Created At</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipments as $code)
                            <tr class="{{ $code->status !== 'active' ? 'row-inactive' : '' }}">
                                <td>{{ $code->code }}</td>
                                <td>{{ $code->name }}</td>
                                <td>{{ $code->brand }}</td>
                                <td>{{ $code->location }}</td>
                                <td>{{ $code->type_work }}</td>
                                <td>{{ $code->created_at->format('M-d-y') }}</td>
                                <td>
                                    @if($code->status === 'active')
                                    <span class="badge badge-success badge-status">Active</span>
                                    @elseif($code->status === 'maintenance')
                                    <span class="badge badge-warning badge-status">Maintenance</span>
                                    @else
                                    <span class="badge badge-secondary badge-status">Inactive</span>
                                    @endif
                                </td>
                                <td class="col-actions">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- Editar EQUIPMENT --}}
                                        <button type="button"
                                            class="btn btn-outline-primary editEquipmentBtn"
                                            data-url="{{ route('codes.update', $code->id) }}"
                                            data-id="{{ $code->id }}"
                                            data-code="{{ $code->code }}" {{-- 👈 importante --}}
                                            data-name="{{ $code->name }}"
                                            data-brand="{{ $code->brand }}"
                                            data-type_machine="{{ $code->type_machine }}"
                                            data-type_work="{{ $code->type_work }}"
                                            data-location="{{ $code->location }}"
                                            data-status="{{ $code->status }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {{-- Activar / desactivar --}}
                                        <button type="button"
                                            class="btn btn-sm toggle-status
                                            {{ $code->status === 'active' ? 'btn-success' : 'btn-secondary' }}"
                                            data-id="{{ $code->id }}"
                                            data-status="{{ $code->status }}"
                                            data-url="{{ route('codes.toggle-status', $code->id) }}">
                                            <i class="fas fa-star {{ $code->status === 'active' ? 'text-white' : 'text-white' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- Modal para elegir MACHINE o EQUIPMENT --}}
<div class="modal fade" id="selectCodeTypeModal" tabindex="-1" aria-labelledby="selectCodeTypeLabel" aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="selectCodeTypeLabel">
                 NEW CODE
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center px-4 pt-4 pb-3">
                <p class="text-muted mb-4" style="font-size: 15px;">
                    Please select the type of code you want to create.
                </p>
                <div class="row">
                    {{-- Machine --}}
                    <div class="col-6 pr-1">
                        <button type="button"
                            class="btn btn-primary btn-lg btn-block py-3"
                            id="btnAddMachine"
                            style="font-size: 16px; border-radius: 12px;">
                            <i class="fas fa-cogs fa-lg d-block mb-1"></i>
                            Machine
                        </button>
                    </div>
                    {{-- Equipment --}}
                    <div class="col-6 pl-1">
                        <button type="button"
                            class="btn btn-info btn-lg btn-block py-3"
                            id="btnAddEquipment"
                            style="font-size: 16px; border-radius: 12px;">
                            <i class="fas fa-tools fa-lg d-block mb-1"></i>
                            Equipment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




{{-- MODAL MACHINE (CREATE + EDIT) --}}
<div class="modal fade" id="machineCodeModal" tabindex="-1" aria-labelledby="machineCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="machineCodeForm" method="POST" action="{{ route('codes.store') }}">
                @csrf
                {{-- Laravel: POST (create) | PUT (edit) --}}
                <input type="hidden" id="machineFormMethod" name="_method" value="POST">
                <input type="hidden" name="type_machine" value="Machine">

                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="machineModalTitle">NEW CODE MACHINE</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        {{-- BRAND (select, se llena por /machine-brands) --}}
                        <div class="form-group col-md-6">
                            <label for="machine_brand">Brand</label>
                            <select id="machine_brand" name="brand" class="form-control" required>
                                <option value="">-- Select Brand --</option>
                            </select>
                        </div>

                        {{-- CODE (via brand) --}}
                        <div class="form-group col-md-3">
                            <label for="machine_code">Next Code</label>
                            <input type="text" id="machine_code" name="code" class="form-control" readonly>
                        </div>

                        {{-- TYPE WORK (CNC…) --}}
                        <div class="form-group col-md-3">
                            <label for="machine_type_work">Type Work</label>
                            <select name="type_work" id="machine_type_work" class="form-control" required>
                                <option value="">-- Select Type --</option>
                                <option value="Lathe">Lathe</option>
                                <option value="Mill">Mill</option>
                            </select>
                        </div>

                        {{-- NAME --}}
                        <div class="form-group col-md-6">
                            <label for="machine_name">Name</label>
                            <input type="text" id="machine_name" name="name" class="form-control" required>
                        </div>

                        {{-- LOCATION --}}
                        <div class="form-group col-md-6">
                            <label for="machine_location">Location</label>
                            <select id="machine_location" name="location" class="form-control" required>
                                <option value="Hearst">Hearst</option>
                                <option value="Yarnell">Yarnell</option>
                            </select>
                        </div>

                        {{-- STATUS --}}
                        <div class="form-group col-md-6">
                            <label for="machine_status">Status</label>
                            <select id="machine_status" name="status" class="form-control" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="machineSubmitButton" class="btn btn-primary btn-sm">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>
{{-- MODAL EQUIPMENT (CREATE + EDIT) --}}
<div class="modal fade" id="equipmentCodeModal" tabindex="-1" aria-labelledby="equipmentCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="equipmentCodeForm" method="POST" action="{{ route('codes.store') }}">
                @csrf
                {{-- Laravel: POST (create) | PUT (edit) --}}
                <input type="hidden" id="equipmentFormMethod" name="_method" value="POST">
                <input type="hidden" name="type_machine" value="Equipment">

                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="equipmentModalTitle">NEW CODE EQUIPMENT</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        {{-- TYPE (solo display) --}}
                        <div class="form-group col-md-4">
                            <label for="equipment_type_machine_display">Type</label>
                            <input type="text" id="equipment_type_machine_display" class="form-control" value="Equipment" readonly>
                        </div>

                        {{-- TYPE WORK (Equipment) --}}
                        <div class="form-group col-md-6">
                            <label for="equipment_type_work">Machine Type</label>
                            <select name="type_work" id="equipment_type_work" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="Fabrication Equipment">Fabrication Equipment</option>
                                <option value="Grinding Equipment">Grinding Equipment</option>
                                <option value="Manual Lathe">Manual Lathe</option>
                                <option value="Manual Mill">Manual Mill</option>
                                <option value="Other Equipment">Other Equipment</option>
                                <option value="Welding Equipment">Welding Equipment</option>
                            </select>
                        </div>

                        {{-- NEXT CODE (por type_work) --}}
                        <div class="form-group col-md-2">
                            <label for="equipment_code">Next Code</label>
                            {{-- este input se va a usar tanto para crear como para editar --}}
                            <input type="text" id="equipment_code" name="code" class="form-control" readonly>
                        </div>

                        {{-- NAME --}}
                        <div class="form-group col-md-6">
                            <label for="equipment_name">Name</label>
                            <input type="text" id="equipment_name" name="name" class="form-control" required>
                        </div>

                        {{-- LOCATION --}}
                        <div class="form-group col-md-3">
                            <label for="equipment_location">Location</label>
                            <select id="equipment_location" name="location" class="form-control" required>
                                <option value="Hearst">Hearst</option>
                                <option value="Yarnell">Yarnell</option>
                            </select>
                        </div>

                        {{-- STATUS --}}
                        <div class="form-group col-md-3">
                            <label for="equipment_status">Status</label>
                            <select id="equipment_status" name="status" class="form-control" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>

                        {{-- BRAND (texto libre) --}}
                        <div class="form-group col-md-4">
                            <label for="equipment_brand">Brand</label>
                            <input type="text" id="equipment_brand" name="brand" class="form-control">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="equipmentSubmitButton" class="btn btn-primary btn-sm">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>


@endsection

@section('css')
<style>
    /* filas inactivas en gris, no en rojo */
    .row-inactive {
        color: #9ca3af !important;
    }

    /* encabezado más limpio */
    .table-codes thead th {
        background-color: #f4f6f9;
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        border-bottom: none;
        white-space: nowrap;
    }

    .table-codes td,
    .table-codes th {
        padding: .35rem .5rem;
        vertical-align: middle;
    }

    .col-actions {
        width: 110px;
        text-align: center;
        white-space: nowrap;
    }
</style>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // =========================
        //  DATA TABLES
        // =========================
        function initCodesTable(selector) {
            $(selector).DataTable({
                responsive: true,
                lengthChange: true,
                pageLength: 15,
                lengthMenu: [15, 25, 50],
                paging: true,
                searching: true,
                info: true,
                order: [
                    [0, 'asc']
                ]
            });
        }

        initCodesTable('#codemachineTable');
        initCodesTable('#codeequipmentTable');

        // ======================================================
        //  MACHINE: Cargar marcas solo una vez
        // ======================================================
        let marcasCargadas = false;

        function cargarOpcionesMarca(callback) {
            const brandSelect = document.getElementById('machine_brand');
            if (!brandSelect) return;

            marcasCargadas = true;
            brandSelect.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = '-- Select Brand --';
            brandSelect.appendChild(defaultOption);

            fetch('/machine-brands')
                .then(response => response.json())
                .then(brands => {
                    for (const [label, value] of Object.entries(brands)) {
                        const option = document.createElement('option');
                        option.value = label;
                        option.textContent = label;
                        brandSelect.appendChild(option);
                    }
                    if (typeof callback === 'function') callback();
                })
                .catch(error => {
                    console.error('Error al cargar las marcas:', error);
                    if (typeof callback === 'function') callback();
                });
        }

        // Cuando cambia BRAND MACHINE → obtener code
        const machineBrandEl = document.getElementById('machine_brand');
        if (machineBrandEl) {
            machineBrandEl.addEventListener('change', function() {
                const brandName = this.value;
                if (!brandName) {
                    document.getElementById('machine_code').value = '';
                    return;
                }

                fetch(`/machine-codes/next-code-by-brand?brand=${encodeURIComponent(brandName)}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('machine_code').value = data.next_code || 'Código no disponible';
                    })
                    .catch(error => {
                        console.error('Error al obtener el código:', error);
                        document.getElementById('machine_code').value = 'Error';
                    });
            });
        }

        // ======================================================
        //  EQUIPMENT: type_work → preview_code
        // ======================================================
        const equipmentTypeWorkEl = document.getElementById('equipment_type_work');
        const equipmentCodeInputEl = document.getElementById('equipment_code');

        if (equipmentTypeWorkEl && equipmentCodeInputEl) {
            equipmentTypeWorkEl.addEventListener('change', function() {
                const selectedType = this.value;

                if (!selectedType) {
                    equipmentCodeInputEl.value = '';
                    return;
                }

                fetch(`/type-work/next-code?type=${encodeURIComponent(selectedType)}`)
                    .then(response => response.json())
                    .then(data => {
                        equipmentCodeInputEl.value = data.next_code || 'Código no disponible';
                    })
                    .catch(error => {
                        console.error('Error al obtener el código de equipment:', error);
                        equipmentCodeInputEl.value = 'Error al obtener código';
                    });
            });
        }

        // ======================================================
        //  BOTONES PARA CREAR
        // ======================================================

        // Abrir modal MACHINE en modo CREATE
        $('#btnAddMachine').on('click', function() {
            const form = $('#machineCodeForm');

            $('#selectCodeTypeModal').modal('hide');

            $('#machineModalTitle').text('NEW CODE MACHINE');
            $('#machineSubmitButton').text('Save');

            form.trigger('reset');
            form.attr('action', "{{ route('codes.store') }}");
            $('#machineFormMethod').val('POST');

            $('#machine_code').val('');
            $('#machine_code').prop('readonly', true);

            if (!marcasCargadas) {
                cargarOpcionesMarca(function() {
                    $('#machineCodeModal').modal('show');
                });
            } else {
                $('#machineCodeModal').modal('show');
            }
        });

        // Abrir modal EQUIPMENT en modo CREATE
        $('#btnAddEquipment').on('click', function() {
            const form = $('#equipmentCodeForm');

            $('#selectCodeTypeModal').modal('hide');

            $('#equipmentModalTitle').text('NEW CODE EQUIPMENT');
            $('#equipmentSubmitButton').text('Save');

            form.trigger('reset');
            form.attr('action', "{{ route('codes.store') }}");
            $('#equipmentFormMethod').val('POST');

            $('#equipment_type_machine_display').val('Equipment');
            $('#equipment_preview_code').val('');
            $('#equipmentCodeModal').modal('show');
        });

        // ======================================================
        //  EDITAR MACHINE
        // ======================================================
        $(document).on('click', '.editMachineBtn', function() {
            const btn = $(this);
            const form = $('#machineCodeForm');

            $('#machineModalTitle').text('EDIT CODE MACHINE');
            $('#machineSubmitButton').text('Update');

            form.attr('action', btn.data('url'));
            $('#machineFormMethod').val('PUT');

            const rellenar = () => {
                $('#machine_code').val(btn.data('code'));
                $('#machine_name').val(btn.data('name'));
                $('#machine_brand').val(btn.data('brand'));
                $('#machine_type_work').val(btn.data('type_work'));
                $('#machine_location').val(btn.data('location'));
                $('#machine_status').val(btn.data('status'));
            };

            if (!marcasCargadas) {
                cargarOpcionesMarca(function() {
                    rellenar();
                    $('#machineCodeModal').modal('show');
                });
            } else {
                rellenar();
                $('#machineCodeModal').modal('show');
            }
        });

        // ======================================================
        //  EDITAR EQUIPMENT
        // ======================================================
        $(document).on('click', '.editEquipmentBtn', function() {
            const btn = $(this);
            const form = $('#equipmentCodeForm');

            $('#equipmentModalTitle').text('EDIT CODE EQUIPMENT');
            $('#equipmentSubmitButton').text('Update');

            form.attr('action', btn.data('url'));
            $('#equipmentFormMethod').val('PUT');

            $('#equipment_type_machine_display').val('Equipment');
            $('#equipment_name').val(btn.data('name'));
            $('#equipment_brand').val(btn.data('brand'));
            $('#equipment_type_work').val(btn.data('type_work'));
            $('#equipment_location').val(btn.data('location'));
            $('#equipment_status').val(btn.data('status'));

            // 👈 AQUÍ rellenas el campo Next Code al editar
            $('#equipment_code').val(btn.data('code'));

            $('#equipmentCodeModal').modal('show');
        });

        // ======================================================
        //  SUBMIT: MACHINE (CREATE / EDIT)
        // ======================================================
        const machineForm = document.getElementById('machineCodeForm');
        if (machineForm) {
            machineForm.addEventListener('submit', function(e) {
                const method = document.getElementById('machineFormMethod').value;

                if (method === 'PUT') {
                    e.preventDefault();

                    const url = machineForm.getAttribute('action');
                    const formData = new FormData(machineForm);

                    fetch(url, {
                            method: 'POST', // POST + _method=PUT
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                // Swal.fire('Success', 'Code updated successfully!', 'success');
                                $('#machineCodeModal').modal('hide');
                                location.reload();
                            } else {
                                Swal.fire('Error', data.message || 'Something went wrong.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Request failed.', 'error');
                        });
                }
                // si es POST (create), deja que Laravel recargue normal
            });
        }

        // ======================================================
        //  SUBMIT: EQUIPMENT (CREATE / EDIT)
        // ======================================================
        const equipmentForm = document.getElementById('equipmentCodeForm');
        if (equipmentForm) {
            equipmentForm.addEventListener('submit', function(e) {
                const method = document.getElementById('equipmentFormMethod').value;

                if (method === 'PUT') {
                    e.preventDefault();

                    const url = equipmentForm.getAttribute('action');
                    const formData = new FormData(equipmentForm);

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                //Swal.fire('Success', 'Code updated successfully!', 'success');
                                $('#equipmentCodeModal').modal('hide');
                                location.reload();
                            } else {
                                Swal.fire('Error', data.message || 'Something went wrong.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Request failed.', 'error');
                        });
                }
            });
        }

        // ======================================================
        //  TOGGLE STATUS (estrella) → lo dejas como ya lo tenías
        // ======================================================
        $(document).on('click', '.toggle-status', function() {
            const btn = $(this);
            const url = btn.data('url');
            const row = btn.closest('tr');
            const icon = btn.find('i');

            fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "X-Requested-With": "XMLHttpRequest",
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire("Error", "No se pudo actualizar el código.", "error");
                        return;
                    }

                    const newStatus = data.new_status;
                    const statusCell = row.find('td').eq(6);
                    const badge = statusCell.find('.badge-status');
                    const pretty = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

                    badge
                        .removeClass('badge-success badge-secondary badge-warning')
                        .text(pretty);

                    if (newStatus === 'active') {
                        badge.addClass('badge-success');
                    } else if (newStatus === 'maintenance') {
                        badge.addClass('badge-warning');
                    } else {
                        badge.addClass('badge-secondary');
                    }

                    if (newStatus === 'active') {
                        // Botón verde
                        btn.removeClass('btn-secondary').addClass('btn-success');
                        // Estrella blanca
                        icon.removeClass('text-white').addClass('text-white'); // se mantiene blanca siempre
                        // Quitar clase de fila inactiva
                        row.removeClass('row-inactive');
                        //Swal.fire("Updated", "Code activated.", "success");

                    } else {
                        // Botón gris
                        btn.removeClass('btn-success').addClass('btn-secondary');
                        // Estrella blanca
                        icon.removeClass('text-white').addClass('text-white'); // sigue siendo blanca
                        // Marcar fila como inactiva
                        row.addClass('row-inactive');
                       // Swal.fire("Updated", "Code inactivated.", "info");
                    }

                    btn.data('status', newStatus);
                    btn.attr('data-status', newStatus);
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire("Error", "Error en la petición.", "error");
                });
        });

    });
</script>
@endpush