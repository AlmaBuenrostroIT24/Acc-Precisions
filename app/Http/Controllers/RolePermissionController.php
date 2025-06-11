<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    // Mostrar roles y permisos
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all();  // Obtener permisos
        return view('roles_permissions.index', compact('roles', 'permissions'));
    }

    // Crear un nuevo rol
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        $role = Role::create(['name' => $request->name]);

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role
        ]);
    }

    public function getPermissions($roleId)
    {
        $role = Role::findOrFail($roleId);
        $permissions = Permission::orderBy('name', 'asc')->get()->map(function ($perm) use ($role) {
            return [
                'name'     => $perm->name,
                'label'    => ucfirst(str_replace('-', ' ', $perm->name)),
                'assigned' => $role->hasPermissionTo($perm),
            ];
        });

        return response()->json(['permissions' => $permissions]);
    }

    // Asignar permisos a un rol
    public function assignPermissions(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);
        $role->syncPermissions($request->input('permissions', []));

        $permissions = $role->permissions->sortBy('name')->pluck('name');

        return response()->json([
            'message' => 'Permissions assigned correctly',
            'role_id' => $role->id,
            'permissions' => $permissions, // permisos ordenados por nombre
        ]);
    }

    // Crear un permiso
    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Permission created successfully',
            'permission' => $permission,
        ]);
    }


    // Método para mostrar el permiso en el modal
    public function showEditModal($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission); // Enviar el permiso como JSON
    }

    // Método para actualizar el permiso
    public function updatePermission(Request $request, $id)
    {
        // Validar que el nombre sea único (excepto para el permiso actual)
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $id // Validación de nombre único
        ]);

        // Buscar y actualizar el permiso
        $permission = Permission::findOrFail($id);
        $permission->update($request->only('name')); // Solo actualiza el nombre

        return response()->json([
            'success' => true,
            'message' => 'Permit updated successfully',
            'permission' => $permission
        ]);
    }

    // Método para actualizar el rol
    public function updateRoles(Request $request, $id)
    {

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($id)->where(function ($query) {
                    $query->where('guard_name', 'web');
                }),
            ],
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'role' => $role,
        ]);
    }

    // Eliminar un rol
    public function destroy(Role $role)
    {
        // Verificar si tiene permisos
        if ($role->permissions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This role cannot be deleted because it has permissions assigned.'
            ],);
        }

        // Verificar si hay usuarios con este rol
        if (\DB::table('model_has_roles')->where('role_id', $role->id)->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This role is assigned to one or more users. It cannot be deleted.'
            ],);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role successfully deleted'
        ]);
    }

    public function destroyPermission($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully',
        ]);
    }
}
