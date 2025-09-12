<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Completed Orders')
@section('meta') {{-- ✅ Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-calendar-alt me-2" aria-hidden="true"></i>
            General Schedule
        </h4>

        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Orders Yarnell</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')

{{-- Tabs --}}
@include('orders.schedule_tab')

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4 shadow-sm">

            {{-- 🔹 Header: filtros + acciones + contador --}}
            <div class="card-header py-2">
                <form method="GET" action="{{ route('schedule.endyarnell') }}" id="filterForm">
                    <div class="d-flex flex-wrap align-items-end" style="gap:.5rem">

                        {{-- Customer (llenado vía JS/DataTables) --}}
                        <div class="form-group mb-0">
                            <label for="customerFilter" class="mb-1 sr-only">Customer</label>
                            <div class="input-group input-group" style="min-width:200px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-user-tag text-primary"></i>
                                    </span>
                                </div>
                                <select id="customerFilter" class="form-control dt-filter">
                                    <option value="">— All —</option>
                                </select>
                            </div>
                        </div>

                        {{-- YEAR --}}
                        <div class="form-group mb-0">
                            <label for="year" class="mb-1 sr-only">Year</label>
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

                        {{-- MONTH (display + hidden) --}}
                        <div class="form-group mb-0">
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
                        <div class="form-group mb-0">
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

                        {{-- Acciones de filtro --}}
                        <div class="form-group mb-0">
                            <a href="{{ route('schedule.endyarnell') }}" class="btn btn-secondary ml-1">Clean</a>
                        </div>

                        {{-- Acciones rápidas (a la derecha) --}}
                        <div class="btn-group btn-group ml-auto">
                            <a class="btn btn-outline-secondary"
                                href="{{ route('schedule.endyarnell', array_merge(request()->except(['day','month','year','page']), ['day'=>now()->toDateString()])) }}">
                                <i class="fas fa-bolt mr-1"></i> Today
                            </a>
                            <a class="btn btn-outline-secondary"
                                href="{{ route('schedule.endyarnell', array_merge(request()->except(['day','page']), ['year'=>now()->year,'month'=>now()->month])) }}">
                                <i class="far fa-calendar-alt mr-1"></i> This Month
                            </a>
                            <a class="btn btn-outline-secondary"
                                href="{{ route('schedule.endyarnell', array_merge(request()->except(['day','month','page']), ['year'=>now()->year])) }}">
                                <i class="far fa-calendar mr-1"></i> This Year
                            </a>
                        </div>




                        {{-- Contador --}}
                        <span class="badge badge-info ml-2">
                            Total: {{ isset($orders) && method_exists($orders,'total') ? $orders->total() : (isset($orders) ? count($orders) : 0) }}
                        </span>
                    </div>
                </form>
            </div>

            {{-- 🔹 Body: solo la tabla --}}
            <div class="card-body">
                <div class="table-responsive">
                    <table id="orders_endscheduleTable" class="table table-bordered table-striped table-sm nowrap" style="table-layout: fixed; width:100%">
                        <thead class="table-light">
                            <tr>
                                <th style="width:75px">LOCATION</th>
                                <th style="width:60px">WORK ID</th>
                                <th style="width:65px">PN</th>
                                <th style="width:110px">PART/DESCRIPTION</th>
                                <th style="width:65px">CUSTOMER</th>
                                <th style="width:65px">CO QTY</th>
                                <th style="width:65px">WO QTY</th>
                                <th style="width:55px">REPORT</th>
                                <th style="width:45px">OUT</th>
                                <th style="width:65px">DUE DATE</th>
                                <th style="width:65px">MACH DATE</th>
                                <th style="width:85px">END MACH</th>
                                <th style="width:65px">TARGET</th>
                                <th style="width:65px">NOTES</th>
                            </tr>
                        </thead>
                        <tbody id="statusTable"> @foreach($orders as $order) <tr data-status="{{ $order->status }}">
                                <td> @if ($order->last_location === 'Yarnell') <span style="color: black; font-weight: bold;">Yarnell</span> @endif <span class="badge bg-warning text-dark d-inline-flex align-items-center"> <i class="fas fa-map-marker-alt mr-1"></i>{{ $order->location }} </span> </td>
                                <td>{{ $order->work_id }}</td>
                                <td style="font-size: 14px !important; line-height: 1.1; white-space: normal; word-break: break-word;">{{ $order->PN }}</td>
                                <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">{{ $order->Part_description }}</td>
                                <td>{{ $order->costumer }}</td>
                                <td>{{ $order->qty }}</td>
                                <td>{{ $order->wo_qty }}</td>
                                <td>
                                    <span class="badge  {{ $order->report ? 'bg-primary' : 'bg-secondary' }} p-2" style="font-size:1rem;">
                                        <i class="fas {{ $order->report ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge  {{ $order->our_source ? 'bg-primary' : 'bg-secondary' }} p-2" style="font-size:1rem;">
                                        <i class="fas {{ $order->our_source ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    </span>
                                </td>
                                <td>{{ optional($order->due_date)->format('M-d-y') }}</td>
                                <td>{{ optional($order->machining_date)->format('M-d-y') }}</td>
                                <td data-order="{{ $order->endate_mach ? $order->endate_mach->format('Y-m-d H:i:s') : '' }}"> {{ $order->endate_mach ? $order->endate_mach->format('M-d-y H:i') : '' }} </td>
                                <td> @if ($order->target_mach < 0) <span class="badge bg-danger">{{ $order->target_mach }} Late</span> @elseif ($order->target_mach == 0) <span class="badge bg-success">{{ $order->target_mach }} On time</span> @elseif ($order->target_mach > 0) <span class="badge bg-info">{{ $order->target_mach}} Early</span> @else <span>-</span> {{-- En caso de que target_mach sea null --}} @endif </td>
                                <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;"> <span class="open-notes-modal" data-id="{{ $order->id }}" data-notes="{{ e($order->notes) }}" title="{{ e($order->notes) }}"> {{ Str::limit($order->notes, 30) }} </span> </td>
                            </tr> @endforeach </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>


@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tempusdominus-bootstrap-4@5.39.0/build/css/tempusdominus-bootstrap-4.min.css">

<style>

</style>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/en-au.js"></script> {{-- o en-gb, según idioma --}}
<script src="https://cdn.jsdelivr.net/npm/tempusdominus-bootstrap-4@5.39.0/build/js/tempusdominus-bootstrap-4.min.js"></script>
<script>
    $(document).ready(function() {
        // =========================
        //  DataTables
        // =========================
        const CUSTOMER_COL = 4;

        if ($('#orders_endscheduleTable').length) {
            window.table = $('#orders_endscheduleTable').DataTable({
                scrollX: false,
                autoWidth: false,
                pageLength: 25,
                order: [
                    [11, 'desc']
                ],
                columnDefs: [{
                    targets: [6, 7],
                    orderable: false
                }],
            });

            function populateCustomerFilterFromDT() {
                const sel = document.getElementById('customerFilter');
                if (!sel) return;

                const colData = window.table
                    .column(CUSTOMER_COL, {
                        search: 'applied'
                    })
                    .data().toArray()
                    .map(x => (typeof x === 'string' ? x : $(x).text()))
                    .map(s => s.trim()).filter(Boolean);

                const unique = [...new Set(colData)]
                    .sort((a, b) => a.localeCompare(b, undefined, {
                        sensitivity: 'base'
                    }));

                const current = sel.value || '';
                while (sel.options.length > 1) sel.remove(1);

                const frag = document.createDocumentFragment();
                for (const v of unique) {
                    const opt = document.createElement('option');
                    opt.value = v;
                    opt.textContent = v;
                    frag.appendChild(opt);
                }
                sel.appendChild(frag);

                if (current && unique.includes(current)) sel.value = current;
            }

            document.getElementById('customerFilter')?.addEventListener('change', function() {
                const val = this.value;
                if (!val) window.table.column(CUSTOMER_COL).search('', true, false).draw();
                else {
                    const esc = $.fn.dataTable.util.escapeRegex(val);
                    window.table.column(CUSTOMER_COL).search('^' + esc + '$', true, false).draw();
                }
            });

            populateCustomerFilterFromDT();
            window.table.on('draw', populateCustomerFilterFromDT);
        }

        // =========================
        //  Helpers Tempus Dominus
        // =========================
        function setMonthViewToYear(year) {
            if (!year || !/^\d{4}$/.test(String(year))) return;
            const view = moment({
                year: parseInt(year, 10),
                month: 0,
                day: 1
            });
            $('#monthPickerWrapper').datetimepicker('viewDate', view);
        }

        // Flags para evitar submits por cambios programáticos
        let settingYear = false;
        let settingMonth = false;
        let settingDay = false;

        /* =========================
           YEAR picker (ignora cambios al abrir; envía al cerrar)
           ========================= */
        if ($('#yearPickerWrapper').length) {
            $('#yearPickerWrapper').datetimepicker({
                format: 'YYYY',
                viewMode: 'years',
                useCurrent: false,
                keepOpen: false
            });

            const initYear = $('#yearPickerWrapper').data('initial-year') || $('#year').val();
            if (initYear) {
                settingYear = true;
                $('#yearPickerWrapper').datetimepicker('date', moment(initYear, 'YYYY'));
                settingYear = false;
            }

            let openingUntilTs = 0;
            const OPENING_GRACE_MS = 300;

            $('#yearPickerWrapper')
                .on('show.datetimepicker', function() {
                    $(this).data('dirty', false);
                    openingUntilTs = Date.now() + OPENING_GRACE_MS;
                })
                .on('change.datetimepicker', function(e) {
                    // Ignora: apertura, y cambios programáticos (p.ej., disparados por month/day)
                    if (settingYear) return;
                    if (Date.now() < openingUntilTs) return;

                    if (e.date) {
                        const yearVal = e.date.year().toString();
                        $('#year').val(yearVal);

                        // Solo limpiar DAY aquí. MONTH se limpia solo si NO hay mes seleccionado.
                        // (Así no perdemos el mes cuando lo cambiaste tú mismo)
                        if (!$('#month').val()) {
                            // limpiar month solo si no hay mes seleccionado
                            settingMonth = true;
                            $('#month').val('');
                            $('#monthDisplay').val('');
                            if ($('#monthPickerWrapper').length) $('#monthPickerWrapper').datetimepicker('clear');
                            settingMonth = false;
                        }

                        // Limpia siempre el day (para traer todo el año o todo el mes)
                        settingDay = true;
                        $('#day').val('');
                        if ($('#dayPickerWrapper').length) $('#dayPickerWrapper').datetimepicker('clear');
                        settingDay = false;

                        // Alinear vista de months al nuevo año
                        setMonthViewToYear(yearVal);
                    } else {
                        $('#year').val('');
                    }

                    $(this).data('dirty', true);
                })
                .on('hide.datetimepicker', function() {
                    if ($(this).data('dirty')) {
                        $('#filterForm').submit();
                        $(this).data('dirty', false);
                    }
                });

            // Entrada manual de año (sin submit inmediato)
            $('#year').off('input blur').on('input blur', function() {
                const y = this.value.trim();
                if (/^\d{4}$/.test(y)) {
                    setMonthViewToYear(y);
                    // no enviamos; si quieres enviar aquí, llama a submit().
                }
            });
        }

        /* =========================
           MONTH picker (visible MMM + hidden MM; envía al cerrar)
           ========================= */
        if ($('#monthPickerWrapper').length) {
            $('#monthPickerWrapper').datetimepicker({
                format: 'MMM',
                viewMode: 'months',
                useCurrent: false,
                keepOpen: false
            });

            const mmHidden = ($('#month').val() || '').toString().padStart(2, '0');
            const baseYear = ($('#year').val() ? parseInt($('#year').val(), 10) : moment().year());

            if (mmHidden && mmHidden !== '00') {
                settingMonth = true;
                const m = moment({
                    year: baseYear,
                    month: parseInt(mmHidden, 10) - 1,
                    day: 1
                });
                $('#monthPickerWrapper').datetimepicker('date', m);
                settingMonth = false;
            } else {
                $('#monthDisplay').val('');
                const y = $('#year').val();
                if (y) setMonthViewToYear(y);
            }

            // Al abrir: alinear la vista al año actual/seleccionado
            $('#monthPickerWrapper').on('show.datetimepicker', function() {
                $(this).data('dirty', false);
                const y = $('#year').val() || moment().year();
                setMonthViewToYear(y);
            });

            // Elegir mes: actualizar hidden y año (protegido), limpiar day (no enviamos aún)
            $('#monthPickerWrapper').on('change.datetimepicker', function(e) {
                if (settingMonth) return;

                if (e.date) {
                    const monthVal = e.date.format('MM'); // 01..12
                    $('#month').val(monthVal);

                    // Sincroniza año, pero protegemos el handler de YEAR
                    const y = e.date.year().toString();
                    settingYear = true;
                    $('#year').val(y);
                    $('#yearPickerWrapper').datetimepicker('date', moment(y, 'YYYY'));
                    settingYear = false;

                    // Limpiar el día para traer "todo el mes"
                    settingDay = true;
                    $('#day').val('');
                    if ($('#dayPickerWrapper').length) $('#dayPickerWrapper').datetimepicker('clear');
                    settingDay = false;
                } else {
                    $('#month').val('');
                }

                $(this).data('dirty', true);
            });

            // Al cerrar: si hubo cambio real, enviar
            $('#monthPickerWrapper').on('hide.datetimepicker', function() {
                if ($(this).data('dirty')) {
                    $('#filterForm').submit();
                    $(this).data('dirty', false);
                }
            });

            // Si cambia el Year (por el picker), re-proyecta el mes seleccionado; si no hay mes, solo vista
            $('#yearPickerWrapper').on('change.datetimepicker', function(e) {
                const selYear = e.date ? e.date.year() : ($('#year').val() ? parseInt($('#year').val(), 10) : baseYear);
                const currentMM = $('#month').val();
                if (currentMM) {
                    settingMonth = true;
                    const newDate = moment({
                        year: selYear,
                        month: parseInt(currentMM, 10) - 1,
                        day: 1
                    });
                    $('#monthPickerWrapper').datetimepicker('date', newDate);
                    settingMonth = false;
                } else {
                    setMonthViewToYear(selYear);
                }
            });
        }

        /* =========================
           DAY picker (sincronizado con año/mes; envía al cerrar)
           ========================= */
        if ($('#dayPickerWrapper').length) {
            $('#dayPickerWrapper').datetimepicker({
                format: 'YYYY-MM-DD',
                viewMode: 'days',
                useCurrent: false,
                keepOpen: false
            });

            const initDay = $('#day').val();
            if (initDay) {
                settingDay = true;
                $('#dayPickerWrapper').datetimepicker('date', moment(initDay, 'YYYY-MM-DD'));
                settingDay = false;
            } else {
                const y = $('#year').val();
                const mm = ($('#month').val() || '').toString().padStart(2, '0');
                if (y && mm && mm !== '00') {
                    const view = moment({
                        year: parseInt(y, 10),
                        month: parseInt(mm, 10) - 1,
                        day: 1
                    });
                    $('#dayPickerWrapper').datetimepicker('viewDate', view);
                }
            }

            // Al abrir: si no hay day, alinea a (year,month) o hoy
            $('#dayPickerWrapper').on('show.datetimepicker', function() {
                $(this).data('dirty', false);
                if (!$('#day').val()) {
                    const y = $('#year').val();
                    const mm = $('#month').val();
                    if (y && mm && mm !== '00') {
                        const view = moment({
                            year: parseInt(y, 10),
                            month: parseInt(mm, 10) - 1,
                            day: 1
                        });
                        $('#dayPickerWrapper').datetimepicker('viewDate', view);
                    } else {
                        $('#dayPickerWrapper').datetimepicker('viewDate', moment()); // hoy
                    }
                }
            });

            // Elegir día: sincroniza year + month (no enviamos aún)
            $('#dayPickerWrapper').on('change.datetimepicker', function(e) {
                if (settingDay) return;

                if (e.date) {
                    const d = e.date.clone();
                    $('#day').val(d.format('YYYY-MM-DD'));

                    // YEAR
                    settingYear = true;
                    const y = d.format('YYYY');
                    $('#year').val(y);
                    $('#yearPickerWrapper').datetimepicker('date', moment(y, 'YYYY'));
                    settingYear = false;

                    // MONTH (hidden + display)
                    settingMonth = true;
                    const mm = d.format('MM');
                    $('#month').val(mm);
                    $('#monthPickerWrapper').datetimepicker('date', d.clone().startOf('month'));
                    settingMonth = false;
                } else {
                    $('#day').val('');
                }

                $(this).data('dirty', true);
            });

            // Al cerrar: si hubo cambio real, enviar
            $('#dayPickerWrapper').on('hide.datetimepicker', function() {
                if ($(this).data('dirty')) {
                    $('#filterForm').submit();
                    $(this).data('dirty', false);
                }
            });

            // Si cambia Year/Month y NO hay day seleccionado → mover solo la vista del day-picker
            $('#yearPickerWrapper').on('change.datetimepicker', function(e) {
                if (!$('#day').val()) {
                    const selYear = e.date ? e.date.year() : ($('#year').val() || moment().year());
                    const mm = ($('#month').val() || '01').toString().padStart(2, '0');
                    const view = moment({
                        year: parseInt(selYear, 10),
                        month: parseInt(mm, 10) - 1,
                        day: 1
                    });
                    $('#dayPickerWrapper').datetimepicker('viewDate', view);
                }
            });
            $('#monthPickerWrapper').on('change.datetimepicker', function(e) {
                if (!$('#day').val()) {
                    const y = $('#year').val() ? parseInt($('#year').val(), 10) : moment().year();
                    const mm = $('#month').val() || (e.date ? e.date.format('MM') : '01');
                    const view = moment({
                        year: y,
                        month: parseInt(mm, 10) - 1,
                        day: 1
                    });
                    $('#dayPickerWrapper').datetimepicker('viewDate', view);
                }
            });
        }
        // =========================
        //  Autosubmit de filtros servidor (excluye .dt-filter)
        // =========================
        document
            .querySelectorAll('#filterForm select:not(.dt-filter), #filterForm input[type="date"]')
            .forEach(el => el.addEventListener('change', () => document.getElementById('filterForm').submit()));
    });
</script>


@endpush