<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header bg-light text-white">
        <h5 class="modal-title" id="modalTitle">Search and delete Order</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search by Work ID, PN, Description, Client...">

        <div class="table-responsive">
          <table class="table table-bordered table-hover table-sm" id="searchTable">
            <thead class="thead-light">
              <tr>
                <th>WORK ID</th>
                <th>PN</th>
                <th>Descripción</th>
                <th>CUSTOMER</th>
                <th>DUE DATE</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
