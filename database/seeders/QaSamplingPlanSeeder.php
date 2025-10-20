<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QaSamplingPlan;

class QaSamplingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [2, 8, 1, 2, 5],
            [9, 25, 2, 4, 10],
            [26, 50, 3, 6, 15],
            [51, 90, 4, 8, 20],
            [91, 150, 5, 10, 30],
            [151, 280, 7, 15, 40],
            [281, 500, 10, 25, 50],
            [501, 1200, 16, 40, 80],
            [1201, 3200, 25, 63, 130],
            [3201, null, 2.5, 5, 10, true],
        ];

        foreach ($data as $row) {
            QaSamplingPlan::create([
                'min_qty' => $row[0],
                'max_qty' => $row[1],
                'normal_qty' => $row[2],
                'tightened_qty' => $row[3],
                'surface_qty' => $row[4],
                'is_percent' => $row[5] ?? false,
            ]);
        }
    }
}
