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


    <div class="container-fluid py-4">
        {{-- Cards con tablas --}}
        <div class="row g-4 mb-4">
            {{-- Ordenes esta semana --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-header bg-light d-flex align-items-center">
                        <i class="fas fa-calendar-week text-success fa-lg mr-2"></i>
                        <h6 class="mb-0 text-success">Orders This Week</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tableweek" class="table table-hover align-middle mb-0 small datatable-export">
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
                                    @forelse ($ordenesSemana as $order)
                                    <tr>

                                        <td>{{ $order->work_id }}</td>
                                        <td>{{ $order->PN }}</td>
                                        <td class="text-truncate" style="max-width: 160px;">{{ $order->Part_description }}</td>
                                        <td>{{ ucfirst($order->costumer) }}</td>
                                        <td>{{ $order->qty }}</td>
                                        <td><span class="badge bg-info text-dark">{{ $order->status }}</span></td>
                                        <td><span class="text-primary fw-semibold">{{ $order->due_date->format('d/m/Y') }}</span></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">No orders found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-center py-2">
                        <small class="text-muted">Total orders this week: <strong>{{ $ordenesSemana->count() }}</strong></small>
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
                                        <td class="text-truncate" style="max-width: 160px;">{{ $order->Part_description }}</td>
                                        <td>{{ ucfirst($order->costumer) }}</td>
                                        <td>{{ $order->qty }}</td>
                                        <td><span class="badge bg-warning text-dark">{{ $order->status }}</span></td>
                                        <td><span class="text-danger fw-bold">{{ $order->due_date->format('d/m/Y') }}</span></td>
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
        <div class="row mb-2">
            {{-- Card: Clientes con órdenes --}}
            <div id="card-to-print" class="col-md-4 col-sm-6 mb-3">
                <div class="card shadow-sm rounded-3 border-0 h-100">
                    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 fw-semibold fs-5">
                            <i class="bi bi-people-fill"></i>
                            Customers with Orders
                        </div>
                        <span class="badge bg-light text-primary fs-6">{{ $totalOrdenes }}</span> <button onclick="printCard()" class="btn btn-primary mb-3">Print</button>
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
                                    <strong>{{ ucfirst($orden->costumer) }}</strong> — PN {{ $orden->PN }} ( Qty: {{ $orden->qty }} )<br>
                                    <small class="text-secondary">WORK ID {{ $orden->work_id }} — {{ ucfirst($orden->location) }}. {{ $orden->created_at->format('d M Y') }} </small><br>
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
                            <button onclick="printChart('byCustomerChart', 'ORDERS PER CUSTOMER')" class="btn btn-secondary mb-2 w-75">
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
            $(document).ready(function() {

                function getFechaHoraActual() {
                    const now = new Date();
                    const fecha = now.toLocaleDateString('en-US');
                    const hora = now.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    return `${fecha} ${hora}`;
                }
                // Inicializar DataTables con exportación y buscador
                const logoBase64 = 'data:image/png;base64,...'; // <-- reemplaza con tu base64 real
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
                                title: `${tableTitle} - ${fechaHora}`,// <-- Título en archivo Excel
                                text: '<i class="fas fa-file-excel"></i> Excel',
                                className: 'btn btn-success btn-sm mx-0',
                                filename: `${tableTitle.replace(/\s+/g, '_')}_${fechaHora.replace(/[/: ]/g, '_')}`
                            },
                            {
                                extend: 'pdfHtml5',
                                title: `${tableTitle} - ${fechaHora}`, // <-- Título del PDF
                                text: '<i class="fas fa-file-pdf"></i> PDF',
                                className: 'btn btn-danger btn-sm mx-1',
                                orientation: 'landscape',
                                pageSize: 'A4',
                                filename: `${tableTitle.replace(/\s+/g, '_')}_${fechaHora.replace(/[/: ]/g, '_')}`

                            },
                            {
                                extend: 'print',
                                title: `${tableTitle} - ${fechaHora}`, // <-- Título al imprimir
                                text: '<i class="fas fa-print"></i> Print',
                                className: 'btn btn-primary btn-sm'
                            }
                        ],
                        searching: true,
                    });
                }

                // Inicializa ambas tablas con títulos PDF personalizados
                initDataTable('#tableweek', 'ORDERS THIS WEEK');
                initDataTable('#tablelate', 'LATE ORDERS');

                // Variables para filtros y gráficos
                let currentFilterText = '';

                const filterType = document.getElementById('filterType');
                const yearInput = document.getElementById('yearInput');
                const monthInput = document.getElementById('monthInput');
                const weekInput = document.getElementById('weekInput');
                const customerFilter = document.getElementById('customerFilter');
                const ctx = document.getElementById('ordersChart')?.getContext('2d');
                let chart;

                const filterTypeCustomer = document.getElementById('filterTypeCustomer');
                const yearInputCustomer = document.getElementById('yearInputCustomer');
                const monthInputCustomer = document.getElementById('monthInputCustomer');
                const weekInputCustomer = document.getElementById('weekInputCustomer');
                const ctx2 = document.getElementById('byCustomerChart')?.getContext('2d');
                let customerChart;

                // Función para mostrar/ocultar inputs según filtro seleccionado
                function updateVisibleInputs(typeSelect, yearInp, monthInp, weekInp) {
                    if (!yearInp || !monthInp || !weekInp) return;

                    yearInp.classList.add('d-none');
                    monthInp.classList.add('d-none');
                    weekInp.classList.add('d-none');

                    if (typeSelect.value === 'year') {
                        yearInp.classList.remove('d-none');
                    } else if (typeSelect.value === 'month') {
                        monthInp.classList.remove('d-none');
                    } else if (typeSelect.value === 'week') {
                        weekInp.classList.remove('d-none');
                    }
                }

                // Función para cargar gráfico general de órdenes
                function loadChart() {
                    if (!filterType || !ctx) return;

                    let url = '/orders/summary';
                    currentFilterText = '';

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
                    } else {
                        currentFilterText = 'Todos los períodos';
                    }

                    if (customerFilter?.value) {
                        const separator = url.includes('?') ? '&' : '?';
                        url += `${separator}customer=${encodeURIComponent(customerFilter.value)}`;
                        currentFilterText += `<br>Customer: ${customerFilter.value}`;
                    }

                    fetch(url)
                        .then(res => {
                            if (!res.ok) throw new Error(`Error HTTP ${res.status}`);
                            return res.json();
                        })
                        .then(({
                            labels,
                            data
                        }) => {
                            if (chart) chart.destroy();

                            const totalOrders = data.reduce((acc, val) => acc + val, 0);

                            chart = new Chart(ctx, {
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
                        })
                        .catch(err => console.error('Error al cargar datos:', err));
                }

                // Función para cargar gráfico de órdenes por cliente
                function loadCustomerChart() {
                    if (!filterTypeCustomer || !ctx2) return;

                    let url = '/orders/summary/by-customer';

                    if (filterTypeCustomer.value === 'year') {
                        if (!yearInputCustomer?.value) return;
                        url += `/year/${yearInputCustomer.value}`;
                    } else if (filterTypeCustomer.value === 'month') {
                        if (!monthInputCustomer?.value) return;
                        const [year, month] = monthInputCustomer.value.split('-');
                        url += `/month/${year}/${month}`;
                    } else if (filterTypeCustomer.value === 'week') {
                        if (!weekInputCustomer?.value) return;
                        const [year, week] = weekInputCustomer.value.split('-W');
                        url += `/week/${year}/${week}`;
                    }

                    fetch(url)
                        .then(res => {
                            if (!res.ok) throw new Error(`Error HTTP ${res.status}`);
                            return res.json();
                        })
                        .then(({
                            labels,
                            totals,
                            totalAll
                        }) => {
                            if (customerChart) customerChart.destroy();

                            customerChart = new Chart(ctx2, {
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
                                    indexAxis: 'y',
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            position: 'left',
                                            title: {
                                                display: true,
                                                text: 'CUSTOMER'
                                            }
                                        }
                                    },
                                    plugins: {
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false
                                        },
                                        datalabels: {
                                            anchor: 'end',
                                            align: 'right',
                                            color: '#fff',
                                            font: {
                                                weight: 'bold',
                                                size: 12
                                            },
                                            formatter: value => value
                                        }
                                    }
                                },
                                plugins: [ChartDataLabels]
                            });
                        })
                        .catch(err => console.error('Error al cargar gráfico por cliente:', err));
                }

                // Eventos para cambiar inputs visibles y recargar gráficos
                if (filterType) {
                    filterType.addEventListener('change', () => {
                        updateVisibleInputs(filterType, yearInput, monthInput, weekInput);
                        loadChart();
                    });
                }
                if (filterTypeCustomer) {
                    filterTypeCustomer.addEventListener('change', () => {
                        updateVisibleInputs(filterTypeCustomer, yearInputCustomer, monthInputCustomer, weekInputCustomer);
                        loadCustomerChart();
                    });
                }

                [yearInput, monthInput, weekInput, customerFilter].forEach(el => {
                    if (el) el.addEventListener('change', loadChart);
                });

                [yearInputCustomer, monthInputCustomer, weekInputCustomer].forEach(el => {
                    if (el) el.addEventListener('change', loadCustomerChart);
                });

                // Inicializar inputs visibles según filtro y cargar gráficos
                if (filterType && yearInput && monthInput && weekInput)
                    updateVisibleInputs(filterType, yearInput, monthInput, weekInput);
                if (filterTypeCustomer && yearInputCustomer && monthInputCustomer && weekInputCustomer)
                    updateVisibleInputs(filterTypeCustomer, yearInputCustomer, monthInputCustomer, weekInputCustomer);

                loadChart();
                loadCustomerChart();

                // Botones para imprimir gráficos
                const printOrdersBtn = document.getElementById('printOrdersChartBtn');
                if (printOrdersBtn) {
                    printOrdersBtn.addEventListener('click', () => printChart('ordersChart', 'TOTAL ORDERS'));
                }
                const printCustomerBtn = document.getElementById('printCustomerChartBtn');
                if (printCustomerBtn) {
                    printCustomerBtn.addEventListener('click', () => printChart('byCustomerChart', 'ORDERS PER CUSTOMER'));
                }

                // Función para imprimir gráfico dado el ID del canvas
                function printChart(canvasId, chartTitle) {
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
    <div class="filter-info">${currentFilterText}</div>
    <img src="${dataUrl}" />
    <script>
        window.onload = () => { window.print(); };
    <\/script>
</body>
</html>
        `);
                    printWindow.document.close();
                }

                // Función para imprimir el contenido de un card (útil si tienes un div específico)
                window.printCard = function() {
                    const cardContent = document.getElementById('card-to-print');
                    if (!cardContent) {
                        alert("No se encontró el elemento para imprimir.");
                        return;
                    }
                    const htmlContent = cardContent.innerHTML;
                    const myWindow = window.open('', 'Print', 'width=700,height=700');
                    myWindow.document.write(`
<html>
  <head>
    <title>Imprimir Card</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
      @media print {
        body {
          margin: 1cm;
          font-size: 12pt;
          color: #000;
          background: #fff !important;
        }
        .card {
          box-shadow: none !important;
          border: 1px solid #000 !important;
          page-break-inside: avoid;
        }
        .card-body {
          max-height: none !important;
          overflow: visible !important;
        }
        button, .no-print {
          display: none !important;
        }
        .card-header {
          background: #ccc !important;
          color: #000 !important;
        }
        i.bi {
          color: #000 !important;
        }
      }
      body {
        margin: 20px;
      }
    </style>
  </head>
  <body>
    ${htmlContent}
  </body>
</html>
        `);
                    myWindow.document.close();
                    myWindow.focus();
                    myWindow.print();
                    myWindow.close();
                };
            });
        </script>

        @endpush