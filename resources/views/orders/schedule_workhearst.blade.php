<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Completed Orders')
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
                <li class="breadcrumb-item active" aria-current="page">Orders Yarnell</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')

{{-- Tabs --}}
@include('orders.schedule_tab')

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    {{-- Tabla --}}
                    <table id="workhearst_Table" class="table table-bordered  table-sm nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>LOCATION</th>
                                <th>WORK ID</th>
                                <th>PN</th>
                                <th>PART/DESCRIPTION</th>
                                <th>CUSTOMER</th>
                                <th>CO QTY</th>
                                <th>WO QTY</th>
                                <th>STATUS</th>
                                <th>REPORT</th>
                                <th>OUT</th>
                                <th>MACH DATE</th>
                                <th>DUE DATE</th>
                                <th>NOTES</th>
                            </tr>
                        </thead>
                        <tbody id="statusTable">
                            @foreach($orders as $order)

                            @php
                            $status = strtolower($order->status);
                            $statusClass = match($status) {
                            'pending' => 'bg-status-pending',
                            'waitingformaterial' => 'bg-status-waitingformaterial',
                            'cutmaterial' => 'bg-status-cutmaterial',
                            'grinding' => 'bg-status-grinding',
                            'onrack' => 'bg-status-onrack',
                            'programming' => 'bg-status-programming',
                            'setup' => 'bg-status-setup',
                            'machining' => 'bg-status-machining',
                            'marking' => 'bg-status-marking',
                            'deburring' => 'bg-status-deburring',
                            'qa' => 'bg-status-qa',
                            'outsource' => 'bg-status-outsource',
                            'assembly' => 'bg-status-assembly',
                            'shipping' => 'bg-status-shipping',
                            'sent' => 'bg-status-sent',
                            'ready' => 'bg-status-ready',
                            'onhold' => 'bg-status-onhold',
                            default => '',
                            };
                            @endphp
                            <tr class="{{ $statusClass }}" data-status="{{ $order->status }}">
                                <td>
                                    @if ($order->last_location === 'Yarnell')
                                    <span class="fw-bold text-dark">Yarnell</span>
                                    @endif
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $order->location }}
                                    </span>
                                </td>
                                <td>{{ $order->work_id }}</td>
                                <td style="min-width: 120px;">{{ $order->PN }}</td>
                                <td style="font-size: 12px;">{{ $order->Part_description }}</td>
                                <td>{{ $order->costumer }}</td>
                                <td>{{ $order->qty }}</td>
                                <td>{{ $order->wo_qty }}</td>
                                <td>
                                    <span class="badge bg-secondary text-white">{{ ucfirst($status) }}</span>

                                </td>
                                <td>
                                    <button class="btn btn-sm toggle-report-btn {{ $order->report ? 'btn-primary' : 'btn-secondary' }}"
                                        data-id="{{ $order->id }}" data-value="{{ $order->report ? 1 : 0 }}"
                                        style="cursor: default; pointer-events: none;">
                                        <i class="fas {{ $order->report ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    </button>
                                </td>
                                <td>
                                    <button class="btn btn-sm toggle-source-btn {{ $order->our_source ? 'btn-primary' : 'btn-secondary' }}"
                                        data-id="{{ $order->id }}" data-value="{{ $order->our_source }}"
                                        style="cursor: default; pointer-events: none;">
                                        <i class="fas {{ $order->our_source ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    </button>
                                </td>
                                <td>{{ optional($order->machining_date)->format('M-d-y') }}</td>
                                <td>{{ optional($order->due_date)->format('M-d-y') }}</td>
                                <td>
                                    <span class="open-notes-modal" data-id="{{ $order->id }}"
                                        data-notes="{{ e($order->notes) }}" title="{{ e($order->notes) }}">
                                        {{ Str::limit($order->notes, 30) }}
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

