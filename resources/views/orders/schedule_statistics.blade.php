<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Orders Statistics')
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
                <li class="breadcrumb-item active" aria-current="page">Orders Statistics</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')

{{-- Tabs --}}
@include('orders.schedule_tab')

{{-- MÉTRICAS PRINCIPALES COMPACTAS & ATRACTIVAS --}}
<div class="container-fluid py-4">

    {{-- Cards resumen principal --}}

    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success shadow-sm">
                    <i class="fas fa-cogs"></i> <!-- Ícono de engranaje múltiple -->
                </span>
                <div class="info-box-content">
                    <div class="row">
                        <!-- Primera columna -->
                        <div class="col-5">
                            <span class="info-box-text">Order Summary</span>
                            <h4 class="mb-0 fw-bold">{{ $totalOrdenes }}</h4>
                        </div>
                        <!-- Segunda columna -->
                        <div class="col-7 text-end">
                            <span class="badge badge-soft-info" style="font-size: 1rem;">
                                Hearst: {{ $cantidadHearst }}
                            </span>
                            <span class="badge badge-soft-info" style="font-size: 1rem;">
                                Yarnell: {{ $cantidadYarnell }}
                            </span>
                            <span class="badge bg-secondary bg-opacity-25">
                                Floor: {{ $cantidadFloor }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger shadow-sm">
                    <i class="fas fa-exclamation-triangle"></i> <!-- Ícono de engranaje múltiple -->
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Late Orders</span>
                    <h4 class="mb-0 fw-bold">{{ $cantidadAtrasadas }}</h4>
                </div>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary shadow-sm">
                    <i class="fas fa-calendar-week"></i> <!-- Ícono de engranaje múltiple -->
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Order this week</span>
                    <h4 class="mb-0 fw-bold">{{ $ordenesSemana->count() }}</h4>
                </div>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning shadow-sm">
                    <i class="fas fa-tasks"></i> <!-- Ícono de engranaje múltiple -->
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">New Orders this week</span>
                    <span class="info-box-number">{{ $totalAgregadasSemana }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card: Clientes con órdenes --}}
    <div class="col-md-12 "> {{-- Ocupa toda la fila --}}
        <div class="card shadow-sm rounded-3 border-0 h-100">
            <div class="card-body px-3 py-2" style="max-height: 280px; overflow-y: auto;">
                @if ($ordenesPorCliente->isNotEmpty())
                <div class="row text-center">
                    @foreach ($ordenesPorCliente->sortByDesc('total') as $grupo)
                    @php
                    if ($grupo->total > 10) {
                    $circleClass = 'bg-success text-white'; // verde
                    } elseif ($grupo->total >= 5 && $grupo->total <= 10) { $circleClass='bg-warning text-dark' ; } else
                        { $circleClass='bg-secondary text-white' ; } @endphp <div
                        class="col-1 d-flex flex-column align-items-center">
                        <div class="rounded-circle d-flex justify-content-center align-items-center mx-auto {{ $circleClass }}"
                            style="width: 50px; height: 50px; font-weight: 600; font-size: 2rem; user-select:none;">
                            {{ $grupo->total }}
                        </div>
                        <small class="text-truncate mt-1" title="{{ ucfirst($grupo->costumer) }}">
                            {{ ucfirst($grupo->costumer) }}
                        </small>
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

