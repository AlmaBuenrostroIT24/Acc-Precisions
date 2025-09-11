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

                <div class="card mb-4">
                    @unlessrole('Deburring|QCShipping')
                    <div class="card-body">

                        <div class="row">
                            <!-- Columna izquierda: primer filtro + botón + gráfica -->
                            <div class="col-md-4" style="border-right: 1px solid #ddd; padding-right: 20px;">
                                <!-- Primer bloque de filtros -->
                                <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                                    <i class="fas fa-file-upload text-primary mr-2"></i>
                                    <span class="font-weight-semibold"><strong>Import orders (CSV)</strong></span>
                                </div>

                                <form id="upload-form" action="{{ route('schedule.orders.import') }}" method="POST" enctype="multipart/form-data" class="position-relative">
                                    @csrf

                                    <div class="form-group mb-2">
                                        <label for="csv_file" class="mb-1">Field CSV <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="csv_file" id="csv_file" accept=".csv" required>
                                                <label id="csv_file_label" class="custom-file-label" for="csv_file">Select File</label>
                                            </div>
                                            <div class="input-group-append">
                                                <button id="btn-upload" type="submit" class="btn btn-primary px-4">
                                                    <i class="fas fa-upload mr-1"></i> Upload File
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                    {{-- Overlay de carga --}}
                                    <div id="loading-overlay" class="position-absolute w-100 h-100 d-none" style="top:0;left:0;background:rgba(255,255,255,.8);" aria-live="polite" aria-atomic="true">
                                        <div class="d-flex h-100 w-100 align-items-center justify-content-center text-primary">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Uploading file, please wait…
                                        </div>
                                    </div>
                                </form>

                            </div>
                            <!-- Columna derecha: segundo filtro + botón + gráfica -->
                            <div class="col-md-8" style="padding-left: 20px;">
                                <!-- Segundo bloque de filtros -->
                                <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                                    <i class="fas fa-filter text-info mr-2"></i>
                                    <span class="font-weight-semibold"><strong>Filters</strong></span>
                                </div>
                                {{-- Location --}}
                                <form method="GET" action="{{ route('schedule.general') }}" id="filterForm" class="form-row">
                                    {{-- Location --}}
                                    <div class="form-group col-12 col-md-2">
                                        <label for="locationFilter" class="mb-1">Location</label>
                                        <div class="input-group input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-danger"></i></span>
                                            </div>
                                            <select name="location" id="locationFilter" class="form-control auto-submit">
                                                <option value="">— All —</option>
                                                @foreach($locations as $location)
                                                <option value="{{ strtolower($location) }}" {{ strtolower(request('location')) == strtolower($location) ? 'selected' : '' }}>
                                                    {{ $location ?? 'Sin asignar' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Status --}}
                                    <div class="form-group col-12 col-md-3">
                                        <label for="statusFilter" class="mb-1">Status</label>
                                        <div class="input-group input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-tasks text-primary"></i></span>
                                            </div>
                                            <select name="status" id="statusFilter" class="form-control auto-submit">
                                                <option value="">— All —</option>
                                                @foreach($orders->pluck('status')->unique() as $status)
                                                <option value="{{ strtolower($status) }}" {{ strtolower(request('status')) == strtolower($status) ? 'selected' : '' }}>
                                                    {{ \Illuminate\Support\Str::title(strtolower($status ?? 'Sin estado')) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Customer --}}
                                    <div class="form-group col-12 col-md-3">
                                        <label for="customerFilter" class="mb-1">Customer</label>
                                        <div class="input-group input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light"><i class="fas fa-user-tag text-success"></i></span>
                                            </div>
                                            <select name="customer" id="customerFilter" class="form-control auto-submit">
                                                <option value="">— All —</option>
                                                @foreach($customers as $customer)
                                                <option value="{{ strtolower($customer) }}" {{ strtolower(request('customer')) == strtolower($customer) ? 'selected' : '' }}>
                                                    {{ \Illuminate\Support\Str::title(strtolower($customer)) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Priority & Delete --}}
                                    <div class="form-group col-12 col-md-2">
                                        <label class="mb-1">Priority & Delete</label>
                                        <div class="input-group input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">
                                                    <i class="fas fa-cogs text-secondary"></i>
                                                </span>
                                            </div>
                                            <button class="form-control form-control text-left dropdown-toggle"
                                                type="button"
                                                id="actionMenuButton"
                                                data-toggle="dropdown"
                                                aria-haspopup="true"
                                                aria-expanded="false">
                                                — Action —
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right w-100 p-2" aria-labelledby="actionMenuButton">
                                                <button type="button"
                                                    class="btn btn-info text-white w-100 d-flex align-items-center mb-2"
                                                    data-toggle="modal" data-target="#deleteModal" data-mode="priority">
                                                    <i class="fas fa-star mr-2"></i> Priority
                                                </button>

                                                <button type="button"
                                                    class="btn btn-danger text-white w-100 d-flex align-items-center"
                                                    data-toggle="modal" data-target="#deleteModal" data-mode="delete">
                                                    <i class="fas fa-trash-alt mr-2"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Export (PDF / Excel / Print) - BS4 --}}
                                    <div class="form-group col-12 col-md-2">
                                        <label class="mb-1">Export</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">
                                                    <i class="fas fa-file-export text-warning"></i>
                                                </span>
                                            </div>

                                            {{-- Contenedor dropdown BS4 --}}
                                            <div class="dropdown flex-grow-1">
                                                <button
                                                    class="form-control text-left dropdown-toggle"
                                                    type="button"
                                                    id="exportMenuBtn"
                                                    data-toggle="dropdown" {{-- <- BS4 --}}
                                                    aria-haspopup="true"
                                                    aria-expanded="false">
                                                    — Select —
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-right p-2 w-100" aria-labelledby="exportMenuBtn" style="min-width:100%;">
                                                    <button type="button" class="btn btn-success w-100 mb-2 d-flex align-items-center justify-content-center export-action" data-action="excel">
                                                        <i class="fas fa-file-excel mr-2"></i> Excel
                                                    </button>

                                                    <button type="button" class="btn btn-danger w-100 mb-2 d-flex align-items-center justify-content-center export-action" data-action="pdf">
                                                        <i class="fas fa-file-pdf mr-2"></i> PDF
                                                    </button>

                                                    <button type="button" class="btn btn-secondary w-100 d-flex align-items-center justify-content-center export-action" data-action="print">
                                                        <i class="fas fa-print mr-2"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                </form>
                            </div>

                        </div>

                    </div>
                    @endunlessrole
                </div>
                @php
                use Illuminate\Support\Facades\File;

                $logoRel = 'img/logo.png'; // ← usa .png o .jpg
                $logoAbs = public_path($logoRel);

                $mime = File::exists($logoAbs) ? (File::mimeType($logoAbs) ?? 'image/png') : null;
                $b64 = $mime ? base64_encode(file_get_contents($logoAbs)) : null;
                $dataUrl = ($mime && $b64) ? "data:$mime;base64,$b64" : null;
                @endphp
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



<!-- Bootstrap 4 JS + Popper (para dropdowns BS4 en general) -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script>

     // Queda null si no existe o no se pudo leer
  window.LOGO_BASE64 =   'data:image/png;base64,{{ base64_encode(file_get_contents(public_path("img/acc.png"))) }}';

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
                    tbody.innerHTML =
                        `<tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>`;
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