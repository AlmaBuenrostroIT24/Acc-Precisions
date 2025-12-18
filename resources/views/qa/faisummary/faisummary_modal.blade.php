<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form method="POST" action="" id="edit-form"> {{-- Ruta se asigna dinámicamente con JS --}}
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header fai-modal-header align-items-center py-2">
                    <div class="d-flex align-items-center">
                        <span class="fai-modal-icon mr-2"><i class="fas fa-clipboard-check"></i></span>
                        <div>
                            <h5 class="modal-title mb-0 text-dark">Inspection Process</h5>
                            <small class="d-block text-muted">Capture and track FAI / IPI inspections</small>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <!-- Campos fijos -->
                    <div class="row">
                        <!-- Columna izquierda: primer filtro + botón + gráfica -->
                        <div class="col-md-7 pr-md-4">
                            <!-- Primer bloque de filtros -->
                            <div class="row mb-3">
                                <div class="form-group col-md-3">
                                    <label for="edit-inspector">INSPECTOR</label>
                                    <input type="text" class="form-control" id="edit-inspector" name="inspector"
                                        value="{{ Auth::user()->name }}" readonly>
                                </div>
                                <div class="form-group col-md-5">
                                    <label>PART# REV.</label>
                                    <input type="text" class="form-control" id="edit-fullpart" name="full_part" readonly>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>JOB</label>
                                    <input type="text" class="form-control" id="edit-workid" name="work_id" readonly>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>WO QTY</label>
                                    <input type="text" class="form-control" id="edit-woqty" name="wo_qty" readonly>
                                </div>
                            </div>
                            <div class="row align-items-end mb-3">

                                <div class="form-group col-md-3">
                                    <label>QTY. TO CHECK</label>
                                    <select class="form-control" id="edit-sampling-type" name="sampling_type">
                                        <option value="normal" selected>Normal</option>
                                        <option value="tightened">Tightened</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>SAMPLING</label>
                                    <input type="text" class="form-control" id="edit-sampling-result" name="sampling_qty" readonly>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="edit-extra">NO.OPS</label>
                                    <div class="d-flex">
                                        <input type="hidden" id="order-id">
                                        <input type="text" class="form-control" id="operationInput" name="dynamic_field[]">
                                        <button type="button" class="btn btn-success btn-erp ml-2" id="addOperationBtn"><i class="fas fa-save mr-1"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Columna derecha: segundo filtro + botón + gráfica -->
                        <div class="col-md-5 pl-md-4">
                            <!-- Segundo bloque de filtros -->
                            <div class="form-group col-md-12">
                                <div class="d-flex align-items-center justify-content-between mb-1 fai-packet-head">
                                    <div class="d-flex align-items-center">
                                        <span class="fai-packet-icon mr-2"><i class="fas fa-file-alt"></i></span>
                                        <div>
                                            <label class="mb-0">FAI/IPI Inspection Packet Report</label>
                                            <small class="text-muted d-block">Resumen y notas del paquete</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-erp btn-sm d-none" id="addRowBtn" disabled>
                                        <i class="fas fa-plus mr-1"></i> New Inspection
                                    </button>
                                </div>
                                <div id="inspection-missing-container" class="fai-packet-box">
                                    <pre id="inspection-missing" class="m-0" style="white-space: pre-wrap;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor con scroll -->
                    <div class="border rounded" style="max-height: 500px; overflow-y: auto;">
                        <!-- Tabla dinámica -->
                        <table class="table table-sm table-hover mb-0" id="dynamicTable">
                            <thead>
                                <tr>
                                    <th style="width: 1%;">DATE</th>
                                    <th style="width: 7%;">TYPE</th>
                                    <th style="width: 8%;">OPERATION</th>
                                    <th style="width: 9%;">OPERATOR</th>
                                    <th style="width: 7%;">RESULTS</th>
                                    <th style="width: 11%;">SB/IS</th>
                                    <th style="width: 20%;">OBSERVATION</th>
                                    <th style="width: 8%;">STATION</th>
                                    <th style="width: 10%;">METHOD</th>
                                    <th style="width: 7%;">QTY INSP</th>
                                    <th style="width: 10%;">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="rowsContainer">
                                <!-- Se agregan dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-success btn-erp" id="btnFinishInspection">
                        <i class="fas fa-check-circle mr-1"></i> Finish Inspection
                    </button>
                    <button type="button" class="btn btn-light border btn-erp text-secondary" data-dismiss="modal">Exit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* 2025-12-17: estilos puntuales del modal para look más profesional / ERP */
    #editModal .modal-dialog {
        max-width: 90%;
    }

    #editModal .modal-content {
        border-radius: 14px;
    }

    #editModal .modal-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .fai-modal-header {
        background: #f8fafc;
        border-left: 4px solid #0d6efd;
    }

    .fai-modal-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(13, 110, 253, 0.12);
        color: #0d6efd;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    #editModal .modal-body label {
        font-weight: 700;
        color: #1f2937;
        font-size: 0.85rem;
    }

    /* 2025-12-17: botones estilo ERP */
    .btn-erp {
        border-radius: 10px;
        font-weight: 700;
        letter-spacing: 0.01em;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.08);
        transition: transform .08s ease, box-shadow .12s ease, filter .12s ease;
    }

    .btn-erp:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(16, 24, 40, 0.12);
        filter: brightness(1.02);
    }

    .btn-erp:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(16, 24, 40, 0.10);
    }

    .btn-erp:focus {
        outline: none;
        box-shadow: 0 0 0 .18rem rgba(13, 110, 253, 0.15), 0 2px 10px rgba(16, 24, 40, 0.08);
    }

    /* 2025-12-17: inputs estilo ERP (sobrios, sin brillos) */
    #editModal .form-control,
    #editModal .custom-select,
    #editModal select.form-control {
        border: 1px solid rgba(15, 23, 42, 0.18);
        border-radius: 10px;
        box-shadow: none;
        font-size: 0.9rem;
        color: #0f172a;
        background: #fff;
        padding: 0.4rem 0.65rem;
        transition: border-color .12s ease, box-shadow .12s ease;
    }

    #editModal .form-control:focus,
    #editModal .custom-select:focus,
    #editModal select.form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 .15rem rgba(13, 110, 253, 0.15);
    }

    #editModal .form-control[readonly] {
        background: #f8fafc;
        color: #475569;
    }

    /* 2025-12-17: enfatizar campos no editables */
    #editModal .form-control[readonly],
    #editModal .form-control:disabled,
    #editModal select.form-control:disabled {
        background: #f1f5f9;
        color: #6b7280;
        border-color: rgba(15, 23, 42, 0.08);
        cursor: not-allowed;
        opacity: 0.95;
    }

    #editModal .btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    #dynamicTable thead th {
        background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #1f2937;
        border: 0;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    #dynamicTable tbody td {
        vertical-align: middle;
        font-size: 0.85rem;
    }

    #dynamicTable tbody tr:hover {
        background: rgba(13, 110, 253, 0.04);
    }

    /* Caja ERP para FAI/IPI packet */
    .fai-packet-head label {
        font-weight: 700;
        color: #0f172a;
    }

    .fai-packet-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #e2e8f0;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .fai-packet-box {
        min-height: 140px;
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f6 100%);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
    }

    /* 2025-12-18: tabla resumen ERP compacta */
    .fai-summary-table {
        font-size: 0.85rem;
        width: 100%;
        table-layout: fixed;
    }

    .fai-summary-table th {
        background: #f1f5f9;
        color: #0f172a;
        border-bottom: 1px solid #e2e8f0;
    }

    .fai-summary-table td {
        vertical-align: middle;
    }

    .fai-summary-table .badge {
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .fai-icon-badge i {
        font-size: 0.95rem;
        line-height: 1;
    }

    /* 2025-12-17: marcar filas no editables */
    #dynamicTable tbody tr.fai-row-disabled {
        opacity: 0.55;
        filter: grayscale(0.35);
        pointer-events: none;
    }
