$(document).ready(function () {
    // Inicializar DataTables
    const permissionsTable = initDataTable("#permissionsTable");
    const rolesTable = initDataTable("#rolesTable");
    const csrfToken = $('meta[name="csrf-token"]').attr("content");

    function initDataTable(selector) {
        return $(selector).DataTable({
            responsive: true,
            lengthChange: true,
            pageLength: 10,
        });
    }

    function showAlert(title, text, type = "success") {
        return Swal.fire({ title, text, type });
    }

    function confirmDelete(form, table, successMsg, errorMsg) {
        const url = form.action;
        const token = $(form).find('input[name="_token"]').val();

        Swal.fire({
            title: "¿Confirm deletion?",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.value) {
                $.post(url, { _token: token, _method: "DELETE" })
                    .done((response) => {
                        if (response.success) {
                            showAlert(
                                "Deleted",
                                response.message || successMsg
                            );
                            table.row($(form).closest("tr")).remove().draw();
                        } else {
                            Swal.fire(
                                "Error",
                                response.message || errorMsg,
                                "error"
                            );
                        }
                    })
                    .fail(() => {
                        Swal.fire("Error", errorMsg, "error");
                    });
            }
        });
    }

    function buildModal({ id, name, type }) {
        const modalId = `edit${capitalize(type)}Modal`;
        const inputId = `${type}NameInput`;
        const url = `/${type}s/${id}`;
        //console.log("buildModal ->", { id, name, type }); // 👈 esto te dirá si `id` llega bien
        const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="${modalId}Form" class="modal-content">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="PUT">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar ${type}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="${inputId}">Nombre del ${type}</label>
                            <input id="${inputId}" type="text" class="form-control" name="name" value="${name}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    `;

        // Si hay modales abiertos, ciérralos y luego abre el nuevo
        if ($(".modal.show").length) {
            $(".modal.show")
                .modal("hide")
                .on("hidden.bs.modal", function () {
                    $(this).remove();
                    setTimeout(() => showNewModal(), 10);
                });
        } else {
            showNewModal();
        }

        function showNewModal() {
            $(".modal-backdrop").remove();
            $("body").removeClass("modal-open");
            $(".wrapper").removeAttr("aria-hidden");
            $(".modal").removeAttr("aria-hidden");

            $(`#${modalId}`).remove();
            $("body").append(modalHtml);
            $(`#${modalId}`).modal("show");

            $(`#${modalId}`).on("shown.bs.modal", function () {
                $(`#${inputId}`).focus();
            });

            // Envío AJAX del formulario
            $(`#${modalId}Form`).on("submit", function (e) {
                e.preventDefault(); // Previene envío normal
                e.stopImmediatePropagation(); // Detiene otros listeners del mismo evento
                const formData = $(this).serialize();

                // console.log('Enviar a:', url);
                // console.log('Datos:', formData);

                $.ajax({
                    url: url, // /roles/66
                    type: "PUT", // ✅ ahora Laravel sí lo reconoce
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            $(`#${modalId}`)
                                .modal("hide")
                                .on("hidden.bs.modal", function () {
                                    $(this).remove();
                                    $("body").removeClass("modal-open");
                                    $(".modal-backdrop").remove();
                                    $(".wrapper").removeAttr("aria-hidden");
                                });
                            updateRow(type, response);
                            showAlert("Updated", response.message);
                        }
                    },
                    error: function (xhr) {
                        const errorMsg =
                            xhr.responseJSON?.message ||
                            `No se pudo actualizar el ${type}`;
                        showAlert("Error", errorMsg, "error");
                    },
                });
            });
        }
    }

    function updateRow(type, response) {
        if (type === "permission") {
            $(`#permissionName${response.permission.id}`).text(
                response.permission.name
            );
            permissionsTable
                .row(`#permissionRow${response.permission.id}`)
                .invalidate()
                .draw(false);
        } else {
            $(`#roleName${response.role.id}`).text(response.role.name);
            rolesTable
                .row(`#roleRow${response.role.id}`)
                .invalidate()
                .draw(false);
        }
    }

    function capitalize(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    // Abrir modales
    $(document).on("click", ".open-edit-permission-modal", function () {
        buildModal({
            id: $(this).data("id"),
            name: $(this).data("name"),
            type: "permission",
        });
    });

    $(document).on("click", ".open-edit-role-modal", function () {
        // Solución al error: aria-hidden no debe estar en ancestros visibles
        $("body, .wrapper, .content-wrapper").removeAttr("aria-hidden");
        buildModal({
            id: $(this).data("id"),
            name: $(this).data("name"),
            type: "role",
        });
    });

    // Crear permiso
    $("#create-permission-form").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        $.post(form.attr("action"), form.serialize())
            .done((response) => {
                showAlert("Ready", response.message);
                form[0].reset();
                const rowNode = permissionsTable.row
                    .add([
                        `<span id="permissionName${response.permission.id}">${response.permission.name}</span>`,
                        `
                        <button class="btn btn-warning btn-sm open-edit-permission-modal" data-id="${response.permission.id}" data-name="${response.permission.name}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="/permissions/${response.permission.id}" method="POST" class="d-inline delete-permission-form">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete permission">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>`,
                    ])
                    .draw(false)
                    .node();
                $(rowNode).attr("id", `permissionRow${response.permission.id}`);
            })
            .fail((xhr) => {
                showAlert(
                    "Error",
                    xhr.responseJSON?.message ||
                        "The permission could not be created",
                    "error"
                );
            });
    });

    // Eliminar rol y permiso
    $(document).on("submit", ".delete-form", function (e) {
        e.preventDefault();
        const isRole = $(this).hasClass("delete-role-form");
        const table = isRole ? rolesTable : permissionsTable;
        const successMsg = isRole
            ? "Role successfully deleted."
            : "Permission successfully removed.";
        const errorMsg = isRole
            ? "The role could not be deleted."
            : "The permission could not be removed.";

        confirmDelete(this, table, successMsg, errorMsg);
    });

    // ⚠️ Soluciona el warning de accesibilidad (focus oculto al cerrar modal)
    $(document).on("hide.bs.modal", ".modal", function () {
        document.activeElement?.blur();
    });

    //--------------------------------------------------------------------------------------------------------

    // Asignar permisos (solo a formularios de asignación)
    $(document).on("submit", "form.modal-content", function (e) {
        e.preventDefault();
        const form = $(this);
        $.post(form.attr("action"), form.serialize())
            .done((response) => {
                showAlert("¡Ready!", response.message);
                form.closest(".modal")
                    .modal("hide")
                    .on("hidden.bs.modal", function () {
                        $("body").removeClass("modal-open");
                        $(".modal-backdrop").remove();
                    });

                const badgesHtml = response.permissions
                    .map(
                        (name) =>
                            `<span class="badge badge-info mr-1">${name}</span>`
                    )
                    .join("");
                const row = rolesTable.row(`#roleRow${response.role_id}`);
                if (row.node()) {
                    let rowData = row.data();
                    rowData[1] = badgesHtml;
                    row.data(rowData).invalidate().draw(false);
                }
            })
            .fail((xhr) => {
                showAlert(
                    "Error",
                    xhr.responseJSON?.message ||
                        "Permissions could not be assigned",
                    "error"
                );
            });
    });

    // Crear rol y crear en esa tabla la fila con el nuevo rol
    $("#createRoleForm").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        $.post(form.attr("action"), form.serialize())
            .done((response) => {
                showAlert("¡Ready!", response.message);
                form[0].reset();
                const rowNode = rolesTable.row
                    .add([
                        `<span id="roleName${response.role.id}">${response.role.name}</span>`,
                        "",
                        `

                    <button class="btn btn-primary btn-sm open-assign-permissions-modal" data-id="${response.role.id}" data-name="${response.role.name}">Assign</button>
                    <button class="btn btn-warning btn-sm open-edit-role-modal" data-id="${response.role.id}" data-name="${response.role.name}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="/roles/${response.role.id}" method="POST" class="d-inline delete-role-form">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger btn-sm" title="Delete rol">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>`,
                    ])
                    .draw(false)
                    .node();
                $(rowNode).attr("id", `roleRow${response.role.id}`);
            })
            .fail((xhr) => {
                showAlert(
                    "Error",
                    xhr.responseJSON?.message ||
                        "The role could not be created",
                    "error"
                );
            });
    });
    // clic a assign y abrir dinamicamente el modal de los permisos con checkbox
    $(document).on("click", ".open-assign-permissions-modal", function () {
        const roleId = $(this).data("id");
        const roleName = $(this).data("name");
        const modalId = `#assignPermissionsModal${roleId}`;

        // Si el modal ya existe, solo mostrar
        if ($(modalId).length) {
            $(modalId).modal("show");
            // Y recarga permisos en cada apertura:
            loadPermissions(roleId);
            return;
        }

        // Modal dinámico base
        const modalHtml = `
            <div class="modal fade" id="assignPermissionsModal${roleId}" tabindex="-1" role="dialog" aria-labelledby="assignPermissionsModalLabel${roleId}" aria-hidden="true">
                <div class="modal-dialog" role="document">
                   <form action="/roles/${roleId}/permissions" method="POST" class="modal-content">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <div class="modal-header bg-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-tag text-primary mr-2"></i>
                                <h5 class="modal-title mb-0">Permissions For</h5>
                            </div>
                            <strong class="badge badge-primary px-3 py-2">${roleName}</strong>
                            <button type="button" class="close ml-2" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-muted">Loading permissions...</div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Save</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>`;

        $("body").append(modalHtml);
        $(modalId).modal("show");

        // Cargar permisos vía AJAX
        $.get(`/roles/${roleId}/permissions`)
            .done((data) => {
                const rows = data.permissions
                    .map((p) => {
                        const checked = p.assigned ? "checked" : "";
                        return `
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="${p.name}" ${checked}>
                                <label class="form-check-label">${p.label}</label>
                            </div>
                        </div>`;
                    })
                    .join("");

                $(`${modalId} .modal-body`).html(
                    `<div class="row">${rows}</div>`
                );
            })
            .fail(() => {
                $(`${modalId} .modal-body`).html(
                    '<div class="alert alert-danger">Could not load permissions.</div>'
                );
            });
    });

    // Limpiar modal al cerrarlo
    $(document).on("hidden.bs.modal", "#assignPermissionsModal", function () {
        $(this).find(".modal-body").empty();
    });
});
