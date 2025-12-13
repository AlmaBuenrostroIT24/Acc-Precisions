document.addEventListener("DOMContentLoaded", () => {
    // Cache de elementos usados frecuentemente
    // Helpers
    const qs = (id) => document.getElementById(id);

    // Cache de elementos (pueden NO existir según el rol)
    const tableElement = $("#orders_scheduleTable"); // jQuery, ok si no existe
    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const loadingMsg = qs("loading-message");
    const uploadForm = qs("upload-form");
    const inputCsv = qs("csv_file");
    const labelCsv = qs("csv_file_label");
    //------Agregar funcionalidad para cerrar el mensaje---------------

    // ------ Cerrar mensajes (si existen) ------
    const closeButtons = document.querySelectorAll(".close");
    if (closeButtons.length) {
        closeButtons.forEach((btn) => {
            btn.addEventListener("click", () => {
                const alertMessage = btn.closest(".alert-message");
                if (alertMessage) alertMessage.style.display = "none";
            });
        });
    }
    // Fetch helper con CSRF
    const postJson = (url, data) =>
        fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json", // 2025-12-12: fuerza respuesta JSON para evitar HTML en errores
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify(data),
        }).then(async (res) => {
            const rawText = await res.text();
            if (!res.ok) {
                const msg = rawText || res.statusText;
                throw new Error(`HTTP ${res.status}: ${msg}`);
            }
            try {
                return JSON.parse(rawText);
            } catch (err) {
                throw new Error(
                    `Respuesta no JSON valida: ${rawText.slice(0, 200)}`
                );
            }
        });

    // ------ Mostrar mensaje de carga al enviar form (si existe) ------
    if (uploadForm && loadingMsg) {
        uploadForm.addEventListener("submit", () => {
            loadingMsg.style.display = "block";
        });
    }

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

    //  Formateador comun para exportar "lo que ves"
    function exportCellFormatter(data, row, column, node) {
        const $node = $(node);

        // 0) Botones toggle (Report / Our Source)
        const $toggle = $node.find(
            "button.toggle-report-btn, button.toggle-source-btn"
        );
        if ($toggle.length) {
            // Detecta tipo (por si quieres etiquetar)
            const label = $toggle.hasClass("toggle-report-btn")
                ? "Report"
                : $toggle.hasClass("toggle-source-btn")
                ? "Source"
                : "";

            // ¿Esta activo?
            // Nota: data('value') puede quedar desactualizado si no lo actualizas al hacer toggle;
            // por eso también verificamos clase e icono.
            const valAttr = String(
                $toggle.attr("data-value") || ""
            ).toLowerCase();
            const isOn =
                $toggle.hasClass("btn-primary") ||
                $toggle.find(".fa-check-circle").length > 0 ||
                valAttr === "1" ||
                valAttr === "true";

            // Elige el formato que prefieras:
            // return isOn ? '1' : '0';                  // binario
            // return isOn ? '✔' : '✘';                 // simbolos (ojo con fuentes PDF)
            // return `${label}: ${isOn ? 'Yes' : 'No'}`; // con etiqueta
            return isOn ? "Yes" : "No";
        }

        // 1) Selects → solo opcion seleccionada
        const $sel = $("select", $node);
        if ($sel.length) {
            return $sel.find("option:selected").text().trim();
        }

        // 2) Inputs (ej. WOQTY)
        const $inp = $("input", $node);
        if ($inp.length) {
            return ($inp.val() || "").toString().trim();
        }

        // 3) Progress/Badges (ALERT)
        const $pb = $(".progress-bar", $node);
        if ($pb.length) {
            return $pb.text().trim();
        }

        // 4) Spans editables
        const $span = $("span", $node);
        if ($span.length && !$sel.length && !$inp.length) {
            return $span.text().trim();
        }

        // 5) Fallback: limpia HTML → texto
        return $("<div>").html(data).text().replace(/\s+/g, " ").trim();
    }

    // Sello de tiempo consistente: YYYY-MM-DD HH:mm
    const pad2 = (n) => String(n).padStart(2, "0");
    const now = new Date();
    const genStamp = `${now.getFullYear()}-${pad2(now.getMonth() + 1)}-${pad2(
        now.getDate()
    )} ${pad2(now.getHours())}:${pad2(now.getMinutes())}`;

    function initOrdersTable(tableElement, options = {}) {
        const baseOptions = {
            paging: true,
            pageLength: 15,
            lengthChange: false,
            searching: true,
            order: [[12, "asc"]], // 2025-12-15: ordenar por due_date (col oculta 12)
            info: true,
            autoWidth: false,
            columnDefs: [
                { targets: 0, visible: false, searchable: false }, // ID
                { targets: 1, visible: false, searchable: true }, // LocationText
                { targets: 2, visible: false, searchable: true }, // StatusText
                { targets: 12, visible: false, searchable: false }, // DueDateText
            ],
            // Habilita zona para botones (B)
            dom: "Bfrtip",

            //  Botones (se ocultaran visualmente y los dispararemos desde el <select>)
            buttons: [
                {
                    extend: "excelHtml5",
                    title: `Orders Schedule — Generated: ${genStamp}`,
                    filename: `orders_schedule_${new Date()
                        .toISOString()
                        .slice(0, 10)}`,
                    exportOptions: {
                        columns: function (idx, data, node) {
                            const dt = $.fn.dataTable.Api
                                ? tableElement.DataTable()
                                : null;
                            const visible = dt
                                ? dt.column(idx).visible()
                                : true;
                            const noExport = $(node).hasClass("no-export");
                            return visible && !noExport;
                        },
                        modifier: { search: "applied", order: "applied" },
                        format: { body: exportCellFormatter }, //  AQUi
                    },
                },
                {
                    extend: "pdfHtml5",
                    title: "Orders Schedule",
                    orientation: "landscape",
                    pageSize: "A4",
                    exportOptions: {
                        columns: (idx, data, node) => {
                            const noExport = $(node).hasClass("no-export");
                            const visible = window.table
                                ? window.table.column(idx).visible()
                                : true;
                            return visible && !noExport;
                        },
                        modifier: { search: "applied", order: "applied" },
                        format: { body: exportCellFormatter }, //  AQUi
                    },
                    customize: function (doc) {
                        // sello de tiempo
                        const pad2 = (n) => String(n).padStart(2, "0");
                        const now = new Date();
                        const genStamp = `${now.getFullYear()}-${pad2(
                            now.getMonth() + 1
                        )}-${pad2(now.getDate())} ${pad2(
                            now.getHours()
                        )}:${pad2(now.getMinutes())}`;

                        // usa el dataURL DIRECTO (sin doc.images)
                        if (
                            window.LOGO_BASE64 &&
                            /^data:image\/(png|jpe?g);base64,/.test(
                                window.LOGO_BASE64
                            )
                        ) {
                            doc.pageMargins = [20, 60, 20, 30]; // margen superior mayor por el header
                            doc.header = {
                                margin: [20, 10, 20, 0],
                                columns: [
                                    { image: window.LOGO_BASE64, width: 60 },
                                    {
                                        text: `Acc Precision Inc.\nGenerated: ${genStamp}`,
                                        alignment: "right",
                                        margin: [0, 10, 0, 0],
                                        fontSize: 9,
                                    },
                                ],
                            };
                        } else {
                            console.warn(
                                "Logo omitido: dataURL invalido o formato no soportado (usa PNG/JPG)."
                            );
                        }
                        // estilos de tabla y footer
                        doc.styles.tableHeader.fontSize = 9;
                        doc.defaultStyle.fontSize = 8;
                        if (doc.content[1] && doc.content[1].layout) {
                            doc.content[1].layout.hLineWidth = () => 0.3;
                            doc.content[1].layout.vLineWidth = () => 0.3;
                        }
                        doc.footer = (currentPage, pageCount) => ({
                            text: currentPage + " / " + pageCount,
                            alignment: "right",
                            margin: [0, 0, 20, 0],
                            fontSize: 8,
                        });
                    },
                },
                {
                    extend: "print",
                    title: "", // dejamos vacio y armamos el header con messageTop
                    messageTop: function () {
                        // usa el base64 si esta disponible; si no, usa la ruta pública
                        const src =
                            window.LOGO_BASE64 &&
                            /^data:image\/(png|jpe?g);base64,/.test(
                                window.LOGO_BASE64
                            )
                                ? window.LOGO_BASE64
                                : "/img/logo.png";

                        // sello de tiempo
                        const pad2 = (n) => String(n).padStart(2, "0");
                        const d = new Date();
                        const genStamp = `${d.getFullYear()}-${pad2(
                            d.getMonth() + 1
                        )}-${pad2(d.getDate())} ${pad2(d.getHours())}:${pad2(
                            d.getMinutes()
                        )}`;

                        // header HTML (logo izquierda, titulo/fecha derecha)
                        return `
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
        <img src="${src}" style="height:46px;"/>
        <div style="text-align:right; font-size:12px; line-height:1.2;">
          <div style="font-weight:600; font-size:14px;">Acc Precision Inc.</div>
          <div>Generado: ${genStamp}</div>
        </div>
      </div>
    `;
                    },
                    exportOptions: {
                        columns: function (idx, data, node) {
                            const dt = $.fn.dataTable.Api
                                ? tableElement.DataTable()
                                : null;
                            const visible = dt
                                ? dt.column(idx).visible()
                                : true;
                            const noExport = $(node).hasClass("no-export");
                            return visible && !noExport;
                        },
                        modifier: { search: "applied", order: "applied" },
                        format: { body: exportCellFormatter }, //  igual que en PDF/Excel
                    },
                    customize: function (win) {
                        // CSS para imprimir en landscape y compactar tabla
                        const css = `
      @page { size: landscape; margin: 12mm; }
      body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; }
      table.dataTable { width: 100% !important; }
      table.dataTable th, table.dataTable td { padding: 4px 6px !important; vertical-align: middle; }
      table.dataTable thead th { background: #f1f1f1 !important; font-weight: 600; }
    `;
                        const head =
                            win.document.head ||
                            win.document.getElementsByTagName("head")[0];
                        const style = win.document.createElement("style");
                        style.type = "text/css";
                        style.appendChild(win.document.createTextNode(css));
                        head.appendChild(style);
                    },
                },
            ],
            initComplete: function () {
                const wrapper = document.getElementById("table-wrapper");
                const loader = document.getElementById("loader");

                if (loader) loader.style.display = "none";
                if (wrapper) {
                    wrapper.style.display = "block";
                    //console.log(" Forzando mostrar wrapper");
                }

                //  Ajusta columnas si el contenedor estaba oculto
                this.api().columns.adjust().draw();

                //console.log(" DataTable completamente inicializada");
            },
        };

        const finalOptions = { ...baseOptions, ...options };
        const dataTableInstance = tableElement.DataTable(finalOptions);

        tableElement.find("thead").css({
            "background-color": "#d5d8dc",
            color: "black",
        });

        return dataTableInstance;
    }

    //  Inicializacion condicional por ruta
    if (tableElement.length) {
        const path = window.location.pathname;

        const tableConfigs = {
            "/scheduley": { pageLength: 40, searching: false, order: [] },
            "/scheduleh": { pageLength: 40, searching: false, order: [] },
            "/schedule/workhearst": {
                pageLength: 10,
                lengthChange: true,
                ordering: false,
            },
        };

        const config = tableConfigs[path] || {};
        if (typeof applyRowLateHighlight === "function") {
            config.rowCallback = applyRowLateHighlight;
        }

        //  Mostrar loader antes de inicializar
        const loader = document.getElementById("loader");
        const wrapper = document.getElementById("table-wrapper");
        if (loader) loader.style.display = "block";
        if (wrapper) wrapper.style.display = "none";

        //  Inicializar tabla
        window.table = initOrdersTable(tableElement, config);

        // Oculta los botones nativos (opcional)
        setTimeout(() => $(".dt-buttons").hide(), 0); // por si el DOM se pinta despues

        // Helper para disparar el boton correcto
        function triggerExport(action) {
            if (!window.table || typeof window.table.button !== "function") {
                console.error("DataTable no inicializada o falta Buttons.");
                return;
            }
            // Por clase… y si no, por indice (0=excel, 1=pdf, 2=print)
            if (action === "excel") {
                window.table.button(".buttons-excel").length
                    ? window.table.button(".buttons-excel").trigger()
                    : window.table.button(0).trigger();
            } else if (action === "pdf") {
                window.table.button(".buttons-pdf").length
                    ? window.table.button(".buttons-pdf").trigger()
                    : window.table.button(1).trigger();
            } else if (action === "print") {
                window.table.button(".buttons-print").length
                    ? window.table.button(".buttons-print").trigger()
                    : window.table.button(2).trigger();
            }
        }

        // 1) SELECT simple
        const exportSel = document.getElementById("exportFilter");
        if (exportSel) {
            exportSel.addEventListener("change", function () {
                if (this.value) triggerExport(this.value);
                this.value = ""; // reset
            });
        }

        // 2) DROPDOWN estilo select
        $(document).on("click", ".export-action", function () {
            triggerExport(this.dataset.action);
        });
    } else {
        console.error(" No se encontro #orders_scheduleTable");
    }

    
    function applyRowLateHighlight(row, data, index) {
        const $row = $(row);
        const priority = String($row.data("priority") || "").toLowerCase();
        const status = (data[2] || "").toLowerCase();
        const locationVal = String(
            (data[1] || $row.find("[id^='hidden-location-']").text() || "")
                .trim()
        ).toLowerCase();

        const diasTd = $row.find("td[id^='dias-restantes-']");
        const diasMatch = (diasTd.text().trim().match(/-?\d+/) || [])[0];
        const dias = diasMatch != null ? parseInt(diasMatch, 10) : null;

        const isStandbyOnhold =
            locationVal === "standby" && status === "onhold";

        // limpiar
        $row.removeClass("row-late row-priority");
        $row.removeClass((i, c) => (c.match(/bg-status-\S+/g) || []).join(" "));

        if (isStandbyOnhold) {
            // Limpiar días/alerta en esta vista
            if (diasTd.length) {
                diasTd.text("");
                diasTd.removeClass();
            }
            const alertaDiv = $row.find("div[id^='alerta-'] .progress-bar");
            if (alertaDiv.length) {
                alertaDiv
                    .removeClass()
                    .addClass("progress-bar d-none")
                    .text("");
            }
            if (status) $row.addClass(`bg-status-${status}`);
            if (priority === "yes") $row.addClass("row-priority");
            return;
        }

        if (dias !== null && dias < 0) {
            $row.addClass("row-late");
            if (priority === "yes") $row.addClass("row-priority");
            return;
        }

        if (priority === "yes") {
            $row.addClass("row-priority");
            return;
        }

        if (status) $row.addClass(`bg-status-${status}`);
    }

    // Ocultar dias y alerta (Standby + Onhold)
    function hideDiasYAlerta(orderId) {
        const diasTd = document.getElementById(`dias-restantes-${orderId}`);
        if (diasTd) {
            diasTd.textContent = "";
            diasTd.className = "";
        }
        const alertaDiv = document.querySelector(
            `#alerta-${orderId} .progress-bar`
        );
        if (alertaDiv) {
            alertaDiv.className = "progress-bar d-none";
            alertaDiv.textContent = "";
        }
    }

    // 2025-12-15: Calcular fecha restando días hábiles (omite sábado/domingo)
    function subtractBusinessDays(dateStr, daysBack = 5) {
        const d = new Date(`${dateStr}T12:00:00`);
        let count = 0;
        while (count < daysBack) {
            d.setDate(d.getDate() - 1);
            const dow = d.getDay();
            if (dow !== 0 && dow !== 6) count++;
        }
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, "0");
        const day = String(d.getDate()).padStart(2, "0");
        return `${y}-${m}-${day}`;
    }

    // 2025-12-15: Ajustar machining_date automáticamente 5 días hábiles antes del due_date
    function autoUpdateMachiningDate(orderId, dueDate, statusForStyle) {
        const newMach = subtractBusinessDays(dueDate, 5);
        handlePostJsonWithAlerts(
            `/orders/${orderId}/update-date-machining`,
            { machining_date: newMach },
            (mData) => {
                if (!mData.success) return;

                // Refrescar span editable de machining_date
                const machSpan = $(
                    `.editable-machining-date[data-id="${orderId}"]`
                );
                const newSpan = createEditableDateSpan(orderId, newMach, {
                    cssClass: "editable-machining-date",
                    underline: true,
                    bold: true,
                });
                if (machSpan.length) machSpan.replaceWith(newSpan);

                // Actualizar d/alerta si corresponde (respetando Standby+Onhold)
                const rowEl = document.querySelector(
                    `tr[data-order-id="${orderId}"]`
                );
                const locVal = rowEl
                    ? String(
                          $(rowEl).find(".location-select").val() || ""
                      ).toLowerCase()
                    : "";
                const isStandbyOnhold =
                    (mData.status || statusForStyle) === "onhold" &&
                    locVal === "standby";

                if (isStandbyOnhold) {
                    hideDiasYAlerta(orderId);
                } else {
                    const diasVal =
                        typeof mData.dias_restantes === "number"
                            ? mData.dias_restantes
                            : null;
                    const diasTd = document.getElementById(
                        `dias-restantes-${orderId}`
                    );
                    if (diasTd && diasVal !== null) {
                        diasTd.textContent = `${diasVal} days`;
                        diasTd.className =
                            diasVal < 0
                                ? "text-danger fw-bold"
                                : diasVal <= 2
                                ? "text-warning fw-bold"
                                : "text-success fw-bold";
                    }
                    const alertaDiv = document.querySelector(
                        `#alerta-${orderId} .progress-bar`
                    );
                    if (alertaDiv && mData.alertColor) {
                        alertaDiv.className =
                            "progress-bar " + mData.alertColor;
                        alertaDiv.textContent =
                            mData.alertLabel || alertaDiv.textContent;
                    }
                }

                applyRowLateStyle(
                    orderId,
                    mData.dias_restantes || 0,
                    mData.status || statusForStyle || ""
                );
            },
            "Error al ajustar machining_date desde due_date."
        );
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
                //console.log("Respuesta recibida:", response); // Para debug
                onSuccess(response);
            })
            .catch((error) => {
                console.error("Error en la peticion:", error); // Ver error real en consola
                alert(`${onErrorMsg}
${error && error.message ? error.message : error}`);
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

    //==============================
    //
    // Ultima Location

    let lastLocations = {};

    tableElement.on("focus", ".location-select", function () {
        const select = $(this);
        const orderId = select.data("id");
        lastLocations[orderId] = select.val(); // Guardamos la última antes del cambio
    });

    // Actualizar Location
    tableElement.on("change", ".location-select", function () {
        const scrollTopBefore = $(window).scrollTop(); // 🧠 guarda scroll para que no de el error que al actualizar se vaya hacia arriba

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

                    // MOSTRAR ETIQUETA CON ÚLTIMA UBICACIoN GUARDADA
                    const label = select
                        .closest("td")
                        .find(".last-location-label");

                    if (
                        String(newLocation || "").toLowerCase() ===
                            "standby" &&
                        data.last_location
                    ) {
                        label
                            .html(
                                `
                        <span class="badge bg-secondary text-light">
                            <i class="fas fa-hourglass-half me-1"></i> ${data.last_location}
                        </span>
                    `
                            )
                            .show();
                    } else if (data.last_location === "Yarnell") {
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

                    // Actualizamos la variable local para el proximo cambio
                    lastLocations[orderId] = newLocation;
                    $(window).scrollTop(scrollTopBefore); //  restaura scroll
                    select.blur(); // quita foco
                } else {
                    alert("Hubo un problema al actualizar la ubicacion.");
                }
            },
            "Error al comunicarse con el servidor."
        );
    });

    
    function applyRowLateStyle(orderId, dias, status) {
        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (!row) return;

        const locCell = row.querySelector(`#hidden-location-${orderId}`);
        const locationVal = locCell
            ? String(locCell.textContent || "").trim().toLowerCase()
            : "";
        const statusVal = String(status || "").toLowerCase();
        const isStandbyOnhold =
            locationVal === "standby" && statusVal === "onhold";

        row.className = row.className
            .split(" ")
            .filter(
                (c) =>
                    !c.startsWith("bg-status-") &&
                    c !== "row-late" &&
                    c !== "row-priority"
            )
            .join(" ");

        if (isStandbyOnhold) {
            row.classList.add(`bg-status-${statusVal || "onhold"}`);
            if (String(row.dataset.priority || "").toLowerCase() === "yes") {
                row.classList.add("row-priority");
            }
            return;
        }

        if (dias < 0) {
            row.classList.add("row-late");
        } else if (statusVal) {
            row.classList.add(`bg-status-${statusVal}`);
        }
    }

    //-------------------------------------------------START---------------------------------------------------------------
    ///--------------------------------MANEJO DE BOTONES "report & our_source" --------------------------------------------
    //FUNCIONES AUXILIARES
    //  1. Centralizar alertas te ayuda a cambiar el comportamiento facilmente mas adelante
    function showSourceAlert(currentValue) {
        if (currentValue === 1) {
            alert(
                "¡Atencion! Estas desactivando 'our_source' para esta orden."
            );
        } else {
            alert("Estas activando 'our_source' para esta orden.");
        }
    }
    // 2. Evita duplicar logica de clases e iconos con una funcion
    function updateToggleButton(button, newValue) {
        button.data("value", newValue);
        button.toggleClass("btn-primary", newValue === 1);
        button.toggleClass("btn-secondary", newValue === 0);
        button
            .find("i")
            .attr(
                "class",
                `fas ${newValue === 1 ? "fa-check-circle" : "fa-times-circle"}`
            );
    }
    // 3. Extrae funcion para activar edicion de fecha
    function triggerEditableMachiningDate(orderId) {
        const dateSpan = $(`.editable-machining-date[data-id="${orderId}"]`);
        if (dateSpan.length > 0) {
            dateSpan.attr("data-enabled", "1");
            setTimeout(() => dateSpan.trigger("click"), 100);
        } else {
            console.warn(
                "No se encontro el span editable para la orden:",
                orderId
            );
        }
    }
    // 4. Evita crear HTML con strings largos: usa plantillas
    function createEditableDateSpan(orderId, date, options = {}) {
        const {
            cssClass = "editable-date", // clase personalizada
            enabled = true,
            underline = true,
            bold = true,
        } = options;

        // 2025-12-15: Formatear fecha como "Jul-11-2025" (ano completo)
        const [year, month, day] = date.split("-");
        const dateObj = new Date(`${year}-${month}-${day}T12:00:00`);
        const shortMonth = dateObj.toLocaleString("en-US", { month: "short" });
        const formatted = `${shortMonth}-${day.padStart(
            2,
            "0"
        )}-${year}`;

        // Armar clases
        const classes = [
            cssClass,
            underline ? "text-decoration-underline" : "",
            bold ? "fw-bold" : "",
        ]
            .filter(Boolean)
            .join(" ");

        return $(`
        <span class="${classes}"
              data-id="${orderId}"
              data-enabled="${enabled ? 1 : 0}"
              data-value="${date}"
              style="cursor:${enabled ? "pointer" : "default"};">
            ${formatted}
        </span>
    `);
    }

    //  Main: toggle de botones y edicion de fecha

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

            //Mensaje de alertas cuando es 0 o 1
            // if (!isReport) showSourceAlert(currentValue);

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

                        updateToggleButton(button, newValue);

                        if (!isReport && newValue === 1) {
                            triggerEditableMachiningDate(orderId);
                        } else if (!isReport && newValue === 0) {
                            const dateSpan = $(
                                `.editable-machining-date[data-id="${orderId}"]`
                            );
                            if (dateSpan.length > 0) {
                                dateSpan.attr("data-enabled", "0");
                                dateSpan.css("cursor", "default");
                            }
                        }
                    } else {
                        alert("Error al actualizar.");
                    }
                },
                "Error al comunicarse con el servidor."
            );
        }
    );

    // Edicion de fecha de maquinado

    tableElement.on("click", ".editable-machining-date", function () {
        const span = $(this);
        const orderId = span.data("id");
        const isEnabled = parseInt(span.data("enabled")) === 1;
        const currentValue = span.data("value") || "";

        if (!isEnabled) return;

        const input = $(
            `<input type="date" class="form-control form-control-sm machining-date-input">`
        ).val(currentValue);

        span.replaceWith(input);
        input.focus();

        input.on("blur", function () {
            const newDate = input.val();
            if (!newDate || newDate === currentValue) {
                input.replaceWith(span);
                return;
            }
            handlePostJsonWithAlerts(
                `/orders/${orderId}/update-date-machining`,
                { machining_date: newDate },
                (data) => {
                    if (data.success) {
                        localStorage.setItem(
                            "date-machining-change",
                            JSON.stringify({
                                orderId,
                                machining_date: newDate,
                                dias_restantes: data.dias_restantes,
                                alertColor: data.alertColor,
                                alertLabel: data.alertLabel,
                                status: data.status, // ✅ Incluye el estado actual
                                updatedAt: Date.now(),
                            })
                        );

                        const newSpan = createEditableDateSpan(
                            orderId,
                            newDate,
                            {
                                cssClass: "editable-machining-date",
                                underline: true,
                                bold: true,
                            }
                        );

                        input.replaceWith(newSpan);
                        //  Actualizar visualmente dias/alerta u ocultar si Standby+Onhold
                        const rowEl = document.querySelector(
                            `tr[data-order-id="${orderId}"]`
                        );
                        const locVal = rowEl
                            ? String(
                                  $(rowEl).find(".location-select").val() || ""
                              ).toLowerCase()
                            : "";
                        const isStandbyOnhold =
                            data.status === "onhold" && locVal === "standby";

                        if (isStandbyOnhold) {
                            hideDiasYAlerta(orderId);
                        } else {
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
                        }

                        // ✅ Aplicar estilo visual correcto a la fila , para cuando se cambie la fecha, me detecte el color segun el estatus
                        applyRowLateStyle(
                            orderId,
                            data.dias_restantes,
                            data.status || ""
                        );
                    } else {
                        alert("Error al guardar la fecha.");
                        input.replaceWith(span);
                    }
                }
            );
        });
    });

    ///--------------------------------MANEJO DE BOTONES "report & our_source" --------------------------------------------
    //----------------------------------------------------END--------------------------------------------------------------

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
                        alert("Error al guardar la estacion");
                        select.select2("destroy");
                        select.replaceWith(
                            createSpanStation(orderId, currentValue)
                        );
                    }
                },
                "Error en la conexion"
            );
        });
    });

    //-------------------------------------------------START-------------------------------------------------------------//
    //--------------------------------MANEJO DE DEL SELECT Y FUNCIONALIDADES --------------------------------------------//

    // Captura dinamica del estado anterior. Se agrega este bloque antes del "change" para asegurar que data-old-status siempre tenga el valor actual:
    tableElement.on("focus", ".status-select", function () {
        const currentVal = ($(this).val() || "").toLowerCase(); // .toLowerCase() Normalizar el oldStatus a minúsculas
        $(this).data("old-status", currentVal);
    });


    // Actualizar Status con confirmacion SweetAlert
    tableElement.on("change", ".status-select", function () {
        const scrollTopBefore = $(window).scrollTop();
        const select = $(this);
        const orderId = select.data("id");

        //  Leer valor anterior y new status de forma segura y normalizada
        const oldStatus = (select.data("old-status") || "").toLowerCase();
        const newStatus = (select.val() || "").toLowerCase();

        const row = select.closest("tr");
        const locationSelect = row.find(".location-select");
        const currentLocation = locationSelect.length
            ? locationSelect.val()
            : "";

        const enviarCambioStatus = () => {
            //  lee la confirmacion de inspeccion si existe
            const insp = JSON.parse(
                localStorage.getItem("inspection-change") || "null"
            );
            const note = JSON.parse(
                localStorage.getItem("inspection-note-change") || "null"
            );

            // payload minimo
            const payload = { status: newStatus };

            // si se confirmo la inspeccion, anadimos el campo
            if (insp && insp.orderId === orderId) {
                payload.status_inspection = insp.status_inspection; // "completed"
            }
            if (note && note.orderId === orderId)
                payload.inspection_note = note.inspection_note;

            handlePostJsonWithAlerts(
                `/orders/${orderId}/update-status`,
                payload,
                (data) => {
                    if (data.success) {
                        // limpiar banderas ya aplicadas
                        localStorage.removeItem("inspection-change");
                        localStorage.removeItem("inspection-note-change");

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

                        if (data.location && locationSelect.length) {
                            locationSelect.val(data.location);
                        }

                        const label = row.find(".last-location-label");
                        const currentLoc = String(
                            (data.location || locationSelect.val() || "")
                        ).toLowerCase();
                        if (
                            currentLoc === "standby" &&
                            data.last_location &&
                            data.last_location !== ""
                        ) {
                            label
                                .html(
                                    `
                                <span class="badge bg-secondary text-light">
                                    <i class="fas fa-hourglass-half me-1"></i> ${data.last_location}
                                </span>
                            `
                                )
                                .show();
                        } else if (data.last_location === "Yarnell") {
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

                        //✅ Eliminar fila si estado es "sent"
                        if (newStatus === "sent") {
                            window.table.row(row).remove().draw(false);
                            return;
                        }

                        //✅ Actualizar sent_at
                        if (data.sent_at) {
                            const sentCell = document.getElementById(
                                `sent-at-${orderId}`
                            );
                            if (sentCell) sentCell.textContent = data.sent_at;
                        }

                        //✅ Actualizar estado oculto
                        const hiddenStatusCell = document.getElementById(
                            `hidden-status-${orderId}`
                        );
                        if (hiddenStatusCell)
                            hiddenStatusCell.textContent =
                                data.status.toLowerCase();

                        //✅ Actualizar en DataTable (columna 2)
                        if (window.table) {
                            const rowIndex = window.table.row(row[0]).index();
                            window.table
                                .cell(rowIndex, 2)
                                .data(data.status.toLowerCase())
                                .draw(false);
                            $(window).scrollTop(scrollTopBefore); //  restaura scroll
                            select.blur(); // quita foco
                        }

                        // 2025-12-15: Si pasa a onhold, forzar ubicacion Standby (con persistencia)
                        if (newStatus === "onhold" && locationSelect.length) {
                            locationSelect.val("Standby").trigger("change");
                            const hiddenLoc = document.getElementById(
                                `hidden-location-${orderId}`
                            );
                            if (hiddenLoc) hiddenLoc.textContent = "standby";
                        }

                        // 2025-12-15: Si sale de onhold y esta en Standby, regresar a last_location (o ubicacion actual si viene del backend)
                        if (
                            oldStatus === "onhold" &&
                            newStatus !== "onhold" &&
                            locationSelect.length &&
                            String(locationSelect.val() || "").toLowerCase() ===
                                "standby"
                        ) {
                            const targetLoc =
                                (data.last_location || "").trim() ||
                                "Floor";
                            locationSelect.val(targetLoc).trigger("change");
                            const hiddenLoc = document.getElementById(
                                `hidden-location-${orderId}`
                            );
                            if (hiddenLoc)
                                hiddenLoc.textContent = targetLoc.toLowerCase();
                        }

                        // Agregar nuevo valor al filtro si no existe
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

                        // Actualizar d\xedas/alerta u ocultar si Standby+Onhold
                        const locVal = locationSelect.length
                            ? String(locationSelect.val() || "").toLowerCase()
                            : String(data.location || "").toLowerCase();
                        const isStandbyOnhold =
                            newStatus === "onhold" && locVal === "standby";

                        if (isStandbyOnhold) {
                            hideDiasYAlerta(orderId);
                        } else {
                            const diasTd = document.getElementById(
                                `dias-restantes-${orderId}`
                            );
                            if (diasTd) {
                                const dias = data.dias_restantes;
                                diasTd.textContent = `${dias} days`;
                                diasTd.className =
                                    dias < 0
                                        ? "text-danger fw-bold"
                                        : dias <= 2
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
                        }

                        //  Actualiza el valor de referencia para futuros cambios
                        select.data("old-status", newStatus);
                    } else {
                        alert("Hubo un problema al actualizar el estado.");
                    }
                },
                "Error al comunicarse con el servidor."
            );
        };

        //  Confirmacion si nuevo estado es 'sent'
        if (newStatus === "sent") {
            // 1) Intentar leer WO_QTY de input y, si no, del td
            const $inp = row.find(".wo-qty-input");

            // 2) Normalizar a numero (tolerando comas, espacios, etc.)
            const toNumber = (v) => {
                if (v === undefined || v === null) return null;
                const n = Number(String(v).replace(/[^\d.-]/g, ""));
                return Number.isFinite(n) ? n : null;
            };

            // Prioridad: input -> td
            const woQtyNum = toNumber($inp.length ? $inp.val() : null);

            // 3) Validar
            if (!Number.isFinite(woQtyNum) || woQtyNum < 0) {
                Swal.fire({
                    icon: "warning",
                    title: "WO_QTY required",
                    text: "Before selecting as 'sent', you must capture a valid value in WO_QTY.",
                    confirmButtonText: "OK",
                }).then(() => {
                    select.val(oldStatus).trigger("change");
                    if ($inp.length) $inp.focus().select();
                });
                return; //  no continuar
            }

            // 4) Confirmar envio
            Swal.fire({
                title: "¿Are you sure?",
                text: `Changing the status to '${newStatus}'. It will be moved to 'Completed Orders'.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Completed",
                cancelButtonText: "No, cancel",
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    enviarCambioStatus();
                } else {
                    select.val(oldStatus).trigger("change");
                }
            });
            return;
        }

        //  Activar edicion del due_date si es "onhold"
        const dueSpan = row.find(`.editable-due-date[data-id="${orderId}"]`);
        if (newStatus === "onhold") {
            //  Activar edicion
            if (dueSpan.length) {
                dueSpan.attr("data-enabled", "1");
                dueSpan.css("cursor", "pointer");
                dueSpan.addClass("fw-bold");
                triggerEditableDueDate(orderId); //  abre de inmediato
            }
        } else {
            //  Desactivar edicion
            if (dueSpan.length) {
                dueSpan.attr("data-enabled", "0");
                dueSpan.css("cursor", "default");
                dueSpan.removeClass("fw-bold");
            }
        }

        // ================================
        // 1) Overrides locales y helpers
        // ================================
        // ===== Overrides locales =====
        const INSPECTION_OVERRIDES = {}; // { "123": { status_inspection: "completed", inspection_progress: 100 } }

        function setInspectionOverrideCompleted(orderId, pct = 100) {
            INSPECTION_OVERRIDES[String(orderId)] = {
                status_inspection: "completed",
                inspection_progress: pct,
            };
        }

        function applyInspectionOverride(orderId, meta) {
            const o = INSPECTION_OVERRIDES[String(orderId)];
            if (!o) return meta;
            if (o.status_inspection) {
                meta.status_inspection = String(
                    o.status_inspection
                ).toLowerCase();
            }
            if (typeof o.inspection_progress !== "undefined") {
                meta.inspection_progress = Number(o.inspection_progress);
            }
            return meta;
        }

        // ===== Cache + fetch con override =====
        const OPS_META_CACHE = {}; // si ya existe global, NO lo vuelvas a declarar

        function fetchOpsMeta(orderId) {
            const key = String(orderId);

            if (OPS_META_CACHE[key]) {
                const metaCopy = { ...OPS_META_CACHE[key] };
                applyInspectionOverride(key, metaCopy);
                return $.Deferred().resolve(metaCopy).promise();
            }

            return $.getJSON(`/orders/${orderId}/ops-meta`).then(function (r) {
                const meta = {
                    operation: Number((r && r.operation) || 0),
                    parent_id:
                        r && typeof r.parent_id !== "undefined"
                            ? r.parent_id
                            : null,
                    inspection_progress: Number(
                        (r && r.inspection_progress) || 0
                    ),
                    status_inspection: String(
                        (r && r.status_inspection) || ""
                    ).toLowerCase(),
                };
                OPS_META_CACHE[key] = meta;
                applyInspectionOverride(key, OPS_META_CACHE[key]); // aplica override al cache
                return { ...OPS_META_CACHE[key] }; // devuelve copia con override
            });
        }

        // ===== Wrapper por si enviarCambioStatus no retorna promesa =====
        function safeEnviarCambioStatus() {
            const r = enviarCambioStatus(); // tu funcion existente
            if (r && typeof r.then === "function") return r;
            return $.Deferred().resolve({}).promise();
        }

        // =======================
        // BLOQUE 1: YARNELL ➜ mover a Hearst (deburring/shipping)
        // =======================
        if (
            (newStatus === "deburring" || newStatus === "shipping") &&
            String(currentLocation).toLowerCase() === "yarnell"
        ) {
            fetchOpsMeta(orderId)
                .then(function ({ operation, parent_id, inspection_progress }) {
                    const progress = Number(inspection_progress || 0);

                    //  ÚNICA confirmacion si < 100%
                    if (progress < 100) {
                        const html = `
          <div class="mb-2">Current inspection progress: <b>${progress}%</b></div>
          <small class="text-muted d-block mt-2">
            If you continue, <b>Inspection</b> will be marked as <b>COMPLETED</b>.
          </small>`;
                        return Swal.fire({
                            title: "Inspection not at 100%",
                            html,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Yes, continue",
                            cancelButtonText: "Review first",
                            reverseButtons: true,
                            focusCancel: true,
                        }).then((resp) => {
                            if (!resp.isConfirmed) {
                                select.val(oldStatus).trigger("change.select2");
                                return $.Deferred()
                                    .reject("cancelled")
                                    .promise();
                            }
                            //  Override inmediato para no volver a preguntar en la siguiente vez
                            setInspectionOverrideCompleted(orderId);
                        });
                    }
                })
                .then(function () {
                    // Bandera para backend: completar inspeccion
                    localStorage.setItem(
                        "inspection-change",
                        JSON.stringify({
                            orderId,
                            status_inspection: "completed",
                        })
                    );

                    // Refuerza override y cache local
                    setInspectionOverrideCompleted(orderId);
                    if (OPS_META_CACHE[String(orderId)]) {
                        OPS_META_CACHE[String(orderId)].status_inspection =
                            "completed";
                    }

                    const progress = Number(
                        (OPS_META_CACHE[orderId] || {}).inspection_progress || 0
                    );

                    //  Nota SOLO si 0%
                    if (progress === 0) {
                        return Swal.fire({
                            title: "Inspection note",
                            input: "textarea",
                            inputLabel:
                                "Explain why Inspection has 0% progress before completing.",
                            inputPlaceholder: "Reason / context…",
                            inputAttributes: {
                                "aria-label": "Inspection note",
                            },
                            showCancelButton: false,
                            confirmButtonText: "Save note",
                            reverseButtons: true,
                            inputValidator: (value) => {
                                if (!value || value.trim().length < 20) {
                                    return "Write a short note (min 20 characters).";
                                }
                            },
                        }).then((noteResult) => {
                            if (!noteResult.isConfirmed) {
                                select.val(oldStatus).trigger("change.select2");
                                return $.Deferred()
                                    .reject("note-not-provided")
                                    .promise();
                            }
                            localStorage.setItem(
                                "inspection-note-change",
                                JSON.stringify({
                                    orderId,
                                    inspection_note: noteResult.value.trim(),
                                })
                            );
                            confirmarCambioHearst();
                        });
                    }

                    confirmarCambioHearst();

                    function confirmarCambioHearst() {
                        const html = `
          <p class="text-muted d-block mt-2" style="font-size: 1.05rem;">
            Changing status to <b>${newStatus}</b> will also move the location to <b>HEARST</b>.
          </p>`;
                        Swal.fire({
                            title: "¿Are you sure?",
                            html,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Yes, Change",
                            cancelButtonText: "No, Cancel",
                            reverseButtons: true,
                        }).then((result) => {
                            if (!result.isConfirmed) {
                                select.val(oldStatus).trigger("change.select2");
                                return;
                            }

                            // mover ubicacion
                            localStorage.setItem(
                                "location-change",
                                JSON.stringify({ orderId, location: "hearst" })
                            );

                            // POST
                            safeEnviarCambioStatus()
                                .then(() => {
                                    // Reafirma override y deja el old-status en el nuevo
                                    setInspectionOverrideCompleted(orderId);
                                    select.data("old-status", newStatus);
                                })
                                .catch(() => {
                                    select
                                        .val(oldStatus)
                                        .trigger("change.select2");
                                });
                        });
                    }
                })
                .fail(function (reason) {
                    if (
                        reason !== "cancelled" &&
                        reason !== "note-not-provided"
                    ) {
                        select.val(oldStatus).trigger("change.select2");
                        Swal.fire({
                            title: "Error",
                            text: "Couldn't fetch operation/progress.",
                            icon: "error",
                        });
                    }
                });

            //  No sigas el flujo normal
            return;
        }

        // =======================
        // BLOQUE 2: HEARST (deburring/shipping/ready) sin mover ubicacion
        // Sin confirmar el cambio de estatus (directo a enviarCambioStatus)
        // =======================

        // =========================================
        // 3) BLOQUE HEARST (deburring/shipping/ready)
        // =========================================
        if (
            ["deburring", "shipping", "ready"].includes(newStatus) &&
            String(currentLocation).toLowerCase() === "hearst"
        ) {
            fetchOpsMeta(orderId)
                .then(function ({
                    operation,
                    parent_id,
                    status_inspection,
                    inspection_progress,
                }) {
                    const insp = String(status_inspection || "").toLowerCase();
                    const progress = Number(inspection_progress || 0);

                    if (insp === "completed") {
                        return safeEnviarCambioStatus()
                            .then(() => {
                                setInspectionOverrideCompleted(orderId);
                                select.data("old-status", newStatus);
                            })
                            .catch(() => {
                                select.val(oldStatus).trigger("change.select2");
                            });
                    }

                    function proceedCompleteAndSend() {
                        localStorage.setItem(
                            "inspection-change",
                            JSON.stringify({
                                orderId,
                                status_inspection: "completed",
                            })
                        );
                        setInspectionOverrideCompleted(orderId);
                        if (OPS_META_CACHE[String(orderId)]) {
                            OPS_META_CACHE[String(orderId)].status_inspection =
                                "completed";
                        }

                        if (progress === 0) {
                            return Swal.fire({
                                title: "Inspection note",
                                input: "textarea",
                                inputLabel:
                                    "Explain why Inspection has 0% progress before completing.",
                                inputPlaceholder: "Reason / context…",
                                inputAttributes: {
                                    "aria-label": "Inspection note",
                                },
                                showCancelButton: false,
                                confirmButtonText: "Save note",
                                reverseButtons: true,
                                inputValidator: (value) => {
                                    if (!value || value.trim().length < 20) {
                                        return "Write a short note (min 20 characters).";
                                    }
                                },
                            }).then((noteResult) => {
                                if (!noteResult.isConfirmed) {
                                    select
                                        .val(oldStatus)
                                        .trigger("change.select2");
                                    return $.Deferred()
                                        .reject("note-not-provided")
                                        .promise();
                                }
                                localStorage.setItem(
                                    "inspection-note-change",
                                    JSON.stringify({
                                        orderId,
                                        inspection_note:
                                            noteResult.value.trim(),
                                    })
                                );
                                return safeEnviarCambioStatus().then(() => {
                                    setInspectionOverrideCompleted(orderId);
                                    select.data("old-status", newStatus);
                                });
                            });
                        }

                        return safeEnviarCambioStatus().then(() => {
                            setInspectionOverrideCompleted(orderId);
                            select.data("old-status", newStatus);
                        });
                    }

                    if (progress < 100) {
                        const html = `
          <div class="mb-2">Current inspection progress: <b>${progress}%</b></div>
          <small class="text-muted d-block mt-2">
            If you continue, <b>Inspection</b> will be marked as <b>COMPLETED</b>.
          </small>`;
                        return Swal.fire({
                            title: "Inspection not at 100%",
                            html,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Yes, continue",
                            cancelButtonText: "Review first",
                            reverseButtons: true,
                            focusCancel: true,
                        }).then((resp) => {
                            if (!resp.isConfirmed) {
                                select.val(oldStatus).trigger("change.select2");
                                return;
                            }
                            return proceedCompleteAndSend().catch((err) => {
                                if (err !== "note-not-provided") {
                                    select
                                        .val(oldStatus)
                                        .trigger("change.select2");
                                }
                            });
                        });
                    }

                    return proceedCompleteAndSend().catch((err) => {
                        if (err !== "note-not-provided") {
                            select.val(oldStatus).trigger("change.select2");
                        }
                    });
                })
                .fail(function () {
                    select.val(oldStatus).trigger("change.select2");
                    Swal.fire({
                        title: "Error",
                        text: "Couldn't fetch order meta.",
                        icon: "error",
                    });
                });

            return;
        }

        //  Enviar directamente si no requiere confirmacion
        enviarCambioStatus();
    });

    // ---- helpers/operation.js ----
    const OPS_META_CACHE = {};
    /**
     * Trae { operation, parent_id, status_inspection } de BD y hace cache por orderId.
     * Sigue siendo compatible con consumidores que solo usan operation/parent_id.
     */
    function fetchOpsMeta(orderId) {
        if (OPS_META_CACHE[orderId]) {
            // Devolver una copia para evitar mutaciones externas del cache
            return $.Deferred()
                .resolve({ ...OPS_META_CACHE[orderId] })
                .promise();
        }

        return $.getJSON(`/orders/${orderId}/ops-meta`).then(function (r) {
            const meta = {
                operation: Number((r && r.operation) || 0),
                parent_id:
                    r && typeof r.parent_id !== "undefined"
                        ? r.parent_id
                        : null,
                inspection_progress: Number((r && r.inspection_progress) || 0),
                status_inspection: String(
                    (r && r.status_inspection) || ""
                ).toLowerCase(), //  nuevo
            };
            OPS_META_CACHE[orderId] = meta;
            return { ...meta };
        });
    }

    //----
    function triggerEditableDueDate(orderId) {
        const dateSpan = $(`.editable-due-date[data-id="${orderId}"]`);
        //console.log(" triggerEditableDueDate llamado para:", orderId);

        if (dateSpan.length > 0) {
            // console.log(" Span encontrado:", dateSpan[0]);
            dateSpan.attr("data-enabled", "1");

            setTimeout(() => {
                //console.log("⏱ Ejecutando .trigger('click') para:", orderId);
                dateSpan.trigger("click");
            }, 100);
        } else {
            console.warn(
                " No se encontro due-date editable para la orden:",
                orderId
            );
        }
    }

    // ===================================================================================================
    //    START------ Editable-due-date ------
    // ===================================================================================================
    tableElement.on("click", ".editable-due-date", function () {
        const span = $(this);
        //console.log(" Click en due-date span", span[0]);
        const orderId = span.data("id");
        const isEnabled = parseInt(span.data("enabled")) === 1;
        const currentValue = span.data("value") || "";

        // console.log( " isEnabled:", isEnabled,"| orderId:",orderId, "| value:",currentValue );

        if (!isEnabled) {
            // console.log(" Edicion deshabilitada para este campo.");
            return;
        }
        const input = $(
            `<input type="date" class="form-control form-control-sm due-date-input">`
        ).val(currentValue);

        // console.log(" Input generado:", input[0]);
        span.replaceWith(input);
        input.focus();

        input.on("blur", function () {
            const newDate = input.val();
            if (!newDate || newDate === currentValue) {
                input.replaceWith(span);
                return;
            }
            handlePostJsonWithAlerts(
                `/orders/${orderId}/update-date-due`,
                { due_date: newDate },
                (data) => {
                    if (data.success) {
                        localStorage.setItem(
                            "date-due-change",
                            JSON.stringify({
                                orderId,
                                due_date: newDate,
                                dias_restantes: data.dias_restantes,
                                alertColor: data.alertColor,
                                alertLabel: data.alertLabel,
                                status: data.status,
                                updatedAt: Date.now(),
                            })
                        );
                        const newSpan = createEditableDateSpan(
                            orderId,
                            newDate,
                            {
                                cssClass: "editable-due-date",
                                underline: false,
                                bold: true,
                            }
                        );
                        const newSpanHtml = newSpan.prop("outerHTML"); // 2025-12-15: reusa HTML para DataTable sin ReferenceError

                        input.replaceWith(newSpan);

                        // Actualizar d?as/alerta u ocultar si Standby+Onhold
                        const rowEl = document.querySelector(
                            `tr[data-order-id="${orderId}"]`
                        );
                        const locVal = rowEl
                            ? String(
                                  $(rowEl).find(".location-select").val() || ""
                              ).toLowerCase()
                            : "";
                        const isStandbyOnhold =
                            data.status === "onhold" && locVal === "standby";

                        if (isStandbyOnhold) {
                            hideDiasYAlerta(orderId);
                        } else {
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
                        }

                        // 2025-12-15: actualizar due_date oculta y reordenar por col 12 (revalida todas las filas)
                        if (rowEl && window.table) {
                            const rowApi = window.table.row(rowEl);
                            const rowIdx = rowApi.index();
                            // Actualiza datos internos en las columnas correctas
                            window.table.cell(rowIdx, 12).data(newDate); // col oculta de ordenamiento
                            window.table.cell(rowIdx, 13).data(newSpanHtml); // col visible con span
                            // Reordenar por due_date (col 12)
                            window.table.order([12, "asc"]).draw(false);
                        }

                        applyRowLateStyle(
                            orderId,
                            data.dias_restantes,
                            data.status || ""
                        );

                        // 2025-12-15: Ajuste automatico de machining_date (-5 dias habiles)
                        autoUpdateMachiningDate(
                            orderId,
                            newDate,
                            data.status || ""
                        );
                    } else {
                        alert("Error al guardar due_date.");
                        input.replaceWith(span);
                    }
                }
            );
        });
    });
    // ===================================================================================================
    //    END------ Editable-due-date ------
    // ===================================================================================================

    // ===================================================================================================
    //   START------ Tooltips ------
    // ===================================================================================================

    // Tooltips
    const initTooltips = () => {
        document
            .querySelectorAll("[title]")
            .forEach((el) => new bootstrap.Tooltip(el));
    };
    initTooltips();

    // ===================================================================================================
    //   START------ Abrir modal notas ------
    // ===================================================================================================

    tableElement.on("click", ".open-notes-modal", function (event) {
        event.preventDefault();
        // console.log("Clic detectado en .open-notes-modal"); // Verifica si esto aparece en consola
        const orderId = $(this).data("id");
        const fullNotes = $(this).data("notes") || "";

        $("#notesOrderId").val(orderId);
        $("#notesTextarea").val(fullNotes);

        // Aqui: usar jQuery y llamar a modal('show')
        $("#notesModal").modal("show");
    });
    // Guardar notas
    $("#notesForm").submit(function (e) {
        //console.log("Interceptando submit del formulario de notas"); //  esto
        e.preventDefault(); // Esto evita que el form se envie "normalmente"
        const orderId = $("#notesOrderId").val();
        const notes = $("#notesTextarea").val();

        handlePostJsonWithAlerts(
            `/orders/${orderId}/update-notes`,
            { notes },
            (data) => {
                //console.log("Respuesta del servidor:", data);
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

    // ===================================================================================================
    //   START------ Es el input para agregar WO QTY ------
    //   Evento blur en inputs de WO QTY
    // ===================================================================================================

    $(document).on("blur", ".wo-qty-input", function () {
        const input = $(this);
        const original = input.data("original");

        let newVal = String(input.val()).trim();
        if (newVal === "") {
            newVal = "0"; // puedes cambiarlo a null si tu backend lo acepta
        }
        const qty = parseInt(newVal, 10);
        if (isNaN(qty) || qty < 0) {
            Swal.fire(" Invalid quantity", "Enter a valid number", "warning");
            return;
        }
        if (String(original) === String(qty)) return;

        const orderId = input.data("id");

        handlePostJsonWithAlerts(
            `/orders/${orderId}/update-wo-qty`,
            { wo_qty: qty },
            (data) => {
                // 1) Actualizar el input con el nuevo valor confirmado
                input.data("original", data.wo_qty_saved);
                input.val(data.wo_qty_saved);

                // 2) Actualizar el total del grupo en el padre
                const parentId = data.parent_id;
                const $parentRow = $(`tr[data-id="${parentId}"]`);
                $parentRow.find(".cell-group-wo-qty").text(data.group_wo_qty);

                // 3) Si usas DataTables, refrescar datos sin recargar todo:
                // const table = $('#miDataTable').DataTable();
                // const row = table.row($parentRow);
                // let rowData = row.data();
                // rowData.wo_qty = data.group_wo_qty;
                // row.data(rowData).draw(false);

                // 4) Guardar en localStorage para sincronizar entre pestanas (opcional)
                localStorage.setItem(
                    "wo-qty-change",
                    JSON.stringify({
                        orderId: data.order_id,
                        parentId: data.parent_id,
                        wo_qty: data.wo_qty_saved,
                        group_wo_qty: data.group_wo_qty,
                    })
                );
                localStorage.removeItem("wo-qty-change");
            },
            " Error to save"
        );
    });

    // ===================================================================================================
    // --- START Aqui agregamos la logica para el boton "Agregar" dentro de Part_description ---
    // Insertar boton 'Agregar' en las celdas de Part_description que contienen "kit"
    // ===================================================================================================

    function agregarBotonesKit() {
        tableElement.find("tbody tr").each(function (index) {
            const partDescCell = $(this).find("td").eq(3); // columna PART/DESCRIPTION
            partDescCell.css("position", "relative"); // para posicionar el boton dentro

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
                            title="Add" 
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
                    //console.log(`✅ Boton agregado en fila ${index}`);
                }
            }
        });
    }

    // Ejecutar al cargar por primera vez
    agregarBotonesKit();

    // Re-ejecutar cada vez que se redibuja la tabla
    tableElement.on("draw.dt", function () {
        //console.log(" Evento draw.dt disparado");
        agregarBotonesKit();
    });

    // Manejar el click en botones 'Agregar' dinamicos
    tableElement.on("click", ".btn-add-kit", function () {
        const btn = $(this);
        const row = btn.closest("tr");

        //    usa data-id en el <tr data-id="123"> y deja este fallback a la col 0
        const originalId = row.data("orderId");

        // Obtener proximo ID antes de hacer cualquier cosa
        fetch("/orders/next-id")
            .then((res) => res.json())
            .then((data) => {
                const nextId = data.next_id;

                // Clonar la fila original (sin eventos)
                const newRow = row.clone(false);

                copySelectAndInputValues(row, newRow);
                // console.log("✔ Location clonada: ",newRow.find('select[name="location"]').val());

                // Mostrar cuantas columnas tiene la fila
                // console.log( "Total celdas en la fila:",newRow.find("td").length);

                // Mostrar el next_id en la primera celda (columna 0)
                // const idCell = newRow.find("td:eq(0)");
                // idCell.text(nextId);
                //  idCell.append(
                //   `<input type="hidden" name="id" value="${nextId}">`
                //  );

                // En las columnas 2, 4 y 6 ponemos inputs vacios
                [1, 3, 5].forEach((index) => {
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
                    .find('input[name="col_text_5"]')
                    .on("keydown", function (e) {
                        if (e.key === "Enter" && !guardado) {
                            e.preventDefault(); // Evita que el Enter dispare el submit
                            e.stopPropagation(); // Evita burbujas

                            const val6 = $(this).val().trim();
                            if (val6) {
                                Swal.fire({
                                    title: "¿Save Order?",
                                    icon: "question",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, Save",
                                    cancelButtonText: "Cancel",
                                    reverseButtons: true,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        guardado = true;
                                        checkInputsAndSend();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "Required WO QTY",
                                    text: "You must capture at least the WO QTY",
                                    icon: "warning",
                                    confirmButtonText: "Ok",
                                });
                            }
                        }
                    });

                function checkInputsAndSend() {
                    const val6 = newRow
                        .find('input[name="col_text_5"]')
                        .val()
                        .trim();

                    if (!val6) {
                        //  alert( "Debes capturar al menos el campo de Cantidad (columna 6).");
                        return;
                    }

                    let dataToSend = { id: nextId };

                    newRow.find("td").each(function (index) {
                        const cell = $(this);
                        //Si existe un input o select:→ Guarda su valor usando .val().
                        const inputOrSelect = cell.find("input, select");

                        if (inputOrSelect.length) {
                            dataToSend[`col_text_${index}`] =
                                inputOrSelect.val();
                        } else {
                            let finalText = "";

                            //  Caso especial para columna de Notas (con .open-notes-modal)
                            const noteSpan = cell.find(".open-notes-modal");
                            if (noteSpan.length) {
                                finalText = noteSpan.data("notes") || "";
                            }

                            //  Caso especial para fecha de maquinado (con .editable-machining-date)
                            else if (
                                cell.find(".editable-machining-date").length
                            ) {
                                finalText =
                                    cell
                                        .find(".editable-machining-date")
                                        .data("value") || "";
                            }
                            //  Caso especial para fecha de maquinado (con .editable-machining-date)
                            else if (cell.find(".editable-due-date").length) {
                                finalText =
                                    cell
                                        .find(".editable-due-date")
                                        .data("value") || "";
                            }

                            //  Caso general (texto visible, sin hijos como select/div/span)
                            else {
                                finalText = cell
                                    .clone()
                                    .children()
                                    .remove()
                                    .end()
                                    .text()
                                    .trim();
                                if (index === 15 && finalText === "Note")
                                    finalText = "";
                            }

                            dataToSend[`col_text_${index}`] = finalText;
                        }
                        //---------------------------------------------------------------------------------

                        // También incluir inputs ocultos
                        cell.find('input[type="hidden"]').each(function () {
                            const hiddenInput = $(this);
                            dataToSend[hiddenInput.attr("name")] =
                                hiddenInput.val();
                        });
                    });

                    //  aqui anadimos el id de la fila original para enviar los valores de co y cust_po
                    dataToSend.original_id = originalId;

                    //console.log("Datos a enviar:", dataToSend); // Aqui justo antes de enviar
                    handlePostJsonWithAlerts(
                        "/orders",
                        dataToSend,
                        (response) => {
                            // alert("Registro guardado con ID: " + response.order_id);

                            // Actualizar visualmente la fila: eliminar inputs excepto columna 7 y dejar texto plano
                            newRow.find("td").each(function (index) {
                                const cell = $(this);
                                if (index === 6) return; // No tocar input en columna 7
                                const input = cell.find("input");
                                if (input.length) {
                                    const value = input.val();
                                    cell.text(value);
                                }
                            });

                            // Recolocar el ID en la columna 0
                            newRow.find("td:eq(0)").text(response.id);
                            // console.log(" Insertando contenido en la columna 18 (Notas)");
                            //  Generar contenido de la columna 18 (Notas)
                            const orderId = response.id;
                            const safeNotes = ""; // Al guardar nuevo, inicia vacio
                            const shortNote = "Note";

                            const newNotesHtml = `<span class="open-notes-modal" data-id="${orderId}" data-notes="" style="cursor:pointer;" title="">
                                    <i class="fas fa-plus-circle me-1 text-muted"></i> Note</span>`;

                            // Insertar en la columna 18 (si existe)
                            const notesCell = newRow.find("td:eq(15)");
                            if (notesCell.length) {
                                notesCell.html(newNotesHtml);
                            } else {
                                //console.warn("⚠ La columna 18 no existe en esta fila.");
                            }

                            // Actualizar DataTable si esta en uso
                            location.reload();
                        },
                        "Error al guardar el registro"
                    );
                }
            })
            .catch((err) => {
                //console.error("Error obteniendo proximo ID:", err);
                //alert("No se pudo obtener el proximo ID");
            });
    });

    //-----------------------------------------

    function copySelectAndInputValues(originalRow, clonedRow) {
        originalRow.find("select").each(function (i) {
            const value = $(this).val();
            const className = $(this).attr("class");
            const name = $(this).attr("name");

            // Encuentra el select correspondiente en la fila clonada
            const cloned = clonedRow
                .find(`select.${className.split(" ").join(".")}`)
                .eq(i);
            if (cloned.length) {
                cloned.val(value); // asigna el valor seleccionado real
            }
        });

        originalRow.find("input").each(function (i) {
            const value = $(this).val();
            const name = $(this).attr("name");
            const className = $(this).attr("class");

            const cloned = clonedRow
                .find(`input.${className.split(" ").join(".")}`)
                .eq(i);
            if (cloned.length) {
                cloned.val(value);
            }
        });
    }
});
