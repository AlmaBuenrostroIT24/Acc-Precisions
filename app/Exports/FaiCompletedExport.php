<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class FaiCompletedExport implements FromView
{
    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function view(): View
    {
        return view('qa.faisummary.excel_completed_table', [
            'rows' => $this->rows
        ]);
    }
}