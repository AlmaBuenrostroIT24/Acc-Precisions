@extends('adminlte::page')

@section('title', 'Orders Yarnell')
@section('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            @php
                $dayValue = '';
                $yearValue = (string) (request('year') ?? '');
                $monthValue = (string) (request('month') ?? '');
                $today = now()->toDateString();
                $isTodayQuick = (request('day') && request('day') === $today);
                $isThisMonthQuick = (!request('day') && request('year') && request('month') && (int) request('year') === (int) now()->year && (int) request('month') === (int) now()->month);
                try {
                    if (request('day')) {
                        $dayValue = \Carbon\Carbon::parse(request('day'))->format('Y-m-d');
                    }
                } catch (\Throwable $e) {
                    $dayValue = '';
                }

                if ($yearValue === '' && $dayValue !== '') {
                    $yearValue = substr($dayValue, 0, 4);
                }

                if ($yearValue === '' && request('month')) {
                    $yearValue = (string) now()->year;
                }

                // Default (igual que Completed Orders): mostrar año actual en UI.
                // Nota: no fijamos mes; el backend ya limita por defecto al mes actual.
                $hasAnyDateFilter = ($yearValue !== '') || ($monthValue !== '') || ($dayValue !== '');
                if (!$hasAnyDateFilter) {
                    $yearValue = (string) now()->year;
                }
            @endphp
            <form method="GET" action="{{ route('schedule.endyarnell') }}" id="filterForm" class="mb-2 erp-yarnell-filters">
                <div class="erp-filters-layout d-flex align-items-end justify-content-between flex-wrap" style="gap:.5rem">
                    <div class="erp-filters-fields d-flex flex-wrap align-items-end" style="gap:.5rem">
                        {{-- Customer (DataTables) --}}
                        <div class="form-group mb-0">
                            <label for="customerFilter" class="mb-1 sr-only">Customer</label>
                            <div class="input-group input-group" style="min-width:200px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-user-tag text-primary"></i>
                                    </span>
                                </div>
                                <select id="customerFilter" class="form-control form-control-sm erp-filter-control dt-filter">
                                    <option value="">-- All --</option>
                                </select>
                            </div>
                        </div>

                        {{-- Status (server-side) --}}
                        <div class="form-group mb-0">
                            <label for="statusFilter" class="mb-1 sr-only">Status</label>
                            <div class="input-group input-group" style="min-width:190px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-tasks text-info"></i>
                                    </span>
                                </div>
                                <select id="statusFilter" class="form-control form-control-sm erp-filter-control dt-filter">
                                    <option value="">-- All --</option>
                                </select>
                            </div>
                            <input type="hidden" name="status" id="statusHidden" value="{{ request('status') }}">
                        </div>

                        {{-- Year --}}
                        <div class="form-group mb-0">
                            <label for="year" class="mb-1 sr-only">Year</label>
                            <div class="input-group input-group date" id="yearPickerWrapper"
                                data-target-input="nearest"
                                data-initial-year="{{ $yearValue }}"
                                style="min-width:160px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-calendar-alt text-success"></i>
                                    </span>
                                </div>
                                <input type="text" id="year" name="year"
                                    class="form-control form-control-sm datetimepicker-input erp-filter-control"
                                    data-toggle="datetimepicker" data-target="#yearPickerWrapper"
                                    value="{{ $yearValue }}"
                                    placeholder="Year" autocomplete="off">
                            </div>
                        </div>

                        {{-- Month (display + hidden) --}}
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
                            <input type="hidden" id="month" name="month" value="{{ $monthValue }}">
                        </div>

                        {{-- Day --}}
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
                                    value="{{ $dayValue }}"
                                    placeholder="Day" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="erp-filters-actions d-flex flex-wrap align-items-end justify-content-end" style="gap:.5rem">
                        <a href="{{ route('schedule.endyarnell') }}"
                            class="btn btn-erp-danger btn-sm erp-chart-btn flex-shrink-0"
                            title="Clean">
                            <i class="fas fa-eraser"></i>
                        </a>

                        <div class="btn-group btn-group">
                            <a class="btn btn-erp-primary btn-sm erp-chart-btn {{ $isTodayQuick ? 'is-active' : '' }}"
                                href="{{ route('schedule.endyarnell', array_merge(request()->except(['day','month','year','page']), ['day'=>now()->toDateString()])) }}">
                                <i class="fas fa-bolt mr-1"></i> Today
                            </a>
                            <a class="btn btn-erp-primary btn-sm erp-chart-btn {{ $isThisMonthQuick ? 'is-active' : '' }}"
                                href="{{ route('schedule.endyarnell', array_merge(request()->except(['day','page']), ['year'=>now()->year,'month'=>now()->month])) }}">
                                <i class="far fa-calendar-alt mr-1"></i> This Month
                            </a>
                        </div>

                        <span class="btn erp-chip erp-chip--purple align-self-center flex-shrink-0" style="pointer-events:none;">
                            Total <span class="erp-chip-count" id="badgeEndyarnell">{{ isset($orders) ? count($orders) : 0 }}</span>
                        </span>
                    </div>
                </div>
            </form>

            <div class="card mb-4 shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <div id="endyarnellErpToolbar" class="erp-table-toolbar d-flex align-items-center justify-content-between flex-wrap mb-0">
                        <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                            <div class="input-group input-group-sm" style="width: 130px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">Rows</span>
                                </div>
                                <select id="endyarnellPageLength" class="form-control erp-filter-control">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <div class="btn-group">
                                <button type="button" class="btn btn-erp-primary btn-sm erp-chart-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-columns mr-1"></i> Columns
                                </button>
                                <div class="dropdown-menu dropdown-menu-right p-2" id="endyarnellColumnsMenu" style="min-width: 170px;"></div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                            <div class="erp-chip-group d-flex align-items-center flex-wrap" style="gap:.35rem">
                                <button type="button" class="btn erp-chip erp-chip--info" data-status-chip="shipping">
                                    Shipping <span class="erp-chip-count" id="chipShipping">0</span>
                                </button>
                                <button type="button" class="btn erp-chip erp-chip--warn" data-status-chip="deburring">
                                    Deburring <span class="erp-chip-count" id="chipDeburring">0</span>
                                </button>
                            </div>
                            <div class="erp-toolbar-search" style="width: 260px;">
                                <input id="endyarnellGlobalSearch" type="search" class="form-control erp-stats-search" placeholder="Search..." autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive d-none" id="endyarnellTableWrapper">
                        <table id="orders_endscheduleTable" class="table table-bordered table-sm table-hover erp-table" style="table-layout: fixed; width: 100%;">
                            <thead class="table-light thead-custom">
                                <tr class="text-center align-middle">
                                    <th class="text-center align-middle" style="width:65px">LOC</th>
                                    <th class="text-center align-middle" style="width:80px">WORK ID</th>
                                    <th style="width:90px">PN</th>
                                    <th style="width:250px">DESCRIPTION</th>
                                    <th style="width:100px">CUSTOMER</th>
                                    <th style="width:110px" class="text-center">STATUS</th>
                                    <th style="width:60px" class="text-center">QTY</th>
                                    <th style="width:70px" class="text-center">WO QTY</th>
                                    <th style="width:30px" class="text-center">REP</th>
                                    <th style="width:30px" class="text-center">OUT</th>
                                    <th style="width:70px" class="text-center">DUE</th>
                                    <th style="width:90px" class="text-center">MACH</th>
                                    <th style="width:125px" class="text-center">END MACH</th>
                                    <th style="width:95px" class="text-center">TARGET</th>
                                    <th style="width:220px">NOTES</th>
                                </tr>
                            </thead>
                            <tbody id="statusTable">
                                @foreach($orders as $order)
                                    <tr data-status="{{ $order->status }}">
                                        <td data-last-location="{{ $order->last_location }}">
                                            <span class="d-block erp-location-text" style="color:#0f172a;">{{ $order->location }}</span>
                                            @if (($order->last_location ?? '') === 'Yarnell')
                                                <span class="erp-pill erp-pill--warn erp-pill--sm d-inline-block mt-1">
                                                    <i class="fas fa-map-marker-alt mr-1"></i> Yarnell
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">{{ $order->work_id }}</td>
                                        <td class="erp-cell-wrap">{{ $order->PN }}</td>
                                        <td class="erp-cell-wrap" style="font-size: 12px;">{{ $order->Part_description }}</td>
                                        <td class="erp-cell-wrap">{{ $order->costumer }}</td>
                                        <td class="text-center">
                                            @php
                                                $st = strtolower(trim((string) ($order->status ?? '')));
                                                $statusClass = 'erp-pill--info';
                                                if ($st === 'shipping') $statusClass = 'erp-pill--info';
                                                else if (in_array($st, ['sent', 'ready'], true)) $statusClass = 'erp-pill--success';
                                                else if (in_array($st, ['onhold', 'late', 'overdue'], true)) $statusClass = 'erp-pill--danger';
                                                else if (in_array($st, ['pending', 'waitingformaterial', 'deburring', 'qa', 'assembly'], true)) $statusClass = 'erp-pill--warn';
                                            @endphp
                                            <span class="erp-pill erp-pill--sm {{ $statusClass }}" title="{{ $order->status }}">
                                                {{ ucfirst($order->status ?? '-') }}
                                            </span>
                                        </td>
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
                                        <td class="text-center text-nowrap"
                                            data-order="{{ $order->due_date ? $order->due_date->format('Y-m-d') : '' }}">
                                            {{ $order->due_date ? $order->due_date->format('M-d-y') : '-' }}
                                        </td>
                                        <td class="text-center text-nowrap"
                                            data-order="{{ $order->machining_date ? $order->machining_date->format('Y-m-d') : '' }}">
                                            {{ $order->machining_date ? $order->machining_date->format('M-d-y') : '-' }}
                                        </td>
                                        <td class="text-center text-nowrap" data-order="{{ $order->endate_mach ? $order->endate_mach->format('Y-m-d H:i:s') : '' }}">
                                            {{ $order->endate_mach ? $order->endate_mach->format('M-d-y H:i') : '' }}
                                        </td>
                                        <td class="text-center">
                                            @if ($order->target_mach < 0)
                                                <span class="erp-pill erp-target-pill erp-pill--danger">{{ $order->target_mach }} Late</span>
                                            @elseif ($order->target_mach == 0)
                                                <span class="erp-pill erp-target-pill erp-pill--success">{{ $order->target_mach }} On time</span>
                                            @elseif ($order->target_mach > 0)
                                                <span class="erp-pill erp-target-pill erp-pill--info">{{ $order->target_mach }} Early</span>
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>
                                        <td class="erp-cell-wrap" style="font-size: 12px;">
                                            <span class="open-notes-modal" data-id="{{ $order->id }}" data-notes="{{ e($order->notes) }}" title="{{ e($order->notes) }}">
                                                {{ Str::limit($order->notes, 120) }}
                                            </span>
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
@endsection

@section('css')
<style>
    .erp-location-text {
        font-size: 0.85rem;
        font-weight: 600;
    }

    .erp-yarnell-filters {
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(248, 250, 252, 0.75);
        padding: 10px 12px;
    }

    .erp-yarnell-filters .erp-filter-control {
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

    .erp-yarnell-filters .input-group-text {
        height: 34px;
        border: 1px solid #c5c9d2;
        border-right: 0;
        border-radius: 10px 0 0 10px;
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%) !important;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.06);
        color: #0f172a;
    }

    .erp-yarnell-filters .input-group > .erp-filter-control {
        border-left: 0;
        border-radius: 0 10px 10px 0;
    }

    .erp-yarnell-filters select.erp-filter-control {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding-right: 34px;
        background-image:
            linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%),
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%23475569' d='M5.5 7.5 10 12l4.5-4.5 1.4 1.4L10 14.8 4.1 8.9z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 0 0, right 10px center;
        background-size: auto, 14px 14px;
    }

    .erp-yarnell-filters .erp-filter-control:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
        outline: none;
    }

    .erp-yarnell-filters .erp-chart-btn {
        height: 34px;
        border-radius: 8px;
        padding: 6px 12px;
        font-weight: 700;
    }

    .erp-yarnell-filters .btn-erp-primary,
    .erp-yarnell-filters .btn-erp-danger {
        background: #f8fafc;
        border: 1px solid #d5d8dd;
        color: #1f2937;
        border-radius: 8px;
        font-weight: 700;
        box-shadow: none;
    }

    .erp-yarnell-filters .btn-erp-primary i {
        color: #0b5ed7;
    }

    .erp-yarnell-filters .btn-erp-danger i {
        color: #b91c1c;
    }

    .erp-yarnell-filters .btn-erp-primary.is-active {
        background: #0b5ed7;
        border-color: #0b5ed7;
        color: #fff;
        box-shadow: 0 0 0 2px rgba(11, 94, 215, 0.15);
    }

    .erp-yarnell-filters .btn-erp-primary.is-active i {
        color: #fff;
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

    .erp-table-toolbar .input-group-text {
        height: 34px;
        border: 1px solid #c5c9d2;
        border-right: 0;
        border-radius: 10px 0 0 10px;
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%) !important;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.06);
        color: #0f172a;
        font-weight: 800;
        font-size: 0.78rem;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .erp-table-toolbar .input-group > .erp-filter-control {
        border-left: 0;
        border-radius: 0 10px 10px 0;
    }

    .erp-table-toolbar select.erp-filter-control {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding-right: 34px;
        background-image:
            linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%),
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='%23475569' d='M5.5 7.5 10 12l4.5-4.5 1.4 1.4L10 14.8 4.1 8.9z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 0 0, right 10px center;
        background-size: auto, 14px 14px;
    }

    .erp-table-toolbar .erp-stats-search {
        height: 34px;
        border-radius: 10px;
        border: 1px solid #d5d8dd;
        padding: 6px 10px;
        background: #fff;
        box-shadow: none;
        color: #0f172a;
        font-weight: 600;
        line-height: 1.2;
    }

    .erp-table-toolbar .erp-chart-btn {
        height: 34px;
        border-radius: 10px;
        padding: 6px 12px;
        font-weight: 800;
    }

    .erp-table-toolbar .btn-erp-primary {
        background: #f8fafc;
        border: 1px solid #d5d8dd;
        color: #1f2937;
        box-shadow: none;
    }

    #endyarnellColumnsMenu {
        border-radius: 12px;
        border: 1px solid #d5d8dd;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16);
        padding: 5px 6px;
        max-height: 320px;
        overflow: auto;
    }

    #endyarnellColumnsMenu .custom-control {
        padding: 4px 6px 4px 1.85rem;
        margin: 0;
        border-radius: 9px;
    }

    #endyarnellColumnsMenu .custom-control:hover {
        background: #f1f5f9;
    }

    #endyarnellColumnsMenu .custom-control-label {
        color: #0f172a;
        font-weight: 700;
        font-size: 0.78rem;
        cursor: pointer;
    }

    #endyarnellColumnsMenu .custom-control-label::before {
        border-radius: 7px;
        border: 1px solid #d5d8dd;
        background: #f8fafc;
        box-shadow: none;
    }

    #endyarnellColumnsMenu .custom-control-input:checked~.custom-control-label::before {
        background: #0b5ed7;
        border-color: #0b5ed7;
    }

    #endyarnellTableWrapper {
        border-radius: 12px;
        border: none;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.94));
        padding: 2px 10px 0;
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

    .erp-chip--purple {
        border-color: rgba(147, 51, 234, 0.45);
        background: rgba(147, 51, 234, 0.10);
    }

    .erp-chip--warn {
        border-color: rgba(245, 158, 11, 0.45);
        background: rgba(245, 158, 11, 0.10);
    }

    .erp-chip--info {
        border-color: rgba(14, 165, 233, 0.45);
        background: rgba(14, 165, 233, 0.10);
    }

    .erp-chip--purple .erp-chip-count {
        background: rgba(147, 51, 234, 0.18);
        color: #2e1065;
    }

    .erp-chip.is-active {
        border-color: rgba(11, 94, 215, 0.55);
        box-shadow: 0 0 0 2px rgba(11, 94, 215, 0.15);
    }

    #orders_endscheduleTable.erp-table {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0;
        table-layout: fixed;
        width: 100%;
    }

    @media (max-width: 1200px) {
        #orders_endscheduleTable.erp-table {
            min-width: 1250px;
        }
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
        padding: 6px 8px;
    }

    #orders_endscheduleTable.erp-table tbody td {
        font-size: 0.92rem;
        color: #111827;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 6px 8px;
    }

    #orders_endscheduleTable.erp-table tbody tr:hover {
        background: rgba(2, 6, 23, 0.04);
    }

    .erp-cell-wrap {
        white-space: normal;
        overflow-wrap: anywhere;
    }

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

    #orders_endscheduleTable.erp-table .erp-icon-pill i {
        color: inherit;
    }

    #orders_endscheduleTable.erp-table .erp-icon-pill--on {
        border-color: rgba(11, 94, 215, 0.55);
        background: #0b5ed7;
        color: #fff;
    }

    #orders_endscheduleTable.erp-table .erp-icon-pill--off {
        border-color: rgba(148, 163, 184, 0.55);
        background: rgba(148, 163, 184, 0.12);
        color: #475569;
    }

    /* Hide default DT controls; use our toolbar */
    #orders_endscheduleTable_wrapper .dataTables_length,
    #orders_endscheduleTable_wrapper .dataTables_filter {
        display: none;
    }
