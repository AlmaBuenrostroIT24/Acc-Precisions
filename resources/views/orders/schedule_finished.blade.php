<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Completed Orders')
@section('meta') {{-- ✅ Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')

{{-- Tabs --}}
@include('orders.schedule_tab')

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4 shadow-sm">
            {{-- Filtros dinámicos --}}
            <div class="card-body">
                <form method="GET" action="{{ route('schedule.finished') }}" id="filterForm" class="mb-2 erp-finished-filters">
                    <div class="erp-filters-layout d-flex align-items-end justify-content-between flex-wrap" style="gap:.5rem">
                        <div class="erp-filters-fields d-flex flex-wrap align-items-end erp-inline-filters" style="gap:.5rem">

                        {{-- 🔹 Location --}}
                        <div class="form-group mb-0">
                            <label for="locationFilter" class="mb-1 sr-only">Location</label>
                            <div class="input-group input-group" style="min-width:180px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                    </span>
                                </div>
                                <select name="location" id="locationFilter" class="form-control form-control-sm erp-filter-control auto-submit">
                                    <option value="">— All —</option>
                                    @foreach($locations ?? [] as $loc)
                                    <option value="{{ strtolower($loc) }}" {{ strtolower(request('location')) == strtolower($loc) ? 'selected' : '' }}>
                                        {{ $loc }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 🔹 Customer --}}
                        <div class="form-group mb-0">
                            <label for="customerFilter" class="mb-1 sr-only">Customer</label>
                            <div class="input-group input-group" style="min-width:200px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-user-tag text-primary"></i>
                                    </span>
                                </div>
                                <select id="customerFilter" class="form-control form-control-sm erp-filter-control dt-filter">
                                    <option value="">— All —</option>
                                </select>
                            </div>
                        </div>

                        {{-- 🔹 Year --}}
                        <div class="form-group mb-0">
                            <label for="year" class="mb-1 sr-only">Year</label>
                                <div class="input-group input-group date" id="yearPickerWrapper"
                                    data-target-input="nearest"
                                data-initial-year="{{ request('year') ?? ($appliedYear ?? '') }}"
                                    style="min-width:160px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-calendar-alt text-success"></i>
                                    </span>
                                </div>
                                <input type="text" id="year" name="year"
                                    class="form-control form-control-sm datetimepicker-input erp-filter-control"
                                    data-toggle="datetimepicker" data-target="#yearPickerWrapper"
                                    value="{{ request('year') ?? ($appliedYear ?? '') }}" placeholder="Year" autocomplete="off">
                            </div>
                        </div>

                        {{-- 🔹 Month (display + hidden) --}}
                        <div class="form-group mb-0">
                            <label for="monthDisplay" class="mb-1 sr-only">Month</label>
                            <div class="input-group input-group date" id="monthPickerWrapper"
                                data-target-input="nearest" style="min-width:160px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-calendar-alt text-danger"></i>
                                    </span>
                                </div>
                                <input type="text" id="monthDisplay"
                                    class="form-control form-control-sm datetimepicker-input erp-filter-control"
                                    data-toggle="datetimepicker" data-target="#monthPickerWrapper"
                                    placeholder="Month" autocomplete="off">
                            </div>
                            <input type="hidden" id="month" name="month" value="{{ request('month') }}">
                        </div>

                        {{-- 🔹 Day --}}
                        <div class="form-group mb-0">
                            <label for="day" class="mb-1 sr-only">Day</label>
                            <div class="input-group input-group date" id="dayPickerWrapper"
                                data-target-input="nearest" style="min-width:180px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-calendar-day text-warning"></i>
                                    </span>
                                </div>
                                <input type="text" id="day" name="day"
                                    class="form-control form-control-sm datetimepicker-input erp-filter-control"
                                    data-toggle="datetimepicker" data-target="#dayPickerWrapper"
                                    value="{{ request('day') ? \Carbon\Carbon::parse(request('day'))->format('Y-m-d') : '' }}"
                                    placeholder="Day" autocomplete="off">
                            </div>
                        </div>

                        {{-- 🔹 Clean --}}

                        </div>

                        <div class="erp-filters-actions d-flex flex-wrap align-items-end justify-content-end" style="gap:.5rem">

                        <a href="{{ route('schedule.finished') }}"
                            class="btn btn-erp-danger btn-sm erp-chart-btn flex-shrink-0"
                            title="Clean">
                            <i class="fas fa-eraser"></i>
                        </a>


                        {{-- 🔹 Quick actions (right aligned) --}}
                        <div class="btn-group btn-group">
                            <a class="btn btn-erp-primary btn-sm erp-chart-btn"
                                href="{{ route('schedule.finished', array_merge(request()->except(['day','month','year','page']), ['day'=>now()->toDateString()])) }}">
                                <i class="fas fa-bolt mr-1"></i> Today
                            </a>
                            <a class="btn btn-erp-primary btn-sm erp-chart-btn"
                                href="{{ route('schedule.finished', array_merge(request()->except(['day','page']), ['year'=>now()->year,'month'=>now()->month])) }}">
                                <i class="far fa-calendar-alt mr-1"></i> This Month
                            </a>
                            <a class="btn btn-erp-primary btn-sm erp-chart-btn"
                                href="{{ route('schedule.finished', array_merge(request()->except(['day','month','page']), ['year'=>now()->year])) }}">
                                <i class="far fa-calendar mr-1"></i> This Year
                            </a>
                        </div>

                        {{-- 🔹 Counter --}}


                        <span class="btn erp-chip erp-chip--purple align-self-center flex-shrink-0" style="pointer-events:none;">
                            Total <span class="erp-chip-count" id="badgeFinished">{{ isset($orders) ? count($orders) : 0 }}</span>
                        </span>
                        </div>
                    </div>
                </form>

                <div id="finishedErpToolbar" class="erp-table-toolbar d-flex align-items-center justify-content-between flex-wrap mb-2">
                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                        <div class="input-group input-group-sm erp-toolbar-search" style="width: 260px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input id="finishedGlobalSearch" type="text" class="form-control erp-filter-control" placeholder="Search..." autocomplete="off">
                        </div>

                        <div class="input-group input-group-sm" style="width: 130px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light">Rows</span>
                            </div>
                            <select id="finishedPageLength" class="form-control erp-filter-control">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>

                        <div class="erp-chip-group d-flex align-items-center flex-wrap" style="gap:.35rem">
                            <button type="button" class="btn erp-chip" data-target-filter="all">
                                All <span class="erp-chip-count" id="chipAll">0</span>
                            </button>
                            <button type="button" class="btn erp-chip erp-chip--danger" data-target-filter="late">
                                Late <span class="erp-chip-count" id="chipLate">0</span>
                            </button>
                            <button type="button" class="btn erp-chip erp-chip--success" data-target-filter="on-time">
                                On time <span class="erp-chip-count" id="chipOnTime">0</span>
                            </button>
                            <button type="button" class="btn erp-chip erp-chip--info" data-target-filter="early">
                                Early <span class="erp-chip-count" id="chipEarly">0</span>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                        <div class="btn-group">
                            <button type="button" class="btn btn-erp-primary btn-sm erp-chart-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-columns mr-1"></i> Columns
                            </button>
                            <div class="dropdown-menu dropdown-menu-right p-2" id="finishedColumnsMenu" style="min-width: 220px;"></div>
                        </div>

                        <button type="button" class="btn btn-erp-primary btn-sm erp-chart-btn" id="finishedExportCsv">
                            <i class="fas fa-file-csv mr-1"></i> Export CSV
                        </button>

                    </div>
                </div>

                <div class="table-responsive d-none" id="finishedTableWrapper">
                    {{-- Tabla --}}

                    <table id="orders_endscheduleTable" class="table table-bordered table-sm table-hover erp-table">
                        <thead class="table-light thead-custom">
                            <tr class="text-center align-middle">
                                <th class="text-center align-middle">LOCATION</th>
                                <th class="text-center align-middle">WORKID</th>
                                <th>PN</th>
                                <th style="width: 220px; ">DESCRIPTION</th>
                                <th>CUSTOMER</th>
                                <th style="width: 55px; ">CO QTY</th>
                                <th style="width: 55px; ">WO QTY</th>
                                <th class="text-center align-middle">REP</th>
                                <th class="text-center align-middle">OUT</th>
                                <th style="width: 70px; " class="text-center align-middle">DUE DATE</th>
                                <th style="width: 130px;">END DATE</th>
                                <th class="text-center align-middle">TARGET</th>
                                <th class="text-center align-middle">NOTES</th>
                                <th class="text-center align-middle" style="width: 70px;">ORD ID</th>
                                <th class="text-center align-middle" style="width: 90px;">CUST PO</th>
                                <th class="text-center align-middle">STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="statusTable">
                            @foreach($orders as $order)
                            <tr data-status="{{ $order->status }}">
                                <td data-last-location="{{ $order->last_location }}">
                                    <span class="d-block erp-location-text" style="color: black;">{{ $order->location }}</span>

                                    @if ($order->last_location === 'Yarnell')
                                    <span class="erp-pill erp-pill--warn erp-pill--sm d-inline-block mt-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i> Yarnell
                                    </span>
                                    @endif
                                </td>
                                <td>{{ $order->work_id }}</td>
                                <td>{{ $order->PN }}</td>
                                <td style="font-size: 12px;">{{ $order->Part_description }}</td>
                                <td>{{ $order->costumer }}</td>
                                <td class="text-center">{{ $order->qty }}</td>
                                <td class="text-center">{{ $order->wo_qty }}</td>
                                <td class="text-center">
                                    <span class="erp-icon-pill {{ $order->report ? 'erp-icon-pill--on' : 'erp-icon-pill--off' }}">
                                        <i class="fas {{ $order->report ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="erp-icon-pill {{ $order->our_source ? 'erp-icon-pill--on' : 'erp-icon-pill--off' }}">
                                        <i class="fas {{ $order->our_source ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    </span>
                                </td>
                                <td>{{ optional($order->due_date)->format('M-d-y') }}</td>
                                <td class="text-center editable-end-date"
                                    data-id="{{ $order->id }}"
                                    data-enddate="{{ $order->sent_at ? $order->sent_at->format('Y-m-d H:i') : '' }}"
                                    data-order="{{ $order->sent_at ? $order->sent_at->format('Y-m-d H:i:s') : '' }}">

                                    @php
                                    $endDateText = $order->sent_at
                                    ? $order->sent_at->format('M-d-y H:i')
                                    : '— Set end date —';

                                    $endDateClass = $order->was_endsentat_modified
                                    ? 'modified-end-date'
                                    : 'normal-end-date';
                                    @endphp

                                    <span class="enddate-display {{ $endDateClass }}">
                                        {{ $endDateText }}
                                    </span>

                                    {{-- Ícono para editar --}}
                                    <i class="fas fa-edit text-secondary ml-2 enddate-icon"
                                        style="cursor:pointer;"
                                        title="Edit End Date"></i>
                                </td>
                                <td class="text-center">
                                    @if ($order->target_date < 0)
                                        <span class="erp-pill erp-target-pill erp-pill--danger">{{ $order->target_date }} Late</span>
                                        @elseif ($order->target_date == 0)
                                        <span class="erp-pill erp-target-pill erp-pill--success">{{ $order->target_date }} On time</span>
                                        @elseif ($order->target_date > 0)
                                        <span class="erp-pill erp-target-pill erp-pill--info">{{ $order->target_date }} Early</span>
                                        @else
                                        <span>-</span> {{-- En caso de que target_date sea null --}}
                                        @endif
                                </td>
                                <td style="font-size: 12px;">
                                    <span class="open-notes-modal" data-id="{{ $order->id }}"
                                        data-notes="{{ e($order->notes) }}" title="{{ e($order->notes) }}">
                                        {{ Str::limit($order->notes, 130) }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $order->co }}</td>
                                <td class="text-center">{{ $order->cust_po }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">

                                        {{-- Botón existente: Return Order --}}
                                        <button class="btn btn-sm toggle-status-btn btn-erp-success erp-table-btn"
                                            title="Return Order"
                                            data-id="{{ $order->id }}"
                                            data-status="sent">
                                            <i class="fas fa-check"></i>
                                        </button>

                                        {{-- 🔹 Nuevo botón: PDF --}}
                                         @can('sched.down.pdf.log')
                                         <a href="{{ route('schedule.finished.pdf', $order->id) }}"
                                             class="btn btn-sm btn-erp-danger erp-table-btn"
                                             title="Download PDF"
                                             target="_blank">
                                             <i class="fas fa-file-pdf"></i>
                                         </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- MODAL: Edit End Date --}}
