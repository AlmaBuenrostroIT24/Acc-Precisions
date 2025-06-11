<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    // Constructor para proteger las rutas con autenticación (opcional)
    public function __construct()
    {
        // Asegura que el usuario esté autenticado (si tienes un sistema de autenticación)
        $this->middleware('auth');
    }


    //
    public function index()
    {
        // Obtener todos los usuarios con sus roles
        $users = User::with('roles')->get();  // Usamos 'with' para cargar los roles asociados
        $users = User::all(); // Obtener todos los usuarios
        return view('users.index', compact('users')); // Enviar a la vista

    }

    // Método para mostrar el formulario de creación de usuario
    public function create()
    {
        $roles = Role::all(); // Obtiene todos los roles disponibles
        return view('users.create', compact('roles')); // Pásalos a la vista
    }

    // Método para guardar el nuevo usuario
    public function store(Request $request)
    {
        // Validación de los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // Confirmación de contraseña
            'role' => 'required|exists:roles,name',
        ]);

        // Crear el nuevo usuario y asignarlo a la variable $user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Encriptar la contraseña
        ]);

        // Asignar el rol al usuario utilizando Spatie
        $user->assignRole($validated['role']); // Asignar el rol recibido desde el formulario

        // Redirigir después de crear el usuario
        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente');
    }

    // Mostrar formulario de edición
    public function edit(User $user)
    {
        // Obtener todos los roles disponibles
        $roles = Role::all();
        // Pasar tanto el usuario como los roles a la vista
        return view('users.edit', compact('user', 'roles'));
    }

    // Guardar cambios
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            // la contraseña es opcional
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);
        // Actualizar los datos del usuario
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        // Si se proporciona una nueva contraseña, actualizarla
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        // Guardar los cambios del usuario
        $user->save();
        // Asignar el nuevo rol al usuario
        // Si el usuario ya tiene roles asignados, los roles previos se reemplazarán
        $user->syncRoles([$validated['role']]);  // Asigna el rol que se ha validado
        // Redirigir con un mensaje de éxito
        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
