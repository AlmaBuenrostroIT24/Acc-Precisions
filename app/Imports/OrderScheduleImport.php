<?php

namespace App\Imports;

use App\Services\OrderScheduleImportService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrderScheduleImport implements ToModel, WithHeadingRow
{
    public OrderScheduleImportService $service;

    public function __construct(OrderScheduleImportService $service)
    {
        $this->service = $service;
    }

    public function model(array $row)
    {
        return $this->service->cleanAndPrepareRow($row);
    }
}
