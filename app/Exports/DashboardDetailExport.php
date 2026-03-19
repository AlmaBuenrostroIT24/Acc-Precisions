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
        private readonly ?string $summaryTitle = null,
        private readonly array $summaryHeadings = [],
        private readonly array $summaryRows = [],
        private readonly ?string $summaryMode = null,
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
        return 'A' . $this->tableHeaderRow();
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
        $drawing->setHeight(78);
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
                $hasSummary = $this->hasMonthlySummary();
                $headerRow = $this->tableHeaderRow();
                $dataStart = $headerRow + 1;
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = max($headerRow, $sheet->getHighestRow());
                $statusIndex = array_search('Status', $this->headings, true);
                $statusColumn = $statusIndex !== false
                    ? Coordinate::stringFromColumnIndex($statusIndex + 1)
                    : null;
                $helperMonthIndex = array_search('Period Month', $this->headings, true);
                $helperMonthColumn = $helperMonthIndex !== false
                    ? Coordinate::stringFromColumnIndex($helperMonthIndex + 1)
                    : null;
                $helperTotalIndex = array_search('Period Total', $this->headings, true);
                $helperTotalColumn = $helperTotalIndex !== false
                    ? Coordinate::stringFromColumnIndex($helperTotalIndex + 1)
                    : null;
                $helperFailOpsIndex = array_search('Fail Ops Total', $this->headings, true);
                $helperFailOpsColumn = $helperFailOpsIndex !== false
                    ? Coordinate::stringFromColumnIndex($helperFailOpsIndex + 1)
                    : null;

                $sheet->mergeCells('A1:B4');
                $sheet->mergeCells("C1:{$lastColumn}1");
                $sheet->setCellValue('C1', 'ACC Precision, Inc.');
                $sheet->mergeCells("C2:{$lastColumn}2");
                $sheet->setCellValue('C2', $this->title);
                $sheet->mergeCells("C3:{$lastColumn}3");
                $sheet->setCellValue('C3', 'Dashboard Detail Export');
                $sheet->mergeCells("C4:{$lastColumn}4");
                $sheet->setCellValue('C4', 'Generated ' . now()->format('M/d/Y H:i'));

                if ($statusColumn) {
                    $statusTitleRange = 'G5:I5';
                    $statusDataRange = 'G6:I8';

                    $sheet->setCellValue('G5', 'Status Summary');
                    $sheet->mergeCells($statusTitleRange);

                    $sheet->setCellValue('G6', 'On Time');
                    $sheet->setCellValue('H6', '=COUNTIF($' . $statusColumn . '$' . $dataStart . ':$' . $statusColumn . '$' . $lastRow . ',"On Time")');
                    $sheet->setCellValue('I6', '=IF(COUNTA($' . $statusColumn . '$' . $dataStart . ':$' . $statusColumn . '$' . $lastRow . ')=0,0,H6/COUNTA($' . $statusColumn . '$' . $dataStart . ':$' . $statusColumn . '$' . $lastRow . '))');

                    $sheet->setCellValue('G7', 'Late');
                    $sheet->setCellValue('H7', '=COUNTIF($' . $statusColumn . '$' . $dataStart . ':$' . $statusColumn . '$' . $lastRow . ',"Late")');
                    $sheet->setCellValue('I7', '=IF(COUNTA($' . $statusColumn . '$' . $dataStart . ':$' . $statusColumn . '$' . $lastRow . ')=0,0,H7/COUNTA($' . $statusColumn . '$' . $dataStart . ':$' . $statusColumn . '$' . $lastRow . '))');

                    $sheet->setCellValue('G8', 'Total');
                    $sheet->setCellValue('H8', '=COUNTA($' . $statusColumn . '$' . $dataStart . ':$' . $statusColumn . '$' . $lastRow . ')');
                    $sheet->setCellValue('I8', '=IF(H8=0,0,H8/H8)');

                    $sheet->getStyle($statusTitleRange)->applyFromArray([
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

                    $sheet->getStyle($statusDataRange)->applyFromArray([
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

                    $sheet->getStyle('I6:I8')
                        ->getNumberFormat()
                        ->setFormatCode('0.0%');

                    $sheet->getStyle('G6:I6')->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'DCFCE7'],
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '166534'],
                        ],
                    ]);

                    $sheet->getStyle('G7:I7')->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEE2E2'],
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '991B1B'],
                        ],
                    ]);

                    $sheet->getStyle('G8:I8')->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E5E7EB'],
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '334155'],
                        ],
                    ]);
                }

                if ($hasSummary) {
                    $summaryStartRow = 5;
                    $summaryHeaderRow = $summaryStartRow + 1;
                    $summaryStartColumnIndex = 1;
                    $summaryStartColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex);
                    $summaryLastColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + max(1, count($this->summaryHeadings)) - 1);
                    $summaryEndRow = $summaryHeaderRow + count($this->summaryRows);
                    $summaryFirstDataRow = $summaryHeaderRow + 1;

                    $sheet->setCellValue("{$summaryStartColumn}{$summaryStartRow}", $this->summaryTitle);
                    $sheet->mergeCells("{$summaryStartColumn}{$summaryStartRow}:{$summaryLastColumn}{$summaryStartRow}");

                    foreach ($this->summaryHeadings as $index => $heading) {
                        $column = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + $index);
                        $sheet->setCellValue("{$column}{$summaryHeaderRow}", $heading);
                    }

                    foreach ($this->summaryRows as $rowIndex => $rowValues) {
                        $excelRow = $summaryHeaderRow + 1 + $rowIndex;
                        foreach (array_values($rowValues) as $cellIndex => $value) {
                            $column = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + $cellIndex);
                            $sheet->setCellValue("{$column}{$excelRow}", $value);
                        }
                    }

                    $summaryTotalRow = $summaryEndRow + 1;
                    $sheet->setCellValue("{$summaryStartColumn}{$summaryTotalRow}", 'Total');

                    $sheet->getStyle("{$summaryStartColumn}{$summaryStartRow}:{$summaryLastColumn}{$summaryStartRow}")->applyFromArray([
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
                    $sheet->getStyle("{$summaryStartColumn}{$summaryHeaderRow}:{$summaryLastColumn}{$summaryHeaderRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'D5D8DD'],
                            ],
                        ],
                    ]);

                    if ($summaryEndRow > $summaryHeaderRow) {
                        $sheet->getStyle("{$summaryStartColumn}" . ($summaryHeaderRow + 1) . ":{$summaryLastColumn}{$summaryTotalRow}")->applyFromArray([
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

                        if ($this->summaryMode === 'otd') {
                            $onTimePctColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 2);
                            $latePctColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 4);
                            $sheet->getStyle("{$onTimePctColumn}{$summaryFirstDataRow}:{$onTimePctColumn}{$summaryEndRow}")
                                ->getNumberFormat()
                                ->setFormatCode('0.0%');
                            $sheet->getStyle("{$latePctColumn}{$summaryFirstDataRow}:{$latePctColumn}{$summaryEndRow}")
                                ->getNumberFormat()
                                ->setFormatCode('0.0%');
                            $sheet->getStyle("{$onTimePctColumn}{$summaryTotalRow}")
                                ->getNumberFormat()
                                ->setFormatCode('0.0%');
                            $sheet->getStyle("{$latePctColumn}{$summaryTotalRow}")
                                ->getNumberFormat()
                                ->setFormatCode('0.0%');
                        } else {
                            $percentageColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + count($this->summaryHeadings) - 1);
                            $sheet->getStyle("{$percentageColumn}{$summaryFirstDataRow}:{$percentageColumn}{$summaryEndRow}")
                                ->getNumberFormat()
                                ->setFormatCode('0.0%');
                            $sheet->getStyle("{$percentageColumn}{$summaryTotalRow}")
                                ->getNumberFormat()
                                ->setFormatCode('0.0%');
                        }

                        if ($helperMonthColumn) {
                            for ($row = $summaryHeaderRow + 1; $row <= $summaryEndRow; $row += 1) {
                                $monthCell = "{$summaryStartColumn}{$row}";
                                if ($this->summaryMode === 'otd' && $statusColumn) {
                                    $onTimeCountCell = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 1) . $row;
                                    $onTimePctCell = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 2) . $row;
                                    $lateCountCell = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 3) . $row;
                                    $latePctCell = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 4) . $row;
                                    $sheet->setCellValue(
                                        $onTimeCountCell,
                                        '=SUMPRODUCT(($' . $helperMonthColumn . '$' . $dataStart . ':$' . $helperMonthColumn . '$' . $lastRow . '=' . $monthCell . ')*($' . $statusColumn . '$' . $dataStart . ':$' . $statusColumn . '$' . $lastRow . '="On Time"))'
                                    );
                                    $sheet->setCellValue(
                                        $lateCountCell,
                                        '=SUMPRODUCT(($' . $helperMonthColumn . '$' . $dataStart . ':$' . $helperMonthColumn . '$' . $lastRow . '=' . $monthCell . ')*($' . $statusColumn . '$' . $dataStart . ':$' . $statusColumn . '$' . $lastRow . '="Late"))'
                                    );
                                    $sheet->setCellValue($onTimePctCell, '=IF((' . $onTimeCountCell . '+' . $lateCountCell . ')=0,0,' . $onTimeCountCell . '/(' . $onTimeCountCell . '+' . $lateCountCell . '))');
                                    $sheet->setCellValue($latePctCell, '=IF((' . $onTimeCountCell . '+' . $lateCountCell . ')=0,0,' . $lateCountCell . '/(' . $onTimeCountCell . '+' . $lateCountCell . '))');
                                } elseif ($this->summaryMode === 'fai') {
                                    $rejectsCell = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 1) . $row;
                                    $totalCell = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 2) . $row;
                                    $percentageCell = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 3) . $row;
                                    if ($helperFailOpsColumn) {
                                        $sheet->setCellValue(
                                            $rejectsCell,
                                            '=SUMPRODUCT(($' . $helperMonthColumn . '$' . $dataStart . ':$' . $helperMonthColumn . '$' . $lastRow . '=' . $monthCell . ')*($' . $helperFailOpsColumn . '$' . $dataStart . ':$' . $helperFailOpsColumn . '$' . $lastRow . '))'
                                        );
                                    } else {
                                        $sheet->setCellValue(
                                            $rejectsCell,
                                            '=COUNTIF($' . $helperMonthColumn . '$' . $dataStart . ':$' . $helperMonthColumn . '$' . $lastRow . ',' . $monthCell . ')'
                                        );
                                    }
                                    if ($helperTotalColumn) {
                                        $sheet->setCellValue(
                                            $totalCell,
                                            '=IFERROR(INDEX($' . $helperTotalColumn . '$' . $dataStart . ':$' . $helperTotalColumn . '$' . $lastRow . ',MATCH(' . $monthCell . ',$' . $helperMonthColumn . '$' . $dataStart . ':$' . $helperMonthColumn . '$' . $lastRow . ',0)),0)'
                                        );
                                    }
                                    $sheet->setCellValue($percentageCell, '=IF(' . $totalCell . '=0,0,' . $rejectsCell . '/' . $totalCell . ')');
                                }
                            }
                        }

                        if ($this->summaryMode === 'otd') {
                            $onTimeCountColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 1);
                            $onTimePctColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 2);
                            $lateCountColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 3);
                            $latePctColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 4);
                            $sheet->setCellValue("{$onTimeCountColumn}{$summaryTotalRow}", "=SUM({$onTimeCountColumn}{$summaryFirstDataRow}:{$onTimeCountColumn}{$summaryEndRow})");
                            $sheet->setCellValue("{$lateCountColumn}{$summaryTotalRow}", "=SUM({$lateCountColumn}{$summaryFirstDataRow}:{$lateCountColumn}{$summaryEndRow})");
                            $sheet->setCellValue("{$onTimePctColumn}{$summaryTotalRow}", "=IF(({$onTimeCountColumn}{$summaryTotalRow}+{$lateCountColumn}{$summaryTotalRow})=0,0,{$onTimeCountColumn}{$summaryTotalRow}/({$onTimeCountColumn}{$summaryTotalRow}+{$lateCountColumn}{$summaryTotalRow}))");
                            $sheet->setCellValue("{$latePctColumn}{$summaryTotalRow}", "=IF(({$onTimeCountColumn}{$summaryTotalRow}+{$lateCountColumn}{$summaryTotalRow})=0,0,{$lateCountColumn}{$summaryTotalRow}/({$onTimeCountColumn}{$summaryTotalRow}+{$lateCountColumn}{$summaryTotalRow}))");
                        } else {
                            $rejectsColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 1);
                            $totalColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 2);
                            $percentageColumn = Coordinate::stringFromColumnIndex($summaryStartColumnIndex + 3);
                            $sheet->setCellValue("{$rejectsColumn}{$summaryTotalRow}", "=SUM({$rejectsColumn}{$summaryFirstDataRow}:{$rejectsColumn}{$summaryEndRow})");
                            $sheet->setCellValue("{$totalColumn}{$summaryTotalRow}", "=SUM({$totalColumn}{$summaryFirstDataRow}:{$totalColumn}{$summaryEndRow})");
                            $sheet->setCellValue("{$percentageColumn}{$summaryTotalRow}", "=IF({$totalColumn}{$summaryTotalRow}=0,0,{$rejectsColumn}{$summaryTotalRow}/{$totalColumn}{$summaryTotalRow})");
                        }

                        $sheet->getStyle("{$summaryStartColumn}{$summaryTotalRow}:{$summaryLastColumn}{$summaryTotalRow}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'E5E7EB'],
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '334155'],
                            ],
                        ]);
                    }
                }

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

                $sheet->getStyle("A1:{$lastColumn}4")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(18);
                $sheet->getRowDimension(5)->setRowHeight(18);

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

                if ($helperMonthColumn) {
                    $sheet->getColumnDimension($helperMonthColumn)->setVisible(false);
                }
                if ($helperTotalColumn) {
                    $sheet->getColumnDimension($helperTotalColumn)->setVisible(false);
                }
                if ($helperFailOpsColumn) {
                    $sheet->getColumnDimension($helperFailOpsColumn)->setVisible(false);
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
                $sheet->freezePane('A' . $dataStart);
            },
        ];
    }

    private function hasStatusColumn(): bool
    {
        return in_array('Status', $this->headings, true);
    }

    private function hasMonthlySummary(): bool
    {
        return $this->summaryTitle !== null && $this->summaryHeadings !== [] && $this->summaryRows !== [];
    }

    private function tableHeaderRow(): int
    {
        $lastReservedRow = 5;

        if ($this->hasStatusColumn()) {
            $lastReservedRow = max($lastReservedRow, 8);
        }

        if ($this->hasMonthlySummary()) {
            $lastReservedRow = max($lastReservedRow, 7 + count($this->summaryRows) + 1);
        }

        return $lastReservedRow + 1;
    }
}
