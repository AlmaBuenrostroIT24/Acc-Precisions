{{-- MODAL: NCR (reused from orders/schedule_finished) --}}
<div class="modal fade" id="ncrModal" tabindex="-1" role="dialog" aria-labelledby="ncrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <form id="ncrForm">
                <div class="modal-header py-2 erp-ncr-modal-header">
                    <div class="d-flex align-items-center justify-content-between w-100" style="gap:.75rem;">
                        <div class="d-flex align-items-center" style="gap:.6rem;">
                            <span class="erp-ncr-title-icon" aria-hidden="true">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                            <div class="d-flex flex-column">
                                <h5 class="modal-title mb-0" id="ncrModalLabel">Create Non-Conformance</h5>
                                <small class="erp-ncr-subtitle">Register</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap justify-content-end" style="gap:.4rem;">
                            <span class="erp-ncr-chip" title="Work ID">
                                <i class="fas fa-hashtag mr-1 text-info"></i>
                                <span id="ncrHeaderWorkId">—</span>
                            </span>
                            <span class="erp-ncr-chip" title="Customer">
                                <i class="fas fa-user-tag mr-1 text-success"></i>
                                <span id="ncrHeaderCustomer">—</span>
                            </span>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-2 erp-ncr-modal-body">
                    <input type="hidden" id="ncrOrderId">
                    <input type="hidden" id="ncrPostUrl">

                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 mb-2">
                            <label class="mb-1 erp-ncr-label" for="ncrReviewer">Reviewer</label>
                            <div class="input-group input-group-sm erp-ncr-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user-check text-primary"></i></span>
                                </div>
                                <input id="ncrReviewer" type="text" class="form-control erp-ncr-control" readonly value="{{ auth()->user()->name ?? auth()->user()->email ?? '' }}" data-default="{{ auth()->user()->name ?? auth()->user()->email ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-12 col-md-2 mb-2" id="ncrDateCol">
                            <label for="ncrDate" class="mb-1 erp-ncr-label">Date</label>
                            <div class="input-group input-group-sm erp-ncr-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt text-secondary"></i></span>
                                </div>
                                <input type="date" id="ncrDate" class="form-control erp-ncr-control">
                            </div>
                        </div>

                        <div class="form-group col-12 col-md-3 mb-2" id="ncrCustomerCol">
                            <label class="mb-1 erp-ncr-label" for="ncrCustomer">Customer</label>
                            <div class="input-group input-group-sm erp-ncr-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user-tag text-success"></i></span>
                                </div>
                                <input id="ncrCustomer" type="text" class="form-control erp-ncr-control" readonly>
                            </div>
                        </div>

                        <div class="form-group col-12 col-md-2 mb-2" id="ncrNumberCol">
                            <label for="ncrNumber" class="mb-1 erp-ncr-label">NCR Number</label>
                            <div class="input-group input-group-sm erp-ncr-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-clipboard-check text-warning"></i></span>
                                </div>
                                <input type="text" id="ncrNumber" class="form-control erp-ncr-control" maxlength="50" placeholder="e.g. NCR-1234" readonly>
                            </div>
                        </div>

                        <div class="form-group col-12 col-md-2 mb-2" id="ncrNcarTypeCol">
                            <label for="ncrNcarType" class="mb-1 erp-ncr-label">NCAR Type</label>
                            <div class="input-group input-group-sm erp-ncr-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-exchange-alt text-primary"></i></span>
                                </div>
                                <select id="ncrNcarType" class="form-control erp-ncr-control">
                                    <option value="">Select...</option>
                                    <option value="internal">Internal NCAR</option>
                                    <option value="external">External NCAR</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-12 col-md-3 mb-2 d-none" id="ncrStageCol">
                            <label for="ncrStage" class="mb-1 erp-ncr-label font-weight-bold">Stage</label>
                            <div class="input-group input-group-sm erp-ncr-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-layer-group text-secondary"></i></span>
                                </div>
                                <select id="ncrStage" class="form-control erp-ncr-control">
                                    <option value="">Select...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="erp-ncr-orderbox mb-2">
                        <div class="erp-ncr-orderbox-title">Impact</div>

                        <div class="form-row">
                            <div class="form-group col-6 col-md-3 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrWorkId">Work ID</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-hashtag text-info"></i></span>
                                    </div>
                                    <input id="ncrWorkId" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>

                            <div class="form-group col-6 col-md-3 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrCo">CO</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-file-invoice text-primary"></i></span>
                                    </div>
                                    <input id="ncrCo" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>

                            <div class="form-group col-6 col-md-3 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrCustPo">Cust PO</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-receipt text-success"></i></span>
                                    </div>
                                    <input id="ncrCustPo" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>

                            <div class="form-group col-6 col-md-3 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrPn">PN</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-tag text-warning"></i></span>
                                    </div>
                                    <input id="ncrPn" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrOperation">Operation</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-tasks text-secondary"></i></span>
                                    </div>
                                    <input id="ncrOperation" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>

                            <div class="form-group col-6 col-md-3 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrQty">Qty</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calculator text-secondary"></i></span>
                                    </div>
                                    <input id="ncrQty" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>

                            <div class="form-group col-6 col-md-3 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrWoQty">WO Qty</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-cubes text-secondary"></i></span>
                                    </div>
                                    <input id="ncrWoQty" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="mb-1 erp-ncr-label" for="ncrDescription">Part Description</label>
                            <textarea id="ncrDescription" class="form-control form-control-sm erp-ncr-control" rows="2" readonly></textarea>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="ncrNotes" class="mb-1 erp-ncr-label">Notes</label>
                        <textarea id="ncrNotes" class="form-control form-control-sm erp-ncr-control" rows="3" maxlength="2000" placeholder="Details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 erp-ncr-modal-footer">
                    <button type="button" class="btn btn-light btn-sm erp-ncr-btn" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm erp-ncr-btn" id="ncrSaveBtn">
                        Create NCR
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
