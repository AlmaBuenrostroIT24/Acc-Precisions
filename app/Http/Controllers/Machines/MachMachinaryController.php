<?php

namespace App\Http\Controllers\Machines;

use App\Http\Controllers\Controller;
use App\Models\Machines\MachineCode;
use App\Models\Machines\MachineMachinary;
use App\Models\Machines\MachineImage;
use Illuminate\Http\Request;

class MachMachinaryController extends Controller
{
    public function index()
    {
        // Lo que ya tengas para listar la maquinaria
        $machineries = MachineMachinary::with('machineCode')->get();

        // 1) Obtener los IDs que ya están usados en machines_machinary
        $usedCodes = MachineMachinary::pluck('machine_code_id')->toArray();

        // 2) Códigos disponibles:
        $availableCodes = MachineCode::where('status', 'active')
            ->where('type_machine', 'Machine')
            ->whereNotIn('id', $usedCodes)
            ->orderBy('code')
            ->get();

        return view('machines.machinary.index', compact('machineries', 'availableCodes'));
    }


    public function store(Request $request)
    {
        // 1) Validación de datos de la maquinaria
        $data = $request->validate([
            'machine_code_id' => ['required', 'exists:machines_codes,id'],
            'model'           => ['nullable', 'string', 'max:255'],
            'serial'          => ['nullable', 'string', 'max:255'],
            'control_system'  => ['nullable', 'string', 'max:255'],
            'made'            => ['nullable', 'string', 'max:255'],
            'year'            => 'nullable|date', // viene como YYYY-MM-01
            'machine_area'    => ['nullable', 'string', 'max:255'],
            'massof_machine'  => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'servo_batteries' => ['nullable', 'string', 'max:255'],
            'kw'              => 'nullable|string|max:255',
            'voltage'         => ['nullable', 'string', 'max:255'],
            'tool_type'       => ['nullable', 'string', 'max:255'],
            'baud_rate'       => ['nullable', 'string', 'max:255'],
            'tool_qty'        => ['nullable', 'string', 'max:255'],
            // 👇 NUEVO: validación de imágenes
            'images'   => ['nullable', 'array', 'max:3'], // máximo 3 archivos
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], // 4MB c/u
        ]);

        // 2) Crear la maquinaria (sin incluir el array 'images')
        $machine = MachineMachinary::create(
            collect($data)->except('images')->toArray()
        );

        // 3) Guardar imágenes (tabla machines_images)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {

                // Guardar archivo en storage/app/public/machines
                $path = $file->store('machines', 'public');

                // Crear registro en machines_images
                $machine->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()
            ->route('machines.machinary.index')
            ->with('success', 'Machinery registered successfully.');
    }

    public function edit(MachineMachinary $machinary)
    {
        $codes = MachineCode::orderBy('code')->get();

        return view('machines.machinary.edit', [
            'machinary' => $machinary,
            'codes'     => $codes,
        ]);
    }

    public function update(Request $request, MachineMachinary $machinary)
    {
        $data = $request->validate([
            'machine_code_id' => ['required', 'exists:machines_codes,id'],
            'model'           => ['nullable', 'string', 'max:255'],
            'serial'          => ['nullable', 'string', 'max:255'],
            'type'            => ['nullable', 'string', 'max:255'],
            'control_system'  => ['nullable', 'string', 'max:255'],
            'made'            => ['nullable', 'string', 'max:255'],
            'year'            => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'machine_area'    => ['nullable', 'string', 'max:255'],
            'massof_machine'  => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'servo_batteries' => ['nullable', 'string', 'max:255'],
            'kw'              => ['nullable', 'numeric'],
            'voltage'         => ['nullable', 'string', 'max:255'],
            'tool_type'       => ['nullable', 'string', 'max:255'],
            'baud_rate'       => ['nullable', 'string', 'max:255'],
            'tool_qty'        => ['nullable', 'integer', 'min:0'],
        ]);

        $machinary->update($data);

        return redirect()
            ->route('machines.machinary.index')
            ->with('success', 'Machinery updated successfully.');
    }

    public function destroy(MachineMachinary $machinary)
    {
        $machinary->delete();

        return redirect()
            ->route('machines.machinary.index')
            ->with('success', 'Machinery deleted successfully.');
    }
}
