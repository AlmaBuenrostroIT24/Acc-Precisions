<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Orders Yarnell')
@section('meta') {{-- ✅ Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')


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
                            Total: <span id="badgeFinished">{{ isset($orders) ? count($orders) : 0 }}</span>
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


<style>

</style>
@endsection

@push('js')

<script src="{{ asset('vendor/js/date-filters.js') }}"></script>
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
        //  Tempus Dominus (reutilizable)
        // =========================
        window.initTempusFilters({
            form: '#filterForm',
            yearWrapper: '#yearPickerWrapper',
            monthWrapper: '#monthPickerWrapper',
            dayWrapper: '#dayPickerWrapper',
            yearInput: '#year',
            monthHiddenInput: '#month',
            monthDisplayInput: '#monthDisplay',
            dayInput: '#day',
            initialYear: document.querySelector('#yearPickerWrapper')?.dataset.initialYear || '',
        });


        // ---------------------- 7. Autosubmit de filtros servidor (excluye .dt-filter) ----------------------
        const $badge = $('#badgeFinished');

        function refreshBadge() {
            const filtered = table.rows({
                search: 'applied'
            }).count();
            $badge.text(filtered);
        }

        // Inicial
        refreshBadge();

        // Mantenerlo sincronizado
        table.on('draw.dt search.dt order.dt page.dt', refreshBadge);

        // Al cambiar Location/Customer ya llamas table.draw(), que dispara refreshBadge
        $('#customerFilter').on('change', function() {
            table.draw();
        });
    });
</script>


@endpush
