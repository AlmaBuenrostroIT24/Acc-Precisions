<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
//------------------------------------------------------------------------
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\Order_ScheduleController;
use App\Http\Controllers\QaFaiSummaryController;
use App\Http\Controllers\NonConformanceController;
use App\Http\Controllers\Machines\MachCodeController;

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

require __DIR__ . '/auth.php';

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

//-------------------------------------------------------------------------------------------------------------------------------------------------
Route::resource('schedule/general', Order_ScheduleController::class);
// Ruta para almacenar la nueva orden
Route::post('/orders', [Order_ScheduleController::class, 'store'])->name('orders.store');

//Tabs de vistas
Route::get('/schedule/general', [Order_ScheduleController::class, 'index'])->name('schedule.general')->middleware('auth');
Route::get('/schedule/endyarnell', [Order_ScheduleController::class, 'endyarnell'])->name('schedule.endyarnell')->middleware('auth');
Route::get('/schedule/finished', [Order_ScheduleController::class, 'finished'])->name('schedule.finished')->middleware('auth');
Route::get('/schedule/statistics', [Order_ScheduleController::class, 'statistics'])->name('schedule.statistics')->middleware('auth');
Route::get('/schedule/workhearst', [Order_ScheduleController::class, 'workhearst'])->name('schedule.workhearst')->middleware('auth');

// Partials para recarga dinámica de secciones en schedule_workhearst
Route::get('/workhearst/deburring/partial', [Order_ScheduleController::class, 'partialDeburring']);
Route::get('/workhearst/ready/partial', [Order_ScheduleController::class, 'partialReady']);
Route::get('/workhearst/outsource/partial', [Order_ScheduleController::class, 'partialOutsource']);
Route::get('/workhearst/processend/partial', [Order_ScheduleController::class, 'partialProcessend']);
Route::get('/workhearst/workinprocess/partial', [Order_ScheduleController::class, 'partialWorkhearst']);


//Importar archivo en excel
Route::post('/schedule-orders', [Order_ScheduleController::class, 'import'])->name('schedule.orders.import');

Route::post('/orders/{order}/update-status', [Order_ScheduleController::class, 'updateStatus']);
Route::get('/orders/{order}/ops-meta', [Order_ScheduleController::class, 'getOpsMeta'])->name('orders.opsMeta');
Route::post('/orders/{order}/update-report', [Order_ScheduleController::class, 'updateReport']);
Route::post('/orders/{order}/update-source', [Order_ScheduleController::class, 'updateSource']);
Route::post('/orders/{order}/update-location', [Order_ScheduleController::class, 'updateLocation'])->name('orders.updateLocation');
Route::post('/orders/{order}/update-date-machining', [Order_ScheduleController::class, 'updateDateMachining']);
Route::post('/orders/{order}/update-notes', [Order_ScheduleController::class, 'updateNotes']);
Route::post('/orders/{order}/update-work-id', [Order_ScheduleController::class, 'ajaxUpdateWorkId'])->name('orders.ajaxUpdateWorkId');
Route::post('/orders/{order}/update-station', [Order_ScheduleController::class, 'updateStation'])->name('orders.update-station');
Route::post('/orders/{order}/update-date-due', [Order_ScheduleController::class, 'updateDueDate'])->name('orders.update-date-due');
Route::post('/orders/{order}/update-end-date', [Order_ScheduleController::class, 'updateEndDate'])->name('orders.updateEndDate');
Route::get('/schedule/finished/{order}/pdf', [Order_ScheduleController::class, 'finishedOrderPdf'])->name('schedule.finished.pdf');  //Report logs


Route::post('/orders/{order}/return-previous', [Order_ScheduleController::class, 'returnPreviousStatus'])->name('orders.returnPreviousStatus');
Route::post('/orders/{order}/calculate-days', [Order_ScheduleController::class, 'calcularDias']);

//Ventanas de Hearst y Yarnell
Route::get('/scheduley', [Order_ScheduleController::class, 'yarnellSchedule'])->name('schedule.yarnell');
Route::get('/scheduleh', [Order_ScheduleController::class, 'hearstSchedule'])->name('schedule.hearst');
Route::get('/api/schedule-last-update', [Order_ScheduleController::class, 'lastUpdate']); // detecta la ultima actualizacion de una orden para actualizar vistas en PCS


