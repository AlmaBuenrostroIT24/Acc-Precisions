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
        }).then((res) => {
            if (!res.ok) {
                return res.text().then((text) => {
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
            pageLength: 15,
            lengthChange: false,
            searching: true,
            order: [[12, "asc"]], // asc = más antigua primero
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
            case "/scheduleh":
                window.table = initOrdersTable(tableElement, {
                    pageLength: 40,
                    searching: false,
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
        const label = value || "Add";

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
            .then((response) => {
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

    // Ultima Location

    let lastLocations = {};

    tableElement.on("focus", ".location-select", function () {
        const select = $(this);
        const orderId = select.data("id");
        lastLocations[orderId] = select.val(); // Guardamos la última antes del cambio
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

                    // Actualizamos localStorage y UI
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
                        // Actualiza la columna 1 (Location)
                        window.table
                            .cell(rowIndex, 1)
                            .data(locationLower)
                            .draw(false);
                    }

                    // MOSTRAR ETIQUETA CON ÚLTIMA UBICACIÓN GUARDADA
                    const label = select
                        .closest("td")
                        .find(".last-location-label");

                    if (data.last_location === "Yarnell") {
                        label
                            .html(
                                `
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-map-marker-alt me-1"></i> Yarnell
                        </span>
                    `
                            )
                            .show();
                    } else {
                        label.hide().html("");
                    }

                    // Actualizamos la variable local para el próximo cambio
                    lastLocations[orderId] = newLocation;
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

    // Actualizar Status con confirmación SweetAlert
    tableElement.on("change", ".status-select", function () {
        const select = $(this);
        const orderId = select.data("id");
        const oldStatus = select.data("old-status") || ""; // Estado previo guardado en data attribute
        const newStatus = select.val().toLowerCase();

        // Ubicación actual del order en la fila
        const row = select.closest("tr");
        const locationSelect = row.find(".location-select");
        const currentLocation = locationSelect.length
            ? locationSelect.val()
            : "";

        // Función que hace la petición para cambiar el status y actualiza UI
        const enviarCambioStatus = () => {
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
                        // Actualizar location si viene en la respuesta
                        if (data.location) {
                            if (locationSelect.length) {
                                locationSelect.val(data.location);
                            }
                        }

                        // Actualizar badge last-location-label
                        const label = row.find(".last-location-label");
                        if (data.last_location === "Yarnell") {
                            label
                                .html(
                                    `
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-map-marker-alt me-1"></i> Yarnell
                                </span>
                            `
                                )
                                .show();
                        } else {
                            label.hide().html("");
                        }

                        // Eliminar fila si estado es "sent"
                        if (newStatus === "sent") {
                            window.table.row(row).remove().draw(false);
                            return;
                        }

                        // Actualizar campo sent_at si existe
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
                            $statusFilter.find(
                                `option[value="${newStatusVal}"]`
                            ).length === 0
                        ) {
                            const options = $statusFilter
                                .find("option")
                                .toArray();
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
                            alertaDiv.className =
                                "progress-bar " + data.alertColor;
                            alertaDiv.textContent = data.alertLabel;
                        }

                        // Actualiza el estado viejo guardado para poder revertir si se edita otra vez
                        select.data("old-status", newStatus);
                    } else {
                        alert("Hubo un problema al actualizar el estado.");
                    }
                },
                "Error al comunicarse con el servidor."
            );
        };

        // Mostrar confirmación si cumple condiciones
        // Mostrar confirmación si cumple condiciones
        if (
            (newStatus === "deburring" || newStatus === "shipping") &&
            currentLocation.toLowerCase() === "yarnell"
        ) {
            Swal.fire({
                title: "¿Are you sure??",
                text: `Change status to '${newStatus}' will move the location to 'Hearst'.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, change",
                cancelButtonText: "No, cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    // 🔁 Notifica a otras pestañas que esta orden ahora estará en Hearst
                    localStorage.setItem(
                        "location-change",
                        JSON.stringify({
                            orderId: orderId,
                            location: "hearst",
                        })
                    );

                    console.log(
                        "📢 Notificación enviada a pestañas: orden movida a Hearst"
                    );

                    enviarCambioStatus();
                } else {
                    // Revertir al estado anterior
                    select.val(oldStatus);
                }
            });
        } else {
            enviarCambioStatus();
        }
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
        e.preventDefault(); // Esto evita que el form se envíe "normalmente"
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
                        .cell(rowIndex, 19)
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

    //-------------------------------------------
    //Es el input para agregar WO QTY
    $(document).on("blur", ".wo-qty-input", function () {
        const input = $(this);
        const original = input.data("original");
        const newVal = input.val();

        if (original == newVal) return;

        const orderId = input.data("id");

        handlePostJsonWithAlerts(
            `/orders/${orderId}/update-wo-qty`,
            { wo_qty: newVal },
            (data) => {
                localStorage.setItem(
                    "wo-qty-change",
                    JSON.stringify({
                        orderId,
                        wo_qty: newVal,
                    })
                );
                localStorage.removeItem("wo-qty-change");
            },
            "❌ Error to save"
        );
    });

    //--------------------------------------------------------
    // --- Aquí agregamos la lógica para el botón "Agregar" dentro de Part_description ---
    // Insertar botón 'Agregar' en las celdas de Part_description que contienen "kit"

    function agregarBotonesKit() {
        tableElement.find("tbody tr").each(function (index) {
            const partDescCell = $(this).find("td").eq(4); // columna PART/DESCRIPTION
            partDescCell.css("position", "relative"); // para posicionar el botón dentro

            const texto = partDescCell.text().toLowerCase();

            //console.log(`🔍 Fila ${index} - Texto: ${texto}`);
            const keywords = [
                "kit",
                "asy",
                "assy",
                "assembly",
                "asemble",
                "asembly",
            ];
            if (keywords.some((word) => texto.toLowerCase().includes(word))) {
                if (partDescCell.find(".btn-add-kit").length === 0) {
                    const btn = $(
                        `<button 
                            class="btn btn-primary btn-add-kit rounded-circle p-0" 
                            type="button" 
                            title="Agregar" 
                            style="
                                width: 1.6em; 
                                height: 1.6em; 
                                font-size: 1em;
                                position: absolute; 
                                right: 0.25em; 
                                top: 70%; 
                                transform: translateY(-50%);
                            ">
                            <i class="fas fa-plus" style="line-height: 1.6em;"></i>
                        </button>`
                    );
                    partDescCell.append(btn);
                    //console.log(`✅ Botón agregado en fila ${index}`);
                }
            }
        });
    }

    // Ejecutar al cargar por primera vez
    agregarBotonesKit();

    // Re-ejecutar cada vez que se redibuja la tabla
    tableElement.on("draw.dt", function () {
        //console.log("📢 Evento draw.dt disparado");
        agregarBotonesKit();
    });

    // Manejar el click en botones 'Agregar' dinámicos
    tableElement.on("click", ".btn-add-kit", function () {
        const btn = $(this);
        const row = btn.closest("tr");

        // Obtener próximo ID antes de hacer cualquier cosa
        fetch("/orders/next-id")
            .then((res) => res.json())
            .then((data) => {
                const nextId = data.next_id;

                // Clonar la fila original (sin eventos)
                const newRow = row.clone(false);

                // Mostrar cuántas columnas tiene la fila
                // console.log("Total celdas en la fila:", newRow.find("td").length);

                // Mostrar el next_id en la primera celda (columna 0)
                const idCell = newRow.find("td:eq(0)");
                idCell.text(nextId);
                idCell.append(
                    `<input type="hidden" name="id" value="${nextId}">`
                );

                // En las columnas 2, 4 y 6 ponemos inputs vacíos
                [2, 4, 6].forEach((index) => {
                    const cell = newRow.find(`td:eq(${index})`);
                    cell.html(
                        `<input type="text" name="col_text_${index}" class="form-control form-control-sm" value="">`
                    );
                });

                // Insertar la nueva fila justo debajo de la actual
                row.after(newRow);

                let guardado = false;

                // Guardar solo al presionar Enter en input col_text_6
                newRow
                    .find('input[name="col_text_6"]')
                    .on("keydown", function (e) {
                        if (e.key === "Enter" && !guardado) {
                            const val6 = $(this).val().trim();
                            if (val6) {
                                if (confirm("¿Save this new record?")) {
                                    guardado = true;
                                    checkInputsAndSend();
                                }
                            } else {
                                //alert("Debes capturar al menos el campo de Cantidad (columna 6).");
                            }
                        }
                    });

                function checkInputsAndSend() {
                    const val6 = newRow
                        .find('input[name="col_text_6"]')
                        .val()
                        .trim();

                    if (!val6) {
                        //  alert( "Debes capturar al menos el campo de Cantidad (columna 6).");
                        return;
                    }

                    let dataToSend = { id: nextId };

                    newRow.find("td").each(function (index) {
                        const cell = $(this);
                        const input = cell.find("input");
                        if (input.length) {
                            dataToSend[`col_text_${index}`] = input.val();
                        } else {
                            let text = cell.text().trim();

                            // Si es la columna 17 y tiene el texto por defecto "Note", se guarda vacío
                            if (index === 17 && text === "Note") {
                                text = "";
                            }

                            dataToSend[`col_text_${index}`] = text;
                        }

                        // También incluir inputs ocultos
                        cell.find('input[type="hidden"]').each(function () {
                            const hiddenInput = $(this);
                            dataToSend[hiddenInput.attr("name")] =
                                hiddenInput.val();
                        });
                    });
                    // console.log("Datos a enviar:", dataToSend); // Aquí justo antes de enviar

                    handlePostJsonWithAlerts(
                        "/orders",
                        dataToSend,
                        (response) => {
                            // alert("Registro guardado con ID: " + response.order_id);

                            // Actualizar visualmente la fila: eliminar inputs excepto columna 7 y dejar texto plano
                            newRow.find("td").each(function (index) {
                                const cell = $(this);
                                if (index === 7) return; // No tocar input en columna 7
                                const input = cell.find("input");
                                if (input.length) {
                                    const value = input.val();
                                    cell.text(value);
                                }
                            });

                            // Recolocar el ID en la columna 0
                            newRow.find("td:eq(0)").text(response.id);
                            // console.log("⏳ Insertando contenido en la columna 18 (Notas)");
                            // 🔽 Generar contenido de la columna 18 (Notas)
                            const orderId = response.id;
                            const safeNotes = ""; // Al guardar nuevo, inicia vacío
                            const shortNote = "Note";

                            const newNotesHtml = `<span class="open-notes-modal" data-id="${orderId}" data-notes="" style="cursor:pointer;" title="">
                                    <i class="fas fa-plus-circle me-1 text-muted"></i> Note</span>`;

                            // Insertar en la columna 18 (si existe)
                            const notesCell = newRow.find("td:eq(17)");
                            if (notesCell.length) {
                                notesCell.html(newNotesHtml);
                            } else {
                                //console.warn("⚠ La columna 18 no existe en esta fila.");
                            }

                            // Actualizar DataTable si está en uso
                            location.reload();
                        },
                        "Error al guardar el registro"
                    );
                }
            })
            .catch((err) => {
                //console.error("Error obteniendo próximo ID:", err);
                //alert("No se pudo obtener el próximo ID");
            });
    });

    //-----------------------------------------
});
