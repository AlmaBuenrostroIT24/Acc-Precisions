<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
//------------------------------------------------------------------------
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\Order_ScheduleController;
use App\Http\Controllers\QaFaiSummaryController;

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
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

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

//---------------------------------------------------------------------------------------------------------------------------------
Route::resource('schedule/general', Order_ScheduleController::class);
// Ruta para almacenar la nueva orden
Route::post('/orders', [Order_ScheduleController::class, 'store'])->name('orders.store');

//Route::resource('/schedule/general', Order_ScheduleController::class); VISTAS
Route::get('/schedule/general', [Order_ScheduleController::class, 'index'])->name('schedule.general')->middleware('auth');
Route::get('/schedule/endyarnell', [Order_ScheduleController::class, 'endyarnell'])->name('schedule.endyarnell')->middleware('auth');
Route::get('/schedule/finished', [Order_ScheduleController::class, 'finished'])->name('schedule.finished')->middleware('auth');
Route::get('/schedule/statistics', [Order_ScheduleController::class, 'statistics'])->name('schedule.statistics')->middleware('auth');

Route::get('/scheduley', [Order_ScheduleController::class, 'yarnellSchedule'])->name('schedule.yarnell');
Route::get('/scheduleh', [Order_ScheduleController::class, 'hearstSchedule'])->name('schedule.hearst');

Route::post('/schedule-orders', [Order_ScheduleController::class, 'import'])->name('schedule.orders.import');
Route::post('/orders/{order}/update-status', [Order_ScheduleController::class, 'updateStatus']);
Route::post('/orders/{order}/update-report', [Order_ScheduleController::class, 'updateReport']);
Route::post('/orders/{order}/update-source', [Order_ScheduleController::class, 'updateSource']);
Route::post('/orders/{order}/update-location', [Order_ScheduleController::class, 'updateLocation'])->name('orders.updateLocation');
Route::post('/orders/{order}/calculate-days', [Order_ScheduleController::class, 'calcularDias']);
Route::post('/orders/{order}/update-notes', [Order_ScheduleController::class, 'updateNotes']);
Route::post('/orders/{order}/update-work-id', [Order_ScheduleController::class, 'ajaxUpdateWorkId'])->name('orders.ajaxUpdateWorkId');
Route::post('/orders/{order}/update-station', [Order_ScheduleController::class, 'updateStation'])->name('orders.update-station');

Route::post('/orders/{id}/update-wo-qty', [Order_ScheduleController::class, 'updateWoQty']);
Route::post('/orders/duplicate', [Order_ScheduleController::class, 'duplicate'])->name('orders.duplicate');
Route::get('/orders/next-id', function () {
    $lastId = \App\Models\OrderSchedule::max('id') ?? 0;
    return response()->json(['next_id' => $lastId + 1]);
});
Route::get('/orders/summary/year/{year}', [Order_ScheduleController::class, 'summaryByYear']);
Route::get('/orders/summary/month/{year}/{month}', [Order_ScheduleController::class, 'summaryByMonth']);
Route::get('/orders/summary/week/{year}/{week}', [Order_ScheduleController::class, 'summaryByWeek']);
Route::get('/orders/summary/by-customer/year/{year}', [Order_ScheduleController::class, 'summaryByCustomerYear']);
Route::get('/orders/summary/by-customer/month/{year}/{month}', [Order_ScheduleController::class, 'summaryByCustomerMonth']);
Route::get('/orders/summary/by-customer/week/{year}/{week}', [Order_ScheduleController::class, 'summaryByCustomerWeek']);

// -----------------------------------QA FAI-------------------------------------------------------

Route::get('/qa/faisummary', [QaFaiSummaryController::class, 'summary'])->name('faisummary.general')->middleware('auth');
Route::get('/qa/partsrevision', [QaFaiSummaryController::class, 'partsrevision'])->name('faisummary.partsrevision')->middleware('auth');
Route::get('/qa/faicompleted', [QaFaiSummaryController::class, 'faicompleted'])->name('faisummary.completed')->middleware('auth');
Route::get('/qa/faistatistics', [QaFaiSummaryController::class, 'faistatistics'])->name('faisummary.statistics')->middleware('auth');

Route::post('/orders-schedule/{id}/update-operation', [QaFaiSummaryController::class, 'updateOperation'])->name('orders-schedule.updateOperation');
Route::post('/qa/faisummary/store-single', [QaFAiSummaryController::class, 'storeSingle']);
Route::get('/qa/faisummary/by-order/{orderScheduleId}', [QaFaiSummaryController::class, 'getByOrder']);
Route::delete('/qa/faisummary/delete/{id}', [QaFaiSummaryController::class, 'destroy']);
Route::put('/orders-schedule/{order}/status-inspection', [QaFaiSummaryController::class, 'updateStatusInspection'])->name('orders.statusInspection.update');

Route::get('/stations/by-order/{orderScheduleId}', [QaFaiSummaryController::class, 'byOrderStation']);
Route::get('/operators/by-order/{orderScheduleId}', [QaFaiSummaryController::class, 'byOrderOperator']);

Route::get('/sampling-plan', [QaFaiSummaryController::class, 'get']);

// -----------------------------------Machines-------------------------------------------------------