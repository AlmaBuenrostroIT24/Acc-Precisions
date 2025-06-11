<!-- resources/views/users/index.blade.php -->
@extends('adminlte::page')

@section('title', 'Users')



@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-user-shield mr-2" aria-hidden="true"></i>
            Users
        </h4>

        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Users</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <!-- Botón para abrir el modal -->
        <button type="button" class="btn btn-secondary mb-3" data-toggle="modal" data-target="#createUserModal">
            New User
        </button>
    </div>
    <div class="card-body">
        <!-- Tabla de Calibraciones -->
        <table id="usersTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Rol</th>
                    <th>Create at</th>
                    <th>Update at</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <!-- Mostrar los roles del usuario -->
                        @foreach($user->roles as $role)
                        <span class="badge badge-info">{{ ucfirst($role->name) }}</span>
                        @endforeach
                    </td>
                    <td>{{ $user->created_at->format('d F Y H:i') }}</td> <!-- Fecha con mes completo -->
                    <td>{{ $user->updated_at->format('d F Y H:i') }}</td> <!-- Fecha con mes completo -->
                    <td>

                        <button class="btn btn-sm btn-warning open-edit-modal"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            data-role="{{ $user->roles->first()?->name }}"
                            data-toggle="modal"
                            data-target="#editUserModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm delete-user-btn" data-id="{{ $user->id }}">
                            <i class="fas fa-trash-alt"></i>
                        </button>

                        <!-- Formulario oculto por cada usuario -->
                        <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>




<!-- Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Nuevo Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Nombre -->
                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Contraseña -->
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        @error('password')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirmar contraseña -->
                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña:</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <!-- Rol -->
                    <div class="form-group">
                        <label for="role">Rol</label>
                        <select name="role" class="form-control" required>
                            <option value="">-- Selecciona un rol --</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                        @error('role') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal de edición de usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" id="editUserForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="user_id" id="editUserId">

                    <div class="form-group">
                        <label for="editName">Nombre</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="editEmail">Correo Electrónico</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="editRole">Rol</label>
                        <select name="role" id="editRole" class="form-control" required>
                            @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('css')
<!-- Agregar los estilos de DataTables -->

@endpush

@push('js')
<!-- Agregar los scripts necesarios para DataTables -->
<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            responsive: true,
            lengthChange: false,
            pageLength: 10,
        });

        $(document).on("click", ".open-edit-modal", function() {
            const userId = $(this).data("id");
            const userName = $(this).data("name");
            const userEmail = $(this).data("email");
            const userRole = $(this).data("role");

            $("#editUserId").val(userId);
            $("#editName").val(userName);
            $("#editEmail").val(userEmail);
            $("#editRole").val(userRole);

            const formAction = `/users/${userId}`;
            $("#editUserForm").attr("action", formAction);
        });

        $(document).on('click', '.delete-user-btn', function() {
            const userId = $(this).data('id');

            Swal.fire({
                title: '¿Eliminar usuario?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-form-${userId}`).submit();
                }
            });
        });
    }); // Cierre correcto de document.ready
</script>

@endpush