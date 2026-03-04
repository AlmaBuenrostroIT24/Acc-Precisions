<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Carbon\Carbon;

class DashboardKpiExport implements FromArray, WithHeadings, WithCustomStartCell, WithColumnWidths, WithEvents, WithDrawings, WithTitle
{
    public function __construct(
        private readonly int $year,
        private readonly array $rows,
        private readonly array $otdYtd,
        private readonly array $otdR12,
        private readonly Carbon $dashboardEndDate,
    ) {
    }

    public function title(): string
    {
        return 'KPIs ' . $this->year;
    }

    public function startCell(): string
    {
        // Reserve top rows for an ERP-like header block.
        return 'A7';
    }

    public function headings(): array
    {
        $yearShort = substr((string) $this->year, -2);

        return [
            [
                'Type',
                'Prcs.',
                'Name',
                "Q1 {$yearShort}", '', '',
                "Q2 {$yearShort}", '', '',
                "Q3 {$yearShort}", '', '',
                "Q4 {$yearShort}", '', '',
                'YTD',
                'Rolling 12M',
                'Goal/Per Term',
                'Trend / NC Doc Ref.',
            ],
            array_merge(['', '', ''], array_map('strval', range(1, 12)), ['', '', '', '']),
        ];
    }

    public function array(): array
    {
        $out = [];

        foreach ($this->rows as $row) {
            $isOtd = ($row['key'] ?? '') === 'customer_otd';
            $values = $row['values'] ?? [];

            $months = [];
            foreach (range(1, 12) as $m) {
                $cell = $values[$m] ?? null;
                if ($isOtd && is_array($cell)) {
                    $pct = $cell['pct'] ?? null;
                    $total = (int) ($cell['total'] ?? 0);
                    if ($pct === null && !$total) {
                        $months[] = '';
                    } else {
                        $months[] = ($pct !== null ? number_format((float) $pct, 1) . '%' : '')
                            . ($total ? "\n(" . $total . ')' : '');
                    }
                } else {
                    $months[] = is_array($cell) ? '' : (string) ($cell ?? '');
                }
            }

            $ytdPct = $isOtd ? ($this->otdYtd['pct'] ?? null) : null;
            $ytdTotal = $isOtd ? (int) ($this->otdYtd['total'] ?? 0) : null;
            $r12Pct = $isOtd ? ($this->otdR12['pct'] ?? null) : null;
            $r12Total = $isOtd ? (int) ($this->otdR12['total'] ?? 0) : null;

            $name = $this->wrapTwoLines((string) ($row['name'] ?? ''), 58, 58);

            $out[] = array_merge(
                [
                    (string) ($row['type'] ?? ''),
                    (string) ($row['prcs'] ?? ''),
                    $name,
                ],
                $months,
                [
                    $this->pctTotalCell($ytdPct, $ytdTotal),
                    $this->pctTotalCell($r12Pct, $r12Total),
                    (string) ($row['goal'] ?? ''),
                    (string) ($row['trend'] ?? ''),
                ],
            );
        }

        return $out;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // Type / labels
            'B' => 5,  // Prcs
            'C' => 44, // Name
            'D' => 7, 'E' => 7, 'F' => 7,
            'G' => 7, 'H' => 7, 'I' => 7,
            'J' => 7, 'K' => 7, 'L' => 7,
            'M' => 7, 'N' => 7, 'O' => 7,
            'P' => 11, // YTD
            'Q' => 12, // Rolling
            'R' => 13, // Goal
            'S' => 18, // Trend
        ];
    }

    public function drawings()
    {
        $logoPath = public_path(config('adminlte.logo_img', ''));
        if (!$logoPath || !is_file($logoPath)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($logoPath);
        // Sized to cover A1:B1 and A2:B2 (do not overlap the title in column C).
        $drawing->setResizeProportional(false);
        $drawing->setWidth(115);
        $drawing->setHeight(78);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(0);
        $drawing->setOffsetY(0);

        return [$drawing];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $workbook = $sheet->getParent();
                if ($workbook) {
                    $workbook->getDefaultStyle()->getFont()->setName('Arial');
                    $workbook->getDefaultStyle()->getFont()->setSize(12);
                }

                $startRow = 7;
                $headerRow1 = $startRow;
                $headerRow2 = $startRow + 1;
                $dataStartRow = $startRow + 2;
                $dataRowCount = count($this->rows);
                $lastRow = $dataRowCount ? ($dataStartRow + $dataRowCount - 1) : $dataStartRow;

                // Top header area
                // Title starts at column C (A reserved for logo).
                $sheet->mergeCells('C1:S1');
                $sheet->setCellValue('C1', 'Quality Objectives & Key Performance Indicators (KPIs)');
                $sheet->mergeCells('C2:S2');
                $sheet->setCellValue('C2', 'ACC Precision, Inc.');
                $sheet->mergeCells('A1:B2');

                $asOf = Carbon::parse($this->dashboardEndDate)->format('F/d/Y');

                $sheet->setCellValue('A3', 'Year');
                $sheet->setCellValue('B3', $this->year);
                $sheet->mergeCells('B3:I3');

                // Put "As of" around mid-sheet like the PDF sample.
                $sheet->setCellValue('J3', 'As of');
                $sheet->setCellValue('K3', $asOf);
                $sheet->mergeCells('K3:S3');

                $sheet->setCellValue('A4', 'Notes');
                $sheet->mergeCells('B4:S4');
                $sheet->setCellValue('B4', 'To achieve the Quality Policy, the following QOs and KPIs are set forth by ACC Precision, Inc. and measured/analyzed/evaluated; they may be updated as needed.');

                $sheet->mergeCells('A6:S6');
                $sheet->setCellValue('A6', 'REPORT');

                // Merge quarter bands and lock non-month headers over 2 rows
                $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
                $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");
                $sheet->mergeCells("C{$headerRow1}:C{$headerRow2}");
                $sheet->mergeCells("P{$headerRow1}:P{$headerRow2}");
                $sheet->mergeCells("Q{$headerRow1}:Q{$headerRow2}");
                $sheet->mergeCells("R{$headerRow1}:R{$headerRow2}");
                $sheet->mergeCells("S{$headerRow1}:S{$headerRow2}");

                $sheet->mergeCells("D{$headerRow1}:F{$headerRow1}");
                $sheet->mergeCells("G{$headerRow1}:I{$headerRow1}");
                $sheet->mergeCells("J{$headerRow1}:L{$headerRow1}");
                $sheet->mergeCells("M{$headerRow1}:O{$headerRow1}");

                // Freeze panes (keep headers + first 3 columns)
                $sheet->freezePane("D{$dataStartRow}");

                // Print setup (landscape)
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_LETTER)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.3)
                    ->setBottom(0.5)
                    ->setLeft(0.3);

                // Footer (ERP form code + page numbers)
                $sheet->getHeaderFooter()->setOddFooter('&LF-620-001 Rev. B LA Authorized&RPage &P of &N');

                // Styles
                $sheet->getStyle('C1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F3A66']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('C2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '475569']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(40);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(34);
                $sheet->getRowDimension(6)->setRowHeight(20);

                $sheet->getStyle('A3:S3')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9E2EF']]],
                ]);
                $sheet->getStyle('A4:S4')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9E2EF']]],
                ]);
                $sheet->getStyle('A3')->getFont()->setBold(true);
                $sheet->getStyle('J3')->getFont()->setBold(true);
                $sheet->getStyle('A4')->getFont()->setBold(true);

                // Meta block fills like the PDF: label cells slightly darker.
                $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EDF2F7');
                $sheet->getStyle('J3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EDF2F7');
                $sheet->getStyle('A4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EDF2F7');
                $sheet->getStyle('B3:I3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F7FAFC');
                $sheet->getStyle('K3:S3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F7FAFC');
                $sheet->getStyle('B4:S4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F7FAFC');

                $sheet->getStyle('A3:S3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A3:S4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('B4')->getAlignment()->setWrapText(true);
                $sheet->getStyle('B3:I3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('K3:S3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('A6')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '2B4F86']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Header rows
                $headerRange = "A{$headerRow1}:S{$headerRow2}";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EEF3F9']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D9E6']]],
                ]);

                // Month header (white like PDF)
                $sheet->getStyle("D{$headerRow2}:O{$headerRow2}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');

                $sheet->getRowDimension($headerRow1)->setRowHeight(22);
                $sheet->getRowDimension($headerRow2)->setRowHeight(18);

                // Table body range
                $tableRange = "A{$headerRow1}:S{$lastRow}";
                $sheet->getStyle($tableRange)->getAlignment()->setWrapText(true);

                // Body fills (ERP-ish)
                if ($dataRowCount) {
                    $sheet->getStyle("A{$dataStartRow}:B{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EDF2F7');
                    $sheet->getStyle("C{$dataStartRow}:C{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5FB');
                    $sheet->getStyle("D{$dataStartRow}:O{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                    $sheet->getStyle("P{$dataStartRow}:Q{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F7FAFC');
                    $sheet->getStyle("R{$dataStartRow}:R{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF7D6');
                }

                // Alignments
                $sheet->getStyle("C{$dataStartRow}:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A{$dataStartRow}:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$dataStartRow}:S{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$dataStartRow}:C{$lastRow}")->getFont()->setBold(true);

                // Borders for the whole table
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D1D9E6');

                // Thicker separators after each quarter (F, I, L, O)
                foreach (['F', 'I', 'L', 'O'] as $col) {
                    $sheet->getStyle("{$col}{$headerRow1}:{$col}{$lastRow}")
                        ->getBorders()
                        ->getRight()
                        ->setBorderStyle(Border::BORDER_MEDIUM)
                        ->getColor()
                        ->setRGB('94A3B8');
                }

                // Row heights for consistent look
                for ($r = $dataStartRow; $r <= $lastRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(40);
                }
            },
        ];
    }

    private function pctTotalCell($pct, $total): string
    {
        $pctText = $pct !== null ? number_format((float) $pct, 1) . '%' : '';
        $totalNum = (int) ($total ?? 0);

        if ($pctText === '' && !$totalNum) {
            return '';
        }

        return $pctText . ($totalNum ? "\n(" . $totalNum . ')' : '');
    }

    private function wrapTwoLines(string $text, int $max1 = 58, int $max2 = 58): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return '';
        }

        $words = preg_split('/\s+/u', $text) ?: [];
        $line1 = '';
        $line2 = '';

        foreach ($words as $word) {
            $candidate = trim(($line1 !== '' ? $line1 . ' ' : '') . $word);
            if (mb_strlen($candidate, 'UTF-8') <= $max1) {
                $line1 = $candidate;
                continue;
            }

            if ($line1 === '') {
                $line1 = mb_substr($candidate, 0, $max1, 'UTF-8');
            }

            $candidate2 = trim(($line2 !== '' ? $line2 . ' ' : '') . $word);
            if (mb_strlen($candidate2, 'UTF-8') <= $max2) {
                $line2 = $candidate2;
                continue;
            }

            $line2 = mb_substr($candidate2, 0, max(0, $max2 - 1), 'UTF-8') . '…';
            break;
        }

        return $line2 === '' ? $line1 : ($line1 . "\n" . $line2);
    }
}
