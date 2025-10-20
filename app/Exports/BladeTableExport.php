<?php
// app/Exports/BladeTableExport.php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BladeTableExport implements FromView, WithStyles, WithColumnFormatting, ShouldAutoSize, WithDrawings
{
    public function __construct(public string $view, public array $data) {}

    public function view(): View
    {
        return view($this->view, $this->data);
    }

    /** Logo en A1 (asegúrate de la extensión real .png/.PNG) */
    public function drawings()
    {
        $path = public_path('vendor/adminlte/dist/img/accl.png'); // <- ajusta si es .PNG
        // Si quieres validar:
        // if (!file_exists($path)) { throw new \RuntimeException("Logo no encontrado: $path"); }

        $drawing = new Drawing();
        $drawing->setName('Company Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($path);
        $drawing->setHeight(55);
        $drawing->setCoordinates('A1');   // Logo en A1
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(4);
        return $drawing;
    }

    /** Formatos */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_YYYYMMDD, // DATE
            'G' => NumberFormat::FORMAT_NUMBER,        // WO QTY
            'H' => NumberFormat::FORMAT_NUMBER,        // SAMP.
            'L' => NumberFormat::FORMAT_PERCENTAGE_00, // PROG. (debe venir 0..1)
        ];
    }

    /** Estilos seguros */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = 'L';


        // Título (fila 1): B1:L1 fusionado (A1 lo ocupa el logo)
        $sheet->mergeCells('B1:' . $lastCol . '1');
        $sheet->setCellValue('B1', "FAI / IPI Completed Report\n" . now()->format('m-d-Y H:i'));
        $sheet->getStyle('B1')->getAlignment()->setWrapText(true); // permite salto de línea

        $sheet->getStyle('B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 20, 'color' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(50);

        // Encabezados en fila 2
        $sheet->getStyle('A2:' . $lastCol . '2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F81BD']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        // Bordes desde encabezado hasta última fila
        $sheet->getStyle('A2:' . $lastCol . $lastRow)->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Alineaciones de datos (desde fila 3)
        foreach (['B', 'C', 'D', 'F', 'I', 'J', 'K'] as $c) {
            $sheet->getStyle($c . '3:' . $c . $lastRow)->getAlignment()->setHorizontal('center');
        }
        foreach (['G', 'H', 'L'] as $c) {
            $sheet->getStyle($c . '3:' . $c . $lastRow)->getAlignment()->setHorizontal('right');
        }
        $sheet->getStyle('E3:E' . $lastRow)->getAlignment()->setWrapText(true);

        // Autofiltro + freeze pane
        $sheet->setAutoFilter('A2:' . $lastCol . '2');
        $sheet->freezePane('A3');

        return [];
    }
}
