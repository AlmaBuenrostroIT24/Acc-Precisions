@extends('adminlte::page')
@section('title', 'Edit User')

@section('content')
<div class="container">
    <h1>Editar Usuario</h1>

    <form method="POST" action="{{ route('users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Nombre:</label>
            <input value="{{ old('name', $user->name) }}" name="name" type="text" class="form-control" required>
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="email">Correo:</label>
            <input value="{{ old('email', $user->email) }}" name="email" type="email" class="form-control" required>
            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="password">Nueva Contraseña (opcional):</label>
            <input name="password" type="password" class="form-control">
            @error('password') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar Contraseña:</label>
            <input name="password_confirmation" type="password" class="form-control">
        </div>
      
        <div>
            <label for="role">Rol</label>
            <select name="role" id="role" required>
                <option value="">Seleccionar rol</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" 
                        {{ old('role', $user->roles->isNotEmpty() ? $user->roles->first()->name : '') == $role->name ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
</div>
@endsection