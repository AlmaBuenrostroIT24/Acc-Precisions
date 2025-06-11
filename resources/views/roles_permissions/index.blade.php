<!-- resources/views/users/index.blade.php -->
@extends('adminlte::page')

@section('title', 'Roles & Permissions')

@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-users-cog mr-2" aria-hidden="true"></i>
            Management of Roles and Permissions
        </h4>

        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Roles & Permissions</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="row">
    {{-- Columna de Roles --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <strong>Role Management</strong>
            </div>
            <div class="card-body table-responsive">
                <form action="{{ route('roles.store') }}" method="POST" class="form-inline mb-3" id="createRoleForm">
                    @csrf
                    <input type="text" name="name" class="form-control form-control-sm mr-2" placeholder="Role name" required>
                    <button type="submit" class="btn btn-success btn-sm animate-icon">
                        <i class="fas fa-plus mr-1"></i> Add Role
                    </button>
                </form>

                <table class="table table-bordered" id="rolesTable">
                    <thead>
                        <tr>
                            <th class="col-2">Role</th>
                            <th>Permissions</th>
                            <th class="col-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr id="roleRow{{ $role->id }}">
                            <td id="roleName{{ $role->id }}">{{ ucfirst($role->name) }}</td>
                            <td>
                                @foreach($role->permissions as $permission)
                                <span class="badge badge-info">{{ $permission->name }}</span>
                                @endforeach
                            </td>
                            <td style="white-space: nowrap; width: auto;">
                                <button
                                    class="btn btn-primary btn-sm open-assign-permissions-modal"
                                    data-id="{{ $role->id }}"
                                    data-name="{{ $role->name }}">
                                    Assign
                                </button>
                                <button class="btn btn-warning btn-sm open-edit-role-modal"
                                    data-id="{{ $role->id }}"
                                    data-name="{{ $role->name }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <x-delete-buttonRP
                                    :action="route('roles.destroy', $role->id)"
                                    tooltip="Delete role"
                                    class="delete-role-form" />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">There are no roles.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Columna de Permisos --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <strong>Permission Management</strong>
            </div>
            <div class="card-body table-responsive">
                <form action="{{ route('permissions.create') }}" method="POST" class="form-inline mb-3" id="create-permission-form">
                    @csrf
                    <input type="text" class="form-control form-control-sm mr-2" name="name" placeholder="Permission name" required>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Add Permission
                    </button>
                </form>

                <table class="table table-bordered" id="permissionsTable">
                    <thead>
                        <tr>
                            <th>Permission</th>
                            <th class="col-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                        <tr id="permissionRow{{ $permission->id }}">
                            <td id="permissionName{{ $permission->id }}">{{ $permission->name }}</td>
                            <td style="white-space: nowrap; width: auto;">
                                <button class="btn btn-warning btn-sm open-edit-permission-modal"
                                    data-id="{{ $permission->id }}"
                                    data-name="{{ $permission->name }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <x-delete-buttonRP
                                    :action="route('permissions.destroy', $permission->id)"
                                    tooltip="Delete permission"
                                    class="delete-permission-form" />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2">There are no permissions.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>





@endsection

@push('css')
<style>
    .dataTables_filter input {
        width: 8ch;
        /* ancho según caracteres visibles */
        min-width: 80px;
        max-width: 100%;
        transition: width 0.3s ease;
        box-sizing: border-box;
    }

    .dataTables_filter input:focus {
        width: 20ch;
    }
</style>
@endpush

@push('js')
<!-- Agregar los scripts necesarios -->

<script src="{{ asset('vendor/js/roles&permissions.js') }}"></script>
@endpush