<?php

namespace App\Http\Controllers\Machines;

use Illuminate\Http\Request;
use App\Models\Machines\MachineCode;
use App\Http\Controllers\Controller;

class MachCodeController extends Controller
{
    // Mapea el tipo de máquina/equipo al prefijo
    private $prefixMap = [
        'Fabrication Equipment' => 'FE',
        'Grinding Equipment'    => 'GE',
        'Manual Lathe'          => 'ML',
        'Manual Mill'           => 'MM',
        'Other Equipment'       => 'OE',
        'Welding Equipment'     => 'WE',
    ];

    private $machineBrands = [
        'Mori Seiki CO. LTD'                 => 'Mori',
        'Hwacheon Machine Tool CO. LTD'      => 'HW',
        'Dossan Infracore Co., Ltd'          => 'D',
        'Yeong Chin Machinery Industries CO. LTD' => 'YCM',
        'Ganesh Machinery'                   => 'Gan',
        'SMEC. CO. LTD'                      => 'SME',
        'Siemens'                            => 'G',
        'Kia Heavy Insdustries Corp'         => 'Kia',
        'FANUC LTD'                          => 'F',
        'Kitamura Machinery CO. LTD'         => 'Kit',
        'Howa Machinery, LTD'                => 'H',
        'Brother Industries, LTD'            => 'B',
        'Litz Hitech Corp.'                  => 'Kiw',
        'Chevalier Machinery INC.'           => 'C',
    ];

    private $machineTypes = [
        'CNC Lathe',
        'CNC Mill',  // agrega aquí los tipos que consideres máquinas
    ];

    // Método para devolver las marcas a la vista (AJAX)
    public function getMachineBrands()
    {
        return response()->json($this->machineBrands);
    }

    // ✅ GET /machines/codes
    public function index()
    {
        // Obtener todas las máquinas
        $allCodes = MachineCode::all();

        // Separar por tipo
        $machines   = $allCodes->where('type_machine', 'Machine');
        $equipments = $allCodes->where('type_machine', 'Equipment');

        return view('machines.codes.mach_codeindex', compact('machines', 'equipments'));
    }

    // ✅ GET /machines-codes/next-code-by-brand?brand=Mori Seiki CO. LTD
    public function getNextCodeByBrand(Request $request)
    {
        $brand = $request->query('brand');

        $brandMap = [
            'Mori Seiki CO. LTD'                 => 'Mori',
            'Hwacheon Machine Tool CO. LTD'      => 'HW',
            'Dossan Infracore Co., Ltd'          => 'D',
            'Yeong Chin Machinery Industries CO. LTD' => 'YCM',
            'Ganesh Machinery'                   => 'Gan',
            'SMEC. CO. LTD'                      => 'SME',
            'Siemens'                            => 'G',
            'Kia Heavy Insdustries Corp'         => 'Kia',
            'FANUC LTD'                          => 'F',
            'Kitamura Machinery CO. LTD'         => 'Kit',
            'Howa Machinery, LTD'                => 'H',
            'Brother Industries, LTD'            => 'B',
            'Litz Hitech Corp.'                  => 'Kiw',
            'Chevalier Machinery INC.'           => 'C',
        ];

        if (!isset($brandMap[$brand])) {
            return response()->json(['error' => 'Marca inválida'], 400);
        }

        $prefix = $brandMap[$brand];

        // Buscar el último código que tenga ese prefijo (SIN guion: Mori001, YCM001, etc.)
        $last = MachineCode::where('code', 'like', $prefix . '%')   // antes: "$prefix-%"
            ->orderBy('code', 'desc')
            ->first();

        // Extraer la parte numérica después del prefijo
        $nextNumber = $last
            ? (int) substr($last->code, strlen($prefix)) + 1   // antes: strlen($prefix) + 1
            : 1;

        // Ejemplo: Mori1, YCM2
        $nextCode = $prefix . $nextNumber;  // Sin ceros

        return response()->json(['next_code' => $nextCode]);
    }

