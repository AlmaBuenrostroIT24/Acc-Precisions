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
    public function __construct(
        public string $view,
        public array $data,
        public string $title = 'Report',
        // formatos por columna (A,B,C...)
        public array $columnFormats = [],
        // estilos por columnas
        public array $centerCols = [],
        public array $rightCols  = [],
        public array $wrapCols   = [],
    ) {}

    public function view(): View
    {
        return view($this->view, $this->data);
    }

    /** Logo en A1 */
    public function drawings()
    {
        $path = public_path('vendor/adminlte/dist/img/accl.png'); // <- ajusta si es .PNG

        $drawing = new Drawing();
        $drawing->setName('Company Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($path);
        $drawing->setHeight(55);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(4);

        return $drawing;
    }

    /** Formatos de columnas (dinámico por constructor) */
    public function columnFormats(): array
    {
        return $this->columnFormats;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();      // última fila con datos
        $lastCol = $sheet->getHighestColumn();   // última columna real (A..L / A..N / A..P etc.)

        // ============== TÍTULO (fila 1) ==============
        $sheet->mergeCells('B1:' . $lastCol . '1');
        $sheet->setCellValue(
            'B1',
            $this->title . "\n" . now()->format('m-d-Y H:i')
        );
        $sheet->getStyle('B1')->getAlignment()->setWrapText(true);

        $sheet->getStyle('B1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 20,
                'color' => ['rgb' => '1F4E78'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical'   => 'center',
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(50);

        // ============== ENCABEZADOS (fila 2) ==============
        // OJO: la vista Blade debe tener la fila dummy primero.
        $sheet->getStyle('A2:' . $lastCol . '2')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => '4F81BD'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical'   => 'center',
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        // ============== BORDES ==============
        $sheet->getStyle('A2:' . $lastCol . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // ============== ALINEACIONES (desde fila 3) ==============
        foreach ($this->centerCols as $c) {
            $sheet->getStyle($c . '3:' . $c . $lastRow)
                ->getAlignment()
                ->setHorizontal('center');
        }

        foreach ($this->rightCols as $c) {
            $sheet->getStyle($c . '3:' . $c . $lastRow)
                ->getAlignment()
                ->setHorizontal('right');
        }

        foreach ($this->wrapCols as $c) {
            $sheet->getStyle($c . '3:' . $c . $lastRow)
                ->getAlignment()
                ->setWrapText(true);
        }

        // ============== AUTOFILTRO + FREEZE ==============
        $sheet->setAutoFilter('A2:' . $lastCol . '2');
        $sheet->freezePane('A3');

        return [];
    }
}
