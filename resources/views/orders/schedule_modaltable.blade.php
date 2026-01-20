        <!-- Modal ERP para editar notas -->
        <div class="modal fade erp-modal" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <form id="notesForm" class="modal-content erp-modal-content">
                    <div class="modal-header erp-modal-header">
                        <div class="d-flex align-items-center">
                            <span class="erp-pane-icon erp-pane-icon--info mr-2" aria-hidden="true">
                                <i class="fas fa-sticky-note"></i>
                            </span>
                            <div class="erp-modal-title-wrap">
                                <div class="modal-title erp-modal-title" id="notesModalLabel">Edit Note</div>
                            </div>
                        </div>
                        <button type="button" class="close erp-modal-close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-3">
                        <input type="hidden" id="notesOrderId" />
                        <textarea id="notesTextarea" class="form-control erp-modal-control" rows="6" placeholder="Write a note..."></textarea>
                    </div>
                    <div class="modal-footer erp-modal-footer">
                        <button type="button" class="btn btn-light erp-modal-close-btn" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-erp-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
