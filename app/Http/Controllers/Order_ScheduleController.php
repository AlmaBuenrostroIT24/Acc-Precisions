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
     * Display a listing of the resource.
     */

    //------------------------------------------------INTERFACE VIEWS--------------------------------------------------------------
    //General Schedule-------------------------------------------------------------------------------------------------------------

    public function index(Request $request)
    {
        $base = OrderSchedule::query()
            ->where('status', '!=', 'sent')
            ->where(function ($q) {
                $q->whereNull('status_order')->orWhere('status_order', 'active');
            });

        // 👇 Filtros por request (sin duplicar)
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


    //----------------------------------------------------------------------------------------------------------------------------
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



    //Orders Yarnell--------------------------------------------------------------------------------------------------------------

    public function endyarnell(Request $request)
    {
        $query = OrderSchedule::latest()
            ->where('last_location', 'Yarnell')
            ->where('location', 'Hearst'); // solo órdenes que están en Hearst y vienen de Yarnell

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        // 👇 obtenemos los valores únicos
        $statuses = OrderSchedule::select('status')->distinct()->pluck('status');
        $customers = OrderSchedule::select('costumer')->distinct()->pluck('costumer');

        foreach ($orders as $order) {
            $order->dias_restantes = $this->calcularDiasInterno(
                $order->status,
                $order->due_date,
                $order->machining_date
            );
        }

        return view('orders.schedule_endyarnell', compact('orders', 'statuses', 'customers'));
    }

    //----------------------------------------------------------------------------------------------------------------------------
    //Completed Orders--------------------------------------------------------------------------------------------------------------

    public function finished(Request $request)
    {
        // $orders = OrderSchedule::latest()->get();
        //return view('orders.index_schedule', compact('orders'));

        $query = OrderSchedule::latest();

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        // 👇 obtenemos TODAS las ubicaciones sin afectar la paginación
        $locations = OrderSchedule::select('location')->distinct()->pluck('location');
        $statuses = OrderSchedule::select('status')->distinct()->pluck('status');
        $customers = OrderSchedule::select('costumer')->distinct()->pluck('costumer');

        foreach ($orders as $order) {
            $order->dias_restantes = $this->calcularDiasInterno(
                $order->status,
                $order->due_date,
                $order->machining_date
            );
        }
        // 👇 Asegúrate de enviar $locations y $statuses a la vista
        return view('orders.schedule_finished', compact('orders', 'locations', 'statuses', 'customers'));
    }

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
    //Orders Statistics--------------------------------------------------------------------------------------------------------------

    public function statistics(Request $request)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $ordenesSemana = OrderSchedule::whereBetween('due_date', [$startOfWeek, $endOfWeek])->get();

        $ordenesAtrasadas = OrderSchedule::where('due_date', '<', $today)
            ->where('status', '!=', 'sent')
            ->get();

        $cantidadAtrasadas = $ordenesAtrasadas->count();

        // 👉 Semana pasada: lunes a domingo
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();

        // Cantidad de órdenes de la semana pasada que no tienen status 'sent' y ya vencieron
        $cantidadAtrasadasSemanaPasada = OrderSchedule::whereBetween('due_date', [$startOfLastWeek, $endOfLastWeek])
            ->where('due_date', '<', $today)
            ->where('status', '!=', 'sent')
            ->count();

        $totalOrdenes = OrderSchedule::where('status', '!=', 'sent')->count();

        $cantidadHearst = OrderSchedule::where('location', 'hearst')
            ->where('status', '!=', 'sent')
            ->count();
        $cantidadYarnell = OrderSchedule::where('location', 'yarnell')
            ->where('status', '!=', 'sent')
            ->count();
        $cantidadFloor = OrderSchedule::where('location', 'floor')
            ->where('status', '!=', 'sent')
            ->count();

        $ordenesPorCliente = OrderSchedule::select('costumer', DB::raw('count(*) as total'))
            ->where('status', '!=', 'sent')
            ->groupBy('costumer')
            ->get();

        // Órdenes creadas esta semana
        $ordenesAgregadasSemana = OrderSchedule::whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();

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
            ->orderBy('due_date', 'asc')
            ->get();
        // Retornar un resumen
        $resumen = [
            'total' => $weeklyOrders->count(),
            'send' => $weeklyOrders->where('status', 'sent')->count(),
            'pendients' => $weeklyOrders->where('status', '!=', 'sent')->count(),
            'all_shipping' => $weeklyOrders->every(fn($o) => $o->status === 'sent'),
        ];
        //--------------------------------------------------------------------------------------
        //Controlador con filtro hasta la semana actual
        $orders = DB::table('orders_schedule')
            ->selectRaw('
        YEARWEEK(due_date, 1) as week,
        COUNT(*) as total,SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = "sent" AND sent_at > due_date THEN 1 ELSE 0 END) as late')
            ->whereRaw('YEARWEEK(due_date, 1) <= YEARWEEK(CURDATE(), 1)') //órdenes cuya due_date está hasta la semana actual (inclusive), ignorando futuras semanas.
            ->groupBy('week')
            ->orderBy('week', 'desc')
            ->get();
        //------------------------------------------------------------------
        //65% de las semanas cumplieron con todas las órdenes a tiempo
        $totalWeeks = DB::table('orders_schedule')
            ->selectRaw('YEARWEEK(due_date, 1) as week')
            ->whereRaw('YEARWEEK(due_date, 1) <= YEARWEEK(CURDATE(), 1)')
            ->groupBy('week')
            ->get()
            ->count();

        $weeksOnTime = DB::table('orders_schedule')
            ->selectRaw('YEARWEEK(due_date, 1) as week')
            ->whereRaw('YEARWEEK(due_date, 1) <= YEARWEEK(CURDATE(), 1)')
            ->groupBy('week')
            ->havingRaw('SUM(CASE WHEN status != "sent" THEN 1 ELSE 0 END) = 0 AND SUM(CASE WHEN sent_at > due_date THEN 1 ELSE 0 END) = 0')
            ->get()
            ->count();

        $percentageOnTime = $totalWeeks > 0 ? round(($weeksOnTime / $totalWeeks) * 100) : 0;
        //-------------------------------------------------------------------------------------
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
    //----------------------------------------------------------------------------------------------------------------------------
    //----------------------------------------------------------------------------------------------------------------------------

    public function store(Request $request)
    {
       // Log::info('Request completo:', $request->all());

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
            'col_text_9' => 'due_date',
            'col_text_10' => 'days',
            'col_text_11' => 'alert',
            'col_text_12' => 'report',
            'col_text_13' => 'our_source',
            'col_text_14' => 'station',
            'col_text_15' => 'notes',
        ];

        try {
            $input = $request->only(array_keys($mapping));

            $data = [];
            foreach ($input as $key => $value) {
                if (isset($mapping[$key])) {
                    $data[$mapping[$key]] = $value;
                }
            }
            // 🔎 DEBUG: original_id recibido
           // Log::info('original_id recibido:', ['original_id' => $request->input('original_id')]);
            // --- 👇 Aquí inyectamos co y cust_po desde la orden original ---
            if ($request->filled('original_id')) {
                $orig = OrderSchedule::select('co', 'cust_po')
                    ->find($request->input('original_id'));
                if ($orig) {
                    $data['co']      = $orig->co;
                    $data['cust_po'] = $orig->cust_po;
                }
            }
            // ---------------------------------------------------------------

            // Limpiar cadenas
            foreach ($data as $field => &$value) {
                if (is_string($value)) {
                    $value = trim(preg_replace('/\s+/', ' ', $value));
                }
            }
            unset($value);

            // Extraer solo el número para 'days'
            if (!empty($data['days'])) {
                preg_match('/\d+/', $data['days'], $matches);
                $data['days'] = isset($matches[0]) ? (int)$matches[0] : null;
            }

            // Convertir fechas con Carbon (manejar excepciones)
            try {
                if (!empty($data['machining_date'])) {
                    $data['machining_date'] = \Carbon\Carbon::parse($data['machining_date'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $data['machining_date'] = null;
            }
            try {
                if (!empty($data['due_date'])) {
                    $data['due_date'] = \Carbon\Carbon::parse($data['due_date'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $data['due_date'] = null;
            }

            // Defaults
            $data['alert']             = $data['alert'] ?? '';
            $data['priority']          = $data['priority'] ?? 'no';
            $data['status_order']      = $data['status_order'] ?? 'active';
            $data['operation']         = $data['operation'] ?? '0';
            $data['total_fai']         = $data['total_fai'] ?? '0';
            $data['total_ipi']         = $data['total_ipi'] ?? '0';
            $data['sampling']          = $data['sampling'] ?? '0';
            $data['status_inspection'] = $data['status_inspection'] ?? 'pending';


            $validatedData = validator($data, [
                'work_id'        => 'nullable|string|max:255',
                'PN'             => 'nullable|string|max:255',
                'Part_description' => 'nullable|string|max:255',
                'qty'            => 'nullable|integer|min:0',
                'costumer'       => 'required|string|max:255',
                'wo_qty'         => 'nullable|integer',
                'status'         => 'nullable|string|max:255',
                'machining_date' => 'nullable|date',
                'due_date'       => 'nullable|date',
                'days'           => 'nullable|integer',
                'alert'          => 'nullable|string',
                'report'         => 'nullable|string',
                'our_source'     => 'nullable|string|max:255',
                'station'        => 'nullable|string|max:255',
                'notes'          => 'nullable|string',
                'location'       => 'nullable|string|max:255',
                'priority'       => 'nullable|string|max:10',
                'status_order'   => 'nullable|string|max:10',
                'operation'      => 'nullable|string|max:255',
                'total_fai'      => 'nullable|integer',
                'total_ipi'      => 'nullable|integer',
                'sampling'       => 'nullable|integer',
                'status_inspection'  => 'nullable|string|max:50',
                'co'             => 'nullable|string|max:255',
                'cust_po'        => 'nullable|string|max:255',
            ])->validate();

            $order = OrderSchedule::create($validatedData);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'message' => 'Orden creada correctamente',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning('Validación fallida en store: ' . $ve->getMessage());
            return response()->json([
                'success' => false,
                'errors'  => $ve->errors(),
                'message' => 'Error de validación en los datos.',
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error inesperado en store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Contacta al administrador.',
            ], 500);
        }
    }


    public function updateWoQty(Request $request, $id)
    {
        // Log::info('Petición WO_QTY', ['id' => $id, 'data' => $request->all()]);
        $request->validate([
            'wo_qty' => 'required|integer|min:0',
        ]);

        $order = OrderSchedule::findOrFail($id);
        $order->wo_qty = $request->input('wo_qty');
        $order->save();

        return response()->json(['success' => true]);
    }


    public function create()
    {
        return view('orders.create');
    }

    /*  public function store(Request $request)
    {
        $validated = $request->validate([
            'work_id' => 'required|string|max:255',
            'was_work_id_null' => empty($parsedWorkId),
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

        // Agregar usuario autenticado como creador si está logueado
        $validated['created_by'] = auth()->check() ? auth()->id() : null;

        OrderSchedule::create($validated);

        return redirect()->route('orders.index')->with('success', 'Orden creada correctamente.');
    }*/

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



    public function updateStatus(Request $request, OrderSchedule $order)
    {
        try {

            // Log::info("Cambio de status orden {$order->id} a: " . $request->status);

            $request->validate([
                'status' => 'required|string|max:50',
                'target_date' => 'nullable|date',
            ]);

            $newStatus = strtolower($request->status);
            // Captura el estado anterior antes de sobrescribirlo
            $previousStatus = $order->status;
            $order->status = $newStatus;

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


    public function updateReport(Request $request, OrderSchedule $order)
    {
        $order->report = $request->input('report');
        $order->save();

        return response()->json(['success' => true]);
    }

    public function updateSource(Request $request, OrderSchedule $order)
    {
        $order->our_source = $request->our_source;
        $order->save();

        return response()->json(['success' => true]);
    }

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

    public function ajaxUpdateWorkId(Request $request, OrderSchedule  $order)
    {
        $order->work_id = $request->input('work_id');
        $order->save();

        return response()->json(['success' => true]);
    }

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

    //--------------------------------------------------------------------------------------------------------------
    ///////////////////Imporr Excel 

    protected $service;

    // Inyectar el servicio en el constructor
    public function __construct(OrderScheduleImportService $service)
    {
        $this->service = $service;
    }
    public function import(Request $request)
    {
        // 0) Validación flexible para CSV/XLS/XLSX
        $request->validate([
            // sube el máximo si tus archivos pesan más
            'csv_file' => 'required|file|max:20480|mimes:csv,txt,xlsx,xls',
        ]);

        // 1) Asegura que el archivo realmente llegó
        if (!$request->hasFile('csv_file')) {
            return back()->withErrors(['csv_file' => 'No llegó ningún archivo (hasFile=false).']);
        }

        $file = $request->file('csv_file');

        if (!$file->isValid()) {
            Log::error('Upload inválido', ['error_code' => $file->getError()]);
            return back()->withErrors(['csv_file' => 'El archivo subido no es válido. Código: ' . $file->getError()]);
        }

        // 2) Log de metadatos del archivo
        //--------------------------------------------
        /*Log::info('📦 Archivo recibido', [
            'client_name' => $file->getClientOriginalName(),
            'mime'        => $file->getMimeType(),
            'size_kb'     => round($file->getSize() / 1024, 1),
            'ext'         => $file->getClientOriginalExtension(),
        ]);*/
        //--------------------------------------------

        // 3) Guarda una copia para depurar e impórtala desde storage (evita issues de stream)
        $storedPath = $file->storeAs(
            'tmp_imports',
            now()->format('Ymd_His') . '__' . $file->getClientOriginalName()
        );
        $absPath = storage_path('app/' . $storedPath);
        //--------------------------------------------
        // Log::info('📁 Copia guardada', ['path' => $storedPath]);
        //--------------------------------------------

        // 4) (Opcional pero útil) Ver los encabezados que Laravel-Excel detecta
        try {
            $heads = (new HeadingRowImport)->toArray($absPath);
            //--------------------------------------------
            /*Log::info('🧾 Encabezados detectados (primera hoja)', [
                'headings' => $heads[0][0] ?? [],
            ]);*/
            //--------------------------------------------
        } catch (\Throwable $e) {
            Log::warning('No se pudieron leer encabezados', ['msg' => $e->getMessage()]);
        }

        // 5) Ejecuta el import con try/catch para ver VALIDACIONES de filas y otros errores
        try {
            $importer = new OrderScheduleImport($this->service);

            // 👇 Importa desde el path guardado (más estable que pasar UploadedFile)
            Excel::import($importer, $absPath);

            $count = $this->service->importedCount ?? 0;
            // Log::info('✅ Import finalizado', ['importedCount' => $count]);

            // Si count=0, probablemente TODAS las filas fueron descartadas por tu service:
            // - due_date no parseada
            // - faltan part_id / misc_reference / due_date
            // - duplicados
            if ($count === 0) {
                Log::warning('⚠️ Import terminó con 0 filas. Revisa logs del servicio: ' .
                    'parseo de due_date, claves de encabezado, y columnas unset.');
            }

            return redirect()->route('schedule.general')
                ->with('success', "$count records were imported successfully.");
        } catch (ExcelValidationException $e) {
            // Validaciones por fila (si usas WithValidation)
            $msgs = collect($e->failures())->map(
                fn($f) =>
                "Row {$f->row()}: " . implode(', ', $f->errors())
            )->take(20)->implode(' | ');

            Log::warning('❌ Fallos de validación por fila', ['count' => count($e->failures())]);
            return back()->withErrors(['import' => 'Errores de validación: ' . $msgs]);
        } catch (\Throwable $e) {
            /* Log::error('💥 Excepción durante import', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);*/
            return back()->with('error', 'No se pudo importar: ' . $e->getMessage());
        }
    }

    //--------------------------------------------------------------------------------------------------------------
    // Por año (meses)
    public function summaryByYear(Request $request, $year)
    {
        $query = OrderSchedule::query()
            ->whereYear('created_at', $year);

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
    // Por mes (días)
    public function summaryByMonth(Request $request, $year, $month)
    {
        $query = OrderSchedule::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

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

    public function summaryByWeek(Request $request, $year, $week)
    {
        $query = OrderSchedule::query();

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
    //--------------------------------------------------------------------------------------------------------------
    // Resumen sin filtro (todo)
    public function summaryByCustomer()
    {
        $data = DB::table('orders_schedule')
            ->select('costumer', DB::raw('count(*) as total'))
            ->groupBy('costumer')
            ->orderBy('total', 'desc')
            ->get();

        return $this->formatChartData($data);
    }

    // Filtro por año
    public function summaryByCustomerYear($year)
    {
        $query = DB::table('orders_schedule')
            ->select('costumer', DB::raw('count(*) as total'))
            ->whereYear('created_at', $year)
            ->groupBy('costumer')
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

    // Filtro Año: summaryByCustomerYear
    public function summaryByCustomerMonth($year, $month)
    {
        $query = DB::table('orders_schedule')
            ->select('costumer', DB::raw('count(*) as total'))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('costumer')
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

    // Filtro por semana
    public function summaryByCustomerWeek($year, $week)
    {
        $query = DB::table('orders_schedule')
            ->select('costumer', DB::raw('count(*) as total'))
            ->whereRaw('YEARWEEK(created_at, 1) = ?', ["{$year}{$week}"])
            ->groupBy('costumer')
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



    public function getOrdersByWeekAjax(Request $request)
    {
        $weekInput = $request->query('week'); // ejemplo: "2025-W29"

        if (!$weekInput || !preg_match('/^(\d{4})-W(\d{2})$/', $weekInput, $matches)) {
            return response()->json(['html' => '', 'count' => 0]);
        }

        $year = (int)$matches[1];
        $week = (int)$matches[2];

        $start = \Carbon\Carbon::now()->setISODate($year, $week)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $ordenes = OrderSchedule::whereBetween('due_date', [$start, $end])->get();

        return response()->json([
            'html' => view('orders.schedule_tablestatistics', ['ordenesSemana' => $ordenes])->render(),
            'count' => $ordenes->count(),
        ]);
    }


    ///-----------------------------------------------------
    public function summaryNextWeeks($weeks = 8)
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endDate = $startOfWeek->copy()->addWeeks($weeks);

        // Agrupar por semana y contar total y enviados
        $ordersByWeek = DB::table('orders_schedule')
            ->selectRaw("YEARWEEK(due_date, 1) as yearweek")
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count")
            ->whereBetween('due_date', [$startOfWeek, $endDate])
            ->whereNotNull('due_date')
            ->groupBy('yearweek')
            ->orderBy('yearweek')
            ->get();

        $labels = [];
        $totalData = [];
        $sentData = [];

        foreach ($ordersByWeek as $row) {
            $year = substr($row->yearweek, 0, 4);
            $week = substr($row->yearweek, 4);

            $monday = Carbon::now()->setISODate($year, $week)->format('M d');

            $labels[] = "Week $week\n($monday)";
            $totalData[] = $row->total;
            $sentData[] = $row->sent_count;
        }

        return response()->json([
            'labels' => $labels,
            'total' => $totalData,
            'sent' => $sentData,
        ]);
    }
    //--------------------------------------------------------------------


    public function summaryOnTimeFiltered(Request $request)
    {
        $query = DB::table('orders_schedule')
            ->whereNotNull('due_date')
            ->whereNotNull('sent_at');

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
    public function deactivate(OrderSchedule $order)
    {
        $order->status_order = 'inactive';
        $order->save();

        return redirect()->back()->with('success', 'Order deleted.');
    }

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


    //------------------------------------------------------------------------------------------------
}
