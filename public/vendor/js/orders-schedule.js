document.addEventListener("DOMContentLoaded", () => {
    // Cache de elementos usados frecuentemente
    const tableElement = $("#orders_scheduleTable");
    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const loadingMessage = document.getElementById("loading-message");
    //const notesModalElement = document.getElementById("notesModal");
    //const notesModal = bootstrap.Modal.getOrCreateInstance(notesModalElement);
    //  const notesModalElement = $('#notesModal');  // usando jQuery
    const inputCsv = document.getElementById("csv_file");
    const labelCsv = document.getElementById("csv_file_label");

    //------Agregar funcionalidad para cerrar el mensaje---------------

    const closeButtons = document.querySelectorAll(".close");
    closeButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const alertMessage = button.closest(".alert-message");
            alertMessage.style.display = "none";
        });
    });

    // Fetch helper con CSRF
    const postJson = (url, data) =>
        fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify(data),
        }).then(res => {
            if (!res.ok) {
                return res.text().then(text => {
                    throw new Error(`HTTP ${res.status}: ${text}`);
                });
            }
            return res.json();
        });

    // Mostrar mensaje de carga al enviar form
    document.getElementById("upload-form").addEventListener("submit", () => {
        loadingMessage.style.display = "block";
    });

    // Actualiza label del input file
    if (inputCsv && labelCsv) {
        // Mostrar nombre del archivo seleccionado
        inputCsv.addEventListener("change", () => {
            labelCsv.textContent =
                inputCsv.files.length > 0
                    ? inputCsv.files[0].name
                    : "Select file";
        });
    }

    const uploadForm = document.getElementById("upload-form");
    if (uploadForm && loadingMessage) {
        uploadForm.addEventListener("submit", () => {
            loadingMessage.style.display = "block";
        });
    }

    // Función para inicializar DataTable con opciones base + personalizadas
    function initOrdersTable(tableElement, options = {}) {
        const baseOptions = {
            paging: true,
            pageLength: 30,
            lengthChange: false,
            searching: true,
            order: [[11, "asc"]], // asc = más antigua primero
            info: true,
            autoWidth: false,
            columnDefs: [
                { targets: 1, visible: false, searchable: true },
                { targets: 2, visible: false, searchable: true },
            ],
        };
        const finalOptions = { ...baseOptions, ...options };
        const dataTableInstance = tableElement.DataTable(finalOptions);

        // Aplicar el estilo al thead después de inicializar la tabla
        tableElement.find("thead").css({
            "background-color": "#d5d8dc",
            color: "black",
        });

        return dataTableInstance;
    }

    // --- Inicialización condicional según la ruta o variable global ---
    // Ejemplo con window.location.pathname, cámbialo según tu necesidad
    if (tableElement.length) {
        switch (window.location.pathname) {
            case "/scheduley":
                window.table = initOrdersTable(tableElement, {
                    pageLength: 40,
                    searching: false,
                });
                break;
            case "/ruta-vista-2":
                window.table = initOrdersTable(tableElement, {
                    pageLength: 50,
                    searching: false,
                    lengthChange: true,
                });
                break;
            case "/ruta-vista-3":
                window.table = initOrdersTable(tableElement, {
                    pageLength: 10,
                    lengthChange: true,
                    ordering: false,
                });
                break;
            default:
                window.table = initOrdersTable(tableElement);
        }
    }

    // Filtrado con regex exacto
    const applyFilter = (selector, columnIndex) => {
        $(selector).on("change", function () {
            const val = $(this).val()?.toLowerCase() || "";
            window.table
                .column(columnIndex)
                .search(val ? `^${val}$` : "", true, false)
                .draw();
        });
    };
    applyFilter("#locationFilter", 1);
    applyFilter("#statusFilter", 2);
    applyFilter("#customerFilter", 7);

    //-------------------------------------------------------------------------------------

    // Funciones auxiliares comunes
    function createSpanWorkId(orderId, value, saved, editable) {
        const editableClass = editable ? "editable-work-id" : "";
        const finalClass = saved
            ? `${editableClass} text-success fw-bold`
            : `${editableClass} text-decoration-underline text-muted`;
        const label = value || "Click para agregar";

        return $(
            `<span class="${finalClass.trim()}" data-id="${orderId}" style="cursor:pointer;">${label}</span>`
        );
    }

    function createSpanStation(orderId, values, saved = false) {
        const classes = "editable-station text-decoration-underline";
        const finalClass = saved
            ? `${classes} text-success fw-bold`
            : `${classes} text-muted`;
        const label = values.length ? values.join(", ") : "N/A";
        return $(
            `<span class="${finalClass}" data-id="${orderId}" style="cursor:pointer;">${label}</span>`
        );
    }

    function arraysEqual(arr1, arr2) {
        if (arr1.length !== arr2.length) return false;
        return arr1.slice().sort().join(",") === arr2.slice().sort().join(",");
    }

    function handlePostJsonWithAlerts(
        url,
        body,
        onSuccess,
        onErrorMsg = "Error al comunicarse con el servidor."
    ) {
        postJson(url, body)
            .then(response => {
                console.log("Respuesta recibida:", response); // Para debug
                onSuccess(response);
            })
            .catch((error) => {
                console.error("Error en la petición:", error); // Ver error real en consola
                alert(onErrorMsg);
            });
    }

    // ------------------ Eventos ---------------------

    // Editable Work ID
    tableElement.on("click", ".editable-work-id", function () {
        const span = $(this);
        const currentValue = span.data("value") || "";
        const orderId = span.data("id");

        const input = $(
            '<input type="text" class="form-control form-control-sm work-id-input" style="width: 100px;" />'
        ).val(currentValue);

        span.replaceWith(input);
        input.focus();

        input.on("blur", function () {
            const newValue = input.val().trim();
            if (newValue === currentValue) {
                input.replaceWith(
                    createSpanWorkId(orderId, currentValue, false, true)
                );
                return;
            }

            handlePostJsonWithAlerts(
                `/orders/${orderId}/update-work-id`,
                { work_id: newValue },
                (data) => {
                    if (data.success) {
                        localStorage.setItem(
                            "work-id-change",
                            JSON.stringify({
                                orderId,
                                work_id: newValue,
                                updatedAt: Date.now(),
                            })
                        );
                        input.replaceWith(
                            createSpanWorkId(orderId, newValue, true, true)
                        );
                    } else {
                        alert("Error al guardar el Work ID");
                        input.replaceWith(
                            createSpanWorkId(orderId, currentValue, false, true)
                        );
                    }
                }
            );
        });
    });

    // Actualizar Location
    tableElement.on("change", ".location-select", function () {
        const select = $(this);
        const orderId = select.data("id");
        const newLocation = select.val();
        const row = select.closest("tr");
        const stationTd = row.find("td[data-location]");

        stationTd.attr("data-location", newLocation);

        handlePostJsonWithAlerts(
            `/orders/${orderId}/update-location`,
            { location: newLocation },
            (data) => {
                if (data.success) {
                    const locationLower = data.location.toLowerCase();

                    localStorage.setItem(
                        "location-change",
                        JSON.stringify({
                            orderId,
                            location: locationLower,
                            updatedAt: Date.now(),
                        })
                    );

                    const hiddenLocationCell = document.getElementById(
                        `hidden-location-${orderId}`
                    );
                    if (hiddenLocationCell) {
                        hiddenLocationCell.textContent = locationLower;
                        stationTd.attr("data-location", locationLower);
                    }

                    if (window.table) {
                        const rowIndex = window.table.row(row[0]).index();
                        window.table.cell(rowIndex, 1).data(locationLower);
                    }
                } else {
                    alert("Hubo un problema al actualizar la ubicación.");
                }
            },
            "Error al comunicarse con el servidor."
        );
    });

    // Toggle report/source buttons
    tableElement.on(
        "click",
        ".toggle-report-btn, .toggle-source-btn",
        function () {
            const button = $(this);
            const orderId = button.data("id");
            const currentValue = parseInt(button.data("value"));
            const newValue = currentValue === 1 ? 0 : 1;
            const isReport = button.hasClass("toggle-report-btn");
            const url = `/orders/${orderId}/${
                isReport ? "update-report" : "update-source"
            }`;
            const body = isReport
                ? { report: newValue }
                : { our_source: newValue };

            handlePostJsonWithAlerts(
                url,
                body,
                (data) => {
                    if (data.success) {
                        localStorage.setItem(
                            isReport ? "report-toggle" : "source-toggle",
                            JSON.stringify({
                                orderId,
                                value: newValue,
                                updatedAt: Date.now(),
                            })
                        );

                        button.data("value", newValue);
                        button.toggleClass("btn-primary", newValue === 1);
                        button.toggleClass("btn-secondary", newValue === 0);
                        button
                            .find("i")
                            .attr(
                                "class",
                                `fas ${
                                    newValue === 1
                                        ? "fa-check-circle"
                                        : "fa-times-circle"
                                }`
                            );
                    } else {
                        alert("Error al actualizar.");
                    }
                },
                "Error al comunicarse con el servidor."
            );
        }
    );

    // Editable Station
    tableElement.on("click", ".editable-station", function () {
        const span = $(this);
        const orderId = span.data("id");
        const td = span.closest("td");
        const location = td.attr("data-location");

        let options = [];
        if (location === "Yarnell") {
            options = [
                "D2",
                "D3",
                "D5",
                "D6",
                "D7",
                "F2",
                "F6",
                "Gan2",
                "Gan3",
                "HW1",
                "HW2",
                "HW3",
                "Kia1",
                "Mori5",
                "Mori9",
                "Mori10",
                "Mori11",
                "SME1",
                "YCM1",
                "YCM2",
                "YCM4",
                "YCM5",
                "YCM6",
            ];
        } else if (location === "Hearst") {
            options = [
                "B1",
                "C1",
                "C2",
                "F1",
                "F3",
                "F4",
                "F5",
                "F7",
                "F8",
                "Gan1",
                "H1",
                "Kit2",
                "Kit4",
                "Kiw3",
                "Kiw4",
                "Mori4",
                "Mori6",
                "Mori7",
                "YCM3",
            ];
        } else {
            options = ["N/A"];
        }

        const select = $(
            '<select multiple class="form-control form-control-sm station-select" style="width: 100px;"></select>'
        );
        options.forEach((opt) =>
            select.append(`<option value="${opt}">${opt}</option>`)
        );

        const currentValue =
            span.text().trim() === "Click para agregar"
                ? []
                : span
                      .text()
                      .split(",")
                      .map((v) => v.trim());

        select.val(currentValue);
        span.replaceWith(select);

        select
            .select2({
                placeholder: "Select Machines...",
                width: "resolve",
                dropdownParent: select.parent(),
            })
            .select2("open");

        select.on("change", function () {
            const newValues = select.val() || [];

            if (arraysEqual(newValues, currentValue)) {
                select.select2("destroy");
                select.replaceWith(createSpanStation(orderId, currentValue));
                return;
            }

            handlePostJsonWithAlerts(
                `/orders/${orderId}/update-station`,
                { stations: newValues },
                (data) => {
                    if (data.success) {
                        localStorage.setItem(
                            "station-change",
                            JSON.stringify({
                                orderId,
                                stations: newValues,
                                updatedAt: Date.now(),
                            })
                        );
                        select.select2("destroy");
                        select.replaceWith(
                            createSpanStation(orderId, newValues, true)
                        );
                    } else {
                        alert("Error al guardar la estación");
                        select.select2("destroy");
                        select.replaceWith(
                            createSpanStation(orderId, currentValue)
                        );
                    }
                },
                "Error en la conexión"
            );
        });
    });

    // Actualizar Status
    tableElement.on("change", ".status-select", function () {
        const select = $(this);
        const orderId = select.data("id");
        const newStatus = select.val().toLowerCase();

        handlePostJsonWithAlerts(
            `/orders/${orderId}/update-status`,
            { status: newStatus },
            (data) => {
                localStorage.setItem(
                    "status-change",
                    JSON.stringify({
                        orderId,
                        status: data.status,
                        dias_restantes: data.dias_restantes,
                        alertColor: data.alertColor,
                        alertLabel: data.alertLabel,
                    })
                );

                if (data.success) {
                    const row = select.closest("tr");

                    // 🚫 Eliminar fila si el estado es "sent"
                    if (newStatus === "sent") {
                        window.table
                            .row($(select).closest("tr"))
                            .remove()
                            .draw(false);
                        return;
                    }
                    // 👇 Actualizar campo sent_at si existe
                    if (data.sent_at) {
                        const sentCell = document.getElementById(
                            `sent-at-${orderId}`
                        );
                        if (sentCell) sentCell.textContent = data.sent_at;
                    }

                    const hiddenStatusCell = document.getElementById(
                        `hidden-status-${orderId}`
                    );
                    if (hiddenStatusCell)
                        hiddenStatusCell.textContent =
                            data.status.toLowerCase();

                    if (window.table) {
                        const rowIndex = window.table.row(row[0]).index();
                        window.table
                            .cell(rowIndex, 2)
                            .data(data.status.toLowerCase())
                            .draw(false);
                    }

                    const $statusFilter = $("#statusFilter");
                    const newStatusVal = data.status.toLowerCase();

                    if (
                        $statusFilter.find(`option[value="${newStatusVal}"]`)
                            .length === 0
                    ) {
                        const options = $statusFilter.find("option").toArray();
                        const newOption = new Option(
                            newStatusVal,
                            newStatusVal
                        );
                        let inserted = false;
                        for (let i = 1; i < options.length; i++) {
                            if (options[i].value > newStatusVal) {
                                $(options[i]).before(newOption);
                                inserted = true;
                                break;
                            }
                        }
                        if (!inserted) $statusFilter.append(newOption);
                    }

                    $(row).removeClass((i, c) =>
                        (c.match(/bg-status-\S+/g) || []).join(" ")
                    );
                    $(row).addClass(`bg-status-${data.status}`);

                    const diasTd = document.getElementById(
                        `dias-restantes-${orderId}`
                    );
                    if (diasTd) {
                        diasTd.textContent = `${data.dias_restantes} days`;
                        diasTd.className =
                            data.dias_restantes < 0
                                ? "text-danger fw-bold"
                                : data.dias_restantes <= 2
                                ? "text-warning fw-bold"
                                : "text-success fw-bold";
                    }

                    const alertaDiv = document.querySelector(
                        `#alerta-${orderId} .progress-bar`
                    );
                    if (alertaDiv) {
                        alertaDiv.className = "progress-bar " + data.alertColor;
                        alertaDiv.textContent = data.alertLabel;
                    }
                } else {
                    alert("Hubo un problema al actualizar el estado.");
                }
            },
            "Error al comunicarse con el servidor."
        );
    });

    //-------------------------------------------------
    // Tooltips
    const initTooltips = () => {
        document
            .querySelectorAll("[title]")
            .forEach((el) => new bootstrap.Tooltip(el));
    };
    initTooltips();

    // Abrir modal notas

    tableElement.on("click", ".open-notes-modal", function (event) {
        event.preventDefault();
       // console.log("Clic detectado en .open-notes-modal"); // Verifica si esto aparece en consola
        const orderId = $(this).data("id");
        const fullNotes = $(this).data("notes") || "";

        $("#notesOrderId").val(orderId);
        $("#notesTextarea").val(fullNotes);

        // Aquí: usar jQuery y llamar a modal('show')
        $("#notesModal").modal("show");
    });

    // Guardar notas
    $("#notesForm").submit(function (e) {
        console.log("Interceptando submit del formulario de notas"); // 👈 esto
        e.preventDefault();// Esto evita que el form se envíe "normalmente"
        const orderId = $("#notesOrderId").val();
        const notes = $("#notesTextarea").val();

        handlePostJsonWithAlerts(
            `/orders/${orderId}/update-notes`,
            { notes },
            (data) => {
                console.log("Respuesta del servidor:", data);
                if (!data.success) return alert("Error al guardar la nota.");

                localStorage.setItem(
                    "notes-change",
                    JSON.stringify({ orderId, notes: data.notes })
                );

                const shortNote =
                    notes.length > 30 ? notes.substring(0, 30) + "..." : notes;
                const safeNotes = notes.replace(/"/g, "&quot;").trim();

                const newNotesHtml =
                    safeNotes === ""
                        ? `<span class="open-notes-modal" data-id="${orderId}" data-notes="" style="cursor:pointer;" title="">
                <i class="fas fa-plus-circle me-1 text-muted"></i> Note</span>`
                        : `<span class="open-notes-modal" data-id="${orderId}" data-notes="${safeNotes}" style="cursor:pointer;" title="${safeNotes}">
                ${shortNote}</span>`;

                const row = $(`tr[data-order-id="${orderId}"]`)[0];
                if (row && window.table) {
                    const rowIndex = window.table.row(row).index();
                    window.table
                        .cell(rowIndex, 18)
                        .data(newNotesHtml)
                        .draw(false);
                } else {
                    $(`td.notes-cell[data-id="${orderId}"]`).html(newNotesHtml);
                }

                initTooltips();
                $("#notesModal").modal("hide");
            },
            "Error al comunicarse con el servidor."
        );
    });

    //-----------------------------------------
});