<div class="row">
    {{-- Tarjeta 1 --}}
    {{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
    <div class="col-md-6 col-sm-6 mb-3">
        <div class="card shadow-sm rounded-3 border-0 h-100">
            {{-- Header --}}
            <div class="card-header bg-secondary text-white d-flex align-items-center font-weight-semibold">
                <i class="fas fa-calendar-week mr-2"></i> READY TO DELIVER
            </div>
            <div class="card-body">
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="ordersReady_Table"
                        class="table table-bordered table-sm nowrap small mb-0 text-nowrap align-middle"
                        style="table-layout: fixed; width: 100%;">
                        <thead class="table-light text-center small">
                            <tr class="text-center">
                                <th style="width: 40px;">LOC.</th>
                                <th style="width: 50px;">WORK ID</th>
                                <th style="width: 80px;">PN</th>
                                <th style="width: 110px;">PART</th>
                                <th style="width: 70px;">CUSTOMER</th>
                                <th style="width: 45px;">CO QTY</th>
                                <th style="width: 45px;">WO QTY</th>
                                <th style="width: 30px;">STATUS</th>
                                <th style="width: 60px;">DUE DATE</th>
                            </tr>
                        </thead>
                        <tbody id="statusTable">
                            @foreach($ordersReady as $order)
                            @php
                            $status = strtolower($order->status);
                            $statusClass = match($status) {
                            'pending' => 'bg-status-pending',
                            'waitingformaterial' => 'bg-status-waitingformaterial',
                            'cutmaterial' => 'bg-status-cutmaterial',
                            'grinding' => 'bg-status-grinding',
                            'onrack' => 'bg-status-onrack',
                            'programming' => 'bg-status-programming',
                            'setup' => 'bg-status-setup',
                            'machining' => 'bg-status-machining',
                            'marking' => 'bg-status-marking',
                            'deburring' => 'bg-status-deburring',
                            'qa' => 'bg-status-qa',
                            'outsource' => 'bg-status-outsource',
                            'assembly' => 'bg-status-assembly',
                            'shipping' => 'bg-status-shipping',
                            'sent' => 'bg-status-sent',
                            'ready' => 'bg-status-ready',
                            'onhold' => 'bg-status-onhold',
                            default => '',
                            };
                            @endphp
                            <tr class="{{ $statusClass }} text-nowrap align-middle small">
                                <td>
                                    @if ($order->last_location === 'Yarnell')
                                    <span class="fw-bold text-dark d-block">Yarnell</span>
                                    @endif
                                    <span class="badge bg-warning text-dark d-block mt-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $order->location }}
                                    </span>
                                </td>
                                <td>{{ $order->work_id }}</td>
                                <td>{{ $order->PN }}</td>
                                <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                    {{ $order->Part_description }}
                                </td>
                                <td>{{ $order->costumer }}</td>
                                <td>{{ $order->qty }}</td>
                                <td>{{ $order->wo_qty }}</td>
                                <td>
                                    <span class="badge bg-success text-white">{{ ucfirst($status) }}</span>
                                </td>
                                <td>{{ optional($order->due_date)->format('M-d-y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
    <div class="col-md-6 col-sm-6 mb-3">
        <div class="card shadow-sm rounded-3 border-0 h-100">
            {{-- Header --}}
            <div class="card-header bg-secondary text-white d-flex align-items-center font-weight-semibold">
                <i class="fas fa-calendar-week mr-2"></i> ORDERS IN DEBURRING
            </div>
            <div class="card-body">
                {{-- Tabla de órdenes por semana --}}
                <div class="table-responsive">
                    <table id="ordersDeburring_Table"
                        class="table table-bordered table-sm nowrap small mb-0 text-nowrap align-middle"
                        style="table-layout: fixed; width: 100%;">
                        <thead class="table-light text-center small">
                            <tr class="text-center">
                                <th style="width: 40px;">LOC</th>
                                <th style="width: 50px;">WORK ID</th>
                                <th style="width: 80px;">PN</th>
                                <th style="width: 100px;">PART</th>
                                <th style="width: 70px;">CUSTOMER</th>
                                <th style="width: 45px;">CO QTY</th>
                                <th style="width: 45px;">WO QTY</th>
                                <th style="width: 50px;">STATUS</th>
                                <th style="width: 50px;">DUE DATE</th>
                            </tr>
                        </thead>
                        <tbody id="statusTable">
                            @foreach($ordersDeburring as $order)
                            @php
                            $status = strtolower($order->status);
                            $statusClass = match($status) {
                            'pending' => 'bg-status-pending',
                            'waitingformaterial' => 'bg-status-waitingformaterial',
                            'cutmaterial' => 'bg-status-cutmaterial',
                            'grinding' => 'bg-status-grinding',
                            'onrack' => 'bg-status-onrack',
                            'programming' => 'bg-status-programming',
                            'setup' => 'bg-status-setup',
                            'machining' => 'bg-status-machining',
                            'marking' => 'bg-status-marking',
                            'deburring' => 'bg-status-deburring',
                            'qa' => 'bg-status-qa',
                            'outsource' => 'bg-status-outsource',
                            'assembly' => 'bg-status-assembly',
                            'shipping' => 'bg-status-shipping',
                            'sent' => 'bg-status-sent',
                            'ready' => 'bg-status-ready',
                            'onhold' => 'bg-status-onhold',
                            default => '',
                            };
                            @endphp
                            <tr class="{{ $statusClass }} text-nowrap align-middle small">
                                <td>
                                    @if ($order->last_location === 'Yarnell')
                                    <span class="fw-bold text-dark d-block">Yarnell</span>
                                    @endif
                                    <span class="badge bg-warning text-dark d-block mt-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $order->location }}
                                    </span>
                                </td>
                                <td>{{ $order->work_id }}</td>
                                <td>{{ $order->PN }}</td>
                                <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                                    {{ $order->Part_description }}
                                </td>
                                <td>{{ $order->costumer }}</td>
                                <td>{{ $order->qty }}</td>
                                <td>{{ $order->wo_qty }}</td>
                                <td>
                                    <span class="badge bg-success text-white">{{ ucfirst($status) }}</span>
                                </td>
                                <td>{{ optional($order->due_date)->format('M-d-y') }}</td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
<div class="col-md-6 col-sm-6 mb-3">
    <div class="card shadow-sm rounded-3 border-0 h-100">
        {{-- Header --}}
        <div class="card-header bg-success text-white d-flex align-items-center font-weight-semibold">
            <i class="fas fa-calendar-week mr-2"></i> Orders Ready
        </div>

        {{-- Tabla de órdenes por semana --}}
        <div class="table-responsive">
            <table id="workhearst_Table"
                class="table table-bordered table-sm nowrap small mb-0 text-nowrap align-middle"
                style="table-layout: fixed; width: 100%;">
                <thead class="table-light text-center small">
                    <tr class="text-center">
                        <th style="width: 60px;">LOCATION</th>
                        <th style="width: 70px;">WORK ID</th>
                        <th style="width: 90px;">PN</th>
                        <th style="width: 120px;">PART</th>
                        <th style="width: 100px;">CUSTOMER</th>
                        <th style="width: 50px;">CO QTY</th>
                        <th style="width: 50px;">WO QTY</th>
                        <th style="width: 50px;">STATUS</th>
                        <th style="width: 70px;">DUE DATE</th>
                        <th style="width: 100px;">NOTES</th>
                    </tr>
                </thead>
                <tbody id="statusTable">
                    @foreach($ordersReady as $order)
                    @php
                    $status = strtolower($order->status);
                    $statusClass = match($status) {
                    'pending' => 'bg-status-pending',
                    'waitingformaterial' => 'bg-status-waitingformaterial',
                    'cutmaterial' => 'bg-status-cutmaterial',
                    'grinding' => 'bg-status-grinding',
                    'onrack' => 'bg-status-onrack',
                    'programming' => 'bg-status-programming',
                    'setup' => 'bg-status-setup',
                    'machining' => 'bg-status-machining',
                    'marking' => 'bg-status-marking',
                    'deburring' => 'bg-status-deburring',
                    'qa' => 'bg-status-qa',
                    'outsource' => 'bg-status-outsource',
                    'assembly' => 'bg-status-assembly',
                    'shipping' => 'bg-status-shipping',
                    'sent' => 'bg-status-sent',
                    'ready' => 'bg-status-ready',
                    'onhold' => 'bg-status-onhold',
                    default => '',
                    };
                    @endphp
                    <tr class="{{ $statusClass }} text-nowrap align-middle small">
                        <td>
                            @if ($order->last_location === 'Yarnell')
                            <span class="fw-bold text-dark">Yarnell</span>
                            @endif
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $order->location }}
                            </span>
                        </td>
                        <td>{{ $order->work_id }}</td>
                        <td>{{ $order->PN }}</td>
                        <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                            {{ $order->Part_description }}
                        </td>
                        <td>{{ $order->costumer }}</td>
                        <td>{{ $order->qty }}</td>
                        <td>{{ $order->wo_qty }}</td>
                        <td>
                            <span class="badge bg-success text-white">{{ ucfirst($status) }}</span>

                        </td>

                        <td>{{ optional($order->due_date)->format('M-d-y') }}</td>
                        <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                            {{ $order->notes}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Card: Aquí puedes agregar una tercera tarjeta si la necesitas --}}
<div class="col-md-6 col-sm-6 mb-3">
    <div class="card shadow-sm rounded-3 border-0 h-100">
        {{-- Header --}}
        <div class="card-header bg-success text-white d-flex align-items-center font-weight-semibold">
            <i class="fas fa-calendar-week mr-2"></i> Orders Ready
        </div>

        {{-- Tabla de órdenes por semana --}}
        <div class="table-responsive">
            <table id="workhearst_Table"
                class="table table-bordered table-sm nowrap small mb-0 text-nowrap align-middle"
                style="table-layout: fixed; width: 100%;">
                <thead class="table-light text-center small">
                    <tr class="text-center">
                        <th style="width: 60px;">LOCATION</th>
                        <th style="width: 70px;">WORK ID</th>
                        <th style="width: 90px;">PN</th>
                        <th style="width: 120px;">PART</th>
                        <th style="width: 100px;">CUSTOMER</th>
                        <th style="width: 50px;">CO QTY</th>
                        <th style="width: 50px;">WO QTY</th>
                        <th style="width: 50px;">STATUS</th>
                        <th style="width: 70px;">DUE DATE</th>
                        <th style="width: 100px;">NOTES</th>
                    </tr>
                </thead>
                <tbody id="statusTable">
                    @foreach($ordersReady as $order)
                    @php
                    $status = strtolower($order->status);
                    $statusClass = match($status) {
                    'pending' => 'bg-status-pending',
                    'waitingformaterial' => 'bg-status-waitingformaterial',
                    'cutmaterial' => 'bg-status-cutmaterial',
                    'grinding' => 'bg-status-grinding',
                    'onrack' => 'bg-status-onrack',
                    'programming' => 'bg-status-programming',
                    'setup' => 'bg-status-setup',
                    'machining' => 'bg-status-machining',
                    'marking' => 'bg-status-marking',
                    'deburring' => 'bg-status-deburring',
                    'qa' => 'bg-status-qa',
                    'outsource' => 'bg-status-outsource',
                    'assembly' => 'bg-status-assembly',
                    'shipping' => 'bg-status-shipping',
                    'sent' => 'bg-status-sent',
                    'ready' => 'bg-status-ready',
                    'onhold' => 'bg-status-onhold',
                    default => '',
                    };
                    @endphp
                    <tr class="{{ $statusClass }} text-nowrap align-middle small">
                        <td>
                            @if ($order->last_location === 'Yarnell')
                            <span class="fw-bold text-dark">Yarnell</span>
                            @endif
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $order->location }}
                            </span>
                        </td>
                        <td>{{ $order->work_id }}</td>
                        <td>{{ $order->PN }}</td>
                        <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                            {{ $order->Part_description }}
                        </td>
                        <td>{{ $order->costumer }}</td>
                        <td>{{ $order->qty }}</td>
                        <td>{{ $order->wo_qty }}</td>
                        <td>
                            <span class="badge bg-success text-white">{{ ucfirst($status) }}</span>

                        </td>

                        <td>{{ optional($order->due_date)->format('M-d-y') }}</td>
                        <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                            {{ $order->notes}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>





@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/css/orders-schedule.css') }}">
@endsection


@push('js')

<script>
    $(document).ready(function() {

        // Agrega un filtro personalizado para mostrar solo los status diferentes a "sent"
        /*  $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
              const row = settings.aoData[dataIndex].nTr;
              const status = $(row).data('status');
              return status !== 'sent'; // cambia esta línea
          });*/

        // Inicializa la tabla con DataTables
        function initTable(selector, orderColumnIndex = 0, orderDir = 'asc') {
            return $(selector).DataTable({
                scrollX: false,
                autoWidth: false,
                pageLength: 10,
                order: [
                    [orderColumnIndex, orderDir]
                ]
            });
        }

        // Inicializa varias
        window.table1 = initTable('#workhearst_Table', 10, 'desc');
        window.table2 = initTable('#ordersReady_Table', 7, 'desc');
        window.table3 = initTable('#ordersDeburring_Table', 7, 'desc');


    });
</script>
@endpush