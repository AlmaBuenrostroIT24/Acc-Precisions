@extends('adminlte::page')

@section('title', 'Machines Machinary')

@section('content_header')
<div class="card-header d-flex align-items-center">
    <h4 class="mb-0 text-dark">
        <i class="fas fa-industry mr-2"></i> Machinary
    </h4>

    <button type="button"
        class="btn btn-dark btn-sm ml-auto"
        data-toggle="modal"
        data-target="#selectMachineryModal">
        <i class="fas fa-plus"></i> Machine
    </button>
</div>
@endsection


@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif


<div class="row" id="machinaryLayout">

    {{-- TABLA PRINCIPAL --}}
    <div class="col-md-12" id="colTable">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex align-items-center py-2 bg-white border-bottom">
                <h5 class="mb-0 text-secondary">
                    <i class="fas fa-list mr-2 text-primary"></i> Machinery List
                </h5>

                <span class="ml-auto text-muted small">
                    Total: {{ $machineries->count() }} machines
                </span>
            </div>

            <div class="card-body table-responsive p-0">
                <table id="machineryTable" class="table table-hover table-striped table-sm mb-0 align-middle machinery-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Model</th>
                            <th>Serial</th>
                            <th>Type</th>
                            <th>Control</th>
                            <th>Made</th>
                            <th>Year</th>
                            <th>Acquisition</th>
                            <th>Area</th>
                            <th>Mass</th>
                            <th>Batteries</th>
                            <th>kW</th>
                            <th>Volt.</th>
                            <th>Tool Type</th>
                            <th>Baud</th>
                            <th>Tool Qty</th>
                            <th class="text-center" style="width:110px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($machineries as $machinary)
                        <tr>
                            <td class="text-muted small">{{ $machinary->id }}</td>

                            {{-- IMAGE --}}
                            <td>
                                @php $firstImage = $machinary->images->first(); @endphp

                                @if($firstImage)
                                <div class="mach-thumb-wrapper">
                                    <img src="{{ asset('storage/' . $firstImage->image_path) }}"
                                        class="img-fluid mach-thumb"
                                        alt="Machine image">
                                </div>
                                @else
                                <span class="text-muted small">No image</span>
                                @endif
                            </td>

                            <td>
                                <span class="font-weight-semibold">
                                    {{ optional($machinary->machineCode)->code }}
                                </span>
                            </td>
                            <td>{{ optional($machinary->machineCode)->name }}</td>
                            <td>{{ $machinary->model }}</td>
                            <td>{{ $machinary->serial }}</td>

                            {{-- TYPE --}}
                            <td>
                                @php $type = optional($machinary->machineCode)->type; @endphp
                                @if($type)
                                <span class="badge badge-light border text-uppercase small px-2">
                                    {{ $type }}
                                </span>
                                @endif
                            </td>

                            <td>{{ $machinary->control_system }}</td>
                            <td>{{ $machinary->made }}</td>
                            <td>{{ $machinary->year }}</td>
                            <td>{{ optional($machinary->machineCode)->created_at?->format('Y-m-d') }}</td>
                            <td>{{ $machinary->machine_area }}</td>
                            <td>{{ $machinary->massof_machine }}</td>
                            <td>{{ $machinary->servo_batteries }}</td>
                            <td>{{ $machinary->kw }}</td>
                            <td>{{ $machinary->voltage }}</td>
                            <td>{{ $machinary->tool_type }}</td>
                            <td>{{ $machinary->baud_rate}}</td>
                            <td>{{ $machinary->tool_qty }}</td>

                            {{-- ACTIONS --}}
                            <td class="text-center text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('machines.machinary.edit', $machinary) }}"
                                        class="btn btn-outline-primary"
                                        title="Edit machinery">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('machines.machinary.destroy', $machinary) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this machinery?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-outline-danger"
                                            title="Delete machinery">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="20" class="text-center py-3 text-muted">
                                No machinery registered yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>


    {{-- FORMULARIO — INICIA OCULTO --}}
    <div class="col-md-8 d-none" id="colForm">
        <div class="card shadow-sm border-0 mach-form-card">

            {{-- HEADER --}}
            <div class="card-header d-flex align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-plus mr-1"></i> New Machinery
                </h5>

                <button type="button" class="btn btn-sm btn-outline-secondary ml-auto" id="btnCancelForm">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body">

                <form id="createMachineryForm"
                    method="POST"
                    action="{{ route('machines.machinary.store') }}"
                    enctype="multipart/form-data">

                    @csrf
                    <input type="hidden" id="selectedMachineId" name="machine_code_id">

                    {{-- MACHINE CODE INFO --}}
                    <h6 class="section-title">
                        <i class="fas fa-info-circle mr-2"></i> Machine Code Info
                    </h6>
                    <div class="field-group">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label class="form-label-sm">Code</label>
                                <input type="text" id="mc_code"
                                    class="form-control form-control-lgx" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label-sm">Name</label>
                                <input type="text" id="mc_name"
                                    class="form-control form-control-lgx" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label-sm">Manufactured</label>
                                <input type="text" id="mc_brand"
                                    class="form-control form-control-lgx" readonly>
                            </div>
                            <div class="form-group col-md-2">
                                <label class="form-label-sm">Location</label>
                                <input type="text" id="mc_location"
                                    class="form-control form-control-lgx" readonly>
                            </div>
                            <div class="form-group col-md-2">
                                <label class="form-label-sm">Type Work</label>
                                <input type="text" id="mc_type_work"
                                    class="form-control form-control-lgx" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- MACHINERY + TECHNICAL SIDE BY SIDE --}}
                    <div class="row">

                        {{-- MACHINERY DETAILS (Left) --}}
                        <div class="col-md-6">
                            <h6 class="section-title mt-3">
                                <i class="fas fa-cogs mr-2"></i> Machinery Details
                            </h6>

                            <div class="field-group">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Model</label>
                                        <input type="text" class="form-control form-control-lgx" name="model" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Serial</label>
                                        <input type="text" class="form-control form-control-lgx" name="serial">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Control System</label>
                                        <input type="text" class="form-control form-control-lgx" name="control_system">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Made</label>
                                        <input type="text" class="form-control form-control-lgx" name="made">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Year (Month / Year)</label>

                                        <div class="input-group date" id="yearPicker" data-target-input="nearest">
                                            <input type="text"
                                                id="year_display"
                                                class="form-control form-control-lgx datetimepicker-input"
                                                data-target="#yearPicker"
                                                placeholder="MM / YYYY"
                                                autocomplete="off">
                                        </div>

                                        <input type="hidden" name="year" id="year">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Machine Area</label>
                                        <input type="text" class="form-control form-control-lgx" name="machine_area">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label class="form-label-sm">Mass of Machine</label>
                                        <input type="text" class="form-control form-control-lgx" name="massof_machine">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TECHNICAL DATA (Right) --}}
                        <div class="col-md-6">
                            <h6 class="section-title mt-3">
                                <i class="fas fa-bolt mr-2"></i> Technical Data
                            </h6>

                            <div class="field-group">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Servo Batteries</label>
                                        <input type="text" class="form-control form-control-lgx" name="servo_batteries">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">kW</label>
                                        <input type="text" class="form-control form-control-lgx" name="kw">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Voltage</label>
                                        <input type="text" class="form-control form-control-lgx" name="voltage">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Tool Type</label>
                                        <input type="text" class="form-control form-control-lgx" name="tool_type">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Baud Rate</label>
                                        <input type="text" class="form-control form-control-lgx" name="baud_rate">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="form-label-sm">Tool Qty</label>
                                        <input type="text" class="form-control form-control-lgx" name="tool_qty">
                                    </div>
                                </div>
                            </div>

                            {{-- DESCRIPTION --}}
                            <h6 class="section-title mt-3">
                                <i class="fas fa-align-left mr-2"></i> Description
                            </h6>
                            <div class="field-group">
                                <div class="form-group mb-1">
                                    <textarea class="form-control form-control-lgx"
                                        name="description"
                                        rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="col-md-8">
                        {{-- IMAGES --}}
                        <h6 class="section-title mt-3">
                            <i class="fas fa-image mr-2"></i> Images
                        </h6>
                        <div class="field-group">
                            <div class="form-group mb-1">
                                <input type="file"
                                    name="images[]"
                                    id="imagesInput"
                                    class="form-control form-control-lgx"
                                    multiple
                                    accept="image/*">

                                <small class="text-muted">
                                    You can upload up to 3 images of the machine.
                                </small>

                                <div id="imagesPreview" class="d-flex flex-wrap mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-primary btn-sm px-4">
                            <i class="fas fa-save mr-1"></i> Save Machinery
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>



