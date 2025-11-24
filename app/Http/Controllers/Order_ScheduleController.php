<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\OrderSchedule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OrderScheduleImport;
use App\Services\OrderScheduleImportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\OrdMachiningDateLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Maatwebsite\Excel\HeadingRowImport;

class Order_ScheduleController extends Controller
{

    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 1. TAB "General Schedule" CONSULTAS
     * ===================================================================================================================
     */

    public function index(Request $request)
    {
        $base = OrderSchedule::query()
            ->where('status', '!=', 'sent')
            ->where(function ($q) {
                $q->whereNull('status_order')->orWhere('status_order', 'active');
            });

        // 🔒 Filtro especial: usuarios con rol Deburring solo ven status=deburring
        if (auth()->check() && auth()->user()->hasRole('Deburring')) {
            $base->where('status', 'deburring');
        }

        // 🔒 Orden especial para QCShipping: primero Shipping, luego Ready, luego el resto
        if (auth()->check() && auth()->user()->hasRole('QCShipping')) {
            $base->orderByRaw("
        CASE 
            WHEN status = 'shipping' THEN 0
            WHEN status = 'ready'    THEN 1
            ELSE 2
        END
    ");
        }

        /// 👇 Filtros por request (se aplican solo si no es Deburring)
        $base->when($request->filled('location'), function ($q) use ($request) {
            $q->where('location', $request->location);
        });

        $base->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        // 👇 Regla especial para /schedule/general + QAdmin (solo si NO se pasó location explícita)
        if (
            !$request->filled('location')
            && $request->is('schedule/general')
            && auth()->check()
            && auth()->user()->hasRole('QAdmin')
        ) {
            $base->whereIn('location', ['yarnell', 'floor']);
        }

        // 👇 Prioridad primero, luego due_date (NULLs al final)
        $base->orderByRaw("CASE WHEN COALESCE(priority,'no') = 'yes' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('due_date', 'asc'); // ← si realmente quieres descendente, cambia a 'desc'

        $orders = $base->get();

        // Estos catálogos se obtienen aparte (no afectan la consulta principal)
        $locations = OrderSchedule::select('location')->distinct()->pluck('location');
        $statuses  = OrderSchedule::select('status')->distinct()->pluck('status');
        $customers = OrderSchedule::select('costumer')->distinct()->pluck('costumer');

        foreach ($orders as $order) {
            $order->dias_restantes = $this->calcularDiasInterno(
                $order->status,
                $order->due_date,
                $order->machining_date
            );
        }

        return view('orders.index_schedule', compact('orders', 'locations', 'statuses', 'customers'));
    }

    /** ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🟢 SAVE: Agregar nuevas ordenes, desde el kit.  
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    public function store(Request $request)
    {
        $mapping = [
            'col_text_0'  => 'location',
            'col_text_1'  => 'work_id',
            'col_text_2'  => 'PN',
            'col_text_3'  => 'Part_description',
            'col_text_4'  => 'costumer',
            'col_text_5'  => 'qty',
            'col_text_6'  => 'wo_qty',
            'col_text_7'  => 'status',
            'col_text_8'  => 'machining_date',
            'col_text_9'  => 'due_date',
            'col_text_10' => 'days',
            'col_text_11' => 'alert',
            'col_text_12' => 'report',
            'col_text_13' => 'our_source',
            'col_text_14' => 'station',
            'col_text_15' => 'notes',
        ];

        // Helper: normalizar enteros (acepta "1,200", "1 200")
        $normalizeInt = function ($v) {
            if ($v === null) return null;
            if (is_int($v)) return $v;
            $n = preg_replace('/[^\d\-]/', '', (string)$v);
            return ($n === '' || $n === '-') ? null : (int)$n;
        };

        // Helper: parsear fechas de forma segura
        $parseDateSafe = function ($v) {
            if (empty($v)) return null;
            $cands = ['Y-m-d', 'm/d/Y', 'd/m/Y', 'Y-m-d H:i:s', 'm/d/Y H:i', 'd/m/Y H:i'];
            foreach ($cands as $fmt) {
                try {
                    $dt = \Carbon\Carbon::createFromFormat($fmt, trim((string)$v));
                    if ($dt !== false) return $dt->format('Y-m-d');
                } catch (\Throwable $e) {
                }
            }
            try {
                return \Carbon\Carbon::parse($v)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        };

        try {
            // 1) Mapear SIEMPRE todas las columnas (aunque vengan vacías)
            $data = [];
            foreach ($mapping as $colKey => $field) {
                $value = $request->input($colKey, null);
                $data[$field] = is_string($value)
                    ? trim(preg_replace('/\s+/', ' ', $value))
                    : $value;
            }

            // 2) Copiar co / cust_po desde la orden original (si viene original_id)
            if ($request->filled('original_id')) {
                $orig = \App\Models\OrderSchedule::select('co', 'cust_po')->find($request->input('original_id'));
                if ($orig) {
                    $data['co']      = $orig->co;
                    $data['cust_po'] = $orig->cust_po;
                }
            }
            // 3) Normalizaciones previas
            if (!empty($data['status'])) $data['status'] = strtolower($data['status']);

            // days: si viene con texto, extraer dígitos
            if (!empty($data['days'])) {
                preg_match('/\d+/', (string)$data['days'], $m);
                $data['days'] = isset($m[0]) ? (int)$m[0] : null;
            }

            // fechas
            $data['machining_date'] = !empty($data['machining_date']) ? $parseDateSafe($data['machining_date']) : null;
            $data['due_date']       = !empty($data['due_date'])       ? $parseDateSafe($data['due_date'])       : null;

            // enteros
            $data['qty']    = array_key_exists('qty', $data)    ? $normalizeInt($data['qty'])    : null;
            $data['wo_qty'] = array_key_exists('wo_qty', $data) ? $normalizeInt($data['wo_qty']) : null;

            // Defaults
            $data['alert']             = $data['alert'] ?? '';
            $data['priority']          = $data['priority'] ?? 'no';
            $data['status_order']      = $data['status_order'] ?? 'active';
            $data['operation']         = $data['operation'] ?? '0';
            $data['total_fai']         = $normalizeInt($data['total_fai'] ?? 0);
            $data['total_ipi']         = $normalizeInt($data['total_ipi'] ?? 0);
            $data['sampling']          = $normalizeInt($data['sampling']   ?? 0);
            $data['status_inspection'] = $data['status_inspection'] ?? 'pending';

            // 4) group_key con datos de la NUEVA orden
            //    CONCAT(PN, '#', COALESCE(NULLIF(work_id,''), 'NO-WO'))
            $pn = trim((string)($data['PN'] ?? ''));
            $wo = trim((string)($data['work_id'] ?? ''));
            $data['group_key'] = $pn . '#' . ($wo !== '' ? $wo : 'NO-WO');

            // 5) Validación
            $validated = validator($data, [
                'work_id'          => 'nullable|string|max:255',
                'PN'               => 'nullable|string|max:255',
                'Part_description' => 'nullable|string|max:255',
                'qty'              => 'nullable|integer|min:0',
                'costumer'         => 'required|string|max:255',
                'wo_qty'           => 'nullable|integer|min:0',
                'status'           => 'nullable|string|max:255',
                'machining_date'   => 'nullable|date',
                'due_date'         => 'nullable|date',
                'days'             => 'nullable|integer',
                'alert'            => 'nullable|string',
                'report'           => 'nullable|string',
                'our_source'       => 'nullable|string|max:255',
                'station'          => 'nullable|string|max:255',
                'notes'            => 'nullable|string',
                'location'         => 'nullable|string|max:255',
                'priority'         => 'nullable|string|max:10',
                'status_order'     => 'nullable|string|max:10',
                'operation'        => 'nullable|string|max:255',
                'total_fai'        => 'nullable|integer|min:0',
                'total_ipi'        => 'nullable|integer|min:0',
                'sampling'         => 'nullable|integer|min:0',
                'status_inspection' => 'nullable|string|max:50',
                'co'               => 'nullable|string|max:255',
                'cust_po'          => 'nullable|string|max:255',
                'group_key'        => 'nullable|string|max:255',
                // group_wo_qty lo agregamos tras validar
            ])->validate();

            // 6) Si no vino days y hay due_date → calcular (hoy → due_date)
            if (!isset($validated['days']) && !empty($validated['due_date'])) {
                try {
                    $today = \Carbon\Carbon::today();
                    $due   = \Carbon\Carbon::createFromFormat('Y-m-d', $validated['due_date']);
                    $validated['days'] = $today->diffInDays($due, false); // negativo si vencido
                } catch (\Throwable $e) {
                    $validated['days'] = null;
                }
            }

            // 7) AHORA sí: group_wo_qty desde el wo_qty ya validado/normalizado
            $validated['group_wo_qty'] = $validated['wo_qty'] ?? 0;

            // (opcional) Log para depurar rápidamente
            // \Log::info('STORE check', [
            //     'req_col_text_6'   => $request->input('col_text_6'),
            //     'validated_wo_qty' => $validated['wo_qty'] ?? null,
            //     'group_wo_qty'     => $validated['group_wo_qty'] ?? null,
            // ]);

            // 8) Crear registro
            $order = \App\Models\OrderSchedule::create($validated);

            return response()->json([
                'success'  => true,
                'id'       => $order->id,   // útil para tu JS
                'order_id' => $order->id,   // compatibilidad si usas order_id
                'message'  => 'Orden creada correctamente',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning('Validación fallida en store: ' . $ve->getMessage());
            return response()->json([
                'success' => false,
                'errors'  => $ve->errors(),
                'message' => 'Error de validación en los datos.',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error inesperado en store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Contacta al administrador.',
            ], 500);
        }
    }

    /** ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🔵IMPORT: Importar el archivo excel de ordenes en el tab Genera;l Shedule
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    protected $service;
    // Inyectar el servicio en el constructor
    public function __construct(OrderScheduleImportService $service)
    {
        $this->service = $service;
    }

    public function import(Request $request, OrderScheduleImportService $svc)
    {
        // 0) Validación (sube el max si necesitas más de 20MB)
        $request->validate([
            'csv_file' => 'required|file|max:20480|mimes:csv,txt,xlsx,xls',
        ]);
        if (!$request->hasFile('csv_file')) {
            return back()->withErrors(['csv_file' => 'No llegó ningún archivo (hasFile=false).']);
        }
        $file = $request->file('csv_file');
        if (!$file->isValid()) {
            Log::error('Upload inválido', ['error_code' => $file->getError()]);
            return back()->withErrors(['csv_file' => 'El archivo subido no es válido. Código: ' . $file->getError()]);
        }
        // 1) Guardar copia estable
        $storedPath = $file->storeAs(
            'tmp_imports',
            now()->format('Ymd_His') . '__' . $file->getClientOriginalName()
        );
        $absPath = storage_path('app/' . $storedPath);

        // 2) (Opcional) inspeccionar encabezados
        try {
            $heads = (new HeadingRowImport)->toArray($absPath);
            // Log::info('Encabezados', ['headings' => $heads[0][0] ?? []]);
        } catch (\Throwable $e) {
            Log::warning('No se pudieron leer encabezados', ['msg' => $e->getMessage()]);
        }

        try {
            // 3) Ejecutar import (inyecta el service CORRECTO)
            $importer = new OrderScheduleImport($svc);
            Excel::import($importer, $absPath);

            // 4) Re-etiquetar padres/hijos (¡después del import!)
            $svc->relabelParents();

            $count = $svc->importedCount ?? 0;
            if ($count === 0) {
                Log::warning('Import terminó con 0 filas. Revisa el parseo de due_date / claves / duplicados.');
            }
            return redirect()
                ->route('schedule.general')
                ->with('success', "{$count} records were imported successfully.");
        } catch (ExcelValidationException $e) {
            // Errores por fila (WithValidation)
            $msgs = collect($e->failures())
                ->map(fn($f) => "Row {$f->row()}: " . implode(', ', $f->errors()))
                ->take(20)
                ->implode(' | ');

            Log::warning('Fallos de validación por fila', ['count' => count($e->failures())]);
            return back()->withErrors(['import' => 'Errores de validación: ' . $msgs]);
        } catch (\Throwable $e) {
            Log::error('Excepción durante import', ['msg' => $e->getMessage()]);
            return back()->with('error', 'No se pudo importar: ' . $e->getMessage());
        }
    }

    /** +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🔵UPDATE: Actualizar en el tab General Shedule dentro de la tabla el campo "WOQTY" 
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    public function updateWoQty(Request $request, $id)
    {
        $data = $request->validate([
            'wo_qty' => 'required|integer|min:0',
        ]);

        return DB::transaction(function () use ($id, $data) {
            // 1) Guardar el cambio en la fila editada
            /** @var \App\Models\OrderSchedule $order */
            $order = OrderSchedule::lockForUpdate()->findOrFail($id);
            $order->wo_qty = (int) $data['wo_qty'];
            $order->save();

            // 2) Determinar el padre del grupo (si es hijo, su padre; si es padre, él mismo)
            $parentId = $order->parent_id ?: $order->id;

            // 3) Recalcular total del grupo SOLO con filas cuyo work_id NO esté vacío y was_work_id_null = 1
            //    (si quieres excluir 'sent', añade ->where('status', '<>', 'sent'))
            $groupTotal = OrderSchedule::query()
                ->where(function ($q) use ($parentId) {
                    $q->where('id', $parentId)
                        ->orWhere('parent_id', $parentId);
                })
                ->whereRaw("NULLIF(TRIM(work_id), '') IS NOT NULL") // work_id no vacío ni null (ignora espacios)
                ->where('was_work_id_null', 0)
                ->sum(DB::raw('COALESCE(wo_qty, 0)'));

            // 4) Guardar el total en el padre
            OrderSchedule::whereKey($parentId)->update([
                'group_wo_qty' => (int) $groupTotal,
            ]);

            // 5) Responder al front con info útil para actualizar UI
            return response()->json([
                'success'      => true,
                'order_id'     => $order->id,
                'parent_id'    => $parentId,
                'wo_qty_saved' => (int) $order->wo_qty,
                'group_wo_qty' => (int) $groupTotal,
            ]);
        });
    }

    /** +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🔵UPDATE: Actualizar en el tab General Shedule dentro de la tabla el campo "STATUS"
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function updateStatus(Request $request, OrderSchedule $order)
    {
        try {

            // Log::info("Cambio de status orden {$order->id} a: " . $request->status);

            $request->validate([
                'status' => 'required|string|max:50',
                'target_date' => 'nullable|date',
                'status_inspection' => 'nullable|in:pending,in_progress,completed',
                'inspection_note'   => 'nullable|string|max:500', // 👈 nuevo
            ]);

            $newStatus = strtolower($request->status);
            // Captura el estado anterior antes de sobrescribirlo
            $previousStatus = $order->status;

            // 1) Captura el estado de inspección previo ANTES de modificarlo
            $prevInspection = $order->status_inspection;

            $order->status = $newStatus;

            // guardar nota si vino
            if ($request->filled('inspection_note')) {
                $order->inspection_note = $request->inspection_note;
            }

            // 2) Si viene status_inspection, asigna y setea inspection_endate si pasa a completed
            if ($request->filled('status_inspection')) {
                $newInspection = strtolower($request->status_inspection);
                $order->status_inspection = $newInspection;

                // Solo si ANTES no estaba completed y AHORA sí
                if ($newInspection === 'completed' && $prevInspection !== 'completed') {
                    if (empty($order->inspection_endate)) {
                        $order->inspection_endate = now();
                    }
                    if (empty($order->completed_by)) {
                        $order->completed_by = Auth::id();
                    }
                }

                // (Opcional) Si quieres limpiar al revertir desde completed:
                if (in_array($newInspection, ['pending', 'in_progress']) && $prevInspection === 'completed') {
                    $order->inspection_endate = null;
                    $order->completed_by = null;
                }
            }

            // ✅ Guardar previous_status si se cambia a "sent"
            if ($newStatus === 'sent') {
                $order->previous_status = $previousStatus;
                $order->sent_at = now(); // También mantén esto aquí
            }

            // ✅ Si el status es "deburring" o "shipping", actualizar la location a "hearst"
            if (
                in_array($newStatus, ['deburring', 'shipping']) &&
                ($order->location) === 'Yarnell'
            ) {
                $order->last_location = $order->location; // Guardar ubicación anterior
                $order->location = 'Hearst';
                $order->endate_mach = now(); // Guardar fecha y hora del cambio en endate_mach
            }

            // Guardar la fecha cuando cambia a "sent"
            if ($newStatus === 'sent') {
                $order->sent_at = now();
                // Log::info("Se asignó sent_at para orden {$order->id}: {$order->sent_at}");
            }

            // Solo calcular target_date si tenemos ambas fechas
            if ($order->due_date && $order->sent_at) {
                $dueDate = \Carbon\Carbon::parse($order->due_date)->startOfDay();
                $sentDate = \Carbon\Carbon::parse($order->sent_at)->startOfDay();

                // Diferencia en días (positivo o negativo)
                $diff = $dueDate->diffInDays($sentDate, false); // diferencia con signo

                // Si la fecha enviada es mayor a due_date, invertimos el signo para que sea negativo
                $diffDays = $diff > 0 ? -$diff : abs($diff);

                $order->target_date = $diffDays;
            }
            // target_mach
            if ($order->endate_mach && $order->machining_date) {
                $endateMach = \Carbon\Carbon::parse($order->endate_mach)->startOfDay();
                $machiningDate = \Carbon\Carbon::parse($order->machining_date)->startOfDay();

                // Invertimos el orden para restar machining_date - endate_mach
                $diffMach = $machiningDate->diffInDays($endateMach, false);

                $diffMachDays = $diffMach > 0 ? -$diffMach : abs($diffMach);

                $order->target_mach = $diffMachDays;
            }

            $order->save();

            // Calcular días restantes
            $dias = $this->calcularDiasInterno($order->status, $order->due_date, $order->machining_date);

            // Determinar color para "alerta"
            $alert = $dias < 0 || $dias <= 2;
            $alertColor = $dias < 0 ? 'bg-danger' : ($dias <= 2 ? 'bg-warning' : 'bg-success');
            $alertLabel = $dias < 0 ? 'Late' : ($dias <= 2 ? 'Expedite' : 'On time');
            //Log::info("Respuesta para orden {$order->id}: dias_restantes={$dias}, alert={$alertLabel}");
            return response()->json([
                'success' => true,
                'dias_restantes' => $dias,
                'alertColor' => $alertColor,
                'alertLabel' => $alertLabel,
                'status' => strtolower($order->status), // ✅ aseguramos el status en minúsculas
                'location' => $order->location, // 👈 ¡Agrega esto!
                'last_location' => $order->last_location, // <== Aquí
            ]);
        } catch (\Exception $e) {
            // Log::error("Error actualizando status orden {$order->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getOpsMeta(\App\Models\OrderSchedule $order)
    {
        return response()->json([
            'operation' => $order->operation ?? 0,
            'parent_id' => $order->parent_id, // null si no tiene padre
            'status_inspection'  => strtolower((string) $order->status_inspection), // null|pending|in_progress|completed
            'inspection_progress'  => (int) ($order->inspection_progress ?? 0),
        ]);
    }

    /** +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🔵UPDATE: Actualizar en el tab General Shedule dentro de la tabla el campo "LOCATION"
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    public function updateLocation(Request $request, OrderSchedule $order)
    {
        $request->validate([
            'location' => 'required|in:Floor,Yarnell,Hearst',
        ]);

        $order->last_location = $order->location; // Guardamos la ubicación actual
        $order->location = $request->location;
        $order->save();

        return response()->json([
            'success' => true,
            'location' => $order->location,
            'last_location' => $order->last_location,
            // otros datos que necesites devolver
        ]);
    }

    /** +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🔵UPDATE: Actualizar en el tab General Shedule dentro de la tabla el campo "REPORT"
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function updateReport(Request $request, OrderSchedule $order)
    {
        $order->report = $request->input('report');
        $order->save();

        return response()->json(['success' => true]);
    }

    /** +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🔵UPDATE: Actualizar en el tab General Shedule dentro de la tabla el campo "OUTSOURCE"
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    public function updateSource(Request $request, OrderSchedule $order)
    {
        $order->our_source = $request->our_source;
        $order->save();

        return response()->json(['success' => true]);
    }

    /** +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🔵UPDATE: Actualizar en el tab General Shedule dentro de la tabla el campo "NOTES"
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    public function updateNotes(Request $request, $orderId)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000', // Ajusta la validación según necesites
        ]);

        $order = OrderSchedule::find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Orden no encontrada'], 404);
        }

        $order->notes = $request->notes;
        $order->save();

        return response()->json(['success' => true, 'notes' => $request->notes]);
    }

    /** +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🔵UPDATE: Actualizar en el tab General Shedule dentro de la tabla el campo "WORK ID"
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    public function ajaxUpdateWorkId(Request $request, OrderSchedule $order)
    {
        $data = $request->validate([
            'work_id' => 'nullable|string|max:191',
        ]);

        return DB::transaction(function () use ($order, $data) {

            /** @var \App\Models\OrderSchedule $o */
            $o = OrderSchedule::lockForUpdate()->findOrFail($order->id);

            // 1) Actualizar work_id (sin tocar was_work_id_null)
            // Normaliza: "" -> NULL (con trim)
            $wi = isset($data['work_id']) ? trim($data['work_id']) : null;
            $o->work_id = ($wi === '') ? null : $wi;

            // 2) Recalcular group_key (PN#work_id | PN#NO-WO)
            $o->group_key = $o->PN . '#' . ($o->work_id ? $o->work_id : 'NO-WO');

            $o->save();

            // 3) Identificar padre del grupo
            $parentId = $o->parent_id ?: $o->id;

            // 4) Recalcular total del grupo:
            //    - SIEMPRE suma el padre
            //    - + hijos con work_id no vacío y was_work_id_null = 1
            //    (si quieres excluir 'sent', añade ->where('status','<>','sent') donde se indica)
            $parentQty = (int) OrderSchedule::whereKey($parentId)
                //->where('status','<>','sent')
                ->value(DB::raw('COALESCE(wo_qty,0)'));

            $childrenSum = (int) OrderSchedule::where('parent_id', $parentId)
                //->where('status','<>','sent')
                ->whereRaw("NULLIF(TRIM(work_id),'') IS NOT NULL")
                ->where('was_work_id_null', 1)
                ->sum(DB::raw('COALESCE(wo_qty,0)'));

            $groupTotal = $parentQty + $childrenSum;

            OrderSchedule::whereKey($parentId)->update([
                'group_wo_qty' => (int) $groupTotal,
            ]);

            // 5) Responder con todo lo necesario para refrescar la UI
            return response()->json([
                'success'       => true,
                'order_id'      => $o->id,
                'parent_id'     => $parentId,
                'work_id'       => $o->work_id,
                'group_key'     => $o->group_key,
                'group_wo_qty'  => (int) $groupTotal,
            ]);
        });
    }

    /** +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🔵UPDATE: Actualizar en el tab General Shedule dentro de la tabla el campo "Status"
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function updateStation(Request $request, OrderSchedule $order)
    {
        $stations = $request->input('stations');

        // ⚠️ Aquí aseguramos que si viene vacío, guardamos null
        $order->station = empty($stations) ? null : implode(',', $stations);
        $order->save();

        return response()->json([
            'success' => true,
            'station' => $order->station, // será null o el string "X,Y,Z"
        ]);
    }

    /** +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🔵UPDATE: Actualizar fecha de envio de orden
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function updateEndDate(Request $request, OrderSchedule $order)
    {
        $validated = $request->validate([
            'sent_at' => ['nullable', 'date'],
        ]);

        // Sent_at original
        $originalSentAt = $order->sent_at
            ? ($order->sent_at instanceof Carbon ? $order->sent_at : Carbon::parse($order->sent_at))
            : null;

        // Nueva fecha
        $newSentAt = $validated['sent_at'] ?? null;
        $order->sent_at = $newSentAt;

        // Recalcular target_date
        if ($order->due_date && $order->sent_at) {
            $due  = $order->due_date instanceof Carbon ? $order->due_date : Carbon::parse($order->due_date);
            $sent = $order->sent_at instanceof Carbon ? $order->sent_at : Carbon::parse($order->sent_at);

            $order->target_date = $due->diffInDays($sent, false); // con signo
        } else {
            $order->target_date = null;
        }

        // Marcar was_endsentat_modified si cambió
        $originalStr = $originalSentAt ? $originalSentAt->format('Y-m-d H:i:s') : null;
        $newStr      = $newSentAt ? Carbon::parse($newSentAt)->format('Y-m-d H:i:s') : null;

        if ($originalStr !== $newStr) {
            $order->was_endsentat_modified = 1;
        }

        $order->save();

        $sentAt = $order->sent_at;

        return response()->json([
            'success'              => true,
            'sent_at_value'        => $sentAt ? $sentAt->format('Y-m-d H:i') : null,
            'sent_at_formatted'    => $sentAt ? $sentAt->format('M-d-y H:i') : null,
            'sent_at_order'        => $sentAt ? $sentAt->format('Y-m-d H:i:s') : null,
            'target_date'          => $order->target_date,
            'was_modified'         => (bool)$order->was_endsentat_modified,
        ]);
    }


    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 2. TAB "Schedule Hearst" CONSULTAS
     * ===================================================================================================================
     */

    public function workhearst(Request $request)
    {
        $orders = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->whereIn('status', [
                'pending',
                'waitingformaterial',
                'cutmaterial',
                'grinding',
                'onrack',
                'programming',
                'setup',
                'machining',
                'marking'
            ])
            ->get();

        $ordersReady = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->where('status', 'ready')
            ->get();

        $ordersDeburring = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->where('status', 'deburring')
            ->get();

        $ordersReady = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->where('status', 'ready')
            ->get();

        $ordersOutsource = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->where('status', 'outsource')
            ->get();

        $ordersProcessend = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->whereIn('status', [
                'assembly',
                'shipping',
                'onhold'
            ])
            ->get();
        // Define la ubicación para la sincronización
        $location = 'workhearst';

        return view('orders.schedule_workhearst', compact('orders', 'ordersReady', 'ordersDeburring', 'ordersOutsource', 'ordersProcessend', 'location'));
    }

    public function partialDeburring()
    {
        $ordersDeburring = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->where('status', 'deburring')
            ->get();
        return view('orders.partials.deburring_table_body', compact('ordersDeburring'));
    }

    public function partialReady()
    {
        $ordersReady = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->where('status', 'ready')
            ->get();
        return view('orders.partials.ready_table_body', compact('ordersReady'));
    }

    public function partialOutsource()
    {
        $ordersOutsource = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->where('status', 'outsource')
            ->get();
        return view('orders.partials.outsource_table_body', compact('ordersOutsource'));
    }

    public function partialProcessend()
    {
        $ordersProcessend = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->whereIn('status', ['assembly', 'shipping', 'onhold'])
            ->get();
        return view('orders.partials.processend_table_body', compact('ordersProcessend'));
    }

    public function partialWorkhearst()
    {
        $orders = OrderSchedule::latest()
            ->where('location', 'Hearst')
            ->whereIn('status', [
                'pending',
                'waitingformaterial',
                'cutmaterial',
                'grinding',
                'onrack',
                'programming',
                'setup',
                'machining',
                'marking',
            ])
            ->get();
        return view('orders.partials.workhearst_table_body', compact('orders'));
    }


    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 3. TAB "Orders Yarnell" CONSULTAS
     * ===================================================================================================================
     */

    public function endyarnell(Request $request)
    {
        // Parámetros de filtro
        $year     = $request->integer('year');
        $month    = $request->integer('month');
        $day      = $request->input('day');

        $query = OrderSchedule::latest()
            ->where('last_location', 'Yarnell')
            ->where('location', 'Hearst'); // solo órdenes que están en Hearst y vienen de Yarnell

        // 📅 Filtro de fechas (prioridad como en summary)
        // Cambia 'due_date' si tu campo de fecha principal es otro (p. ej., 'sent_at')
        if ($day) {
            $query->whereDate('endate_mach', Carbon::parse($day)->toDateString());
        } elseif ($year && $month) {
            $query->whereYear('endate_mach', $year)->whereMonth('endate_mach', $month);
        } elseif ($year) {
            $query->whereYear('endate_mach', $year);
        } elseif ($month) {
            $query->whereYear('endate_mach', now()->year)->whereMonth('endate_mach', $month);
        } else {
            // Por defecto: mes actual para no traer dataset enorme
            $query->whereBetween('endate_mach', [now()->startOfMonth(), now()->endOfMonth()]);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $orders = $query->get();

        // 👇 obtenemos los valores únicos
        $statuses = OrderSchedule::select('status')->distinct()->pluck('status');
        $customers = OrderSchedule::select('costumer')->distinct()->pluck('costumer');

        $currentYear = now()->year;
        $years  = range($currentYear, $currentYear - 5);
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        ];

        foreach ($orders as $order) {
            $order->dias_restantes = $this->calcularDiasInterno(
                $order->status,
                $order->due_date,
                $order->machining_date
            );
        }
        return view('orders.schedule_endyarnell', compact('orders', 'statuses', 'customers', 'years', 'months', 'year', 'month', 'day'));
    }

    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 4. TAB "Completed Orders" CONSULTAS
     * ===================================================================================================================
     */

    public function finished(Request $request)
    {
        // ⬇️ Cambia esto a 'sent_at' si tu "Finished" se basa en esa fecha
        $dateField = 'sent_at';

        $query = OrderSchedule::query();

        // Por defecto, mostrar solo terminados (ajusta si tu flujo usa otro status)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'sent'); // o 'finished' si así lo guardas
        }

        // Location (server-side)
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // === Filtros Year / Month / Day sobre $dateField ===
        // Prioridad: DAY > (YEAR+MONTH) > YEAR
        $hasDay   = $request->filled('day');
        $hasYear  = $request->filled('year') && preg_match('/^\d{4}$/', (string) $request->year);
        $hasMonth = $request->filled('month') && preg_match('/^\d{1,2}$/', (string) $request->month);

        if ($hasDay) {
            // Día exacto
            try {
                $day = Carbon::parse($request->day)->startOfDay();
                $query->whereDate($dateField, $day->toDateString());
            } catch (\Throwable $e) {
                // si el día no es válido, ignorar filtro
            }
        } else {
            if ($hasYear) {
                $query->whereYear($dateField, (int) $request->year);
            }
            if ($hasMonth) {
                $query->whereMonth($dateField, (int) $request->month);
            }
        }

        // Orden principal por fecha de finalización (coincide con tu DataTable col 11)
        $query->orderByDesc($dateField);

        $orders = $query->get();

        // Catálogos para filtros (sin afectar resultado)
        $locations = OrderSchedule::select('location')->distinct()->pluck('location');
        $statuses  = OrderSchedule::select('status')->distinct()->pluck('status');
        $customers = OrderSchedule::select('costumer')->distinct()->pluck('costumer');

        // Si aún necesitas dias_restantes (aunque sea "finished")
        foreach ($orders as $order) {
            $order->dias_restantes = $this->calcularDiasInterno(
                $order->status,
                $order->due_date,
                $order->machining_date
            );
        }
        return view('orders.schedule_finished', compact('orders', 'locations', 'statuses', 'customers'));
    }

    /** +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🟣 RETURN: Regresar ornedes al tab General Schedule con el ultimo status 
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    public function returnPreviousStatus(Request $request, OrderSchedule $order)
    {
        try {
            if (!$order->previous_status) {
                return response()->json(['success' => false, 'message' => 'No hay estado anterior registrado.'], 400);
            }
            $order->status = $order->previous_status;
            $order->previous_status = null;
            $order->sent_at = null; // limpiar si lo deseas
            $order->target_date = null; // limpiar si lo deseas
            $order->save();
            return response()->json([
                'success' => true,
                'newStatus' => $order->status,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    //----------------------------------------------------------------------------------------------------------------------------
    //----------------------------------------------------------------------------------------------------------------------------



    public function create()
    {
        return view('orders.create');
    }

    public function edit(OrderSchedule $order)
    {
        return view('orders.edit', compact('order'));
    }

    public function update(Request $request, OrderSchedule $order)
    {
        $validated = $request->validate([
            'work_id' => 'required|string|max:255',
            'PN' => 'required|string|max:255',
            'Part_description' => 'required|string|max:255',
            'costumer' => 'required|string|max:255',
            'qty' => 'required|integer',
            'operation' => 'nullable|string|max:255',
            'machines' => 'nullable|string|max:255',
            'done' => 'nullable|boolean',
            'status' => 'nullable|string|max:255',
            'machining_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'days' => 'nullable|integer',
            'alert' => 'nullable|boolean',
            'report' => 'nullable|string',
            'our_source' => 'nullable|string|max:255',
            'station_notes' => 'nullable|string',
            'location' => 'nullable|in:Yarnell,Hearst', // 👈 Añadir esto

            // Campos adicionales
            'priority' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'material_type' => 'nullable|string|max:255',
            'process_time' => 'nullable|integer',
            'canceled' => 'nullable|boolean',
            'tracking_number' => 'nullable|string|max:255',
            'revision' => 'nullable|string|max:255',
        ]);

        $order->update($validated);

        return redirect()->route('orders.index')->with('success', 'Orden actualizada correctamente.');
    }









    public function yarnellSchedule(Request $request)
    {

        // Filtra solo las órdenes activas en location 'yarnell'
        $orders = OrderSchedule::where('location', 'yarnell')
            ->where('status', '!=', 'sent')
            ->where(function ($q) {
                $q->whereNull('status_order') // 🧠 permite nulos o explícitamente activos
                    ->orWhere('status_order', 'active');
            })
            ->orderByRaw("CASE WHEN priority = 'yes' THEN 0 ELSE 1 END") // ✔️ Primero las 'yes'
            ->orderByRaw("FIELD(LOWER(status), 'deburring', 'ready','qa', 'assembly','shipping', 'outsource','onhold')")
            ->orderBy('due_date')
            ->get();

        // Si necesitas calcular días restantes como en index()
        foreach ($orders as $order) {
            $order->dias_restantes = $this->calcularDiasInterno(
                $order->status,
                $order->due_date,
                $order->machining_date
            );
        }

        // Define la ubicación para la sincronización
        $location = 'yarnell';

        // Retorna la vista y pasa también la variable $location
        return view('orders.schedule_tableyarnell', compact('orders', 'location'));
    }

    public function hearstSchedule(Request $request)
    {

        $orders = OrderSchedule::where('location', 'hearst')
            ->where('status', '!=', 'sent')
            ->where(function ($q) {
                $q->whereNull('status_order') // 🧠 permite nulos o explícitamente activos
                    ->orWhere('status_order', 'active');
            })
            ->orderByRaw("CASE WHEN priority = 'yes' THEN 0 ELSE 1 END") // ✔️ Primero las 'yes'
            ->orderByRaw("FIELD(LOWER(status), 'deburring', 'ready','qa', 'assembly','shipping', 'outsource','onhold')")
            ->orderBy('due_date')
            ->get();

        foreach ($orders as $order) {
            $order->dias_restantes = $this->calcularDiasInterno(
                $order->status,
                $order->due_date,
                $order->machining_date
            );
        }

        // Define la ubicación para la sincronización
        $location = 'hearst';
        //dd($orders->pluck('status'));
        // Retorna la vista y pasa también la variable $location
        return view('orders.schedule_tablehearst', compact('orders', 'location'));
    }

    // detecta la ultima actualizacion de una orden para actualizar vistas en PCS
    public function lastUpdate()
    {
        $last = \App\Models\OrderSchedule::orderByDesc('updated_at')->first();
        return response()->json([
            'updated_at' => optional($last)->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }
    //----------------------------------------------------------------------------
    public function updateDateMachining(Request $request, OrderSchedule $order)
    {

        Log::info('Entró al método updateDateMachining', [
            'id' => $order->id,
            'fecha' => $request->machining_date
        ]);
        try {
            $request->validate([
                'machining_date' => 'required|date',
            ]);

            $newDate = $request->machining_date;

            // Guardar log solo si la fecha cambia
            if ($order->machining_date !== $newDate) {
                OrdMachiningDateLog::create([
                    'order_schedule_id' => $order->id,
                    'previous_date' => $order->machining_date,
                    'new_date' => $newDate,
                    'changed_by' => Auth::user()?->name ?? 'System',
                ]);

                $order->machining_date = $newDate;
                $order->save();
            }

            // Calcular días restantes
            $dias = $this->calcularDiasInterno($order->status, $order->due_date, $order->machining_date);

            $alert = $dias < 0 || $dias <= 2;
            $alertColor = $dias < 0 ? 'bg-danger' : ($dias <= 2 ? 'bg-warning' : 'bg-success');
            $alertLabel = $dias < 0 ? 'Late' : ($dias <= 2 ? 'Expedite' : 'On time');


            return response()->json([
                'success' => true,
                'machining_date' => $order->machining_date,
                'dias_restantes' => $dias,
                'alertColor' => $alertColor,
                'alertLabel' => $alertLabel,
                'status' => strtolower($order->status), // ✅ Añadir status en minúsculas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //---------------------------------------------------------------------------------------------
    public function updateDueDate(Request $request, OrderSchedule $order)
    {
        Log::info('Entró al método updateDueDate', [
            'id' => $order->id,
            'anterior' => $order->due_date,
            'nueva_fecha' => $request->due_date
        ]);

        try {
            $request->validate([
                'due_date' => 'required|date',
            ]);

            $newDate = $request->due_date;

            // Guardar log solo si la fecha cambia
            if ($order->due_date !== $newDate) {
                // Aquí podrías crear un log similar si deseas
                // DueDateLog::create([...]);

                $order->due_date = $newDate;
                $order->save();
            }

            // Calcular días restantes usando la nueva due_date
            $dias = $this->calcularDiasInterno($order->status, $order->due_date, $order->machining_date);

            $alertColor = $dias < 0 ? 'bg-danger' : ($dias <= 2 ? 'bg-warning' : 'bg-success');
            $alertLabel = $dias < 0 ? 'Late' : ($dias <= 2 ? 'Expedite' : 'On time');

            return response()->json([
                'success' => true,
                'due_date' => $order->due_date,
                'dias_restantes' => $dias,
                'alertColor' => $alertColor,
                'alertLabel' => $alertLabel,
                'status' => strtolower($order->status),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    //----------------------------------------------------------------------------------------------------------



    public function destroy(OrderSchedule $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Orden eliminada.');
    }

    // Método que responde a la petición AJAX, recibe Request
    public function calcularDias(Request $request, OrderSchedule $order)
    {
        $dias = $this->calcularDiasInterno($request->status, $order->due_date, $order->machining_date);
        return response()->json(['success' => true, 'dias' => $dias]);
    }
    // Función interna para hacer el cálculo real (no recibe Request, recibe strings)
    private function calcularDiasInterno($status, $dueDate, $machiningDate)
    {

        $today = \Carbon\Carbon::today();
        $status = strtolower($status);
        $especiales = ['outsource', 'qa', 'deburring', 'shipping', 'assembly', 'ready'];

        if (in_array($status, $especiales)) {
            // Cuando el status es especial, cuenta hasta due_date - 1, no cuenta el día objetivo
            $fechaObjetivo = \Carbon\Carbon::parse($dueDate)->startOfDay()->subDay();
            $dias = $today->diffInWeekdays($fechaObjetivo, false);
            return $dias;  // Permitir negativos
            // return max($dias, 0);  // No devolver días negativos
        } else {
            // Cuando el status NO es especial, cuenta hasta machining_date INCLUYENDO el día objetivo
            $fechaObjetivo = \Carbon\Carbon::parse($machiningDate)->startOfDay();
            // dd($dueDate);
            $dias = $today->diffInWeekdays($fechaObjetivo, false);
            if (!$today->isWeekend() && $dias >= 0) {
                $dias += 1;  // Cuenta el día de hoy si es laborable
            }
            return $dias;  // Permitir negativos
            //return max($dias, 0);
        }
    }







    /**
     * ==================================================================================================
     * PONER UNA ORDEN EN EL CAMPO "status_order"
     * en 'inactive' o en su caso 'active'
     * ===================================================================================================
     */

    public function deactivate(OrderSchedule $order)
    {
        $order->status_order = 'inactive';
        $order->save();

        return redirect()->back()->with('success', 'Order deleted.');
    }

    /**
     * =====================================================================================================
     * PONER UNA ORDEN COMO PRIORIDAD EN EL CAMPO "priority"
     * en 'yes' o  en su caso 'no'
     * ======================================================================================================
     */

    public function setPriority(OrderSchedule $order)
    {
        $order->priority = 'yes';
        $order->save();

        return redirect()->back()->with('success', 'Orden marcada como prioridad.');
    }

    public function togglePriority(OrderSchedule $order)
    {
        $order->priority = $order->priority === 'yes' ? 'no' : 'yes'; // ✅ evita null
        $order->save();

        $message = $order->priority === 'yes'
            ? 'Order marked as priority.'
            : 'Priority removed from order.';

        return redirect()->back()->with('success', $message);
    }


    public function search(Request $request)
    {
        $search = $request->input('term');

        $orders = OrderSchedule::query()
            ->where(function ($q) {
                $q->whereNull('status_order')
                    ->orWhere('status_order', 'active');
            })
            ->whereNull('sent_at') // 🛑 Asegura que no se haya enviado
            ->where(function ($query) use ($search) {
                $query->where('work_id', 'LIKE', "%{$search}%")
                    ->orWhere('PN', 'LIKE', "%{$search}%")
                    ->orWhere('Part_description', 'LIKE', "%{$search}%")
                    ->orWhere('costumer', 'LIKE', "%{$search}%")
                    ->orWhereDate('due_date', $search);
            })
            ->select([
                'id',
                'work_id',
                'PN',
                'Part_description',
                'costumer',
                'due_date',
                'priority', // ✅ Necesario para saber si ya está priorizado
            ])
            ->limit(10)
            ->get();

        return response()->json($orders);
    }




    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 5. TAB "Order Statistics" CONSULTAS
     * ===================================================================================================================
     */

    public function statistics(Request $request)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        /** 🟢 VERIFIED: Table-> Orders This Week */
        $ordenesSemana = OrderSchedule::whereBetween('due_date', [$startOfWeek, $endOfWeek])
            ->whereRaw("LOWER(TRIM(status_order)) != 'inactive'")
            ->get();

        /** 🟢 VERIFIED: Table-> Late Orders */
        $ordenesAtrasadas = OrderSchedule::where('due_date', '<', $today)
            ->where('status', '!=', 'sent')
            ->where('status_order', '!=', 'inactive')
            ->get();

        $cantidadAtrasadas = $ordenesAtrasadas->count();

        // 👉 Semana pasada: lunes a domingo
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();

        // Cantidad de órdenes de la semana pasada que no tienen status 'sent' y ya vencieron
        $cantidadAtrasadasSemanaPasada = OrderSchedule::whereBetween('due_date', [$startOfLastWeek, $endOfLastWeek])
            ->where('status', '!=', 'sent')
            ->where('status_order', '!=', 'inactive')
            ->count();

        /** 🟢 VERIFIED: Box text-> total Order Summary */
        $totalOrdenes = OrderSchedule::where('status', '!=', 'sent')
            ->where('status_order', '!=', 'inactive')
            ->count();

        /** 🟢 VERIFIED: Box text-> total Hearst */
        $cantidadHearst = OrderSchedule::where('location', 'hearst')
            ->where('status', '!=', 'sent')
            ->where('status_order', '!=', 'inactive')
            ->count();

        /** 🟢 VERIFIED: Box text-> total Yarnell */
        $cantidadYarnell = OrderSchedule::where('location', 'yarnell')
            ->where('status', '!=', 'sent')
            ->where('status_order', '!=', 'inactive')
            ->count();

        /** 🟢 VERIFIED: Box text-> total Floor */
        $cantidadFloor = OrderSchedule::where('location', 'floor')
            ->where('status', '!=', 'sent')
            ->where('status_order', '!=', 'inactive')
            ->count();

        /** 🟢 VERIFIED: Circle text-> Ordenes por cliente */
        $ordenesPorCliente = OrderSchedule::select('costumer', DB::raw('count(*) as total'))
            ->where('status', '!=', 'sent')
            ->where('status_order', '!=', 'inactive')
            ->groupBy('costumer')
            ->get();

        // Órdenes creadas esta semana
        $ordenesAgregadasSemana = OrderSchedule::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('status_order', '!=', 'inactive')
            ->get();

        // Cantidad total de esas órdenes
        $totalAgregadasSemana = $ordenesAgregadasSemana->count();

        $customers = OrderSchedule::select('costumer')
            ->whereNotNull('costumer')
            ->distinct()
            ->orderBy('costumer')
            ->pluck('costumer');
        //-------------------------------------------------------------------------------------------
        //Utiliza whereBetween con la fecha de due_date o target_date según tu sistema para obtener solo las órdenes de esta semana:
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $weeklyOrders = OrderSchedule::whereBetween('due_date', [$startOfWeek, $endOfWeek])
            ->whereRaw("LOWER(TRIM(status_order)) != 'inactive'")
            ->orderBy('due_date', 'asc')
            ->get();

        // Retornar un resumen
        $resumen = [
            'total' => $weeklyOrders->count(),
            'send' => $weeklyOrders->where('status', 'sent')->count(),
            'pendients' => $weeklyOrders->where('status', '!=', 'sent')->count(),
            'all_shipping' => $weeklyOrders->every(fn($o) => $o->status === 'sent'),
        ];

        /** ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
         * 🟢 VERIFIED: TABLE-> Weekly Orders
         * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
         */

        //1. Controlador con filtro hasta la semana actual
        $orders = DB::table('orders_schedule')
            ->selectRaw('
        YEARWEEK(due_date, 1) as week,
        COUNT(*) as total,SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = "sent" AND sent_at > due_date THEN 1 ELSE 0 END) as late')
            ->whereRaw('YEARWEEK(due_date, 1) <= YEARWEEK(CURDATE(), 1)') //órdenes cuya due_date está hasta la semana actual (inclusive), ignorando futuras semanas.
            ->groupBy('week')
            ->whereRaw("LOWER(TRIM(status_order)) != 'inactive'")
            ->orderBy('week', 'desc')
            ->get();

        //2. 65% de las semanas cumplieron con todas las órdenes a tiempo
        $totalWeeks = DB::table('orders_schedule')
            ->selectRaw('YEARWEEK(due_date, 1) as week')
            ->whereRaw('YEARWEEK(due_date, 1) <= YEARWEEK(CURDATE(), 1)')
            ->whereRaw("LOWER(TRIM(status_order)) != 'inactive'")
            ->groupBy('week')
            ->get()
            ->count();

        $weeksOnTime = DB::table('orders_schedule')
            ->selectRaw('YEARWEEK(due_date, 1) as week')
            ->whereRaw('YEARWEEK(due_date, 1) <= YEARWEEK(CURDATE(), 1)')
            ->whereRaw("LOWER(TRIM(status_order)) != 'inactive'")
            ->groupBy('week')
            ->havingRaw('SUM(CASE WHEN status != "sent" THEN 1 ELSE 0 END) = 0 AND SUM(CASE WHEN sent_at > due_date THEN 1 ELSE 0 END) = 0')
            ->get()
            ->count();

        $percentageOnTime = $totalWeeks > 0 ? round(($weeksOnTime / $totalWeeks) * 100) : 0;

        //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


        // 👇 Asegúrate de enviar $locations y $statuses a la vista
        return view('orders.schedule_statistics', compact(
            'ordenesSemana',
            'ordenesAtrasadas',
            'cantidadAtrasadasSemanaPasada',
            'cantidadAtrasadas',
            'cantidadHearst',
            'cantidadYarnell',
            'cantidadFloor',
            'totalOrdenes',
            'ordenesPorCliente',
            'ordenesAgregadasSemana',
            'totalAgregadasSemana',
            'customers',
            'resumen',
            'weeklyOrders',
            'orders',
            'percentageOnTime'
        ));
    }

    /** ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🟢 VERIFIED: TABLE-> Orders This Week- Select week
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function getOrdersByWeekAjax(Request $request)
    {
        $weekInput = $request->query('week'); // ejemplo: "2025-W29"

        if (!$weekInput || !preg_match('/^(\d{4})-W(\d{2})$/', $weekInput, $matches)) {
            return response()->json(['html' => '', 'count' => 0]);
        }

        $year = (int)$matches[1];
        $week = (int)$matches[2];

        $start = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $ordenes = OrderSchedule::whereBetween('due_date', [$start, $end])
            ->where('status_order', '!=', 'inactive')
            ->get();

        return response()->json([
            'html' => view('orders.schedule_tablestatistics', ['ordenesSemana' => $ordenes])->render(),
            'count' => $ordenes->count(),
        ]);
    }

    /** ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🟡 FILTERS & ORDERS CHARTS FOR ORDER: For All "Year", "Month", "Week" 
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    // 🟨============== 1. FILTERS  For "Year"===================================== 
    public function summaryByYear(Request $request, $year)
    {
        $query = OrderSchedule::query()
            ->whereYear('created_at', $year)
            ->where('status_order', 'active'); // ⬅️ Solo órdenes activas;

        if ($request->has('customer') && $request->customer) {
            $query->where('costumer', $request->customer);
        }

        $data = $query
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $data->pluck('month')->map(function ($m) {
            return date('F', mktime(0, 0, 0, $m, 10)); // nombre del mes
        });

        $values = $data->pluck('total');

        return response()->json([
            'labels' => $labels,
            'data' => $values,
        ]);
    }
    // 🟨============== 2. FILTERS  For "Month"===================================== 
    public function summaryByMonth(Request $request, $year, $month)
    {
        $query = OrderSchedule::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('status_order', 'active'); // ⬅️ Solo órdenes activas;

        if ($request->has('customer') && $request->customer) {
            $query->where('costumer', $request->customer);
        }

        $data = $query
            ->select(DB::raw('DAY(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $labels = $data->pluck('day')->map(function ($d) use ($month, $year) {
            return date('d M', strtotime("$year-$month-$d"));
        });

        $values = $data->pluck('total');

        return response()->json([
            'labels' => $labels,
            'data' => $values,
        ]);
    }

    // 🟨============== 3. FILTERS  For "Week"===================================== 
    public function summaryByWeek(Request $request, $year, $week)
    {
        $query = OrderSchedule::query()
            ->where('status_order', 'active'); // ⬅️ Solo órdenes activas;

        if ($request->has('customer') && $request->customer) {
            $query->where('costumer', $request->customer);
        }

        // Filtrar por semana usando YEARWEEK MySQL (o puedes usar Carbon para fechas)
        $weekString = $year . str_pad($week, 2, '0', STR_PAD_LEFT); // ej: "202523"

        $data = $query
            ->select(DB::raw('DAYOFWEEK(created_at) as weekday'), DB::raw('COUNT(*) as total'))
            ->whereRaw("YEARWEEK(created_at, 1) = ?", [$weekString]) // 1 = lunes como primer día
            ->groupBy('weekday')
            ->orderBy('weekday')
            ->get();

        // Día de la semana en nombre corto (Dom, Lun, ...)
        $labels = $data->pluck('weekday')->map(function ($w) {
            $days = ['Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat'];
            return $days[$w - 1] ?? 'Día';
        });

        $values = $data->pluck('total');

        return response()->json([
            'labels' => $labels,
            'data' => $values,
        ]);
    }

    /** ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🟡 FILTERS & ORDERS CHARTS FOR ORDER: For CUSTOMER"Year", "Month", "Week" 
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    // 🟨============== 1. FILTERS  For Customer "sin filtro (todo)"===================================== 
    public function summaryByCustomer()
    {
        $data = DB::table('orders_schedule')
            ->select('costumer', DB::raw('count(*) as total'))
            ->groupBy('costumer')
            ->orderBy('total', 'desc')
            ->where('status_order', 'active')
            ->get();

        return $this->formatChartData($data);
    }
    // 🟨============== 2. FILTERS  For Customer "Year"===================================== 
    public function summaryByCustomerYear($year)
    {
        $query = DB::table('orders_schedule')
            ->select('costumer', DB::raw('count(*) as total'))
            ->whereYear('created_at', $year)
            ->groupBy('costumer')
            ->where('status_order', 'active')
            ->orderBy('total', 'desc');

        $data = $query->get();
        $totalAll = $data->sum('total');

        $dataWithPercentage = $data->map(function ($item) use ($totalAll) {
            $item->percentage = $totalAll ? round(($item->total / $totalAll) * 100, 2) : 0;
            return $item;
        });

        return response()->json([
            'labels' => $dataWithPercentage->pluck('costumer'),
            'totals' => $dataWithPercentage->pluck('total'),
            'percentages' => $dataWithPercentage->pluck('percentage'),
            'totalAll' => $totalAll,
        ]);
    }

    // 🟨============== 3. FILTERS  For Customer "Month===================================== 
    public function summaryByCustomerMonth($year, $month)
    {
        $query = DB::table('orders_schedule')
            ->select('costumer', DB::raw('count(*) as total'))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('costumer')
            ->where('status_order', 'active')
            ->orderBy('total', 'desc');

        $data = $query->get();

        $totalAll = $data->sum('total');

        // Añadimos el porcentaje a cada registro
        $dataWithPercentage = $data->map(function ($item) use ($totalAll) {
            $item->percentage = $totalAll ? round(($item->total / $totalAll) * 100, 2) : 0;
            return $item;
        });

        // Preparar datos para la gráfica
        $labels = $dataWithPercentage->pluck('costumer');
        $totals = $dataWithPercentage->pluck('total');
        $percentages = $dataWithPercentage->pluck('percentage');

        return response()->json([
            'labels' => $labels,
            'totals' => $totals,
            'percentages' => $percentages,
            'totalAll' => $totalAll,
        ]);
    }

    // 🟨============== 4. FILTERS  For Customer "Week"=====================================
    public function summaryByCustomerWeek($year, $week)
    {
        $query = DB::table('orders_schedule')
            ->select('costumer', DB::raw('count(*) as total'))
            ->whereRaw('YEARWEEK(created_at, 1) = ?', ["{$year}{$week}"])
            ->groupBy('costumer')
            ->where('status_order', 'active')
            ->orderBy('total', 'desc');

        $data = $query->get();
        $totalAll = $data->sum('total');

        $dataWithPercentage = $data->map(function ($item) use ($totalAll) {
            $item->percentage = $totalAll ? round(($item->total / $totalAll) * 100, 2) : 0;
            return $item;
        });

        return response()->json([
            'labels' => $dataWithPercentage->pluck('costumer'),
            'totals' => $dataWithPercentage->pluck('total'),
            'percentages' => $dataWithPercentage->pluck('percentage'),
            'totalAll' => $totalAll,
        ]);
    }

    /** ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🟡 Orders Due-Next 8 Weeks 
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    public function summaryNextWeeks($weeks = 8)
    {
        try {

            $weeks = (int) $weeks;

            // Lunes de esta semana (00:00)
            $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $endDate     = $startOfWeek->copy()->addWeeks($weeks);

            // Traer datos agrupados por año + semana ISO
            $ordersByWeek = DB::table('orders_schedule')
                ->whereBetween('due_date', [$startOfWeek, $endDate])
                ->whereNotNull('due_date')
                ->where('status_order', 'active')
                ->selectRaw('
                YEAR(due_date)            as y,
                WEEK(due_date, 1)         as w,
                COUNT(*)                  as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_count
            ')
                ->groupByRaw('YEAR(due_date), WEEK(due_date, 1)')
                ->orderBy('y')
                ->orderBy('w')
                ->get()
                ->keyBy(function ($row) {
                    return $row->y . '-' . str_pad($row->w, 2, '0', STR_PAD_LEFT);
                });

            $labels    = [];
            $totalData = [];
            $sentData  = [];

            // Recorremos exactamente $weeks semanas hacia adelante
            for ($i = 0; $i < $weeks; $i++) {
                $weekStart = $startOfWeek->copy()->addWeeks($i);
                $year      = (int) $weekStart->isoWeekYear;
                $week      = (int) $weekStart->isoWeek;

                $key = $year . '-' . str_pad($week, 2, '0', STR_PAD_LEFT);
                $row = $ordersByWeek->get($key);

                $labels[]    = 'Week ' . str_pad($week, 2, '0', STR_PAD_LEFT) . "\n(" . $weekStart->format('M d') . ')';
                $totalData[] = $row->total      ?? 0;
                $sentData[]  = $row->sent_count ?? 0;
            }

            return response()->json([
                'labels' => $labels,
                'total'  => $totalData,
                'sent'   => $sentData,
            ]);
        } catch (\Throwable $e) {

            // Registrar el error con detalle
            Log::error('summaryNextWeeks error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            // Mostrar el error directo al frontend
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }




    /** ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 🟡 On Time vs Late Deliveries
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function summaryOnTimeFiltered(Request $request)
    {
        $query = DB::table('orders_schedule')
            ->whereNotNull('due_date')
            ->whereNotNull('sent_at')
            ->whereRaw("LOWER(status_order) = 'active'"); // ⬅️ SOLO órdenes activas

        if ($request->filled('month')) {
            $month = $request->input('month'); // formato YYYY-MM
            $query->whereRaw("DATE_FORMAT(due_date, '%Y-%m') = ?", [$month]);
        }

        if ($request->filled('year')) {
            $year = $request->input('year');
            $query->whereYear('due_date', $year);
        }

        if ($request->filled('customer')) {
            $query->where('costumer', $request->customer); // 👈 sigue usando la columna 'costumer'
        }

        $result = $query
            ->selectRaw("
                SUM(CASE 
                WHEN sent_at IS NOT NULL AND due_date IS NOT NULL AND DATE(sent_at) < DATE(due_date) THEN 1 
                ELSE 0 
            END) as early,
            SUM(CASE 
                WHEN sent_at IS NOT NULL AND due_date IS NOT NULL AND DATE(sent_at) = DATE(due_date) THEN 1 
                ELSE 0 
            END) as on_time,
            SUM(CASE 
                WHEN sent_at IS NOT NULL AND due_date IS NOT NULL AND DATE(sent_at) > DATE(due_date) THEN 1 
                ELSE 0 
            END) as late
        ")
            ->first();

        return response()->json([
            'labels' => ['Early', 'On Time', 'Late'],
            'data' => [
                $result->early ?? 0,
                $result->on_time ?? 0,
                $result->late ?? 0
            ],
            'total' => ($result->early ?? 0) + ($result->on_time ?? 0) + ($result->late ?? 0),
            'selectedCustomer' => $request->input('customer') ?? 'All',
            'selectedYear' => $request->year ?? '',
        ]);
    }


    ///-----------------------------------------------------


    // Función para formatear respuesta JSON para Chart.js
    private function formatChartData($data)
    {
        return response()->json([
            'labels' => $data->pluck('costumer'),
            'data' => $data->pluck('total'),
        ]);
    }






    //------------------------------------------------------------------------------------------------
}