<div class="container-fluid py-4">
    {{-- Cards con tablas --}}
    <div class="row g-4 mb-4">
        {{-- Ordenes esta semana --}}
        <div class="col-lg-6">

            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap">
                    {{-- Título a la izquierda --}}
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
                        <table id="tableweek" class="table table-hover align-middle mb-0 small datatable-export">
                            <thead class="text-dark">
                                <tr class="text-center align-middle">
                                    <th>WORK ID</th>
                                    <th>PN</th>
                                    <th>DESCRIPTION</th>
                                    <th>CUSTOMER</th>
                                    <th>QTY</th>
                                    <th>STATUS</th>
                                    <th>DUE DATE</th>
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
                        <table id="tablelate" class="table table-hover align-middle mb-0 small datatable-export">
                            <thead class="text-dark">
                                <tr>
                                    <th>WORK ID</th>
                                    <th>PN</th>
                                    <th>DESCRIPTION</th>
                                    <th>CUSTOMER</th>
                                    <th>QTY</th>
                                    <th>STATUS</th>
                                    <th>DUE DATE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ordenesAtrasadas as $order)
                                <tr>
                                    <td>{{ $order->work_id }}</td>
                                    <td>{{ $order->PN }}</td>
                                    <td class="text-truncate" style="max-width: 160px;">{{ $order->Part_description }}
                                    </td>
                                    <td>{{ ucfirst($order->costumer) }}</td>
                                    <td>{{ $order->qty }}</td>
                                    <td><span class="badge bg-warning text-dark">{{ $order->status }}</span></td>
                                    <td><span
                                            class="text-danger fw-semibold">{{ $order->due_date->format('M/d/Y') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3">No late orders found.</td>
                                </tr>
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
                <!-- Columna izquierda: primer filtro + botón + gráfica -->
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
                                @for ($y = date('Y'); $y >= 2020; $y--)
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
                <!-- Columna derecha: segundo filtro + botón + gráfica -->
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
            <h5>Next Orders</h5>
        </div>
        <div class="card-body">
            <div class="row">

                <!-- Columna derecha: segundo filtro + botón + gráfica -->
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
                <!-- Columna derecha: gráfica de entregas a tiempo vs tarde -->
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
                                <option value="{{ $y }}">{{ $y }}</option>
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
        {{-- Card: Clientes con órdenes --}}
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

        {{-- Card: Órdenes agregadas esta semana --}}
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card shadow rounded-4 border-0 h-100">
                @if ($resumen['all_shipping'])
                <div class="card-header bg-success text-white text-center rounded-top-4">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i> PENDING ORDERS THIS WEEK
                    </h5>
                </div>
                <div class="card-body text-center py-4">
                    <i class="fas fa-box-open fa-3x text-success mb-3"></i>
                    <p class="mb-1 text-dark" style="font-size: 1.25rem;">TOTAL ORDERS:
                        <strong>{{ $resumen['total'] }}</strong>
                    </p>
                    <p class="fs-6 fw-bold text-success mb-0" style="font-size: 1.25rem;">✅ ¡Everything shipped this
                        week!</p>
                </div>
                @else
                <div class="card-header bg-warning text-dark text-center rounded-top-4">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i> PENDING ORDERS THIS WEEK
                    </h5>
                </div>
                <div class="card-body py-3">
                    <div class="row">
                        {{-- Columna izquierda: resumen --}}

                        <div class="col-4">
                            <div class="text-start small">
                                {{-- Cabecera con ícono --}}
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-box fa-2x text-warning mr-3"></i>
                                    <h6 class="mb-0 font-weight-bold text-dark">Order Summary</h6>
                                </div>

                                {{-- Línea: Total --}}
                                <div class="d-flex align-items-center mb-2">
                                    <div class="mr-2" style="width: 28px;">
                                        <i class="fas fa-list-alt text-muted"></i>
                                    </div>
                                    <span class="flex-grow-1 text-dark" style="font-size: 1.2rem;">Total Orders</span>
                                    <span class="font-weight-bold text-dark"
                                        style="font-size: 1.2rem;">{{ $resumen['total'] }}</span>
                                </div>

                                {{-- Línea: Pending --}}
                                <div class="d-flex align-items-center mb-2">
                                    <div class="mr-4" style="width: 28px;">
                                        <i class="fas fa-clock text-warning"></i>
                                    </div>
                                    <span class="flex-grow-1 text-dark" style="font-size: 1.2rem;">Pending</span>
                                    <span class="font-weight-bold text-warning"
                                        style="font-size: 1.2rem;">{{ $resumen['pendients'] }}</span>
                                </div>

                                {{-- Línea: Sent --}}
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




        {{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card shadow-sm rounded-3 border-0 h-100">
                {{-- Header --}}
                <div class="card-header bg-secondary text-white d-flex align-items-center font-weight-semibold">
                    <i class="fas fa-calendar-week mr-2"></i> Weekly Orders
                </div>

                @php
                // Dinámica de colores e íconos según el cumplimiento global
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

                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
                    <table class="table table-sm table-bordered table-striped small mb-0">
                        <thead class="thead-light sticky-top">
                            <tr class="text-center">
                                <th>Week</th>
                                <th>Date Range</th>
                                <th>Total</th>
                                <th class="text-success">✔ Done</th>
                                <th class="text-warning">⏱ Late</th>
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
                            <tr>
                                <td colspan="6" class="text-center text-muted">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>





    </div>


    @endsection

    @section('css')
    <style>
        .dataTables_wrapper {
            margin-top: 20px !important;
            margin-left: 15px !important;
            margin-right: 15px !important;
        }
    </style>

    <!-- CSS de DataTables + Botones -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">

    @endsection

    @push('js')
    <!-- JS de DataTables -->


    <!-- Botones -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>

    <!-- Exportaciones -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        // orders_dashboard.js

        Chart.register(ChartDataLabels);

        function getFechaHoraActual() {
            const now = new Date();
            const fecha = now.toLocaleDateString('en-US');
            const hora = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
            return `${fecha} ${hora}`;
        }

        function initDataTable(selector, tableTitle) {
            const fechaHora = getFechaHoraActual();
            $(selector).DataTable({
                pageLength: 10,
                order: [
                    [6, 'asc']
                ],
                dom: "<'row mb-3'<'col-md-6 d-flex'B><'col-md-6 d-flex justify-content-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        title: `${tableTitle} - ${fechaHora}`,
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm mx-0',
                        filename: `${tableTitle.replace(/\s+/g, '_')}_${fechaHora.replace(/[/: ]/g, '_')}`
                    },
                    {
                        extend: 'pdfHtml5',
                        title: `${tableTitle} - ${fechaHora}`,
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm mx-1',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        filename: `${tableTitle.replace(/\s+/g, '_')}_${fechaHora.replace(/[/: ]/g, '_')}`
                    },
                    {
                        extend: 'print',
                        title: `${tableTitle} - ${fechaHora}`,
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-primary btn-sm'
                    }
                ],
                searching: true,
            });
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

        //-----------------------------------------------------------
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

            // 🔵 Nuevo gráfico: entregas a tiempo vs tarde con filtros
            const onTimeCtx = document.getElementById('onTimeChart')?.getContext('2d');
            const monthFilter = document.getElementById('monthFilter');
            const yearFilter = document.getElementById('yearFilter'); // 🆕
            const customerFilterOnTime = document.getElementById('customerFilterOnTime');
            const onTimeChartRef = {
                chart: null
            };

            function loadOnTimeChart() {
                if (!onTimeCtx) return;

                const month = monthFilter?.value;
                const year = yearFilter?.value;
                const customer = customerFilterOnTime?.value;

                let displayMonth = '';

                if (month) {
                    const [year, monthNum] = month.split('-'); // ejemplo: "2025-07" → ["2025", "07"]
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
                if (year) params.append('year', year); // 🆕
                if (customer) params.append('customer', customer);

                if (params.toString()) url += `?${params.toString()}`;
                console.log("🔗 URL:", url);

                fetch(url)
                    .then(res => res.json())
                    .then(({
                        labels,
                        data,
                        total,
                        selectedCustomer,
                        selectedYear
                    }) => {
                        if (onTimeChartRef.chart) onTimeChartRef.chart.destroy();

                        const colorMap = {
                            'Early': '#007bff',
                            'On Time': '#28a745',
                            'Late': '#dc3545'
                        };

                        const totalOrders = total ?? data.reduce((a, b) => a + b, 0);

                        const displayCustomer = selectedCustomer ?
                            selectedCustomer.charAt(0).toUpperCase() + selectedCustomer.slice(1).toLowerCase() :
                            'All';

                        const displayYear = selectedYear || '';
                        const titleParts = [`Total Orders: ${totalOrders}`];
                        if (displayCustomer !== 'All') titleParts.push(`Customer: ${displayCustomer}`);
                        if (displayYear) titleParts.push(`Year: ${displayYear}`);
                        if (displayMonth) titleParts.push(`Month: ${displayMonth}`);
                        const fullTitle = titleParts.join(' | ');

                        const colors = labels.map(label => colorMap[label] || '#999');
                        onTimeChartRef.chart = new Chart(onTimeCtx, {
                            type: 'doughnut',
                            data: {
                                labels,
                                datasets: [{
                                    data,
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
                                            const sum = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                            const percent = sum ? Math.round((value / sum) * 100) : 0;
                                            return `${value} (${percent}%)`;
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
                    });
            }

            // 👉 Cargar al iniciar
            if (onTimeCtx) loadOnTimeChart();

            // 👉 Escuchar cambios en filtros
            monthFilter?.addEventListener('change', loadOnTimeChart);
            yearFilter?.addEventListener('change', loadOnTimeChart); // 🆕
            customerFilterOnTime?.addEventListener('change', loadOnTimeChart);


        });;


        $(document).ready(function() {
            initDataTable('#tableweek', 'ORDERS THIS WEEK');
            initDataTable('#tablelate', 'LATE ORDERS');
            loadChartElements();
            loadCustomerChartElements();

            const weekFilter = document.getElementById('week-filter');

            if (weekFilter) {
                // ✅ Detectar cambio de semana
                weekFilter.addEventListener("change", function() {
                    const week = this.value;
                    console.log("Semana seleccionada:", week);

                    fetch(`/orders/by-week/ajax?week=${week}`, {
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            },
                        })
                        .then((res) => res.json())
                        .then((data) => {
                            const tbody = document.getElementById("tableweek-body");
                            const count = document.getElementById("order-count");

                            if (!tbody || !count) {
                                console.warn("No se encontró tbody o contador.");
                                return;
                            }

                            // 💡 Destruir DataTable anterior si existe
                            const table = $("#tableweek");
                            if ($.fn.DataTable.isDataTable(table)) {
                                table.DataTable().clear().destroy();
                            }

                            // 💡 Actualizar tbody con nuevas filas
                            tbody.innerHTML = data.html;

                            // 💡 Actualizar contador
                            count.textContent = data.count;

                            // 💡 Reinicializar DataTable
                            initDataTable("#tableweek", "ORDERS THIS WEEK");

                            // 💡 Actualizar texto visible de la semana
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

                            // 👉 Función para obtener lunes de la semana ISO
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
                            console.error("Error fetching data:", error);
                            alert("Error loading data for the selected week.");
                        });

                });

                // ✅ Al cargar la página, establecer semana actual si no hay valor
                if (!weekFilter.value) {
                    const today = new Date();
                    const year = today.getFullYear();

                    const getISOWeek = date => {
                        const tmp = new Date(date.getTime());
                        tmp.setUTCDate(tmp.getUTCDate() + 4 - (tmp.getUTCDay() || 7));
                        const yearStart = new Date(Date.UTC(tmp.getUTCFullYear(), 0, 1));
                        const weekNo = Math.ceil((((tmp - yearStart) / 86400000) + 1) / 7);
                        return weekNo;
                    };

                    const week = getISOWeek(today).toString().padStart(2, '0');
                    const value = `${year}-W${week}`;
                    weekFilter.value = value;

                    // 💥 Disparar manualmente el evento change
                    weekFilter.dispatchEvent(new Event("change"));
                }
            }
        });
    </script>

    @endpush