<div class="modal fade" id="endDateModal" tabindex="-1" role="dialog" aria-labelledby="endDateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <form id="endDateForm">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="endDateModalLabel">
                        <i class="fas fa-clock mr-1"></i> Edit "SEND DATE"
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-2">
                    <input type="hidden" id="endDateOrderId">

                    <div class="form-group mb-2">
                        <label for="endDateInput" class="mb-1">End Date (sent_at)</label>
                        <div class="input-group date" id="endDatePickerWrapper" data-target-input="nearest">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                            </div>
                            <input type="text"
                                id="endDateInput"
                                class="form-control datetimepicker-input"
                                data-toggle="datetimepicker"
                                data-target="#endDatePickerWrapper"
                                autocomplete="off"
                                placeholder="YYYY-MM-DD HH:mm">
                        </div>
                        <small class="text-muted d-block mt-1">
                            Leave blank to clear the date.
                        </small>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .normal-end-date {
        color: #212529;
    }

    .modified-end-date {
        color: #007bff !important;
        font-weight: 600;
    }

    .erp-location-text {
        font-size: 0.85rem;
        font-weight: 600;
    }

    .erp-finished-filters {
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(248, 250, 252, 0.75);
        padding: 10px 12px;
    }

    .erp-finished-filters .erp-filters-actions {
        margin-left: auto;
    }

    .erp-finished-filters .erp-filter-control {
        border: 1px solid #c5c9d2;
        border-radius: 8px;
        padding: 6px 10px;
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.08);
        color: #0f172a;
        font-weight: 600;
        height: 34px;
        line-height: 1.2;
    }

    .erp-finished-filters .erp-filter-control:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
        outline: none;
    }

    .erp-finished-filters .erp-chart-btn {
        height: 34px;
        border-radius: 8px;
        padding: 6px 12px;
        font-weight: 700;
    }

    .erp-finished-filters .btn-erp-primary,
    .erp-finished-filters .btn-erp-danger {
        background: #f8fafc;
        border: 1px solid #d5d8dd;
        color: #1f2937;
        border-radius: 8px;
        font-weight: 700;
        box-shadow: none;
    }

    .erp-finished-filters .btn-erp-primary i {
        color: #0b5ed7;
    }

    .erp-finished-filters .btn-erp-danger i {
        color: #b91c1c;
    }

    .erp-finished-filters .btn-erp-primary:hover,
    .erp-finished-filters .btn-erp-danger:hover {
        filter: brightness(0.97);
        color: #111827;
    }

    .erp-finished-filters .erp-total-badge {
        display: inline-flex;
        align-items: center;
        height: 34px;
        border-radius: 8px;
        padding: 6px 12px;
        font-weight: 700;
        font-size: 0.95rem;
        line-height: 1.2;
    }

    .erp-panel-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
    }

    .erp-table-toolbar .erp-filter-control {
        border: 1px solid #c5c9d2;
        border-radius: 8px;
        padding: 6px 10px;
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.08);
        color: #0f172a;
        font-weight: 600;
        height: 34px;
        line-height: 1.2;
    }

    .erp-table-toolbar .erp-filter-control:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
        outline: none;
    }

    .erp-chip {
        height: 34px;
        border-radius: 999px;
        padding: 6px 10px;
        border: 1px solid #d5d8dd;
        background: #f8fafc;
        color: #111827;
        font-weight: 800;
        font-size: 0.85rem;
        box-shadow: none;
    }

    .erp-chip .erp-chip-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 26px;
        height: 22px;
        margin-left: 6px;
        border-radius: 999px;
        padding: 0 6px;
        background: rgba(148, 163, 184, 0.20);
        color: #0f172a;
        font-weight: 900;
        font-size: 0.80rem;
    }

    .erp-chip--danger {
        border-color: rgba(239, 68, 68, 0.45);
        background: rgba(239, 68, 68, 0.10);
    }

    .erp-chip--success {
        border-color: rgba(34, 197, 94, 0.45);
        background: rgba(34, 197, 94, 0.10);
    }

    .erp-chip--info {
        border-color: rgba(14, 165, 233, 0.45);
        background: rgba(14, 165, 233, 0.10);
    }

    .erp-chip--purple {
        border-color: rgba(147, 51, 234, 0.45);
        background: rgba(147, 51, 234, 0.10);
    }

    .erp-chip--purple .erp-chip-count {
        background: rgba(147, 51, 234, 0.18);
        color: #2e1065;
    }

    .erp-chip.is-active {
        border-color: rgba(11, 94, 215, 0.55);
        box-shadow: 0 0 0 2px rgba(11, 94, 215, 0.15);
    }

    /* Tabla estilo ERP */
    #finishedTableWrapper {
        border-radius: 12px;
        border: 1.5px solid rgba(15, 23, 42, 0.14);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.94));
        padding: 10px;
    }

    /* Min-widths por columna para evitar "distorción" */
    /* Definir anchos por columna (aplica también al header clonado de DataTables) */
    #orders_endscheduleTable.erp-table {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0;
    }

    #orders_endscheduleTable.erp-table thead th {
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        color: #0f172a;
        font-weight: 800;
        font-size: 0.86rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-bottom: 1px solid #d5d8dd !important;
        vertical-align: middle;
    }

    #orders_endscheduleTable.erp-table tbody td {
        font-size: 0.96rem;
        color: #111827;
        vertical-align: middle;
    }

    #orders_endscheduleTable.erp-table tbody tr:hover {
        background: rgba(2, 6, 23, 0.04);
    }

    /* Pills / badges dentro de la tabla */
    #orders_endscheduleTable.erp-table .erp-pill {
        display: inline-flex;
        align-items: center;
        height: 28px;
        border-radius: 8px;
        padding: 4px 10px;
        border: 1px solid #d5d8dd;
        background: #f8fafc;
        color: #111827;
        font-weight: 800;
        font-size: 0.85rem;
        line-height: 1;
        white-space: nowrap;
    }

    #orders_endscheduleTable.erp-table .erp-pill--sm {
        height: 22px;
        padding: 2px 8px;
        font-size: 0.78rem;
    }

    #orders_endscheduleTable.erp-table .erp-target-pill {
        min-width: 80px;
        justify-content: center;
        text-align: center;
    }

    #orders_endscheduleTable.erp-table .erp-pill--warn {
        border-color: rgba(245, 158, 11, 0.45);
        background: rgba(245, 158, 11, 0.12);
        color: #7c2d12;
    }

    #orders_endscheduleTable.erp-table .erp-pill--danger {
        border-color: rgba(239, 68, 68, 0.45);
        background: rgba(239, 68, 68, 0.12);
        color: #7f1d1d;
    }

    #orders_endscheduleTable.erp-table .erp-pill--success {
        border-color: rgba(34, 197, 94, 0.45);
        background: rgba(34, 197, 94, 0.12);
        color: #14532d;
    }

    #orders_endscheduleTable.erp-table .erp-pill--info {
        border-color: rgba(14, 165, 233, 0.45);
        background: rgba(14, 165, 233, 0.12);
        color: #0c4a6e;
    }

    #orders_endscheduleTable.erp-table .erp-icon-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #d5d8dd;
        background: #f8fafc;
        color: #111827;
        font-size: 1rem;
    }

    #orders_endscheduleTable.erp-table .erp-icon-pill--on {
        border-color: rgba(11, 94, 215, 0.45);
        background: rgba(11, 94, 215, 0.10);
        color: #0b5ed7;
    }

    #orders_endscheduleTable.erp-table .erp-icon-pill--off {
        border-color: rgba(148, 163, 184, 0.55);
        background: rgba(148, 163, 184, 0.12);
        color: #475569;
    }

    #orders_endscheduleTable.erp-table .erp-table-btn {
        height: 30px;
        width: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    #orders_endscheduleTable.erp-table .btn-erp-success,
    #orders_endscheduleTable.erp-table .btn-erp-danger {
        background: #f8fafc;
        border: 1px solid #d5d8dd;
        color: #1f2937;
        box-shadow: none;
        font-weight: 700;
    }

    #orders_endscheduleTable.erp-table .btn-erp-success i {
        color: #0f5132;
    }

    #orders_endscheduleTable.erp-table .btn-erp-danger i {
        color: #b91c1c;
    }

    #orders_endscheduleTable.erp-table .btn-erp-success:hover,
    #orders_endscheduleTable.erp-table .btn-erp-danger:hover {
        filter: brightness(0.97);
        color: #111827;
    }

    /* Controles DataTables estilo ERP */
    #orders_endscheduleTable_wrapper .dataTables_length,
    #orders_endscheduleTable_wrapper .dataTables_filter {
        display: none;
    }

    #orders_endscheduleTable_wrapper .dataTables_length select,
    #orders_endscheduleTable_wrapper .dataTables_filter input {
        border: 1px solid #c5c9d2;
        border-radius: 8px;
        padding: 6px 10px;
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.08);
        color: #0f172a;
        font-weight: 600;
        height: 34px;
        line-height: 1.2;
    }

    #orders_endscheduleTable_wrapper .dataTables_length select:focus,
    #orders_endscheduleTable_wrapper .dataTables_filter input:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
        outline: none;
    }

    #orders_endscheduleTable_wrapper .dataTables_info {
        color: #475569;
        font-weight: 600;
    }

    #orders_endscheduleTable_wrapper .pagination .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: 1px solid #d5d8dd;
        background: #f8fafc;
        color: #1f2937;
        font-weight: 700;
        box-shadow: none;
    }

    #orders_endscheduleTable_wrapper .pagination .page-item.active .page-link {
        background: #0b5ed7;
        border-color: #0b5ed7;
        color: #fff;
    }

    #orders_endscheduleTable_wrapper .pagination .page-item.disabled .page-link {
        opacity: .6;
    }