{{-- MODAL PARA SELECCIONAR MACHINE CODE --}}
<div class="modal fade" id="selectMachineryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cogs mr-1"></i> Select a Machine
                </h5>
                <button class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                @if($availableCodes->isEmpty())
                <div class="alert alert-info">No machines available.</div>
                @else
                <p class="text-muted mb-2">Select a machine to begin:</p>

                <div class="list-group">
                    @foreach($availableCodes as $code)
                    <button type="button"
                        class="list-group-item list-group-item-action select-machinery"
                        data-id="{{ $code->id }}"
                        data-code="{{ $code->code }}"
                        data-name="{{ $code->name }}"
                        data-brand="{{ $code->brand }}"
                        data-location="{{ $code->location }}"
                        data-type-work="{{ $code->type_work }}">
                        <strong>{{ $code->code }}</strong> — {{ $code->name }}
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- MODAL PARA MOSTRAR IMAGEN EN GRANDE -->
<div id="imageModal" class="image-modal">
    <span class="image-modal-close">&times;</span>
    <span class="image-modal-prev">&#10094;</span>
    <span class="image-modal-next">&#10095;</span>
    <img class="image-modal-content" id="imageModalImg">
</div>

@endsection


@section('css')
<style>
    /* Ocultar columnas cuando tabla es col-md-6 */
    #colTable.col-md-4 .machinery-table th:nth-child(1),
    #colTable.col-md-4 .machinery-table td:nth-child(1),
    #colTable.col-md-4 .machinery-table th:nth-child(4),
    #colTable.col-md-4 .machinery-table td:nth-child(4),
    #colTable.col-md-4 .machinery-table th:nth-child(5),
    #colTable.col-md-4 .machinery-table td:nth-child(5),
    #colTable.col-md-4 .machinery-table th:nth-child(6),
    #colTable.col-md-4 .machinery-table td:nth-child(6),
    #colTable.col-md-4 .machinery-table th:nth-child(7),
    #colTable.col-md-4 .machinery-table td:nth-child(7),
    #colTable.col-md-4 .machinery-table th:nth-child(8),
    #colTable.col-md-4 .machinery-table td:nth-child(8),
    #colTable.col-md-4 .machinery-table th:nth-child(9),
    #colTable.col-md-4 .machinery-table td:nth-child(9),
    #colTable.col-md-4 .machinery-table th:nth-child(10),
    #colTable.col-md-4 .machinery-table td:nth-child(10),
    #colTable.col-md-4 .machinery-table th:nth-child(13),
    #colTable.col-md-4 .machinery-table td:nth-child(13),
    #colTable.col-md-4 .machinery-table th:nth-child(14),
    #colTable.col-md-4 .machinery-table td:nth-child(14),
    #colTable.col-md-4 .machinery-table th:nth-child(15),
    #colTable.col-md-4 .machinery-table td:nth-child(15),
    #colTable.col-md-4 .machinery-table th:nth-child(16),
    #colTable.col-md-4 .machinery-table td:nth-child(16),
    #colTable.col-md-4 .machinery-table th:nth-child(17),
    #colTable.col-md-4 .machinery-table td:nth-child(17) {
        display: none !important;
    }

    .machinery-table thead th {
        font-size: .78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #080808ff;
        background: #f7fafc;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    .machinery-table tbody td {
        font-size: .85rem;
        vertical-align: middle !important;
    }

    .machinery-table tbody tr:hover {
        background: #f1f5f9;
    }

    .mach-thumb-wrapper {
        width: 90px;
        height: 60px;
        border-radius: .35rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #edf2f7;
    }

    .mach-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .machinery-table .badge {
        font-weight: 600;
    }

    .machinery-table .btn-group .btn {
        border-radius: .25rem;
    }

    /* --- CARD HEADER --- */
    .mach-form-card .card-header {
        background: #f7f9fc;
        color: #343a40;
        border-bottom: 1px solid #dee2e6;
        padding: .6rem .75rem;
    }

    /* --- TITULOS DE SECCIÓN --- */
    .section-title {
        font-size: .90rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: .04em;
        color: #16436dff;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: .35rem;
        margin-bottom: .65rem;
        display: flex;
        align-items: center;
    }

    .section-title i {
        font-size: 1rem;
        margin-right: .4rem;
        color: #3182ce !important;
    }

    /* --- CONTENEDORES DE GRUPO --- */
    .field-group {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: .35rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    /* --- LABELS --- */
    .form-label-sm {
        font-size: .85rem;
        font-weight: 600;
        color: #0e0d0dff;
    }

    /* --- INPUTS GRANDES, ELEGANTES --- */
    .form-control-lgx {
        height: calc(2.35rem + 2px);
        padding: .45rem .75rem;
        font-size: 1rem;
        border-radius: .35rem;
        border: 1px solid #cbd5e0;
        background-color: #fff;
    }

    .form-control-lgx:focus {
        border-color: #3182ce;
        box-shadow: 0 0 0 0.15rem rgba(49, 130, 206, 0.25);
    }

    /* ====== MODO COMPACTO DEL FORM ====== */

    /* Menos padding en el card */
    .mach-form-card .card-body {
        padding: 0.75rem 1rem;
    }

    /* Títulos de sección más pegados */
    .section-title {
        font-size: .85rem;
        padding-bottom: .25rem;
        margin-bottom: .4rem;
    }

    /* Contenedor de grupo: menos aire */
    .field-group {
        padding: 0.6rem 0.75rem;
        margin-bottom: 0.6rem;
    }

    /* Menos margen entre inputs */
    .mach-form-card .form-group {
        margin-bottom: 0.35rem;
    }

    .mach-form-card .form-row {
        margin-bottom: 0.15rem;
    }

    /* Inputs un poco más bajos */
    .form-control-lgx {
        height: calc(2rem + 2px);
        padding: .3rem .6rem;
        font-size: .9rem;
    }

    /* Textarea también compacto */
    textarea.form-control-lgx {
        min-height: 60px;
    }

    /* --- PREVIEW DE IMÁGENES --- */
    #imagesPreview img {
        max-width: 270px;
        max-height: 270px;
        object-fit: cover;
        border-radius: .35rem;
        border: 1px solid #cbd5e0;
        margin-right: .5rem;
        margin-bottom: .5rem;
    }

    .btn-primary {
        font-size: .95rem;
        padding: .55rem 1rem;
        border-radius: .35rem;
    }

    #imagesPreview {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    #imagesPreview .preview-wrapper {
        position: relative;
        display: inline-block;
        margin-right: 8px;
        margin-bottom: 8px;
        cursor: move;
    }

    #imagesPreview .preview-wrapper.drag-over {
        outline: 2px dashed #007bff;
        outline-offset: 2px;
    }

    #imagesPreview img.preview-img {
        max-width: 270px;
        height: auto;
        border-radius: .35rem;
        border: 1px solid #dee2e6;
        background: #fff;
        display: block;
    }

    #imagesPreview .remove-image-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: none;
        background: rgba(0, 0, 0, .7);
        color: #fff;
        font-size: 14px;
        line-height: 22px;
        text-align: center;
        cursor: pointer;
        padding: 0;
    }

    /* MODAL IMÁGENES GRANDES */
    .image-modal {
        display: none;
        position: fixed;
        z-index: 99999;
        padding-top: 60px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.85);
    }

    .image-modal-content {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 85vh;
    }

    .image-modal-close {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #ffffff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    .image-modal-prev,
    .image-modal-next {
        position: absolute;
        top: 50%;
        color: #ffffff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        padding: 10px 16px;
        user-select: none;
    }

    .image-modal-prev {
        left: 20px;
    }

    .image-modal-next {
        right: 20px;
    }

    .image-modal-prev:hover,
    .image-modal-next:hover {
        background-color: rgba(0, 0, 0, 0.3);
        border-radius: 4px;
    }
</style>
@endsection


@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const table = $('#machineryTable').DataTable({
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            columnDefs: [{
                    orderable: false,
                    targets: [1, -1]
                } // Imagen y Actions sin orden
            ],
        });

        // SELECCIONAR MÁQUINA DESDE EL MODAL
        $(document).on('click', '.select-machinery', function() {

            const id = $(this).data('id');
            const code = $(this).data('code');
            const name = $(this).data('name');
            const brand = $(this).data('brand');
            const location = $(this).data('location');
            const typeWork = $(this).data('type-work');

            $('#selectedMachineId').val(id);
            $('#mc_code').val(code);
            $('#mc_name').val(name);
            $('#mc_brand').val(brand);
            $('#mc_location').val(location);
            $('#mc_type_work').val(typeWork);

            $('#selectMachineryModal').modal('hide');

            $('#colTable').removeClass('col-md-12').addClass('col-md-4');
            $('#colForm').removeClass('d-none');
        });

        // DATEPICKER (MONTH/YEAR)
        $('#yearPicker').datetimepicker({
            format: 'MM/YYYY',
            viewMode: 'months',
            useCurrent: false
        });

        $('#yearPicker').on('change.datetimepicker', function(e) {
            if (!e.date) {
                $('#year').val('');
                return;
            }
            const year = e.date.year();
            const month = String(e.date.month() + 1).padStart(2, '0');
            $('#year').val(`${year}-${month}-01`);
        });

        // --------- IMÁGENES: PREVIEW + ELIMINAR + DRAG&DROP + MODAL ---------
        const imagesInput = document.getElementById('imagesInput');
        const imagesPreview = document.getElementById('imagesPreview');
        let selectedFiles = [];

        // CERRAR FORMULARIO
        $('#btnCancelForm').on('click', function() {
            $('#createMachineryForm')[0].reset();
            $('#colForm').addClass('d-none');
            $('#colTable').removeClass('col-md-4').addClass('col-md-12');

            if (imagesPreview) imagesPreview.innerHTML = '';
            if (imagesInput) imagesInput.value = '';
            selectedFiles = [];
        });

        if (imagesInput && imagesPreview) {

            function syncInputFiles() {
                const dataTransfer = new DataTransfer();
                selectedFiles.forEach(file => dataTransfer.items.add(file));
                imagesInput.files = dataTransfer.files;
            }

            function renderImagesPreview() {
                imagesPreview.innerHTML = '';
                selectedFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'preview-wrapper';
                        wrapper.dataset.index = index;

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = file.name;
                        img.className = 'preview-img';
                        img.dataset.index = index;

                        const btnRemove = document.createElement('button');
                        btnRemove.type = 'button';
                        btnRemove.className = 'remove-image-btn';
                        btnRemove.dataset.index = index;
                        btnRemove.innerHTML = '&times;';

                        wrapper.appendChild(img);
                        wrapper.appendChild(btnRemove);
                        imagesPreview.appendChild(wrapper);
                    };
                    reader.readAsDataURL(file);
                });
            }

            imagesInput.addEventListener('change', function() {
                const newFiles = Array.from(this.files);
                selectedFiles = selectedFiles.concat(newFiles);

                if (selectedFiles.length > 3) {
                    selectedFiles = selectedFiles.slice(0, 3);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Limit reached', 'Only 3 images will be used.', 'info');
                    }
                }

                syncInputFiles();
                renderImagesPreview();
                this.value = '';
            });

            Sortable.create(imagesPreview, {
                animation: 150,
                ghostClass: 'drag-over',
                onEnd: function(evt) {
                    const oldIndex = evt.oldIndex;
                    const newIndex = evt.newIndex;
                    if (oldIndex === newIndex) return;

                    const moved = selectedFiles.splice(oldIndex, 1)[0];
                    selectedFiles.splice(newIndex, 0, moved);
                    syncInputFiles();

                    const wrappers = imagesPreview.querySelectorAll('.preview-wrapper');
                    wrappers.forEach((w, idx) => {
                        w.dataset.index = idx;
                        const img = w.querySelector('.preview-img');
                        if (img) img.dataset.index = idx;
                        const btn = w.querySelector('.remove-image-btn');
                        if (btn) btn.dataset.index = idx;
                    });
                }
            });

            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('imageModalImg');
            const modalClose = document.querySelector('.image-modal-close');
            const modalPrev = document.querySelector('.image-modal-prev');
            const modalNext = document.querySelector('.image-modal-next');
            let currentImageIndex = 0;

            function showModalImageByIndex(index) {
                const thumbs = imagesPreview.querySelectorAll('.preview-img');
                if (!thumbs.length || !modal || !modalImg) return;
                if (index < 0) index = thumbs.length - 1;
                if (index >= thumbs.length) index = 0;
                currentImageIndex = index;
                modalImg.src = thumbs[currentImageIndex].src;
                modal.style.display = 'block';
            }

            imagesPreview.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-image-btn')) {
                    const index = parseInt(e.target.dataset.index, 10);
                    selectedFiles.splice(index, 1);
                    syncInputFiles();
                    renderImagesPreview();
                    return;
                }
                if (e.target.classList.contains('preview-img')) {
                    const idx = parseInt(e.target.dataset.index, 10) || 0;
                    showModalImageByIndex(idx);
                }
            });

            if (modalClose) {
                modalClose.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            }

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) modal.style.display = 'none';
                });
            }

            if (modalPrev) {
                modalPrev.addEventListener('click', function(e) {
                    e.stopPropagation();
                    showModalImageByIndex(currentImageIndex - 1);
                });
            }

            if (modalNext) {
                modalNext.addEventListener('click', function(e) {
                    e.stopPropagation();
                    showModalImageByIndex(currentImageIndex + 1);
                });
            }
        }

    });
</script>
@endpush