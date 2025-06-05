<!-- resources/views/users/index.blade.php -->
@extends('adminlte::page')

@section('title', 'Users')

@section('content_header')
<div class="card shadow-sm mb-4 border-0 bg-light">
    <div class="card-body text-center">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-user-shield mr-2"></i> Users
        </h2>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('users.create') }}" class="btn btn-success">New User</a>
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

                    </td>
                    <td>{{ $user->created_at->format('d F Y H:i') }}</td> <!-- Fecha con mes completo -->
                    <td>{{ $user->updated_at->format('d F Y H:i') }}</td> <!-- Fecha con mes completo -->
                    <td>
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
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
    });
</script>
@endpush