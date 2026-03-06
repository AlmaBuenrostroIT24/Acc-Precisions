<?php

namespace App\Http\Controllers;

use App\Exports\DashboardKpiExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = $this->normalizeYear((int) $request->query('year', now()->year));

        return view('dashboard', $this->buildDashboardPayload($year));
    }

    public function otdDetails(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month');

        if ($year < 2000 || $year > (int) now()->year) {
            return response()->json(['html' => '', 'count' => 0, 'message' => 'Invalid year'], 422);
        }
        if ($month < 1 || $month > 12) {
            return response()->json(['html' => '', 'count' => 0, 'message' => 'Invalid month'], 422);
        }

        $filter = strtolower(trim((string) $request->query('filter', 'all')));
        if (!in_array($filter, ['all', 'ontime', 'late'], true)) {
            $filter = 'all';
        }

        $query = DB::table('orders_schedule')
            ->select([
                'id',
                'work_id',
                'PN',
                'Part_description',
                'costumer',
                'due_date',
                'sent_at',
            ])
            ->whereNotNull('due_date')
            ->whereNotNull('sent_at')
            ->whereRaw("LOWER(TRIM(status)) = 'sent'")
            ->whereRaw("LOWER(TRIM(status_order)) != 'inactive'")
            ->whereRaw('YEAR(due_date) = ? AND MONTH(due_date) = ?', [$year, $month]);

        if ($filter === 'ontime') {
            $query->whereRaw('DATE(sent_at) <= DATE(due_date)');
        } elseif ($filter === 'late') {
            $query->whereRaw('DATE(sent_at) > DATE(due_date)');
        }

        $rows = $query
            ->orderBy('sent_at', 'desc')
            ->orderBy('due_date', 'desc')
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
            'filter' => $filter,
        ]);
    }

    public function faiRejDetails(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month');

        if ($year < 2000 || $year > (int) now()->year) {
            return response()->json(['html' => '', 'count' => 0, 'message' => 'Invalid year'], 422);
        }
        if ($month < 1 || $month > 12) {
            return response()->json(['html' => '', 'count' => 0, 'message' => 'Invalid month'], 422);
        }

        $base = DB::table('orders_schedule')
            ->whereNotNull('due_date')
            ->whereRaw("LOWER(TRIM(status)) = 'sent'")
            ->whereRaw("LOWER(TRIM(status_order)) = 'active'")
            ->whereRaw('YEAR(due_date) = ? AND MONTH(due_date) = ?', [$year, $month]);

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->join('qa_faisummary as qfs', 'qfs.order_schedule_id', '=', 'orders_schedule.id')
            ->whereRaw("UPPER(TRIM(qfs.insp_type)) = 'FAI'")
            ->whereRaw("LOWER(TRIM(qfs.results)) IN ('no pass','nopass','no_pass','fail','np')")
            ->selectRaw('
                orders_schedule.id,
                orders_schedule.work_id,
                orders_schedule.PN,
                orders_schedule.Part_description,
                orders_schedule.costumer,
                orders_schedule.due_date,
                orders_schedule.sent_at,
                COUNT(*) as fail_ops,
                GROUP_CONCAT(DISTINCT NULLIF(TRIM(qfs.operation), \'\') ORDER BY qfs.operation SEPARATOR \', \') as fail_operations
            ')
            ->groupBy(
                'orders_schedule.id',
                'orders_schedule.work_id',
                'orders_schedule.PN',
                'orders_schedule.Part_description',
                'orders_schedule.costumer',
                'orders_schedule.due_date',
                'orders_schedule.sent_at',
            )
            ->orderBy('orders_schedule.sent_at', 'desc')
            ->orderBy('orders_schedule.due_date', 'desc')
            ->limit(2000)
            ->get();

        $rejects = $rows->count();
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
        ]);
    }

    public function exportPdf(Request $request)
    {
        $year = $this->normalizeYear((int) $request->query('year', now()->year));
        $payload = $this->buildDashboardPayload($year);

        $pdf = Pdf::loadView('dashboard.export_pdf', $payload)
            ->setPaper('letter', 'landscape');

        return $pdf->stream('dashboard-kpis-' . $year . '.pdf', ['Attachment' => false]);
    }

    public function exportExcel(Request $request)
    {
        $year = $this->normalizeYear((int) $request->query('year', now()->year));
        $payload = $this->buildDashboardPayload($year);

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

    private function normalizeYear(int $year): int
    {
        $currentYear = (int) now()->year;
        if ($year < 2000 || $year > $currentYear) {
            return $currentYear;
        }

        return $year;
    }

    private function buildDashboardPayload(int $year): array
    {
        $currentYear = (int) now()->year;

        $baseQuery = DB::table('orders_schedule')
            ->whereNotNull('due_date')
            ->whereNotNull('sent_at')
            ->whereRaw("LOWER(TRIM(status)) = 'sent'")
            ->whereRaw("LOWER(TRIM(status_order)) != 'inactive'");

        $now = now();
        $endDate = $year === $currentYear ? $now->copy()->endOfDay() : $now->copy()->setDate($year, 12, 31)->endOfDay();
        $ytdStart = $endDate->copy()->startOfYear();
        $r12Start = $endDate->copy()->subMonthsNoOverflow(12)->addDay()->startOfDay();

        $otdByMonth = (clone $baseQuery)
            ->selectRaw("
                MONTH(due_date) as m,
                COUNT(*) as total,
                SUM(CASE WHEN DATE(sent_at) <= DATE(due_date) THEN 1 ELSE 0 END) as on_time
            ")
            ->whereRaw('YEAR(due_date) = ?', [$year])
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
        // Denominator: orders_schedule (sent + active). Numerator: orders that have at least one FAI "no pass" in qa_faisummary.
        $faiBase = DB::table('orders_schedule')
            ->whereNotNull('due_date')
            ->whereRaw("LOWER(TRIM(status)) = 'sent'")
            ->whereRaw("LOWER(TRIM(status_order)) = 'active'");

        $faiTotalsByMonth = (clone $faiBase)
            ->selectRaw('MONTH(due_date) as m, COUNT(*) as total')
            ->whereRaw('YEAR(due_date) = ?', [$year])
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $faiRejectsByMonth = (clone $faiBase)
            ->join('qa_faisummary as qfs', 'qfs.order_schedule_id', '=', 'orders_schedule.id')
            ->whereRaw('YEAR(due_date) = ?', [$year])
            ->whereRaw("UPPER(TRIM(qfs.insp_type)) = 'FAI'")
            ->whereRaw("LOWER(TRIM(qfs.results)) IN ('no pass','nopass','no_pass','fail','np')")
            ->selectRaw('MONTH(due_date) as m, COUNT(DISTINCT orders_schedule.id) as rejects')
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

        // "YTD" on this dashboard means full-year (Jan 1 → Dec 31) for the selected year.
        $yearStart = $endDate->copy()->startOfYear();
        $yearEnd = $endDate->copy()->endOfYear();
        $otdYtd = $this->computeOtdForRange(clone $baseQuery, $yearStart, $yearEnd);
        $otdR12 = $this->computeOtdForRange(clone $baseQuery, $r12Start, $endDate);
        $otdAllYears = $this->computeOtdAllTime(clone $baseQuery);

        $faiRejYtd = $this->computeFaiRejForRange($yearStart, $yearEnd);
        $faiRejR12 = $this->computeFaiRejForRange($r12Start, $endDate);

        $currentMonth = (int) $endDate->month;
        $otdThisMonth = $this->computeOtdForMonth(clone $baseQuery, $year, $currentMonth);
        $sentThisMonth = (clone $baseQuery)
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
            ['key' => 'customer_conf', 'type' => 'QO', 'prcs' => '', 'name' => 'Customer Conformance', 'values' => [1 => '98.5% (5)'], 'goal' => '98%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'internal_conf', 'type' => 'QO', 'prcs' => '', 'name' => 'Internal Conformance', 'values' => [1 => '99.4% (2)'], 'goal' => '98%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'cust_survey', 'type' => 'QO', 'prcs' => '', 'name' => 'Customer Satisfaction Surveys', 'values' => [6 => '94.2% 2025.1'], 'goal' => '90%', 'goal_class' => '', 'trend' => ''],

            ['key' => 'training', 'type' => 'KPI', 'prcs' => '1', 'name' => 'Training Progress (Req. Training/Req. Eval.)', 'values' => [3 => '2/2'], 'goal' => '< 3 / < 2 Eval.', 'goal_class' => 'goal-warn', 'trend' => ''],
            ['key' => 'planning_ncars', 'type' => 'KPI', 'prcs' => '2', 'name' => 'Planning NCARs', 'values' => [3 => '0'], 'goal' => '< 7', 'goal_class' => 'goal-warn', 'trend' => ''],
            ['key' => 'ext_otd', 'type' => 'KPI', 'prcs' => '3', 'name' => 'External Provider OTD (Tot. Jobs)', 'values' => [3 => '94.5% (217)'], 'goal' => '90%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'ext_conf', 'type' => 'KPI', 'prcs' => '3', 'name' => "External Provider Conformance (Rej.'s)", 'values' => [3 => '99.1% (2)'], 'goal' => '98%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'fai_rej', 'type' => 'KPI', 'prcs' => '4', 'name' => 'Internal FAI Rejection Rate (Rej./Tot.)', 'values' => $faiRejCells, 'goal' => '15%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'work_audit', 'type' => 'KPI', 'prcs' => '4', 'name' => 'Work Audit Conformance', 'values' => [3 => '96.7%'], 'goal' => '90%', 'goal_class' => '', 'trend' => ''],
            ['key' => 'audit_findings', 'type' => 'KPI', 'prcs' => '5', 'name' => 'Internal Audit Findings', 'values' => [9 => '3 in 2025'], 'goal' => '< 15', 'goal_class' => '', 'trend' => ''],
        ];
    }

    private function computeOtdForMonth($baseQuery, int $year, int $month): array
    {
        $row = $baseQuery
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN DATE(sent_at) <= DATE(due_date) THEN 1 ELSE 0 END) as on_time')
            ->whereRaw('YEAR(due_date) = ? AND MONTH(due_date) = ?', [$year, $month])
            ->first();

        $total = (int) ($row->total ?? 0);
        $onTime = (int) ($row->on_time ?? 0);
        $pct = $total > 0 ? round(($onTime / $total) * 100, 1) : null;

        return ['pct' => $pct, 'on_time' => $onTime, 'total' => $total];
    }

    private function computeOtdForRange($baseQuery, $startDate, $endDate): array
    {
        $row = $baseQuery
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN DATE(sent_at) <= DATE(due_date) THEN 1 ELSE 0 END) as on_time')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->first();

        $total = (int) ($row->total ?? 0);
        $onTime = (int) ($row->on_time ?? 0);
        $pct = $total > 0 ? round(($onTime / $total) * 100, 1) : null;

        return ['pct' => $pct, 'on_time' => $onTime, 'total' => $total];
    }

    private function computeFaiRejForRange($startDate, $endDate): array
    {
        $base = DB::table('orders_schedule')
            ->whereNotNull('due_date')
            ->whereRaw("LOWER(TRIM(status)) = 'sent'")
            ->whereRaw("LOWER(TRIM(status_order)) = 'active'")
            ->whereBetween('due_date', [$startDate, $endDate]);

        $total = (int) (clone $base)->count();

        $rejects = (int) (clone $base)
            ->join('qa_faisummary as qfs', 'qfs.order_schedule_id', '=', 'orders_schedule.id')
            ->whereRaw("UPPER(TRIM(qfs.insp_type)) = 'FAI'")
            ->whereRaw("LOWER(TRIM(qfs.results)) IN ('no pass','nopass','no_pass','fail','np')")
            ->selectRaw('COUNT(DISTINCT orders_schedule.id) as rejects')
            ->value('rejects');

        $pct = $total > 0 ? round(($rejects / $total) * 100, 1) : null;

        return ['pct' => $pct, 'rejects' => $rejects, 'total' => $total];
    }

    private function computeOtdAllTime($baseQuery): array
    {
        $row = $baseQuery
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN DATE(sent_at) <= DATE(due_date) THEN 1 ELSE 0 END) as on_time')
            ->first();

        $total = (int) ($row->total ?? 0);
        $onTime = (int) ($row->on_time ?? 0);
        $pct = $total > 0 ? round(($onTime / $total) * 100, 1) : null;

        return ['pct' => $pct, 'on_time' => $onTime, 'total' => $total];
    }

}
