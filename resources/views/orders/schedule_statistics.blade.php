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
<div class="row">
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success" style="min-height: 110px; max-width: 100%;">
            <div class="d-flex justify-content-between align-items-center px-3 py-2" style="height: 100%;">
                <div>
                    <h3 class="mb-1">{{ $totalOrdenes }}</h3>
                    <p class="mb-0">Order Summary</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-info text-dark d-block mb-1">
                        <i class="fas fa-industry me-1"></i> Hearst: {{ $cantidadHearst }}
                    </span>
                    <span class="badge bg-info text-dark d-block mb-1">
                        <i class="fas fa-industry me-1"></i> Yarnell: {{ $cantidadYarnell }}
                    </span>
                    <span class="badge bg-secondary d-block">
                        <i class="fas fa-industry me-1"></i> Floor: {{ $cantidadFloor }}
                    </span>
                </div>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
        </div>
    </div>

    <!-- ./col -->
    <div class="col-lg-2 col-6">
        <!-- small box -->
        <div class="small-box bg-success" style="min-height: 110px; max-width: 100%;">
            <div class="inner">
                <h3>53<sup style="font-size: 20px">%</sup></h3>

                <p>Pendient</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-2 col-6">
        <!-- small box -->
        <div class="small-box bg-warning" style="min-height: 110px; max-width: 100%;">
            <div class="inner">
                <h3>44</h3>

                <p>Pendient</p>
            </div>
            <div class="icon">
                <i class="ion ion-person-add"></i>
            </div>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-2 col-6">
        <!-- small box -->
        <div class="small-box bg-danger" style="min-height: 110px; max-width: 100%;">
            <div class="inner">
                <h3>{{ $cantidadAtrasadas }}</h3>

                <p>Late Orders</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->





{{-- TABLAS DE ÓRDENES SEMANALES Y ATRASADAS --}}
<div class="row mb-4">
    {{-- Órdenes de esta semana --}}
    <div class="col-md-6">
        <div class="card shadow-sm rounded-3 border-0 mb-4">
            <div class="card-header bg-primary text-white py-3 px-4 d-flex align-items-center">
                <i class="fas fa-calendar-week me-2 fs-5"></i>
                <h6 class="mb-0">Orders this week</h6>
            </div>
            <div class="card-body p-3">
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
                                <td colspan="8" class="text-center text-muted py-3">
                                    <i class="bi bi-check-circle me-2"></i>No orders found.
                                </td>
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

    {{-- Órdenes atrasadas --}}
    <div class="col-md-6">
        <div class="card shadow-sm rounded-3 border-0 mb-4">
            <div class="card-header bg-danger text-white py-3 px-4 d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2 fs-5 text-white"></i>
                <h6 class="mb-0">Late Orders</h6>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="tablelate" class="table table-hover align-middle mb-0 small">
                        <thead class="table-danger text-dark">
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
                                <td colspan="8" class="text-center text-muted py-3">
                                    <i class="bi bi-check-circle me-2"></i>No late orders found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light text-center py-2">
                {{-- Opcional: info adicional --}}
            </div>
        </div>
    </div>
</div>

{{-- CLIENTES CON ÓRDENES --}}
<div class="row">
    <div class="col-md-4 col-sm-6 col-12">
        <div class="card shadow-sm rounded-3 border-0 mb-4" style="overflow: hidden;">
            <div class="card-header bg-gradient-primary text-white py-3 px-4 d-flex justify-content-between align-items-center" style="font-weight: 600; font-size: 1.1rem;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-people-fill fs-3"></i>
                    Customers with Orders
                </div>
                <span class="badge bg-light text-primary fs-6" title="Total de órdenes">{{ $totalOrdenes }}</span>
            </div>

            <div class="card-body px-3 py-2" style="overflow-y: auto;">
                @if ($ordenesPorCliente->isNotEmpty())
                <ul class="list-group list-group-flush small">
                    @foreach ($ordenesPorCliente as $grupo)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                        <span class="text-truncate" style="max-width: 65%;">
                            <i class="bi bi-person-circle me-2 text-muted fs-5"></i>
                            {{ ucfirst($grupo->costumer) }}
                        </span>
                        <span class="badge bg-success rounded-pill fs-6" style="min-width: 35px;">{{ $grupo->total }}</span>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center text-muted small py-5">
                    <i class="bi bi-info-circle fs-2 mb-2"></i>
                    No hay órdenes registradas
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-calendar-plus me-2"></i>Órdenes agregadas esta semana
        </h5>
        <span class="badge bg-light text-primary fs-6">
            Total: {{ $totalAgregadasSemana }}
        </span>
    </div>

    <div class="card-body p-0">
        @if($ordenesAgregadasSemana->count())
            <ul class="list-group list-group-flush">
                @foreach ($ordenesAgregadasSemana as $orden)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong> {{ ucfirst($orden->costumer) }}</strong> PN {{ $orden->PN }}. WORK ID{{ $orden->work_id }}
                            <span class="text-muted">({{ $orden->created_at->format('d M Y') }})</span><br>
                            <small class="text-secondary">
                                {{ ucfirst($orden->location) }} &mdash; Qty:{{ ucfirst($orden->qty) }}
                            </small>
                        </div>
                        <span class="badge bg-success">{{ $orden->status ?? 'Sin estado' }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="p-3 text-center text-muted">
                No se han agregado órdenes esta semana.
            </div>
        @endif
    </div>
</div>



@endsection

@section('css')

@endsection

@push('js')

<script>
    $(document).ready(function() {
        const tableOptions = {
            responsive: true,
            pageLength: 5,
            lengthMenu: [5, 10, 20, 50],
        };

        $('#tableweek, #tablelate').DataTable(tableOptions);
    });
</script>
@endpush