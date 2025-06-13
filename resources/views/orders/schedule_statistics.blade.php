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
                            <span class="badge bg-secondary bg-opacity-25" style="font-size: 1rem;">
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
                <span class="info-box-icon bg-warning shadow-sm">
                    <i class="fas fa-exclamation-triangle"></i> <!-- Ícono de engranaje múltiple -->
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Sales</span>
                    <span class="info-box-number">760</span>
                </div>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-light shadow-sm">
                    <i class="fas fa-exclamation-triangle"></i> <!-- Ícono de engranaje múltiple -->
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">New Orders</span>
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
                    <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                        <i class="fas fa-calendar-week fs-5"></i>
                        <h6 class="mb-0">Orders This Week</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tableweek" class="table table-hover align-middle mb-0 small">
                                <thead class="table-primary text-dark">
                                    <tr>
                                        <th>ID</th>
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
                                        <td>{{ $order->id }}</td>
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
                    <div class="card-header bg-light text-white d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-triangle fs-5"></i>
                        <h6 class="mb-0">Late Orders</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tablelate" class="table table-hover align-middle mb-0 small">
                                <thead class="text-dark">
                                    <tr>
                                        <th>ID</th>
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
                                        <td>{{ $order->id }}</td>
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
                        <small class="text-muted">Total orders this week: <strong>{{ $ordenesSemana->count() }}</strong></small>
                    </div>
                </div>
            </div>

        </div>
        <div class="row mb-2">
            {{-- Card: Clientes con órdenes --}}
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card shadow-sm rounded-3 border-0 h-100">
                    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 fw-semibold fs-5">
                            <i class="bi bi-people-fill"></i>
                            Customers with Orders
                        </div>
                        <span class="badge bg-light text-primary fs-6">{{ $totalOrdenes }}</span>
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


        <div class="row mb-3">
            <div class="col-md-3">
                <label for="filterType">Year / Month / Week:</label>
                <select id="filterType" class="form-control">
                    <option value="year">Year</option>
                    <option value="month">Month</option>
                    <option value="week">Week</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="yearInput">Date:</label>
                <input type="month" id="monthInput" class="form-control d-none">
                <input type="week" id="weekInput" class="form-control d-none">
                <select id="yearInput" class="form-control">
                    @for ($y = date('Y'); $y >= 2020; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3">
                <label for="customerFilter">Customer:</label>
                <select id="customerFilter" class="form-control">
                    <option value="">All Customers</option>
                    @foreach ($customers as $customer)
                    <option value="{{ $customer }}">{{ $customer }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <canvas id="ordersChart" height="100"></canvas>

        <div class="row mb-3">
            <!-- Selector tipo filtro (año, mes, semana) -->
            <div class="col-md-3">
                <label for="filterTypeCustomer">Year / Month / Week:</label>
                <select id="filterTypeCustomer" class="form-control">
                    <option value="year" selected>Year</option>
                    <option value="month">Month</option>
                    <option value="week">Week</option>
                </select>
            </div>

            <!-- Inputs según tipo de filtro -->
            <div class="col-md-3">
                <label for="yearInputCustomer">Date:</label>
                <!-- Para año, un select con años -->
                <select id="yearInputCustomer" class="form-control">
                    @for ($y = date('Y'); $y >= 2020; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
                <!-- Para mes, input tipo month -->
                <input type="month" id="monthInputCustomer" class="form-control d-none">
                <!-- Para semana, input tipo week -->
                <input type="week" id="weekInputCustomer" class="form-control d-none">
            </div>
        </div>









        <canvas id="byCustomerChart" height="120"></canvas>

        @endsection

        @section('css')



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
            document.addEventListener('DOMContentLoaded', () => {
                // --- Ordenes general ---
                const filterType = document.getElementById('filterType');
                const yearInput = document.getElementById('yearInput');
                const monthInput = document.getElementById('monthInput');
                const weekInput = document.getElementById('weekInput');
                const customerFilter = document.getElementById('customerFilter');
                const ctx = document.getElementById('ordersChart').getContext('2d');
                let chart;

                // --- Órdenes por cliente ---
                const filterTypeCustomer = document.getElementById('filterTypeCustomer');
                const yearInputCustomer = document.getElementById('yearInputCustomer');
                const monthInputCustomer = document.getElementById('monthInputCustomer');
                const weekInputCustomer = document.getElementById('weekInputCustomer');
                const ctx2 = document.getElementById('byCustomerChart').getContext('2d');
                let customerChart;

                // Función para mostrar inputs según filtro seleccionado (genérica)
                function updateVisibleInputs(typeSelect, yearInp, monthInp, weekInp) {
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

                // Cargar gráfico de órdenes general
                function loadChart() {
                    let url = '/orders/summary';

                    if (filterType.value === 'year') {
                        if (!yearInput.value) return;
                        url += `/year/${yearInput.value}`;
                    } else if (filterType.value === 'month') {
                        if (!monthInput.value) return;
                        const [year, month] = monthInput.value.split('-');
                        url += `/month/${year}/${month}`;
                    } else if (filterType.value === 'week') {
                        if (!weekInput.value) return;
                        const [year, week] = weekInput.value.split('-W');
                        url += `/week/${year}/${week}`;
                    }

                    if (customerFilter.value) {
                        url += `?customer=${encodeURIComponent(customerFilter.value)}`;
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
                            chart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels,
                                    datasets: [{
                                        label: 'Órdenes',
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

                // Cargar gráfico de órdenes por cliente con filtros independientes
                function loadCustomerChart() {
                    let url = '/orders/summary/by-customer';

                    if (filterTypeCustomer.value === 'year') {
                        if (!yearInputCustomer.value) return;
                        url += `/year/${yearInputCustomer.value}`;
                    } else if (filterTypeCustomer.value === 'month') {
                        if (!monthInputCustomer.value) return;
                        const [year, month] = monthInputCustomer.value.split('-');
                        url += `/month/${year}/${month}`;
                    } else if (filterTypeCustomer.value === 'week') {
                        if (!weekInputCustomer.value) return;
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
                            percentages,
                            totalAll
                        }) => {

                            if (customerChart) customerChart.destroy();
                            customerChart = new Chart(ctx2, {
                                type: 'bar',
                                data: {
                                    labels,
                                    datasets: [{
                                            label: 'Órdenes por Cliente',
                                            data: totals,
                                            backgroundColor: 'rgba(153, 102, 255, 0.7)',
                                            borderColor: 'rgba(153, 102, 255, 1)',
                                            borderWidth: 1,
                                            yAxisID: 'y',
                                        },


                                    ]
                                },
                                options: {
                                    indexAxis: 'y',
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            position: 'left',
                                            title: {
                                                display: true,
                                                text: `Órdenes (Total: ${totalAll})`
                                            }
                                        }
                                    },
                                    plugins: {
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                        }
                                    }
                                }
                            });
                        })
                        .catch(err => console.error('Error al cargar gráfico por cliente:', err));
                }

                // Eventos para mostrar inputs según filtro
                filterType.addEventListener('change', () => {
                    updateVisibleInputs(filterType, yearInput, monthInput, weekInput);
                    loadChart();
                });
                filterTypeCustomer.addEventListener('change', () => {
                    updateVisibleInputs(filterTypeCustomer, yearInputCustomer, monthInputCustomer, weekInputCustomer);
                    loadCustomerChart();
                });

                // Eventos para inputs que cambian el gráfico
                [yearInput, monthInput, weekInput, customerFilter].forEach(el => el.addEventListener('change', loadChart));
                [yearInputCustomer, monthInputCustomer, weekInputCustomer].forEach(el => el.addEventListener('change', loadCustomerChart));

                // Inicializar
                updateVisibleInputs(filterType, yearInput, monthInput, weekInput);
                updateVisibleInputs(filterTypeCustomer, yearInputCustomer, monthInputCustomer, weekInputCustomer);
                loadChart();
                loadCustomerChart();
            });
        </script>
        @endpush