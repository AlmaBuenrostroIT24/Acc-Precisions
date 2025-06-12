<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'Schedule Orders')
@section('meta') {{-- ✅ Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content_header')
<div class="card bg-light d-flex justify-content-center align-items-center" style="height: 50px; padding: 0 15px;">
    <h2 class="text-dark" style="font-size: 24px; margin: 0;">
        <i class="fas fa-box"></i> Schedule Orders
    </h2>
</div>
@endsection


@section('content')



{{-- Tabs --}}
@include('orders.schedule_tab')

    {{-- Tab: By Active Schedules --}}

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    {{-- Filtros dinámicos --}}
                    <div class="row mb-4">
                        <!-- Formulario de carga -->
                        <div class="col-md-4">
                            <div class="card shadow">
                                <form id="upload-form" action="{{ route('schedule.orders.import') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="csv_file">Field CSV</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="csv_file" id="csv_file" accept=".csv" required>
                                                    <label id="csv_file_label" class="custom-file-label" for="csv_file">Select file</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Subir</button>
                                    </div>
                                </form>
                                <!-- Indicador de carga -->
                                <div id="loading-message" style="display:none; text-align: center; padding: 20px; font-size: 16px; color: #007bff;">
                                    <i class="fas fa-spinner fa-spin"></i> Uploading file, please wait...
                                </div>
                                @if (session('success'))
                                <div id="success-message" class="alert alert-success alert-message mt-3">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    {{ session('success') }}
                                </div>
                                @endif
                            </div>
                        </div>
                        <!-- Filtros -->
                        <div class="col-md-8">
                            <div class="card shadow">
                                <div class="card-body row">
                                    <div class="form-group col-md-12">
                                        <form method="GET" action="{{ route('schedule.general') }}" id="filterForm" class="row g-3 mb-3">
                                            <div class="form-group col-md-4">
                                                <label for="locationFilter">Location</label>
                                                <select name="location" id="locationFilter" class="form-control auto-submit">
                                                    <option value="">-- All --</option>
                                                    @foreach($locations as $location)
                                                    <option value="{{ strtolower($location) }}" {{ strtolower(request('location')) == strtolower($location) ? 'selected' : '' }}>
                                                        {{ $location ?? 'Sin asignar' }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="statusFilter">Status</label>
                                                <select name="status" id="statusFilter" class="form-control auto-submit">
                                                    <option value="">-- All --</option>
                                                    @foreach($orders->pluck('status')->unique() as $status)
                                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                        {{ $status ?? 'Sin estado' }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="customerFilter">Customer</label>
                                                <select id="customerFilter" class="form-control auto-submit">
                                                    <option value="">-- All --</option>
                                                    @foreach($customers as $customer)
                                                    <option value="{{ strtolower($customer) }}" {{ strtolower(request('customer')) == strtolower($customer) ? 'selected' : '' }}>
                                                        {{ $customer }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--   <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="fas fa-plus"></i> New Order
                        </button> -->
                    @include('orders.schedule_table')
                </div>
            </div>
        </div>
    </div>


<!--  {{-- Tab: By End Schedule --}}-->


<!-- Modal -->
@include('orders.schedule_modaltable')

@endsection


@section('css')
<link rel="stylesheet" href="{{ asset('vendor/css/orders-schedule.css') }}">
@endsection

@push('js')

<script src="{{ asset('vendor/js/orders-schedule.js') }}"></script>


<script>

</script>
@endpush