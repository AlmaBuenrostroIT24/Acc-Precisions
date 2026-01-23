<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Completed Orders')
@section('meta') {{-- ✅ Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')


<div class="row">
    <div class="col-md-12">
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
                                    <select id="locationFilter" class="form-control form-control-sm erp-filter-control">
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
        <div class="card mb-4 shadow-sm">
            {{-- Filtros dinámicos --}}
             
            <div class="card-body p-0">
            

                <div id="finishedErpToolbar" class="erp-table-toolbar d-flex align-items-center justify-content-between flex-wrap mb-0 px-3 pt-3 pb-2">
                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                        <div class="input-group input-group-sm erp-page-length-group" style="width: 130px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light erp-rows-addon">Rows</span>
                            </div>
                            <select id="finishedPageLength" class="form-control form-control-sm erp-filter-control">
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
                            <div class="dropdown-menu dropdown-menu-right p-2" id="finishedColumnsMenu" style="min-width: 170px;"></div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                        <div class="erp-chip-group d-flex align-items-center flex-wrap" style="gap:.35rem">
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
                        <div class="erp-toolbar-search" style="width: 260px;">
                            <input id="finishedGlobalSearch" type="search" class="form-control erp-stats-search" placeholder="Search..." autocomplete="off">
                        </div>

                    </div>
                </div>

                <div class="table-responsive d-none" id="finishedTableWrapper">
                    {{-- Tabla --}}

                    <table id="orders_endscheduleTable" class="table table-bordered table-sm table-striped table-hover erp-table" style="table-layout: fixed; width: 100%;">
                        <thead class="table-light thead-custom">
                            <tr class="text-center align-middle">
                                <th class="text-center align-middle" style="width: 65px;">LOC</th>
                                <th class="text-center align-middle" style="width: 80px;">WORKID</th>
                                <th style="width: 90px;">PN</th>
                                <th class="text-center align-middle" style="width: 60px;">ORD</th>
                                <th class="text-center align-middle" style="width: 95px;">PO</th>
                                <th style="width: 260px;">DESCRIPTION</th>
                                <th style="width: 100px;">CUSTOMER</th>
                                <th style="width: 40px;">CO</th>
                                <th style="width: 40px;">WO</th>
                                <th class="text-center align-middle" style="width: 30px;">REP</th>
                                <th class="text-center align-middle" style="width: 30px;">OUT</th>
                                <th style="width: 70px;" class="text-center align-middle">DUE</th>
                                <th style="width: 125px;">SENT</th>
                                <th class="text-center align-middle" style="width: 95px;">TARGET</th>
                                <th class="text-center align-middle" style="width: 150px;">NOTES</th>
                                <th class="text-center align-middle" style="width: 100px;">ACTION</th>
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
                                <td class="text-center">{{ $order->co }}</td>
                                <td class="text-center">{{ $order->cust_po }}</td>
                                <td style="font-size: 14px;">{{ $order->Part_description }}</td>
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
                                <td style="font-size: 14px;">
                                    <span class="open-notes-modal" data-id="{{ $order->id }}"
                                        data-notes="{{ e($order->notes) }}" title="{{ e($order->notes) }}">
                                        {{ Str::limit($order->notes, 130) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">

                                        {{-- Botón existente: Return Order --}}
                                        <button class="btn btn-sm toggle-status-btn btn-erp-success erp-table-btn"
                                            title="Return Order"
                                            data-id="{{ $order->id }}"
                                            data-status="sent">
                                            <i class="fas fa-undo-alt"></i>
                                        </button>

                                        {{-- 🔹 Nuevo botón: PDF --}}
                                        <button type="button"
                                            class="btn btn-sm btn-erp-warning erp-table-btn btn-ncr {{ !empty($order->ncr_number) ? 'is-active' : '' }}"
                                            title="{{ !empty($order->ncr_number) ? 'NCR: '.$order->ncr_number : 'Register NCR' }}"
                                            data-id="{{ $order->id }}"
                                            data-url="{{ route('schedule.finished.ncr', $order->id) }}"
                                            data-work-id="{{ $order->work_id }}"
                                            data-co="{{ $order->co ?? '' }}"
                                            data-cust-po="{{ e($order->cust_po ?? '') }}"
                                            data-pn="{{ e($order->PN ?? '') }}"
                                            data-part-description="{{ e($order->Part_description ?? '') }}"
                                            data-customer="{{ e($order->costumer ?? '') }}"
                                            data-qty="{{ $order->qty ?? '' }}"
                                            data-wo-qty="{{ $order->wo_qty ?? '' }}"
                                            data-ncr-number="{{ $order->ncr_number ?? '' }}"
                                            data-ncr-notes="{{ e($order->ncr_notes ?? '') }}">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>

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

{{-- MODAL: NCR --}}
<div class="modal fade" id="ncrModal" tabindex="-1" role="dialog" aria-labelledby="ncrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <form id="ncrForm">
                <div class="modal-header py-2 erp-ncr-modal-header">
                    <div class="d-flex align-items-center justify-content-between w-100" style="gap:.75rem;">
                        <div class="d-flex align-items-center" style="gap:.6rem;">
                            <span class="erp-ncr-title-icon" aria-hidden="true">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                            <div class="d-flex flex-column">
                                <h5 class="modal-title mb-0" id="ncrModalLabel">Create Non-Conformance</h5>
                                <small class="erp-ncr-subtitle">Register</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap justify-content-end" style="gap:.4rem;">
                            <span class="erp-ncr-chip" title="Work ID">
                                <i class="fas fa-hashtag mr-1 text-info"></i>
                                <span id="ncrHeaderWorkId">—</span>
                            </span>
                            <span class="erp-ncr-chip" title="Customer">
                                <i class="fas fa-user-tag mr-1 text-success"></i>
                                <span id="ncrHeaderCustomer">—</span>
                            </span>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-2 erp-ncr-modal-body">
                    <input type="hidden" id="ncrOrderId">
                    <input type="hidden" id="ncrPostUrl">

                    <div class="erp-ncr-orderbox mb-2">
                        <div class="erp-ncr-orderbox-title">Impact</div>

                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrWorkId">Work ID</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-hashtag text-info"></i></span>
                                    </div>
                                    <input id="ncrWorkId" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>

                            <div class="form-group col-12 col-md-6 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrCo">CO</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-file-invoice text-primary"></i></span>
                                    </div>
                                    <input id="ncrCo" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrCustPo">Cust PO</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-receipt text-success"></i></span>
                                    </div>
                                    <input id="ncrCustPo" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>

                            <div class="form-group col-12 col-md-6 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrPn">PN</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-tag text-warning"></i></span>
                                    </div>
                                    <input id="ncrPn" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-12 col-md-6 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrCustomer">Customer</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user-tag text-success"></i></span>
                                    </div>
                                    <input id="ncrCustomer" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>

                            <div class="form-group col-6 col-md-3 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrQty">Qty</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calculator text-secondary"></i></span>
                                    </div>
                                    <input id="ncrQty" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>

                            <div class="form-group col-6 col-md-3 mb-2">
                                <label class="mb-1 erp-ncr-label" for="ncrWoQty">WO Qty</label>
                                <div class="input-group input-group-sm erp-ncr-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-cubes text-secondary"></i></span>
                                    </div>
                                    <input id="ncrWoQty" type="text" class="form-control erp-ncr-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="mb-1 erp-ncr-label" for="ncrDescription">Part Description</label>
                            <textarea id="ncrDescription" class="form-control form-control-sm erp-ncr-control" rows="2" readonly></textarea>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label for="ncrNumber" class="mb-1 erp-ncr-label">NCR Number</label>
                        <div class="input-group input-group-sm erp-ncr-input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-clipboard-check text-warning"></i></span>
                            </div>
                            <input type="text" id="ncrNumber" class="form-control erp-ncr-control" maxlength="50" placeholder="e.g. NCR-1234">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="ncrNotes" class="mb-1 erp-ncr-label">Notes</label>
                        <textarea id="ncrNotes" class="form-control form-control-sm erp-ncr-control" rows="3" maxlength="2000" placeholder="Details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 erp-ncr-modal-footer">
                    <button type="button" class="btn btn-light btn-sm erp-ncr-btn" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm erp-ncr-btn" id="ncrSaveBtn">
                        Create NCR
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

    /* NCR modal (ERP style) */
    #ncrModal .modal-content {
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, 0.14);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
        overflow: hidden;
    }

    /* Más ancho que el modal-sm/MD; mantiene buena vista en pantallas pequeñas */
    #ncrModal .modal-dialog {
        max-width: 1120px;
        width: calc(100% - 1rem);
    }

    #ncrModal .erp-ncr-modal-header {
        background: linear-gradient(180deg, rgba(247, 249, 252, 0.98) 0%, rgba(237, 241, 246, 0.98) 100%);
        border-bottom: 1px solid rgba(15, 23, 42, 0.10);
    }

    #ncrModal .erp-ncr-title-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(245, 158, 11, 0.40);
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    #ncrModal .erp-ncr-title-icon i {
        font-size: 16px;
    }

    #ncrModal .erp-ncr-subtitle {
        color: #475569;
        font-weight: 600;
        line-height: 1.1;
    }

    #ncrModal .erp-ncr-chip {
        height: 28px;
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 10px;
        border: 1px solid rgba(15, 23, 42, 0.14);
        background: rgba(248, 250, 252, 0.85);
        color: #0f172a;
        font-weight: 800;
        font-size: 0.80rem;
        white-space: nowrap;
    }

    #ncrModal .erp-ncr-modal-body {
        background: #fff;
        max-height: calc(100vh - 210px);
        overflow: auto;
    }

    #ncrModal .erp-ncr-modal-footer {
        background: rgba(248, 250, 252, 0.75);
        border-top: 1px solid rgba(15, 23, 42, 0.10);
    }

    #ncrModal .erp-ncr-label {
        color: #0f172a;
        font-weight: 800;
        font-size: 0.78rem;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    #ncrModal .erp-ncr-input-group .input-group-text {
        border: 1px solid #c5c9d2;
        border-right: 0;
        border-radius: 10px 0 0 10px;
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%) !important;
        color: #0f172a;
        height: 34px;
    }

    #ncrModal .erp-ncr-control {
        border: 1px solid #c5c9d2;
        border-radius: 0 10px 10px 0;
        padding: 6px 10px;
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.08);
        color: #0f172a;
        font-weight: 700;
        height: 34px;
        line-height: 1.2;
    }

    #ncrModal .erp-ncr-control[readonly] {
        cursor: default;
        opacity: 1;
    }

    #ncrModal textarea.erp-ncr-control {
        height: auto;
        border-radius: 10px;
    }

    #ncrModal .erp-ncr-orderbox {
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(248, 250, 252, 0.55);
        padding: 10px 10px 8px;
    }

    #ncrModal .erp-ncr-orderbox-title {
        font-weight: 900;
        color: #0f172a;
        font-size: 0.78rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    #ncrModal .erp-ncr-btn {
        border-radius: 10px;
        font-weight: 800;
        height: 34px;
        padding: 6px 12px;
    }

    /* ===== NCR modal look (similar to ERP screenshot) ===== */
    #ncrModal .erp-ncr-modal-header {
        background: #fff !important;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08) !important;
        padding: 14px 16px !important;
    }

    /* Mantener el header profesional pero con badge + subtítulo (como pediste) */
    #ncrModal .erp-ncr-chip {
        display: none !important;
    }

    #ncrModal .erp-ncr-title-icon {
        display: inline-flex !important;
    }

    #ncrModal .erp-ncr-subtitle {
        display: block !important;
        margin-top: 2px;
        font-size: 0.82rem;
        color: #6b7280;
        font-weight: 600;
    }

    #ncrModal .erp-ncr-modal-body {
        padding: 14px 16px !important;
        max-height: calc(100vh - 190px) !important;
    }

    #ncrModal {
        font-size: 14px;
    }

    #ncrModal .erp-ncr-modal-footer {
        background: #fff !important;
        border-top: 1px solid rgba(15, 23, 42, 0.08) !important;
        padding: 14px 16px !important;
    }

    #ncrModal .erp-ncr-label {
        display: block !important;
        margin: 0 0 6px !important;
        color: #6b7280 !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        letter-spacing: .02em !important;
        text-transform: none !important;
    }

    #ncrModal .erp-ncr-input-group .input-group-text {
        display: none !important;
    }

    #ncrModal .erp-ncr-control {
        height: 46px !important;
        border-radius: 8px !important;
        border: 1px solid rgba(15, 23, 42, 0.12) !important;
        background: #fff !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06) !important;
        color: #111827 !important;
        font-weight: 600 !important;
        padding: 10px 12px !important;
    }

    #ncrModal .erp-ncr-control:focus {
        border-color: rgba(59, 130, 246, 0.55) !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
        outline: none !important;
    }

    #ncrModal .erp-ncr-control[readonly] {
        background: rgba(241, 245, 249, 0.85) !important;
        color: #0f172a !important;
        box-shadow: none !important;
    }

    #ncrModal textarea.erp-ncr-control {
        min-height: 86px !important;
        resize: vertical;
    }

    #ncrModal .erp-ncr-orderbox {
        background: #fff !important;
        border: 1px solid rgba(15, 23, 42, 0.10) !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06) !important;
        border-radius: 10px !important;
        padding: 12px 12px 10px !important;
    }

    #ncrModal .erp-ncr-orderbox-title {
        font-weight: 700 !important;
        color: #111827 !important;
        font-size: 1.1rem !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
        margin-bottom: 10px !important;
    }

    #ncrModal .erp-ncr-btn {
        height: 40px !important;
        border-radius: 8px !important;
        padding: 8px 14px !important;
        font-weight: 700 !important;
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
        display: flex;
        align-items: center;
        line-height: 1;
        padding: 0 10px;
    }

    .erp-table-toolbar .input-group-sm {
        align-items: stretch;
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

    /* "Rows" addon: un poco más pequeño que el resto */
    .erp-table-toolbar .input-group-text.erp-rows-addon {
        font-weight: 700;
        font-size: 0.72rem;
        letter-spacing: 0;
        text-transform: none;
        padding: 0 8px;
    }

    /* Igualar tamaño visual al botón dropdown (Columns) */
    .erp-table-toolbar .erp-page-length-group .input-group-text,
    .erp-table-toolbar .erp-page-length-group select {
        height: 34px !important;
        font-size: 14px !important;
        font-weight: 800 !important;
        line-height: 1 !important;
    }

    .erp-table-toolbar .erp-page-length-group .input-group-text {
        text-transform: none !important;
        letter-spacing: 0 !important;
    }

    .erp-table-toolbar .erp-filter-control:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
        outline: none;
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

    .erp-table-toolbar .btn-erp-primary i {
        color: #0b5ed7;
    }

    /* Dropdown Columns estilo ERP */
    #finishedColumnsMenu {
        border-radius: 12px;
        border: 1px solid #d5d8dd;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16);
        padding: 5px 6px;
        max-height: 320px;
        overflow: auto;
    }

    #finishedColumnsMenu .custom-control {
        padding: 4px 6px 4px 1.85rem;
        margin: 0;
        border-radius: 9px;
    }

    #finishedColumnsMenu .custom-control:hover {
        background: #f1f5f9;
    }

    #finishedColumnsMenu .custom-control-label {
        color: #0f172a;
        font-weight: 700;
        font-size: 0.78rem;
        cursor: pointer;
    }

    #finishedColumnsMenu .custom-control-label::before {
        border-radius: 7px;
        border: 1px solid #d5d8dd;
        background: #f8fafc;
        box-shadow: none;
    }

    #finishedColumnsMenu .custom-control-input:checked~.custom-control-label::before {
        background: #0b5ed7;
        border-color: #0b5ed7;
    }

    /* Search similar a Order Statistics */
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

    .erp-table-toolbar .erp-stats-search:focus {
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
        border: none;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.94));
        padding: 2px 6px 6px;
    }

    /* Min-widths por columna para evitar "distorción" */
    /* Definir anchos por columna (aplica también al header clonado de DataTables) */
    #orders_endscheduleTable.erp-table {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0;
        table-layout: fixed;
        width: 100%;
        font-size: 14px;
    }

    /* Solo forzar scroll en pantallas chicas */
    @media (max-width: 1200px) {
        #orders_endscheduleTable.erp-table {
            min-width: 1250px;
        }
    }

    #orders_endscheduleTable.erp-table thead th {
        background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
        color: #0f172a;
        font-weight: 800;
        font-size: 14px;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-bottom: 1px solid #d5d8dd !important;
        vertical-align: middle;
        padding: 6px 8px;
    }

    #orders_endscheduleTable.erp-table tbody td {
        font-size: 14px;
        color: #111827;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 6px 8px;
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
    #orders_endscheduleTable.erp-table .btn-erp-danger,
    #orders_endscheduleTable.erp-table .btn-erp-warning {
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

    #orders_endscheduleTable.erp-table .btn-erp-warning i {
        color: #f59e0b;
    }

    #orders_endscheduleTable.erp-table .btn-erp-warning.is-active {
        background: #f59e0b;
        border-color: #f59e0b;
        color: #fff;
    }

    #orders_endscheduleTable.erp-table .btn-erp-warning.is-active i {
        color: #fff;
    }

    #orders_endscheduleTable.erp-table .btn-erp-success:hover,
    #orders_endscheduleTable.erp-table .btn-erp-danger:hover,
    #orders_endscheduleTable.erp-table .btn-erp-warning:hover {
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
        font-size: 0.80rem;
        line-height: 1.1;
    }

    #orders_endscheduleTable_wrapper .erp-dt-footer {
        margin-top: 2px;
        /* pegarlo a la tabla */
        padding: 0 0 8px;
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
        // Si el usuario hace "refresh" (F5 / recargar), regresar la vista al estado inicial:
        // - quitar query params (filtros servidor)
        // - limpiar stateSave de DataTables (search/pageLength/orden)
        const RESET_FLAG = 'finished_force_reset_v1';
        const LAST_URL_KEY = 'finished_last_url_v1';
        let shouldReset = false;

        try {
            let isReloadByLastUrl = false;
            try {
                const lastUrl = (sessionStorage.getItem(LAST_URL_KEY) || '').toString();
                const currentUrl = (window.location && window.location.href) ? window.location.href.toString() : '';
                isReloadByLastUrl = !!lastUrl && !!currentUrl && lastUrl === currentUrl;
            } catch (e) {}

            const navEntry = (performance.getEntriesByType && performance.getEntriesByType('navigation') || [])[0];
            const isReload =
                isReloadByLastUrl ||
                (navEntry && navEntry.type === 'reload') ||
                (performance && performance.navigation && performance.navigation.type === 1);

            if (isReload) {
                sessionStorage.setItem(RESET_FLAG, '1');
            }

            // Si hay filtros en la URL, limpiar la URL primero (evita que el server vuelva a pintar selected)
            if (isReload && window.location.search) {
                window.location.replace(window.location.origin + window.location.pathname);
                return;
            }
        } catch (e) {}

        try {
            shouldReset = sessionStorage.getItem(RESET_FLAG) === '1';
            if (shouldReset) sessionStorage.removeItem(RESET_FLAG);
        } catch (e) {}

        // Guardar la URL actual al salir para detectar refresh en el siguiente load.
        try {
            $(window).on('beforeunload', function() {
                try {
                    if (window.location && window.location.href) {
                        sessionStorage.setItem(LAST_URL_KEY, window.location.href.toString());
                    }
                } catch (e) {}
            });
        } catch (e) {}

        // Si fue refresh, borrar el estado persistido de DataTables (stateSaveCallback)
        if (shouldReset) {
            try {
                localStorage.removeItem('scheduleFinishedTableState');
            } catch (e) {}
        }

        const $tableElement = $('#orders_endscheduleTable');
        if (!$tableElement.length) return;

        const $wrapper = $('#finishedTableWrapper'); // 👈 el div que envuelve la tabla

        // (Opcional recomendado) Limpiar filtros ext.search previos para evitar efectos colaterales
        $.fn.dataTable.ext.search.length = 0;

        // ---------------------- 1. INICIALIZAR DATATABLE ----------------------
        const table = $tableElement.DataTable({
            dom: "rt<'erp-dt-footer d-flex align-items-center justify-content-between flex-wrap'<'dataTables_info'i><'dataTables_paginate'p>>",
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
                targets: [9, 10, 15],
                orderable: false
            }],
            deferRender: true, // 👈 opcional, ayuda a que cargue más suave/rápido
            initComplete: function() {
                // 👇 aquí mostramos el contenedor una vez DataTables está listo
                $wrapper.removeClass('d-none');
            }
        });

        window.table = table;

        function extractTextFromHtml(x) {
            const raw = (x ?? '').toString();
            if (!raw) return '';
            return $('<div>').html(raw).text().trim();
        }

        function getLocationMetaFromCellHtml(cellHtml) {
            const $tmp = $('<div>').html((cellHtml ?? '').toString());
            let baseLocation = ($tmp.find('span').first().text() || '').trim();
            const fullText = ($tmp.text() || '').replace(/\s+/g, ' ').trim();
            const hasYarnell = fullText.toLowerCase().includes('yarnell');

            // Fallback: cuando DataTables nos da texto plano (sin spans) por `deferRender`
            if (!baseLocation) {
                if (hasYarnell) {
                    baseLocation = fullText
                        .replace(/yarnell/ig, '')
                        .replace(/[-,]/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();
                } else {
                    baseLocation = fullText;
                }
            }

            return {
                base: (baseLocation || '').replace(/\s+/g, ' ').trim(),
                hasYarnell,
            };
        }

        function getLocationOptionsFromCellHtml(cellHtml) {
            const meta = getLocationMetaFromCellHtml(cellHtml);
            const location = meta.base;
            const hasYarnell = meta.hasYarnell;

            const options = [];
            if (location) options.push(location);
            if (hasYarnell) options.push('Yarnell');
            if (location && hasYarnell && location.toLowerCase() !== 'yarnell') {
                options.push(`${location}-Yarnell`);
            }
            return [...new Set(options)].filter(Boolean);
        }

        function getLocationKeyFromCellHtml(cellHtml) {
            const opts = getLocationOptionsFromCellHtml(cellHtml);
            const combo = opts.find(v => v.includes('-Yarnell'));
            if (combo) return combo;
            return opts[0] || '';
        }

        function populateLocationFilterFromDT(dt) {
            const sel = document.getElementById('locationFilter');
            if (!sel || !dt) return;

            // Importante: sacar TODAS las filas, aunque haya búsqueda/filtros activos
            const raw = dt.column(0, { search: 'none' }).data().toArray();
            const unique = new Set();
            for (const html of raw) {
                for (const opt of getLocationOptionsFromCellHtml(html)) unique.add(opt);
            }

            const current = (sel.value || '').toString();
            while (sel.options.length > 1) sel.remove(1);
            const frag = document.createDocumentFragment();
            [...unique]
                .sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' }))
                .forEach(v => {
                    const o = document.createElement('option');
                    o.value = v;
                    o.textContent = v;
                    frag.appendChild(o);
                });
            sel.appendChild(frag);

            if (current && [...unique].includes(current)) sel.value = current;
        }

        function populateCustomerFilterFromDT(dt) {
            const sel = document.getElementById('customerFilter');
            if (!sel || !dt) return;

            // Importante: sacar TODAS las filas, aunque haya búsqueda/filtros activos
            const raw = dt.column(6, { search: 'none' }).data().toArray();
            const unique = [...new Set(raw.map(extractTextFromHtml).filter(Boolean))]
                .sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' }));

            const current = (sel.value || '').toString();
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

        // ---------------------- 2. POBLAR SELECTS DE FILTRO ----------------------
        // Importante: NO leer del DOM (tbody tr) porque DataTables con `deferRender`
        // solo mantiene en el DOM las filas visibles (25). Usamos el API de DataTables.
        populateLocationFilterFromDT(table);
        populateCustomerFilterFromDT(table);

        // ---------------------- 3. APLICAR FILTROS COMBINADOS (client-side) ----------------------
        $('#locationFilter, #customerFilter').on("change", function() {
            table.draw();
        });

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable && settings.nTable.id !== 'orders_endscheduleTable') return true;

            const locationVal = ($('#locationFilter').val() || '').toString().trim();
            const customerVal = ($('#customerFilter').val() || '').toString().trim();

            // Usar la data real del row (incluye HTML) aunque el <tr> no exista en el DOM por `deferRender`
            const rowData = table.row(dataIndex).data() || data || [];

            let locationMatch = true;
            if (locationVal) {
                const selectedLc = locationVal.toLowerCase();
                const meta = getLocationMetaFromCellHtml(rowData[0]);
                const baseLc = (meta.base || '').toLowerCase();

                if (selectedLc === 'yarnell') {
                    // "Yarnell" = SOLO cuando la location base es Yarnell (no Hearst-Yarnell)
                    locationMatch = baseLc === 'yarnell';
                } else if (selectedLc.endsWith('-yarnell')) {
                    // "Hearst-Yarnell" = SOLO cuando tiene Yarnell y el baseLocation es Hearst
                    const wantedBase = selectedLc.replace(/-yarnell$/, '');
                    locationMatch = !!meta.hasYarnell && baseLc === wantedBase;
                } else {
                    // "Hearst" = SOLO cuando baseLocation es Hearst y NO tiene Yarnell
                    locationMatch = !meta.hasYarnell && baseLc === selectedLc;
                }
            }

            const customerText = extractTextFromHtml(rowData[6]).toLowerCase();
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

        // ---------------------- 5.1 BOTÓN: NCR (modal + guardar) ----------------------
        $tableElement.on('click', '.btn-ncr', function() {
            const $btn = $(this);
            const orderId = ($btn.data('id') || '').toString();
            const url = ($btn.data('url') || '').toString();

            const decodeHtml = function(v) {
                const raw = (v ?? '').toString();
                if (!raw) return '';
                try {
                    return $('<div>').html(raw).text();
                } catch (e) {
                    return raw;
                }
            };

            $('#ncrOrderId').val(orderId);
            $('#ncrPostUrl').val(url);
            $('#ncrNumber').val(decodeHtml($btn.data('ncr-number')));
            $('#ncrNotes').val(decodeHtml($btn.data('ncr-notes')));

            // Info de la orden (desde orders_schedule)
            const workId = decodeHtml($btn.data('work-id'));
            const customer = decodeHtml($btn.data('customer'));

            $('#ncrWorkId').val(workId);
            $('#ncrCo').val(decodeHtml($btn.data('co')));
            $('#ncrCustPo').val(decodeHtml($btn.data('cust-po')));
            $('#ncrPn').val(decodeHtml($btn.data('pn')));
            $('#ncrCustomer').val(customer);
            $('#ncrQty').val(decodeHtml($btn.data('qty')));
            $('#ncrWoQty').val(decodeHtml($btn.data('wo-qty')));
            $('#ncrDescription').val(decodeHtml($btn.data('part-description')));

            $('#ncrHeaderWorkId').text(workId || '—');
            $('#ncrHeaderCustomer').text(customer || '—');

            $('#ncrModal').data('btn', $btn);
            $('#ncrModal').modal('show');
        });

        $('#ncrForm').on('submit', function(e) {
            e.preventDefault();

            const url = ($('#ncrPostUrl').val() || '').toString();
            if (!url) return;

            const ncrNumber = ($('#ncrNumber').val() || '').toString().trim();
            const ncrNotes = ($('#ncrNotes').val() || '').toString().trim();

            const $saveBtn = $('#ncrSaveBtn');
            $saveBtn.prop('disabled', true);

            $.ajax({
                url,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    ncr_number: ncrNumber,
                    ncr_notes: ncrNotes,
                }
            }).done(function(res) {
                if (!res || !res.success) {
                    Swal.fire('Attention', (res && res.message) ? res.message : 'Could not save NCR.', 'warning');
                    return;
                }

                const $btn = $('#ncrModal').data('btn');
                if ($btn && $btn.length) {
                    const savedNumber = (res.ncr_number || '').toString();
                    const savedNotes = (res.ncr_notes || '').toString();
                    $btn.data('ncr-number', savedNumber);
                    $btn.data('ncr-notes', savedNotes);
                    $btn.attr('data-ncr-number', savedNumber);
                    $btn.attr('data-ncr-notes', savedNotes);

                    $btn.toggleClass('is-active', !!savedNumber);
                    $btn.attr('title', savedNumber ? ('NCR: ' + savedNumber) : 'Register NCR');
                }

                $('#ncrModal').modal('hide');
                Swal.fire('Saved', 'NCR updated.', 'success');
            }).always(function() {
                $saveBtn.prop('disabled', false);
            }).fail(function(xhr) {
                let msg = 'Error saving NCR.';
                try {
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                } catch (e) {}
                Swal.fire('Error', msg, 'error');
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

        let targetChipFilter = null;

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
            for (let i = 0; i < rows.length; i++) {
                const targetCell = rows[i][13]; // TARGET column
                const category = getTargetCategoryFromCell(targetCell);
                if (category === 'late') late++;
                if (category === 'on-time') onTime++;
                if (category === 'early') early++;
            }

            $('#chipLate').text(late);
            $('#chipOnTime').text(onTime);
            $('#chipEarly').text(early);

            $('#finishedErpToolbar .erp-chip').removeClass('is-active');
            if (targetChipFilter) {
                $(`#finishedErpToolbar .erp-chip[data-target-filter="${targetChipFilter}"]`).addClass('is-active');
            }
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

        // Reset completo al hacer refresh: limpia filtros + search + page length a defaults.
        if (shouldReset) {
            try {
                // Global search
                $search.val('');
                table.search('');

                // DT filter (customer)
                $('#customerFilter').val('');

                // Server-side select (location) y date inputs (por si el navegador los recuerda)
                $('#locationFilter').val('');
                $('#month').val('');
                $('#monthDisplay').val('');
                $('#day').val('');
                const initialYear = (document.querySelector('#yearPickerWrapper')?.dataset?.initialYear || '').toString();
                $('#year').val(initialYear);

                // Page length default 25
                $pageLen.val('25');
                table.page.len(25);

                table.draw();
            } catch (e) {}
        }

        // Restore saved page length + search (if available)
        try {
            if (!shouldReset) {
                const state = table.state.loaded();
                if (state && state.search && typeof state.search.search === 'string') {
                    $search.val(state.search.search);
                }
                if (state && state.length) {
                    $pageLen.val(String(state.length));
                }
            }
        } catch (e) {}

        // Chip filter for TARGET column
        $.fn.dataTable.ext.search.push(function(settings, data) {
            if (settings.nTable && settings.nTable.id !== 'orders_endscheduleTable') return true;
            if (!targetChipFilter) return true;

            const targetHtml = (data[13] || '').toString().toLowerCase();
            if (targetChipFilter === 'late') return targetHtml.includes('late');
            if (targetChipFilter === 'on-time') return targetHtml.includes('on time');
            if (targetChipFilter === 'early') return targetHtml.includes('early');
            return true;
        });

        // Click: aplica filtro; si vuelves a hacer clic en el mismo chip, lo quita (como "Total")
        $('#finishedErpToolbar').on('click', '.erp-chip', function() {
            const clicked = $(this).data('target-filter') || null;
            targetChipFilter = (targetChipFilter === clicked) ? null : clicked;
            table.draw();
        });

        // Columns menu
        const $columnsMenu = $('#finishedColumnsMenu');

        // Permite seleccionar mÃ¡s de una columna sin que el dropdown se cierre en cada click
        $columnsMenu.on('mousedown click', function(e) {
            e.stopPropagation();
        });

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
                    // 0=LOC, 1=WORKID, 2=PN, 3=ORD, 4=PO, 5=DESC, 6=CUSTOMER, 7=CO, 8=WO, 9=REP, 10=OUT, 11=DUE, 12=END, 13=TARGET, 14=NOTES, 15=STATUS
                    const $row = $td.closest('tr');
                    const $targetTd = $row.find('td').eq(13); // TARGET

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
