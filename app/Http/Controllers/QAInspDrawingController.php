<?php

namespace App\Http\Controllers;

use App\Models\QAInspDrawing;
use App\Models\QAInspCharacteristic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QAInspDrawingController extends Controller
{
    public function create()
    {
        // Formulario para subir PDF/imagen
        return view('qa.inspectionplan.create');
    }

    public function store(Request $request)
    {
        Log::info('QAInspDrawingController@store llamado', $request->all());

        // Aceptamos PDF o imagen
        $data = $request->validate([
            'file'     => 'required|mimes:pdf,png,jpg,jpeg|max:51200',
            'customer' => 'nullable|string',
            'pn'       => 'nullable|string',
            'rev'      => 'nullable|string',
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());

        $pathForDrawing = null; // ruta de la IMAGEN final (PNG/JPG) para el plano

        // ===========================
        //  SI ES PDF → usar Ghostscript
        // ===========================
        if ($ext === 'pdf') {

            // 1) Guardar PDF original en storage/app/public/qa_drawings/pdf
            $pdfPath = $file->store('qa_drawings/pdf', 'public');
            $pdfFull = Storage::disk('public')->path($pdfPath);

            // 2) Definir ruta destino para PNG
            $pngName = pathinfo($pdfPath, PATHINFO_FILENAME) . '.png';
            $pngPath = 'qa_drawings/img/' . $pngName;

            // Crear carpeta si no existe
            Storage::disk('public')->makeDirectory('qa_drawings/img');

            // 3) Convertir PRIMERA página del PDF a PNG
            $this->convertPdfToPng($pdfFull, Storage::disk('public')->path($pngPath));

            $pathForDrawing = $pngPath;

        } else {
            // ===========================
            //  SI ES IMAGEN (PNG/JPG)
            // ===========================
            $pathForDrawing = $file->store('qa_drawings/img', 'public');
        }

        // ===========================
        //  OBTENER TAMAÑO DE IMAGEN
        // ===========================
        [$w, $h] = getimagesize(Storage::disk('public')->path($pathForDrawing));

        // ===========================
        //  GUARDAR EN BD
        // ===========================
        $drawing = QAInspDrawing::create([
            'customer'   => $data['customer'] ?? null,
            'pn'         => $data['pn'] ?? null,
            'rev'        => $data['rev'] ?? null,
            'file_path'  => $pathForDrawing, // siempre ruta de IMAGEN
            'img_width'  => $w,
            'img_height' => $h,
        ]);

        Log::info('Plano creado OK', ['id' => $drawing->id, 'file_path' => $pathForDrawing]);

        return redirect()
            ->route('qa.drawings.show', ['drawing' => $drawing->id])
            ->with('success', 'Plano subido correctamente.');
    }

    /**
     * Convierte la primera página de un PDF a PNG usando Ghostscript (gswin64c).
     *
     * @param string $pdfFullPath Ruta completa del PDF (C:\...\archivo.pdf)
     * @param string $pngFullPath Ruta completa donde se guardará el PNG (C:\...\archivo.png)
     */
    protected function convertPdfToPng(string $pdfFullPath, string $pngFullPath): void
    {
        // 🔴 OJO: ajusta la ruta si tu versión de Ghostscript es distinta
        // Ejemplo típico: C:\Program Files\gs\gs10.06.0\bin\gswin64c.exe
        $gsPath = 'C:\\Program Files\\gs\\gs10.06.0\\bin\\gswin64c.exe';

        // Comando Ghostscript
        $cmd = sprintf(
            '"%s" -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r200 -sOutputFile=%s %s',
            $gsPath,
            escapeshellarg($pngFullPath),
            escapeshellarg($pdfFullPath)
        );

        Log::info('Ejecutando Ghostscript', ['cmd' => $cmd]);

        $output = [];
        $returnVar = 0;
        exec($cmd . ' 2>&1', $output, $returnVar);

        Log::info('Ghostscript output', [
            'return' => $returnVar,
            'output' => $output,
        ]);

        if ($returnVar !== 0) {
            throw new \RuntimeException('Ghostscript falló al convertir el PDF. Código: ' . $returnVar);
        }
    }

    // ===========================
    //  MOSTRAR PLANO + GLOBOS
    // ===========================
public function show(QAInspDrawing $drawing)
{
    $drawing->load('characteristics');

    // Construimos los datos de características para el panel derecho
    $charData = $drawing->characteristics->mapWithKeys(function ($c) {
        return [
            $c->id => [
                'id'          => $c->id,
                'char_no'     => $c->char_no,
                'reference_location'        => $c->reference_location,
                'characteristic_designator' => $c->characteristic_designator,
                'requirement'               => $c->requirement,
                'results'                   => $c->results,
                'tooling'                   => $c->tooling,
                'non_conformance_number'    => $c->non_conformance_number,
                'comments'                  => $c->comments,
                'x'                         => $c->x,
                'y'                         => $c->y,
            ],
        ];
    });

    return view('qa.inspectionplan.show', [
        'drawing'  => $drawing,
        'charData' => $charData,
    ]);
}

    // ===========================
    //  CREAR GLOBO
    // ===========================
    public function storeCharacteristic(Request $request, QAInspDrawing $drawing)
    {
        $v = $request->validate([
            'x' => 'required|numeric',
            'y' => 'required|numeric',
        ]);

        $next = (int)($drawing->characteristics()->max('char_no') ?? 0) + 1;

        $char = $drawing->characteristics()->create([
            'char_no' => $next,
            'x'       => $v['x'],
            'y'       => $v['y'],
        ]);

        return response()->json($char);
    }

    // ===========================
    //  ACTUALIZAR GLOBO
    // ===========================
    public function updateCharacteristic(Request $request, QAInspDrawing $drawing, QAInspCharacteristic $char)
    {
        if ($char->drawing_id !== $drawing->id) {
            abort(404);
        }

        $data = $request->validate([
            'x'                         => 'nullable|numeric',
            'y'                         => 'nullable|numeric',
            'reference_location'        => 'nullable|string',
            'characteristic_designator' => 'nullable|string',
            'requirement'               => 'nullable|string',
            'results'                   => 'nullable|string',
            'tooling'                   => 'nullable|string',
            'non_conformance_number'    => 'nullable|string',
            'comments'                  => 'nullable|string',
        ]);

        $char->update($data);

        return response()->json(['ok' => true]);
    }

    // ===========================
    //  ELIMINAR GLOBO
    // ===========================
    public function destroyCharacteristic(QAInspDrawing $drawing, QAInspCharacteristic $char)
    {
        if ($char->drawing_id !== $drawing->id) {
            abort(404);
        }

        $char->delete();

        return response()->json(['ok' => true]);
    }
}
