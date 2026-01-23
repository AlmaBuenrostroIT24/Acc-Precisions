document.addEventListener("DOMContentLoaded", () => {
    // Cache de elementos usados frecuentemente
    // Helpers
    const qs = (id) => document.getElementById(id);

    function erpSwalFire(opts) {
        const SwalRef = window.Swal;
        if (!SwalRef || typeof SwalRef.fire !== "function") {
            const msg =
                typeof opts === "string"
                    ? opts
                    : (opts && (opts.text || opts.title)) || "OK";
            alert(String(msg));
            return Promise.resolve({ isConfirmed: true });
        }

        const base = {
            buttonsStyling: false,
            customClass: {
                popup: "erp-swal",
                title: "erp-swal-title",
                htmlContainer: "erp-swal-text",
                icon: "erp-swal-icon",
                confirmButton: "btn btn-erp-primary px-4",
                cancelButton: "btn btn-light px-4",
            },
        };

        const merged = typeof opts === "string" ? { title: opts } : opts || {};

        return SwalRef.fire({
            ...base,
            ...merged,
            customClass: {
                ...base.customClass,
                ...(merged.customClass || {}),
            },
        });
    }

    function getJson(url) {
        return fetch(url, {
            method: "GET",
            headers: { Accept: "application/json" },
        }).then(async (res) => {
            const rawText = await res.text();
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${rawText || res.statusText}`);
            }
            try {
                return JSON.parse(rawText);
            } catch (err) {
                throw new Error(
                    `Respuesta no JSON valida: ${rawText.slice(0, 200)}`
                );
            }
        });
    }

    // Cache de elementos (pueden NO existir según el rol)
    const tableElement = $("#orders_scheduleTable"); // jQuery, ok si no existe
    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const loadingMsg = qs("loading-message");
    const loadingOverlay = qs("loading-overlay");
    const uploadForm = qs("upload-form");
    const inputCsv = qs("csv_file");
    const labelCsv = qs("csv_file_label");
    const btnUpload = qs("btn-upload");
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

    // ------ Mostrar mensaje/overlay de carga al enviar form (si existe) ------
    if (uploadForm) {
        uploadForm.addEventListener("submit", () => {
            if (loadingMsg) loadingMsg.style.display = "block";
            if (loadingOverlay) loadingOverlay.classList.remove("d-none");
            if (btnUpload) btnUpload.disabled = true;
            // No deshabilitar el input file antes de enviar: un control disabled no se incluye en el POST,
            // y Laravel lo interpreta como "csv_file is required".
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

    // 2025-12-15: hook global para ocultar Standby+Onhold salvo filtro Standby o búsqueda
    let standbyFilterHookAdded = false;
    function ensureStandbyFilterHook() {
        if (standbyFilterHookAdded) return;
        $.fn.dataTable.ext.search.push(function (settings, data) {
            if (
                !settings.nTable ||
                settings.nTable.id !== "orders_scheduleTable"
            )
                return true;
            const loc = String(data[1] || "").toLowerCase(); // col oculta location
            const status = String(data[2] || "").toLowerCase(); // col oculta status
            const locationFilterVal = (
                document.getElementById("locationFilter")?.value || ""
            ).toLowerCase();
            const statusFilterVal = (
                document.getElementById("statusFilter")?.value || ""
            ).toLowerCase();
            const customerFilterVal = (
                document.getElementById("customerFilter")?.value || ""
            ).toLowerCase();
            const globalSearch = (
                (settings.oPreviousSearch &&
                    settings.oPreviousSearch.sSearch) ||
                ""
            ).trim();
            const isStandbyOnhold = loc === "standby" && status === "onhold";
            if (locationFilterVal === "standby") return true; // mostrar todo si filtra Standby
            if (statusFilterVal === "onhold") return true; // permitir verlos si el filtro de status es onhold
            if (customerFilterVal) return true; // permitir verlos si hay filtro de cliente
            if (globalSearch) return true; // permitir búsqueda global
            if (isStandbyOnhold) return false; // ocultar en vista normal
            return true;
        });
        standbyFilterHookAdded = true;
    }

    // 2025-12-17: ocultar registros con due_date en año 2049 (salvo búsqueda global o filtro customer)
    let year2049FilterHookAdded = false;
    function ensureYear2049FilterHook() {
        if (year2049FilterHookAdded) return;
        $.fn.dataTable.ext.search.push(function (settings, data) {
            if (
                !settings.nTable ||
                settings.nTable.id !== "orders_scheduleTable"
            )
                return true;
            const globalSearch = (
                (settings.oPreviousSearch &&
                    settings.oPreviousSearch.sSearch) ||
                ""
            ).trim();
            const customerFilterVal = (
                document.getElementById("customerFilter")?.value || ""
            ).toLowerCase();
            const dueDateHidden = String(data[12] || "").trim(); // col oculta due_date (YYYY-MM-DD)
            const dueDateVisible = String(data[13] || "").trim(); // col visible formato M-d-Y
            const dueDateStr = dueDateHidden || dueDateVisible;
            const isYear2049 = /2049/.test(dueDateStr);

            // Permitir verlos si hay búsqueda global o filtro customer
            if (globalSearch) return true;
            if (customerFilterVal) return true;

            // En vista normal (sin búsqueda ni filtro customer), ocultar 2049
            if (isYear2049) return false;
            return true;
        });
        year2049FilterHookAdded = true;
    }

    function initOrdersTable(tableElement, options = {}) {
        const baseOptions = {
            paging: true,
            pageLength: 15,
            lengthChange: false,
            searching: true,
            order: [
                [22, "desc"], // PriorityText (1/0) al inicio
                [12, "asc"], // 2025-12-15: ordenar por due_date (col oculta 12)
            ],
            info: true,
            autoWidth: false,
            columnDefs: [
                { targets: 0, visible: false, searchable: false }, // ID
                { targets: 1, visible: false, searchable: true }, // LocationText
                { targets: 2, visible: false, searchable: true }, // StatusText
                { targets: 12, visible: false, searchable: false }, // DueDateText
                { targets: 22, visible: false, searchable: false }, // PriorityText
            ],
            // ERP footer (match Orders Completed): table + footer (info/pagination). Buttons hidden via JS.
            dom: "Brt<'erp-dt-footer d-flex align-items-center justify-content-between flex-wrap'<'dataTables_info'i><'dataTables_paginate'p>>",

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
            "/scheduley": {
                pageLength: 40,
                searching: false,
                order: [
                    [22, "desc"],
                    [12, "asc"],
                ],
            },
            "/scheduleh": {
                pageLength: 40,
                searching: false,
                order: [
                    [22, "desc"],
                    [12, "asc"],
                ],
            },
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

        // 2025-12-15: asegurar filtro Standby+Onhold antes de inicializar DataTable
        ensureStandbyFilterHook();
        // 2025-12-17: ocultar due_date año 2049 salvo filtro Standby o búsqueda
        ensureYear2049FilterHook();

        //  Inicializar tabla
        window.table = initOrdersTable(tableElement, config);

        // Re-inicializar tooltips en cada redraw (DataTables recrea nodos)
        tableElement.on("draw.dt", function () {
            if (typeof initTooltips === "function") initTooltips();
        });

        // Search compacto en la cabecera (si existe). Si no existe, se mantiene el search nativo.
        const topSearch = qs("scheduleGlobalSearch");
        if (topSearch && window.table) {
            const dtFilterEl = document.getElementById(
                "orders_scheduleTable_filter"
            );
            if (dtFilterEl) dtFilterEl.style.display = "none";
            try {
                topSearch.value = window.table.search() || "";
            } catch (e) {}
            topSearch.addEventListener("input", () => {
                window.table.search(topSearch.value || "").draw();
            });
        }

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
            (
                data[1] ||
                $row.find("[id^='hidden-location-']").text() ||
                ""
            ).trim()
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

        // 2025-12-15: onhold debe conservar su color aunque la fecha esté vencida
        if (status === "onhold") {
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
                        // Mantener clase ERP del alert (para que no cambie el estilo al refrescar)
                        const keepErp = alertaDiv.classList.contains("erp-alert-bar") ||
                            !!alertaDiv.closest(".erp-alert");
                        alertaDiv.className =
                            "progress-bar " +
                            (keepErp ? "erp-alert-bar " : "") +
                            mData.alertColor;
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
    // 2025-12-15: forzar redraw para que el filtro Standby aplique el ext.search
    $("#locationFilter").on("change", function () {
        if (window.table) window.table.draw(false);
    });

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
        if ($(this).closest("tr").hasClass("kit-new-row")) return;
        const select = $(this);
        const orderId = select.data("id");
        lastLocations[orderId] = select.val(); // Guardamos la última antes del cambio
    });

    // Actualizar Location
    tableElement.on("change", ".location-select", function () {
        if ($(this).closest("tr").hasClass("kit-new-row")) return;
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
                        String(newLocation || "").toLowerCase() === "standby" &&
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
            ? String(locCell.textContent || "")
                  .trim()
                  .toLowerCase()
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

        // 2025-12-15: onhold debe respetar su color (sin marcar por fecha)
        if (statusVal === "onhold") {
            row.classList.add(`bg-status-${statusVal}`);
            if (String(row.dataset.priority || "").toLowerCase() === "yes") {
                row.classList.add("row-priority");
            }
            return;
        }

        const isPriority = String(row.dataset.priority || "").toLowerCase() === "yes";

        if (dias < 0) {
            row.classList.add("row-late");
            if (isPriority) row.classList.add("row-priority");
            return;
        }

        if (statusVal) {
            row.classList.add(`bg-status-${statusVal}`);
        }
        if (isPriority) row.classList.add("row-priority");
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

    function setTooltipText(el, text) {
        if (!el) return;
        try {
            el.setAttribute("title", text);
            // Bootstrap 4 caches title in data-original-title
            el.setAttribute("data-original-title", text);
            // Bootstrap 5 caches title in data-bs-original-title
            el.setAttribute("data-bs-original-title", text);
        } catch (e) {
            // ignore
        }
    }

    function disposeTooltip(el) {
        if (!el) return;
        try {
            const inst = window.bootstrap?.Tooltip?.getInstance?.(el);
            if (inst) {
                try {
                    inst.hide?.();
                } catch (e) {
                    // ignore
                }
                inst.dispose();
            }
        } catch (e) {
            // ignore
        }
        try {
            if (window.jQuery?.fn?.tooltip) {
                window.jQuery(el).tooltip("hide");
                window.jQuery(el).tooltip("dispose");
            }
        } catch (e) {
            // ignore
        }
    }

    // 2. Evita duplicar logica de clases e iconos con una funcion
    function updateToggleButton(button, newValue) {
        button.data("value", newValue);
        button.attr("data-value", newValue);
        button.toggleClass("btn-primary", newValue === 1);
        button.toggleClass("btn-secondary", newValue === 0);
        button
            .find("i")
            .attr(
                "class",
                `fas ${newValue === 1 ? "fa-check-circle" : "fa-times-circle"}`
            );
    }

    function updateToggleTooltip(button, isReport, newValue) {
        const tooltipEl = button
            .closest('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]')
            .get(0);
        if (!tooltipEl) return;

        const label = isReport ? "Report" : "Outsource";
        const valueText = newValue === 1 ? "Yes" : "No";
        setTooltipText(tooltipEl, `${label}: ${valueText}`);

        // Re-init so tooltip uses the updated title without requiring a page refresh
        disposeTooltip(tooltipEl);
        try {
            initTooltips();
        } catch (e) {
            // ignore
        }
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
        const formatted = `${shortMonth}-${day.padStart(2, "0")}-${year}`;

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
            if (button.closest("tr").hasClass("kit-new-row")) {
                const currentValue = parseInt(button.data("value"));
                const newValue = currentValue === 1 ? 0 : 1;
                const isReport = button.hasClass("toggle-report-btn");
                updateToggleButton(button, newValue);
                updateToggleTooltip(button, isReport, newValue);
                return;
            }
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
                        updateToggleTooltip(button, isReport, newValue);

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
            if (!newDate) {
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
                                const keepErp = alertaDiv.classList.contains("erp-alert-bar") ||
                                    !!alertaDiv.closest(".erp-alert");
                                alertaDiv.className =
                                    "progress-bar " +
                                    (keepErp ? "erp-alert-bar " : "") +
                                    data.alertColor;
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
        if ($(this).closest("tr").hasClass("kit-new-row")) return;
        const currentVal = ($(this).val() || "").toLowerCase(); // .toLowerCase() Normalizar el oldStatus a minúsculas
        $(this).data("old-status", currentVal);
        // Al abrir el dropdown, evitar que se vea "verde" mientras seleccionas otro status
        $(this).removeClass("erp-status-select--ready");
    });

    tableElement.on("blur", ".status-select", function () {
        if ($(this).closest("tr").hasClass("kit-new-row")) return;
        const cur = ($(this).val() || "").toLowerCase();
        $(this).toggleClass("erp-status-select--ready", cur === "ready");
    });

    // Actualizar Status con confirmacion SweetAlert
    tableElement.on("change", ".status-select", function () {
        if ($(this).closest("tr").hasClass("kit-new-row")) return;
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
                            data.location || locationSelect.val() || ""
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
                                (data.last_location || "").trim() || "Floor";
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
                                const keepErp = alertaDiv.classList.contains("erp-alert-bar") ||
                                    !!alertaDiv.closest(".erp-alert");
                                alertaDiv.className =
                                    "progress-bar " +
                                    (keepErp ? "erp-alert-bar " : "") +
                                    data.alertColor;
                                alertaDiv.textContent = data.alertLabel;
                            }
                        }

                        //  Actualiza el valor de referencia para futuros cambios
                        select.data("old-status", newStatus);
                        select.toggleClass(
                            "erp-status-select--ready",
                            newStatus === "ready"
                        );

                        // Asegurar que el color de fila respete "Late" primero (vs. status)
                        if (typeof applyRowLateStyle === "function") {
                            applyRowLateStyle(orderId, data.dias_restantes, data.status);
                        }
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
                erpSwalFire({
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
            erpSwalFire({
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

        function fetchInspectionFamily(orderId) {
            return $.getJSON(`/orders/${orderId}/inspection-family`);
        }

        function fmtVal(v) {
            const s = (v ?? "").toString().trim();
            return s ? s : "-";
        }

        function showInspectionFamilySwal(opts) {
            const parent = opts.parent || null;
            const children = Array.isArray(opts.children) ? opts.children : [];
            const currentChildId = opts.currentChildId;
            const isCurrentParent =
                parent && Number(parent?.id) === Number(currentChildId);

            function dueSortKey(x) {
                const d = (x?.due_date || "").toString().trim();
                if (!d) return Number.POSITIVE_INFINITY;
                const t = Date.parse(`${d}T00:00:00`);
                return Number.isFinite(t) ? t : Number.POSITIVE_INFINITY;
            }

            // Ordenar por Due Date y dejar el "Parent/Last Partial" siempre al final
            const sortedChildren = [...children].sort((a, b) => {
                const ka = dueSortKey(a);
                const kb = dueSortKey(b);
                if (ka !== kb) return ka - kb;
                return Number(a?.id || 0) - Number(b?.id || 0);
            });

            const totalItems = sortedChildren.length + (parent ? 1 : 0);
            let posLabel = "";
            if (currentChildId && totalItems > 0) {
                if (parent && Number(parent?.id) === Number(currentChildId)) {
                    posLabel = `${totalItems}/${totalItems}`;
                } else {
                    const idx = sortedChildren.findIndex(
                        (x) => Number(x?.id) === Number(currentChildId)
                    );
                    if (idx >= 0) posLabel = `${idx + 1}/${totalItems}`;
                }
            }

            const progressPct = (() => {
                const p = Number(parent?.inspection_progress);
                if (Number.isFinite(p)) return p;
                const maxChild = Math.max(
                    0,
                    ...sortedChildren.map((c) => Number(c?.inspection_progress || 0))
                );
                return Number.isFinite(maxChild) ? maxChild : 0;
            })();

            const parentRow = parent
                ? `<tr class="${String(parent?.status || "").toLowerCase() === "sent" ? "table-success" : ""}">
                    <td><b>Last Partial</b></td>
                    <td>${fmtVal(parent.work_id)}</td>
                    <td>${fmtVal(parent.location)}</td>
                    <td>${fmtVal(parent.status)}</td>
                    <td>${fmtVal(parent.due_date)}</td>
                  </tr>`
                : "";

            const childRows = sortedChildren
                .map((c, i) => {
                    const isCurrent = Number(c?.id) === Number(currentChildId);
                    const tag = isCurrent ? `<span class="badge badge-primary ml-2">Selected</span>` : "";
                    const rowClass =
                        String(c?.status || "").toLowerCase() === "sent"
                            ? "table-success"
                            : "";
                    return `<tr class="${rowClass}">
                        <td>Partial ${i + 1}${tag}</td>
                        <td>${fmtVal(c.work_id)}</td>
                        <td>${fmtVal(c.location)}</td>
                        <td>${fmtVal(c.status)}</td>
                        <td>${fmtVal(c.due_date)}</td>
                    </tr>`;
                })
                .join("");

            const html = `
                <div class="text-left" style="font-size:14px;">
                    <div class="mb-2 text-muted">
                        ${
                            isCurrentParent
                                ? "This order belongs to a group of partial orders. This is the last partial order."
                                : "This order belongs to a partials group. Inspection completion is handled by the last partial order."
                        }
                        ${posLabel ? `<span class="badge badge-light ml-2">${posLabel}</span>` : ""}
                    </div>
                    <div class="mb-2">Current inspection progress: <b>${progressPct}%</b></div>
                    <div class="table-responsive" style="max-height:320px; overflow:auto;">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:110px;">Partial</th>
                                    <th style="width:90px;">Work ID</th>
                                    <th style="width:90px;">Location</th>
                                    <th style="width:120px;">Status</th>
                                    <th style="width:120px;">Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${childRows}
                                ${parentRow}
                            </tbody>
                        </table>
                    </div>
                </div>`;

            return erpSwalFire({
                title: "Inspection overview",
                html,
                icon: "info",
                showCancelButton: true,
                confirmButtonText: "Continue",
                cancelButtonText: "Cancel",
                reverseButtons: true,
                focusCancel: true,
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

                    if (parent_id) {
                        return fetchInspectionFamily(parent_id)
                            .then((family) =>
                                showInspectionFamilySwal({
                                    parent: family?.parent,
                                    children: family?.children,
                                    currentChildId: orderId,
                                })
                            )
                            .then((resp) => {
                                if (!resp || !resp.isConfirmed) {
                                    select.val(oldStatus).trigger("change.select2");
                                    return $.Deferred().reject("cancelled").promise();
                                }

                                const html = `
          <p class="text-muted d-block mt-2" style="font-size: 1.05rem;">
            Changing status to <b>${newStatus}</b> will also move the location to <b>HEARST</b>.
          </p>`;
                                return erpSwalFire({
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
                                        return $.Deferred().reject("cancelled").promise();
                                    }

                                    localStorage.setItem(
                                        "location-change",
                                        JSON.stringify({ orderId, location: "hearst" })
                                    );

                                    return safeEnviarCambioStatus()
                                        .then(() => {
                                            select.data("old-status", newStatus);
                                            // Importante: este caso (partial/hijo) ya hizo el flujo completo.
                                            // Cortamos la cadena para que NO se dispare el confirm duplicado
                                            // del flujo normal (que marca inspection como completed).
                                            return $.Deferred()
                                                .reject("handled-child")
                                                .promise();
                                        })
                                        .catch((err) => {
                                            // Este reject es intencional para cortar el flujo; no revertir el select.
                                            if (err === "handled-child") {
                                                return $.Deferred().reject(err).promise();
                                            }
                                            select.val(oldStatus).trigger("change.select2");
                                            return $.Deferred().reject(err).promise();
                                        });
                                });
                            });
                    }

                    //  ÚNICA confirmacion si < 100%
                    // Si es el parent (no tiene parent_id) y tiene hijos, mostrar el overview primero.
                    if (!parent_id) {
                        return fetchInspectionFamily(orderId)
                            .then((family) => {
                                const kids = Array.isArray(family?.children)
                                    ? family.children
                                    : [];
                                if (!kids.length) return;
                                return showInspectionFamilySwal({
                                    parent: family?.parent,
                                    children: kids,
                                    currentChildId: orderId,
                                }).then((resp) => {
                                    if (!resp || !resp.isConfirmed) {
                                        select.val(oldStatus).trigger("change.select2");
                                        return $.Deferred()
                                            .reject("cancelled")
                                            .promise();
                                    }
                                });
                            })
                            .then(() => {
                                if (progress < 100) {
                                    const html = `
          <div class="mb-2">Current inspection progress: <b>${progress}%</b></div>
          <small class="text-muted d-block mt-2">
            If you continue, <b>Inspection</b> will be marked as <b>COMPLETED</b>.
          </small>`;
                                    return erpSwalFire({
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
                                        setInspectionOverrideCompleted(orderId);
                                    });
                                }
                            });
                    }

                    if (progress < 100) {
                        const html = `
          <div class="mb-2">Current inspection progress: <b>${progress}%</b></div>
          <small class="text-muted d-block mt-2">
            If you continue, <b>Inspection</b> will be marked as <b>COMPLETED</b>.
          </small>`;
                        return erpSwalFire({
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
                        return erpSwalFire({
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
                        erpSwalFire({
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
                        reason !== "note-not-provided" &&
                        reason !== "handled-child"
                    ) {
                        select.val(oldStatus).trigger("change.select2");
                        erpSwalFire({
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

                    if (parent_id) {
                        return fetchInspectionFamily(parent_id)
                            .then((family) =>
                                showInspectionFamilySwal({
                                    parent: family?.parent,
                                    children: family?.children,
                                    currentChildId: orderId,
                                })
                            )
                            .then((resp) => {
                                if (!resp || !resp.isConfirmed) {
                                    select.val(oldStatus).trigger("change.select2");
                                    return;
                                }
                                return safeEnviarCambioStatus()
                                    .then(() => {
                                        select.data("old-status", newStatus);
                                    })
                                    .catch(() => {
                                        select.val(oldStatus).trigger("change.select2");
                                    });
                            });
                    }

                    function mainFlow() {
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
                            return erpSwalFire({
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
                        return erpSwalFire({
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
                    }

                    // Parent: si tiene hijos, mostrar overview y luego continuar con la lógica normal.
                    return fetchInspectionFamily(orderId)
                        .then((family) => {
                            const kids = Array.isArray(family?.children)
                                ? family.children
                                : [];
                            if (!kids.length) return;
                            return showInspectionFamilySwal({
                                parent: family?.parent,
                                children: kids,
                                currentChildId: orderId,
                            }).then((resp) => {
                                if (!resp || !resp.isConfirmed) {
                                    select.val(oldStatus).trigger("change.select2");
                                    return $.Deferred()
                                        .reject("cancelled")
                                        .promise();
                                }
                            });
                        })
                        .then(() => mainFlow());
                })
                .fail(function (reason) {
                    if (reason === "cancelled") return;
                    select.val(oldStatus).trigger("change.select2");
                    erpSwalFire({
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

    // 2025-12-15: formatear fechas a "Mon-25-2025" para badges
    function formatDateLabel(dateStr) {
        if (!dateStr) return "";
        const d = new Date(dateStr);
        if (Number.isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString("en-US", {
            month: "short",
            day: "2-digit",
            year: "numeric",
        });
    }

    // 2025-12-15: render badge de update_duedate bajo due_date
    function renderUpdateDueBadge(spanEl, updateDateStr, show) {
        const td = $(spanEl).closest("td");
        let badgeWrap = td.find(".update-duedate-badge-wrap");
        if (!show) {
            if (badgeWrap.length) badgeWrap.remove();
            return;
        }
        const label = formatDateLabel(updateDateStr);
        if (!badgeWrap.length) {
            badgeWrap = $(`
                <div class="mt-1 update-duedate-badge-wrap">
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-history me-1"></i> ${label}
                    </span>
                </div>
            `);
            td.append(badgeWrap);
        } else {
            badgeWrap
                .find(".badge")
                .html(`<i class="fas fa-history me-1"></i> ${label}`)
                .removeClass("bg-secondary text-light")
                .addClass("bg-warning text-dark");
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

                        // Badge de update_duedate en vivo
                        const shouldShowBadge = !!data.update_duedate; // mostrar badge siempre que exista update_duedate
                        if (typeof renderUpdateDueBadge === "function") {
                            renderUpdateDueBadge(
                                newSpan,
                                data.update_duedate,
                                shouldShowBadge
                            );
                        }
                        const badgeHtml = shouldShowBadge
                            ? `
                                <div class="mt-1 update-duedate-badge-wrap">
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-history me-1"></i> ${formatDateLabel(
                                            data.update_duedate
                                        )}
                                    </span>
                                </div>
                              `
                            : "";

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
                                const keepErp = alertaDiv.classList.contains("erp-alert-bar") ||
                                    !!alertaDiv.closest(".erp-alert");
                                alertaDiv.className =
                                    "progress-bar " +
                                    (keepErp ? "erp-alert-bar " : "") +
                                    data.alertColor;
                                alertaDiv.textContent = data.alertLabel;
                            }
                        }

                        // 2025-12-15: actualizar due_date oculta y reordenar por col 12 (revalida todas las filas)
                        if (rowEl && window.table) {
                            const rowApi = window.table.row(rowEl);
                            const rowIdx = rowApi.index();
                            // Actualiza datos internos en las columnas correctas
                            window.table.cell(rowIdx, 12).data(newDate); // col oculta de ordenamiento
                            window.table
                                .cell(rowIdx, 13)
                                .data(newSpanHtml + badgeHtml); // col visible con span + badge si aplica
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
    function initTooltips() {
        const nodes = document.querySelectorAll(
            '[data-bs-toggle="tooltip"], [data-toggle="tooltip"]'
        );

        const erpTemplate =
            '<div class="tooltip erp-tooltip" role="tooltip">' +
            '<div class="arrow"></div>' +
            '<div class="tooltip-inner"></div>' +
            "</div>";

        nodes.forEach((el) => {
            try {
                const requestedClass =
                    el.getAttribute("data-bs-custom-class") ||
                    el.getAttribute("data-custom-class") ||
                    "";
                const wantsErp = requestedClass
                    .split(/\s+/)
                    .includes("erp-tooltip");

                const baseOptions = {
                    container: "body",
                    boundary: "window",
                };

                // Bootstrap 5 (or any build exposing window.bootstrap.Tooltip)
                if (window.bootstrap?.Tooltip) {
                    const supportsGetOrCreate =
                        typeof window.bootstrap.Tooltip.getOrCreateInstance ===
                        "function";

                    const options = { ...baseOptions };
                    if (wantsErp && supportsGetOrCreate) {
                        options.customClass = "erp-tooltip";
                    }
                    if (wantsErp && !supportsGetOrCreate) {
                        options.template = erpTemplate;
                    }

                    if (supportsGetOrCreate) {
                        window.bootstrap.Tooltip.getOrCreateInstance(
                            el,
                            options
                        );
                    } else {
                        new window.bootstrap.Tooltip(el, options);
                    }
                    return;
                }

                // Bootstrap 4 (AdminLTE) fallback via jQuery plugin
                if (window.jQuery?.fn?.tooltip) {
                    const options = { ...baseOptions };
                    if (wantsErp) options.template = erpTemplate;
                    window.jQuery(el).tooltip(options);
                }
            } catch (e) {
                // ignore
            }
        });
    }
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
            erpSwalFire({
                title: "Invalid quantity",
                text: "Enter a valid number",
                icon: "warning",
                confirmButtonText: "OK",
            });
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
                const $parentRow = $(
                    `tr[data-order-id="${parentId}"], tr#row-${parentId}`
                );
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
            if ($(this).hasClass("kit-new-row")) return;
            const partDescCell = $(this).find("td.part-desc-cell"); // columna PART/DESCRIPTION
            if (!partDescCell.length) return;
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
                                top: 50%; 
                                transform: translateY(-50%);
                                z-index: 2;
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

    function handleAddKitOriginal(btn, row, originalId) {
        btn.prop("disabled", true);
        getJson("/orders/next-id")
            .then((data) => {
                const nextId = data && data.next_id;
                if (!nextId) throw new Error("Missing next_id");

                const $row = $(row);
                const newRow = $row.clone(false);
                copySelectAndInputValues($row, newRow);
                newRow.addClass("kit-new-row");
                newRow.find(".btn-add-kit").remove();

                // Inputs en posiciones fijas (mapping del backend), NO por índice de columna visual
                newRow.find("td.work-id-cell").html(
                    `<input type="text" name="col_text_1" class="form-control form-control-sm" value="">`
                );
                newRow.find("td.part-desc-cell").html(
                    `<input type="text" name="col_text_3" class="form-control form-control-sm" value="">`
                );

                // Copiar COQTY/WOQTY a inputs (ahi se edita/guarda)
                const coQtyOrig = safeText($row.find("td.qty-cell").first());
                const woQtyOrigRaw =
                    $row.find(".wo-qty-input").length
                        ? String($row.find(".wo-qty-input").val() || "").trim()
                        : safeText($row.find("td.wo-qty-cell").first());

                newRow.find("td.qty-cell").html(
                    `<input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="off" name="col_text_5" class="form-control form-control-sm" value="${coQtyOrig}">`
                );
                newRow.find("td.wo-qty-cell").html(
                    `<input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="off" name="col_text_6" class="form-control form-control-sm kit-wo-qty-input" value="${woQtyOrigRaw}">`
                );

                // Desactivar controles que podrían disparar updates a la orden original
                newRow
                    .find(".location-select, .status-select")
                    .prop("disabled", false)
                    .removeAttr("data-id")
                    .removeAttr("data-old")
                    .removeAttr("data-old-status");
                newRow
                    .find(".toggle-report-btn, .toggle-source-btn")
                    .removeAttr("data-id")
                    .prop("disabled", false)
                    .css({ "pointer-events": "", opacity: "" });
                newRow
                    .find(
                        ".editable-work-id, .editable-station, .editable-machining-date, .editable-due-date, .open-notes-modal"
                    )
                    .removeClass(
                        "editable-work-id editable-station editable-machining-date editable-due-date open-notes-modal text-decoration-underline"
                    )
                    .removeAttr("data-id")
                    .removeAttr("data-value")
                    .removeAttr("data-enabled")
                    .removeAttr("data-notes");

                $row.after(newRow);
                const workIdInput = newRow.find('input[name="col_text_1"]');
                const coQtyInput = newRow.find('input[name="col_text_5"]');
                const woQtyInput = newRow.find('input[name="col_text_6"]');
                if (workIdInput.length) setTimeout(() => workIdInput.trigger("focus"), 0);

                let guardado = false;

                function tryConfirmSave(e) {
                    if (e.key !== "Enter" || guardado) return;
                    e.preventDefault();
                    e.stopPropagation();

                    const coNum = Number(String(coQtyInput.val() || "").trim());
                    const woNum = Number(String(woQtyInput.val() || "").trim());

                    const validInt = (n) =>
                        Number.isFinite(n) && Number.isInteger(n) && n >= 0;

                    if (!validInt(coNum)) {
                        erpSwalFire({
                            title: "Required CO QTY",
                            text: "Enter a valid CO QTY (0 or more).",
                            icon: "warning",
                            confirmButtonText: "OK",
                        });
                        coQtyInput.trigger("focus");
                        return;
                    }

                    if (!validInt(woNum)) {
                        erpSwalFire({
                            title: "Required WO QTY",
                            text: "Enter a valid WO QTY (0 or more).",
                            icon: "warning",
                            confirmButtonText: "OK",
                        });
                        woQtyInput.trigger("focus");
                        return;
                    }

                    erpSwalFire({
                        title: "Save Order?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonText: "Yes, Save",
                        cancelButtonText: "Cancel",
                        reverseButtons: true,
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        guardado = true;
                        checkInputsAndSend();
                    });
                }

                coQtyInput.on("keydown", tryConfirmSave);
                woQtyInput.on("keydown", tryConfirmSave);

                function safeText($el) {
                    const t = ($el && $el.text ? $el.text() : "") || "";
                    return String(t).trim();
                }

                function safeData($el, key) {
                    if (!$el || !$el.length) return "";
                    const v = $el.data(key);
                    return v == null ? "" : String(v).trim();
                }

                function safeBtnValue($el) {
                    if (!$el || !$el.length) return "0";
                    const vData = $el.data("value");
                    if (vData != null && vData !== "") return String(vData).trim();
                    const vAttr = $el.attr("data-value");
                    return vAttr == null ? "0" : String(vAttr).trim();
                }

                function checkInputsAndSend() {
                    const coNum = Number(String(coQtyInput.val() || "").trim());
                    const woNum = Number(String(woQtyInput.val() || "").trim());
                    if (
                        !Number.isFinite(coNum) ||
                        !Number.isInteger(coNum) ||
                        coNum < 0 ||
                        !Number.isFinite(woNum) ||
                        !Number.isInteger(woNum) ||
                        woNum < 0
                    )
                        return;

                    const notesVal = safeData($row.find(".open-notes-modal").first(), "notes");
                    const stationTxt = safeText($row.find("td.station-cell").first());

                    const dataToSend = {
                        id: nextId,
                        original_id: originalId,
                        col_text_0: String(newRow.find("select.location-select").val() || "").trim(),
                        col_text_1: String(workIdInput.val() || "").trim(),
                        col_text_2: safeText($row.find("td.pn-cell").first()),
                        col_text_3: String(newRow.find('input[name="col_text_3"]').val() || "").trim(),
                        col_text_4: safeText($row.find("td.customer-cell").first()),
                        col_text_5: String(coQtyInput.val() || "").trim(),
                        col_text_6: String(woQtyInput.val() || "").trim(),
                        col_text_7: String(newRow.find("select.status-select").val() || "").trim(),
                        col_text_8: safeData($row.find(".editable-machining-date").first(), "value"),
                        col_text_9: safeData($row.find(".editable-due-date").first(), "value"),
                        col_text_10: "",
                        col_text_11: "",
                        col_text_12: safeBtnValue(newRow.find("button.toggle-report-btn").first()),
                        col_text_13: safeBtnValue(newRow.find("button.toggle-source-btn").first()),
                        col_text_14: stationTxt === "N/A" ? "" : stationTxt,
                        col_text_15: notesVal,
                    };

                    handlePostJsonWithAlerts(
                        "/orders",
                        dataToSend,
                        () => location.reload(),
                        "Error al guardar el registro"
                    );
                }
            })
            .catch((err) => {
                console.error("KIT error", err);
                erpSwalFire({
                    title: "Error",
                    text: "No se pudo crear la fila kit.",
                    icon: "error",
                    confirmButtonText: "OK",
                });
            })
            .finally(() => {
                btn.prop("disabled", false);
            });
    }

    // Manejar el click en botones 'Agregar' dinamicos
    tableElement.on("click", ".btn-add-kit", function (e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = $(this);
        const row = btn.closest("tr");

        //    usa data-id en el <tr data-id="123"> y deja este fallback a la col 0
        const originalId =
            row.data("orderId") ||
            row.attr("data-order-id") ||
            row.attr("data-orderid") ||
            null;
        if (!originalId) {
            erpSwalFire({
                title: "Error",
                text: "Couldn't read original order id.",
                icon: "error",
                confirmButtonText: "OK",
            });
            return;
        }

        // Usa la lógica "original" (crear fila debajo y guardar con Enter en WO QTY)
        handleAddKitOriginal(btn, row, originalId);
        return;

        // Obtener proximo ID antes de hacer cualquier cosa
        btn.prop("disabled", true);
        getJson("/orders/next-id")
            .then((data) => {
                const nextId = data && data.next_id;
                if (!nextId) {
                    throw new Error("Missing next_id");
                }

                // Clonar la fila original (sin eventos)
                const newRow = row.clone(false);

                // Evitar IDs duplicados / handlers que afecten la orden original
                newRow.removeAttr("id");
                newRow.removeAttr("data-order-id");
                newRow.removeAttr("data-priority");
                newRow.addClass("kit-new-row");
                newRow.find("[id]").removeAttr("id");

                // Quitar acciones/ediciones en la fila clonada (evita updates a la orden original)
                newRow
                    .find(
                        ".open-notes-modal, .editable-station, .editable-work-id, .editable-machining-date, .editable-due-date"
                    )
                    .each(function () {
                        $(this)
                            .removeClass(
                                "open-notes-modal editable-station editable-work-id editable-machining-date editable-due-date text-decoration-underline"
                            )
                            .removeAttr("data-id")
                            .removeAttr("data-notes")
                            .removeAttr("data-value")
                            .removeAttr("data-enabled")
                            .removeAttr("title")
                            .removeAttr("data-toggle")
                            .removeAttr("data-placement")
                            .removeAttr("data-bs-toggle")
                            .removeAttr("data-bs-placement");
                    });

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

                // Desactivar selects para que no disparen handlers (y aun asi se puedan leer sus valores)
                newRow.find(".location-select").each(function () {
                    $(this)
                        .removeClass("location-select")
                        .removeAttr("data-id")
                        .prop("disabled", true);
                });
                newRow.find(".status-select").each(function () {
                    $(this)
                        .removeClass("status-select")
                        .removeAttr("data-id")
                        .removeAttr("data-old")
                        .prop("disabled", true);
                });

                // Deshabilitar toggles para que no intenten actualizar nada
                newRow
                    .find(".toggle-report-btn, .toggle-source-btn")
                    .prop("disabled", true)
                    .css({ "pointer-events": "none", opacity: 0.6 });

                // Reemplazar campos para "Kit": Work ID / Part Description / WO QTY
                const workIdCell = newRow
                    .find("td")
                    .has(".editable-work-id")
                    .first();
                const workIdCellFinal = workIdCell.length
                    ? workIdCell
                    : newRow.find("td").eq(4); // fallback: columna WORK ID
                workIdCellFinal.html(
                    `<input type="text" class="form-control form-control-sm kit-work-id-input" value="">`
                );

                const partCell = newRow.find("td.part-desc-cell").first();
                if (partCell.length) {
                    partCell.html(
                        `<input type="text" class="form-control form-control-sm kit-part-desc-input" value="">`
                    );
                }

                const woQtyCell = newRow.find("td").has(".wo-qty-input").first();
                if (woQtyCell.length) {
                    woQtyCell.html(
                        `<input type="number" min="0" step="1" class="form-control form-control-sm kit-wo-qty-input" value="">`
                    );
                }

                // Notas: dejar texto simple (sin modal)
                newRow.find("td.notes-cell").each(function () {
                    $(this).html(
                        '<span class="text-muted fst-italic">Note</span>'
                    );
                });

                // Insertar la nueva fila justo debajo de la actual
                row.after(newRow);
                setTimeout(() => {
                    newRow.find("input.kit-work-id-input").trigger("focus");
                }, 0);

                let guardado = false;

                // Guardar solo al presionar Enter en input col_text_6
                newRow
                    .find("input.kit-wo-qty-input")
                    .on("keydown", function (e) {
                        if (e.key === "Enter" && !guardado) {
                            e.preventDefault(); // Evita que el Enter dispare el submit
                            e.stopPropagation(); // Evita burbujas

                            const val6 = String($(this).val() || "").trim();
                            const qtyNum = Number(val6);
                            const isValidQty =
                                Number.isFinite(qtyNum) &&
                                Number.isInteger(qtyNum) &&
                                qtyNum >= 0;
                            if (isValidQty) {
                                erpSwalFire({
                                    title: "Save Order?",
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
                                erpSwalFire({
                                    title: "Required WO QTY",
                                    text: "Enter a valid WO QTY (0 or more).",
                                    icon: "warning",
                                    confirmButtonText: "OK",
                                });
                            }
                        }
                    });

                function checkInputsAndSend() {
                    const qtyVal = String(
                        newRow.find("input.kit-wo-qty-input").val() || ""
                    ).trim();
                    const qtyNum = Number(qtyVal);
                    if (
                        !Number.isFinite(qtyNum) ||
                        !Number.isInteger(qtyNum) ||
                        qtyNum < 0
                    ) {
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
                console.error("KIT: next-id error", err);
                erpSwalFire({
                    title: "Error",
                    text: "No se pudo generar el siguiente ID. Verifica tu sesión y vuelve a intentar.",
                    icon: "error",
                    confirmButtonText: "OK",
                });
            })
            .finally(() => {
                btn.prop("disabled", false);
            });
    });

    //-----------------------------------------

    function copySelectAndInputValues(originalRow, clonedRow) {
        const $orig = $(originalRow);
        const $clone = $(clonedRow);

        // Copiar por orden de aparición para evitar selectores inválidos por clases con espacios al final
        $orig.find("select").each(function (i) {
            const $dest = $clone.find("select").eq(i);
            if ($dest.length) $dest.val($(this).val());
        });

        $orig.find("input").each(function (i) {
            const $dest = $clone.find("input").eq(i);
            if ($dest.length) $dest.val($(this).val());
        });
    }
});
