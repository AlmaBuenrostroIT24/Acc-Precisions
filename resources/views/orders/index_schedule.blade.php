<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'General Schedule')
@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-calendar-alt me-2" aria-hidden="true"></i>
            Order Schedule
        </h4>

        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">General Schedule</li>
            </ol>
        </nav>
    </div>
</div>
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
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload file</button>
                                </div>
                            </form>
                            <!-- Indicador de carga -->
                            <div id="loading-message" style="display:none; text-align: center; padding: 20px; font-size: 16px; color: #007bff;">
                                <i class="fas fa-spinner fa-spin"></i> Uploading file, please wait...
                            </div>

                        </div>
                    </div>
                    <!-- Filtros -->
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-body row">
                                <div class="form-group col-md-12">
                                    <form method="GET" action="{{ route('schedule.general') }}" id="filterForm" class="row g-3 mb-3">
                                        <div class="form-group col-md-2">
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

                                        <div class="form-group col-md-3">
                                            <label for="statusFilter">Status</label>
                                            <select name="status" id="statusFilter" class="form-control auto-submit">
                                                <option value="">-- All --</option>
                                                @foreach($orders->pluck('status')->unique() as $status)
                                                <option value="{{ strtolower($status) }}" {{ strtolower(request('status')) == strtolower($status) ? 'selected' : '' }}>
                                                    {{ \Illuminate\Support\Str::title(strtolower($status ?? 'Sin estado')) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="customerFilter">Customer</label>
                                            <select id="customerFilter" class="form-control auto-submit">
                                                <option value="">-- All --</option>
                                                @foreach($customers as $customer)
                                                <option value="{{ strtolower($customer) }}" {{ strtolower(request('customer')) == strtolower($customer) ? 'selected' : '' }}>
                                                    {{ \Illuminate\Support\Str::title(strtolower($customer)) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2 align-self-end">
                                            <!-- Botón Delete -->
                                            <button type="button" class="btn btn-danger w-100" data-toggle="modal" data-target="#deleteModal" data-mode="delete">
                                                <i class="fas fa-trash-alt"></i> Delete Order
                                            </button>
                                        </div>
                                        <div class="form-group col-md-2 align-self-end">
                                            <!-- Botón Priority -->
                                            <button type="button" class="btn btn-info w-100" data-toggle="modal" data-target="#deleteModal" data-mode="priority">
                                                <i class="fas fa-star"></i> Priority
                                            </button>
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
@include('orders.schedule_deleteprioritymodalorder')
@endsection


@section('css')
<link rel="stylesheet" href="{{ asset('vendor/css/orders-schedule.css') }}">
@endsection

@push('js')

<script src="{{ asset('vendor/js/orders-schedule.js') }}"></script>


<script>
    let currentActionMode = 'delete'; // default

    // 🟢 Detectar qué botón abre el modal y actualizar contenido
    document.querySelectorAll('[data-toggle="modal"][data-target="#deleteModal"]').forEach(button => {
        button.addEventListener('click', function() {
            currentActionMode = this.getAttribute('data-mode');

            const modalTitle = document.getElementById('modalTitle');
            const searchInput = document.getElementById('searchInput');

            if (currentActionMode === 'delete') {
                modalTitle.textContent = 'Search and delete Order';
                searchInput.placeholder = 'Search by Work ID, PN, Description, Client...';
            } else if (currentActionMode === 'priority') {
                modalTitle.textContent = 'Search and prioritize Order';
                searchInput.placeholder = 'Search to mark as priority...';
            }

            // Limpiar campo y tabla
            searchInput.value = '';
            document.querySelector('#searchTable tbody').innerHTML = '';
        });
    });

    // 🔍 Buscar órdenes según input
    document.getElementById('searchInput').addEventListener('input', function() {
        const term = this.value.trim();

        if (term.length < 2) {
            document.querySelector('#searchTable tbody').innerHTML = '';
            return;
        }

        fetch(`/orders/search?term=${encodeURIComponent(term)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#searchTable tbody');
                tbody.innerHTML = '';

                if (data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>`;
                    return;
                }

                data.forEach(order => {
                    const row = document.createElement('tr');
                    const dueDate = order.due_date ? new Date(order.due_date) : null;

                    const formattedDueDate = dueDate ?
                        dueDate.toLocaleDateString('en-US', {
                            month: 'short',
                            day: '2-digit',
                            year: '2-digit'
                        }) : '';

                    const orderDueDate = dueDate ? dueDate.toISOString().slice(0, 10) : '';

                    row.innerHTML = `
                    <td>${order.work_id ?? ''}</td>
                    <td>${order.PN ?? ''}</td>
                    <td>${order.Part_description ?? ''}</td>
                    <td>${order.costumer ?? ''}</td>
                    <td data-order="${orderDueDate}">${formattedDueDate}</td>
                `;

                    // 🔁 Acción dinámica según modo
                    if (currentActionMode === 'delete') {
                        row.innerHTML += `
                        <td>
                            <form method="POST" action="/orders/${order.id}/deactivate" class="form-action">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="button" class="btn btn-sm btn-danger btn-action">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    `;
                    } else if (currentActionMode === 'priority') {
                        const isPrioritized = order.priority === 'yes';

                        const formAction = `/orders/${order.id}/toggle-priority`; // Nueva ruta toggle
                        const buttonClass = isPrioritized ? 'btn-secondary' : 'btn-info';
                        const buttonIcon = isPrioritized ?
                            '<i class="fas fa-star text-warning"></i>' // llena
                            :
                            '<i class="far fa-star"></i>'; // vacía

                        const confirmText = isPrioritized ?
                            'This will remove priority from the order.' :
                            'This will mark the order as priority.';

                        row.innerHTML += `
        <td>
            <form method="POST" action="${formAction}" class="form-action">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button type="button" class="btn btn-sm ${buttonClass} btn-action" data-confirm="${confirmText}">
                    ${buttonIcon}
                </button>
            </form>
        </td>
    `;
                    }

                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error al buscar órdenes:', error);
            });
    });

    // ✅ Delegación única de eventos para confirmaciones
    document.getElementById('searchTable').addEventListener('click', function(e) {
        const button = e.target.closest('.btn-action');
        if (!button) return;

        const form = button.closest('form');
        if (!form) return;

        const isDelete = button.classList.contains('btn-danger');
        const confirmText = isDelete ?
            'This will mark the order as deleted.' :
            'This will mark the order as priority.';

        Swal.fire({
            title: 'Are you sure?',
            text: confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, confirm',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: '¡Success!',
            text: "{{ session('success') }}",
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
@endpush