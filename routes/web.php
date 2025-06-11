<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
//------------------------------------------------------------------------
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolePermissionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::resource('users', UserController::class);

//--------------------------------------------------
Route::resource('roles', RolePermissionController::class)->except(['update']);
// Asignar permisos a un rol
Route::post('roles/{role}/permissions', [RolePermissionController::class, 'assignPermissions'])->name('roles.assignPermissions');
// Mostrar roles y permisos
Route::get('roles-permissions', [RolePermissionController::class, 'index'])->name('roles.index');
// Crear un nuevo rol
Route::post('roles', [RolePermissionController::class, 'store'])->name('roles.store');
// Crear un permiso
Route::post('permissions', [RolePermissionController::class, 'createPermission'])->name('permissions.create');
// Eliminar un rol
Route::delete('permissions/{permission}', [RolePermissionController::class, 'destroyPermission'])->name('permissions.destroy');
// Ruta para mostrar el modal con los datos del permiso
Route::get('permissions/{id}/edit-modal', [RolePermissionController::class, 'showEditModal'])->name('permissions.showEditModal');
// Ruta para actualizar el permiso
//Route::put('/permissions/{id}/update', [RolePermissionController::class, 'updatePermission'])->name('permissions.update');
Route::put('/permissions/{id}', [RolePermissionController::class, 'updatePermission'])->name('permissions.update');
Route::match(['put', 'patch'], '/roles/{id}', [RolePermissionController::class, 'updateRoles'])->name('roles.update');
// Devuelve la lista de permisos (GET)
Route::get('/roles/{id}/permissions', [RolePermissionController::class, 'getPermissions']);


//--------------------------------------------------