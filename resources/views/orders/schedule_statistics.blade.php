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
                            <input type="week" name="week" id="week-filter" class="form-control border-secondary text-dark font-weight-bold"
                                style="height: 32px;">
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
                                @for ($y = date('Y'); $y >= 2020; $y--)
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
                    <span class="badge bg-light text-primary fs-6">{{ $totalOrdenes }}</span> <button
                        onclick="printCard()" class="btn btn-primary mb-3">Print</button>
                </div>
                <div class="card-body px-3 py-2" style="max-height: 280px; overflow-y: auto;">
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
            <div class="card shadow-sm rounded-3 border-0 h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 fw-semibold fs-5">
                        <i class="fas fa-calendar-plus"></i>
                        Orders Added This Week
                    </div>
                    <span class="badge bg-light text-primary fs-6">{{ $totalAgregadasSemana }}</span>
                </div>
                <div class="card-body px-3 py-2" style="max-height: 280px; overflow-y: auto;">
                    @if ($ordenesAgregadasSemana->isNotEmpty())
                    <ul class="list-group list-group-flush small">
                        @foreach ($ordenesAgregadasSemana as $orden)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                            <div class="text-truncate" style="max-width: 65%;">
                                <strong>{{ ucfirst($orden->costumer) }}</strong> — PN {{ $orden->PN }} ( Qty:
                                {{ $orden->qty }} )<br>
                                <small class="text-secondary">WORK ID {{ $orden->work_id }} —
                                    {{ ucfirst($orden->location) }}. {{ $orden->created_at->format('d M Y') }}
                                </small><br>
                            </div>
                            <span class="badge bg-success">{{ $orden->status ?? 'No status' }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="text-center text-muted small py-5">
                        <i class="bi bi-info-circle fs-2 mb-2"></i>
                        No orders added this week.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card shadow-sm rounded-3 border-0 h-100">
                <div class="card-header bg-secondary text-white d-flex align-items-center fw-semibold fs-5">
                    <i class="fas fa-box me-2"></i> Example Third Card
                </div>
                <div class="card-body px-3 py-2 text-muted small">
                    Content for a third card goes here.
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