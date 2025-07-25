window.addEventListener("DOMContentLoaded", () => {
    const currentLocation = window.currentLocation;
    const validLocations = ["hearst", "yarnell","workhearst"];
    if (!validLocations.includes(currentLocation)) return;

    const statusLabels = {
        waitingformaterial: "Wait Material",
        cutmaterial: "Cut Material",
        grinding: "Grinding",
        onrack: "OnRack",
        programming: "Programming",
        setup: "SetUp",
        machining: "Machining",
        marking: "Marking",
        deburring: "Deburring",
        qa: "QA",
        outsource: "OutSource",
        assembly: "Assembly",
        shipping: "Shipping",
        sent: "Sent",
        onhold: "OnHold",
        pending: "Pending",
    };

    //🔁 formatShortDate(dateStr) Convierte una fecha en formato YYYY-MM-DD a un formato corto como "Jul-08-25"
    function formatShortDate(dateStr) {
        const [year, month, day] = dateStr.split("-");
        const dateObj = new Date(`${year}-${month}-${day}T12:00:00`);
        const shortMonth = dateObj.toLocaleString("en-US", { month: "short" });
        return `${shortMonth}-${day.padStart(2, "0")}-${year.slice(-2)}`;
    }

    //🔄 updateButtonToggle(orderId, value, isReport)
    //Actualiza el botón de "source" o "report" (fa-check-circle / fa-times-circle) visualmente, y cambia el color del botón según el valor (1 o 0).
    function updateButtonToggle(orderId, value, isReport) {
        const selector = isReport
            ? `.toggle-report-btn[data-id="${orderId}"]`
            : `.toggle-source-btn[data-id="${orderId}"]`;
        const btn = document.querySelector(selector);
        if (!btn) return;
        btn.dataset.value = value;
        btn.classList.toggle("btn-primary", value == 1);
        btn.classList.toggle("btn-secondary", value == 0);
        btn.querySelector("i").className =
            "fas " + (value == 1 ? "fa-check-circle" : "fa-times-circle");
    }

    //🔁 updateStation(orderId, stations)
    //Actualiza el texto y estilo del campo de estación en la fila de la orden.
    // Si una orden pasa de "M1" a "M2, M3", este método actualiza el <span> para mostrar "M2, M3" en color verde y negrita.
    function updateStation(orderId, stations) {
        const span = document.querySelector(
            `.editable-station[data-id="${orderId}"]`
        );
        if (!span) return;
        span.classList.remove("text-muted");
        span.classList.add("text-success", "fw-bold");
        span.innerText = stations.length ? stations.join(", ") : "N/A";
    }

    //🔁 updateDiasYAlerta(orderId, dias_restantes, alertColor, alertLabel)
    // Actualiza la columna de días restantes y la barra de alerta de una orden.
    // Aplica clases como text-danger, text-warning, text-success. Cambia el color y texto de la barra de progreso (alertColor, alertLabel)
    function updateDiasYAlerta(
        orderId,
        dias_restantes,
        alertColor,
        alertLabel
    ) {
        const diasTd = document.getElementById(`dias-restantes-${orderId}`);
        if (diasTd) {
            diasTd.textContent = `${dias_restantes} days`;
            diasTd.className =
                dias_restantes < 0
                    ? "text-danger fw-bold"
                    : dias_restantes <= 2
                    ? "text-warning fw-bold"
                    : "text-success fw-bold";
        }
        const alertaDiv = document.querySelector(
            `#alerta-${orderId} .progress-bar`
        );
        if (alertaDiv) {
            alertaDiv.className = "progress-bar " + alertColor;
            alertaDiv.textContent = alertLabel;
        }
    }

    //🔁 updateStatus(data)
    // Actualiza completamente el estado de una orden: Si el estado es sent, elimina la fila de la tabla
    //1. Aplica clase de color a la fila (bg-status-xyz). 2. Reemplaza el select de estado. 3. Actualiza el valor oculto. 4. Llama a updateDiasYAlerta.
    function updateStatus(data) {
        const { orderId, status, dias_restantes, alertColor, alertLabel } =
            data;
        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (!row) return;

        if (status.toLowerCase() === "sent") {
            window.table.row(row).remove().draw(false);
            return;
        }
        applyRowLateStyle(orderId, dias_restantes, status);
        const rowIdx = window.table.row(row).index();

        // ✅ Actualizar <select> en columna 10 (location)
        const colIdx = 10;
        const optionsHtml = Object.keys(statusLabels)
            .map((s) => {
                const selected =
                    s.toLowerCase() === status.toLowerCase() ? "selected" : "";
                const label = statusLabels[s.toLowerCase()] || s;
                return `<option value="${s}" ${selected}>${label}</option>`;
            })
            .join("");
        const selectHtml = `
        <select class="form-control form-control-sm location-select fw-bold text-capitalize" style="font-weight: bold; color: black;"
            data-id="${orderId}" data-location="${window.currentLocation}">
            ${optionsHtml}
        </select>`;
        window.table.cell(rowIdx, colIdx).data(selectHtml).draw(false);

        // ✅ Actualizar estado oculto
        const hidden = document.getElementById(`hidden-status-${orderId}`);
        if (hidden) hidden.textContent = status.toLowerCase();

        // ✅ Actualizar alerta visual
        updateDiasYAlerta(orderId, dias_restantes, alertColor, alertLabel);
    }

    function applyRowLateStyle(orderId, dias, status) {
        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (!row) {
            // console.warn(`❌ No se encontró la fila para orderId=${orderId}`);
            return;
        }
        // Debug: ver qué datos estamos usando
        // console.log("🔍 applyRowLateStyle", {orderId,dias, status, rowClassList: row.className, });
        // Limpiar clases previas de estado y de late
        row.className = row.className
            .split(" ")
            .filter((c) => !c.startsWith("bg-status-") && c !== "row-late")
            .join(" ");
        if (dias < 0) {
            row.classList.add("row-late");
            // console.log(`🔴 Orden ${orderId} marcada como LATE`);
        } else if (status) {
            row.classList.add(`bg-status-${status.toLowerCase()}`);
            // console.log(`🟢 Orden ${orderId} con clase: bg-status-${status.toLowerCase()}`);
        } else {
            console.log(
                `⚠️ No se aplicó clase de estado a la orden ${orderId}`
            );
        }
    }

    //🔁 updateNotes(orderId, notes)
    //Actualiza el contenido de las notas: 1. Muestra el texto corto o el ícono de "Note".
    //2. Agrega evento para abrir el modal con el texto completo al hacer clic. 3. Limpia y escapa caracteres especiales (").
    function updateNotes(orderId, notes) {
        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (!row) return;

        const shortNote =
            notes.length > 30 ? notes.substring(0, 30) + "..." : notes;
        const safeNotes = notes.replace(/"/g, "&quot;").trim();

        const newNotesHtml =
            safeNotes === ""
                ? `<span class="open-notes-modal" data-id="${orderId}" data-notes="" style="cursor:pointer;" title="">
                <i class="fas fa-plus-circle me-1 text-muted"></i> Note</span>`
                : `<span class="open-notes-modal" data-id="${orderId}" data-notes="${safeNotes}" style="cursor:pointer;" title="${safeNotes}">
                ${shortNote}</span>`;

        const rowIndex = window.table.row(row).index();
        window.table.cell(rowIndex, 19).data(newNotesHtml).draw(false);
        $(".open-notes-modal")
            .off("click")
            .on("click", function () {
                const orderId = $(this).data("id");
                const fullNotes = $(this).data("notes") || "";
                $("#notesOrderId").val(orderId);
                $("#notesTextarea").val(fullNotes);
                notesModal.show();
            });
    }

    //🔢 updateWorkId(orderId, workId)
    //Actualiza visualmente el Work ID: 1. Muestra el nuevo valor o el texto “Click para agregar”.
    // 2. Aplica estilos dependiendo si hay valor (text-success, text-muted, etc.).
    function updateWorkId(orderId, workId) {
        const span = document.querySelector(
            `.editable-work-id[data-id="${orderId}"]`
        );
        if (!span) return;
        span.dataset.value = workId;
        span.textContent = workId || "Click para agregar";
        if (workId && workId.trim() !== "") {
            span.classList.remove("text-muted", "text-decoration-underline");
            span.classList.add("text-success", "fw-bold");
        } else {
            span.classList.add("text-muted", "text-decoration-underline");
            span.classList.remove("text-success", "fw-bold");
        }
    }

    // 🔄 updateWoQty(orderId, wo_qty)
    //Actualiza la cantidad de WO Qty en el <input>: Establece el nuevo valor. Pone el texto en negro si hay valor, o gris si es cero o vacío.
    function updateWoQty(orderId, wo_qty) {
        const input = document.querySelector(
            `input.wo-qty-input[data-id="${orderId}"]`
        );
        if (!input) return;
        input.value = wo_qty;
        input.classList.toggle("fw-bold", wo_qty > 0);
        input.style.color = wo_qty > 0 ? "black" : "gray";
    }

    //🔄window.addEventListener("storage", ...)
    //Escucha los cambios del localStorage que ocurren desde otras pestañas o ventanas del navegador, y sincroniza los datos de forma en tiempo real.
    //Maneja distintos event.key:
    window.addEventListener("storage", (event) => {
        if (!event.newValue) return;
        let data;
        try {
            data = JSON.parse(event.newValue);
        } catch {
            return;
        }
        const keysRequiringUpdate = [
            "station-change",
            "status-change",
            "notes-change",
            "work-id-change",
            "wo-qty-change",
            "date-machining-change",
        ];

        if (event.key === "location-change") {
            if (data.location === currentLocation) {
                window.location.reload();
            } else {
                const row = window.table
                    ?.rows()
                    .nodes()
                    .to$()
                    .filter(function () {
                        return (
                            $(this).find(".location-select").data("id") ==
                            data.orderId
                        );
                    });
                if (row?.length) window.table.row(row).remove().draw(false);
            }
        } else if (["report-toggle", "source-toggle"].includes(event.key)) {
            updateButtonToggle(
                data.orderId,
                data.value,
                event.key === "report-toggle"
            );
            applyRowLateStyle(data.orderId, data.dias_restantes, ""); // El status no cambia, así que pasa string vacío
            const span = document.querySelector(
                `.editable-machining-date[data-id="${data.orderId}"]`
            );
            if (span) {
                const isEnabled = data.value === 1;
                span.dataset.enabled = isEnabled ? "1" : "0";
                span.style.cursor = isEnabled ? "pointer" : "default";
                span.classList.toggle("text-decoration-underline", isEnabled);
            }
        } else if (currentLocation && keysRequiringUpdate.includes(event.key)) {
            switch (event.key) {
                case "station-change":
                    updateStation(data.orderId, data.stations || []);
                    break;
                case "status-change":
                    updateStatus(data);
                    applyRowLateStyle(
                        data.orderId,
                        data.dias_restantes,
                        data.status
                    );
                    break;
                case "notes-change":
                    updateNotes(data.orderId, data.notes || "");
                    break;
                case "work-id-change":
                    updateWorkId(data.orderId, data.work_id || "");
                    break;
                case "wo-qty-change":
                    updateWoQty(data.orderId, data.wo_qty || 0);
                    break;
                case "date-machining-change":
                    const span = document.querySelector(
                        `.editable-machining-date[data-id="${data.orderId}"]`
                    );
                    const dias = data.dias_restantes;
                    let status = (data.status || "").trim().toLowerCase(); // ✅ preferencia al status sincronizado

                    if (!status) {
                        const statusHidden = document.getElementById(
                            `hidden-status-${data.orderId}`
                        );
                        status = statusHidden
                            ? statusHidden.textContent.trim()
                            : "";
                    }
                    if (span) {
                        span.dataset.value = data.machining_date;
                        span.innerText = formatShortDate(data.machining_date);
                    }
                    updateDiasYAlerta(
                        data.orderId,
                        data.dias_restantes,
                        data.alertColor,
                        data.alertLabel
                    );
                    // Aplica el estilo correcto usando el estado actual
                    applyRowLateStyle(data.orderId, dias, status);
                    break;
            }
        }
    });
    //------------------------------------------------------------------------------------------------------
    //-------------------------SOLUCIÓN para sincronizar entre PCs SIN usar AJAX:----------------------------

    let lastServerUpdate = null;

    setInterval(() => {
        fetch("/api/schedule-last-update")
            .then((res) => res.json())
            .then((data) => {
                if (!lastServerUpdate) {
                    lastServerUpdate = data.updated_at;
                } else if (data.updated_at !== lastServerUpdate) {
                    //console.log('🟡 Cambio detectado, recargando...');
                    location.reload(); // recarga la vista completa
                }
            })
            .catch((err) =>
                console.error("Error verificando actualizaciones:", err)
            );
    }, 2000); // cada 3segundos
    //------------------------------------------------------------------------------------------------------
});
