<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary Completed')
{{--
@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-clipboard-list me-2" aria-hidden="true"></i>
            FAI Summary
        </h4>

        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active" aria-current="page">FAI Summary Completed</li>
</ol>
</nav>
</div>
</div>
@endsection
--}}



@section('content')



{{-- Tabs --}}
@include('qa.faisummary.faisummary_tab')

{{-- Tab: By Active Schedules --}}

<div class="row">

    {{-- Columna izquierda: KPI / otro contenido --}}
    <div class="col-lg-3">
        <div class="row">
            {{-- Columna A: Filtros --}}
            <div class="col-md-12">
                <div class="card mb-3 sticky-top" style="top: 10px;">
                    <div class="card-header py-2">
                        <strong><i class="fas fa-filter mr-2"></i>Filters</strong>
                    </div>
                    <div class="card-body">
                        {{-- ======= TU FORMULARIO ======= --}}
                        <form method="GET" action="{{ route('faisummary.completed') }}" id="filtersForm">
                            {{-- Global Search --}}
                            <div class="form-group mb-2">
                                <label for="tableSearch" class="mb-1">Search</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>
                                    <input type="text"
                                        id="tableSearch"
                                        class="form-control"
                                        placeholder="Type to filter the table…"
                                        autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="button" id="clearTableSearch" class="btn btn-outline-secondary" title="Clear">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>


                            {{-- Location --}}
                            <div class="form-group mb-2">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-danger"></i></span>
                                    </div>
                                    <select id="locationFilter" class="form-control dt-filter" name="location">
                                        <option value="">— All —</option>
                                    </select>
                                </div>
                            </div>

                            {{-- YEAR --}}
                            <div class="form-group mb-2">
                                <label for="year">Date</label>
                                <div class="input-group input-group date" id="yearPickerWrapper"
                                    data-target-input="nearest"
                                    data-initial-year="{{ request('year') ?? '' }}"
                                    style="min-width:160px">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-calendar-alt text-success"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="year" name="year" class="form-control datetimepicker-input"
                                        data-toggle="datetimepicker" data-target="#yearPickerWrapper"
                                        value="{{ request('year') }}" placeholder="Year" autocomplete="off">
                                </div>
                            </div>

                            {{-- MONTH --}}
                            <div class="form-group mb-2">
                                <label for="monthDisplay" class="mb-1 sr-only">Month</label>
                                <div class="input-group input-group date" id="monthPickerWrapper"
                                    data-target-input="nearest" style="min-width:160px">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-calendar-alt text-danger"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="monthDisplay" class="form-control datetimepicker-input"
                                        data-toggle="datetimepicker" data-target="#monthPickerWrapper"
                                        placeholder="Month" autocomplete="off">
                                </div>
                                <input type="hidden" id="month" name="month" value="{{ request('month') }}">
                            </div>

                            {{-- DAY --}}
                            <div class="form-group mb-2">
                                <label for="day" class="mb-1 sr-only">Day</label>
                                <div class="input-group input-group date" id="dayPickerWrapper"
                                    data-target-input="nearest" style="min-width:180px">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-calendar-day text-warning"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="day" name="day" class="form-control datetimepicker-input"
                                        data-toggle="datetimepicker" data-target="#dayPickerWrapper"
                                        value="{{ request('day') ? \Carbon\Carbon::parse(request('day'))->format('Y-m-d') : '' }}"
                                        placeholder="Day" autocomplete="off">
                                </div>
                            </div>

                            {{-- Clean + Total --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <a href="{{ route('faisummary.completed') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eraser mr-1"></i> Clean
                                </a>
                                <span class="badge badge-info py-2 px-3" style="font-size: 1rem;">
                                    <i class="fas fa-list-ol mr-1"></i>
                                    Total: <span id="badgeFinished">{{ isset($orderscompleted) ? $orderscompleted->count() : 0 }}</span>

                                </span>
                            </div>

                            {{-- Quick actions --}}
                            <div class="btn-group btn-group-sm d-flex mb-2">
                                <a class="btn btn-outline-secondary flex-fill"
                                    href="{{ route('faisummary.completed', array_merge(request()->except(['day','month','year','page']), ['day'=>now()->toDateString()])) }}">
                                    <i class="fas fa-bolt mr-1"></i> Today
                                </a>
                                <a class="btn btn-outline-secondary flex-fill"
                                    href="{{ route('faisummary.completed', array_merge(request()->except(['day','page']), ['year'=>now()->year,'month'=>now()->month])) }}">
                                    <i class="far fa-calendar-alt mr-1"></i> Month
                                </a>
                                <a class="btn btn-outline-secondary flex-fill"
                                    href="{{ route('faisummary.completed', array_merge(request()->except(['day','month','page']), ['year'=>now()->year])) }}">
                                    <i class="far fa-calendar mr-1"></i> Year
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Columna B: KPI/Resumen --}}
            <div class="col-md-12">
                <div class="card mb-3 sticky-top" style="top: 10px;">
                    <div class="card-header py-2">
                        <strong><i class="fas fa-chart-bar mr-2"></i>Summary</strong>
                    </div>
                    <div class="card-body p-2">
                        {{-- KPI compactos --}}
                        <div class="info-box info-box-sm bg-light mb-2">
                            <span class="info-box-icon"><i class="fas fa-clipboard-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Inspections Completed</span>
                                <h5 class="mb-0" id="kpiTotal">0</h5>
                            </div>
                        </div>

                        <div class="info-box info-box-sm bg-secondary mb-2">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Completed</span>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0" id="kpiPass">0</h5>
                                    <small class="text-white-50">Approved</small>
                                </div>
                            </div>
                        </div>

                        <div class="info-box info-box-sm bg-info mb-2">
                            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">No Pass</span>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0" id="kpiFail">0</h5>
                                    <small class="text-white-50">Rejected</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Columna derecha: Tabla --}}
    <div class="col-lg-9">
        <div class="card mb-4">
            {{-- Header de la tabla --}}
            <div class="card-header py-2 d-flex align-items-center">
                {{-- Título a la izquierda --}}
                <div class="d-flex align-items-center mr-auto">
                    <i class="fas fa-list-alt mr-2"></i>
                    <strong class="mb-0">FAI/IPI Completed</strong>
                </div>
                {{-- Botones de exportación --}}
                <div class="btn-group" role="group" aria-label="Export Buttons">
                    <button id="btnExportExcel" type="button" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel mr-1"></i> Excel
                    </button>
                    <button id="btnExportPdf" type="button" class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf mr-1"></i> PDF
                    </button>
                </div>

                {{-- Formularios ocultos para enviar ids[] por POST --}}
                <form id="exportExcelForm" action="{{ route('faisummary.completed.export.excel') }}" method="POST" target="_blank" class="d-none">
                    @csrf
                </form>
                <form id="exportPdfForm" action="{{ route('faisummary.completed.export.pdf') }}" method="POST" target="_blank" class="d-none">
                    @csrf
                </form>
            </div>

            <div class="card-body p-2">
                <div class="table-responsive">
                    <table id="faicompleteTable" class="table table-bordered table-sm table-striped  table-sticky" style="table-layout: fixed; width: 100%;">
                        <thead class="thead-light sticky-thead">
                            <tr>
                                <th style="width: 100px;">DATE</th>
                                <th style="width: 70px;">LOC.</th>
                                <th style="width: 100px;">WORK ID</th>
                                <th style="width: 100px;">PN</th>
                                <th style="width: 200px;">DESCRIPTION</th>
                                <th style="width: 100px;">SAMP. PLAN</th>
                                <th style="width: 70px;">WO QTY</th>
                                <th style="width: 70px;">SAMP.</th>
                                <th style="width: 50px;">OPS.</th>
                                <th style="width: 40px;">FAI</th>
                                <th style="width: 40px;">IPI</th>
                                <th style="width: 100px;">PROG.</th>
                                <th style="width: 100px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderscompleted as $o)
                            @php
                            // Requerimientos
                            $faiReqPcs = (int) ($o->total_fai ?? 0); // piezas requeridas para IPI
                            $ipiReqPcs = (int) ($o->total_ipi ?? 0); // piezas requeridas para IPI

                            // Piezas aprobadas desde qa_faisummary (withSum)
                            $faiPassQty = (int) ($o->fai_pass_qty ?? 0);
                            $ipiPassQty = (int) ($o->ipi_pass_qty ?? 0);

                            // % de avance
                            $faiPct = $faiReqPcs > 0 ? min(100, (int) round(($faiPassQty / $faiReqPcs) * 100)) : 100;
                            $ipiPct = $ipiReqPcs > 0 ? min(100, (int) round(($ipiPassQty / $ipiReqPcs) * 100)) : 100;

                            // Overall = el más bajo (para que no se marque 100% si uno quedó incompleto)
                            $overall = min($faiPct, $ipiPct);

                            // Se completa cuando ambas metas se alcanzan
                            $completed = ($faiReqPcs === 0 || $faiPassQty >= $faiReqPcs)
                            && ($ipiReqPcs === 0 || $ipiPassQty >= $ipiReqPcs);

                            $barClass = $completed ? 'bg-success'
                            : ($overall >= 75 ? 'bg-info'
                            : ($overall >= 50 ? 'bg-warning' : 'bg-danger'));
                            @endphp
                            <tr id="row-{{ $o->id }}">
                                <td>
                                    {{ optional($o->inspection_endate)->format('M-d-y') }}
                                    @if($o->inspection_endate)
                                    <span class="badge badge-light">
                                        {{ $o->inspection_endate->format('H:i') }}
                                    </span>
                                    @endif
                                </td>
                                <td>{{ ucfirst($o->location) }}</td>
                                <td>{{ $o->work_id }}</td>
                                <td>{{ $o->PN }}</td>
                                <td class="td-ellipsis" title="{{ $o->Part_description }}">
                                    {{ \Illuminate\Support\Str::before($o->Part_description, ',') }}
                                </td>
                                <td class="text-center">{{ ucfirst($o->sampling_check) }}</td>
                                <td class="text-center">{{ $o->group_wo_qty }}</td>
                                <td class="text-center">{{ $o->sampling }}</td>
                                <td class="text-center">{{ $o->operation }}</td>
                                <td class="text-center">{{ $o->total_fai }}</td>
                                <td class="text-center">{{ $o->total_ipi }}</td>
                                {{-- PROGRESO desde BD --}}
                                {{-- Columna PROGRESO --}}
                                <td>
                                    <div class="progress" style="height:18px;"
                                        title="FAI {{ $faiPassQty }}/{{ $faiReqPcs }} ({{ $faiPct }}%) • IPI {{ $ipiPassQty }}/{{ $ipiReqPcs }} ({{ $ipiPct }}%)">
                                        <div class="progress-bar {{ $barClass }}" style="width: {{ $overall }}%;" aria-valuenow="{{ $overall }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ $overall }}%
                                        </div>
                                    </div>
                                    @if($completed)
                                    <span class="badge badge-success mt-1"><i class="fas fa-check"></i> Done</span>
                                    @else
                                    <small class="text-muted d-block mt-1">
                                        FAI {{ $faiPassQty }}/{{ $faiReqPcs }} • IPI {{ $ipiPassQty }}/{{ $ipiReqPcs }}
                                    </small>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="#" class="btn btn-danger btn-open-pdf"
                                            data-pdf-url="{{ route('qa.faisummary.pdf', $o->id) }}">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="{{ route('qa.faisummary.pdf', $o->id) }}?download=1" class="btn btn-info">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="#"
                                            class="btn btn-warning btn-edit-pdf"
                                            data-id="{{ $o->id }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty


                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>




