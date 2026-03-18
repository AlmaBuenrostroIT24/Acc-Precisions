<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DashboardDetailExport implements FromArray, WithHeadings, WithEvents, ShouldAutoSize
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = max(2, $sheet->getHighestRow());

                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValue('A1', $this->title);
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->setCellValue('A2', 'Exported from dashboard modal');

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getFont()->setName('Arial');
                $sheet->getStyle("A1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => '0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getStyle("A2")->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '475569']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                $headerRow = 3;
                $dataStart = 4;

                $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EDF1F6'],
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

                    for ($row = $dataStart; $row <= $lastRow; $row += 1) {
                        if (($row - $dataStart) % 2 === 1) {
                            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setRGB('F8FAFC');
                        }
                    }
                }

                $sheet->freezePane('A4');
            },
        ];
    }
}
