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

use Illuminate\Support\Facades\Log;

class Order_ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $orders = OrderSchedule::latest()->get();
        //return view('orders.index_schedule', compact('orders'));

        $query = OrderSchedule::whereRaw('LOWER(status) != ?', ['sent']); // excluye "sent"
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ordenar por due_date descendente (más reciente primero)

        // 👇 Filtrar automáticamente solo si estamos en /schedule/general y el usuario tiene un rol específico
        //Agrega una verificación con auth()->check() antes de llamar a hasRole():
        if ($request->is('schedule/general') && auth()->check() && auth()->user()->hasRole('QAdmin')) {
            $query->where('location', 'yarnell');
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
        return view('orders.index_schedule', compact('orders', 'locations', 'statuses', 'customers'));
    }


    public function store(Request $request)
    {
        // Log::info('Request completo:', $request->all());

        $mapping = [
            'col_text_1'  => 'location',
            'col_text_2'  => 'work_id',
            'col_text_3'  => 'PN',
            'col_text_4'  => 'Part_description',
            'col_text_5'  => 'costumer',
            'col_text_6'  => 'qty',
            'col_text_7'  => 'wo_qty',
            'col_text_8'  => 'status',
            'col_text_9'  => 'machining_date',
            'col_text_10' => 'due_date',
            'col_text_12' => 'days',
            'col_text_13' => 'alert',
            'col_text_14' => 'report',
            'col_text_15' => 'our_source',
            'col_text_16' => 'station',
            'col_text_17' => 'notes',
        ];

        try {
            $input = $request->only(array_keys($mapping));

            $data = [];
            foreach ($input as $key => $value) {
                if (isset($mapping[$key])) {
                    $data[$mapping[$key]] = $value;
                }
            }

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

            // Asegurar que alert NO sea nulo
            if (empty($data['alert'])) {
                $data['alert'] = '';
            }

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
            'customers'
        ));
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
            $order->status = $newStatus;

            // ✅ Si el status es "deburring" o "shipping", actualizar la location a "hearst"
            if (
                in_array($newStatus, ['deburring', 'shipping']) &&
                ($order->location) === 'Yarnell'
            ) {
                $order->last_location = $order->location; // Guardar ubicación anterior
                $order->location = 'Hearst';
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
                'status' => $order->status,
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

        // Filtra solo las órdenes con location 'yarnell'
        $orders = OrderSchedule::where('location', 'yarnell')->latest()->get();

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

        // Filtra solo las órdenes con location 'hw'
        $orders = OrderSchedule::where('location', 'hearst')->latest()->get();

        // Si necesitas calcular días restantes como en index()
        foreach ($orders as $order) {
            $order->dias_restantes = $this->calcularDiasInterno(
                $order->status,
                $order->due_date,
                $order->machining_date
            );
        }

        // Define la ubicación para la sincronización
        $location = 'hearst';

        // Retorna la vista y pasa también la variable $location
        return view('orders.schedule_tablehearst', compact('orders', 'location'));
    }

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
        $especiales = ['outsource', 'qa', 'deburring', 'shipping'];

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
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        // Ahora pasas el servicio al importador cuando lo creas
        $importer = new OrderScheduleImport($this->service);

        // Usar el importador para importar el archivo
        Excel::import($importer, $request->file('csv_file'));

        // Acceder al contador de registros importados
        $count = $this->service->importedCount;

        return redirect()->route('schedule.general')
            ->with('success', "$count records were imported successfully.");
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
            $days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
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

    // Función para formatear respuesta JSON para Chart.js
    private function formatChartData($data)
    {
        return response()->json([
            'labels' => $data->pluck('costumer'),
            'data' => $data->pluck('total'),
        ]);
    }
}
