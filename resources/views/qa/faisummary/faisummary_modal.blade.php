<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 80%;">
        <form method="POST" action="" id="edit-form"> {{-- Ruta se asigna dinámicamente con JS --}}
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">INSPECTION PROCESS</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Campos fijos -->
                    <div class="form-row mb-1">
                        <div class="form-group col-md-1">
                            <label for="edit-inspector">INSPECTOR</label>
                            <input type="text" class="form-control" id="edit-inspector" name="inspector"
                                value="{{ Auth::user()->name }}" readonly>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Part# Rev.</label>
                            <input type="text" class="form-control" id="edit-fullpart" name="full_part" readonly>
                        </div>
                        <div class="form-group col-md-1">
                            <label>JOB</label>
                            <input type="text" class="form-control" id="edit-workid" name="work_id" readonly>
                        </div>
                        <div class="form-group col-md-1">
                            <label>WO QTY</label>
                            <input type="text" class="form-control" id="edit-woqty" name="wo_qty" readonly>
                        </div>
                        <div class="form-group col-md-1">
                            <label>QTY. TO CHECK</label>
                            <select class="form-control" id="edit-sampling-type" name="sampling_type">
                                <option value="normal" selected>Normal</option>
                                <option value="tightened">Tightened</option>
                            </select>
                        </div>
                        <div class="form-group col-md-1">
                            <label for="edit-extra">NO.OPS</label>
                            <div class="d-flex">
                                <input type="hidden" id="order-id">
                                <input type="text" class="form-control" id="operationInput" name="dynamic_field[]">
                                <button type="button" class="btn btn-success ml-2" id="addOperationBtn">+</button>
                            </div>
                        </div>
                        <div class="form-group col-md-1">
                            <label>SAMPLING</label>
                            <input type="text" class="form-control" id="edit-sampling-result" name="sampling_qty" readonly>
                        </div>
                        <div class="form-group col-md-4">
                            <label>REPORT</label>
                            <div id="inspection-missing-container" class="border rounded p-2 bg-light" style="min-height: 120px;">
                                <pre id="inspection-missing" class="m-0" style="white-space: pre-wrap;"></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla dinámica -->
                    <table class="table table-bordered" id="dynamicTable">
                        <thead>
                            <tr>
                                <th style="width: 8%;">DATE</th>
                                <th style="width: 8%;">INSP TYPE</th>
                                <th style="width: 8%;">OPERATION</th>
                                <th style="width: 8%;">OPERATOR</th>
                                <th style="width: 8%;">RESULTS</th>
                                <th style="width: 11%;">SB/IS</th>
                                <th style="width: 20%;">OBSERVATION</th>
                                <th style="width: 8%;">STATION</th>
                                <th style="width: 12%;">METHOD</th>
                                <th style="width: 9%;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="rowsContainer">
                            <!-- Se agregan dinámicamente -->
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-primary mb-3" id="addRowBtn">Add OP Inspection</button>
                </div>
                <div class="modal-footer">
                   <!-- <button type="submit" class="btn btn-success">Save Inspection</button>-->
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Exit</button>
                </div>
            </div>
        </form>
    </div>
</div>