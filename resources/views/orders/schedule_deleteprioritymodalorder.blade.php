<div class="modal fade erp-modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content erp-modal-content">
      <div class="modal-header erp-modal-header">
        <div class="d-flex align-items-center">
          <span id="modalModeIconWrap" class="erp-pane-icon erp-pane-icon--danger mr-2" aria-hidden="true">
            <i id="modalModeIcon" class="fas fa-trash-alt"></i>
          </span>
          <div class="erp-modal-title-wrap">
            <div class="modal-title erp-modal-title" id="modalTitle">Search and delete Order</div>
          </div>
        </div>
        <button type="button" class="close erp-modal-close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body pt-3">
        <div class="input-group erp-input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text" aria-hidden="true"><i class="fas fa-search"></i></span>
          </div>
          <input type="text" id="searchInput" class="form-control erp-filter-control" placeholder="Search by Work ID, PN, Description, Customer...">
        </div>

        <div class="table-responsive erp-modal-table-wrap">
          <table class="table table-sm table-hover erp-modal-table" id="searchTable">
            <colgroup>
              <col style="width: 120px;">
              <col style="width: 120px;">
              <col style="width: 320px;">
              <col style="width: 170px;">
              <col style="width: 120px;">
              <col style="width: 90px;">
            </colgroup>
            <thead class="thead-light">
              <tr>
                <th>WORK ID</th>
                <th>PN</th>
                <th>DESCRIPTION</th>
                <th>CUSTOMER</th>
                <th>DUE DATE</th>
                <th class="text-center">ACTION</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer erp-modal-footer">
        <button type="button" class="btn btn-light erp-filter-control erp-modal-close-btn" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
