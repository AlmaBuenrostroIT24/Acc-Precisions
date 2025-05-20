<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')
{{-- Colapsar u ocultar sidebar --}}
@section('classes_body', 'sidebar-collapse layout-top-nav') {{-- o 'layout-top-nav' para quitarlo completamente --}}


@section('title', 'Schedule Orders Hearst')

@section('meta') {{-- ✅ Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection


@section('content')
<div class="tab-content mt-3">
    {{-- Tab: General Schedule --}}
    <div class="tab-pane fade show active" id="byMachine">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">

                    <div class="card-body">
                        {{-- Filtros dinámicos --}}
                        <form id="upload-form" action="{{ route('schedule.orders.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                        </form>
                        @include('orders.schedule_table')
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <!-- Modal Bootstrap para editar notas -->
        @include('orders.schedule_modaltable')
        @endsection

        @section('css')
        <link rel="stylesheet" href="{{ asset('vendor/css/orders-schedule.css') }}">
        <style>
            /* Ocultar navbar */
            .main-header {
                display: none !important;
            }
        </style>
        @endsection

        @push('js')
        <script src="{{ asset('vendor/js/orders-schedule.js') }}"></script>
        <script>
            window.currentLocation = '{{ $location }}'; // Ej: 'hearst'

            const isHearst = window.currentLocation === 'hearst';

            // Opciones y etiquetas para status (reutilizable)
            const statusLabels = {
                "waitingformaterial": "Wait Material",
                "onrack": "OnRack",
                "setup": "SetUp",
                "machining": "Machining",
                "outsource": "OutSource",
                "qa": "QA",
                "deburring": "Deburring",
                "shipping": "Shipping",
                "sent": "Sent",
                "onhold": "OnHold",
                "pending": "Pending"
            };

            function updateButtonToggle(orderId, value, isReport) {
                const selector = isReport ?
                    `.toggle-report-btn[data-id="${orderId}"]` :
                    `.toggle-source-btn[data-id="${orderId}"]`;
                const btn = document.querySelector(selector);
                if (!btn) return;
                btn.dataset.value = value;
                btn.classList.toggle("btn-primary", value == 1);
                btn.classList.toggle("btn-secondary", value == 0);
                btn.querySelector("i").className = "fas " + (value == 1 ? "fa-check-circle" : "fa-times-circle");
                console.log(`🔄 Sincronizado botón ${isReport ? 'report' : 'source'} en hearst`);
            }

            function updateStation(orderId, stations) {
                const span = document.querySelector(`.editable-station[data-id="${orderId}"]`);
                if (!span) return;
                span.classList.remove("text-muted");
                span.classList.add("text-success", "fw-bold");
                span.innerText = stations.length ? stations.join(", ") : "N/A";
                console.log("🔁 Estación sincronizada para orden", orderId);
            }

            function updateStatus(data) {
                const {
                    orderId,
                    status,
                    dias_restantes,
                    alertColor,
                    alertLabel
                } = data;
                if (status.toLowerCase() === "sent") {
                    const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
                    if (row) {
                        window.table.row(row).remove().draw(false);
                        // console.log("🚫 Orden eliminada de la tabla por estatus 'sent':", orderId);
                    }
                    return; // salir sin seguir actualizando
                }
                const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
                if (!row) return;

                // Actualizar clase de fila
                row.className = row.className
                    .split(" ")
                    .filter(c => !c.startsWith("bg-status-"))
                    .concat(`bg-status-${status}`)
                    .join(" ");

                const rowIdx = window.table.row(row).index();
                const colIdx = 10;

                const optionsHtml = Object.keys(statusLabels).map(s => {
                    const selected = s.toLowerCase() === status.toLowerCase() ? "selected" : "";
                    const label = statusLabels[s.toLowerCase()] || s;
                    return `<option value="${s}" ${selected}>${label}</option>`;
                }).join("");

                const selectHtml = `
               <select class="form-control form-control-sm location-select fw-bold text-capitalize" style="font-weight: bold; color: black;"
                data-id="${orderId}" data-location="${window.currentLocation}">
                ${optionsHtml}
            </select>
        `;

                window.table.cell(rowIdx, colIdx).data(selectHtml).draw(false);

                // Celda oculta status
                const hidden = document.getElementById(`hidden-status-${orderId}`);
                if (hidden) hidden.textContent = status.toLowerCase();

                // Días restantes
                const diasTd = document.getElementById(`dias-restantes-${orderId}`);
                if (diasTd) {
                    diasTd.textContent = `${dias_restantes} días`;
                    diasTd.className =
                        dias_restantes < 0 ? "text-danger fw-bold" :
                        dias_restantes <= 2 ? "text-warning fw-bold" :
                        "text-success fw-bold";
                }

                // Alerta
                const alertaDiv = document.querySelector(`#alerta-${orderId} .progress-bar`);
                if (alertaDiv) {
                    alertaDiv.className = "progress-bar " + alertColor;
                    alertaDiv.textContent = alertLabel;
                }

                console.log("🔄 Status sincronizado para orden", orderId);
            }

            function updateNotes(orderId, notes) {
                const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
                if (!row) return;

                const shortNote = notes.length > 30 ? notes.substring(0, 30) + "..." : notes;
                const safeNotes = notes.replace(/"/g, "&quot;").trim();

                const newNotesHtml = safeNotes === "" ?
                    `<span class="open-notes-modal" data-id="${orderId}" data-notes="" style="cursor:pointer;" title="">
                <i class="fas fa-plus-circle me-1 text-muted"></i> Note</span>` :
                    `<span class="open-notes-modal" data-id="${orderId}" data-notes="${safeNotes}" style="cursor:pointer;" title="${safeNotes}">
                ${shortNote}</span>`;

                const rowIndex = window.table.row(row).index();
                window.table.cell(rowIndex, 19).data(newNotesHtml).draw(false);

                // Inicializa tooltips si usas Bootstrap 4 o 5
                if (typeof initTooltips === "function") {
                    initTooltips();
                } else if (window.bootstrap) {
                    // Bootstrap 5
                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                        new bootstrap.Tooltip(el);
                    });
                } else if (window.jQuery && $.fn.tooltip) {
                    // Bootstrap 4
                    $('[data-toggle="tooltip"]').tooltip();
                }

                $(".open-notes-modal").off("click").on("click", function() {
                    const orderId = $(this).data("id");
                    const fullNotes = $(this).data("notes") || "";
                    $("#notesOrderId").val(orderId);
                    $("#notesTextarea").val(fullNotes);
                    notesModal.show();
                });

                console.log("🔄 Nota sincronizada para orden", orderId);
            }

            function updateWorkId(orderId, workId) {
                const span = document.querySelector(`.editable-work-id[data-id="${orderId}"]`);
                if (!span) return;

                span.dataset.value = workId;
                span.textContent = workId || "Click para agregar";

                // Ajusta clases según si tiene valor o no
                if (workId && workId.trim() !== "") {
                    span.classList.remove("text-muted", "text-decoration-underline");
                    span.classList.add("text-success", "fw-bold");
                } else {
                    span.classList.add("text-muted", "text-decoration-underline");
                    span.classList.remove("text-success", "fw-bold");
                }

                //console.log(`🔄 Work ID sincronizado para orden ${orderId}`);
            }

            function updateWoQty(orderId, wo_qty) {
                const input = document.querySelector(`input.wo-qty-input[data-id="${orderId}"]`);
                if (!input) return;

                input.value = wo_qty;

                if (wo_qty && wo_qty > 0) {
                    input.classList.add("fw-bold");
                    input.style.color = "black";
                } else {
                    input.classList.remove("fw-bold");
                    input.style.color = "gray";
                }

                // console.log(`🔄 WO QTY sincronizado para orden ${orderId}:`, wo_qty);
            }

            window.addEventListener('storage', function(event) {
                if (!event.newValue) return;

                let data;
                try {
                    data = JSON.parse(event.newValue);
                } catch {
                    return;
                }

                switch (event.key) {
                    case 'location-change':
                        if (!data.location || !data.orderId) return;
                        if (!isHearst) return;

                        if (data.location === 'hearst') {
                            // Recargar toda la página porque la tabla no es AJAX
                            console.log("Cambio detectado en otra pestaña para location: hearst. Recargando página..");
                            window.location.reload();
                        } else {
                            const row = window.table?.rows().nodes().to$().filter(function() {
                                return $(this).find('.location-select').data('id') == data.orderId;
                            });
                            if (row?.length) {
                                window.table.row(row).remove().draw(false);
                                console.log(`🧹 Orden ${data.orderId} removida de vista hearst.`);
                            }
                        }
                        break;

                    case 'report-toggle':
                    case 'source-toggle':
                        updateButtonToggle(data.orderId, data.value, event.key === 'report-toggle');
                        break;

                    case 'station-change':
                        if (!isHearst) return;
                        updateStation(data.orderId, data.stations || []);
                        break;

                    case 'status-change':
                        if (!isHearst) return;
                        updateStatus(data);
                        break;

                    case 'notes-change':
                        if (!isHearst) return;
                        updateNotes(data.orderId, data.notes || "");
                        break;

                    case 'work-id-change':
                        if (!isHearst) return;
                        updateWorkId(data.orderId, data.work_id || "");
                        break;
                    case 'wo-qty-change':
                        if (!isHearst) return;
                        updateWoQty(data.orderId, data.wo_qty || 0);
                        break;

                    default:
                        break;
                }
            });
        </script>
        @endpush