Route::post('/orders/{id}/update-wo-qty', [Order_ScheduleController::class, 'updateWoQty']);
Route::post('/orders/duplicate', [Order_ScheduleController::class, 'duplicate'])->name('orders.duplicate');
Route::get('/orders/next-id', function () {
    $lastId = \App\Models\OrderSchedule::max('id') ?? 0;
    return response()->json(['next_id' => $lastId + 1]);
});
Route::post('/orders/{order}/deactivate', [Order_ScheduleController::class, 'deactivate'])->name('orders.deactivate');
Route::get('/orders/search', [Order_ScheduleController::class, 'search'])->name('orders.search');
Route::post('/orders/{order}/priority', [Order_ScheduleController::class, 'setPriority']);
Route::post('/orders/{order}/toggle-priority', [Order_ScheduleController::class, 'togglePriority'])->name('orders.toggle-priority');
Route::get('/orders/{id}/clone-data', [Order_ScheduleController::class, 'cloneData']);


Route::get('/orders/summary/year/{year}', [Order_ScheduleController::class, 'summaryByYear']);
Route::get('/orders/summary/month/{year}/{month}', [Order_ScheduleController::class, 'summaryByMonth']);
Route::get('/orders/summary/week/{year}/{week}', [Order_ScheduleController::class, 'summaryByWeek']);
Route::get('/orders/summary/by-customer/year/{year}', [Order_ScheduleController::class, 'summaryByCustomerYear']);
Route::get('/orders/summary/by-customer/month/{year}/{month}', [Order_ScheduleController::class, 'summaryByCustomerMonth']);
Route::get('/orders/summary/by-customer/week/{year}/{week}', [Order_ScheduleController::class, 'summaryByCustomerWeek']);
Route::get('/orders/summary/next-weeks/{weeks}', [Order_ScheduleController::class, 'summaryNextWeeks']);
Route::get('/orders/summary/on-time-filtered', [Order_ScheduleController::class, 'summaryOnTimeFiltered']);

Route::get('/orders/by-week/ajax', [Order_ScheduleController::class, 'getOrdersByWeekAjax'])->name('orders.byWeek.ajax');



//++++++++++++++++++++++++++++++++++++++++<-START->QA FAI/IPI +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::middleware('auth')->group(function () {
    Route::get('/qa/partsrevision', [QaFaiSummaryController::class, 'partsrevision'])->name('faisummary.partsrevision');
    Route::get('/qa/partsrevision/data', [QaFaiSummaryController::class, 'partsrevisionData'])->name('faisummary.partsrevision.data');
    Route::get('/qa/faisummary', [QaFaiSummaryController::class, 'summary'])->name('faisummary.general');
    Route::get('/qa/faicompleted', [QaFaiSummaryController::class, 'faicompleted'])->name('faisummary.completed');
    Route::get('/qa/faistatistics', [QaFaiSummaryController::class, 'faistatistics'])->name('faisummary.statistics');
    Route::get('/qa/rejectedfaiorders', [QaFaiSummaryController::class, 'rejectedfaiorders'])->name('faisummary.rejectedfaiorders');
});
//=========================================================================================================
// -----------------------------------faisummary-------------------------------------------------------
//===========================================================================================================
Route::post('/orders-schedule/{id}/update-operation', [QaFaiSummaryController::class, 'updateOperation'])->name('orders-schedule.updateOperation');
Route::post('/qa/faisummary/store-single', [QaFaiSummaryController::class, 'storeSingle']);
Route::get('/qa/faisummary/by-order/{orderScheduleId}', [QaFaiSummaryController::class, 'getByOrder']);
Route::delete('/qa/faisummary/delete/{id}', [QaFaiSummaryController::class, 'destroy']);
Route::put('/orders-schedule/{order}/status-inspection', [QaFaiSummaryController::class, 'updateStatusInspection'])->name('orders.statusInspection.update');