</style>
@endsection

@push('js')
<script src="{{ asset('vendor/js/date-filters.js') }}"></script>
<script>
    $(document).ready(function() {
        const tableSelector = '#orders_endscheduleTable';
        const CUSTOMER_COL = 4;
        const STATUS_COL = 5;
        const $badge = $('#badgeEndyarnell');
        const $columnsMenu = $('#endyarnellColumnsMenu');

        function extractText(x) {
            const raw = (x ?? '').toString();
            if (!raw) return '';
            return $('<div>').html(raw).text().trim();
        }

        function refreshBadge(dt) {
            if (!$badge.length || !dt) return;
            $badge.text(dt.rows({ search: 'applied' }).count());
        }

        function populateCustomerFilterFromDT(dt) {
            const sel = document.getElementById('customerFilter');
            if (!sel || !dt) return;

            const colData = dt
                .column(CUSTOMER_COL, { search: 'applied' })
                .data()
                .toArray()
                .map(extractText)
                .filter(Boolean);

            const unique = [...new Set(colData)].sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' }));
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

        function populateStatusFilterFromDT(dt) {
            const sel = document.getElementById('statusFilter');
            if (!sel || !dt) return;

            const rowStatuses = dt
                .rows({ search: 'applied' })
                .nodes()
                .toArray()
                .map(tr => (tr?.getAttribute?.('data-status') || '').toString().trim())
                .filter(Boolean);

            const unique = [...new Set(rowStatuses)]
                .sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' }));
            const current = sel.value || '';
            while (sel.options.length > 1) sel.remove(1);

            const frag = document.createDocumentFragment();
            for (const v of unique) {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v ? (v.charAt(0).toUpperCase() + v.slice(1)) : v;
                frag.appendChild(opt);
            }
            sel.appendChild(frag);

            if (current && unique.includes(current)) sel.value = current;
        }

        function refreshStatusChips(dt, selectedStatus) {
            if (!dt) return;
            const nodes = dt.rows({ search: 'applied' }).nodes().toArray();
            let shipping = 0;
            let deburring = 0;
            for (const tr of nodes) {
                const st = (tr?.getAttribute?.('data-status') || '').toString().trim().toLowerCase();
                if (st === 'shipping') shipping++;
                if (st === 'deburring') deburring++;
            }
            $('#chipShipping').text(shipping);
            $('#chipDeburring').text(deburring);

            $('#endyarnellErpToolbar .erp-chip[data-status-chip]').removeClass('is-active');
            if (selectedStatus) {
                $(`#endyarnellErpToolbar .erp-chip[data-status-chip="${selectedStatus}"]`).addClass('is-active');
            }
        }

        function buildColumnsMenu(dt) {
            if (!$columnsMenu.length || !dt) return;
            $columnsMenu.empty();
            dt.columns().every(function(idx) {
                const col = this;
                const title = ($(col.header()).text() || '').trim() || `Column ${idx + 1}`;
                const id = `endyarnell-col-${idx}`;
                const checked = col.visible();

                const $item = $(`
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="${id}" ${checked ? 'checked' : ''}>
                        <label class="custom-control-label" for="${id}">${title}</label>
                    </div>
                `);
                $item.find('input').on('change', function() {
                    col.visible(this.checked);
                    dt.columns.adjust();
                });
                $columnsMenu.append($item);
            });
        }

        let dt = null;
        if ($.fn.DataTable && $(tableSelector).length) {
            const $wrapper = $('#endyarnellTableWrapper');
            const selected = { status: ($('#statusHidden').val() || '').toString().trim() };
            dt = $(tableSelector).DataTable({
                scrollX: false,
                autoWidth: false,
                pageLength: 25,
                lengthChange: false,
                dom: 't<"d-flex justify-content-between align-items-center mt-2 erp-dt-footer"ip>',
                order: [
                    [12, 'desc']
                ],
                columnDefs: [{
                    targets: [STATUS_COL, 14],
                    render: function(data, type) {
                        if (type === 'filter' || type === 'sort') {
                            return extractText(data);
                        }
                        return data;
                    }
                }, {
                    targets: [8, 9],
                    orderable: false
                }]
            });

            if ($wrapper.length) {
                $wrapper.removeClass('d-none');
            }

            if (!dt.__statusFilterAdded) {
                dt.__statusFilterAdded = true;
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (!settings || !settings.nTable || settings.nTable.id !== 'orders_endscheduleTable') return true;
                    const wanted = (selected.status || '').toString().trim().toLowerCase();
                    if (!wanted) return true;
                    try {
                        const node = dt.row(dataIndex).node();
                        const rowStatus = (node?.getAttribute?.('data-status') || '').toString().trim().toLowerCase();
                        return rowStatus === wanted;
                    } catch (e) {
                        return true;
                    }
                });
            }

            $('#endyarnellPageLength').on('change', function() {
                const v = parseInt(this.value, 10);
                if (!Number.isNaN(v)) dt.page.len(v).draw();
            });

            $('#endyarnellGlobalSearch').on('input', function() {
                dt.search(this.value || '').draw();
            });

            document.getElementById('customerFilter')?.addEventListener('change', function() {
                const val = (this.value || '').trim();
                if (!val) dt.column(CUSTOMER_COL).search('', true, false).draw();
                else {
                    const esc = $.fn.dataTable.util.escapeRegex(val);
                    dt.column(CUSTOMER_COL).search('^' + esc + '$', true, false).draw();
                }
            });

            document.getElementById('statusFilter')?.addEventListener('change', function() {
                const val = (this.value || '').trim();
                const $hidden = $('#statusHidden');
                if ($hidden.length) $hidden.val(val);
                selected.status = val;
                dt.draw();
            });

            buildColumnsMenu(dt);
            populateCustomerFilterFromDT(dt);
            populateStatusFilterFromDT(dt);
            refreshBadge(dt);
            refreshStatusChips(dt, (selected.status || '').toString().trim().toLowerCase());

            // Prefill status desde query param (si existe) sin depender del server-side list
            const initialStatus = ($('#statusHidden').val() || '').trim();
            if (initialStatus) {
                const $statusSel = $('#statusFilter');
                if ($statusSel.length && $statusSel.find('option').filter(function() { return (this.value || '').toString() === initialStatus; }).length === 0) {
                    $statusSel.append($('<option>', { value: initialStatus, text: initialStatus }));
                }
                $statusSel.val(initialStatus);
                selected.status = initialStatus;
                dt.draw();
            }

            dt.on('draw.dt search.dt order.dt page.dt', function() {
                populateCustomerFilterFromDT(dt);
                populateStatusFilterFromDT(dt);
                refreshBadge(dt);
                refreshStatusChips(dt, (selected.status || '').toString().trim().toLowerCase());
            });

            // Click chips: aplica filtro de status; si vuelves a hacer click, lo quita
            $('#endyarnellErpToolbar').on('click', '.erp-chip[data-status-chip]', function() {
                const clicked = (($(this).data('status-chip') || '') + '').toLowerCase();
                const next = (selected.status || '').toString().trim().toLowerCase() === clicked ? '' : clicked;

                selected.status = next;
                $('#statusHidden').val(next);
                const $statusSel = $('#statusFilter');
                if ($statusSel.length) {
                    if (next && $statusSel.find('option').filter(function() { return (this.value || '').toString().toLowerCase() === next; }).length === 0) {
                        $statusSel.append($('<option>', { value: next, text: next.charAt(0).toUpperCase() + next.slice(1) }));
                    }
                    $statusSel.val(next);
                }
                dt.draw();
            });
        }

        // Nota: los filtros de fecha (Year/Month/Day) se auto-envían desde `date-filters.js` al cerrar el picker.

        if (window.initTempusFilters) {
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
        }

        // Si no hay year seleccionado, mostrar el año actual en el selector (sin forzar value/submit)
        try {
            const $yearW = $('#yearPickerWrapper');
            const $year = $('#year');
            if ($yearW.length && $year.length && !($year.val() || '').trim()) {
                $yearW.datetimepicker('viewDate', moment({ year: moment().year(), month: 0, day: 1 }));
            }
        } catch (e) {}
    });
</script>
@endpush
