<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Orders Statistics')
@section('meta') {{-- Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection


@section('content')

{{-- Tabs --}}
@include('orders.schedule_tab')

{{-- M├ëTRICAS PRINCIPALES COMPACTAS & ATRACTIVAS --}}
<div class="container-fluid pt-0 pb-4">

    {{-- Cards resumen principal --}}

    <div class="row kpi-erp">
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <div class="info-box fai-theme-warning" role="button" tabindex="0" aria-label="Open Active Orders detail">
                <span class="info-box-icon bg-warning shadow-sm">
                    <i class="fas fa-cogs"></i> <!-- ├ìcono de engranaje m├║ltiple -->
                </span>
                <div class="info-box-content">
                    <div class="kpi-split">
                        <div class="kpi-main">
                            <span class="info-box-text">Active</span>
                            <span class="info-box-number">{{ $totalOrdenes }}</span>
                        </div>
                        <div class="kpi-badges">
                            <div class="kpi-badges-row">
                                <span class="kpi-pill kpi-pill--info">Hearst: {{ $cantidadHearst ?? 0 }}</span>
                                <span class="kpi-pill kpi-pill--info">Yarnell: {{ $cantidadYarnell ?? 0 }}</span>
                            </div>
                            <div class="kpi-badges-row">
                                <span class="kpi-pill kpi-pill--muted">Floor: {{ $cantidadFloor ?? 0}}</span>
                                <span class="kpi-pill kpi-pill--danger">Standby: {{ $cantidadStandby ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <div class="info-box fai-theme-danger js-open-late-orders" role="button" tabindex="0" aria-label="Open Late Orders detail">
                <span class="info-box-icon bg-danger shadow-sm">
                    <i class="fas fa-exclamation-triangle"></i> <!-- ├ìcono de engranaje m├║ltiple -->
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Late Orders</span>
                    <span class="info-box-number">{{ $cantidadAtrasadas }}</span>
                </div>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <div class="info-box fai-theme-primary" role="button" tabindex="0" aria-label="Open Orders This Week detail">
                <span class="info-box-icon bg-primary shadow-sm">
                    <i class="fas fa-calendar-week"></i> <!-- ├ìcono de engranaje m├║ltiple -->
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Orders This Week</span>
                    <span class="info-box-number">{{ $ordenesSemana->count() }}</span>
                </div>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <div class="info-box fai-theme-info" role="button" tabindex="0" aria-label="Open New Orders This Week detail">
                <span class="info-box-icon bg-info-teal shadow-sm">
                    <i class="fas fa-tasks"></i> <!-- ├ìcono de engranaje m├║ltiple -->
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">New Orders This Week</span>
                    <span class="info-box-number">{{ $totalAgregadasSemana }}</span>
                </div>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <div class="info-box fai-theme-secondary" role="button" tabindex="0" aria-label="Open Orders Uploaded detail">
                <span class="info-box-icon bg-secondary shadow-sm">
                    <i class="fas fa-upload"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Orders Uploaded ({{ now()->year }})</span>
                    <span class="info-box-number">{{ $uploadedOrdersYear }}</span>
                </div>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <div class="info-box fai-theme-success" role="button" tabindex="0" aria-label="Open Completed Orders detail">
                <span class="info-box-icon bg-success shadow-sm">
                    <i class="fas fa-check-double"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Completed Orders ({{ now()->year }})</span>
                    <span class="info-box-number">{{ $completedOrdersYear }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-sm-12 mb-3">
            {{-- Card: Clientes con ordenes --}}
            <div class="card shadow-sm rounded-3 border-0 h-100">
                <div class="card-body px-2 py-2" style="max-height: 480px; overflow-y: auto;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                    </div>
                    @if ($ordenesPorCliente->isNotEmpty())
                    <div class="row g-2 text-center">
                        @foreach ($ordenesPorCliente->sortByDesc('total') as $grupo)
                        @php
                        if ($grupo->total > 10) {
                        $circleClass = 'bg-success text-white';
                        } elseif ($grupo->total >= 5 && $grupo->total <= 10) {
                            $circleClass='bg-warning text-dark' ;
                            } else {
                            $circleClass='bg-secondary text-white' ;
                            }
                            @endphp
                            <div class="col-6 col-md-3">
                            <div class="p-1 rounded-3 border bg-light h-100 d-flex flex-column align-items-center shadow-sm"
                                style="min-height: 80px;">
                                <div class="d-flex align-items-center justify-content-center gap-3" style="min-height: 46px;">
                                    <div class="rounded-circle d-flex justify-content-center align-items-center mx-auto {{ $circleClass }}"
                                        style="width: 46px; height: 46px; font-weight: 700; font-size: 1.3rem; user-select:none;">
                                        {{ $grupo->total }}
                                    </div>
                                    @if(($grupo->onhold_total ?? 0) > 0)
                                    <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">
                                        Onhold: {{ $grupo->onhold_total }}
                                    </span>
                                    @endif
                                </div>
                                <small class="fw-semibold text-truncate mt-1" title="{{ ucfirst($grupo->costumer) }}">
                                    {{ ucfirst($grupo->costumer) }}
                                </small>
                            </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted small py-5">
                    <i class="bi bi-info-circle fs-2 mb-2"></i>
                    No orders registered
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Card: ordenes agregadas esta semana --}}
    <div class="col-md-4 col-sm-12 mb-3">
        <div class="card shadow rounded-4 border-0 h-100">
            @if ($resumen['all_shipping'])
            <div class="card-header bg-success text-white text-center rounded-top-4 py-2">
                <h4 class="mb-0 fs-6 lh-1">
                    <i class="fas fa-check-circle me-2"></i> PENDING ORDERS THIS WEEK
                </h4>
            </div>
            <div class="card-body text-center py-4">
                <i class="fas fa-box-open fa-3x text-success mb-3"></i>
                <p class="mb-1 text-dark" style="font-size: 1.25rem;">TOTAL ORDERS:
                    <strong>{{ $resumen['total'] }}</strong>
                </p>
                <p class="fs-6 fw-bold text-success mb-0" style="font-size: 1.25rem;">Γ£à ┬íEverything shipped this
                    week!</p>
            </div>
            @else
            <div class="card-header bg-warning text-dark text-center rounded-top-4 py-2">
                <h5 class="mb-0 fs-6 lh-1">
                    <i class="fas fa-exclamation-circle me-2"></i> PENDING ORDERS THIS WEEK
                </h5>
            </div>
            <div class="card-body py-3">
                <div class="row">
                    {{-- Columna izquierda: resumen --}}

                    <div class="col-4">
                        <div class="text-start small">
                            {{-- Cabecera con ├¡cono --}}
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-box fa-2x text-warning mr-3"></i>
                                <h6 class="mb-0 font-weight-bold text-dark">Order Summary</h6>
                            </div>

                            {{-- L├¡nea: Total --}}
                            <div class="d-flex align-items-center mb-2">
                                <div class="mr-2" style="width: 28px;">
                                    <i class="fas fa-list-alt text-muted"></i>
                                </div>
                                <span class="flex-grow-1 text-dark" style="font-size: 1.2rem;">Total Orders</span>
                                <span class="font-weight-bold text-dark"
                                    style="font-size: 1.2rem;">{{ $resumen['total'] }}</span>
                            </div>

                            {{-- L├¡nea: Pending --}}
                            <div class="d-flex align-items-center mb-2">
                                <div class="mr-4" style="width: 28px;">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                                <span class="flex-grow-1 text-dark" style="font-size: 1.2rem;">Pending</span>
                                <span class="font-weight-bold text-warning"
                                    style="font-size: 1.2rem;">{{ $resumen['pendients'] }}</span>
                            </div>

                            {{-- L├¡nea: Sent --}}
                            <div class="d-flex align-items-center mb-2">
                                <div class="mr-4" style="width: 28px;">
                                    <i class="fas fa-paper-plane text-success"></i>
                                </div>
                                <span class="flex-grow-1 text-dark" style="font-size: 1.2rem;">Sent</span>
                                <span class="font-weight-bold text-success"
                                    style="font-size: 1.2rem;">{{ $resumen['send'] }}</span>
                            </div>

                            {{-- Mensaje final (solo si hay pendientes) --}}
                            @if(!$resumen['all_shipping'])
                            <div class="alert alert-warning py-2 px-3 mt-3 mb-0 d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <span class="font-weight-bold medium">There are still orders to be sent.</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Columna derecha: tabla scroll --}}
                    <div class="col-8">
                        <div class="table-responsive" style="max-height: 389px; overflow-y: auto;">
                            <table class="table table-sm table-bordered table-striped small mb-0">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>ORDER</th>
                                        <th>PN</th>
                                        <th>DUE DATE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($weeklyOrders->where('status', '!=', 'sent') as $order)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $order->work_id ?? 'N/A' }}</td>
                                        <td>{{ $order->PN ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($order->due_date)->format('d/m/Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> {{-- end row --}}
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-12"> {{-- Ocupa toda la fila --}}
        <div class="container-fluid py-4">
            {{-- Cards con tablas --}}
            <div class="row g-4 mb-4">
                {{-- Ordenes esta semana --}}
                <div class="col-lg-6">

                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap">
                            {{-- T├¡tulo a la izquierda --}}
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <i class="fas fa-calendar-week text-success fa-lg mr-2"></i>
                                <h6 class="mb-0 text-success">Orders This Week</h6>
                            </div>
                            {{-- Selector de semana a la derecha --}}
                            <div class="d-flex align-items-center ml-auto">
                                <span id="week-display" class="text-dark font-weight-bold small align-middle mr-2"
                                    style="white-space: nowrap;">
                                </span>
                                <div class="input-group input-group-sm" style="width: 180px;">
                                    <input type="week" name="week" id="week-filter"
                                        class="form-control border-secondary text-dark font-weight-bold" style="height: 32px;">
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="tableweek"
                                    class="table table-striped table-hover table-sm align-middle mb-0 table-modern datatable-export">
                                    {{-- Anchos consistentes sin inline-styles --}}
                                    <colgroup>
                                        <col style="width:10%">
                                        <col style="width:12%">
                                        <col style="width:36%"> {{-- DESCRIPTION --}}
                                        <col style="width:14%">
                                        <col style="width:8%"> {{-- QTY --}}
                                        <col style="width:10%"> {{-- STATUS --}}
                                        <col style="width:10%"> {{-- DUE DATE --}}
                                        <col style="width:10%"> {{-- SENT AT --}}
                                        <col style="width:6%"> {{-- SENT? --}}
                                        <col style="width:8%"> {{-- DAYS +/- --}}
                                    </colgroup>

                                    <thead>
                                        <tr style="font-size: 14px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                            <th>W.ID</th>
                                            <th>PN</th>
                                            <th>DESCRIPTION</th>
                                            <th>CUSTOMER</th>
                                            <th>QTY</th>
                                            <th>STATUS</th>
                                            <th>DUE DATE</th>
                                            <th>SENT AT</th>
                                            <th>SENT</th>
                                            <th>DAYS +/-</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableweek-body">
                                        @include('orders.schedule_tablestatistics', ['ordenesSemana' => $ordenesSemana])
                                    </tbody>
                                </table>

                            </div>
                        </div>
                        <div class="card-footer bg-light text-center py-2">
                            <small class="text-muted">Total orders: <strong
                                    id="order-count">{{ $ordenesSemana->count() }}</strong></small>
                        </div>

                    </div>
                </div>

                {{-- Ordenes atrasadas --}}
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-light d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-triangle fs-5 text-danger mr-2"></i>
                            <h6 class="mb-0 text-danger">Late Orders</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="tablelate" class="table table-striped table-hover table-sm align-middle mb-0 table-modern datatable-export"
                                    style="table-layout: fixed; width: 100%;">
                                    <thead>
                                        <tr style="font-size: 14px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                            <th style="width: 40px;">W.ID</th>
                                            <th style="width: 50px;">PN</th>
                                            <th style="width: 150px;">DESCRIPTION</th>
                                            <th style="width: 70px;">CUSTOMER</th>
                                            <th style="width: 30px;">QTY</th>
                                            <th style="width: 50px;">STATUS</th>
                                            <th style="width: 70px;">DUE DATE</th>
                                            <th style="width: 60px;">DAYS LATE</th>
                                            <th style="width: 80px;">NOTES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ordenesAtrasadas as $order)
                                        <tr>
                                            <td>{{ $order->work_id }}</td>
                                            <td>{{ $order->PN }}</td>
                                            <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                                {{ $order->Part_description }}
                                            </td>
                                            <td>{{ ucfirst($order->costumer) }}</td>
                                            <td>{{ $order->qty }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark text-truncate d-inline-block" style="max-width: 70px;"
                                                    title="{{ $order->status }}">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                            <td><span class="text-danger fw-semibold">{{ $order->due_date->format('M/d/Y') }}</span></td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    {{ $order->due_date->diffInDays(now()) }}
                                                </span>
                                            </td>
                                            <td style="white-space: normal !important; font-size: 12px !important; word-break: break-word;" title="{{ $order->notes }}">
                                                {{ $order->notes}}
                                            </td>
                                        </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-center py-2">
                            <small class="text-muted">Total orders this week: <strong>{{ $cantidadAtrasadas }}</strong></small>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card mb-4">
                <div class="card-header">
                    <h5>Filters and Order Charts</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Columna izquierda: primer filtro + bot├│n + gr├ífica -->
                        <div class="col-md-6" style="border-right: 1px solid #ddd; padding-right: 20px;">
                            <!-- Primer bloque de filtros -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="filterType">Year / Month / Week:</label>
                                    <select id="filterType" class="form-control">
                                        <option value="year">Year</option>
                                        <option value="month">Month</option>
                                        <option value="week">Week</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="yearInput">Date:</label>
                                    <input type="month" id="monthInput" class="form-control d-none">
                                    <input type="week" id="weekInput" class="form-control d-none">
                                    <select id="yearInput" class="form-control">
                                        @for ($y = date('Y'); $y >= 2025; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="customerFilter">Customer:</label>
                                    <select id="customerFilter" class="form-control">
                                        <option value="">All Customers</option>
                                        @foreach ($customers as $customer)
                                        <option value="{{ $customer }}">{{ $customer }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button onclick="printChart('ordersChart', 'TOTAL ORDERS')" class="btn btn-secondary mb-2 w-75">
                                    Print Order Totals
                                </button>
                            </div>
                            <div class="d-flex flex-column align-items-center mb-3">
                                <canvas id="ordersChart"></canvas>
                            </div>
                        </div>
                        <!-- Columna derecha: segundo filtro + bot├│n + gr├ífica -->
                        <div class="col-md-6" style="padding-left: 20px;">
                            <!-- Segundo bloque de filtros -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="filterTypeCustomer">Year / Month / Week:</label>
                                    <select id="filterTypeCustomer" class="form-control">
                                        <option value="year" selected>Year</option>
                                        <option value="month">Month</option>
                                        <option value="week">Week</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="yearInputCustomer">Date:</label>
                                    <select id="yearInputCustomer" class="form-control">
                                        @for ($y = date('Y'); $y >= 2025; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                    <input type="month" id="monthInputCustomer" class="form-control d-none">
                                    <input type="week" id="weekInputCustomer" class="form-control d-none">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button onclick="printChart('byCustomerChart', 'ORDERS PER CUSTOMER')"
                                    class="btn btn-secondary mb-2 w-75">
                                    Print Order Customer
                                </button>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <canvas id="byCustomerChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Next Orders and Deliveries</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Columna derecha: segundo filtro + bot├│n + gr├ífica -->
                        <div class="col-md-8" style="border-right: 1px solid #ddd; padding-right: 20px;">
                            <div class="mb-3">
                                <h5 class="text-center font-weight-bold">
                                    Orders Due - Next 8 Weeks
                                </h5>
                            </div>
                            <div class="col-md-4">
                                <button onclick="printChart('nextWeeksChart', 'ORDERS NEXT 8 WEEKS')"
                                    class="btn btn-secondary mb-2 w-100">
                                    Print Chart
                                </button>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <canvas id="nextWeeksChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                        <!-- Columna derecha: gr├ífica de entregas a tiempo vs tarde -->
                        <div class="col-md-4" style="padding-left: 20px;">
                            <div class="mb-2">
                                <h5 class="text-center font-weight-bold">On Time vs Late Deliveries</h5>
                            </div>
                            <!-- Filtros -->
                            <div class="row mb-2">
                                <div class="col-4">
                                    <input type="month" id="monthFilter" class="form-control form-control-sm" title="Filter by Month">
                                </div>
                                <div class="col-4">
                                    <select id="yearFilter" class="form-control form-control-sm" title="Filter by Year">
                                        <option value="">-- Year --</option>
                                        @for ($y = now()->year; $y >= 2025; $y--)
                                        <option value="{{ $y }}" @selected($y==now()->year)>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-4">
                                    <select id="customerFilterOnTime" class="form-control form-control-sm" title="Filter by Customer">
                                        <option value="">-- All --</option>
                                        @foreach ($customers as $customer)
                                        <option value="{{ $customer }}">{{ $customer }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <button onclick="printChart('onTimeChart', 'ON TIME VS LATE')"
                                    class="btn btn-secondary btn-sm mb-2 w-100">
                                    Print Chart
                                </button>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <canvas id="onTimeChart" style="width: 250%; height: 500px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                {{-- Card: Clientes con ├│rdenes --}}
                <div id="card-to-print" class="col-md-4 col-sm-6 mb-3">
                    <div class="card shadow-sm rounded-3 border-0 h-100">
                        <div
                            class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2 fw-semibold fs-5">
                                <i class="bi bi-people-fill"></i>
                                Customers with Orders
                            </div>
                            <span class="badge bg-light text-primary fs-6">{{ $totalOrdenes }}</span>
                            {{-- <button onclick="printCard()" class="btn btn-primary mb-3">Print</button>--}}
                        </div>
                        <div class="card-body px-3 py-2" style="max-height: 410px; overflow-y: auto;">
                            @if ($ordenesPorCliente->isNotEmpty())
                            <ul class="list-group list-group-flush small">
                                @foreach ($ordenesPorCliente as $grupo)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                    <span class="text-truncate" style="max-width: 65%;">
                                        <i class="bi bi-person-circle me-2 text-muted fs-5"></i>
                                        {{ ucfirst($grupo->costumer) }}
                                    </span>
                                    <span class="badge bg-success rounded-pill fs-6">{{ $grupo->total }}</span>
                                </li>
                                @endforeach
                            </ul>
                            @else
                            <div class="text-center text-muted small py-5">
                                <i class="bi bi-info-circle fs-2 mb-2"></i>
                                No orders registered
                            </div>
                            @endif
                        </div>
                    </div>
                </div>





                {{-- Card: Aqu├¡ puedes agregar una tercera tarjeta si la necesitas --}}
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card shadow-sm rounded-3 border-0 h-100">
                        {{-- Header --}}
                        <div class="card-header bg-secondary text-white d-flex align-items-center font-weight-semibold">
                            <i class="fas fa-calendar-week mr-2"></i> Weekly Orders
                        </div>

                        @php
                        // Din├ímica de colores e ├¡conos seg├║n el cumplimiento global
                        if ($percentageOnTime >= 90) {
                        $percentColor = 'text-success';
                        $circleColor = 'bg-success';
                        $icon = 'fa-check';
                        } elseif ($percentageOnTime >= 70) {
                        $percentColor = 'text-warning';
                        $circleColor = 'bg-warning';
                        $icon = 'fa-exclamation';
                        } else {
                        $percentColor = 'text-danger';
                        $circleColor = 'bg-danger';
                        $icon = 'fa-times';
                        }
                        @endphp

                        {{-- Porcentaje general de cumplimiento --}}
                        <div class="px-3 py-2 border-bottom" style="background-color: #f8f9fa;">
                            <div class="d-flex justify-content-center align-items-center mb-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center {{ $circleColor }} mr-2"
                                    style="width: 20px; height: 20px;">
                                    <i class="fas {{ $icon }} text-white" style="font-size: 0.75rem;"></i>
                                </div>
                                <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="Based on {{ $orders->count() }} weeks">
                                    <h7 class="font-weight-bold mb-0 {{ $percentColor }}" style="font-size: 1.25rem;">
                                        {{ $percentageOnTime }}%
                                    </h7>
                                </span>
                            </div>
                            <p class="text-dark text-center mb-0 small">Of weeks had 100% of orders completed on time</p>
                        </div>

                        {{-- Tabla de ├│rdenes por semana --}}
                        <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
                            <table class="table table-sm table-bordered table-striped small mb-0">
                                <thead class="thead-light sticky-top">
                                    <tr class="text-center">
                                        <th>Week</th>
                                        <th>Date Range</th>
                                        <th>Total</th>
                                        <th class="text-success">Done</th>
                                        <th class="text-warning">Late</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $item)
                                    @php
                                    $year = substr($item->week, 0, 4);
                                    $week = substr($item->week, 4);
                                    $startOfWeek = \Carbon\Carbon::now()->setISODate($year, $week)->startOfWeek();
                                    $endOfWeek = \Carbon\Carbon::now()->setISODate($year, $week)->endOfWeek();

                                    $completed = $item->completed ?? 0;
                                    $late = $item->late ?? 0;
                                    $total = $item->total ?: 1;

                                    $completedPercent = round(($completed / $total) * 100);
                                    $latePercent = round(($late / $total) * 100);
                                    @endphp

                                    <tr class="text-center">
                                        <td>{{ $year }} W{{ $week }}</td>
                                        <td>{{ $startOfWeek->format('M d') }} - {{ $endOfWeek->format('M d') }}</td>
                                        <td>{{ $item->total }}</td>
                                        <td class="text-success font-weight-bold">{{ $completed }}</td>
                                        <td class="text-danger font-weight-bold">{{ $late }}</td>
                                        <td>
                                            <span class="badge badge-pill 
                                    {{ $completedPercent >= 90 ? 'badge-success' : ($completedPercent >= 70 ? 'badge-warning' : 'badge-danger') }}">
                                                {{ $completedPercent }}%
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="p-1">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" style="width: {{ $completedPercent }}%;"></div>
                                                <div class="progress-bar bg-warning" style="width: {{ $latePercent }}%;"></div>
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


            {{-- Modal para detalle de entregas --}}
            <div class="modal fade" id="onTimeModal" tabindex="-1" role="dialog" aria-labelledby="onTimeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;" role="document">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="modal-title mb-0" id="onTimeModalLabel">Orders detail</h5>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <input type="month" id="onTimeModalMonth" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                <select id="onTimeModalCustomer" class="form-control form-control-sm erp-filter-control" style="max-width: 220px;">
                                    <option value="">-- All Customers --</option>
                                    @foreach ($customers as $customer)
                                    <option value="{{ $customer }}">{{ $customer }}</option>
                                    @endforeach
                                </select>
                                <div id="onTimeModalButtons" class="d-flex align-items-center gap-2 ml-auto flex-wrap"></div>
                            </div>
                            <div id="onTimeModalSummary" class="mb-2"></div>
                            <div id="onTimeModalContent" class="table-responsive small">
                                <div class="text-center text-muted py-4">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal: Late Orders detail --}}
            <div class="modal fade" id="lateOrdersModal" tabindex="-1" role="dialog" aria-labelledby="lateOrdersModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;" role="document">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="modal-title mb-0" id="lateOrdersModalLabel">Late Orders detail</h5>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <select id="lateOrdersModalCustomer" class="form-control form-control-sm erp-filter-control" style="max-width: 220px;">
                                    <option value="">-- All Customers --</option>
                                </select>
                                <select id="lateOrdersModalStatus" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                    <option value="">-- All Status --</option>
                                </select>
                                <span
                                    id="lateOrdersModalCount"
                                    class="badge bg-light text-dark border"
                                    data-default="Total: {{ $ordenesAtrasadas->count() }} / {{ $ordenesAtrasadas->count() }}"
                                    style="font-size: 0.85rem; min-width: 110px; padding: 6px 10px; border-radius: 8px; margin-left: 6px; height: 34px; line-height: 22px;"
                                >Total: {{ $ordenesAtrasadas->count() }} / {{ $ordenesAtrasadas->count() }}</span>
                                <div id="lateOrdersModalButtons" class="d-flex align-items-center gap-2 ml-auto flex-wrap"></div>
                            </div>
                            <div id="lateOrdersModalLoading" class="text-center text-muted py-3 d-none">Loading...</div>
                            <div class="table-responsive small">
                                <table id="lateOrdersModalTable" class="table table-striped table-hover table-sm align-middle mb-0 table-modern datatable-export">
                                    <thead class="bg-danger text-white">
                                        <tr style="font-size: 14px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                            <th style="width: 40px;">W.ID</th>
                                            <th style="width: 50px;">PN</th>
                                            <th style="width: 150px;">DESCRIPTION</th>
                                            <th style="width: 70px;">CUSTOMER</th>
                                            <th style="width: 30px;">QTY</th>
                                            <th style="width: 50px;">STATUS</th>
                                            <th style="width: 70px;">DUE DATE</th>
                                            <th style="width: 60px;">DAYS LATE</th>
                                            <th style="width: 80px;">NOTES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ordenesAtrasadas as $order)
                                        <tr>
                                            <td>{{ $order->work_id }}</td>
                                            <td>{{ $order->PN }}</td>
                                            <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                                {{ $order->Part_description }}
                                            </td>
                                            <td>{{ ucfirst($order->costumer) }}</td>
                                            <td>{{ $order->qty }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark text-truncate d-inline-block" style="max-width: 70px;" title="{{ $order->status }}">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                            <td><span class="text-danger fw-semibold">{{ $order->due_date->format('M/d/Y') }}</span></td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    {{ $order->due_date->diffInDays(now()) }}
                                                </span>
                                            </td>
                                            <td style="white-space: normal !important; font-size: 12px !important; word-break: break-word;" title="{{ $order->notes }}">
                                                {{ $order->notes}}
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

            {{-- Modal: Orders This Week detail --}}
            <div class="modal fade" id="weekOrdersModal" tabindex="-1" role="dialog" aria-labelledby="weekOrdersModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;" role="document">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="modal-title mb-0" id="weekOrdersModalLabel">Orders This Week</h5>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <select id="weekOrdersModalCustomer" class="form-control form-control-sm erp-filter-control" style="max-width: 220px;">
                                    <option value="">-- All Customers --</option>
                                </select>
                                <select id="weekOrdersModalStatus" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                    <option value="">-- All Status --</option>
                                </select>
                                <span
                                    id="weekOrdersModalCount"
                                    class="badge bg-light text-dark border"
                                    data-default="Total: 0 / 0"
                                    style="font-size: 0.85rem; min-width: 110px; padding: 6px 10px; border-radius: 8px; margin-left: 6px; height: 34px; line-height: 22px;"
                                >Total: 0 / 0</span>
                                <div id="weekOrdersModalButtons" class="d-flex align-items-center gap-2 ml-auto flex-wrap"></div>
                            </div>
                            <div id="weekOrdersModalLoading" class="text-center text-muted py-3 d-none">Loading...</div>
                            <div class="table-responsive small">
                                <table id="weekOrdersModalTable" class="table table-striped table-hover table-sm align-middle mb-0 table-modern datatable-export">
                                    <thead>
                                        <tr style="font-size: 14px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                            <th style="width: 55px;">W.ID</th>
                                            <th style="width: 70px;">PN</th>
                                            <th style="width: 320px;">DESCRIPTION</th>
                                            <th style="width: 110px;">CUSTOMER</th>
                                            <th class="text-center" style="width: 55px;">QTY</th>
                                            <th class="text-center" style="width: 75px;">STATUS</th>
                                            <th class="text-center" style="width: 90px;">DUE DATE</th>
                                            <th class="text-center" style="width: 90px;">SENT AT</th>
                                            <th class="text-center" style="width: 55px;">SENT</th>
                                            <th class="text-center" style="width: 75px;">DAYS +/-</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Se llena por JS clonando el contenido actual de #tableweek --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal: New Orders This Week detail --}}
            <div class="modal fade" id="newOrdersWeekModal" tabindex="-1" role="dialog" aria-labelledby="newOrdersWeekModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;" role="document">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="modal-title mb-0" id="newOrdersWeekModalLabel">New Orders This Week</h5>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                            <div class="modal-body">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <select id="newOrdersWeekModalCustomer" class="form-control form-control-sm erp-filter-control" style="max-width: 220px;">
                                    <option value="">-- All Customers --</option>
                                </select>
                                <select id="newOrdersWeekModalStatus" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                    <option value="">-- All Status --</option>
                                </select>
                                <select id="newOrdersWeekModalUploaded" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                    <option value="">-- All Uploaded --</option>
                                </select>
                                <span
                                    id="newOrdersWeekModalCount"
                                    class="badge bg-light text-dark border"
                                    data-default="Total: {{ $ordenesAgregadasSemana->count() }} / {{ $ordenesAgregadasSemana->count() }}"
                                    style="font-size: 0.85rem; min-width: 110px; padding: 6px 10px; border-radius: 8px; margin-left: 6px; height: 34px; line-height: 22px;"
                                >Total: {{ $ordenesAgregadasSemana->count() }} / {{ $ordenesAgregadasSemana->count() }}</span>
                                <div id="newOrdersWeekModalButtons" class="d-flex align-items-center gap-2 ml-auto flex-wrap"></div>
                            </div>
                            <div id="newOrdersWeekModalLoading" class="text-center text-muted py-3 d-none">Loading...</div>
                            <div class="table-responsive small">
                                <table id="newOrdersWeekModalTable" class="table table-striped table-hover table-sm align-middle mb-0 table-modern datatable-export">
                                    <thead>
                                        <tr style="font-size: 14px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                            <th style="width: 55px;">W.ID</th>
                                            <th style="width: 70px;">PN</th>
                                            <th style="width: 420px;">DESCRIPTION</th>
                                            <th style="width: 110px;">CUSTOMER</th>
                                            <th class="text-center" style="width: 55px;">QTY</th>
                                            <th class="text-center" style="width: 80px;">STATUS</th>
                                            <th class="text-center" style="width: 95px;">UPLOADED</th>
                                            <th class="text-center" style="width: 90px;">DUE DATE</th>
                                            <th class="text-center" style="width: 90px;">SENT AT</th>
                                            <th class="text-center" style="width: 55px;">SENT</th>
                                            <th class="text-center" style="width: 70px;">DAYS</th>
                                            <th style="width: 180px;">NOTES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @include('orders.schedule_tableneworders_week', ['orders' => $ordenesAgregadasSemana])
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal: Active Orders detail --}}
            <div class="modal fade" id="activeOrdersModal" tabindex="-1" role="dialog" aria-labelledby="activeOrdersModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;" role="document">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="modal-title mb-0" id="activeOrdersModalLabel">Active Orders</h5>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <select id="activeOrdersModalLocation" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                    <option value="">-- All Locations --</option>
                                </select>
                                <select id="activeOrdersModalCustomer" class="form-control form-control-sm erp-filter-control" style="max-width: 220px;">
                                    <option value="">-- All Customers --</option>
                                </select>
                                <select id="activeOrdersModalStatus" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                    <option value="">-- All Status --</option>
                                </select>
                                <select id="activeOrdersModalMonth" class="form-control form-control-sm erp-filter-control" style="max-width: 170px;">
                                    <option value="">-- All Months --</option>
                                </select>
                                <select id="activeOrdersModalDay" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                    <option value="">-- All Days --</option>
                                </select>
                                <span
                                    id="activeOrdersModalCount"
                                    class="badge bg-light text-dark border"
                                    data-default="Total: {{ $activeOrdersList->count() }} / {{ $activeOrdersList->count() }}"
                                    style="font-size: 0.85rem; min-width: 110px; padding: 6px 10px; border-radius: 8px; margin-left: 6px; height: 34px; line-height: 22px;"
                                >Total: {{ $activeOrdersList->count() }} / {{ $activeOrdersList->count() }}</span>
                                <div id="activeOrdersModalButtons" class="d-flex align-items-center gap-2 ml-auto flex-wrap"></div>
                            </div>
                            <div id="activeOrdersModalLoading" class="text-center text-muted py-3 d-none">Loading...</div>
                            <div class="table-responsive small">
                                <table id="activeOrdersModalTable" class="table table-striped table-hover table-sm align-middle mb-0 table-modern datatable-export">
                                    <thead>
                                        <tr style="font-size: 14px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                            <th>W.ID</th>
                                            <th>PN</th>
                                            <th>DESCRIPTION</th>
                                            <th>CUSTOMER</th>
                                            <th class="text-center">QTY</th>
                                            <th class="text-center">STATUS</th>
                                            <th class="text-center">LOCATION</th>
                                            <th class="text-center">UPLOADED</th>
                                            <th class="text-center">DUE DATE</th>
                                            <th class="text-center">DUE-UP</th>
                                            <th class="text-center">DUE-TODAY</th>
                                            <th>NOTES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @include('orders.schedule_tableactive_orders', ['orders' => $activeOrdersList])
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal: Orders Uploaded (Year) detail --}}
            <div class="modal fade" id="uploadedOrdersModal" tabindex="-1" role="dialog" aria-labelledby="uploadedOrdersModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;" role="document">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="modal-title mb-0" id="uploadedOrdersModalLabel">Orders Uploaded ({{ now()->year }})</h5>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <select id="uploadedOrdersModalCustomer" class="form-control form-control-sm erp-filter-control" style="max-width: 220px;">
                                    <option value="">-- All Customers --</option>
                                </select>
                                <select id="uploadedOrdersModalStatus" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                    <option value="">-- All Status --</option>
                                </select>
                                <select id="uploadedOrdersModalMonth" class="form-control form-control-sm erp-filter-control" style="max-width: 170px;">
                                    <option value="">-- All Months --</option>
                                </select>
                                <select id="uploadedOrdersModalDay" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                    <option value="">-- All Days --</option>
                                </select>
                                <span
                                    id="uploadedOrdersModalCount"
                                    class="badge bg-light text-dark border"
                                    data-default="Total: {{ $uploadedOrdersListYear->count() }} / {{ $uploadedOrdersListYear->count() }}"
                                    style="font-size: 0.85rem; min-width: 110px; padding: 6px 10px; border-radius: 8px; margin-left: 6px; height: 34px; line-height: 22px;"
                                >Total: {{ $uploadedOrdersListYear->count() }} / {{ $uploadedOrdersListYear->count() }}</span>
                                <div id="uploadedOrdersModalButtons" class="d-flex align-items-center gap-2 ml-auto flex-wrap"></div>
                            </div>
                            <div id="uploadedOrdersModalLoading" class="text-center text-muted py-3 d-none">Loading...</div>
                            <div class="table-responsive small">
                                <table id="uploadedOrdersModalTable" class="table table-striped table-hover table-sm align-middle mb-0 table-modern datatable-export">
                                    <thead>
                                        <tr style="font-size: 14px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                            <th style="width: 55px;">W.ID</th>
                                            <th style="width: 70px;">PN</th>
                                            <th style="width: 420px;">DESCRIPTION</th>
                                            <th style="width: 110px;">CUSTOMER</th>
                                            <th class="text-center" style="width: 55px;">QTY</th>
                                            <th class="text-center" style="width: 80px;">STATUS</th>
                                            <th class="text-center" style="width: 95px;">UPLOADED</th>
                                            <th class="text-center" style="width: 90px;">DUE DATE</th>
                                            <th class="text-center" style="width: 90px;">SENT AT</th>
                                            <th class="text-center" style="width: 55px;">SENT</th>
                                            <th class="text-center" style="width: 70px;">DAYS</th>
                                            <th style="width: 180px;">NOTES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @include('orders.schedule_tableuploaded_year', ['orders' => $uploadedOrdersListYear])
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal: Completed Orders (Year) detail --}}
            <div class="modal fade" id="completedOrdersModal" tabindex="-1" role="dialog" aria-labelledby="completedOrdersModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;" role="document">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="modal-title mb-0" id="completedOrdersModalLabel">Completed Orders ({{ now()->year }})</h5>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <select id="completedOrdersModalCustomer" class="form-control form-control-sm erp-filter-control" style="max-width: 220px;">
                                    <option value="">-- All Customers --</option>
                                </select>
                                <select id="completedOrdersModalMonth" class="form-control form-control-sm erp-filter-control" style="max-width: 170px;">
                                    <option value="">-- All Months --</option>
                                </select>
                                <select id="completedOrdersModalDay" class="form-control form-control-sm erp-filter-control" style="max-width: 160px;">
                                    <option value="">-- All Days --</option>
                                </select>
                                <span
                                    id="completedOrdersModalCount"
                                    class="badge bg-light text-dark border"
                                    data-default="Total: {{ $completedOrdersListYear->count() }} / {{ $completedOrdersListYear->count() }}"
                                    style="font-size: 0.85rem; min-width: 110px; padding: 6px 10px; border-radius: 8px; margin-left: 6px; height: 34px; line-height: 22px;"
                                >Total: {{ $completedOrdersListYear->count() }} / {{ $completedOrdersListYear->count() }}</span>
                                <div id="completedOrdersModalButtons" class="d-flex align-items-center gap-2 ml-auto flex-wrap"></div>
                            </div>
                            <div id="completedOrdersModalLoading" class="text-center text-muted py-3 d-none">Loading...</div>
                            <div class="table-responsive small">
                                <table id="completedOrdersModalTable" class="table table-striped table-hover table-sm align-middle mb-0 table-modern datatable-export">
                                    <thead>
                                        <tr style="font-size: 14px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                            <th style="width: 55px;">W.ID</th>
                                            <th style="width: 70px;">PN</th>
                                            <th style="width: 420px;">DESCRIPTION</th>
                                            <th style="width: 110px;">CUSTOMER</th>
                                            <th class="text-center" style="width: 55px;">QTY</th>
                                            <th class="text-center" style="width: 80px;">STATUS</th>
                                            <th class="text-center" style="width: 95px;">UPLOADED</th>
                                            <th class="text-center" style="width: 90px;">DUE DATE</th>
                                            <th class="text-center" style="width: 90px;">SENT AT</th>
                                            <th class="text-center" style="width: 55px;">SENT</th>
                                            <th class="text-center" style="width: 70px;">DAYS</th>
                                            <th style="width: 180px;">NOTES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @include('orders.schedule_tablecompleted_year', ['orders' => $completedOrdersListYear])
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="ordersDetailModal" tabindex="-1" role="dialog" aria-labelledby="ordersDetailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;" role="document">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center justify-content-between">
                            <div class="d-flex flex-wrap align-items-center">
                                <h5 class="modal-title mb-0" id="ordersDetailModalLabel">Orders detail</h5>
                                <span id="ordersDetailModalFilters" class="text-muted small ml-2"></span>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <select id="ordersDetailCustomer" class="form-control form-control-sm erp-filter-control" style="max-width: 220px;">
                                    <option value="">-- All Customers --</option>
                                    @foreach ($customers as $customer)
                                    <option value="{{ $customer }}">{{ $customer }}</option>
                                    @endforeach
                                </select>
                                <div id="ordersDetailButtons" class="d-flex align-items-center gap-2 ml-auto flex-wrap"></div>
                            </div>
                            <div id="ordersDetailModalContent" class="table-responsive small">
                                <div class="text-center text-muted py-4">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @endsection

            @section('css')
            <style>
                /* KPIs estilo ERP (solo para la fila superior) */
                .kpi-erp {
                    margin-top: -10px;
                    --kpi-teal: #17a2b8;
                }

                /* Theme vars (igual a FAI tabs) */
                .kpi-erp .fai-theme-info {
                    --fai-hover-border: rgba(23, 162, 184, 0.25);
                    --fai-active-border: rgba(23, 162, 184, 0.45);
                    --fai-active-shadow: rgba(12, 135, 153, 0.18);
                    --fai-active-from: rgba(23, 162, 184, 0.92);
                    --fai-active-to: rgba(12, 135, 153, 0.82);
                }

                .kpi-erp .fai-theme-primary {
                    --fai-hover-border: rgba(13, 110, 253, 0.25);
                    --fai-active-border: rgba(13, 110, 253, 0.45);
                    --fai-active-shadow: rgba(13, 110, 253, 0.18);
                    --fai-active-from: rgba(13, 110, 253, 0.92);
                    --fai-active-to: rgba(11, 94, 215, 0.82);
                }

                .kpi-erp .fai-theme-success {
                    --fai-hover-border: rgba(40, 167, 69, 0.25);
                    --fai-active-border: rgba(40, 167, 69, 0.45);
                    --fai-active-shadow: rgba(40, 167, 69, 0.18);
                    --fai-active-from: rgba(40, 167, 69, 0.92);
                    --fai-active-to: rgba(25, 135, 84, 0.82);
                }

                .kpi-erp .fai-theme-danger {
                    --fai-hover-border: rgba(220, 53, 69, 0.25);
                    --fai-active-border: rgba(220, 53, 69, 0.45);
                    --fai-active-shadow: rgba(220, 53, 69, 0.18);
                    --fai-active-from: rgba(220, 53, 69, 0.92);
                    --fai-active-to: rgba(176, 42, 55, 0.82);
                }

                .kpi-erp .fai-theme-warning {
                    --fai-hover-border: rgba(255, 193, 7, 0.30);
                    --fai-active-border: rgba(255, 193, 7, 0.55);
                    --fai-active-shadow: rgba(255, 193, 7, 0.18);
                    --fai-active-from: rgba(255, 193, 7, 0.92);
                    --fai-active-to: rgba(245, 158, 11, 0.82);
                }

                .kpi-erp .fai-theme-secondary {
                    --fai-hover-border: rgba(107, 114, 128, 0.25);
                    --fai-active-border: rgba(107, 114, 128, 0.45);
                    --fai-active-shadow: rgba(107, 114, 128, 0.18);
                    --fai-active-from: rgba(107, 114, 128, 0.92);
                    --fai-active-to: rgba(75, 85, 99, 0.82);
                }

                /* Permite reutilizar el mismo theme en modales (fuera de .kpi-erp) */
                .fai-theme-info {
                    --fai-hover-border: rgba(23, 162, 184, 0.25);
                    --fai-active-border: rgba(23, 162, 184, 0.45);
                    --fai-active-shadow: rgba(12, 135, 153, 0.18);
                    --fai-active-from: rgba(23, 162, 184, 0.92);
                    --fai-active-to: rgba(12, 135, 153, 0.82);
                }

                .fai-theme-primary {
                    --fai-hover-border: rgba(13, 110, 253, 0.25);
                    --fai-active-border: rgba(13, 110, 253, 0.45);
                    --fai-active-shadow: rgba(13, 110, 253, 0.18);
                    --fai-active-from: rgba(13, 110, 253, 0.92);
                    --fai-active-to: rgba(11, 94, 215, 0.82);
                }

                .fai-theme-success {
                    --fai-hover-border: rgba(40, 167, 69, 0.25);
                    --fai-active-border: rgba(40, 167, 69, 0.45);
                    --fai-active-shadow: rgba(40, 167, 69, 0.18);
                    --fai-active-from: rgba(40, 167, 69, 0.92);
                    --fai-active-to: rgba(25, 135, 84, 0.82);
                }

                .fai-theme-danger {
                    --fai-hover-border: rgba(220, 53, 69, 0.25);
                    --fai-active-border: rgba(220, 53, 69, 0.45);
                    --fai-active-shadow: rgba(220, 53, 69, 0.18);
                    --fai-active-from: rgba(220, 53, 69, 0.92);
                    --fai-active-to: rgba(176, 42, 55, 0.82);
                }

                .fai-theme-warning {
                    --fai-hover-border: rgba(255, 193, 7, 0.30);
                    --fai-active-border: rgba(255, 193, 7, 0.55);
                    --fai-active-shadow: rgba(255, 193, 7, 0.18);
                    --fai-active-from: rgba(255, 193, 7, 0.92);
                    --fai-active-to: rgba(245, 158, 11, 0.82);
                }

                .fai-theme-secondary {
                    --fai-hover-border: rgba(107, 114, 128, 0.25);
                    --fai-active-border: rgba(107, 114, 128, 0.45);
                    --fai-active-shadow: rgba(107, 114, 128, 0.18);
                    --fai-active-from: rgba(107, 114, 128, 0.92);
                    --fai-active-to: rgba(75, 85, 99, 0.82);
                }

                .kpi-erp .info-box {
                    height: 80px;
                    padding: .25rem .5rem;
                    border-radius: 10px;
                    border: 1px solid #e5e7eb;
                    background: #fff;
                    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
                    margin-bottom: 0.25rem;
                    transition: box-shadow .15s ease, transform .15s ease;
                    display: flex;
                    align-items: center;
                    gap: .55rem;
                    position: relative;
                    overflow: hidden;
                }

                @media (max-width: 575.98px) {
                    .kpi-erp .info-box {
                        height: auto;
                    }
                }

                .kpi-erp .info-box:hover {
                    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
                    transform: translateY(-1px);
                    border-color: var(--fai-hover-border, #e5e7eb);
                }

                .kpi-erp .info-box[role="button"] {
                    cursor: pointer;
                }

                .kpi-erp .info-box[role="button"]:focus {
                    outline: none;
                }

                .kpi-erp .info-box[role="button"]:focus-visible {
                    box-shadow: 0 0 0 3px var(--fai-hover-border, rgba(99, 102, 241, 0.25)), 0 8px 18px rgba(15, 23, 42, 0.12);
                }

                .kpi-erp .info-box.is-active {
                    border-color: var(--fai-active-border, #cbd5e1);
                    box-shadow: 0 10px 22px var(--fai-active-shadow, rgba(15, 23, 42, 0.14)), 0 2px 6px rgba(15, 23, 42, 0.08);
                    transform: translateY(-1px);
                }

                .kpi-erp .info-box.is-active::after {
                    content: "";
                    position: absolute;
                    inset: -1px;
                    background: linear-gradient(135deg, var(--fai-active-from, rgba(148, 163, 184, 0.9)), var(--fai-active-to, rgba(100, 116, 139, 0.75)));
                    opacity: .10;
                    pointer-events: none;
                }

                #lateOrdersModal.is-loading #lateOrdersModalTable {
                    visibility: hidden;
                }

                #weekOrdersModal.is-loading #weekOrdersModalTable {
                    visibility: hidden;
                }

                #newOrdersWeekModal.is-loading #newOrdersWeekModalTable {
                    visibility: hidden;
                }

                #activeOrdersModal.is-loading #activeOrdersModalTable {
                    visibility: hidden;
                }

                #activeOrdersModal.is-loading .dataTables_scrollHead,
                #activeOrdersModal.is-loading .dataTables_scrollBody {
                    visibility: hidden;
                }

                #uploadedOrdersModal.is-loading #uploadedOrdersModalTable {
                    visibility: hidden;
                }

                #completedOrdersModal.is-loading #completedOrdersModalTable {
                    visibility: hidden;
                }

                .dt-print-area {
                    display: none;
                }

                @media print {
                    @page {
                        size: A4 landscape;
                        margin: 12mm;
                    }

                    body.dt-printing > :not(.dt-print-area) {
                        display: none !important;
                    }

                    body.dt-printing .dt-print-area {
                        display: block !important;
                        visibility: visible !important;
                        position: static !important;
                        margin: 0 !important;
                        width: 100%;
                        background: #fff;
                        padding: 16px;
                    }

                    body.dt-printing .dt-print-title {
                        font-size: 13px;
                        font-weight: 800;
                        margin-bottom: 10px;
                        text-align: center;
                    }

                    body.dt-printing .dt-print-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 11px;
                        table-layout: fixed;
                        page-break-inside: auto;
                    }

                    body.dt-printing .dt-print-table thead {
                        display: table-header-group;
                    }

                    body.dt-printing .dt-print-table tbody {
                        display: table-row-group;
                    }

                    body.dt-printing .dt-print-table tr {
                        break-inside: avoid;
                        page-break-inside: avoid;
                    }

                    body.dt-printing .dt-print-table th,
                    body.dt-printing .dt-print-table td {
                        border: 1px solid #e5e7eb;
                        padding: 6px 8px;
                        vertical-align: top;
                        word-break: break-word;
                    }

                    body.dt-printing .dt-print-table th {
                        background: #111827;
                        color: #ffffff;
                        font-weight: 800;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }

                    body.dt-printing .dt-print-table tbody tr:nth-child(odd) td {
                        background: #f8fafc;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }

                    body.dt-printing .dt-print-table td,
                    body.dt-printing .dt-print-table th {
                        line-height: 1.2;
                    }
                }

                .kpi-erp .info-box .info-box-icon {
                    width: 62px;
                    height: 54px;
                    font-size: 22px;
                    line-height: 54px;
                    border-radius: 8px;
                    background: #f2f4f7 !important;
                    box-shadow: none !important;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    position: relative;
                    z-index: 1;
                }

                .kpi-erp .info-box-icon.bg-success { color: #198754 !important; }
                .kpi-erp .info-box-icon.bg-danger { color: #dc3545 !important; }
                .kpi-erp .info-box-icon.bg-primary { color: #0d6efd !important; }
                .kpi-erp .info-box-icon.bg-warning { color: #f59e0b !important; }
                .kpi-erp .info-box-icon.bg-info { color: #0d6efd !important; }
                .kpi-erp .info-box-icon.bg-info-teal {
                    color: var(--kpi-teal) !important;
                }
                .kpi-erp .info-box-icon.bg-secondary { color: #6b7280 !important; }

                .kpi-erp .info-box .info-box-text {
                    font-size: .70rem;
                    font-weight: 800;
                    letter-spacing: .02em;
                    text-transform: uppercase;
                    color: #64748b;
                }

                .kpi-erp .info-box .badge {
                    border-radius: 999px;
                }

                .kpi-erp .info-box .info-box-content {
                    flex: 1 1 auto;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: flex-start;
                    width: 100%;
                    min-width: 0;
                    gap: 2px;
                    position: relative;
                    z-index: 1;
                }

                .kpi-erp .kpi-split {
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) auto;
                    align-items: center;
                    width: 100%;
                    column-gap: .5rem;
                }

                .kpi-erp .kpi-main {
                    min-width: 0;
                }

                .kpi-erp .kpi-badges {
                    display: grid;
                    row-gap: 2px;
                    justify-items: end;
                    max-width: 220px;
                }

                .kpi-erp .kpi-badges-row {
                    display: flex;
                    gap: 4px;
                    flex-wrap: nowrap;
                    justify-content: flex-end;
                }

                @media (max-width: 575.98px) {
                    .kpi-erp .kpi-split {
                        grid-template-columns: 1fr;
                        row-gap: 4px;
                    }

                    .kpi-erp .kpi-badges {
                        justify-items: start;
                        max-width: none;
                    }

                    .kpi-erp .kpi-badges-row {
                        justify-content: flex-start;
                        flex-wrap: wrap;
                    }
                }

                .kpi-erp .info-box-number {
                    display: block;
                    font-size: 1.4rem;
                    font-weight: 800;
                    line-height: 1.05;
                    color: #0f172a;
                    margin-top: 0;
                }


                .kpi-erp .kpi-pill {
                    display: inline-flex;
                    align-items: center;
                    border-radius: 999px;
                    padding: .16rem .5rem;
                    font-size: .66rem;
                    line-height: 1;
                    font-weight: 700;
                    border: 1px solid transparent;
                    white-space: nowrap;
                }

                .kpi-erp .kpi-pill--info {
                    background: #e7f1ff;
                    border-color: #cfe2ff;
                    color: #084298;
                }

                .kpi-erp .kpi-pill--muted {
                    background: #f3f4f6;
                    border-color: #e5e7eb;
                    color: #374151;
                }

                .kpi-erp .kpi-pill--danger {
                    background: #fee2e2;
                    border-color: #fecaca;
                    color: #991b1b;
                }

                .dataTables_wrapper {
                    margin-top: 20px !important;
                    margin-left: 15px !important;
                    margin-right: 15px !important;
                }

                #tableweek tbody td {
                    height: 50px;
                }

                #tablelate tbody td {
                    height: 50px;
                }

                /* Encabezados de tablas de página (mismo estilo que modales) */
                #tableweek thead th,
                #tablelate thead th {
                    background: linear-gradient(180deg, #eef1f5 0%, #e1e6ee 100%);
                    color: #0f172a;
                    border-bottom: 1px solid #c5c9d2;
                }

                /* ActiveOrdersModalTable usa header clonado (scrollX): forzar mismo color */
                #activeOrdersModal .dataTables_scrollHead th {
                    background: linear-gradient(180deg, #eef1f5 0%, #e1e6ee 100%) !important;
                    color: #0f172a !important;
                    border-bottom: 1px solid #c5c9d2 !important;
                }

                /* LateOrdersModal: mostrar scrollbar horizontal en pantallas chicas */
                #lateOrdersModalTable {
                    min-width: 900px;
                    table-layout: fixed;
                }

                #lateOrdersModalTable th,
                #lateOrdersModalTable td {
                    white-space: nowrap;
                }

                #lateOrdersModalTable td:nth-child(3),
                #lateOrdersModalTable td:nth-child(9) {
                    white-space: normal;
                }

                /* Estilo ERP para el modal On Time/Late */
                #onTimeModal .modal-content,
                #weekOrdersModal .modal-content,
                #newOrdersWeekModal .modal-content,
                #activeOrdersModal .modal-content,
                #uploadedOrdersModal .modal-content,
                #completedOrdersModal .modal-content,
                #lateOrdersModal .modal-content,
                #ordersDetailModal .modal-content {
                    border: 1px solid #c5c9d2;
                    border-radius: 12px;
                    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
                }

                #onTimeModal .modal-header,
                #weekOrdersModal .modal-header,
                #newOrdersWeekModal .modal-header,
                #activeOrdersModal .modal-header,
                #uploadedOrdersModal .modal-header,
                #completedOrdersModal .modal-header,
                #lateOrdersModal .modal-header,
                #ordersDetailModal .modal-header {
                    background: linear-gradient(180deg, #eef1f5 0%, #d9dde3 100%);
                    border-bottom: 1px solid #c5c9d2;
                    color: #0f172a;
                    letter-spacing: .2px;
                    padding-top: .45rem;
                    padding-bottom: .45rem;
                }

                /* Headers por theme (mismo color que el info-box) */
                #lateOrdersModal.fai-theme-danger .modal-header,
                #weekOrdersModal.fai-theme-primary .modal-header,
                #newOrdersWeekModal.fai-theme-info .modal-header,
                #activeOrdersModal.fai-theme-warning .modal-header,
                #uploadedOrdersModal.fai-theme-secondary .modal-header,
                #completedOrdersModal.fai-theme-success .modal-header {
                    background: linear-gradient(180deg, var(--fai-hover-border) 0%, var(--fai-active-shadow) 100%);
                    border-bottom: 0 !important;
                    color: #0f172a;
                }
 
                #lateOrdersModal .modal-header .close { 
                    color: #0f172a; 
                    opacity: 1; 
                    text-shadow: none; 
                } 

                #onTimeModal .modal-title,
                #weekOrdersModal .modal-title,
                #newOrdersWeekModal .modal-title,
                #activeOrdersModal .modal-title,
                #uploadedOrdersModal .modal-title,
                #completedOrdersModal .modal-title,
                #lateOrdersModal .modal-title,
                #ordersDetailModal .modal-title {
                    font-weight: 700;
                }

                #onTimeModal .modal-body,
                #weekOrdersModal .modal-body,
                #newOrdersWeekModal .modal-body,
                #activeOrdersModal .modal-body,
                #uploadedOrdersModal .modal-body,
                #completedOrdersModal .modal-body,
                #lateOrdersModal .modal-body,
                #ordersDetailModal .modal-body {
                    background: #f7f9fc;
                }

                #onTimeModal table thead,
                #weekOrdersModal table thead,
                #newOrdersWeekModal table thead,
                #activeOrdersModal table thead,
                #uploadedOrdersModal table thead,
                #completedOrdersModal table thead,
                #lateOrdersModal table thead,
                #ordersDetailModal table thead {
                    background: linear-gradient(180deg, #f1f4f8 0%, #e4e9f0 100%);
                    color: #0f172a;
                }

                #lateOrdersModal.fai-theme-danger table thead,
                #weekOrdersModal.fai-theme-primary table thead,
                #newOrdersWeekModal.fai-theme-info table thead,
                #activeOrdersModal.fai-theme-warning table thead,
                #uploadedOrdersModal.fai-theme-secondary table thead,
                #completedOrdersModal.fai-theme-success table thead {
                    background: linear-gradient(180deg, var(--fai-hover-border) 0%, var(--fai-active-shadow) 100%);
                    color: #0f172a;
                }

                #onTimeModal table tbody tr:hover,
                #weekOrdersModal table tbody tr:hover,
                #newOrdersWeekModal table tbody tr:hover,
                #activeOrdersModal table tbody tr:hover,
                #uploadedOrdersModal table tbody tr:hover,
                #completedOrdersModal table tbody tr:hover,
                #lateOrdersModal table tbody tr:hover,
                #ordersDetailModal table tbody tr:hover {
                    background: #eef2f7;
                }

                /* Tabla estilo ERP en modal */
                #weekOrdersModal .dataTables_wrapper .dataTables_filter input,
                #onTimeModal .dataTables_wrapper .dataTables_filter input,
                #lateOrdersModal .dataTables_wrapper .dataTables_filter input,
                #newOrdersWeekModal .dataTables_wrapper .dataTables_filter input,
                #activeOrdersModal .dataTables_wrapper .dataTables_filter input,
                #uploadedOrdersModal .dataTables_wrapper .dataTables_filter input,
                #ordersDetailModal .dataTables_wrapper .dataTables_filter input {
                    border: 1px solid #c5c9d2;
                    border-radius: 6px;
                    padding: 4px 8px;
                    background: #f8fafc;
                }

                #weekOrdersModal .dataTables_wrapper .dataTables_length select,
                #onTimeModal .dataTables_wrapper .dataTables_length select,
                #lateOrdersModal .dataTables_wrapper .dataTables_length select,
                #newOrdersWeekModal .dataTables_wrapper .dataTables_length select,
                #activeOrdersModal .dataTables_wrapper .dataTables_length select,
                #uploadedOrdersModal .dataTables_wrapper .dataTables_length select,
                #ordersDetailModal .dataTables_wrapper .dataTables_length select {
                    border: 1px solid #c5c9d2;
                    border-radius: 6px;
                    padding: 4px 6px;
                    background: #f8fafc;
                    font-size: 14px;
                }

                #weekOrdersModal .dataTables_wrapper .dataTables_filter input,
                #onTimeModal .dataTables_wrapper .dataTables_filter input,
                #lateOrdersModal .dataTables_wrapper .dataTables_filter input,
                #newOrdersWeekModal .dataTables_wrapper .dataTables_filter input,
                #activeOrdersModal .dataTables_wrapper .dataTables_filter input,
                #uploadedOrdersModal .dataTables_wrapper .dataTables_filter input,
                #ordersDetailModal .dataTables_wrapper .dataTables_filter input {
                    font-size: 14px;
                }

                /* Compactar espacio vertical de controles */
                #weekOrdersModal .dataTables_wrapper .row:first-child,
                #onTimeModal .dataTables_wrapper .row:first-child,
                #lateOrdersModal .dataTables_wrapper .row:first-child,
                #newOrdersWeekModal .dataTables_wrapper .row:first-child,
                #activeOrdersModal .dataTables_wrapper .row:first-child,
                #uploadedOrdersModal .dataTables_wrapper .row:first-child,
                #ordersDetailModal .dataTables_wrapper .row:first-child {
                    margin-bottom: 0 !important;
                }

                #weekOrdersModal .dataTables_wrapper .dataTables_filter,
                #weekOrdersModal .dataTables_wrapper .dataTables_length,
                #onTimeModal .dataTables_wrapper .dataTables_filter,
                #onTimeModal .dataTables_wrapper .dataTables_length,
                #lateOrdersModal .dataTables_wrapper .dataTables_filter,
                #lateOrdersModal .dataTables_wrapper .dataTables_length,
                #newOrdersWeekModal .dataTables_wrapper .dataTables_filter,
                #newOrdersWeekModal .dataTables_wrapper .dataTables_length,
                #activeOrdersModal .dataTables_wrapper .dataTables_filter,
                #activeOrdersModal .dataTables_wrapper .dataTables_length,
                #uploadedOrdersModal .dataTables_wrapper .dataTables_filter,
                #uploadedOrdersModal .dataTables_wrapper .dataTables_length,
                #ordersDetailModal .dataTables_wrapper .dataTables_filter,
                #ordersDetailModal .dataTables_wrapper .dataTables_length {
                    margin-bottom: 0 !important;
                }

                /* Controles ERP para filtros del modal */
                #weekOrdersModal .erp-filter-control,
                #onTimeModal .erp-filter-control,
                #lateOrdersModal .erp-filter-control,
                #newOrdersWeekModal .erp-filter-control,
                #activeOrdersModal .erp-filter-control,
                #uploadedOrdersModal .erp-filter-control,
                #completedOrdersModal .erp-filter-control,
                #ordersDetailModal .erp-filter-control {
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

                #weekOrdersModal .erp-filter-control:focus,
                #onTimeModal .erp-filter-control:focus,
                #lateOrdersModal .erp-filter-control:focus,
                #newOrdersWeekModal .erp-filter-control:focus,
                #activeOrdersModal .erp-filter-control:focus,
                #uploadedOrdersModal .erp-filter-control:focus,
                #completedOrdersModal .erp-filter-control:focus,
                #ordersDetailModal .erp-filter-control:focus {
                    border-color: #94a3b8;
                    box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
                }

                /* Botones estilo ERP en gris con ícono de color */
                #weekOrdersModal .btn-erp-success,
                #weekOrdersModal .btn-erp-danger,
                #weekOrdersModal .btn-erp-primary,
                #onTimeModal .btn-erp-success,
                #onTimeModal .btn-erp-danger,
                #onTimeModal .btn-erp-primary,
                #lateOrdersModal .btn-erp-success,
                #lateOrdersModal .btn-erp-danger,
                #lateOrdersModal .btn-erp-primary,
                #newOrdersWeekModal .btn-erp-success,
                #newOrdersWeekModal .btn-erp-danger,
                #newOrdersWeekModal .btn-erp-primary,
                #activeOrdersModal .btn-erp-success,
                #activeOrdersModal .btn-erp-danger,
                #activeOrdersModal .btn-erp-primary,
                #uploadedOrdersModal .btn-erp-success,
                #uploadedOrdersModal .btn-erp-danger,
                #uploadedOrdersModal .btn-erp-primary,
                #completedOrdersModal .btn-erp-success,
                #completedOrdersModal .btn-erp-danger,
                #completedOrdersModal .btn-erp-primary,
                #ordersDetailModal .btn-erp-success,
                #ordersDetailModal .btn-erp-danger,
                #ordersDetailModal .btn-erp-primary {
                    background: #f8fafc;
                    border: 1px solid #d5d8dd;
                    color: #1f2937;
                    border-radius: 8px;
                    font-weight: 700;
                    box-shadow: none;
                }

                #weekOrdersModal .btn-erp-success i,
                #onTimeModal .btn-erp-success i,
                #lateOrdersModal .btn-erp-success i,
                #newOrdersWeekModal .btn-erp-success i,
                #activeOrdersModal .btn-erp-success i,
                #uploadedOrdersModal .btn-erp-success i,
                #completedOrdersModal .btn-erp-success i,
                #ordersDetailModal .btn-erp-success i {
                    color: #0f5132;
                }

                #weekOrdersModal .btn-erp-danger i,
                #onTimeModal .btn-erp-danger i,
                #lateOrdersModal .btn-erp-danger i,
                #newOrdersWeekModal .btn-erp-danger i,
                #activeOrdersModal .btn-erp-danger i,
                #uploadedOrdersModal .btn-erp-danger i,
                #completedOrdersModal .btn-erp-danger i,
                #ordersDetailModal .btn-erp-danger i {
                    color: #b91c1c;
                }

                #weekOrdersModal .btn-erp-primary i,
                #onTimeModal .btn-erp-primary i,
                #lateOrdersModal .btn-erp-primary i,
                #newOrdersWeekModal .btn-erp-primary i,
                #activeOrdersModal .btn-erp-primary i,
                #uploadedOrdersModal .btn-erp-primary i,
                #completedOrdersModal .btn-erp-primary i,
                #ordersDetailModal .btn-erp-primary i {
                    color: #0b5ed7;
                }

                #onTimeModal .btn-erp-success:hover,
                #onTimeModal .btn-erp-danger:hover,
                #onTimeModal .btn-erp-primary:hover,
                #lateOrdersModal .btn-erp-success:hover,
                #lateOrdersModal .btn-erp-danger:hover,
                #lateOrdersModal .btn-erp-primary:hover,
                #activeOrdersModal .btn-erp-success:hover,
                #activeOrdersModal .btn-erp-danger:hover,
                #activeOrdersModal .btn-erp-primary:hover,
                #uploadedOrdersModal .btn-erp-success:hover,
                #uploadedOrdersModal .btn-erp-danger:hover,
                #uploadedOrdersModal .btn-erp-primary:hover,
                #completedOrdersModal .btn-erp-success:hover,
                #completedOrdersModal .btn-erp-danger:hover,
                #completedOrdersModal .btn-erp-primary:hover,
                #ordersDetailModal .btn-erp-success:hover,
                #ordersDetailModal .btn-erp-danger:hover,
                #ordersDetailModal .btn-erp-primary:hover {
                    filter: brightness(0.97);
                    color: #111827;
                }

                #ordersDetailModalFilters {
                    display: inline-flex;
                    align-items: center;
                    padding: 2px 8px;
                    background: #eef1f5;
                    border: 1px solid #d5d8dd;
                    border-radius: 12px;
                    font-weight: 600;
                    color: #4b5563;
                }

                #onTimeDetailTable,
                #lateOrdersModalTable,
                #weekOrdersModalTable,
                #newOrdersWeekModalTable,
                #activeOrdersModalTable,
                #uploadedOrdersModalTable,
                #completedOrdersModalTable,
                #ordersDetailTable {
                    border: 1px solid #d1d5db;
                    border-radius: 10px;
                    overflow: hidden;
                    background: #fff;
                    table-layout: auto;
                }

                /* Forzar scroll horizontal en pantallas chicas (como otros modales) */
                #weekOrdersModalTable {
                    min-width: 1050px;
                    table-layout: fixed;
                }

                #weekOrdersModalTable th,
                #weekOrdersModalTable td {
                    white-space: nowrap;
                }

                /* DESCRIPTION puede partir línea */
                #weekOrdersModalTable td:nth-child(3) {
                    white-space: normal;
                    word-break: break-word;
                    font-size: 12px;
                    line-height: 1.1;
                }

                #newOrdersWeekModalTable {
                    min-width: 1350px;
                    table-layout: fixed;
                }

                #newOrdersWeekModalTable th,
                #newOrdersWeekModalTable td {
                    white-space: nowrap;
                }

                /* DESCRIPTION y NOTES pueden partir línea */
                #newOrdersWeekModalTable td:nth-child(3),
                #newOrdersWeekModalTable td:nth-child(12) {
                    white-space: normal;
                    word-break: break-word;
                }

                #activeOrdersModalTable {
                    min-width: 1350px;
                    table-layout: fixed;
                    width: 100% !important;
                }

                #uploadedOrdersModalTable {
                    min-width: 1350px;
                    table-layout: fixed;
                }

                #uploadedOrdersModalTable th,
                #uploadedOrdersModalTable td {
                    white-space: nowrap;
                }

                /* DESCRIPTION y NOTES pueden partir línea */
                #uploadedOrdersModalTable td:nth-child(3),
                #uploadedOrdersModalTable td:nth-child(12) {
                    white-space: normal;
                    word-break: break-word;
                }

                #completedOrdersModalTable {
                    min-width: 1350px;
                    table-layout: fixed;
                }

                #completedOrdersModalTable th,
                #completedOrdersModalTable td {
                    white-space: nowrap;
                }

                /* DESCRIPTION y NOTES pueden partir línea */
                #completedOrdersModalTable td:nth-child(3),
                #completedOrdersModalTable td:nth-child(12) {
                    white-space: normal;
                    word-break: break-word;
                }

                #onTimeDetailTable thead th,
                #lateOrdersModalTable thead th,
                #weekOrdersModalTable thead th,
                #newOrdersWeekModalTable thead th,
                #activeOrdersModalTable thead th,
                #uploadedOrdersModalTable thead th,
                #completedOrdersModalTable thead th,
                #ordersDetailTable thead th {
                    background: linear-gradient(180deg, #eef1f5 0%, #e1e6ee 100%);
                    color: #0f172a;
                    border-bottom: 1px solid #c5c9d2;
                    font-size: 14px;
                    font-weight: 700;
                }

                #onTimeDetailTable td,
                #onTimeDetailTable th,
                #lateOrdersModalTable td,
                #lateOrdersModalTable th,
                #weekOrdersModalTable td,
                #weekOrdersModalTable th,
                #newOrdersWeekModalTable td,
                #newOrdersWeekModalTable th,
                #activeOrdersModalTable td,
                #activeOrdersModalTable th,
                #uploadedOrdersModalTable td,
                #uploadedOrdersModalTable th,
                #completedOrdersModalTable td,
                #completedOrdersModalTable th,
                #ordersDetailTable td,
                #ordersDetailTable th {
                    padding: 8px 10px;
                    vertical-align: middle;
                    font-size: 14px;
                    word-break: break-word;
                }

                /* Fijar anchos para evitar que se muevan al paginar (Active Orders) */
                #activeOrdersModalTable th:nth-child(1),
                #activeOrdersModalTable td:nth-child(1) { width: 70px; }
                #activeOrdersModalTable th:nth-child(2),
                #activeOrdersModalTable td:nth-child(2) { width: 120px; }
                #activeOrdersModalTable th:nth-child(3),
                #activeOrdersModalTable td:nth-child(3) { width: 280px; }
                #activeOrdersModalTable th:nth-child(4),
                #activeOrdersModalTable td:nth-child(4) { width: 150px; }
                #activeOrdersModalTable th:nth-child(5),
                #activeOrdersModalTable td:nth-child(5) { width: 55px; }
                #activeOrdersModalTable th:nth-child(6),
                #activeOrdersModalTable td:nth-child(6) { width: 95px; }
                #activeOrdersModalTable th:nth-child(7),
                #activeOrdersModalTable td:nth-child(7) { width: 95px; }
                #activeOrdersModalTable th:nth-child(8),
                #activeOrdersModalTable td:nth-child(8) { width: 110px; }
                #activeOrdersModalTable th:nth-child(9),
                #activeOrdersModalTable td:nth-child(9) { width: 110px; }
                #activeOrdersModalTable th:nth-child(10),
                #activeOrdersModalTable td:nth-child(10) { width: 70px; }
                #activeOrdersModalTable th:nth-child(11),
                #activeOrdersModalTable td:nth-child(11) { width: 90px; }
                #activeOrdersModalTable th:nth-child(12),
                #activeOrdersModalTable td:nth-child(12) { width: 170px; }

                #activeOrdersModalTable td:nth-child(3),
                #activeOrdersModalTable td:nth-child(12) {
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                /* PN: permitir salto de línea (evita que se salga de la celda) */
                #activeOrdersModalTable td:nth-child(2) {
                    white-space: normal;
                    word-break: break-word;
                    overflow-wrap: anywhere;
                }

                /* PN: permitir salto de l》ea en todos los modales (evita overflow) */
                #lateOrdersModalTable td:nth-child(2),
                #weekOrdersModalTable td:nth-child(2),
                #newOrdersWeekModalTable td:nth-child(2),
                #uploadedOrdersModalTable td:nth-child(2),
                #completedOrdersModalTable td:nth-child(2) {
                    white-space: normal;
                    word-break: break-word;
                    overflow-wrap: anywhere;
                }

                #activeOrdersModalTable th:nth-child(10),
                #activeOrdersModalTable td:nth-child(10),
                #activeOrdersModalTable th:nth-child(11),
                #activeOrdersModalTable td:nth-child(11) {
                    padding-left: 6px;
                    padding-right: 6px;
                    white-space: nowrap;
                }

                /* Reservar espacio del scrollbar para que no “salte” el layout */
                #activeOrdersModal .modal-body {
                    scrollbar-gutter: stable;
                }

                /* Anchos para fechas (Due y Sent) */
                #onTimeDetailTable th:nth-child(7),
                #onTimeDetailTable td:nth-child(7),
                #onTimeDetailTable th:nth-child(8),
                #onTimeDetailTable td:nth-child(8) {
                    min-width: 115px;
                }

                /* Anchos sugeridos para columnas compactas */
                #onTimeDetailTable th:nth-child(1),
                #ordersDetailTable th:nth-child(1),
                #onTimeDetailTable td:nth-child(1),
                #ordersDetailTable td:nth-child(1) {
                    min-width: 70px;
                    width: 8%;
                }

                #onTimeDetailTable th:nth-child(2),
                #ordersDetailTable th:nth-child(2),
                #onTimeDetailTable td:nth-child(2),
                #ordersDetailTable td:nth-child(2) {
                    min-width: 90px;
                    width: 10%;
                }

                #onTimeDetailTable th:nth-child(4),
                #ordersDetailTable th:nth-child(4),
                #onTimeDetailTable td:nth-child(4),
                #ordersDetailTable td:nth-child(4) {
                    min-width: 90px;
                    width: 9%;
                }

                #onTimeDetailTable th:nth-child(5),
                #ordersDetailTable th:nth-child(5),
                #onTimeDetailTable td:nth-child(5),
                #ordersDetailTable td:nth-child(5) {
                    min-width: 55px;
                    width: 5%;
                }

                #onTimeDetailTable th:nth-child(6),
                #ordersDetailTable th:nth-child(6),
                #onTimeDetailTable td:nth-child(6),
                #ordersDetailTable td:nth-child(6) {
                    min-width: 70px;
                    width: 7%;
                }

                #onTimeDetailTable th:nth-child(7),
                #ordersDetailTable th:nth-child(7),
                #onTimeDetailTable td:nth-child(7),
                #ordersDetailTable td:nth-child(7),
                #onTimeDetailTable th:nth-child(8),
                #ordersDetailTable th:nth-child(8),
                #onTimeDetailTable td:nth-child(8),
                #ordersDetailTable td:nth-child(8) {
                    min-width: 80px;
                    width: 7%;
                }

                #onTimeDetailTable th:nth-child(9),
                #ordersDetailTable th:nth-child(9),
                #onTimeDetailTable td:nth-child(9),
                #ordersDetailTable td:nth-child(9) {
                    min-width: 50px;
                    width: 5%;
                }

                #onTimeDetailTable th:nth-child(10),
                #ordersDetailTable th:nth-child(10),
                #onTimeDetailTable td:nth-child(10),
                #ordersDetailTable td:nth-child(10) {
                    min-width: 160px;
                    width: 12%;
                }

                /* Descripcion ocupa el resto */
                #onTimeDetailTable th:nth-child(3),
                #ordersDetailTable th:nth-child(3),
                #onTimeDetailTable td:nth-child(3),
                #ordersDetailTable td:nth-child(3) {
                    min-width: 340px;
                    width: auto;
                }

                #onTimeDetailTable tbody tr:nth-child(odd) {
                    background: #f8fafc;
                }

                #onTimeDetailTable tbody tr:hover {
                    background: #eef2f7;
                }

                #lateOrdersModalTable tbody tr:nth-child(odd) {
                    background: #f8fafc;
                }

                #lateOrdersModalTable tbody tr:hover {
                    background: #eef2f7;
                }

                #weekOrdersModalTable tbody tr:nth-child(odd) {
                    background: #f8fafc;
                }

                #weekOrdersModalTable tbody tr:hover {
                    background: #eef2f7;
                }

                #newOrdersWeekModalTable tbody tr:nth-child(odd) {
                    background: #f8fafc;
                }

                #newOrdersWeekModalTable tbody tr:hover {
                    background: #eef2f7;
                }

                #onTimeModal .dataTables_wrapper .dataTables_info,
                #onTimeModal .dataTables_wrapper .dataTables_length label,
                #onTimeModal .dataTables_wrapper .dataTables_filter label {
                    font-size: 14px;
                    color: #1f2937;
                }

                /* Colores por fila/estado */
                #onTimeModal .status-row-early td {
                    background: inherit;
                }

                #onTimeModal .status-row-on-time td {
                    background: inherit;
                }

                #onTimeModal .status-row-late td {
                    background: inherit;
                }

                /* Δ Days colores */
                #onTimeModal .delta-early {
                    color: #2b6cb0;
                    font-weight: 700;
                }

                #onTimeModal .delta-on-time {
                    color: #065f46;
                    font-weight: 700;
                }

                #onTimeModal .delta-late {
                    color: #b91c1c;
                    font-weight: 700;
                }

                /* Neutralizar colores de texto aplicados por clases de estado */
                #onTimeModal td.text-primary,
                #onTimeModal td.text-success,
                #onTimeModal td.text-danger {
                    color: inherit !important;
                    font-weight: 400 !important;
                }

                /* Variantes por estado */
                /* Early azul, On Time verde */
                #onTimeModal.status-early .modal-header {
                    background: linear-gradient(180deg, #e9f0fb 0%, #d7deeb 100%);
                    border-color: #c5cedd;
                }

                #onTimeModal.status-on-time .modal-header {
                    background: linear-gradient(180deg, #e6f4ec 0%, #d5e7dc 100%);
                    border-color: #c3e0cf;
                }

                #onTimeModal.status-late .modal-header {
                    background: linear-gradient(180deg, #f8e9e5 0%, #edd8d3 100%);
                    border-color: #e0c4bc;
                }
            </style>
            @endsection

            @push('js')

            <script>
                // orders_dashboard.js

                Chart.register(ChartDataLabels);

                // Modal de detalle para gráfico "Total Orders"
                const ordersModalEl = document.getElementById('ordersDetailModal');
                const ordersModalContentEl = document.getElementById('ordersDetailModalContent');
                const ordersModalTitleEl = document.getElementById('ordersDetailModalLabel');
                const ordersModalFiltersEl = document.getElementById('ordersDetailModalFilters');
                const ordersModalCustomerEl = document.getElementById('ordersDetailCustomer');
                const ordersModalButtonsEl = document.getElementById('ordersDetailButtons');
                const ordersDetailState = {
                    params: {}
                };
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                const getFullMonth = (val) => {
                    const n = parseInt(val, 10);
                    return isNaN(n) ? '' : (monthNames[n - 1] || '');
                };

                function openOrdersDetailModal(params, labelText) {
                    ordersDetailState.params = {
                        ...(params || {})
                    };
                    if (ordersModalCustomerEl) {
                        ordersModalCustomerEl.value = params.customer || '';
                    }
                    if (!ordersModalEl || !ordersModalContentEl) return;
                    ordersModalContentEl.innerHTML = '<div class="text-center text-muted py-4">Loading...</div>';
                    if (ordersModalTitleEl) ordersModalTitleEl.textContent = labelText || 'Orders detail';
                    if (ordersModalFiltersEl) {
                        const txt = [];
                        if (params.year) txt.push(`Year: ${params.year}`);
                        if (params.month) {
                            const fullMonth = getFullMonth(params.month) || new Date(`${params.year || new Date().getFullYear()}-${params.month}-01`).toLocaleString('en-US', {
                                month: 'long'
                            });
                            txt.push(`Month: ${params.month} (${fullMonth})`);
                        }
                        if (params.day) txt.push(`Day: ${params.day}`);
                        if (params.week) txt.push(`Week: ${params.week}`);
                        if (params.customer) txt.push(`Customer: ${params.customer}`);
                        ordersModalFiltersEl.textContent = txt.length ? txt.join(' | ') : '';
                    }
                    const searchParams = new URLSearchParams(params);
                    fetch(`/orders/summary/detail?${searchParams.toString()}`)
                        .then(res => res.text())
                        .then(html => {
                            ordersModalContentEl.innerHTML = html || '<div class="text-center text-muted py-4">No data</div>';
                            const $table = $('#ordersDetailTable');
                            if ($.fn.DataTable.isDataTable($table)) {
                                $table.DataTable().destroy();
                            }
                            if ($table.length) {
                                let filtersText = (ordersModalFiltersEl?.textContent || '').trim();
                                if (!filtersText) {
                                    const p = ordersDetailState.params || {};
                                    const parts = [];
                                    if (p.year) parts.push(`Year: ${p.year}`);
                                    if (p.month) {
                                        const fullMonth = getFullMonth(p.month) || new Date(`${p.year || new Date().getFullYear()}-${p.month}-01`).toLocaleString('en-US', {
                                            month: 'long'
                                        });
                                        parts.push(`Month: ${p.month} (${fullMonth})`);
                                    }
                                    if (p.day) parts.push(`Day: ${p.day}`);
                                    if (p.week) parts.push(`Week: ${p.week}`);
                                    if (p.customer) parts.push(`Customer: ${p.customer}`);
                                    filtersText = parts.join(' | ');
                                }
                                const exportTitle = `Schedule Statistics - ${ordersModalTitleEl?.textContent || 'Orders detail'}`;
                                const exportFilename = `${exportTitle}${filtersText ? '_' + filtersText : ''}`.replace(/[\\/:*?"<>| ]+/g, '_');

                                    const dt = $table.DataTable({
                                        dom: "<'row mb-0'<'col-sm-6 d-flex align-items-center'l><'col-sm-6 d-flex justify-content-end align-items-center'f>>" +
                                            "<'row'<'col-12'tr>>" +
                                            "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
                                    pageLength: 14,
                                    lengthMenu: [14, 25, 50, 100],
                                        searching: true,
                                        ordering: true,
                                        info: true,
                                        order: [
                                            [6, 'asc'],
                                            [7, 'asc']
                                        ], // Uploaded then Due
                                        buttons: [{
                                                extend: 'excelHtml5',
                                                text: '<i class="fas fa-file-excel"></i> Excel',
                                                className: 'btn btn-erp-success btn-sm mx-1',
                                                title: exportTitle,
                                                filename: exportFilename,
                                                messageTop: filtersText || null
                                            },
                                            {
                                                extend: 'pdfHtml5',
                                                text: '<i class="fas fa-file-pdf"></i> PDF',
                                                className: 'btn btn-erp-danger btn-sm mx-1',
                                                orientation: 'landscape',
                                                pageSize: 'A4',
                                                title: exportTitle,
                                                filename: exportFilename,
                                                messageTop: filtersText || null,
                                                customize: function(doc) {
                                                    addPdfRowNumbers(doc, '#');
                                                }
                                            },
                                            {
                                                extend: 'print',
                                                text: '<i class="fas fa-print"></i> Print',
                                                className: 'btn btn-erp-primary btn-sm mx-1',
                                                action: function(e, dt) {
                                                    e.preventDefault();
                                                    const printed = printDataTableAsPdf(dt, exportTitle, getFechaHoraActual(), {
                                                        title: exportTitle,
                                                        messageTop: filtersText || null,
                                                        orientation: 'landscape',
                                                        pageSize: 'A4'
                                                    });
                                                    if (!printed) {
                                                        printDataTableInPlace(dt, exportTitle, getFechaHoraActual());
                                                    }
                                                }
                                            }
                                        ]
                                    });
                                const $host = $(ordersModalButtonsEl);
                                if ($host.length) {
                                    $host.empty();
                                    $host.append(dt.buttons().container());
                                }
                                populateOrdersModalCustomers(params.customer || '');
                            }
                            $('#ordersDetailModal').modal('show');
                        })
                        .catch(() => {
                            ordersModalContentEl.innerHTML = '<div class="text-center text-danger py-4">Error loading data</div>';
                            $('#ordersDetailModal').modal('show');
                        });
                }

                // Filtro de customer dentro del modal de Orders
                function refreshOrdersDetailByCustomer() {
                    const base = {
                        ...(ordersDetailState.params || {})
                    };
                    if (ordersModalCustomerEl && ordersModalCustomerEl.value) {
                        base.customer = ordersModalCustomerEl.value;
                    } else {
                        delete base.customer;
                    }
                    openOrdersDetailModal(base, ordersModalTitleEl?.textContent || 'Orders detail');
                }

                function populateOrdersModalCustomers(selected) {
                    if (!ordersModalCustomerEl) return;
                    const tableBody = document.querySelector('#ordersDetailTable tbody');
                    if (!tableBody) return;
                    const customers = new Set();
                    tableBody.querySelectorAll('tr').forEach(tr => {
                        const td = tr.children[3];
                        if (td) {
                            const name = (td.textContent || '').trim();
                            if (name) customers.add(name);
                        }
                    });
                    const prev = ordersModalCustomerEl.value;
                    ordersModalCustomerEl.innerHTML = '<option value="">-- All Customers --</option>';
                    customers.forEach(name => {
                        const opt = document.createElement('option');
                        opt.value = name;
                        opt.textContent = name;
                        ordersModalCustomerEl.appendChild(opt);
                    });
                    if (selected && customers.has(selected)) {
                        ordersModalCustomerEl.value = selected;
                    } else if (customers.has(prev)) {
                        ordersModalCustomerEl.value = prev;
                    } else {
                        ordersModalCustomerEl.value = '';
                    }
                }

                function getFechaHoraActual() {
                    const now = new Date();
                    const fecha = now.toLocaleDateString('en-US');
                    const hora = now.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    return `${fecha} ${hora}`;
                }

                function stripHtml(value) {
                    return $('<div>').html(value ?? '').text().trim();
                }

                function printDataTableInPlace(dt, tableTitle, fechaHora) {
                    if (!dt) return;
                    let $area = $('#dtPrintArea');
                    if (!$area.length) {
                        $area = $('<div id="dtPrintArea" class="dt-print-area" aria-hidden="true"></div>').appendTo('body');
                    }
                    const headers = dt.columns().header().toArray().map(th => $(th).text().trim());
                    const rows = dt.rows({
                        search: 'applied',
                        order: 'applied'
                    }).data().toArray();

                    const thead = `<thead><tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr></thead>`;
                    const tbody = `<tbody>${rows.map(row => {
                        const cells = (Array.isArray(row) ? row : Object.values(row));
                        return `<tr>${cells.map(cell => `<td>${stripHtml(cell)}</td>`).join('')}</tr>`;
                    }).join('')}</tbody>`;

                    const title = `${tableTitle} - ${fechaHora}`;
                    $area.html(`
                        <div class="dt-print-header">
                            <div class="dt-print-title">${title}</div>
                        </div>
                        <table class="dt-print-table">${thead}${tbody}</table>
                    `);

                    document.body.classList.add('dt-printing');
                    setTimeout(() => window.print(), 0);

                    const cleanup = () => {
                        document.body.classList.remove('dt-printing');
                        $area.empty();
                        window.removeEventListener('afterprint', cleanup);
                    };
                    window.addEventListener('afterprint', cleanup, {
                        once: true
                    });
                }

                function buildPdfDocFromDataTable(dt, config) {
                    const exportOptions = (config && config.exportOptions) ? config.exportOptions : {};
                    const exportData = dt.buttons.exportData(exportOptions);
                    const exportInfo = dt.buttons.exportInfo(config || {});
                    const body = [];

                    if ((config && config.header) !== false) {
                        body.push(exportData.header.map(h => ({
                            text: typeof h === 'string' ? h : (h ?? '') + '',
                            style: 'tableHeader'
                        })));
                    }

                    for (let rowIndex = 0; rowIndex < exportData.body.length; rowIndex++) {
                        body.push(exportData.body[rowIndex].map(cell => ({
                            text: typeof (cell = (cell ?? '')) === 'string' ? cell : cell + '',
                            style: (rowIndex % 2) ? 'tableBodyEven' : 'tableBodyOdd'
                        })));
                    }

                    if ((config && config.footer) !== false && exportData.footer) {
                        body.push(exportData.footer.map(f => ({
                            text: typeof f === 'string' ? f : (f ?? '') + '',
                            style: 'tableFooter'
                        })));
                    }

                    const doc = {
                        pageSize: (config && config.pageSize) ? config.pageSize : 'A4',
                        pageOrientation: (config && config.orientation) ? config.orientation : 'portrait',
                        content: [{
                            table: {
                                headerRows: 1,
                                body
                            },
                            layout: 'noBorders'
                        }],
                        styles: {
                            tableHeader: { bold: true, fontSize: 11, color: 'white', fillColor: '#2d4154', alignment: 'center' },
                            tableBodyEven: {},
                            tableBodyOdd: { fillColor: '#f3f3f3' },
                            tableFooter: { bold: true, fontSize: 11, color: 'white', fillColor: '#2d4154' },
                            title: { alignment: 'center', fontSize: 15 },
                            message: {}
                        },
                        defaultStyle: { fontSize: 10 }
                    };

                    if (exportInfo.messageTop) {
                        doc.content.unshift({ text: exportInfo.messageTop, style: 'message', margin: [0, 0, 0, 12] });
                    }
                    if (exportInfo.messageBottom) {
                        doc.content.push({ text: exportInfo.messageBottom, style: 'message', margin: [0, 0, 0, 12] });
                    }
                    if (exportInfo.title) {
                        doc.content.unshift({ text: exportInfo.title, style: 'title', margin: [0, 0, 0, 12] });
                    }

                    if (config && typeof config.customize === 'function') {
                        config.customize(doc, config, dt);
                    }

                    return { doc, exportInfo };
                }

                function printPdfDocInHiddenFrame(doc) {
                    const pdfMake = (window.pdfMake)
                        ? window.pdfMake
                        : ($.fn.dataTable && $.fn.dataTable.Buttons && $.fn.dataTable.Buttons.pdfMake ? $.fn.dataTable.Buttons.pdfMake() : null);

                    if (!pdfMake || !pdfMake.createPdf) return false;

                    pdfMake.createPdf(doc).getBlob(function(blob) {
                        const url = URL.createObjectURL(blob);

                        let iframe = document.getElementById('dtPdfPrintFrame');
                        if (!iframe) {
                            iframe = document.createElement('iframe');
                            iframe.id = 'dtPdfPrintFrame';
                            iframe.style.position = 'fixed';
                            iframe.style.right = '0';
                            iframe.style.bottom = '0';
                            iframe.style.width = '0';
                            iframe.style.height = '0';
                            iframe.style.border = '0';
                            iframe.style.visibility = 'hidden';
                            iframe.setAttribute('aria-hidden', 'true');
                            document.body.appendChild(iframe);
                        }

                        const cleanup = () => {
                            try { URL.revokeObjectURL(url); } catch (_) {}
                            iframe.removeEventListener('load', onLoad);
                            iframe.removeEventListener('error', onError);
                        };

                        const onError = () => cleanup();
                        const onLoad = () => {
                            try {
                                iframe.contentWindow.focus();
                                iframe.contentWindow.print();
                            } finally {
                                setTimeout(cleanup, 1000);
                            }
                        };

                        iframe.addEventListener('load', onLoad);
                        iframe.addEventListener('error', onError);
                        iframe.src = url;
                    });

                    return true;
                }

                function addPdfRowNumbers(doc, label = '#') {
                    if (!doc || !Array.isArray(doc.content)) return;
                    const tableNode = doc.content.find(node => node && node.table && Array.isArray(node.table.body));
                    if (!tableNode) return;
                    const body = tableNode.table.body;
                    if (!Array.isArray(body) || body.length === 0) return;
                    if (body[0] && body[0][0] && body[0][0].text === label) return;

                    // Header row
                    if (Array.isArray(body[0])) {
                        body[0].unshift({ text: label, style: 'tableHeader' });
                    }

                    // Body rows (skip footer rows with tableFooter style)
                    let counter = 1;
                    for (let i = 1; i < body.length; i++) {
                        const row = body[i];
                        if (!Array.isArray(row)) continue;

                        const firstCellStyle = row[0] && row[0].style ? row[0].style : '';
                        const isFooterRow = firstCellStyle === 'tableFooter';

                        if (isFooterRow) {
                            row.unshift({ text: '', style: 'tableFooter' });
                            continue;
                        }

                        const zebraStyle = ((counter - 1) % 2) ? 'tableBodyEven' : 'tableBodyOdd';
                        row.unshift({ text: String(counter), style: zebraStyle });
                        counter++;
                    }
                }

                function printDataTableAsPdf(dt, tableTitle, fechaHora, extraConfig) {
                    try {
                        const cfg = {
                            title: `${tableTitle} - ${fechaHora}`,
                            orientation: 'landscape',
                            pageSize: 'A4',
                            header: true,
                            footer: false
                        };
                        if (extraConfig && typeof extraConfig === 'object') {
                            Object.assign(cfg, extraConfig);
                        }
                        const { doc } = buildPdfDocFromDataTable(dt, cfg);
                        addPdfRowNumbers(doc, '#');
                        return printPdfDocInHiddenFrame(doc);
                    } catch (e) {
                        return false;
                    }
                }

                function initDataTable(selector, tableTitle, options) {
                    const fechaHora = getFechaHoraActual();
                    const opts = options || {};
                    const useErpButtons = opts.buttonStyle === 'erp';
                    const showLength = opts.showLength !== false;
                    const dtConfig = {
                        deferRender: true,
                        autoWidth: false,
                        pageLength: 14,
                        lengthMenu: [[14, 25, 50, 100], [14, 25, 50, 100]],
                        order: [
                            [6, 'asc']
                        ],
                        dom: showLength
                            ? "<'row mb-3'<'col-md-6 d-flex align-items-center'lB><'col-md-6 d-flex justify-content-end'f>>" +
                              "<'row'<'col-12'tr>>" +
                              "<'row mt-2'<'col-md-6'i><'col-md-6'p>>"
                            : "<'row mb-3'<'col-md-6 d-flex'B><'col-md-6 d-flex justify-content-end'f>>" +
                            "<'row'<'col-12'tr>>" +
                            "<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                        buttons: [{
                                extend: 'excelHtml5',
                                title: `${tableTitle} - ${fechaHora}`,
                                text: '<i class="fas fa-file-excel"></i> Excel',
                                className: useErpButtons ? 'btn btn-erp-success btn-sm mx-1' : 'btn btn-success btn-sm mx-0',
                                filename: `${tableTitle.replace(/\s+/g, '_')}_${fechaHora.replace(/[/: ]/g, '_')}`
                            },
                            {
                                extend: 'pdfHtml5',
                                title: `${tableTitle} - ${fechaHora}`,
                                text: '<i class="fas fa-file-pdf"></i> PDF',
                                className: useErpButtons ? 'btn btn-erp-danger btn-sm mx-1' : 'btn btn-danger btn-sm mx-1',
                                orientation: 'landscape',
                                pageSize: 'A4',
                                filename: `${tableTitle.replace(/\s+/g, '_')}_${fechaHora.replace(/[/: ]/g, '_')}`,
                                customize: function(doc) {
                                    addPdfRowNumbers(doc, '#');
                                }
                            },
                            {
                                extend: 'print',
                                title: `${tableTitle} - ${fechaHora}`,
                                text: '<i class="fas fa-print"></i> Print',
                                className: useErpButtons ? 'btn btn-erp-primary btn-sm mx-1' : 'btn btn-primary btn-sm mx-1',
                                action: function(e, dt) {
                                    e.preventDefault();
                                    const printed = printDataTableAsPdf(dt, tableTitle, fechaHora);
                                    if (!printed) {
                                        printDataTableInPlace(dt, tableTitle, fechaHora);
                                    }
                                }
                            }
                        ],
                        searching: true,
                        lengthChange: showLength,
                    };

                    if (typeof opts.scrollX !== 'undefined') {
                        dtConfig.scrollX = !!opts.scrollX;
                    }

                    if (typeof opts.scrollCollapse !== 'undefined') {
                        dtConfig.scrollCollapse = !!opts.scrollCollapse;
                    }

                    if (typeof opts.scrollXInner !== 'undefined') {
                        dtConfig.scrollXInner = opts.scrollXInner;
                    }

                    if (typeof opts.pageLength === 'number') {
                        dtConfig.pageLength = opts.pageLength;
                    }

                    if (opts.lengthMenu) {
                        dtConfig.lengthMenu = opts.lengthMenu;
                    }

                    if (opts.columnDefs) {
                        dtConfig.columnDefs = opts.columnDefs;
                    }

                    if (opts.order) {
                        dtConfig.order = opts.order;
                    }

                    const dt = $(selector).DataTable(dtConfig);

                    if (opts.buttonsHost && dt.buttons && dt.buttons().container) {
                        const $host = $(opts.buttonsHost);
                        if ($host.length) {
                            $host.empty();
                            dt.buttons().container().appendTo($host);
                        }
                    }

                    return dt;
                }

                function updateVisibleInputs(typeSelect, yearInp, monthInp, weekInp) {
                    if (!yearInp || !monthInp || !weekInp) return;
                    yearInp.classList.add('d-none');
                    monthInp.classList.add('d-none');
                    weekInp.classList.add('d-none');

                    if (typeSelect.value === 'year') yearInp.classList.remove('d-none');
                    if (typeSelect.value === 'month') monthInp.classList.remove('d-none');
                    if (typeSelect.value === 'week') weekInp.classList.remove('d-none');
                }

                function printChart(canvasId, chartTitle, filterText = '') {
                    const canvas = document.getElementById(canvasId);
                    if (!canvas) return;

                    const dataUrl = canvas.toDataURL();
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(`
<html>
<head>
    <title>${chartTitle}</title>
    <style>
        body { text-align: center; font-family: Arial; padding: 20px; }
        h2 { margin-bottom: 20px; }
        .filter-info { margin-bottom: 15px; font-size: 14px; }
        img { max-width: 100%; }
    </style>
</head>
<body>
    <h2>${chartTitle}</h2>
    <div class="filter-info">${filterText}</div>
    <img src="${dataUrl}" />
    <script>window.onload = () => { window.print(); };<\/script>
</body>
</html>
    `);
                    printWindow.document.close();
                }

                function loadChartElements() {
                    const filterType = document.getElementById('filterType');
                    const yearInput = document.getElementById('yearInput');
                    const monthInput = document.getElementById('monthInput');
                    const weekInput = document.getElementById('weekInput');
                    const customerFilter = document.getElementById('customerFilter');
                    const ctx = document.getElementById('ordersChart')?.getContext('2d');
                    const chartRef = {
                        chart: null
                    };

                    function updateChart() {
                        let url = '/orders/summary';
                        let currentFilterText = '';

                        if (filterType.value === 'year') {
                            if (!yearInput?.value) return;
                            url += `/year/${yearInput.value}`;
                            currentFilterText = `Year: ${yearInput.value}`;
                        } else if (filterType.value === 'month') {
                            if (!monthInput?.value) return;
                            const [year, month] = monthInput.value.split('-');
                            url += `/month/${year}/${month}`;
                            currentFilterText = `Month: ${monthInput.value}`;
                        } else if (filterType.value === 'week') {
                            if (!weekInput?.value) return;
                            const [year, week] = weekInput.value.split('-W');
                            url += `/week/${year}/${week}`;
                            currentFilterText = `Week: ${weekInput.value}`;
                        }

                        if (customerFilter?.value) {
                            const separator = url.includes('?') ? '&' : '?';
                            url += `${separator}customer=${encodeURIComponent(customerFilter.value)}`;
                            currentFilterText += `<br>Customer: ${customerFilter.value}`;
                        }

                        fetch(url)
                            .then(res => res.json())
                            .then(({
                                labels,
                                data
                            }) => {
                                if (chartRef.chart) chartRef.chart.destroy();

                                const totalOrders = data.reduce((acc, val) => acc + val, 0);

                                chartRef.chart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels,
                                        datasets: [{
                                            label: `ORDERS (Total: ${totalOrders})`,
                                            data,
                                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                                            borderColor: 'rgba(75, 192, 192, 1)',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        plugins: {
                                            datalabels: {
                                                anchor: 'end',
                                                align: 'start',
                                                color: '#000',
                                                font: {
                                                    weight: 'bold',
                                                    size: 12
                                                },
                                                formatter: value => value
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    stepSize: 1
                                                }
                                            }
                                        }
                                    },
                                    plugins: [ChartDataLabels]
                                });

                                // Click para ver detalle en modal
                                chartRef.chart.canvas.onclick = function(evt) {
                                    const points = chartRef.chart.getElementsAtEventForMode(evt, 'nearest', {
                                        intersect: true
                                    }, false);
                                    if (!points.length) return;
                                    const idx = points[0].index;
                                    const params = {
                                        type: filterType.value,
                                        customer: customerFilter?.value || ''
                                    };
                                    let labelText = labels[idx] ?? 'Orders';

                                    if (filterType.value === 'year') {
                                        const label = labels[idx] ?? '';
                                        let monthNum = idx + 1;
                                        const parsed = Date.parse(`${label} 1, 2000`);
                                        if (!isNaN(parsed)) {
                                            monthNum = new Date(parsed).getMonth() + 1;
                                        }
                                        params.year = yearInput.value;
                                        params.month = monthNum.toString().padStart(2, '0');
                                        labelText = label || `Month ${monthNum}`;
                                    } else if (filterType.value === 'month') {
                                        const [y, m] = (monthInput.value || '').split('-');
                                        params.year = y;
                                        params.month = m;
                                        const dayNum = parseInt((labels[idx] || '').split(' ')[0], 10);
                                        if (!isNaN(dayNum)) params.day = dayNum;
                                        // Etiqueta con mes completo
                                        if (monthInput.value) {
                                            const [yy, mm] = monthInput.value.split('-');
                                            const fullMonth = new Date(`${yy}-${mm}-01`).toLocaleString('en-US', {
                                                month: 'long'
                                            });
                                            if (!isNaN(dayNum)) {
                                                labelText = `${dayNum} ${fullMonth}`;
                                            } else {
                                                labelText = fullMonth;
                                            }
                                        }
                                    } else if (filterType.value === 'week') {
                                        const [y, w] = (weekInput.value || '').split('-W');
                                        params.year = y;
                                        params.week = w;
                                        params.weekday = idx + 1; // 1-7
                                    }

                                    openOrdersDetailModal(params, `Orders - ${labelText}`);
                                };
                            });
                    }

                    if (filterType && yearInput && monthInput && weekInput) {
                        updateVisibleInputs(filterType, yearInput, monthInput, weekInput);
                        filterType.addEventListener('change', () => {
                            updateVisibleInputs(filterType, yearInput, monthInput, weekInput);
                            updateChart();
                        });
                    }

                    [yearInput, monthInput, weekInput, customerFilter].forEach(el => {
                        if (el) el.addEventListener('change', updateChart);
                    });

                    updateChart();

                    // Escuchar cambios de customer en modal de detalle
                    ordersModalCustomerEl?.addEventListener('change', refreshOrdersDetailByCustomer);
                }

                function loadCustomerChartElements() {
                    const filterType = document.getElementById('filterTypeCustomer');
                    const yearInput = document.getElementById('yearInputCustomer');
                    const monthInput = document.getElementById('monthInputCustomer');
                    const weekInput = document.getElementById('weekInputCustomer');
                    const ctx = document.getElementById('byCustomerChart')?.getContext('2d');
                    const chartRef = {
                        chart: null
                    };

                    function updateChart() {
                        let url = '/orders/summary/by-customer';

                        if (filterType.value === 'year') {
                            if (!yearInput?.value) return;
                            url += `/year/${yearInput.value}`;
                        } else if (filterType.value === 'month') {
                            if (!monthInput?.value) return;
                            const [year, month] = monthInput.value.split('-');
                            url += `/month/${year}/${month}`;
                        } else if (filterType.value === 'week') {
                            if (!weekInput?.value) return;
                            const [year, week] = weekInput.value.split('-W');
                            url += `/week/${year}/${week}`;
                        }

                        fetch(url)
                            .then(res => res.json())
                            .then(({
                                labels,
                                totals,
                                totalAll
                            }) => {
                                if (chartRef.chart) chartRef.chart.destroy();

                                chartRef.chart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels,
                                        datasets: [{
                                            label: `ORDERS PER CUSTOMER (Total: ${totalAll})`,
                                            data: totals,
                                            backgroundColor: 'rgba(153, 102, 255, 0.7)',
                                            borderColor: 'rgba(153, 102, 255, 1)',
                                            borderWidth: 1,
                                            yAxisID: 'y',
                                        }]
                                    },
                                    options: {
                                        plugins: {
                                            datalabels: {
                                                anchor: 'end',
                                                align: 'start',
                                                color: '#000',
                                                font: {
                                                    weight: 'bold',
                                                    size: 12
                                                },
                                                formatter: value => value
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    stepSize: 1
                                                }
                                            }
                                        }
                                    },
                                    plugins: [ChartDataLabels]
                                });
                            });
                    }

                    if (filterType && yearInput && monthInput && weekInput) {
                        updateVisibleInputs(filterType, yearInput, monthInput, weekInput);
                        filterType.addEventListener('change', () => {
                            updateVisibleInputs(filterType, yearInput, monthInput, weekInput);
                            updateChart();
                        });
                    }

                    [yearInput, monthInput, weekInput].forEach(el => {
                        if (el) el.addEventListener('change', updateChart);
                    });

                    updateChart();
                }

                /**
                 * =================================================================
                 * ≡ƒƒú≡ƒôèGr├ífico: Orders Due- NExt 8 weeks (summaryNextWeeks)
                 */
                document.addEventListener('DOMContentLoaded', () => {
                    const ctx = document.getElementById('nextWeeksChart')?.getContext('2d');
                    const chartRef = {
                        chart: null
                    };

                    if (!ctx) return;

                    fetch('/orders/summary/next-weeks/8')
                        .then(res => res.json())
                        .then(({
                            labels,
                            total,
                            sent
                        }) => {
                            const totalOrders = total.reduce((acc, val) => acc + val, 0);

                            if (chartRef.chart) chartRef.chart.destroy();

                            chartRef.chart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels,
                                    datasets: [{
                                            label: `Total Orders`,
                                            data: total,
                                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1
                                        },
                                        {
                                            label: 'Sent Orders',
                                            data: sent,
                                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                                            borderColor: 'rgba(75, 192, 192, 1)',
                                            borderWidth: 1
                                        }
                                    ]
                                },
                                options: {
                                    plugins: {
                                        datalabels: {
                                            anchor: 'end',
                                            align: 'start',
                                            color: '#000',
                                            font: {
                                                weight: 'bold',
                                                size: 14
                                            },
                                            formatter: value => value
                                        }
                                    },
                                    responsive: true,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                stepSize: 1
                                            }
                                        }
                                    }
                                },
                                plugins: [ChartDataLabels]
                            });
                        })

                    /**
                     * ===================================================================================
                     * ≡ƒƒú≡ƒôèGr├ífico: entregas a tiempo vs tarde con filtros (M├⌐todo summaryOnTimeFiltered)
                     */
                    const onTimeCtx = document.getElementById('onTimeChart')?.getContext('2d');
                    const monthFilter = document.getElementById('monthFilter');
                    const yearFilter = document.getElementById('yearFilter');
                    const customerFilterOnTime = document.getElementById('customerFilterOnTime');
                    const modalEl = document.getElementById('onTimeModal');
                    const modalContentEl = document.getElementById('onTimeModalContent');
                    const modalTitleEl = document.getElementById('onTimeModalLabel');
                    const modalMonthEl = document.getElementById('onTimeModalMonth');
                    const modalCustomerEl = document.getElementById('onTimeModalCustomer');
                    const modalState = {
                        status: null,
                        baseFilters: {}
                    };

                    const onTimeChartRef = {
                        chart: null
                    };

                    // Registrar plugin de datalabels (una sola vez)
                    if (typeof Chart !== 'undefined' && typeof ChartDataLabels !== 'undefined') {
                        Chart.register(ChartDataLabels);
                    }

                    function fetchModalData(status, filters) {
                        if (!modalEl || !modalContentEl) return;
                        modalContentEl.innerHTML = '<div class="text-center text-muted py-4">Loading...</div>';
                        const params = new URLSearchParams(filters || {});
                        params.append('status', status);
                        fetch(`/orders/summary/on-time-filtered-detail?${params.toString()}`)
                            .then(res => res.text())
                            .then(html => {
                                modalContentEl.innerHTML = html || '<div class="text-center text-muted py-4">No data</div>';
                                // Inicializar DataTable para buscar/paginar
                                const $table = $('#onTimeDetailTable');
                                if ($.fn.DataTable.isDataTable($table)) {
                                    $table.DataTable().destroy();
                                }
                                if ($table.length) {
                                    // Construir texto de filtros para exportes (On Time modal)
                                    let filtersText = '';
                                    if (filters) {
                                        const parts = [];
                                        if (filters.year) parts.push(`Year: ${filters.year}`);
                                        if (filters.month) {
                                            const [yy, mm] = filters.month.split('-');
                                            const fullMonth = new Date(`${yy || new Date().getFullYear()}-${mm}-01`).toLocaleString('en-US', {
                                                month: 'long'
                                            });
                                            parts.push(`Month: ${filters.month} (${fullMonth})`);
                                        }
                                        if (filters.customer) parts.push(`Customer: ${filters.customer}`);
                                        filtersText = parts.join(' | ');
                                    }
                                    const exportTitle = `Schedule Statistics - ${modalTitleEl?.textContent || 'Orders detail'}`;
                                    const exportFilename = `${exportTitle}${filtersText ? '_' + filtersText : ''}`.replace(/[\\/:*?"<>| ]+/g, '_');
                                    const dt = $table.DataTable({
                                        dom: "<'row mb-0'<'col-sm-6 d-flex align-items-center'l><'col-sm-6 d-flex justify-content-end align-items-center'f>>" +
                                            "<'row'<'col-12'tr>>" +
                                            "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
                                        pageLength: 14,
                                        lengthMenu: [14, 25, 50, 100],
                                        searching: true,
                                        ordering: true,
                                        info: true,
                                        order: [
                                            [6, 'desc']
                                        ], // Sent column (index 6)
                                        buttons: [{
                                                extend: 'excelHtml5',
                                                text: '<i class="fas fa-file-excel"></i> Excel',
                                                className: 'btn btn-erp-success btn-sm mx-1',
                                                title: exportTitle,
                                                filename: exportFilename,
                                                messageTop: filtersText || null
                                            },
                                            {
                                                extend: 'pdfHtml5',
                                                text: '<i class="fas fa-file-pdf"></i> PDF',
                                                className: 'btn btn-erp-danger btn-sm mx-1',
                                                orientation: 'landscape',
                                                pageSize: 'A4',
                                                title: exportTitle,
                                                filename: exportFilename,
                                                messageTop: filtersText || null,
                                                customize: function(doc) {
                                                    addPdfRowNumbers(doc, '#');
                                                }
                                            },
                                            {
                                                extend: 'print',
                                                text: '<i class="fas fa-print"></i> Print',
                                                className: 'btn btn-erp-primary btn-sm mx-1',
                                                action: function(e, dt) {
                                                    e.preventDefault();
                                                    const printed = printDataTableAsPdf(dt, exportTitle, getFechaHoraActual(), {
                                                        title: exportTitle,
                                                        messageTop: filtersText || null,
                                                        orientation: 'landscape',
                                                        pageSize: 'A4'
                                                    });
                                                    if (!printed) {
                                                        printDataTableInPlace(dt, exportTitle, getFechaHoraActual());
                                                    }
                                                }
                                            }
                                        ]
                                    });
                                    // Mover botones junto a los filtros del modal
                                    if (dt.buttons().container().length) {
                                        const $btnHost = $('#onTimeModalButtons');
                                        $btnHost.empty(); // evita duplicados en recargas
                                        dt.buttons().container().appendTo($btnHost);
                                    }
                                }
                                populateModalCustomers(filters?.customer || '');
                                $('#onTimeModal').modal('show');
                            })
                            .catch(() => {
                                modalContentEl.innerHTML = '<div class="text-center text-danger py-4">Error loading data</div>';
                                $('#onTimeModal').modal('show');
                            });
                    }

                    function openOnTimeModal(status, filters) {
                        if (!modalEl || !modalContentEl) return;
                        // limpiar clases de estado previas
                        modalEl.classList.remove('status-early', 'status-on-time', 'status-late');
                        const key = (status || '').toLowerCase().replace(/\s+/g, '-');
                        if (key === 'early') modalEl.classList.add('status-early');
                        if (key === 'on-time') modalEl.classList.add('status-on-time');
                        if (key === 'late') modalEl.classList.add('status-late');
                        if (modalTitleEl) modalTitleEl.textContent = `Orders - ${status}`;
                        modalState.status = status;
                        modalState.baseFilters = {
                            ...(filters || {})
                        };

                        // Prefijar selects
                        if (modalMonthEl) modalMonthEl.value = filters?.month || '';
                        if (modalCustomerEl) modalCustomerEl.value = filters?.customer || '';

                        const effectiveFilters = {
                            ...(filters || {})
                        };
                        if (modalMonthEl && modalMonthEl.value) effectiveFilters.month = modalMonthEl.value;
                        if (modalCustomerEl && modalCustomerEl.value) effectiveFilters.customer = modalCustomerEl.value;
                        fetchModalData(status, effectiveFilters);
                    }

                    function loadOnTimeChart() {
                        if (!onTimeCtx) return;

                        const month = monthFilter?.value;
                        const year = yearFilter?.value;
                        const customer = customerFilterOnTime?.value;

                        let displayMonth = '';

                        if (month) {
                            const [yearStr, monthNum] = month.split('-'); // "2025-07"
                            const monthNames = [
                                'January', 'February', 'March', 'April', 'May', 'June',
                                'July', 'August', 'September', 'October', 'November', 'December'
                            ];
                            const index = parseInt(monthNum, 10) - 1;

                            if (index >= 0 && index < 12) {
                                displayMonth = monthNames[index];
                            }
                        }

                        let url = '/orders/summary/on-time-filtered';
                        const params = new URLSearchParams();

                        if (month) params.append('month', month);
                        if (year) params.append('year', year);
                        if (customer) params.append('customer', customer);

                        if (params.toString()) url += `?${params.toString()}`;
                        //console.log('≡ƒöù URL:', url);

                        fetch(url)
                            .then(res => res.json())
                            .then(({
                                labels,
                                data,
                                total,
                                selectedCustomer,
                                selectedYear
                            }) => {
                                // ≡ƒöó Asegurar NUM├ëRICOS
                                const numericData = (data || []).map(v => Number(v) || 0);

                                if (onTimeChartRef.chart) {
                                    onTimeChartRef.chart.destroy();
                                }

                                const colorMap = {
                                    'Early': '#007bff',
                                    'On Time': '#28a745',
                                    'Late': '#dc3545'
                                };

                                const totalOrders =
                                    total !== undefined && total !== null ?
                                    Number(total) :
                                    numericData.reduce((a, b) => a + b, 0);

                                const displayCustomer = selectedCustomer ?
                                    selectedCustomer.charAt(0).toUpperCase() +
                                    selectedCustomer.slice(1).toLowerCase() :
                                    'All';

                                const displayYear = selectedYear || '';
                                const titleParts = [`Total Orders: ${totalOrders}`];
                                if (displayCustomer !== 'All') titleParts.push(`Customer: ${displayCustomer}`);
                                if (displayYear) titleParts.push(`Year: ${displayYear}`);
                                if (displayMonth) titleParts.push(`Month: ${displayMonth}`);
                                const fullTitle = titleParts.join(' | ');

                                const colors = labels.map(label => colorMap[label] || '#999');

                                const filters = {
                                    month: month || '',
                                    year: year || '',
                                    customer: customer || ''
                                };

                                onTimeChartRef.chart = new Chart(onTimeCtx, {
                                    type: 'doughnut',
                                    data: {
                                        labels,
                                        datasets: [{
                                            data: numericData,
                                            backgroundColor: colors,
                                            borderColor: '#fff',
                                            borderWidth: 2
                                        }]
                                    },
                                    options: {
                                        maintainAspectRatio: false,
                                        plugins: {
                                            title: {
                                                display: true,
                                                text: fullTitle,
                                                font: {
                                                    size: 18,
                                                    weight: 'bold'
                                                },
                                                color: '#333'
                                            },
                                            datalabels: {
                                                color: '#000',
                                                font: {
                                                    weight: 'bold',
                                                    size: 14
                                                },
                                                formatter: (value, context) => {
                                                    const dataset = context.chart.data.datasets[0];

                                                    // ≡ƒæç AQUI el FIX: asegurar que value sea n├║mero
                                                    const numericValue = Number(value) || 0;

                                                    const sum = dataset.data.reduce(
                                                        (a, b) => a + Number(b || 0),
                                                        0
                                                    );

                                                    const percent = sum ?
                                                        Math.round((numericValue / sum) * 100) :
                                                        0;

                                                    return `${numericValue} (${percent}%)`;
                                                }
                                            },
                                            legend: {
                                                labels: {
                                                    color: '#000',
                                                    font: {
                                                        size: 14
                                                    }
                                                }
                                            }
                                        }
                                    },
                                    plugins: [ChartDataLabels]
                                });

                                // click handler para abrir modal de detalle
                                onTimeChartRef.chart.canvas.onclick = function(evt) {
                                    const points = onTimeChartRef.chart.getElementsAtEventForMode(evt, 'nearest', {
                                        intersect: true
                                    }, false);
                                    if (!points.length) return;
                                    const idx = points[0].index;
                                    const status = labels[idx];
                                    openOnTimeModal(status, filters);
                                };
                            })
                            .catch(err => {
                                console.error('Error cargando On Time chart:', err);
                            });
                    }

                    // Cargar al iniciar
                    if (onTimeCtx) loadOnTimeChart();

                    // Escuchar cambios en filtros
                    monthFilter?.addEventListener('change', loadOnTimeChart);
                    yearFilter?.addEventListener('change', loadOnTimeChart);
                    customerFilterOnTime?.addEventListener('change', loadOnTimeChart);

                    // Filtros dentro del modal (cambian el detalle sin cerrar)
                    const handleModalFilterChange = () => {
                        if (!modalState.status) return;
                        const effective = {
                            ...(modalState.baseFilters || {})
                        };
                        if (modalMonthEl && modalMonthEl.value) effective.month = modalMonthEl.value;
                        else delete effective.month;
                        if (modalCustomerEl && modalCustomerEl.value) effective.customer = modalCustomerEl.value;
                        else delete effective.customer;
                        fetchModalData(modalState.status, effective);
                    };
                    modalMonthEl?.addEventListener('change', handleModalFilterChange);
                    modalCustomerEl?.addEventListener('change', handleModalFilterChange);

                    // Rellena el select de customers con los que aparecen en la tabla del modal
                    function populateModalCustomers(selected) {
                        if (!modalCustomerEl) return;
                        const tableBody = document.querySelector('#onTimeDetailTable tbody');
                        if (!tableBody) return;
                        const customers = new Set();
                        tableBody.querySelectorAll('tr').forEach(tr => {
                            const td = tr.children[3];
                            if (td) {
                                const name = (td.textContent || '').trim();
                                if (name) customers.add(name);
                            }
                        });
                        const prev = modalCustomerEl.value;
                        modalCustomerEl.innerHTML = '<option value="">-- All Customers --</option>';
                        customers.forEach(name => {
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = name;
                            modalCustomerEl.appendChild(opt);
                        });
                        if (selected && customers.has(selected)) {
                            modalCustomerEl.value = selected;
                        } else if (customers.has(prev)) {
                            modalCustomerEl.value = prev;
                        } else {
                            modalCustomerEl.value = '';
                        }
                    }
                });;


                $(document).ready(function() {
                    let weekTableDt = initDataTable('#tableweek', 'ORDERS THIS WEEK');
                    initDataTable('#tablelate', 'LATE ORDERS');
                    loadChartElements();
                    loadCustomerChartElements();

                    // Cache de datos para abrir el modal "Orders This Week" más rápido
                    let weekOrdersCachedRows = null;
                    function refreshWeekOrdersCache() {
                        if ($.fn.DataTable.isDataTable('#tableweek')) {
                            const srcDt = $('#tableweek').DataTable();
                            weekOrdersCachedRows = srcDt.rows({ search: 'applied', order: 'applied' }).data().toArray();
                        } else {
                            weekOrdersCachedRows = $('#tableweek tbody tr').toArray().map(tr => {
                                const tds = $(tr).find('td').toArray();
                                return tds.map(td => $(td).html());
                            });
                        }
                    }
                    refreshWeekOrdersCache();
                    try {
                        weekTableDt.on('draw', refreshWeekOrdersCache);
                    } catch (e) {}

                    // KPI active highlight (por color del icono)
                    $(document).on('click', '.kpi-erp .info-box[role="button"]', function() {
                        $('.kpi-erp .info-box').removeClass('is-active');
                        $(this).addClass('is-active');
                    });

                    $(document).on('keydown', '.kpi-erp .info-box[role="button"]', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            $(this).trigger('click');
                        }
                    });

                    // Pre-inicializa el DataTable del modal en segundo plano (abre casi instantáneo)
                    setTimeout(() => {
                        try {
                            ensureWeekOrdersDtInitialized();
                            syncWeekOrdersDtFromCache();
                        } catch (e) {}
                    }, 150);

                    // Late Orders KPI -> modal detail (similar UX to OnTimeModal)
                    const $lateOrdersModal = $('#lateOrdersModal');
                    const $lateOrdersTrigger = $('.js-open-late-orders');
                    const $lateOrdersLoading = $('#lateOrdersModalLoading');
                    const $lateOrdersCustomer = $('#lateOrdersModalCustomer');
                    const $lateOrdersStatus = $('#lateOrdersModalStatus');
                    const $lateOrdersCount = $('#lateOrdersModalCount');
                    let lateOrdersDt = null;
                    let lateOrdersDidAdjust = false;

                    function escapeRegex(value) {
                        return (value || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    }

                    function updateFilteredCount(dt, $el) {
                        if (!dt || !$el || !$el.length) return;
                        const total = dt.rows().count();
                        const filtered = dt.rows({ search: 'applied' }).count();
                        $el.text(`Total: ${filtered} / ${total}`);
                    }

                    function populateLateOrdersFilters(dt) {
                        if (!dt) return;
                        const customerValues = dt.column(3).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);
                        const statusValues = dt.column(5).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);

                        const uniqCustomers = Array.from(new Set(customerValues)).sort((a, b) => a.localeCompare(b));
                        const uniqStatuses = Array.from(new Set(statusValues)).sort((a, b) => a.localeCompare(b));

                        const prevCustomer = $lateOrdersCustomer.val();
                        const prevStatus = $lateOrdersStatus.val();

                        $lateOrdersCustomer.empty().append('<option value="">-- All Customers --</option>');
                        uniqCustomers.forEach(name => {
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = name;
                            $lateOrdersCustomer.append(opt);
                        });

                        $lateOrdersStatus.empty().append('<option value="">-- All Status --</option>');
                        uniqStatuses.forEach(status => {
                            const opt = document.createElement('option');
                            opt.value = status;
                            opt.textContent = status;
                            $lateOrdersStatus.append(opt);
                        });

                        if (prevCustomer && uniqCustomers.includes(prevCustomer)) $lateOrdersCustomer.val(prevCustomer);
                        if (prevStatus && uniqStatuses.includes(prevStatus)) $lateOrdersStatus.val(prevStatus);
                    }

                    function ensureLateOrdersDtInitialized() {
                        if (lateOrdersDt) return lateOrdersDt;
                        const tableSelector = '#lateOrdersModalTable';
                        lateOrdersDt = initDataTable(tableSelector, 'LATE ORDERS', {
                            buttonsHost: '#lateOrdersModalButtons',
                            buttonStyle: 'erp',
                            // Status column has a badge (HTML); use plain text for filtering/sorting
                            columnDefs: [{
                                targets: [5],
                                render: function(data, type) {
                                    if (type === 'filter' || type === 'sort') {
                                        return $('<div>').html(data).text().trim();
                                    }
                                    return data;
                                }
                            }]
                        });
                        return lateOrdersDt;
                    }

                    const KPI_THEME_CLASSES = 'fai-theme-info fai-theme-primary fai-theme-success fai-theme-danger fai-theme-warning fai-theme-secondary';
                    function applyModalTheme($modal, $trigger) {
                        if (!$modal || !$modal.length || !$trigger || !$trigger.length) return;
                        const classes = ($trigger.attr('class') || '').split(/\s+/);
                        const themeClass = classes.find(c => c && c.indexOf('fai-theme-') === 0);
                        $modal.removeClass(KPI_THEME_CLASSES);
                        if (themeClass) $modal.addClass(themeClass);
                    }

                    function openLateOrdersModal() {
                        applyModalTheme($lateOrdersModal, $lateOrdersTrigger);
                        const lateDefaultText = ($lateOrdersCount.data('default') || '').toString();
                        if (lateDefaultText) $lateOrdersCount.text(lateDefaultText);
                        const isReady = !!(lateOrdersDt && lateOrdersDt.__isSized && lateOrdersDt.__lastW === window.innerWidth);
                        if (!isReady) {
                            $lateOrdersModal.addClass('is-loading');
                            $lateOrdersLoading.removeClass('d-none');
                        } else {
                            $lateOrdersModal.removeClass('is-loading');
                            $lateOrdersLoading.addClass('d-none');
                        }
                        $lateOrdersModal.modal('show');
                    }

                    $lateOrdersTrigger.on('click', openLateOrdersModal);
                    $lateOrdersTrigger.on('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            openLateOrdersModal();
                        }
                    });

                    $lateOrdersModal.on('shown.bs.modal', function() {
                        const dt = ensureLateOrdersDtInitialized();
                        requestAnimationFrame(() => {
                            try { dt.columns.adjust().draw(false); } catch (e) {}
                            dt.__isSized = true;
                            dt.__lastW = window.innerWidth;
                            $lateOrdersModal.removeClass('is-loading');
                            $lateOrdersLoading.addClass('d-none');
                        });
                        if (!dt.__countBound) {
                            dt.__countBound = true;
                            dt.on('draw', function() { updateFilteredCount(dt, $lateOrdersCount); });
                        }
                        populateLateOrdersFilters(dt);
                        updateFilteredCount(dt, $lateOrdersCount);
                    });

                    function applyLateOrdersFilters() {
                        const tableSelector = '#lateOrdersModalTable';
                        if (!$.fn.DataTable.isDataTable(tableSelector)) return;
                        const dt = $(tableSelector).DataTable();

                        const customer = ($lateOrdersCustomer.val() || '').trim();
                        const status = ($lateOrdersStatus.val() || '').trim();

                        dt.column(3).search(customer ? `^${escapeRegex(customer)}$` : '', true, false);
                        dt.column(5).search(status ? `^${escapeRegex(status)}$` : '', true, false);
                        dt.draw();
                    }

                    $lateOrdersCustomer.on('change', applyLateOrdersFilters);
                    $lateOrdersStatus.on('change', applyLateOrdersFilters);

                    $lateOrdersModal.on('hidden.bs.modal', function() {
                        $lateOrdersCustomer.val('');
                        $lateOrdersStatus.val('');
                        applyLateOrdersFilters();
                        const lateDefaultText = ($lateOrdersCount.data('default') || '').toString();
                        $lateOrdersCount.text(lateDefaultText);
                        $lateOrdersTrigger.removeClass('is-active');
                        $lateOrdersModal.removeClass('is-loading');
                        $lateOrdersLoading.addClass('d-none');
                    });

                    // Pre-inicializa el DataTable en segundo plano (abre casi instantáneo)
                    // Nota: no pre-inicializar (evita cálculos de anchos estando oculto)

                    // Orders This Week KPI -> modal detail (clona el contenido actual de #tableweek)
                    const $weekOrdersModal = $('#weekOrdersModal');
                    const $weekOrdersLoading = $('#weekOrdersModalLoading');
                    const $weekOrdersButtons = $('#weekOrdersModalButtons');
                    const $weekOrdersCustomer = $('#weekOrdersModalCustomer');
                    const $weekOrdersStatus = $('#weekOrdersModalStatus');
                    const $weekOrdersCount = $('#weekOrdersModalCount');
                    let weekOrdersDt = null;
                    let weekOrdersDidAdjust = false;
                    let weekOrdersLastSignature = null;

                    function getWeekOrdersSignature(rowsArr) {
                        if (!rowsArr || !rowsArr.length) return '0|';
                        const first = rowsArr[0];
                        const last = rowsArr[rowsArr.length - 1];
                        const firstId = Array.isArray(first) ? (first[0] ?? '') : '';
                        const lastId = Array.isArray(last) ? (last[0] ?? '') : '';
                        const firstText = $('<div>').html(firstId).text().trim();
                        const lastText = $('<div>').html(lastId).text().trim();
                        return `${rowsArr.length}|${firstText}|${lastText}`;
                    }

                    function ensureWeekOrdersDtInitialized() {
                        if (weekOrdersDt) return weekOrdersDt;
                        const tableSelector = '#weekOrdersModalTable';
                        $(`${tableSelector} tbody`).empty();
                        weekOrdersDt = initDataTable(tableSelector, 'ORDERS THIS WEEK', {
                            buttonsHost: '#weekOrdersModalButtons',
                            buttonStyle: 'erp',
                            order: [[6, 'asc']],
                            // Status column has badge HTML; use plain text for filtering/sorting
                            columnDefs: [{
                                targets: [5],
                                className: 'text-center',
                                render: function(data, type) {
                                    if (type === 'filter' || type === 'sort') {
                                        return $('<div>').html(data).text().trim();
                                    }
                                    return data;
                                }
                            }, {
                                targets: [4, 6, 7, 8, 9],
                                className: 'text-center'
                            }, {
                                targets: [0, 6, 7],
                                className: 'text-nowrap'
                            }]
                        });
                        return weekOrdersDt;
                    }

                    function syncWeekOrdersDtFromCache() {
                        if (!weekOrdersDt) return;
                        const rows = (weekOrdersCachedRows && weekOrdersCachedRows.length) ? weekOrdersCachedRows : [];
                        const sig = getWeekOrdersSignature(rows);
                        if (sig === weekOrdersLastSignature) return;

                        weekOrdersLastSignature = sig;
                        weekOrdersDt.clear();
                        if (rows.length) {
                            weekOrdersDt.rows.add(rows);
                        }
                        weekOrdersDt.draw(false);
                        populateWeekOrdersFilters(weekOrdersDt);
                    }

                    const $weekOrdersTrigger = $('.kpi-erp .info-box').filter(function() {
                        return ($(this).find('.info-box-text').text() || '').trim() === 'Orders This Week';
                    }).first();

                    if ($weekOrdersTrigger.length) {
                        $weekOrdersTrigger
                            .addClass('js-open-week-orders')
                            .attr('role', 'button')
                            .attr('tabindex', '0')
                            .attr('aria-label', 'Open Orders This Week detail');
                    }

                    function openWeekOrdersModal() {
                        applyModalTheme($weekOrdersModal, $weekOrdersTrigger);
                        const rows = (weekOrdersCachedRows && weekOrdersCachedRows.length) ? weekOrdersCachedRows : [];
                        const weekDefaultText = `Total: ${rows.length} / ${rows.length}`;
                        $weekOrdersCount.data('default', weekDefaultText);
                        $weekOrdersCount.text(weekDefaultText);
                        const isReady = weekOrdersDt && (getWeekOrdersSignature(rows) === weekOrdersLastSignature);
                        if (!isReady) {
                            $weekOrdersModal.addClass('is-loading');
                            $weekOrdersLoading.removeClass('d-none');
                        } else {
                            $weekOrdersModal.removeClass('is-loading');
                            $weekOrdersLoading.addClass('d-none');
                        }
                        $weekOrdersModal.modal('show');
                    }

                    $(document).on('click', '.js-open-week-orders', openWeekOrdersModal);
                    $(document).on('keydown', '.js-open-week-orders', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            openWeekOrdersModal();
                        }
                    });

                    function populateWeekOrdersFilters(dt) {
                        if (!dt) return;
                        const customerValues = dt.column(3).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);
                        const statusValues = dt.column(5).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);

                        const uniqCustomers = Array.from(new Set(customerValues)).sort((a, b) => a.localeCompare(b));
                        const uniqStatuses = Array.from(new Set(statusValues)).sort((a, b) => a.localeCompare(b));

                        const prevCustomer = $weekOrdersCustomer.val();
                        const prevStatus = $weekOrdersStatus.val();

                        $weekOrdersCustomer.empty().append('<option value="">-- All Customers --</option>');
                        uniqCustomers.forEach(name => {
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = name;
                            $weekOrdersCustomer.append(opt);
                        });

                        $weekOrdersStatus.empty().append('<option value="">-- All Status --</option>');
                        uniqStatuses.forEach(status => {
                            const opt = document.createElement('option');
                            opt.value = status;
                            opt.textContent = status;
                            $weekOrdersStatus.append(opt);
                        });

                        if (prevCustomer && uniqCustomers.includes(prevCustomer)) $weekOrdersCustomer.val(prevCustomer);
                        if (prevStatus && uniqStatuses.includes(prevStatus)) $weekOrdersStatus.val(prevStatus);
                    }

                    function applyWeekOrdersFilters() {
                        const tableSelector = '#weekOrdersModalTable';
                        if (!$.fn.DataTable.isDataTable(tableSelector)) return;
                        const dt = $(tableSelector).DataTable();

                        const customer = ($weekOrdersCustomer.val() || '').trim();
                        const status = ($weekOrdersStatus.val() || '').trim();

                        dt.column(3).search(customer ? `^${escapeRegex(customer)}$` : '', true, false);
                        dt.column(5).search(status ? `^${escapeRegex(status)}$` : '', true, false);
                        dt.draw();
                    }

                    $weekOrdersCustomer.on('change', applyWeekOrdersFilters);
                    $weekOrdersStatus.on('change', applyWeekOrdersFilters);

                    $weekOrdersModal.on('shown.bs.modal', function() {
                        ensureWeekOrdersDtInitialized();
                        syncWeekOrdersDtFromCache();
                        if (weekOrdersDt && !weekOrdersDt.__countBound) {
                            weekOrdersDt.__countBound = true;
                            weekOrdersDt.on('draw', function() { updateFilteredCount(weekOrdersDt, $weekOrdersCount); });
                        }
                        updateFilteredCount(weekOrdersDt, $weekOrdersCount);

                        // Ajustar columnas ya visible
                        if (!weekOrdersDidAdjust) {
                            weekOrdersDidAdjust = true;
                            setTimeout(() => {
                                try { weekOrdersDt.columns.adjust(); } catch (e) {}
                            }, 0);
                        }

                        $weekOrdersButtons.toggleClass('d-none', false);
                        $weekOrdersModal.removeClass('is-loading');
                        $weekOrdersLoading.addClass('d-none');
                    });

                    $weekOrdersModal.on('hidden.bs.modal', function() {
                        $weekOrdersCustomer.val('');
                        $weekOrdersStatus.val('');
                        if (weekOrdersDt) {
                            weekOrdersDt.column(3).search('', true, false);
                            weekOrdersDt.column(5).search('', true, false);
                            weekOrdersDt.draw(false);
                        }
                        const weekDefaultText = ($weekOrdersCount.data('default') || '').toString();
                        $weekOrdersCount.text(weekDefaultText);
                        $weekOrdersTrigger.removeClass('is-active');
                    });

                    // New Orders This Week KPI -> modal detail
                    const $newOrdersWeekModal = $('#newOrdersWeekModal');
                    const $newOrdersWeekLoading = $('#newOrdersWeekModalLoading');
                    const $newOrdersWeekCustomer = $('#newOrdersWeekModalCustomer');
                    const $newOrdersWeekStatus = $('#newOrdersWeekModalStatus');
                    const $newOrdersWeekUploaded = $('#newOrdersWeekModalUploaded');
                    const $newOrdersWeekCount = $('#newOrdersWeekModalCount');
                    let newOrdersWeekDt = null;
                    let newOrdersWeekDidAdjust = false;

                    const $newOrdersWeekTrigger = $('.kpi-erp .info-box').filter(function() {
                        return ($(this).find('.info-box-text').text() || '').trim() === 'New Orders This Week';
                    }).first();

                    if ($newOrdersWeekTrigger.length) {
                        $newOrdersWeekTrigger
                            .addClass('js-open-new-orders-week')
                            .attr('role', 'button')
                            .attr('tabindex', '0')
                            .attr('aria-label', 'Open New Orders This Week detail');
                    }

                    function populateNewOrdersWeekFilters(dt) {
                        if (!dt) return;
                        const customerValues = dt.column(3).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);
                        const statusValues = dt.column(5).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);
                        const uploadedValues = dt.column(6).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);

                        const uniqCustomers = Array.from(new Set(customerValues)).sort((a, b) => a.localeCompare(b));
                        const uniqStatuses = Array.from(new Set(statusValues)).sort((a, b) => a.localeCompare(b));

                        const uniqUploaded = Array.from(new Set(uploadedValues)).sort((a, b) => a.localeCompare(b));

                        const prevCustomer = $newOrdersWeekCustomer.val();
                        const prevStatus = $newOrdersWeekStatus.val();
                        const prevUploaded = $newOrdersWeekUploaded.val();

                        $newOrdersWeekCustomer.empty().append('<option value=\"\">-- All Customers --</option>');
                        uniqCustomers.forEach(name => {
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = name;
                            $newOrdersWeekCustomer.append(opt);
                        });

                        $newOrdersWeekStatus.empty().append('<option value=\"\">-- All Status --</option>');
                        uniqStatuses.forEach(status => {
                            const opt = document.createElement('option');
                            opt.value = status;
                            opt.textContent = status;
                            $newOrdersWeekStatus.append(opt);
                        });

                        $newOrdersWeekUploaded.empty().append('<option value=\"\">-- All Uploaded --</option>');
                        uniqUploaded.forEach(value => {
                            const opt = document.createElement('option');
                            opt.value = value;
                            opt.textContent = value;
                            $newOrdersWeekUploaded.append(opt);
                        });

                        if (prevCustomer && uniqCustomers.includes(prevCustomer)) $newOrdersWeekCustomer.val(prevCustomer);
                        if (prevStatus && uniqStatuses.includes(prevStatus)) $newOrdersWeekStatus.val(prevStatus);
                        if (prevUploaded && uniqUploaded.includes(prevUploaded)) $newOrdersWeekUploaded.val(prevUploaded);
                    }

                    function applyNewOrdersWeekFilters() {
                        const tableSelector = '#newOrdersWeekModalTable';
                        if (!$.fn.DataTable.isDataTable(tableSelector)) return;
                        const dt = $(tableSelector).DataTable();

                        const customer = ($newOrdersWeekCustomer.val() || '').trim();
                        const status = ($newOrdersWeekStatus.val() || '').trim();
                        const uploaded = ($newOrdersWeekUploaded.val() || '').trim();

                        dt.column(3).search(customer ? `^${escapeRegex(customer)}$` : '', true, false);
                        dt.column(5).search(status ? `^${escapeRegex(status)}$` : '', true, false);
                        dt.column(6).search(uploaded ? `^${escapeRegex(uploaded)}$` : '', true, false);
                        dt.draw();
                    }

                    $newOrdersWeekCustomer.on('change', applyNewOrdersWeekFilters);
                    $newOrdersWeekStatus.on('change', applyNewOrdersWeekFilters);
                    $newOrdersWeekUploaded.on('change', applyNewOrdersWeekFilters);

                    function ensureNewOrdersWeekDtInitialized() {
                        if (newOrdersWeekDt) return newOrdersWeekDt;
                        const tableSelector = '#newOrdersWeekModalTable';
                        newOrdersWeekDt = initDataTable(tableSelector, 'NEW ORDERS THIS WEEK', {
                            buttonsHost: '#newOrdersWeekModalButtons',
                            buttonStyle: 'erp',
                            order: [[6, 'asc']],
                            columnDefs: [{
                                targets: [5, 6],
                                render: function(data, type) {
                                    if (type === 'filter' || type === 'sort') {
                                        return $('<div>').html(data).text().trim();
                                    }
                                    return data;
                                }
                            }, {
                                targets: [4, 5, 6, 7, 8, 9, 10],
                                className: 'text-center'
                            }, {
                                targets: [0, 6, 7, 8],
                                className: 'text-nowrap'
                            }]
                        });
                        populateNewOrdersWeekFilters(newOrdersWeekDt);
                        return newOrdersWeekDt;
                    }

                    function openNewOrdersWeekModal() {
                        applyModalTheme($newOrdersWeekModal, $newOrdersWeekTrigger);
                        const newDefaultText = ($newOrdersWeekCount.data('default') || '').toString();
                        if (newDefaultText) $newOrdersWeekCount.text(newDefaultText);
                        if (!newOrdersWeekDt) {
                            $newOrdersWeekModal.addClass('is-loading');
                            $newOrdersWeekLoading.removeClass('d-none');
                        } else {
                            $newOrdersWeekModal.removeClass('is-loading');
                            $newOrdersWeekLoading.addClass('d-none');
                        }
                        $newOrdersWeekModal.modal('show');
                    }

                    $(document).on('click', '.js-open-new-orders-week', openNewOrdersWeekModal);
                    $(document).on('keydown', '.js-open-new-orders-week', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            openNewOrdersWeekModal();
                        }
                    });

                    $newOrdersWeekModal.on('shown.bs.modal', function() {
                        const dt = ensureNewOrdersWeekDtInitialized();
                        if (!newOrdersWeekDidAdjust) {
                            newOrdersWeekDidAdjust = true;
                            setTimeout(() => {
                                try { dt.columns.adjust(); } catch (e) {}
                            }, 0);
                        }
                        if (!dt.__countBound) {
                            dt.__countBound = true;
                            dt.on('draw', function() { updateFilteredCount(dt, $newOrdersWeekCount); });
                        }
                        populateNewOrdersWeekFilters(dt);
                        updateFilteredCount(dt, $newOrdersWeekCount);
                        $newOrdersWeekModal.removeClass('is-loading');
                        $newOrdersWeekLoading.addClass('d-none');
                    });

                    $newOrdersWeekModal.on('hidden.bs.modal', function() {
                        $newOrdersWeekCustomer.val('');
                        $newOrdersWeekStatus.val('');
                        $newOrdersWeekUploaded.val('');
                        applyNewOrdersWeekFilters();
                        const newDefaultText = ($newOrdersWeekCount.data('default') || '').toString();
                        $newOrdersWeekCount.text(newDefaultText);
                        $newOrdersWeekTrigger.removeClass('is-active');
                    });

                    setTimeout(() => {
                        try { ensureNewOrdersWeekDtInitialized(); } catch (e) {}
                    }, 220);

                    // Orders Uploaded KPI -> modal detail (Orders Uploaded - current year)
                    const $uploadedOrdersModal = $('#uploadedOrdersModal');
                    const $uploadedOrdersLoading = $('#uploadedOrdersModalLoading');
                    const $uploadedOrdersCustomer = $('#uploadedOrdersModalCustomer');
                    const $uploadedOrdersStatus = $('#uploadedOrdersModalStatus');
                    const $uploadedOrdersMonth = $('#uploadedOrdersModalMonth');
                    const $uploadedOrdersDay = $('#uploadedOrdersModalDay');
                    const $uploadedOrdersButtons = $('#uploadedOrdersModalButtons');
                    const $uploadedOrdersCount = $('#uploadedOrdersModalCount');
                    let uploadedOrdersDt = null;
                    let uploadedOrdersDidAdjust = false;

                    const $uploadedOrdersTrigger = $('.kpi-erp .info-box').filter(function() {
                        const label = ($(this).find('.info-box-text').text() || '').trim();
                        return label.indexOf('Orders Uploaded') === 0;
                    }).first();

                    if ($uploadedOrdersTrigger.length) {
                        $uploadedOrdersTrigger
                            .addClass('js-open-uploaded-orders')
                            .attr('role', 'button')
                            .attr('tabindex', '0')
                            .attr('aria-label', 'Open Orders Uploaded detail');
                    }

                    function parseMonthKeyFromUploaded(uploadedText) {
                        const text = (uploadedText || '').trim();
                        // expected: "Jan/7/2026" or "Jan/07/2026"
                        const parts = text.split('/');
                        if (parts.length !== 3) return null;
                        const monthAbbr = (parts[0] || '').trim();
                        const year = (parts[2] || '').trim();
                        if (!monthAbbr || !year) return null;
                        const monthIndex = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'].indexOf(monthAbbr.toLowerCase());
                        if (monthIndex < 0) return null;
                        const monthNum = String(monthIndex + 1).padStart(2, '0');
                        return `${year}-${monthNum}`;
                    }

                    function monthLabelFromKey(key) {
                        const m = /^(\d{4})-(\d{2})$/.exec(key || '');
                        if (!m) return key;
                        const year = m[1];
                        const monthNum = parseInt(m[2], 10);
                        const names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        const monthAbbr = names[Math.max(1, Math.min(12, monthNum)) - 1];
                        return `${monthAbbr} ${year}`;
                    }

                    function monthRegexFromKey(key) {
                        const m = /^(\d{4})-(\d{2})$/.exec(key || '');
                        if (!m) return '';
                        const year = m[1];
                        const monthNum = parseInt(m[2], 10);
                        const names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        const monthAbbr = names[Math.max(1, Math.min(12, monthNum)) - 1];
                        return `^${escapeRegex(monthAbbr)}\\/\\d{1,2}\\/${escapeRegex(year)}$`;
                    }

                    function populateUploadedOrdersFilters(dt) {
                        if (!dt) return;
                        const customerValues = dt.column(3).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);
                        const statusValues = dt.column(5).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);
                        const uploadedValues = dt.column(6).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);

                        const uniqCustomers = Array.from(new Set(customerValues)).sort((a, b) => a.localeCompare(b));
                        const uniqStatuses = Array.from(new Set(statusValues)).sort((a, b) => a.localeCompare(b));

                        const monthToDays = new Map(); // key YYYY-MM -> Set(dates text)
                        uploadedValues.forEach(text => {
                            const key = parseMonthKeyFromUploaded(text);
                            if (!key) return;
                            if (!monthToDays.has(key)) monthToDays.set(key, new Set());
                            monthToDays.get(key).add(text);
                        });

                        const uniqMonths = Array.from(monthToDays.keys()).sort((a, b) => a.localeCompare(b));

                        const prevCustomer = $uploadedOrdersCustomer.val();
                        const prevStatus = $uploadedOrdersStatus.val();
                        const prevMonth = $uploadedOrdersMonth.val();
                        const prevDay = $uploadedOrdersDay.val();

                        $uploadedOrdersCustomer.empty().append('<option value=\"\">-- All Customers --</option>');
                        uniqCustomers.forEach(name => {
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = name;
                            $uploadedOrdersCustomer.append(opt);
                        });

                        $uploadedOrdersStatus.empty().append('<option value=\"\">-- All Status --</option>');
                        uniqStatuses.forEach(status => {
                            const opt = document.createElement('option');
                            opt.value = status;
                            opt.textContent = status;
                            $uploadedOrdersStatus.append(opt);
                        });

                        $uploadedOrdersMonth.empty().append('<option value=\"\">-- All Months --</option>');
                        uniqMonths.forEach(key => {
                            const opt = document.createElement('option');
                            opt.value = key;
                            opt.textContent = monthLabelFromKey(key);
                            $uploadedOrdersMonth.append(opt);
                        });

                        const monthSelected = (prevMonth && uniqMonths.includes(prevMonth)) ? prevMonth : ($uploadedOrdersMonth.val() || '');
                        if (monthSelected && uniqMonths.includes(monthSelected)) {
                            $uploadedOrdersMonth.val(monthSelected);
                        }

                        const days = monthSelected && monthToDays.has(monthSelected)
                            ? Array.from(monthToDays.get(monthSelected)).sort((a, b) => a.localeCompare(b))
                            : [];

                        $uploadedOrdersDay.empty().append('<option value=\"\">-- All Days --</option>');
                        days.forEach(value => {
                            const opt = document.createElement('option');
                            opt.value = value;
                            opt.textContent = value;
                            $uploadedOrdersDay.append(opt);
                        });

                        if (prevCustomer && uniqCustomers.includes(prevCustomer)) $uploadedOrdersCustomer.val(prevCustomer);
                        if (prevStatus && uniqStatuses.includes(prevStatus)) $uploadedOrdersStatus.val(prevStatus);
                        if (prevDay && days.includes(prevDay)) $uploadedOrdersDay.val(prevDay);
                    }

                    function applyUploadedOrdersFilters() {
                        const tableSelector = '#uploadedOrdersModalTable';
                        if (!$.fn.DataTable.isDataTable(tableSelector)) return;
                        const dt = $(tableSelector).DataTable();

                        const customer = ($uploadedOrdersCustomer.val() || '').trim();
                        const status = ($uploadedOrdersStatus.val() || '').trim();
                        const monthKey = ($uploadedOrdersMonth.val() || '').trim();
                        const day = ($uploadedOrdersDay.val() || '').trim();

                        dt.column(3).search(customer ? `^${escapeRegex(customer)}$` : '', true, false);
                        dt.column(5).search(status ? `^${escapeRegex(status)}$` : '', true, false);
                        if (day) {
                            dt.column(6).search(`^${escapeRegex(day)}$`, true, false);
                        } else if (monthKey) {
                            dt.column(6).search(monthRegexFromKey(monthKey), true, false);
                        } else {
                            dt.column(6).search('', true, false);
                        }
                        dt.draw();
                    }

                    $uploadedOrdersCustomer.on('change', applyUploadedOrdersFilters);
                    $uploadedOrdersStatus.on('change', applyUploadedOrdersFilters);
                    $uploadedOrdersMonth.on('change', function() {
                        if (uploadedOrdersDt) populateUploadedOrdersFilters(uploadedOrdersDt);
                        $uploadedOrdersDay.val('');
                        applyUploadedOrdersFilters();
                    });
                    $uploadedOrdersDay.on('change', applyUploadedOrdersFilters);

                    function ensureUploadedOrdersDtInitialized() {
                        if (uploadedOrdersDt) return uploadedOrdersDt;
                        const tableSelector = '#uploadedOrdersModalTable';
                        uploadedOrdersDt = initDataTable(tableSelector, `ORDERS UPLOADED (${new Date().getFullYear()})`, {
                            buttonsHost: '#uploadedOrdersModalButtons',
                            buttonStyle: 'erp',
                            order: [[6, 'asc']],
                            columnDefs: [{
                                targets: [5, 6],
                                render: function(data, type) {
                                    if (type === 'filter' || type === 'sort') {
                                        return $('<div>').html(data).text().trim();
                                    }
                                    return data;
                                }
                            }, {
                                targets: [4, 5, 6, 7, 8, 9, 10],
                                className: 'text-center'
                            }, {
                                targets: [0, 6, 7, 8],
                                className: 'text-nowrap'
                            }]
                        });
                        populateUploadedOrdersFilters(uploadedOrdersDt);
                        return uploadedOrdersDt;
                    }

                    function openUploadedOrdersModal() {
                        applyModalTheme($uploadedOrdersModal, $uploadedOrdersTrigger);
                        const upDefaultText = ($uploadedOrdersCount.data('default') || '').toString();
                        if (upDefaultText) $uploadedOrdersCount.text(upDefaultText);
                        if (!uploadedOrdersDt) {
                            $uploadedOrdersModal.addClass('is-loading');
                            $uploadedOrdersLoading.removeClass('d-none');
                        } else {
                            $uploadedOrdersModal.removeClass('is-loading');
                            $uploadedOrdersLoading.addClass('d-none');
                        }
                        $uploadedOrdersModal.modal('show');
                    }

                    $(document).on('click', '.js-open-uploaded-orders', openUploadedOrdersModal);
                    $(document).on('keydown', '.js-open-uploaded-orders', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            openUploadedOrdersModal();
                        }
                    });

                    $uploadedOrdersModal.on('shown.bs.modal', function() {
                        const dt = ensureUploadedOrdersDtInitialized();
                        if (!uploadedOrdersDidAdjust) {
                            uploadedOrdersDidAdjust = true;
                            setTimeout(() => {
                                try { dt.columns.adjust(); } catch (e) {}
                            }, 0);
                        }
                        if (!dt.__countBound) {
                            dt.__countBound = true;
                            dt.on('draw', function() { updateFilteredCount(dt, $uploadedOrdersCount); });
                        }
                        populateUploadedOrdersFilters(dt);
                        updateFilteredCount(dt, $uploadedOrdersCount);
                        $uploadedOrdersModal.removeClass('is-loading');
                        $uploadedOrdersLoading.addClass('d-none');
                    });

                    $uploadedOrdersModal.on('hidden.bs.modal', function() {
                        $uploadedOrdersCustomer.val('');
                        $uploadedOrdersStatus.val('');
                        $uploadedOrdersMonth.val('');
                        $uploadedOrdersDay.val('');
                        applyUploadedOrdersFilters();
                        const upDefaultText = ($uploadedOrdersCount.data('default') || '').toString();
                        $uploadedOrdersCount.text(upDefaultText);
                        $uploadedOrdersTrigger.removeClass('is-active');
                    });

                    setTimeout(() => {
                        try { ensureUploadedOrdersDtInitialized(); } catch (e) {}
                    }, 260);

                    // Active Orders KPI -> modal detail
                    const $activeOrdersModal = $('#activeOrdersModal');
                    const $activeOrdersLoading = $('#activeOrdersModalLoading');
                    const $activeOrdersLocation = $('#activeOrdersModalLocation');
                    const $activeOrdersCustomer = $('#activeOrdersModalCustomer');
                    const $activeOrdersStatus = $('#activeOrdersModalStatus');
                    const $activeOrdersMonth = $('#activeOrdersModalMonth');
                    const $activeOrdersDay = $('#activeOrdersModalDay');
                    const $activeOrdersButtons = $('#activeOrdersModalButtons');
                    const $activeOrdersCount = $('#activeOrdersModalCount');
                    let activeOrdersDt = null;
                    let activeOrdersDidAdjust = false;
                    let activeOrdersResizeTimer = null;

                    const $activeOrdersTrigger = $('.kpi-erp .info-box').filter(function() {
                        return ($(this).find('.info-box-text').text() || '').trim() === 'Active';
                    }).first();

                    if ($activeOrdersTrigger.length) {
                        $activeOrdersTrigger
                            .addClass('js-open-active-orders')
                            .attr('role', 'button')
                            .attr('tabindex', '0')
                            .attr('aria-label', 'Open Active Orders detail');
                    }

                    function populateActiveOrdersFilters(dt) {
                        if (!dt) return;
                        const locationValues = dt.column(6).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);
                        const customerValues = dt.column(3).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);
                        const statusValues = dt.column(5).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);
                        const uploadedValues = dt.column(7).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);

                        const uniqLocations = Array.from(new Set(locationValues)).sort((a, b) => a.localeCompare(b));
                        const uniqCustomers = Array.from(new Set(customerValues)).sort((a, b) => a.localeCompare(b));
                        const uniqStatuses = Array.from(new Set(statusValues)).sort((a, b) => a.localeCompare(b));

                        const monthToDays = new Map(); // key YYYY-MM -> Set(dates text)
                        uploadedValues.forEach(text => {
                            const key = parseMonthKeyFromUploaded(text);
                            if (!key) return;
                            if (!monthToDays.has(key)) monthToDays.set(key, new Set());
                            monthToDays.get(key).add(text);
                        });
                        const uniqMonths = Array.from(monthToDays.keys()).sort((a, b) => a.localeCompare(b));

                        const prevLocation = $activeOrdersLocation.val();
                        const prevCustomer = $activeOrdersCustomer.val();
                        const prevStatus = $activeOrdersStatus.val();
                        const prevMonth = $activeOrdersMonth.val();
                        const prevDay = $activeOrdersDay.val();

                        $activeOrdersLocation.empty().append('<option value=\"\">-- All Locations --</option>');
                        uniqLocations.forEach(loc => {
                            const opt = document.createElement('option');
                            opt.value = loc;
                            opt.textContent = loc;
                            $activeOrdersLocation.append(opt);
                        });

                        $activeOrdersCustomer.empty().append('<option value=\"\">-- All Customers --</option>');
                        uniqCustomers.forEach(name => {
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = name;
                            $activeOrdersCustomer.append(opt);
                        });

                        $activeOrdersStatus.empty().append('<option value=\"\">-- All Status --</option>');
                        uniqStatuses.forEach(status => {
                            const opt = document.createElement('option');
                            opt.value = status;
                            opt.textContent = status;
                            $activeOrdersStatus.append(opt);
                        });

                        if (prevLocation && uniqLocations.includes(prevLocation)) $activeOrdersLocation.val(prevLocation);
                        if (prevCustomer && uniqCustomers.includes(prevCustomer)) $activeOrdersCustomer.val(prevCustomer);
                        if (prevStatus && uniqStatuses.includes(prevStatus)) $activeOrdersStatus.val(prevStatus);

                        $activeOrdersMonth.empty().append('<option value=\"\">-- All Months --</option>');
                        uniqMonths.forEach(key => {
                            const opt = document.createElement('option');
                            opt.value = key;
                            opt.textContent = monthLabelFromKey(key);
                            $activeOrdersMonth.append(opt);
                        });

                        const monthSelected = (prevMonth && uniqMonths.includes(prevMonth)) ? prevMonth : ($activeOrdersMonth.val() || '');
                        if (monthSelected && uniqMonths.includes(monthSelected)) {
                            $activeOrdersMonth.val(monthSelected);
                        }

                        const days = monthSelected && monthToDays.has(monthSelected)
                            ? Array.from(monthToDays.get(monthSelected)).sort((a, b) => a.localeCompare(b))
                            : [];

                        $activeOrdersDay.empty().append('<option value=\"\">-- All Days --</option>');
                        days.forEach(value => {
                            const opt = document.createElement('option');
                            opt.value = value;
                            opt.textContent = value;
                            $activeOrdersDay.append(opt);
                        });

                        if (prevDay && days.includes(prevDay)) $activeOrdersDay.val(prevDay);
                    }

                    function applyActiveOrdersFilters() {
                        const tableSelector = '#activeOrdersModalTable';
                        if (!$.fn.DataTable.isDataTable(tableSelector)) return;
                        const dt = $(tableSelector).DataTable();

                        const location = ($activeOrdersLocation.val() || '').trim();
                        const customer = ($activeOrdersCustomer.val() || '').trim();
                        const status = ($activeOrdersStatus.val() || '').trim();
                        const monthKey = ($activeOrdersMonth.val() || '').trim();
                        const day = ($activeOrdersDay.val() || '').trim();

                        dt.column(6).search(location ? `^${escapeRegex(location)}$` : '', true, false);
                        dt.column(3).search(customer ? `^${escapeRegex(customer)}$` : '', true, false);
                        dt.column(5).search(status ? `^${escapeRegex(status)}$` : '', true, false);
                        if (day) {
                            dt.column(7).search(`^${escapeRegex(day)}$`, true, false);
                        } else if (monthKey) {
                            dt.column(7).search(monthRegexFromKey(monthKey), true, false);
                        } else {
                            dt.column(7).search('', true, false);
                        }
                        dt.draw();
                    }

                    $activeOrdersLocation.on('change', applyActiveOrdersFilters);
                    $activeOrdersCustomer.on('change', applyActiveOrdersFilters);
                    $activeOrdersStatus.on('change', applyActiveOrdersFilters);
                    $activeOrdersMonth.on('change', function() {
                        if (activeOrdersDt) populateActiveOrdersFilters(activeOrdersDt);
                        $activeOrdersDay.val('');
                        applyActiveOrdersFilters();
                    });
                    $activeOrdersDay.on('change', applyActiveOrdersFilters);

                    function ensureActiveOrdersDtInitialized() {
                        if (activeOrdersDt) return activeOrdersDt;
                        const tableSelector = '#activeOrdersModalTable';
                        activeOrdersDt = initDataTable(tableSelector, 'ACTIVE ORDERS', {
                            buttonsHost: '#activeOrdersModalButtons',
                            buttonStyle: 'erp',
                            order: [[8, 'asc']],
                            columnDefs: [{
                                targets: [5, 6, 7, 8],
                                render: function(data, type) {
                                    if (type === 'filter' || type === 'sort') {
                                        return $('<div>').html(data).text().trim();
                                    }
                                    return data;
                                }
                            }, {
                                targets: [4, 5, 6, 7, 8, 9, 10],
                                className: 'text-center'
                            }, {
                                targets: [0, 7, 8],
                                className: 'text-nowrap'
                            }]
                        });
                        populateActiveOrdersFilters(activeOrdersDt);
                        return activeOrdersDt;
                    }

                    function setActiveOrdersCountFromDom() {
                        const $rows = $('#activeOrdersModalTable tbody tr');
                        if (!$rows.length) {
                            $activeOrdersCount.text('Total: 0 / 0');
                            return;
                        }
                        if ($rows.length === 1) {
                            const text = ($rows.first().text() || '').trim().toLowerCase();
                            if (text.includes('no orders found')) {
                                $activeOrdersCount.text('Total: 0 / 0');
                                return;
                            }
                        }
                        const total = $rows.length;
                        $activeOrdersCount.text(`Total: ${total} / ${total}`);
                    }

                    function openActiveOrdersModal() {
                        applyModalTheme($activeOrdersModal, $activeOrdersTrigger);
                        // Mostrar el total inmediatamente (antes de inicializar DataTables)
                        const defaultText = ($activeOrdersCount.data('default') || '').toString();
                        if (defaultText) {
                            $activeOrdersCount.text(defaultText);
                        } else {
                            setActiveOrdersCountFromDom();
                        }
                        if (!activeOrdersDt) {
                            $activeOrdersModal.addClass('is-loading');
                            $activeOrdersLoading.removeClass('d-none');
                        } else {
                            $activeOrdersModal.removeClass('is-loading');
                            $activeOrdersLoading.addClass('d-none');
                        }
                        $activeOrdersModal.modal('show');
                    }

                    $(document).on('click', '.js-open-active-orders', openActiveOrdersModal);
                    $(document).on('keydown', '.js-open-active-orders', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            openActiveOrdersModal();
                        }
                    });

                    $activeOrdersModal.on('shown.bs.modal', function() {
                        const dt = ensureActiveOrdersDtInitialized();
                        // Inicializa ya visible para evitar "brinco" (estilo onTimeModal)
                        setTimeout(() => {
                            try { dt.draw(false); } catch (e) {}
                            $activeOrdersModal.removeClass('is-loading');
                            $activeOrdersLoading.addClass('d-none');
                        }, 0);
                        if (!dt.__countBound) {
                            dt.__countBound = true;
                            dt.on('draw', function() { updateFilteredCount(dt, $activeOrdersCount); });
                        }
                        populateActiveOrdersFilters(dt);
                        updateFilteredCount(dt, $activeOrdersCount);
                    });

                    $activeOrdersModal.on('hidden.bs.modal', function() {
                        $activeOrdersLocation.val('');
                        $activeOrdersCustomer.val('');
                        $activeOrdersStatus.val('');
                        $activeOrdersMonth.val('');
                        $activeOrdersDay.val('');
                        applyActiveOrdersFilters();
                        const defaultText = ($activeOrdersCount.data('default') || '').toString();
                        $activeOrdersCount.text(defaultText);
                        $activeOrdersTrigger.removeClass('is-active');
                        $activeOrdersModal.removeClass('is-loading');
                        $activeOrdersLoading.addClass('d-none');
                    });

                    // Nota: no pre-inicializar para evitar salto en header (como onTimeModal)

                    // Completed Orders KPI -> modal detail (Completed Orders - current year)
                    const $completedOrdersModal = $('#completedOrdersModal');
                    const $completedOrdersLoading = $('#completedOrdersModalLoading');
                    const $completedOrdersCustomer = $('#completedOrdersModalCustomer');
                    const $completedOrdersMonth = $('#completedOrdersModalMonth');
                    const $completedOrdersDay = $('#completedOrdersModalDay');
                    const $completedOrdersButtons = $('#completedOrdersModalButtons');
                    const $completedOrdersCount = $('#completedOrdersModalCount');
                    let completedOrdersDt = null;
                    let completedOrdersDidAdjust = false;

                    const $completedOrdersTrigger = $('.kpi-erp .info-box').filter(function() {
                        const label = ($(this).find('.info-box-text').text() || '').trim();
                        return label.indexOf('Completed Orders') === 0;
                    }).first();

                    if ($completedOrdersTrigger.length) {
                        $completedOrdersTrigger
                            .addClass('js-open-completed-orders')
                            .attr('role', 'button')
                            .attr('tabindex', '0')
                            .attr('aria-label', 'Open Completed Orders detail');
                    }

                    function parseMonthKeyFromDateText(dateText) {
                        const text = (dateText || '').trim();
                        const parts = text.split('/');
                        if (parts.length !== 3) return null;
                        const monthAbbr = (parts[0] || '').trim();
                        const year = (parts[2] || '').trim();
                        if (!monthAbbr || !year) return null;
                        const monthIndex = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'].indexOf(monthAbbr.toLowerCase());
                        if (monthIndex < 0) return null;
                        const monthNum = String(monthIndex + 1).padStart(2, '0');
                        return `${year}-${monthNum}`;
                    }

                    function monthLabelFromKey(key) {
                        const m = /^(\d{4})-(\d{2})$/.exec(key || '');
                        if (!m) return key;
                        const year = m[1];
                        const monthNum = parseInt(m[2], 10);
                        const names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        const monthAbbr = names[Math.max(1, Math.min(12, monthNum)) - 1];
                        return `${monthAbbr} ${year}`;
                    }

                    function monthRegexFromKey(key) {
                        const m = /^(\d{4})-(\d{2})$/.exec(key || '');
                        if (!m) return '';
                        const year = m[1];
                        const monthNum = parseInt(m[2], 10);
                        const names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        const monthAbbr = names[Math.max(1, Math.min(12, monthNum)) - 1];
                        return `^${escapeRegex(monthAbbr)}\\/\\d{1,2}\\/${escapeRegex(year)}$`;
                    }

                    function populateCompletedOrdersFilters(dt) {
                        if (!dt) return;
                        const customerValues = dt.column(3).nodes().toArray().map(td => ($(td).text() || '').trim()).filter(Boolean);

                        const effectiveDateValues = dt.rows().nodes().toArray().map(tr => {
                            const $tds = $(tr).find('td');
                            const sentAt = ($tds.eq(8).text() || '').trim();
                            const dueDate = ($tds.eq(7).text() || '').trim();
                            const effective = (sentAt && sentAt !== '-') ? sentAt : dueDate;
                            return (effective && effective !== '-') ? effective : '';
                        }).filter(Boolean);

                        const uniqCustomers = Array.from(new Set(customerValues)).sort((a, b) => a.localeCompare(b));

                        const monthToDays = new Map();
                        effectiveDateValues.forEach(text => {
                            const key = parseMonthKeyFromDateText(text);
                            if (!key) return;
                            if (!monthToDays.has(key)) monthToDays.set(key, new Set());
                            monthToDays.get(key).add(text);
                        });
                        const uniqMonths = Array.from(monthToDays.keys()).sort((a, b) => a.localeCompare(b));

                        const prevCustomer = $completedOrdersCustomer.val();
                        const prevMonth = $completedOrdersMonth.val();
                        const prevDay = $completedOrdersDay.val();

                        $completedOrdersCustomer.empty().append('<option value=\"\">-- All Customers --</option>');
                        uniqCustomers.forEach(name => {
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = name;
                            $completedOrdersCustomer.append(opt);
                        });

                        $completedOrdersMonth.empty().append('<option value=\"\">-- All Months --</option>');
                        uniqMonths.forEach(key => {
                            const opt = document.createElement('option');
                            opt.value = key;
                            opt.textContent = monthLabelFromKey(key);
                            $completedOrdersMonth.append(opt);
                        });

                        if (prevCustomer && uniqCustomers.includes(prevCustomer)) $completedOrdersCustomer.val(prevCustomer);
                        const monthSelected = (prevMonth && uniqMonths.includes(prevMonth)) ? prevMonth : ($completedOrdersMonth.val() || '');
                        if (monthSelected && uniqMonths.includes(monthSelected)) $completedOrdersMonth.val(monthSelected);

                        const days = monthSelected && monthToDays.has(monthSelected)
                            ? Array.from(monthToDays.get(monthSelected)).sort((a, b) => a.localeCompare(b))
                            : [];

                        $completedOrdersDay.empty().append('<option value=\"\">-- All Days --</option>');
                        days.forEach(value => {
                            const opt = document.createElement('option');
                            opt.value = value;
                            opt.textContent = value;
                            $completedOrdersDay.append(opt);
                        });

                        if (prevDay && days.includes(prevDay)) $completedOrdersDay.val(prevDay);
                    }

                    function applyCompletedOrdersFilters() {
                        const tableSelector = '#completedOrdersModalTable';
                        if (!$.fn.DataTable.isDataTable(tableSelector)) return;
                        const dt = $(tableSelector).DataTable();

                        const customer = ($completedOrdersCustomer.val() || '').trim();
                        const monthKey = ($completedOrdersMonth.val() || '').trim();
                        const day = ($completedOrdersDay.val() || '').trim();

                        dt.column(3).search(customer ? `^${escapeRegex(customer)}$` : '', true, false);
                        dt.draw();
                    }

                    $completedOrdersCustomer.on('change', applyCompletedOrdersFilters);
                    $completedOrdersMonth.on('change', function() {
                        if (completedOrdersDt) populateCompletedOrdersFilters(completedOrdersDt);
                        $completedOrdersDay.val('');
                        applyCompletedOrdersFilters();
                    });
                    $completedOrdersDay.on('change', applyCompletedOrdersFilters);

                    function ensureCompletedOrdersDtInitialized() {
                        if (completedOrdersDt) return completedOrdersDt;
                        const tableSelector = '#completedOrdersModalTable';
                        completedOrdersDt = initDataTable(tableSelector, `COMPLETED ORDERS (${new Date().getFullYear()})`, {
                            buttonsHost: '#completedOrdersModalButtons',
                            buttonStyle: 'erp',
                            order: [[8, 'asc']],
                            columnDefs: [{
                                targets: [5, 6, 7, 8],
                                render: function(data, type) {
                                    if (type === 'filter' || type === 'sort') {
                                        return $('<div>').html(data).text().trim();
                                    }
                                    return data;
                                }
                            }, {
                                targets: [4, 5, 6, 7, 8, 9, 10],
                                className: 'text-center'
                            }, {
                                targets: [0, 6, 7, 8],
                                className: 'text-nowrap'
                            }]
                        });

                        if (!completedOrdersDt.__monthDayFilterAdded) {
                            completedOrdersDt.__monthDayFilterAdded = true;
                            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                                if (!settings || !settings.nTable || settings.nTable.id !== 'completedOrdersModalTable') return true;

                                const monthKey = ($completedOrdersMonth.val() || '').trim();
                                const day = ($completedOrdersDay.val() || '').trim();
                                if (!monthKey && !day) return true;

                                const rowSentAt = (data[8] || '').toString().trim();
                                const rowDueDate = (data[7] || '').toString().trim();
                                const effective = (rowSentAt && rowSentAt !== '-') ? rowSentAt : rowDueDate;
                                if (!effective || effective === '-') return false;

                                if (day) return effective === day;

                                const pattern = monthRegexFromKey(monthKey);
                                if (!pattern) return true;
                                try {
                                    return new RegExp(pattern).test(effective);
                                } catch (e) {
                                    return true;
                                }
                            });
                        }

                        populateCompletedOrdersFilters(completedOrdersDt);
                        return completedOrdersDt;
                    }

                    function openCompletedOrdersModal() {
                        applyModalTheme($completedOrdersModal, $completedOrdersTrigger);
                        const compDefaultText = ($completedOrdersCount.data('default') || '').toString();
                        if (compDefaultText) $completedOrdersCount.text(compDefaultText);
                        if (!completedOrdersDt) {
                            $completedOrdersModal.addClass('is-loading');
                            $completedOrdersLoading.removeClass('d-none');
                        } else {
                            $completedOrdersModal.removeClass('is-loading');
                            $completedOrdersLoading.addClass('d-none');
                        }
                        $completedOrdersModal.modal('show');
                    }

                    $(document).on('click', '.js-open-completed-orders', openCompletedOrdersModal);
                    $(document).on('keydown', '.js-open-completed-orders', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            openCompletedOrdersModal();
                        }
                    });

                    $completedOrdersModal.on('shown.bs.modal', function() {
                        const dt = ensureCompletedOrdersDtInitialized();
                        if (!completedOrdersDidAdjust) {
                            completedOrdersDidAdjust = true;
                            setTimeout(() => {
                                try { dt.columns.adjust(); } catch (e) {}
                            }, 0);
                        }
                        if (!dt.__countBound) {
                            dt.__countBound = true;
                            dt.on('draw', function() { updateFilteredCount(dt, $completedOrdersCount); });
                        }
                        populateCompletedOrdersFilters(dt);
                        updateFilteredCount(dt, $completedOrdersCount);
                        $completedOrdersModal.removeClass('is-loading');
                        $completedOrdersLoading.addClass('d-none');
                    });

                    $completedOrdersModal.on('hidden.bs.modal', function() {
                        $completedOrdersCustomer.val('');
                        $completedOrdersMonth.val('');
                        $completedOrdersDay.val('');
                        applyCompletedOrdersFilters();
                        const compDefaultText = ($completedOrdersCount.data('default') || '').toString();
                        $completedOrdersCount.text(compDefaultText);
                        $completedOrdersTrigger.removeClass('is-active');
                    });

                    setTimeout(() => {
                        try { ensureCompletedOrdersDtInitialized(); } catch (e) {}
                    }, 280);

                    const weekFilter = document.getElementById('week-filter');

                    if (weekFilter) {
                        // Γ£à Detectar cambio de semana
                        weekFilter.addEventListener("change", function() {
                            const week = this.value;
                            // Validar formato ISO YYYY-Www
                            const weekRegex = /^\d{4}-W\d{2}$/;
                            if (!week || !weekRegex.test(week)) {
                                console.warn("Formato de semana inválido:", week);
                                return;
                            }

                            fetch(`/orders/by-week/ajax?week=${week}`, {
                                    headers: {
                                        "X-Requested-With": "XMLHttpRequest"
                                    },
                                })
                                .then(async (res) => {
                                    if (!res.ok) {
                                        const txt = await res.text();
                                        throw new Error(`HTTP ${res.status}: ${txt}`);
                                    }
                                    return res.json();
                                })
                                .then((data) => {
                                    const tbody = document.getElementById("tableweek-body");
                                    const count = document.getElementById("order-count");

                                    if (!tbody || !count) {
                                        console.warn("No se encontr├│ tbody o contador.");
                                        return;
                                    }
                                    // ≡ƒÆí Destruir DataTable anterior si existe
                                    const table = $("#tableweek");
                                    if ($.fn.DataTable.isDataTable(table)) {
                                        table.DataTable().clear().destroy();
                                    }
                                    // ≡ƒÆí Actualizar tbody con nuevas filas
                                    tbody.innerHTML = data.html;

                                    // ≡ƒÆí Actualizar contador
                                    count.textContent = data.count;

                                    // ≡ƒÆí Reinicializar DataTable
                                    weekTableDt = initDataTable("#tableweek", "ORDERS THIS WEEK");
                                    try { weekTableDt.on('draw', refreshWeekOrdersCache); } catch (e) {}
                                    // Recache para el modal de semana
                                    refreshWeekOrdersCache();

                                    // ≡ƒÆí Actualizar texto visible de la semana
                                    const weekDisplay = document.getElementById("week-display");
                                    if (weekDisplay && week) {
                                        const [year, weekNumber] = week.split('-W');
                                        const simpleDate = getFirstDateOfISOWeek(parseInt(weekNumber), parseInt(
                                            year));

                                        const options = {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric'
                                        };
                                        const formatted = simpleDate.toLocaleDateString('en-US', options);
                                        weekDisplay.textContent = `Week starting: ${formatted}`;
                                    }

                                    // ≡ƒæë Funci├│n para obtener lunes de la semana ISO
                                    function getFirstDateOfISOWeek(week, year) {
                                        const simple = new Date(year, 0, 1 + (week - 1) * 7);
                                        const dow = simple.getDay();
                                        if (dow <= 4) {
                                            simple.setDate(simple.getDate() - simple.getDay() + 1);
                                        } else {
                                            simple.setDate(simple.getDate() + 8 - simple.getDay());
                                        }
                                        return simple;
                                    }
                                })
                                .catch((error) => {
                                    // console.error("Error fetching data:", error);
                                    alert(`Error loading data for the selected week.\n\n${error}`);
                                });

                        });

                        // Γ£à Al cargar la p├ígina, establecer semana actual si no hay valor
                        if (!weekFilter.value) {
                            const today = new Date();

                            const getISOInfo = date => {
                                const tmp = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
                                tmp.setUTCDate(tmp.getUTCDate() + 4 - (tmp.getUTCDay() || 7));
                                const isoYear = tmp.getUTCFullYear();
                                const yearStart = new Date(Date.UTC(isoYear, 0, 1));
                                const weekNo = Math.ceil((((tmp - yearStart) / 86400000) + 1) / 7);
                                return {
                                    isoYear,
                                    weekNo
                                };
                            };

                            const {
                                isoYear,
                                weekNo
                            } = getISOInfo(today);
                            const week = weekNo.toString().padStart(2, '0');
                            const value = `${isoYear}-W${week}`;
                            weekFilter.value = value;

                            // 💥 Disparar manualmente el evento change
                            weekFilter.dispatchEvent(new Event('change'));
                        }
                    }
                });
            </script>

            @endpush
