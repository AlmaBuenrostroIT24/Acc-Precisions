<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Exports\BladeTableExport;

public function exportFai14(Request $request)
{
    $data = $this->getFaiSummaryData($request); // lo que ya tienes

    $export = new BladeTableExport(
        view: 'qa.faisummary.exports.fai_general_excel',
        data: $data,
        title: 'FAI / IPI Completed Report',
        columnFormats: [
            'A' => NumberFormat::FORMAT_DATE_DATETIME,
            'L' => NumberFormat::FORMAT_NUMBER, // Qty Insp.
        ],
        centerCols: ['B','C','D','E','F','G','J','K','M','N'],
        rightCols:  ['L'],
        wrapCols:   ['H','I'], // SB/IS y Observation
    );

    return Excel::download($export, 'FAI_Summary_' . now()->format('Ymd_His') . '.xlsx');
}