</style>
@endsection

@push('js')
<script src="{{ asset('vendor/js/date-filters.js') }}"></script>
<script>
    $(document).ready(function() {
        const $tableElement = $('#orders_endscheduleTable');
        if (!$tableElement.length) return;

        const $wrapper = $('#finishedTableWrapper'); // 👈 el div que envuelve la tabla

        // (Opcional recomendado) Limpiar filtros ext.search previos para evitar efectos colaterales
        $.fn.dataTable.ext.search.length = 0;

        // ---------------------- 1. INICIALIZAR DATATABLE ----------------------
        const table = $tableElement.DataTable({
            dom: "rt<'d-flex align-items-center justify-content-between mt-2 flex-wrap'<'dataTables_info'i><'dataTables_paginate'p>>",
            scrollX: false,
            autoWidth: false,
            pageLength: 25,
            stateSave: true,
            stateDuration: -1,
            stateSaveCallback: function(settings, data) {
                try {
                    localStorage.setItem('scheduleFinishedTableState', JSON.stringify(data));
                } catch (e) {}
            },
            stateLoadCallback: function() {
                try {
                    return JSON.parse(localStorage.getItem('scheduleFinishedTableState') || 'null');
                } catch (e) {
                    return null;
                }
            },
            order: [
                [10, 'desc']
            ],
            columnDefs: [{
                targets: [6, 7, 11],
                orderable: false
            }],
            deferRender: true, // 👈 opcional, ayuda a que cargue más suave/rápido
            initComplete: function() {
                // 👇 aquí mostramos el contenedor una vez DataTables está listo
                $wrapper.removeClass('d-none');
            }
        });

        window.table = table;

        // ---------------------- 2. POBLAR SELECTS DE FILTRO ----------------------
        populateFilterFromColumn(0, '#locationFilter'); // columna 0: location
        populateFilterFromColumn(4, '#customerFilter'); // columna 4: customer

        // ---------------------- 3. APLICAR FILTROS COMBINADOS (client-side) ----------------------
        $('#locationFilter, #customerFilter').on("change", function() {
            table.draw();
        });

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const locationVal = $('#locationFilter').val();
            const customerVal = $('#customerFilter').val();

            const row = table.row(dataIndex).node();

            // ------------ LOCATION (columna 0) ------------
            const locationCell = $(row).find('td').eq(0);
            const location = locationCell.find('span').first().text().trim();
            const lastLocation = locationCell.data('last-location');

            let combinedLocation = location;
            if (lastLocation === 'Yarnell' && location !== 'Yarnell') {
                combinedLocation = `${location}-Yarnell`;
            }

            const locationMatch = !locationVal || combinedLocation.toLowerCase() === locationVal.toLowerCase();

            // ------------ CUSTOMER (columna 4) ------------
            const customerCell = $(row).find('td').eq(4);
            const customerText = customerCell.text().trim().toLowerCase();
            const customerMatch = !customerVal || customerText === customerVal.toLowerCase();

            return locationMatch && customerMatch;
        });

        // ---------------------- 4. FUNCIÓN PARA LLENAR SELECTS ----------------------
        function populateFilterFromColumn(columnIndex, selectId) {
            const unique = new Set();

            $('#orders_endscheduleTable tbody tr').each(function() {
                const cell = $(this).find('td').eq(columnIndex);

                if (columnIndex === 0) {
                    const location = cell.find('span').first().text().trim();
                    const lastLocation = cell.data('last-location');

                    if (location && lastLocation === 'Yarnell' && location !== 'Yarnell') {
                        unique.add(`${location}-Yarnell`);
                    } else {
                        if (location) unique.add(location);
                        if (lastLocation === 'Yarnell') unique.add('Yarnell');
                    }
                } else {
                    const value = cell.text().trim();
                    if (value) unique.add(value);
                }
            });

            const $select = $(selectId);
            $select.find('option:not(:first)').remove();

            [...unique].sort().forEach(value => {
                $select.append(`<option value="${value}">${value}</option>`);
            });
        }

        // ---------------------- 5. BOTÓN: RETURN ORDER ----------------------
        $tableElement.on('click', '.toggle-status-btn', function() {
            const btn = $(this);
            const row = btn.closest('tr');
            const orderId = btn.data('id');

            Swal.fire({
                title: '¿Return order?',
                text: 'This order will return to its previous status.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "Yes, Return",
                cancelButtonText: "No, Cancel",
                reverseButtons: true,
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.post(`/orders/${orderId}/return-previous`, {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    })
                    .done(res => {
                        if (res.success) {
                            table.row(row).remove().draw(false);
                            Swal.fire('Done', `The order returned to: ${res.newStatus}`, 'success');
                        } else {
                            Swal.fire('Attention', res.message || 'The order could not be returned.', 'warning');
                        }
                    })
                    .fail(() => {
                        Swal.fire('Error', 'Ocurrió un error al devolver la orden.', 'error');
                    });
            });
        });

        // ---------------------- 6. Tempus Dominus (reutilizable) ----------------------

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

        let targetChipFilter = 'all';

        function getTargetCategoryFromCell(cellHtml) {
            const html = (cellHtml || '').toString().toLowerCase();
            if (html.includes('late')) return 'late';
            if (html.includes('on time')) return 'on-time';
            if (html.includes('early')) return 'early';
            return 'other';
        }

        function refreshTargetChips() {
            const rows = table.rows({
                search: 'applied'
            }).data();

            let late = 0;
            let onTime = 0;
            let early = 0;
            let all = 0;

            for (let i = 0; i < rows.length; i++) {
                all++;
                const targetCell = rows[i][11]; // TARGET column
                const category = getTargetCategoryFromCell(targetCell);
                if (category === 'late') late++;
                if (category === 'on-time') onTime++;
                if (category === 'early') early++;
            }

            $('#chipAll').text(all);
            $('#chipLate').text(late);
            $('#chipOnTime').text(onTime);
            $('#chipEarly').text(early);

            $('#finishedErpToolbar .erp-chip').removeClass('is-active');
            $(`#finishedErpToolbar .erp-chip[data-target-filter="${targetChipFilter}"]`).addClass('is-active');
        }

        // Inicial
        refreshBadge();
        refreshTargetChips();

        // Mantenerlo sincronizado
        table.on('draw.dt search.dt order.dt page.dt', function() {
            refreshBadge();
            refreshTargetChips();
        });

        // Al cambiar Location/Customer ya llamas table.draw(), que dispara refreshBadge
        $('#locationFilter, #customerFilter').on('change', function() {
            table.draw();
        });

        // ---------------------- 8. TOOLBAR: SEARCH / PAGE SIZE / RESET / EXPORT / COLUMNS / CHIPS ----------------------
        const $search = $('#finishedGlobalSearch');
        const $pageLen = $('#finishedPageLength');

        $search.on('input', function() {
            table.search(this.value || '').draw();
        });

        $pageLen.on('change', function() {
            table.page.len(Number(this.value) || 25).draw();
        });

        // Restore saved page length + search (if available)
        try {
            const state = table.state.loaded();
            if (state && state.search && typeof state.search.search === 'string') {
                $search.val(state.search.search);
            }
            if (state && state.length) {
                $pageLen.val(String(state.length));
            }
        } catch (e) {}

        // Chip filter for TARGET column
        $.fn.dataTable.ext.search.push(function(settings, data) {
            if (settings.nTable && settings.nTable.id !== 'orders_endscheduleTable') return true;
            if (!targetChipFilter || targetChipFilter === 'all') return true;

            const targetHtml = (data[11] || '').toString().toLowerCase();
            if (targetChipFilter === 'late') return targetHtml.includes('late');
            if (targetChipFilter === 'on-time') return targetHtml.includes('on time');
            if (targetChipFilter === 'early') return targetHtml.includes('early');
            return true;
        });

        $('#finishedErpToolbar').on('click', '.erp-chip', function() {
            targetChipFilter = $(this).data('target-filter') || 'all';
            table.draw();
        });

        // Columns menu
        const $columnsMenu = $('#finishedColumnsMenu');
        table.columns().every(function(idx) {
            const headerText = $(this.header()).text().trim();
            if (!headerText) return;
            const checked = this.visible();
            const id = `colToggleFinished_${idx}`;
            const item = `
                <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="${id}" data-col="${idx}" ${checked ? 'checked' : ''}>
                    <label class="custom-control-label" for="${id}">${headerText}</label>
                </div>`;
            $columnsMenu.append(item);
        });

        $columnsMenu.on('change', 'input[type="checkbox"][data-col]', function() {
            const colIdx = Number($(this).data('col'));
            const visible = $(this).is(':checked');
            table.column(colIdx).visible(visible);
        });

        // Export CSV (visible columns, filtered rows)
        $('#finishedExportCsv').on('click', function() {
            const visibleCols = [];
            table.columns().every(function(idx) {
                if (this.visible()) visibleCols.push(idx);
            });

            const headers = visibleCols.map(i => $(table.column(i).header()).text().trim());
            const rows = table.rows({
                search: 'applied'
            }).data();

            const csvEscape = (value) => {
                const s = (value ?? '').toString()
                    .replace(/<[^>]*>/g, ' ')
                    .replace(/\\s+/g, ' ')
                    .trim()
                    .replace(/\"/g, '\"\"');
                return `\"${s}\"`;
            };

            const lines = [];
            lines.push(headers.map(csvEscape).join(','));
            for (let r = 0; r < rows.length; r++) {
                const line = visibleCols.map(i => csvEscape(rows[r][i])).join(',');
                lines.push(line);
            }

            const blob = new Blob([lines.join('\\n')], {
                type: 'text/csv;charset=utf-8;'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `completed-orders-${new Date().toISOString().slice(0,10)}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });

        // Hotkey: "/" focus search
        $(document).on('keydown', function(e) {
            if (e.key === '/' && !$('input,textarea,select').is(':focus')) {
                e.preventDefault();
                $search.trigger('focus');
            }
        });

        // ---------------------- 9. EDITAR END DATE (sent_at) ----------------------
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Inicializar datetimepicker del modal (reutilizando Tempus Dominus)
        $('#endDatePickerWrapper').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            icons: {
                time: 'far fa-clock',
                date: 'far fa-calendar',
                up: 'fas fa-chevron-up',
                down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right',
                today: 'far fa-calendar-check',
                clear: 'far fa-trash-alt',
                close: 'far fa-times-circle'
            }
        });

        // ⬇️ Cerrar el calendario automáticamente al elegir fecha
        $('#endDatePickerWrapper').on('change.datetimepicker', function(e) {
            if (e.date) {
                // Actualiza el input por si acaso
                $('#endDateInput').val(e.date.format('YYYY-MM-DD HH:mm'));
            }
            $('#endDatePickerWrapper').datetimepicker('hide');
        });

        // 🔹 Abrir modal al hacer clic en la celda END DATE
        $tableElement.on('click', '.enddate-icon', function() {
            const $td = $(this).closest('.editable-end-date');
            const orderId = $td.data('id');
            const currentEndDate = $td.data('enddate') || '';

            $('#endDateOrderId').val(orderId);

            // Setear valor actual en el input
            $('#endDateInput').val(currentEndDate);

            // Setear valor en el datetimepicker (si existe)
            const picker = $('#endDatePickerWrapper').data('datetimepicker');
            if (picker) {
                picker.date(currentEndDate ? moment(currentEndDate, 'YYYY-MM-DD HH:mm') : null);
            }

            // Guardar referencia al <td> en el modal (para actualizar después)
            $('#endDateModal').data('td', $td);

            // Mostrar modal
            $('#endDateModal').modal('show');
        });

        // 🔹 Guardar cambios de END DATE
        $('#endDateForm').on('submit', function(e) {
            e.preventDefault();

            const orderId = $('#endDateOrderId').val();
            const $modal = $('#endDateModal');
            const $td = $modal.data('td');

            let newEndDate = $('#endDateInput').val().trim(); // puede venir vacío

            $.ajax({
                    url: `/orders/${orderId}/update-end-date`,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        sent_at: newEndDate
                    }
                })
                .done(function(res) {
                    if (!res.success) {
                        Swal.fire('Attention', res.message || 'Could not update the end date.', 'warning');
                        return;
                    }

                    // ----- 1) Actualizar END DATE (celda clickeada) -----
                    const displayText = res.sent_at_formatted || '— Set end date —';
                    const $display = $td.find('.enddate-display'); // 👈 ahora sí definimos $display

                    $display.text(displayText);

                    // Actualizar clases según si fue modificada o no (was_endsentat_modified en BD)
                    $display.removeClass('normal-end-date modified-end-date');
                    if (res.was_modified) {
                        $display.addClass('modified-end-date'); // azul
                    } else {
                        $display.addClass('normal-end-date');
                    }

                    // Actualizar atributos para ordenamiento y para futuros clicks
                    $td.data('enddate', res.sent_at_value || '');
                    $td.attr('data-order', res.sent_at_order || '');

                    // ----- 2) Actualizar TARGET DATE (columna 11) -----
                    // 0=LOCATION, 1=WORKID, 2=PN, 3=DESC, 4=CUSTOMER, 5=CO QTY,
                    // 6=WO QTY, 7=REPORT, 8=OUT/SRC, 9=DUE, 10=END, 11=TARGET, 12=NOTES, 13=ORD ID, 14=CUST PO, 15=STATUS
                    const $row = $td.closest('tr');
                    const $targetTd = $row.find('td').eq(11); // TARGET

                    let targetHtml = '<span>-</span>';

                    if (res.target_date !== null && res.target_date !== undefined) {
                        const tdVal = Number(res.target_date);

                        if (tdVal < 0) {
                            targetHtml = `<span class="erp-pill erp-target-pill erp-pill--danger">${tdVal} Late</span>`;
                        } else if (tdVal === 0) {
                            targetHtml = `<span class="erp-pill erp-target-pill erp-pill--success">${tdVal} On time</span>`;
                        } else if (tdVal > 0) {
                            targetHtml = `<span class="erp-pill erp-target-pill erp-pill--info">${tdVal} Early</span>`;
                        }
                    }

                    $targetTd.html(targetHtml);

                    // ----- 3) Avisar a DataTables de los cambios -----
                    table.row($row).invalidate().draw(false);

                    $modal.modal('hide');
                    Swal.fire('Done', 'End Date & Target Date updated successfully.', 'success');
                })
                .fail(function() {
                    Swal.fire('Error', 'Error updating End Date.', 'error');
                });
        });
    });
</script>
@endpush
