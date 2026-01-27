<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form method="POST" action="" id="edit-form"> {{-- Ruta se asigna dinámicamente con JS --}}
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header fai-modal-header align-items-center py-2">
                    <div class="d-flex align-items-center">
                        <span class="fai-modal-icon mr-2"><i class="fas fa-clipboard-check text-info"></i></span>
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
                                <div class="form-group col-md-3 mb-0">
                                    <label>QTY. TO CHECK</label>
                                    <select class="form-control" id="edit-sampling-type" name="sampling_type">
                                        <option value="normal" selected>Normal</option>
                                        <option value="tightened">Tightened</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 mb-0">
                                    <label>SAMPLING</label>
                                    <input type="text" class="form-control" id="edit-sampling-result" name="sampling_qty" readonly>
                                </div>
                                <div class="form-group col-md-3 mb-0 d-flex align-items-end">
                                    <div class="w-100">
                                        <label for="edit-extra">NO.OPS</label>
                                        <div class="d-flex align-items-end">
                                            <input type="hidden" id="order-id">
                                            <input type="text" class="form-control w-auto" style="max-width: 90px;" id="operationInput" name="dynamic_field[]">
                                            <button type="button" class="btn btn-sm btn-erp-primary btn-erp btn-erp-icon ml-2 d-none" id="addOperationBtn" title="Save ops">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 mb-0 d-flex align-items-end justify-content-end">
                                    <button type="button" class="btn btn-erp-primary btn-erp ml-2" id="addRowBtn" disabled>
                                        <i class="fas fa-plus mr-1"></i> Inspection
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Columna derecha: segundo filtro + botón + gráfica -->
                        <div class="col-md-5 pl-md-4">
                            <!-- Segundo bloque de filtros -->
                            <div class="form-group col-md-12">
                                <div class="d-flex align-items-center justify-content-between mb-1 fai-packet-head">
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-erp-primary btn-erp erp-table-btn mr-2" id="openPacketPdfBtn" title="Open PDF">
                                            <i class="fas fa-file-alt"></i>
                                        </button>
                                        <div>
                                            <label class="mb-0">FAI/IPI Inspection Packet Report</label>
                                            <small class="text-muted d-block">Resumen</small>
                                        </div>
                                    </div>
                                </div>
                                <div id="inspection-missing-container" class="fai-packet-box">
                                    <div id="inspection-missing" class="fai-packet-report m-0"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor con scroll -->
                    <div class="border rounded" style="max-height: 450px; overflow-y: auto;">
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
                    <button type="button" class="btn btn-erp-success btn-erp" id="btnFinishInspection">
                        <i class="fas fa-check-circle mr-1"></i> Finish Inspection
                    </button>
                    <button type="button" class="btn btn-erp-secondary btn-erp" data-dismiss="modal">Exit</button>
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

    /* Todo el texto del modal en negro */
    #editModal,
    #editModal .modal-content,
    #editModal .modal-content * {
        color: #0f172a !important;
    }

    #editModal .form-control::placeholder {
        color: rgba(15, 23, 42, 0.70) !important;
    }

    #editModal .modal-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .fai-modal-header {
        background: #f8fafc;
        border-left: 4px solid rgba(15, 23, 42, 0.10);
    }

    .fai-modal-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(23, 162, 184, 0.12);
        /* info soft */
        color: #17a2b8 !important;
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

    /* Variantes tipo Schedule (fondo claro + borde + ícono con color) */
    #editModal .btn-erp-primary,
    #editModal .btn-erp-success,
    #editModal .btn-erp-warning,
    #editModal .btn-erp-danger,
    #editModal .btn-erp-secondary {
        background: #f8fafc;
        border: 1px solid #d5d8dd;
        color: #1f2937;
        box-shadow: none;
        font-weight: 800;
    }

    #editModal .btn-erp-primary i {
        color: #0b5ed7 !important;
    }

    #editModal .btn-erp-success i {
        color: #0f5132 !important;
    }

    #editModal .btn-erp-warning i {
        color: #f59e0b !important;
    }

    #editModal .btn-erp-danger i {
        color: #b91c1c !important;
    }

    #editModal .btn-erp-secondary {
        color: #475569;
    }

    #editModal .btn-erp-secondary i {
        color: #475569 !important;
    }

    /* Botones ícono dentro de la tabla del modal */
    #editModal .erp-table-btn {
        height: 34px;
        width: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    #editModal .erp-table-btn i {
        font-size: 1.05rem;
        line-height: 1;
    }

    #editModal .erp-table-btn:disabled {
        opacity: 0.7;
        cursor: default;
    }

    #editModal .btn-erp-icon {
        height: 34px;
        width: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
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
    .fai-packet-head {
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        margin-bottom: 8px !important;
    }

    .fai-packet-head label {
        font-weight: 700;
        color: #0f172a;
    }

    .fai-packet-box {
        min-height: 90px;
        background: #fff;
        border: 1px solid #d5d8dd;
        border-radius: 12px;
        padding: 8px;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
        font-size: 14px;
        line-height: 1.35;
    }

    /* Estado del packet (sin fondos "amarillos/verdes" fuertes) */
    .fai-packet-box.is-ok {
        border-color: rgba(34, 197, 94, 0.45);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06), 0 0 0 2px rgba(34, 197, 94, 0.08);
    }

    .fai-packet-box.is-warn {
        border-color: rgba(245, 158, 11, 0.55);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06), 0 0 0 2px rgba(245, 158, 11, 0.10);
    }

    .fai-packet-report {
        font-family: inherit;
        color: #0f172a;
        font-size: 14px;
        line-height: 1.35;
        max-height: 260px;
        overflow: auto;
    }

    /* Evitar efecto "caja dentro de caja": si renderizamos la tabla ERP dentro,
       dejamos que la tabla sea el "card" visual y el contenedor no agregue otro marco. */
    .fai-packet-box.has-summary {
        border: 0;
        box-shadow: none;
        padding: 0;
        background: transparent;
    }

    /* Tabla resumen tipo ERP */
    .fai-summary-table--erp {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
        border: 1px solid rgba(15, 23, 42, 0.12);
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .fai-summary-table--erp thead th,
    .fai-summary-table--erp .fai-summary-thead th {
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        color: #0f172a;
        font-weight: 800;
        font-size: 12px;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(15, 23, 42, 0.12);
        text-align: center;
        padding: 6px 8px;
        white-space: nowrap;
    }

    .fai-summary-table--erp tbody td {
        vertical-align: middle;
        padding: 6px 8px;
        text-align: center;
        font-size: 14px;
        color: #0f172a;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
    }

    .fai-summary-table--erp tbody tr:nth-child(even) td {
        background: rgba(248, 250, 252, 0.85);
    }

    .fai-summary-table--erp tbody tr:hover td {
        background: rgba(2, 6, 23, 0.04);
    }

    .fai-summary-table .badge {
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .fai-icon-badge i {
        font-size: 0.95rem;
        line-height: 1;
    }

    .fai-filter {
        cursor: pointer;
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
        let operationWatchTimer = null;
        const PDF_BASE = '/qa/faisummary';

        function setOperationLocked(locked) {
            $operationInput
                .prop('readonly', locked)
                .toggleClass('bg-light', locked);
            $addOperationBtn.toggleClass('d-none', locked);
        }

        // Arrancar oculto/bloqueado para evitar destellos iniciales
        setOperationLocked(true);

        function refreshOperationUI() {
            const hasVal = ($operationInput.val() || '').trim().length > 0;
            setOperationLocked(hasVal);
        }

        function markDisabledRows() {
            $tbody.find('tr').each(function() {
                const hasEnabled = $(this).find('input:enabled, select:enabled, textarea:enabled, button:enabled').length;
                $(this).toggleClass('fai-row-disabled', !hasEnabled);
            });
        }

        // Observar cambios en el tbody (nuevas filas, ediciones)
        if ($tbody.length) {
            const observer = new MutationObserver(markDisabledRows);
            observer.observe($tbody[0], {
                childList: true,
                subtree: true
            });
        }

        function hideNewInspection() {
            $addRowBtn.addClass('d-none').prop('disabled', true);
        }

        function showNewInspection() {
            $addRowBtn.removeClass('d-none').prop('disabled', false);
        }

        // Bandera: operacion guardada; arranca true solo si llega valor desde BD
        let operationSaved = (($operationInput.val() || '').trim().length > 0);
        // Arrancamos bloqueado/oculto para evitar destellos antes de que carguen datos
        setOperationLocked(true);
        hideNewInspection();

        function syncOperationState(forceDetect = false) {
            const hasVal = (($operationInput.val() || '').trim().length > 0);
            // Si llega valor tardío desde BD, marcalo como guardado solo cuando se fuerza la deteccion
            if (forceDetect && hasVal) {
                operationSaved = true;
            }

            if (operationSaved) {
                showNewInspection();
                setOperationLocked(true);
            } else {
                hideNewInspection();
                setOperationLocked(false);
            }
        }

        function startOperationWatcher(ms = 8000, interval = 80) {
            if (operationWatchTimer) clearInterval(operationWatchTimer);
            const end = Date.now() + ms;
            const tick = () => {
                syncOperationState(true);
                if (Date.now() > end) {
                    clearInterval(operationWatchTimer);
                    operationWatchTimer = null;
                }
            };
            operationWatchTimer = setInterval(tick, interval);
            tick();
        }

        function stopOperationWatcher() {
            if (operationWatchTimer) {
                clearInterval(operationWatchTimer);
                operationWatchTimer = null;
            }
        }

        function updateNewInspectionVisibility() {
            if (operationSaved) showNewInspection();
            else hideNewInspection();
        }

        // Solo tras guardar NO.OPS (botón verde) mostramos "New Inspection"
        $addOperationBtn.on('click', function() {
            const val = ($operationInput.val() || '').trim();
            if (val) {
                operationSaved = true;
                syncOperationState();
            } else {
                operationSaved = false;
                syncOperationState();
            }
        });

        // Permitir editar el campo al hacer click y mostrar nuevamente el boton verde
        $operationInput.on('focus click', function() {
            stopOperationWatcher();
            setOperationLocked(false);
        });

        // Sincronizar al escribir o cambiar valor manualmente
        $operationInput.on('input change', function() {
            operationSaved = false;
            syncOperationState();
        });

        // Al cambiar de pestaAña (Process / Pending) recalcular visibilidad del botA3n
        $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
            startOperationWatcher();
        });

        // Antes de mostrar el modal, resetear estado y recalcular (evita arrastre entre tablas)
        $('#editModal').on('show.bs.modal', function() {
            stopOperationWatcher();
            operationSaved = false;
            hideNewInspection();
            setOperationLocked(false);
            // Recalcula tras que otros listeners (p.e. partsrevision) llenen los campos
            setTimeout(() => {
                operationSaved = (($operationInput.val() || '').trim().length > 0);
                syncOperationState(true);
                startOperationWatcher();
            }, 0);
        });

        // Al cerrar, limpiar flags y estado visual
        $('#editModal').on('hidden.bs.modal', function() {
            stopOperationWatcher();
            operationSaved = false;
            hideNewInspection();
            setOperationLocked(false);
        });

        // Al abrir el modal, habilitar si ya hay operacion guardada desde BD
        $('#editModal').on('shown.bs.modal', function() {
            markDisabledRows();
        });

        // Arrancar watcher inicial para reflejar valores si ya vienen cargados
        startOperationWatcher();

        // Exponer helper global por si se necesita llamar manualmente
        window.markDisabledRows = markDisabledRows;

        // Abrir PDF del packet FAI/IPI con el id actual del modal
        $('#openPacketPdfBtn').on('click', function() {
            const id = ($('#edit-id').val() || $('#order-id').val() || '').trim();
            if (!id) {
                Swal.fire('Sin orden', 'Selecciona una orden para generar el PDF.', 'warning');
                return;
            }
            const url = `${PDF_BASE}/${encodeURIComponent(id)}/pdf`;
            window.open(url, '_blank');
        });

        // ------------------------------
        // Filtro por operación al hacer click en los badges del resumen
        // ------------------------------
        let currentOpFilter = null;

        function getRowOp($row) {
            const opSel = $row.find('select[name="operation[]"], input[name="operation[]"]');
            return (opSel.val() || opSel.text() || '').trim().toUpperCase();
        }

        function applyOpFilter(op) {
            const target = (op || '').trim().toUpperCase();
            if (!target) {
                $tbody.find('tr').show();
                currentOpFilter = null;
                return;
            }
            $tbody.find('tr').each(function() {
                const $r = $(this);
                const opVal = getRowOp($r);
                if (opVal === target) {
                    $r.show();
                } else {
                    $r.hide();
                }
            });
            currentOpFilter = target;
        }

        $(document).on('click', '.fai-summary-row', function() {
            const op = ($(this).data('op') || '').toString();
            // Toggle: si ya está filtrando por la misma op, quitar filtro
            if (currentOpFilter === op.toUpperCase()) {
                applyOpFilter('');
            } else {
                applyOpFilter(op);
            }
        });
    })();
</script>
@endpush
