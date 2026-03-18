<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class DashboardDetailExport implements FromArray, WithHeadings, WithEvents, ShouldAutoSize, WithDrawings, WithCustomStartCell
{
    public function __construct(
        private readonly string $title,
        private readonly array $headings,
        private readonly array $rows,
    ) {
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function startCell(): string
    {
        return 'A9';
    }

    public function drawings()
    {
        $logoPath = public_path(config('adminlte.logo_img', ''));
        if (!$logoPath || !is_file($logoPath)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('ACC Precision Logo');
        $drawing->setPath($logoPath);
        $drawing->setResizeProportional(false);
        $drawing->setWidth(118);
        $drawing->setHeight(98);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(4);
        $drawing->setOffsetY(2);

        return [$drawing];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = max(9, $sheet->getHighestRow());
                $statusIndex = array_search('Status', $this->headings, true);
                $statusColumn = $statusIndex !== false
                    ? Coordinate::stringFromColumnIndex($statusIndex + 1)
                    : null;

                $sheet->mergeCells('A1:B5');
                $sheet->mergeCells("C1:{$lastColumn}1");
                $sheet->setCellValue('C1', 'ACC Precision, Inc.');
                $sheet->mergeCells("C2:{$lastColumn}2");
                $sheet->setCellValue('C2', $this->title);
                $sheet->mergeCells("C3:{$lastColumn}3");
                $sheet->setCellValue('C3', 'Dashboard Detail Export');
                $sheet->mergeCells("C4:{$lastColumn}4");
                $sheet->setCellValue('C4', 'Generated ' . now()->format('M/d/Y H:i'));
                $sheet->mergeCells("C5:{$lastColumn}5");
                $sheet->setCellValue('C5', '');

                $sheet->setCellValue('A6', 'Status Summary');
                $sheet->mergeCells('A6:D6');
                $sheet->setCellValue('A7', 'On Time');
                $sheet->setCellValue('B7', $statusColumn ? '=COUNTIF(' . $statusColumn . '10:' . $statusColumn . $lastRow . ',"On Time")' : 0);
                $sheet->setCellValue('C7', $statusColumn ? '=IF(COUNTA(' . $statusColumn . '10:' . $statusColumn . $lastRow . ')=0,0,B7/COUNTA(' . $statusColumn . '10:' . $statusColumn . $lastRow . '))' : 0);
                $sheet->setCellValue('A8', 'Late');
                $sheet->setCellValue('B8', $statusColumn ? '=COUNTIF(' . $statusColumn . '10:' . $statusColumn . $lastRow . ',"Late")' : 0);
                $sheet->setCellValue('C8', $statusColumn ? '=IF(COUNTA(' . $statusColumn . '10:' . $statusColumn . $lastRow . ')=0,0,B8/COUNTA(' . $statusColumn . '10:' . $statusColumn . $lastRow . '))' : 0);

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getFont()->setName('Arial');
                $sheet->getStyle('C1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F3A66']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getStyle('C2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getStyle('C3:C4')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '475569']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                $sheet->getStyle("A1:{$lastColumn}5")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                ]);

                $sheet->getStyle('A6:D6')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EDF1F6'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D5D8DD'],
                        ],
                    ],
                ]);
                $sheet->getStyle('A7:C8')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D5D8DD'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('A7:A8')->getFont()->setBold(true);
                $sheet->getStyle('C7:C8')->getNumberFormat()->setFormatCode('0.0%');
                $sheet->getStyle('A7:C7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                $sheet->getStyle('A8:C8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(18);
                $sheet->getRowDimension(5)->setRowHeight(16);

                $headerRow = 9;
                $dataStart = 10;

                $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EDF1F6'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D5D8DD'],
                        ],
                    ],
                ]);

                if ($lastRow >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$lastColumn}{$lastRow}")->applyFromArray([
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'D5D8DD'],
                            ],
                        ],
                    ]);

                    $pnIndex = array_search('PN', $this->headings, true);
                    if ($pnIndex !== false) {
                        $pnColumn = Coordinate::stringFromColumnIndex($pnIndex + 1);
                        $sheet->getStyle("{$pnColumn}{$dataStart}:{$pnColumn}{$lastRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }

                    $custPoIndex = array_search('Cust PO', $this->headings, true);
                    if ($custPoIndex !== false) {
                        $custPoColumn = Coordinate::stringFromColumnIndex($custPoIndex + 1);
                        $sheet->getStyle("{$custPoColumn}{$dataStart}:{$custPoColumn}{$lastRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }

                    for ($row = $dataStart; $row <= $lastRow; $row += 1) {
                        if (($row - $dataStart) % 2 === 1) {
                            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setRGB('F8FAFC');
                        }
                    }

                    if ($statusColumn) {
                        for ($row = $dataStart; $row <= $lastRow; $row += 1) {
                            $statusCell = "{$statusColumn}{$row}";
                            $statusValue = strtolower(trim((string) $sheet->getCell($statusCell)->getValue()));
                            if ($statusValue === 'on time') {
                                $sheet->getStyle($statusCell)->applyFromArray([
                                    'font' => ['bold' => true, 'color' => ['rgb' => '166534']],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'startColor' => ['rgb' => 'DCFCE7'],
                                    ],
                                ]);
                            } elseif ($statusValue === 'late') {
                                $sheet->getStyle($statusCell)->applyFromArray([
                                    'font' => ['bold' => true, 'color' => ['rgb' => '991B1B']],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'startColor' => ['rgb' => 'FEE2E2'],
                                    ],
                                ]);
                            }
                        }
                    }
                }

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_LETTER)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getPageMargins()
                    ->setTop(0.35)
                    ->setRight(0.25)
                    ->setBottom(0.35)
                    ->setLeft(0.25);
                $sheet->getHeaderFooter()->setOddFooter('&LACC Precision, Inc.&RPage &P of &N');
                $sheet->getPageSetup()->setPrintArea("A1:{$lastColumn}{$lastRow}");
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($headerRow, $headerRow);
                $sheet->getSheetView()->setView('pageLayout');
                $sheet->freezePane('A10');
            },
        ];
    }
}