Route::get('/stations/by-order/{orderScheduleId}', [QaFaiSummaryController::class, 'byOrderStation']);
Route::get('/operators/by-order/{orderScheduleId}', [QaFaiSummaryController::class, 'byOrderOperator']);
Route::get('/qa/faisummary/{order}/pdf', [QaFaiSummaryController::class, 'pdf'])->name('qa.faisummary.pdf');
Route::get('/orders-schedule/{order}/validate-ops', [QaFaiSummaryController::class, 'validateOps']);

Route::get('/sampling-plan', [QaFaiSummaryController::class, 'get']);
// -----------------------------------faisummary PDF y EXCEL-------------------------------------------------------

Route::get('/faisummary/general', [QaFaiSummaryController::class, 'general'])->name('faisummary.general');
Route::get('/faisummary/export/excel', [QaFaiSummaryController::class, 'exportFai14'])->name('faisummary.export.excel');
Route::get('/faisummary/export/pdf', [QaFaiSummaryController::class, 'exportPdf'])->name('faisummary.export.pdf');

//=========================================================================================================
// -----------------------------------faicompleted-------------------------------------------------------
//=========================================================================================================
Route::post('/qa/faisummary/completed/export/excel', [QaFaiSummaryController::class, 'exportCompletedExcel'])->name('faisummary.completed.export.excel');

Route::post('/qa/faisummary/completed/export/pdf', [QaFaiSummaryController::class, 'exportCompletedPdf'])->name('faisummary.completed.export.pdf');

//=========================================================================================================
// -----------------------------------Rejected FAI Orders-------------------------------------------------------
//=========================================================================================================
Route::get(
    '/qa/rejectedfaiorders/{order}/inspections',[QaFaiSummaryController::class, 'orderInspections'])->name('faisummary.orderInspections');
//=========================================================================================================
// -----------------------------------faistatiscs-------------------------------------------------------
//=========================================================================================================
Route::get('/qa/faistatistics/data', [QaFaiSummaryController::class, 'faistatisticsData'])->name('faisummary.statistics.data');
    // NUEVO: breakdown por operador/inspector
Route::get('/qa/faistatistics/by', [QaFaiSummaryController::class, 'faistatisticsBy'])->name('faisummary.statistics.by'); // ?year=2025&group=operator|inspector
Route::get('/qa/faistatistics/operators', [QaFaiSummaryController::class, 'operatorsList'])->name('faisummary.operators');
use App\Http\Controllers\FaiSummaryController;
Route::get('/faisummary/statistics/by-quarter-operator', [QaFaiSummaryController::class, 'faistatisticsByQuarterOperator'])->name('faisummary.statistics.byQuarterOperator');

//++++++++++++++++++++++++++++++++++++++++ <-END-> QA FAI/IPI +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//================================
//================================
//================================
//++++++++++++++++++++++++++++++++++++++++<-START->NON-CONFORMANCE +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

Route::middleware('auth')->group(function () {
    // Vista principal
    Route::get('/QA/NonConformace', [NonConformanceController::class, 'ncarparts'])->name('nonconformance.ncarparts');

    // Endpoints para la tabla y los gráficos
    Route::get('/QA/NonConformace/data',  [NonConformanceController::class, 'data'])->name('nonconformance.data');

    Route::get('/QA/NonConformace/stats', [NonConformanceController::class, 'stats'])->name('nonconformance.stats');
});



//++++++++++++++++++++++++++++++++++++++++<-START->MACHINES +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

//Route::resource('machines', MachineMachineryController::class);
Route::resource('/machines/codes', MachCodeController::class);
Route::get('/type-work/next-code', [MachCodeController::class, 'getNextCode'])->name('type-work.next-code');
Route::get('/machine-codes/next-code-by-brand', [MachCodeController::class, 'getNextCodeByBrand']);
Route::get('/machine-codes/brands', [MachCodeController::class, 'getBrandsByType']);
Route::get('/machine-brands', [MachCodeController::class, 'getMachineBrands']);
Route::post('/machines-codes/{id}/toggle-status', [MachCodeController::class, 'toggleStatus'])->name('codes.toggle-status');