    // ✅ GET /machines-codes/next-code?type=Fabrication Equipment
    public function getNextCode(Request $request)
    {
        $type = $request->query('type');

        // Mapea el tipo de máquina al prefijo
        $prefixMap = [
            'Fabrication Equipment' => 'FE',
            'Grinding Equipment'    => 'GE',
            'Manual Lathe'          => 'ML',
            'Manual Mill'           => 'MM',
            'Other Equipment'       => 'OE',
            'Welding Equipment'     => 'WE',
        ];

        // Validar que el tipo de máquina es válido
        if (!isset($prefixMap[$type])) {
            return response()->json(['error' => 'Tipo inválido'], 400);
        }

        $prefix = $prefixMap[$type];

        // Buscar el último código de la misma categoría (sin guion: FE001, GE002, etc.)
        $last = MachineCode::where('code', 'like', $prefix . '%')   // antes: "$prefix-%"
            ->orderBy('code', 'desc')
            ->first();

        // Si no existe ningún código, se genera el primero (por ejemplo, FE001)
        $nextNumber = $last
            ? (int) substr($last->code, strlen($prefix)) + 1   // antes: strlen($prefix) + 1
            : 1;

        // Generar el siguiente código (FE001, FE002, ...)
        $nextCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return response()->json(['next_code' => $nextCode]);
    }

    // ✅ POST /machines/codes
    public function store(Request $request)
    {
        $request->validate([
            'type_machine' => 'required|in:Machine,Equipment',
            'type_work'    => 'nullable|string',
            'name'         => 'required|string',
            'location'     => 'nullable|string',
            'brand'        => 'nullable|string',
            'status'       => 'required|in:active,inactive,maintenance',
        ]);

        $typeMachine = $request->input('type_machine');
        $brand       = $request->input('brand');
        $typeWork    = $request->input('type_work');

        // ===============================
        //  A) EQUIPMENT → código por tipo (FE001, GE002, ...)
        // ===============================
        if ($typeMachine === 'Equipment') {

            if (!isset($this->prefixMap[$typeWork])) {
                return back()
                    ->withErrors(['type_work' => 'Tipo de equipo no válido.'])
                    ->withInput();
            }

            $prefix = $this->prefixMap[$typeWork];

            $last = MachineCode::where('code', 'like', $prefix . '%')   // antes: "$prefix-%"
                ->orderBy('code', 'desc')
                ->first();

            $nextNumber = $last
                ? (int) substr($last->code, strlen($prefix)) + 1   // antes: strlen($prefix) + 1
                : 1;

            $newCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // ===============================
            //  B) MACHINE → código por marca (YCM001, Mori001, etc.)
            // ===============================
        } else { // Machine

            if (!isset($this->machineBrands[$brand])) {
                return back()
                    ->withErrors(['brand' => 'Marca de máquina no válida.'])
                    ->withInput();
            }

            $prefix = $this->machineBrands[$brand]; // ej. 'YCM', 'Mori'

            // Buscar último código YCMxxx, Morixxx, etc.
            $last = MachineCode::where('code', 'like', $prefix . '%')   // antes: "$prefix-%"
                ->orderBy('code', 'desc')
                ->first();

            $nextNumber = $last
                ? (int) substr($last->code, strlen($prefix)) + 1   // antes: strlen($prefix) + 1
                : 1;

            $newCode = $prefix . $nextNumber;  // Sin ceros
        }

        // Crear registro
        $machine = MachineCode::create([
            'code'         => $newCode,
            'name'         => $request->input('name'),
            'location'     => $request->input('location'),
            'brand'        => $brand,
            'type_machine' => $typeMachine,
            'type_work'    => $typeWork,
            'status'       => $request->input('status'),
        ]);

        return redirect()
            ->route('codes.index')   // asegúrate que tu resource use nombres "codes.*"
            ->with('success', 'Máquina/equipo registrado con éxito.');
    }

    // ✅ GET /machines/codes/{id}
    public function show($id)
    {
        $machine = MachineCode::find($id);

        if (!$machine) {
            return response()->json(['error' => 'Máquina no encontrada.'], 404);
        }

        return $machine;
    }

    // ✅ PUT /machines/codes/{id}
    public function update(Request $request, $id)
    {
        $machine = MachineCode::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|unique:machines_codes,code,' . $machine->id,
            'name' => 'required|string',
            'brand' => 'required|string',
            'location' => 'required|string',
            'status' => 'required|in:active,inactive,maintenance',
            'type_work' => 'required|string',
        ]);

        $machine->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Machine code updated successfully.',
            'data' => $machine
        ]);
    }

    public function toggleStatus($id)
    {
        $code = MachineCode::findOrFail($id);

        // Si está activo → se inactiva, si no → se activa
        $code->status = $code->status === 'active' ? 'inactive' : 'active';
        $code->save();

        return response()->json([
            'success' => true,
            'new_status' => $code->status
        ]);
    }

    // ✅ DELETE /machines/codes/{id}
    public function destroy($id)
    {
        $machine = MachineCode::find($id);

        if (!$machine) {
            return response()->json(['error' => 'Máquina no encontrada.'], 404);
        }

        $machine->delete();

        return response()->json(['message' => 'Máquina eliminada con éxito.']);
    }
}
