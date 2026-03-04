<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DashboardKpiExport implements FromArray, WithHeadings
{
    public function __construct(
        private readonly int $year,
        private readonly array $rows,
        private readonly array $otdYtd,
        private readonly array $otdR12,
    ) {
    }

    public function headings(): array
    {
        $monthHeaders = [];
        foreach (range(1, 12) as $m) {
            $monthHeaders[] = (string) $m;
        }

        return array_merge(
            ['Type', 'Prcs.', 'Name'],
            $monthHeaders,
            ['YTD %', 'YTD Total', 'Rolling 12M %', 'Rolling 12M Total', 'Goal/Per Term', 'Trend / NC Doc Ref.'],
        );
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
                    $months[] = $pct !== null ? (number_format((float) $pct, 1) . '% (' . $total . ')') : '';
                } else {
                    $months[] = is_array($cell) ? '' : (string) ($cell ?? '');
                }
            }

            $ytdPct = $isOtd ? ($this->otdYtd['pct'] ?? null) : null;
            $ytdTotal = $isOtd ? (int) ($this->otdYtd['total'] ?? 0) : null;
            $r12Pct = $isOtd ? ($this->otdR12['pct'] ?? null) : null;
            $r12Total = $isOtd ? (int) ($this->otdR12['total'] ?? 0) : null;

            $out[] = array_merge(
                [
                    (string) ($row['type'] ?? ''),
                    (string) ($row['prcs'] ?? ''),
                    (string) ($row['name'] ?? ''),
                ],
                $months,
                [
                    $ytdPct !== null ? number_format((float) $ytdPct, 1) . '%' : '',
                    $ytdTotal !== null ? (string) $ytdTotal : '',
                    $r12Pct !== null ? number_format((float) $r12Pct, 1) . '%' : '',
                    $r12Total !== null ? (string) $r12Total : '',
                    (string) ($row['goal'] ?? ''),
                    (string) ($row['trend'] ?? ''),
                ],
            );
        }

        return $out;
    }
}

