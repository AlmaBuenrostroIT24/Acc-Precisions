<?php

namespace App\Http\Controllers;

use App\Exports\DashboardDetailExport;
use App\Exports\DashboardKpiExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $availableYears = $this->getDashboardYears();
        $year = $this->normalizeYear((int) $request->query('year', now()->year), $availableYears);

        return view('dashboard', $this->buildDashboardPayload($year, $availableYears));
    }

    public function otdDetails(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month');
        $quarter = (int) $request->query('quarter');

        if ($year < 2000 || $year > 3000) {
            return response()->json(['html' => '', 'count' => 0, 'message' => 'Invalid year'], 422);
        }
        if ($quarter < 1 || $quarter > 4) {
            $quarter = 0;
        }
        if ($quarter === 0 && ($month < 1 || $month > 12)) {
            return response()->json(['html' => '', 'count' => 0, 'message' => 'Invalid month or quarter'], 422);
        }

        $filter = strtolower(trim((string) $request->query('filter', 'all')));
        if (!in_array($filter, ['all', 'ontime', 'late'], true)) {
            $filter = 'all';
        }

        $rows = $this->buildOtdDetailsQuery($year, $month, $filter, $quarter)
            ->orderBy('due_date', 'asc')
            ->orderBy('sent_at', 'desc')
            ->limit(2000)
            ->get();

        $html = view('dashboard.partials.otd_details_rows', [
            'rows' => $rows,
        ])->render();

        return response()->json([
            'html' => $html,
            'count' => $rows->count(),
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter ?: null,
            'filter' => $filter,
        ]);
    }

    public function exportOtdDetailsExcel(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month');
        $quarter = (int) $request->query('quarter');
        $filter = strtolower(trim((string) $request->query('filter', 'all')));
        $search = trim((string) $request->query('search', ''));

        if (!in_array($filter, ['all', 'ontime', 'late'], true)) {
            $filter = 'all';
        }

        $items = $this->buildOtdDetailsQuery($year, $month, $filter, $quarter)
            ->orderBy('due_date', 'asc')
            ->orderBy('sent_at', 'desc')
            ->limit(5000)
            ->get();
        $items = $this->filterDashboardDetailRows($items, $search, function ($row) {
            $meta = $this->buildOtdRowMeta($row);
            return implode(' ', [
                $row->work_id,
                $row->PN,
                $row->cust_po,
                $row->co,
                $row->Part_description,
                $row->costumer,
                $this->formatDashboardDate($row->due_date),
                $this->formatDashboardDate($row->sent_at),
                $meta['days'],
                $meta['status'],
            ]);
        });

        $title = 'OTD Details - ' . ($quarter ? ('Q' . $quarter . ' ' . $year) : ($this->monthNameEn($month) . ' ' . $year)) . ' - ' . strtoupper($filter);
        $exportRows = [];
        foreach ($items->values() as $index => $row) {
            $meta = $this->buildOtdRowMeta($row);
            $exportRows[] = [
                $index + 1,
                (string) ($row->work_id ?? ''),
                (string) ($row->PN ?? ''),
                (string) ($row->cust_po ?? ''),
                (string) ($row->co ?? ''),
                (string) ($row->Part_description ?? ''),
                (string) ($row->costumer ?? ''),
                $this->formatDashboardDate($row->due_date),
                $this->formatDashboardDate($row->sent_at),
                $meta['days'],
                $meta['status'],
            ];
        }

        return Excel::download(
            new DashboardDetailExport(
                $title,
                ['#', 'Work ID', 'PN', 'Cust PO', 'CO', 'Part/Description', 'Customer', 'Due', 'Sent', 'Days', 'Status'],
                $exportRows,
            ),
            'otd-details-' . $year . '-' . ($quarter ? ('q' . $quarter) : str_pad((string) $month, 2, '0', STR_PAD_LEFT)) . '.xlsx',
        );
    }

    public function faiRejDetails(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month');
        $quarter = (int) $request->query('quarter');

        if ($year < 2000 || $year > 3000) {
            return response()->json(['html' => '', 'count' => 0, 'message' => 'Invalid year'], 422);
        }
        if ($quarter < 1 || $quarter > 4) {
            $quarter = 0;
        }
        if ($quarter === 0 && ($month < 1 || $month > 12)) {
            return response()->json(['html' => '', 'count' => 0, 'message' => 'Invalid month or quarter'], 422);
        }

        $base = $this->buildFaiRejBaseQuery($year, $month, $quarter);

        $total = (int) (clone $base)->count('qfs.id');

        $rows = $this->buildFaiRejRowsQuery($year, $month, $quarter)
            ->orderBy('orders_schedule.due_date', 'asc')
            ->orderBy('fai_date', 'desc')
            ->limit(2000)
            ->get();

        $rejects = (int) (clone $base)
            ->whereRaw("LOWER(TRIM(qfs.results)) IN ('no pass','nopass','no_pass','fail','np')")
            ->count('qfs.id');
        $pct = $total > 0 ? round(($rejects / $total) * 100, 1) : null;

        $html = view('dashboard.partials.fai_rej_details_rows', [
            'rows' => $rows,
        ])->render();

        return response()->json([
            'html' => $html,
            'count' => $rows->count(),
            'total' => $total,
            'rejects' => $rejects,
            'pct' => $pct,
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter ?: null,
        ]);
    }

    public function exportFaiRejDetailsExcel(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month');
        $quarter = (int) $request->query('quarter');
        $search = trim((string) $request->query('search', ''));

        $items = $this->buildFaiRejRowsQuery($year, $month, $quarter)
            ->orderBy('orders_schedule.due_date', 'asc')
            ->orderBy('fai_date', 'desc')
            ->limit(5000)
            ->get();
        $items = $this->filterDashboardDetailRows($items, $search, function ($row) {
            return implode(' ', [
                $row->work_id,
                $row->PN,
                $row->cust_po,
                $row->co,
                $row->Part_description,
                $row->costumer,
                $this->formatDashboardDate($row->due_date),
                $this->formatDashboardDate($row->sent_at),
                $row->fail_ops,
                $row->fail_operations,
            ]);
        });

        $title = 'Internal FAI Rejection Details - ' . ($quarter ? ('Q' . $quarter . ' ' . $year) : ($this->monthNameEn($month) . ' ' . $year));
        $exportRows = [];
        foreach ($items->values() as $index => $row) {
            $failOps = (int) ($row->fail_ops ?? 0);
            $ops = trim((string) ($row->fail_operations ?? ''));
            $exportRows[] = [
                $index + 1,
                (string) ($row->work_id ?? ''),
                (string) ($row->PN ?? ''),
                (string) ($row->cust_po ?? ''),
                (string) ($row->co ?? ''),
                (string) ($row->Part_description ?? ''),
                (string) ($row->costumer ?? ''),
                $this->formatDashboardDate($row->due_date),
                $this->formatDashboardDate($row->sent_at),
                $failOps > 0 ? trim($failOps . ($ops !== '' ? ' Op: ' . $ops : '')) : '-',
            ];
        }

        return Excel::download(
            new DashboardDetailExport(
                $title,
                ['#', 'Work ID', 'PN', 'Cust PO', 'CO', 'Part/Description', 'Customer', 'Due', 'Sent', 'Fail Ops'],
                $exportRows,
            ),
            'internal-fai-rejection-details-' . $year . '-' . ($quarter ? ('q' . $quarter) : str_pad((string) $month, 2, '0', STR_PAD_LEFT)) . '.xlsx',
        );
    }

    public function exportPdf(Request $request)
    {
        $availableYears = $this->getDashboardYears();
        $year = $this->normalizeYear((int) $request->query('year', now()->year), $availableYears);
        $payload = $this->buildDashboardPayload($year, $availableYears);

        $pdf = Pdf::loadView('dashboard.export_pdf', $payload)
            ->setPaper('letter', 'landscape');

        return $pdf->stream('dashboard-kpis-' . $year . '.pdf', ['Attachment' => false]);
    }

    public function exportExcel(Request $request)
    {
        $availableYears = $this->getDashboardYears();
        $year = $this->normalizeYear((int) $request->query('year', now()->year), $availableYears);
        $payload = $this->buildDashboardPayload($year, $availableYears);

        return Excel::download(
            new DashboardKpiExport(
                $year,
                $payload['kpiRows'],
                $payload['otdYtd'],
                $payload['otdR12'],
                $payload['faiRejYtd'],
                $payload['faiRejR12'],
                $payload['dashboardEndDate'],
            ),
            'dashboard-kpis-' . $year . '.xlsx',
        );
    }

    private function normalizeYear(int $year, array $availableYears = []): int
    {
        if ($year < 2000 || $year > 3000) {
            return !empty($availableYears) ? (int) $availableYears[0] : (int) now()->year;
        }

        if (!empty($availableYears) && !in_array($year, $availableYears, true)) {
            return (int) $availableYears[0];
        }

        return $year;
    }

    private function getDashboardYears(): array
    {
        $years = DB::table('orders_schedule')
            ->whereNotNull('due_date')
            ->whereRaw("LOWER(TRIM(status_order)) = 'active'")
            ->selectRaw('DISTINCT YEAR(due_date) as y')
            ->orderBy('y', 'desc')
            ->pluck('y')
            ->map(fn ($y) => (int) $y)
            ->filter(fn ($y) => $y >= 2000 && $y <= 3000)
            ->values()
            ->all();
        
        return array_values(array_unique($years));
    }

    private function buildOtdDetailsQuery(int $year, int $month, string $filter, int $quarter = 0)
    {
        $months = $this->resolveDashboardPeriodMonths($month, $quarter);
        $query = DB::table('orders_schedule')
            ->select([
                'id',
                'work_id',
                'co',
                'cust_po',
                'PN',
                'Part_description',
                'costumer',
                'status',
                'due_date',
                'sent_at',
            ])
            ->whereNotNull('due_date')
            ->whereRaw("LOWER(TRIM(status_order)) = 'active'")
            ->whereRaw('YEAR(due_date) = ?', [$year])
            ->whereIn(DB::raw('MONTH(due_date)'), $months);
        $this->applyCurrentPeriodCutoff($query, 'due_date', $year, $months);

        if ($filter === 'ontime') {
            $query->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->whereRaw("LOWER(TRIM(status)) = 'sent'")
                        ->whereNotNull('sent_at')
                        ->whereRaw('DATE(sent_at) <= DATE(due_date)');
                })->orWhere(function ($q2) {
                    $q2->whereRaw("COALESCE(LOWER(TRIM(status)), '') <> 'sent'")
                        ->whereRaw('DATE(due_date) >= CURDATE()');
                });
            });
        } elseif ($filter === 'late') {
            $query->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->whereRaw("LOWER(TRIM(status)) = 'sent'")
                        ->whereNotNull('sent_at')
                        ->whereRaw('DATE(sent_at) > DATE(due_date)');
                })->orWhere(function ($q2) {
                    $q2->whereRaw("COALESCE(LOWER(TRIM(status)), '') <> 'sent'")
                        ->whereRaw('DATE(due_date) < CURDATE()');
                });
            });
        }

        return $query;
    }

    private function buildFaiRejBaseQuery(int $year, int $month, int $quarter = 0)
    {
        $months = $this->resolveDashboardPeriodMonths($month, $quarter);
        $query = DB::table('qa_faisummary as qfs')
            ->join('orders_schedule', 'orders_schedule.id', '=', 'qfs.order_schedule_id')
            ->whereNotNull('qfs.date')
            ->whereRaw('YEAR(qfs.date) = ?', [$year])
            ->whereIn(DB::raw('MONTH(qfs.date)'), $months)
            ->whereRaw("UPPER(TRIM(qfs.insp_type)) = 'FAI'");
        $this->applyCurrentPeriodCutoff($query, 'qfs.date', $year, $months);

        return $query;
    }

    private function buildFaiRejRowsQuery(int $year, int $month, int $quarter = 0)
    {
        return $this->buildFaiRejBaseQuery($year, $month, $quarter)
            ->whereRaw("LOWER(TRIM(qfs.results)) IN ('no pass','nopass','no_pass','fail','np')")
            ->selectRaw('
                orders_schedule.id,
                orders_schedule.work_id,
                orders_schedule.co,
                orders_schedule.cust_po,
                orders_schedule.PN,
                orders_schedule.Part_description,
                orders_schedule.costumer,
                orders_schedule.due_date,
                orders_schedule.sent_at,
                MAX(qfs.date) as fai_date,
                COUNT(*) as fail_ops,
                GROUP_CONCAT(DISTINCT NULLIF(TRIM(qfs.operation), \'\') ORDER BY qfs.operation SEPARATOR \', \') as fail_operations
            ')
            ->groupBy(
                'orders_schedule.id',
                'orders_schedule.work_id',
                'orders_schedule.co',
                'orders_schedule.cust_po',
                'orders_schedule.PN',
                'orders_schedule.Part_description',
                'orders_schedule.costumer',
                'orders_schedule.due_date',
                'orders_schedule.sent_at',
            );
    }

    private function filterDashboardDetailRows(Collection $rows, string $search, callable $toText): Collection
    {
        $needle = trim($search);
        if ($needle === '') {
            return $rows->values();
        }

        $needle = mb_strtolower($needle, 'UTF-8');

        return $rows->filter(function ($row) use ($needle, $toText) {
            $text = mb_strtolower((string) $toText($row), 'UTF-8');
            return str_contains($text, $needle);
        })->values();
    }

    private function formatDashboardDate($date): string
    {
        if (!$date) {
            return '';
        }

        return \Carbon\Carbon::parse($date)->format('M/d/Y');
    }

    private function monthNameEn(int $month): string
    {
        $names = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
        return $names[$month] ?? 'M' . $month;
    }

    private function applyCurrentPeriodCutoff($query, string $dateColumn, int $year, array $months): void
    {
        $today = now();
        if ($year === (int) $today->year && in_array((int) $today->month, $months, true)) {
            $query->whereDate($dateColumn, '<=', $today->toDateString());
        }
    }

    private function resolveDashboardPeriodMonths(int $month, int $quarter): array
    {
        if ($quarter >= 1 && $quarter <= 4) {
            return match ($quarter) {
                1 => [1, 2, 3],
                2 => [4, 5, 6],
                3 => [7, 8, 9],
                default => [10, 11, 12],
            };
        }

        return [$month];
    }

    private function buildOtdRowMeta(object $row): array
    {
        $due = $row->due_date ? \Carbon\Carbon::parse($row->due_date)->startOfDay() : null;
        $sent = $row->sent_at ? \Carbon\Carbon::parse($row->sent_at)->startOfDay() : null;
        $status = strtolower(trim((string) ($row->status ?? '')));
        $today = now()->startOfDay();
        $isSent = $status === 'sent' && $sent !== null;
        $delta = null;
        $isOnTime = false;

        if ($due) {
            if ($isSent) {
                $delta = $sent->diffInDays($due, false);
                $isOnTime = $delta >= 0;
            } else {
                $delta = $today->diffInDays($due, false);
                $isOnTime = $due->gte($today);
            }
        }

        return [
            'days' => $delta === null ? '-' : (string) $delta,
            'status' => $isOnTime ? 'On Time' : 'Late',
        ];
    }

    private function buildDashboardPayload(int $year, ?array $availableYears = null): array
    {
        $currentYear = (int) now()->year;
        $dashboardYears = $availableYears ?? $this->getDashboardYears();

        $otdBaseQuery = DB::table('orders_schedule')
            ->whereNotNull('due_date')
            ->whereRaw("LOWER(TRIM(status_order)) = 'active'");

        $now = now();
        $endDate = $year === $currentYear ? $now->copy()->endOfDay() : $now->copy()->setDate($year, 12, 31)->endOfDay();
        $ytdStart = $endDate->copy()->startOfYear();
        $r12Start = $endDate->copy()->subMonthsNoOverflow(12)->addDay()->startOfDay();

        $otdByMonth = (clone $otdBaseQuery)
            ->selectRaw("
                MONTH(due_date) as m,
                COUNT(*) as total,
                SUM(
                    CASE
                        WHEN LOWER(TRIM(status)) = 'sent' AND sent_at IS NOT NULL AND DATE(sent_at) <= DATE(due_date) THEN 1
                        WHEN COALESCE(LOWER(TRIM(status)), '') <> 'sent' AND DATE(due_date) >= CURDATE() THEN 1
                        ELSE 0
                    END
                ) as on_time
            ")
            ->whereRaw('YEAR(due_date) = ?', [$year])
            ->when($year === $currentYear, function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    $q->whereRaw('MONTH(due_date) < ?', [(int) $now->month])
                        ->orWhere(function ($q2) use ($now) {
                            $q2->whereRaw('MONTH(due_date) = ?', [(int) $now->month])
                                ->whereDate('due_date', '<=', $now->toDateString());
                        });
                });
            })
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $customerOtdCells = [];
        foreach (range(1, 12) as $month) {
            $row = $otdByMonth->get($month);
            $total = (int) ($row->total ?? 0);
            $onTime = (int) ($row->on_time ?? 0);
            if ($total <= 0) {
                continue;
            }

            $pct = ($onTime / $total) * 100;
            $customerOtdCells[$month] = [
                'pct' => round($pct, 1),
                'on_time' => $onTime,
                'total' => $total,
            ];
        }

        // Internal FAI Rejection Rate (Rej./Tot.)
        // Denominator: all FAI records in the month.
        // Numerator: FAI records with "no pass".
        $faiBase = DB::table('qa_faisummary as qfs')
            ->join('orders_schedule', 'orders_schedule.id', '=', 'qfs.order_schedule_id')
            ->whereNotNull('qfs.date')
            ->whereRaw("UPPER(TRIM(qfs.insp_type)) = 'FAI'");

        $faiTotalsByMonth = (clone $faiBase)
            ->selectRaw('MONTH(qfs.date) as m, COUNT(*) as total')
            ->whereRaw('YEAR(qfs.date) = ?', [$year])
            ->when($year === $currentYear, function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    $q->whereRaw('MONTH(qfs.date) < ?', [(int) $now->month])
                        ->orWhere(function ($q2) use ($now) {
                            $q2->whereRaw('MONTH(qfs.date) = ?', [(int) $now->month])
                                ->whereDate('qfs.date', '<=', $now->toDateString());
                        });
                });
            })
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $faiRejectsByMonth = (clone $faiBase)
            ->whereRaw('YEAR(qfs.date) = ?', [$year])
            ->whereRaw("UPPER(TRIM(qfs.insp_type)) = 'FAI'")
            ->whereRaw("LOWER(TRIM(qfs.results)) IN ('no pass','nopass','no_pass','fail','np')")
            ->selectRaw('MONTH(qfs.date) as m, COUNT(*) as rejects')
            ->when($year === $currentYear, function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    $q->whereRaw('MONTH(qfs.date) < ?', [(int) $now->month])
                        ->orWhere(function ($q2) use ($now) {
                            $q2->whereRaw('MONTH(qfs.date) = ?', [(int) $now->month])
                                ->whereDate('qfs.date', '<=', $now->toDateString());
                        });
                });
            })
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $faiRejCells = [];
        foreach (range(1, 12) as $month) {
            $total = (int) (($faiTotalsByMonth->get($month)->total ?? 0));
            if ($total <= 0) {
                continue;
            }

            $rejects = (int) (($faiRejectsByMonth->get($month)->rejects ?? 0));
            $pct = round(($rejects / $total) * 100, 1);
            $faiRejCells[$month] = [
                'pct' => $pct,
                'rejects' => $rejects,
                'total' => $total,
            ];
        }

        // YTD means Jan 1 through the current cutoff date for the selected year.
        $yearStart = $endDate->copy()->startOfYear();
        $yearEnd = $endDate->copy();
        $otdYtd = $this->computeOtdForRange(clone $otdBaseQuery, $yearStart, $yearEnd);
        $otdR12 = $this->computeOtdForRange(clone $otdBaseQuery, $r12Start, $endDate);
        $otdAllYears = $this->computeOtdAllTime(clone $otdBaseQuery);

        $faiRejYtd = $this->computeFaiRejForRange($yearStart, $yearEnd);
        $faiRejR12 = $this->computeFaiRejForRange($r12Start, $endDate);

        $currentMonth = (int) $endDate->month;
        $otdThisMonth = $this->computeOtdForMonth(clone $otdBaseQuery, $year, $currentMonth);
        $sentThisMonth = DB::table('orders_schedule')
            ->whereNotNull('due_date')
            ->whereRaw("LOWER(TRIM(status_order)) = 'active'")
            ->whereRaw("LOWER(TRIM(status)) = 'sent'")
            ->whereRaw('YEAR(due_date) = ?', [$year])
            ->count();

        $lastUpdated = null;
        if (Schema::hasColumn('orders_schedule', 'updated_at')) {
            $lastUpdated = DB::table('orders_schedule')->max('updated_at');
        }
        $lastSent = DB::table('orders_schedule')->max('sent_at');
        if ($lastSent && (!$lastUpdated || $lastSent > $lastUpdated)) {
            $lastUpdated = $lastSent;
        }

        $kpiRows = $this->buildKpiRows($customerOtdCells, $faiRejCells);

        return [
            'dashboardYear' => $year,
            'dashboardYears' => $dashboardYears,
            'customerOtdCells' => $customerOtdCells,
            'faiRejCells' => $faiRejCells,
            'faiRejYtd' => $faiRejYtd,
            'faiRejR12' => $faiRejR12,
            'otdYtd' => $otdYtd,
            'otdR12' => $otdR12,
            'otdAllYears' => $otdAllYears,
            'otdThisMonth' => $otdThisMonth,
            'sentThisMonth' => $sentThisMonth,
            'dashboardEndDate' => $endDate,
            'lastUpdatedAt' => $lastUpdated,
            'kpiRows' => $kpiRows,
        ];
    }

    private function buildKpiRows(array $customerOtdCells, array $faiRejCells): array
    {
        return [
            ['key' => 'customer_otd', 'type' => 'QO', 'prcs' => '', 'name' => 'Customer On-Time Delivery (OTD)', 'values' => $customerOtdCells, 'goal' => '90%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'customer_conf', 'type' => 'QO', 'prcs' => '', 'name' => 'Customer Conformance', 'values' => [], 'goal' => '98%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'internal_conf', 'type' => 'QO', 'prcs' => '', 'name' => 'Internal Conformance', 'values' => [], 'goal' => '98%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'cust_survey', 'type' => 'QO', 'prcs' => '', 'name' => 'Customer Satisfaction Surveys', 'values' => [], 'goal' => '90%', 'goal_class' => '', 'trend' => ''],

            ['key' => 'training', 'type' => 'KPI', 'prcs' => '1', 'name' => 'Training Progress (Req. Training/Req. Eval.)', 'values' => [], 'goal' => '< 3 / < 2 Eval.', 'goal_class' => 'goal-warn', 'trend' => ''],
            ['key' => 'planning_ncars', 'type' => 'KPI', 'prcs' => '2', 'name' => 'Planning NCARs', 'values' => [], 'goal' => '< 7', 'goal_class' => 'goal-warn', 'trend' => ''],
            ['key' => 'ext_otd', 'type' => 'KPI', 'prcs' => '3', 'name' => 'External Provider OTD (Tot. Jobs)', 'values' => [], 'goal' => '90%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'ext_conf', 'type' => 'KPI', 'prcs' => '3', 'name' => "External Provider Conformance (Rej.'s)", 'values' => [], 'goal' => '98%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'fai_rej', 'type' => 'KPI', 'prcs' => '4', 'name' => 'Internal FAI Rejection Rate (Rej./Tot.)', 'values' => $faiRejCells, 'goal' => '15%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'work_audit', 'type' => 'KPI', 'prcs' => '4', 'name' => 'Work Audit Conformance', 'values' => [], 'goal' => '90%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'audit_findings', 'type' => 'KPI', 'prcs' => '5', 'name' => 'Internal Audit Findings', 'values' => [], 'goal' => '< 15', 'goal_class' => '', 'trend' => ''],
        ];
    }

    private function computeOtdForMonth($baseQuery, int $year, int $month): array
    {
        $query = $baseQuery
            ->selectRaw("
                COUNT(*) as total,
                SUM(
                    CASE
                        WHEN LOWER(TRIM(status)) = 'sent' AND sent_at IS NOT NULL AND DATE(sent_at) <= DATE(due_date) THEN 1
                        WHEN COALESCE(LOWER(TRIM(status)), '') <> 'sent' AND DATE(due_date) >= CURDATE() THEN 1
                        ELSE 0
                    END
                ) as on_time
            ")
            ->whereRaw('YEAR(due_date) = ? AND MONTH(due_date) = ?', [$year, $month]);
        $this->applyCurrentPeriodCutoff($query, 'due_date', $year, [$month]);

        $row = $query->first();

        $total = (int) ($row->total ?? 0);
        $onTime = (int) ($row->on_time ?? 0);
        $pct = $total > 0 ? round(($onTime / $total) * 100, 1) : null;

        return ['pct' => $pct, 'on_time' => $onTime, 'total' => $total];
    }

    private function computeOtdForRange($baseQuery, $startDate, $endDate): array
    {
        $row = $baseQuery
            ->selectRaw("
                COUNT(*) as total,
                SUM(
                    CASE
                        WHEN LOWER(TRIM(status)) = 'sent' AND sent_at IS NOT NULL AND DATE(sent_at) <= DATE(due_date) THEN 1
                        WHEN COALESCE(LOWER(TRIM(status)), '') <> 'sent' AND DATE(due_date) >= CURDATE() THEN 1
                        ELSE 0
                    END
                ) as on_time
            ")
            ->whereBetween('due_date', [$startDate, $endDate])
            ->first();

        $total = (int) ($row->total ?? 0);
        $onTime = (int) ($row->on_time ?? 0);
        $pct = $total > 0 ? round(($onTime / $total) * 100, 1) : null;

        return ['pct' => $pct, 'on_time' => $onTime, 'total' => $total];
    }

    private function computeFaiRejForRange($startDate, $endDate): array
    {
        $base = DB::table('qa_faisummary as qfs')
            ->join('orders_schedule', 'orders_schedule.id', '=', 'qfs.order_schedule_id')
            ->whereNotNull('qfs.date')
            ->whereBetween('qfs.date', [$startDate, $endDate])
            ->whereRaw("UPPER(TRIM(qfs.insp_type)) = 'FAI'");

        $total = (int) (clone $base)->count('qfs.id');

        $rejects = (int) (clone $base)
            ->whereRaw("LOWER(TRIM(qfs.results)) IN ('no pass','nopass','no_pass','fail','np')")
            ->count('qfs.id');

        $pct = $total > 0 ? round(($rejects / $total) * 100, 1) : null;

        return ['pct' => $pct, 'rejects' => $rejects, 'total' => $total];
    }

    private function computeOtdAllTime($baseQuery): array
    {
        $row = $baseQuery
            ->selectRaw("
                COUNT(*) as total,
                SUM(
                    CASE
                        WHEN LOWER(TRIM(status)) = 'sent' AND sent_at IS NOT NULL AND DATE(sent_at) <= DATE(due_date) THEN 1
                        WHEN COALESCE(LOWER(TRIM(status)), '') <> 'sent' AND DATE(due_date) >= CURDATE() THEN 1
                        ELSE 0
                    END
                ) as on_time
            ")
            ->first();

        $total = (int) ($row->total ?? 0);
        $onTime = (int) ($row->on_time ?? 0);
        $pct = $total > 0 ? round(($onTime / $total) * 100, 1) : null;

        return ['pct' => $pct, 'on_time' => $onTime, 'total' => $total];
    }

}