</div>



<!--  {{-- Tab: By End Schedule --}}-->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    PDF Preview
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-0" style="height:80vh;">
                <embed id="pdfEmbed" src="" type="application/pdf" width="100%" height="100%">
            </div>
        </div>
    </div>
</div>



@endsection


@section('css')

@endsection


@push('js')
<script src="{{ asset('vendor/js/date-filters.js') }}"></script>
<script>
    /* ===========================
     *  Modal PDF
     * =========================== */
    $(document)
        .on('click', '.btn-open-pdf', function(e) {
            e.preventDefault();
            const url = $(this).data('pdf-url');
            $('#pdfEmbed').attr('src', url + '#zoom=page-width');
            $('#pdfModal').modal('show');
        });
    $('#pdfModal').on('hidden.bs.modal', function() {
        $('#pdfEmbed').attr('src', '');
    });

    /* ===========================
     *  DataTable + Filtros + Export
     * =========================== */
    $(function() {
        $.fn.dataTable.ext.errMode = 'throw';

        // Índices de columnas (ajusta si cambias el <thead>)
        const COLS = {
            date: 0,
            location: 1,
            work_id: 2,
            pn: 3,
            description: 4,
            samp_plan: 5,
            wo_qty: 6,
            sampling: 7,
            ops: 8,
            fai: 9,
            ipi: 10,
            prog: 11,
            action: 12
        };

        const $tbl = $('#faicompleteTable');
        if (!$tbl.length) return;

        // Destruye si existía
        if ($.fn.DataTable.isDataTable($tbl)) $tbl.DataTable().destroy();

        // Inicializa (si usas AJAX/serverSide, agrégalo aquí)
        const dt = $tbl.DataTable({
            searching: true,
            ordering: false,
            pageLength: 10,
            scrollX: false,
            autoWidth: false,
            dom: 'rtip',
            columnDefs: [{
                targets: [COLS.prog, COLS.action],
                orderable: false
            }]
            // rowId: row => 'row-' + row.id, // <- si cargas por AJAX y tu dataset tiene "id"
        });
        window.faiDT = dt; // útil para depurar en consola

        /* ---------------------------
         * Helpers
         * --------------------------- */
        const nzText = v => (typeof v === 'string' ? v : ($(v).text?.() ?? String(v ?? '')).trim());
        const uniqueSorted = arr => [...new Set(arr.map(nzText).filter(Boolean))]
            .sort((a, b) => a.localeCompare(b, undefined, {
                sensitivity: 'base'
            }));

        /* ---------------------------
         * Buscador global
         * --------------------------- */
        const $search = $('#tableSearch');
        const $clear = $('#clearTableSearch');

        $search.off('.faic').on('input.faic', function() {
            dt.search(this.value || '').page('first').draw('page');
        }).on('keydown.faic', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });

        $clear.off('.faic').on('click.faic', function() {
            $search.val('');
            dt.search('').page('first').draw('page');
            $search.trigger('focus');
        });

        /* ---------------------------
         * Filtro exacto por LOCATION (select)
         * --------------------------- */
        const FILTERS = [{
                id: 'locationFilter',
                col: COLS.location
            },
            // Puedes añadir más: { id: 'operationFilter', col: COLS.ops },
        ];

        function populateSelectFromDT(selectId, colIndex) {
            const sel = document.getElementById(selectId);
            if (!sel) return;

            // Recolecta valores de la columna (filtrados y removidos para capturar todo el universo)
            const values = dt.column(colIndex, {
                    search: 'applied'
                }).data().toArray()
                .concat(dt.column(colIndex, {
                    search: 'removed'
                }).data().toArray());
            const list = uniqueSorted(values);
            const keep = sel.value || '';

            // Deja solo "— All —" (la primera opción)
            while (sel.options.length > 1) sel.remove(1);

            const frag = document.createDocumentFragment();
            for (const v of list) {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                frag.appendChild(opt);
            }
            sel.appendChild(frag);

            if (keep && list.includes(keep)) sel.value = keep;
        }

        function bindExactFilter(selectId, colIndex) {
            const el = document.getElementById(selectId);
            if (!el) return;

            el.addEventListener('change', function() {
                if (!this.value) {
                    dt.column(colIndex).search('', true, false);
                } else {
                    const re = $.fn.dataTable.util.escapeRegex(this.value);
                    dt.column(colIndex).search('^' + re + '$', true, false);
                }
                dt.page('first').draw('page'); // MUY IMPORTANTE
            });
        }

        FILTERS.forEach(f => bindExactFilter(f.id, f.col));

        // Repobla selects al inicio y cuando cambia la búsqueda global o filtros
        function repopulateAll() {
            FILTERS.forEach(f => populateSelectFromDT(f.id, f.col));
        }
        repopulateAll();
        dt.on('search.dt', repopulateAll);

        /* ---------------------------
         * Badge: total visibles
         * --------------------------- */
        const $badge = $('#badgeFinished');

        function refreshBadge() {
            $badge.text(dt.rows({
                search: 'applied'
            }).count());
        }
        refreshBadge();
        dt.on('draw.dt search.dt page.dt', refreshBadge);

        // Fechas (opcional)
        if (window.initTempusFilters) {
            window.initTempusFilters({
                form: '#filtersForm',
                yearWrapper: '#yearPickerWrapper',
                monthWrapper: '#monthPickerWrapper',
                dayWrapper: '#dayPickerWrapper',
                yearInput: '#year',
                monthHiddenInput: '#month',
                monthDisplayInput: '#monthDisplay',
                dayInput: '#day',
                initialYear: document.querySelector('#yearPickerWrapper')?.dataset.initialYear || '',
            });
        }

        /* ---------------------------
         * Export (Excel / PDF) con filtros aplicados
         * --------------------------- */

        // Obtiene los IDs de las filas filtradas.
        function getFilteredIds() {
            // 1) Preferir IDs internos de DataTables (usan el id del <tr>)
            let ids = dt.rows({
                    search: 'applied'
                }).ids().toArray()
                .map(id => String(id).replace(/^row-/, ''));

            // 2) Fallback: leer del DOM todas las páginas si no hay ids()
            if (!ids.length) {
                const $nodes = dt.rows({
                    search: 'applied',
                    page: 'all'
                }).nodes().to$();
                ids = $nodes.map(function() {
                    const domId = this.id || ''; // ej. "row-123"
                    return domId.replace(/^row-/, ''); // -> "123"
                }).get();
            }
            return ids;
        }

        function submitExport(formId) {
            // Por si cambió un filtro justo antes del click
            dt.draw(false);

            const $form = $('#' + formId);
            // Limpia inputs previos excepto @csrf
            $form.find('input[name="ids[]"]').remove();
            $form.find('input[name="year"], input[name="month"], input[name="day"], input[name="location"]').remove();

            const ids = getFilteredIds();
            // Debug opcional:
            // console.log('Filtradas:', dt.rows({ search:'applied' }).count(), 'IDs:', ids.length);

            if (!ids.length) {
                alert('No hay filas para exportar con el filtro actual.');
                return;
            }

            // Enviar los IDs visibles con el filtro actual
            ids.forEach(id => {
                $form.append($('<input>', {
                    type: 'hidden',
                    name: 'ids[]',
                    value: id
                }));
            });

            // (Opcional) también envía los filtros de la URL actual
            $form.append($('<input>', {
                type: 'hidden',
                name: 'year',
                value: '{{ request("year") }}'
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'month',
                value: '{{ request("month") }}'
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'day',
                value: '{{ request("day") }}'
            }));
            $form.append($('<input>', {
                type: 'hidden',
                name: 'location',
                value: '{{ request("location") }}'
            }));

            $form.trigger('submit');
        }

        // Clicks de export
        $('#btnExportExcel').on('click', () => submitExport('exportExcelForm'));
        $('#btnExportPdf').on('click', () => submitExport('exportPdfForm'));
    });

    $(document).on('click', '.btn-edit-pdf', function(e) {
        e.preventDefault();

        const orderId = $(this).data('id');

        Swal.fire({
            title: '¿Move to progress?',
            text: "The inspection will change status to 'In Progress'.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Yes, Continue'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/orders-schedule/${orderId}/status-inspection`, {
                        method: 'PUT', // 👈 tu ruta es PUT, no POST
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status_inspection: 'in_progress'
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Updated!', 'Inspection moved to In Progress.', 'success');
                        } else {
                            Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Hubo un problema en el servidor', 'error');
                    });
            }
        });
    });
</script>




@endpush