</style>

@push('js')
<script>
    (() => {
        const $tbody = $('#dynamicTable tbody');
        const $addRowBtn = $('#addRowBtn');
        const $operationInput = $('#operationInput');
        const $addOperationBtn = $('#addOperationBtn');

        function markDisabledRows() {
            $tbody.find('tr').each(function() {
                const hasEnabled = $(this).find('input:enabled, select:enabled, textarea:enabled, button:enabled').length;
                $(this).toggleClass('fai-row-disabled', !hasEnabled);
            });
        }

        // Observar cambios en el tbody (nuevas filas, ediciones)
        if ($tbody.length) {
            const observer = new MutationObserver(markDisabledRows);
            observer.observe($tbody[0], { childList: true, subtree: true });
        }

        function hideNewInspection() {
            $addRowBtn.addClass('d-none').prop('disabled', true);
        }

        function showNewInspection() {
            $addRowBtn.removeClass('d-none').prop('disabled', false);
        }

        // Bandera: operación ya guardada (al abrir) o luego de guardar con el botón verde
        let operationSaved = (($operationInput.val() || '').trim().length > 0);

        function updateNewInspectionVisibility() {
            if (operationSaved) showNewInspection();
            else hideNewInspection();
        }

        // Solo tras guardar NO.OPS (botón verde) mostramos "New Inspection"
        $addOperationBtn.on('click', function() {
            const val = ($operationInput.val() || '').trim();
            if (val) {
                operationSaved = true;
                showNewInspection();
            } else {
                hideNewInspection();
            }
        });

        // Al abrir el modal, habilitar si ya hay operación guardada desde BD
        $('#editModal').on('shown.bs.modal', function() {
            operationSaved = (($operationInput.val() || '').trim().length > 0);
            updateNewInspectionVisibility();
            markDisabledRows();
        });

        // Exponer helper global por si se necesita llamar manualmente
        window.markDisabledRows = markDisabledRows;
    })();
</script>
@endpush
