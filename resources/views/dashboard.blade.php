@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/css/dashboard.css?v=' . filemtime(public_path('vendor/css/dashboard.css'))) }}">
@stop

@section('content')
    @php
        $dashboardYear = $dashboardYear ?? (int) now()->year;
        $yearShort = substr((string) $dashboardYear, -2);
        $quarters = [
            ['label' => "Q1 {$yearShort}", 'months' => [1, 2, 3]],
            ['label' => "Q2 {$yearShort}", 'months' => [4, 5, 6]],
            ['label' => "Q3 {$yearShort}", 'months' => [7, 8, 9]],
            ['label' => "Q4 {$yearShort}", 'months' => [10, 11, 12]],
        ];

        $customerOtdCells = $customerOtdCells ?? [];
        $faiRejCells = $faiRejCells ?? [];
        $faiRejYtd = $faiRejYtd ?? ['pct' => null, 'rejects' => 0, 'total' => 0];
        $faiRejR12 = $faiRejR12 ?? ['pct' => null, 'rejects' => 0, 'total' => 0];
        $otdYtd = $otdYtd ?? ['pct' => null, 'on_time' => 0, 'total' => 0];
        $otdR12 = $otdR12 ?? ['pct' => null, 'on_time' => 0, 'total' => 0];
        $otdAllYears = $otdAllYears ?? ['pct' => null, 'on_time' => 0, 'total' => 0];
        $otdThisMonth = $otdThisMonth ?? ['pct' => null, 'on_time' => 0, 'total' => 0];
        $sentThisMonth = $sentThisMonth ?? 0;
        $dashboardEndDate = $dashboardEndDate ?? now();

        $rows = [
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

        $months = range(1, 12);
        $monthEs = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
        $fmtEsDate = function ($date) use ($monthEs) {
            $d = \Carbon\Carbon::parse($date);
            $m = (int) $d->format('n');
            return ($monthEs[$m] ?? strtolower($d->format('M'))) . '/' . $d->format('d/Y');
        };
        $fmtEsDateTime = function ($date) use ($monthEs) {
            $d = \Carbon\Carbon::parse($date);
            $m = (int) $d->format('n');
            return ($monthEs[$m] ?? strtolower($d->format('M'))) . '/' . $d->format('d/Y H:i');
        };

        $pctTone = function ($pct) {
            if ($pct === null) return '';
            if ($pct >= 90) return 'kpi-tone--good';
            if ($pct >= 85) return 'kpi-tone--warn';
            return 'kpi-tone--bad';
        };

        $faiGoal = 15.0;
        $pctToneLower = function ($pct, float $goal) {
            if ($pct === null) return '';
            $pct = (float) $pct;
            if ($pct <= $goal) return 'kpi-tone--good';
            if ($pct <= ($goal + 3.0)) return 'kpi-tone--warn';
            return 'kpi-tone--bad';
        };

        $otdGoal = 90.0;

        $kpiBadge = function ($pct, $total) use ($otdGoal) {
            if (!$total) return ['text' => 'No data', 'class' => 'kpi-badge--nodata'];
            if ($pct >= $otdGoal) return ['text' => 'On target', 'class' => 'kpi-badge--good'];
            return ['text' => 'Below goal', 'class' => 'kpi-badge--bad'];
        };

        $totalCols = 3 + count($months) + 4 + 4; // + quarter totals + YTD + R12 + Goal + Trend

        $years = range((int) now()->year, 2025);
    @endphp

    <div class="row mb-2 dashboard-kpi-5" style="row-gap:.5rem;">
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="info-box dashboard-kpi-box dashboard-kpi-box--side" data-accent="slate">
                <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
                <div class="info-box-content">
                    @php
                        $pct = $otdAllYears['pct'];
                        $total = (int) $otdAllYears['total'];
                        $badge = $kpiBadge($pct ?? 0, $total);
                        $delta = $pct !== null ? round($pct - $otdGoal, 1) : null;
                    @endphp
                    <div class="dashboard-kpi-top">
                        <div class="dashboard-kpi-left">
                            <div class="dashboard-kpi-label">OTD (All Years)</div>
                            <div class="dashboard-kpi-value {{ $pct !== null ? $pctTone($pct) : '' }}">
                                {{ $pct !== null ? number_format($pct, 1) . '%' : '-' }}
                            </div>
                            <div class="dashboard-kpi-meta">
                                <span class="dashboard-kpi-goal">Goal {{ number_format($otdGoal, 0) }}%</span>
                                <span class="dashboard-kpi-dot">•</span>
                                <span class="dashboard-kpi-delta {{ $delta !== null && $delta >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $delta !== null ? (($delta >= 0 ? '+' : '') . number_format($delta, 1) . ' pts') : '' }}
                                </span>
                            </div>
                        </div>
                        <div class="dashboard-kpi-right">
                            <div class="dashboard-kpi-ratio">{{ (int) $otdAllYears['on_time'] }} / {{ $total }}</div>
                            <span class="kpi-badge {{ $badge['class'] }}">{{ $badge['text'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="info-box dashboard-kpi-box dashboard-kpi-box--side" data-accent="blue">
                <span class="info-box-icon"><i class="fas fa-bullseye"></i></span>
                <div class="info-box-content">
                    <div class="dashboard-kpi-top">
                        <div class="dashboard-kpi-left">
                            <div class="dashboard-kpi-label">KPI (TBD)</div>
                            <div class="dashboard-kpi-value">-</div>
                            <div class="dashboard-kpi-meta">
                                <span class="text-muted">Pending definition</span>
                            </div>
                        </div>
                        <div class="dashboard-kpi-right">
                            <span class="kpi-badge kpi-badge--nodata">TBD</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="info-box dashboard-kpi-box dashboard-kpi-box--side" data-accent="amber">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <div class="dashboard-kpi-top">
                        <div class="dashboard-kpi-left">
                            <div class="dashboard-kpi-label">KPI (TBD)</div>
                            <div class="dashboard-kpi-value">-</div>
                            <div class="dashboard-kpi-meta">
                                <span class="text-muted">Pending definition</span>
                            </div>
                        </div>
                        <div class="dashboard-kpi-right">
                            <span class="kpi-badge kpi-badge--nodata">TBD</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="info-box dashboard-kpi-box dashboard-kpi-box--side" data-accent="teal">
                <span class="info-box-icon"><i class="fas fa-clipboard-check"></i></span>
                <div class="info-box-content">
                    <div class="dashboard-kpi-top">
                        <div class="dashboard-kpi-left">
                            <div class="dashboard-kpi-label">KPI (TBD)</div>
                            <div class="dashboard-kpi-value">-</div>
                            <div class="dashboard-kpi-meta">
                                <span class="text-muted">Pending definition</span>
                            </div>
                        </div>
                        <div class="dashboard-kpi-right">
                            <span class="kpi-badge kpi-badge--nodata">TBD</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="info-box dashboard-kpi-box dashboard-kpi-box--side" data-accent="purple">
                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <div class="dashboard-kpi-top">
                        <div class="dashboard-kpi-left">
                            <div class="dashboard-kpi-label">KPI (TBD)</div>
                            <div class="dashboard-kpi-value">-</div>
                            <div class="dashboard-kpi-meta">
                                <span class="text-muted">Pending definition</span>
                            </div>
                        </div>
                        <div class="dashboard-kpi-right">
                            <span class="kpi-badge kpi-badge--nodata">TBD</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary mb-2 kpi-card kpi-main-card" id="dashboardKpiContainer">
        <div class="card-header py-2 d-flex align-items-center justify-content-between">
            <div class="w-100">
                <h3 class="card-title mb-0">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Quality Objectives &amp; KPIs
                </h3>
            </div>
        </div>

        <div class="card-body p-1">
            @php
                $buildOtdItem = function (array $data, string $label, string $accent, string $icon) use ($kpiBadge, $otdGoal) {
                    $pct = $data['pct'];
                    $total = (int) $data['total'];
                    $badge = $kpiBadge($pct ?? 0, $total);
                    $delta = $pct !== null ? round($pct - $otdGoal, 1) : null;

                    $meta = 'Goal ' . number_format($otdGoal, 0) . '%';
                    if ($delta !== null) {
                        $meta .= ' • ' . (($delta >= 0 ? '+' : '') . number_format($delta, 1) . ' pts');
                    }
                    $meta = preg_replace('/\\s*[^\\x20-\\x7E]+\\s*/', ' | ', $meta);

                    return [
                        'accent' => $accent,
                        'icon' => $icon,
                        'label' => $label,
                        'value' => $pct !== null ? number_format($pct, 1) . '%' : '-',
                        'meta' => $meta,
                        'meta_class' => $delta !== null ? ($delta >= 0 ? 'text-success' : 'text-danger') : 'text-muted',
                        'ratio' => $total ? ((int) $data['on_time'] . ' / ' . $total) : '',
                        'badge_text' => $badge['text'],
                        'badge_class' => $badge['class'],
                    ];
                };

                $kpiStripItems = [
                    $buildOtdItem($otdYtd, 'OTD (YTD)', 'blue', 'fas fa-truck'),
                    $buildOtdItem($otdThisMonth, 'OTD (Month)', 'indigo', 'fas fa-calendar-alt'),
                    [
                        'accent' => 'green',
                        'icon' => 'fas fa-check-circle',
                        'label' => 'Sent (Year)',
                        'value' => (string) ((int) $sentThisMonth),
                        'meta' => 'Due date year',
                        'meta_class' => 'text-muted',
                        'ratio' => '',
                        'badge_text' => 'Sent',
                        'badge_class' => 'kpi-badge--info',
                    ],
                ];
            @endphp

            <div class="kpi-strip mb-2" role="group" aria-label="KPI summary strip">
                @foreach($kpiStripItems as $item)
                    <div class="kpi-strip-item" data-accent="{{ $item['accent'] }}">
                        <div class="kpi-strip-icon"><i class="{{ $item['icon'] }}"></i></div>
                        <div class="kpi-strip-main">
                            <div class="kpi-strip-label">{{ $item['label'] }}</div>
                            <div class="kpi-strip-value">{{ $item['value'] }}</div>
                            <div class="kpi-strip-meta {{ $item['meta_class'] }}">{{ $item['meta'] }}</div>
                        </div>
                        <div class="kpi-strip-side">
                            @if(!empty($item['ratio']))
                                <div class="kpi-strip-ratio">{{ $item['ratio'] }}</div>
                            @endif
                            <span class="kpi-badge {{ $item['badge_class'] }}">{{ $item['badge_text'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row mb-2 kpi-summary-cards">
                <div class="col-lg-4 col-md-6">
                    <div class="info-box dashboard-kpi-box" data-accent="blue">
                        <span class="info-box-icon"><i class="fas fa-truck"></i></span>
                        <div class="info-box-content">
                            @php
                                $pct = $otdYtd['pct'];
                                $total = (int) $otdYtd['total'];
                                $badge = $kpiBadge($pct ?? 0, $total);
                                $delta = $pct !== null ? round($pct - $otdGoal, 1) : null;
                            @endphp
                            <div class="dashboard-kpi-top">
                                <div class="dashboard-kpi-left">
                                    <div class="dashboard-kpi-label">OTD (YTD)</div>
                                    <div class="dashboard-kpi-value {{ $pct !== null ? $pctTone($pct) : '' }}">
                                        {{ $pct !== null ? number_format($pct, 1) . '%' : '-' }}
                                    </div>
                                    <div class="dashboard-kpi-meta">
                                        <span class="dashboard-kpi-goal">Goal {{ number_format($otdGoal, 0) }}%</span>
                                        <span class="dashboard-kpi-dot">•</span>
                                        <span class="dashboard-kpi-delta {{ $delta !== null && $delta >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $delta !== null ? (($delta >= 0 ? '+' : '') . number_format($delta, 1) . ' pts') : '' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="dashboard-kpi-right">
                                    <div class="dashboard-kpi-ratio">{{ (int) $otdYtd['on_time'] }} / {{ $total }}</div>
                                    <span class="kpi-badge {{ $badge['class'] }}">{{ $badge['text'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="info-box dashboard-kpi-box" data-accent="indigo">
                        <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            @php
                                $pct = $otdThisMonth['pct'];
                                $total = (int) $otdThisMonth['total'];
                                $badge = $kpiBadge($pct ?? 0, $total);
                                $delta = $pct !== null ? round($pct - $otdGoal, 1) : null;
                            @endphp
                            <div class="dashboard-kpi-top">
                                <div class="dashboard-kpi-left">
                                    <div class="dashboard-kpi-label">OTD (Month)</div>
                                    <div class="dashboard-kpi-value {{ $pct !== null ? $pctTone($pct) : '' }}">
                                        {{ $pct !== null ? number_format($pct, 1) . '%' : '-' }}
                                    </div>
                                    <div class="dashboard-kpi-meta">
                                        <span class="dashboard-kpi-goal">Goal {{ number_format($otdGoal, 0) }}%</span>
                                        <span class="dashboard-kpi-dot">•</span>
                                        <span class="dashboard-kpi-delta {{ $delta !== null && $delta >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $delta !== null ? (($delta >= 0 ? '+' : '') . number_format($delta, 1) . ' pts') : '' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="dashboard-kpi-right">
                                    <div class="dashboard-kpi-ratio">{{ (int) $otdThisMonth['on_time'] }} / {{ $total }}</div>
                                    <span class="kpi-badge {{ $badge['class'] }}">{{ $badge['text'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="info-box dashboard-kpi-box" data-accent="green">
                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <div class="dashboard-kpi-top">
                                <div class="dashboard-kpi-left">
                                    <div class="dashboard-kpi-label">Sent (Year)</div>
                                    <div class="dashboard-kpi-value">{{ (int) $sentThisMonth }}</div>
                                    <div class="dashboard-kpi-meta">
                                        <span class="text-muted">Due date year</span>
                                    </div>
                                </div>
                                <div class="dashboard-kpi-right">
                                    <span class="kpi-badge kpi-badge--info">Sent</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-toolbar dashboard-toolbar--integrated mb-2">
                <div class="d-flex align-items-center flex-wrap gap-2 dashboard-meta-group">
                    <div class="d-flex align-items-center gap-1 dashboard-meta-item">
                        <span class="dashboard-chip">Year</span>
                        <select id="dashboardYearSelect" class="form-control form-control-sm dashboard-year-select" aria-label="Select year">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ (int) $dashboardYear === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="dashboardYearReset" class="btn btn-sm btn-outline-secondary dashboard-year-reset" title="Reset to current year">
                            Current
                        </button>
                    </div>

                    <span class="dashboard-chip dashboard-meta-item">
                        <i class="far fa-calendar-alt mr-1" aria-hidden="true"></i>
                        As of {{ $fmtEsDate($dashboardEndDate) }}
                    </span>

                    @if(!empty($lastUpdatedAt))
                        <span class="dashboard-chip dashboard-chip--info dashboard-meta-item" title="Last update from orders_schedule ({{ config('app.timezone') }}): {{ $fmtEsDateTime($lastUpdatedAt) }}">
                            <i class="far fa-clock mr-1" aria-hidden="true"></i>
                            Updated {{ $fmtEsDateTime($lastUpdatedAt) }}
                        </span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm dashboard-export-group" role="group" aria-label="Export">
                        <a
                            class="btn btn-outline-secondary dashboard-export-btn dashboard-export-btn--pdf"
                            href="{{ route('dashboard.exportPdf', ['year' => (int) $dashboardYear]) }}"
                            target="_blank"
                            rel="noopener"
                            title="Export PDF"
                            aria-label="Export PDF"
                        >
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <a
                            class="btn btn-outline-secondary dashboard-export-btn dashboard-export-btn--excel"
                            href="{{ route('dashboard.exportExcel', ['year' => (int) $dashboardYear]) }}"
                            title="Export Excel"
                            aria-label="Export Excel"
                        >
                            <i class="fas fa-file-excel"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row kpi-report-layout" style="row-gap:.5rem;">
                <div class="col-12 col-lg-10 kpi-report-layout__main">
            <div class="kpi-report-shell">
                <div class="kpi-report-scroll">
                    <table class="kpi-report" aria-label="Quality Objectives and KPIs report">
                        <thead>
                            <tr class="kpi-qrow">
                                <th class="col-type" rowspan="2">Type</th>
                                <th class="col-prcs" rowspan="2">Prcs.</th>
                                <th class="col-name" rowspan="2">Name</th>
                                @foreach($quarters as $q)
                                    <th colspan="{{ count($q['months']) + 1 }}" class="kpi-qhdr {{ $loop->last ? 'kpi-sep--block' : 'kpi-sep' }}">{{ $q['label'] }}</th>
                                @endforeach
                                <th class="col-ytd" rowspan="2">YTD</th>
                                <th class="col-r12" rowspan="2">Rolling<br>12M</th>
                                <th class="col-goal" rowspan="2">Goal/Per<br>Term</th>
                                <th class="col-trend" rowspan="2">Trend /<br>NC Doc Ref.</th>
                            </tr>
                            <tr class="kpi-mrow">
                                @foreach($months as $m)
                                    <th class="col-month">{{ $m }}</th>
                                    @if(in_array($m, [3, 6, 9, 12], true))
                                        <th class="col-qtotal {{ $m === 12 ? 'kpi-sep--block' : 'kpi-sep' }}">TOT</th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr class="{{ $row['type'] === 'QO' ? 'row-qo' : '' }}" data-kpi-row="{{ $row['key'] ?? '' }}">
                                    <td class="col-type">
                                        <span class="kpi-pill {{ $row['type'] === 'QO' ? 'kpi-pill--qo' : 'kpi-pill--kpi' }}">{{ $row['type'] }}</span>
                                    </td>
                                    <td class="col-prcs">{{ $row['prcs'] }}</td>
                                    <td class="col-name">{{ $row['name'] }}</td>

                                    @php
                                        $isOtd = ($row['key'] ?? '') === 'customer_otd';
                                        $isFaiRej = ($row['key'] ?? '') === 'fai_rej';
                                        $quarterTotals = [];

                                        if ($isOtd) {
                                            foreach ($quarters as $qi => $q) {
                                                $onTime = 0;
                                                $total = 0;
                                                foreach ($q['months'] as $qm) {
                                                    $cellQm = $row['values'][$qm] ?? null;
                                                    if (is_array($cellQm)) {
                                                        $onTime += (int) ($cellQm['on_time'] ?? 0);
                                                        $total += (int) ($cellQm['total'] ?? 0);
                                                    }
                                                }
                                                $pct = $total > 0 ? round(($onTime / $total) * 100, 1) : null;
                                                $quarterTotals[$qi + 1] = ['pct' => $pct, 'on_time' => $onTime, 'total' => $total];
                                            }
                                        } elseif ($isFaiRej) {
                                            foreach ($quarters as $qi => $q) {
                                                $rejects = 0;
                                                $total = 0;
                                                foreach ($q['months'] as $qm) {
                                                    $cellQm = $row['values'][$qm] ?? null;
                                                    if (is_array($cellQm)) {
                                                        $rejects += (int) ($cellQm['rejects'] ?? 0);
                                                        $total += (int) ($cellQm['total'] ?? 0);
                                                    }
                                                }
                                                $pct = $total > 0 ? round(($rejects / $total) * 100, 1) : null;
                                                $quarterTotals[$qi + 1] = ['pct' => $pct, 'rejects' => $rejects, 'total' => $total];
                                            }
                                        }
                                    @endphp

                                    @foreach($months as $m)
                                        @php
                                            $cell = $row['values'][$m] ?? null;
                                            $pct = $isOtd && is_array($cell) ? ($cell['pct'] ?? null) : null;
                                            $title = $isOtd && is_array($cell) ? (($cell['on_time'] ?? 0) . '/' . ($cell['total'] ?? 0)) : '';
                                            $faiPct = $isFaiRej && is_array($cell) ? ($cell['pct'] ?? null) : null;
                                            $faiClickable = $isFaiRej && is_array($cell) && ((int) ($cell['rejects'] ?? 0) > 0);
                                            $faiTone = $isFaiRej ? $pctToneLower($faiPct, $faiGoal) : '';
                                        @endphp
                                        <td
                                            class="col-month
                                                {{ $isOtd ? 'js-otd-cell kpi-clickable ' . $pctTone($pct) : '' }}
                                                {{ $faiClickable ? 'js-fai-rej-cell kpi-clickable ' . $faiTone : '' }}
                                            "
                                            @if($isOtd || $faiClickable)
                                                data-year="{{ (int) $dashboardYear }}"
                                                data-month="{{ (int) $m }}"
                                                title="{{ $isOtd ? $title : 'FAI no pass details' }}"
                                            @endif
                                        >
                                            @if($isOtd)
                                                {{ $pct !== null ? number_format($pct, 1) . '%' : '' }}
                                                @if(is_array($cell) && !empty($cell['total']))
                                                    <span class="kpi-cell-meta">({{ (int) $cell['total'] }})</span>
                                                @endif
                                            @elseif($isFaiRej && is_array($cell))
                                                {{ ($cell['pct'] ?? null) !== null ? number_format((float) $cell['pct'], 1) . '%' : '' }}
                                                @if(!empty($cell['total']))
                                                    <span class="kpi-cell-meta">({{ (int) ($cell['rejects'] ?? 0) }}/{{ (int) $cell['total'] }})</span>
                                                @endif
                                            @else
                                                {{ is_array($cell) ? '' : ($cell ?? '') }}
                                            @endif
                                        </td>

                                        @if($isOtd && in_array($m, [3, 6, 9, 12], true))
                                            @php
                                                $qi = intdiv($m - 1, 3) + 1;
                                                $qt = $quarterTotals[$qi] ?? ['pct' => null, 'on_time' => 0, 'total' => 0];
                                                $qpct = $qt['pct'] ?? null;
                                                $qtitle = ($qt['on_time'] ?? 0) . '/' . ($qt['total'] ?? 0);
                                            @endphp
                                            <td class="col-qtotal {{ $m === 12 ? 'kpi-sep--block' : 'kpi-sep' }} {{ $qpct !== null ? $pctTone($qpct) : '' }}" title="{{ $qtitle }}">
                                                {{ $qpct !== null ? number_format($qpct, 1) . '%' : '' }}
                                                @if(!empty($qt['total']))
                                                    <span class="kpi-cell-meta">({{ (int) $qt['total'] }})</span>
                                                @endif
                                            </td>
                                        @elseif($isFaiRej && in_array($m, [3, 6, 9, 12], true))
                                            @php
                                                $qi = intdiv($m - 1, 3) + 1;
                                                $qt = $quarterTotals[$qi] ?? ['pct' => null, 'rejects' => 0, 'total' => 0];
                                                $qpct = $qt['pct'] ?? null;
                                                $qtitle = ($qt['rejects'] ?? 0) . '/' . ($qt['total'] ?? 0);
                                            @endphp
                                            <td class="col-qtotal {{ $m === 12 ? 'kpi-sep--block' : 'kpi-sep' }} {{ $qpct !== null ? $pctToneLower($qpct, $faiGoal) : '' }}" title="{{ $qtitle }}">
                                                {{ $qpct !== null ? number_format((float) $qpct, 1) . '%' : '' }}
                                                @if(!empty($qt['total']))
                                                    <span class="kpi-cell-meta">({{ (int) ($qt['rejects'] ?? 0) }}/{{ (int) $qt['total'] }})</span>
                                                @endif
                                            </td>
                                        @elseif(in_array($m, [3, 6, 9, 12], true))
                                            <td class="col-qtotal {{ $m === 12 ? 'kpi-sep--block' : 'kpi-sep' }}"></td>
                                        @endif
                                    @endforeach

                                    @php
                                        $ytdPct = $isOtd ? ($otdYtd['pct'] ?? null) : ($isFaiRej ? ($faiRejYtd['pct'] ?? null) : null);
                                        $r12Pct = $isOtd ? ($otdR12['pct'] ?? null) : ($isFaiRej ? ($faiRejR12['pct'] ?? null) : null);
                                    @endphp
                                    @php
                                        $ytdEmpty = !$isOtd || $ytdPct === null;
                                    @endphp
                                    @php
                                        $ytdTone = $isOtd ? $pctTone($ytdPct) : ($isFaiRej ? $pctToneLower($ytdPct, $faiGoal) : '');
                                        $ytdMeta = $isOtd ? (($otdYtd['on_time'] ?? 0) . '/' . ($otdYtd['total'] ?? 0)) : ($isFaiRej ? (($faiRejYtd['rejects'] ?? 0) . '/' . ($faiRejYtd['total'] ?? 0)) : '');
                                    @endphp
                                    <td class="col-ytd {{ $ytdTone }} {{ ($isOtd || $isFaiRej) && $ytdPct === null ? 'kpi-empty' : '' }}" title="{{ $ytdMeta }}">
                                        @if(($isOtd || $isFaiRej) && $ytdPct !== null)
                                            {{ number_format((float) $ytdPct, 1) . '%' }}
                                        @endif
                                        @if($isOtd && !empty($otdYtd['total']))
                                            <span class="kpi-cell-meta">({{ (int) $otdYtd['total'] }})</span>
                                        @elseif($isFaiRej && !empty($faiRejYtd['total']))
                                            <span class="kpi-cell-meta">({{ (int) ($faiRejYtd['rejects'] ?? 0) }}/{{ (int) $faiRejYtd['total'] }})</span>
                                        @endif
                                    </td>
                                    @php
                                        $r12Empty = !$isOtd || $r12Pct === null;
                                    @endphp
                                    @php
                                        $r12Tone = $isOtd ? $pctTone($r12Pct) : ($isFaiRej ? $pctToneLower($r12Pct, $faiGoal) : '');
                                        $r12Meta = $isOtd ? (($otdR12['on_time'] ?? 0) . '/' . ($otdR12['total'] ?? 0)) : ($isFaiRej ? (($faiRejR12['rejects'] ?? 0) . '/' . ($faiRejR12['total'] ?? 0)) : '');
                                    @endphp
                                    <td class="col-r12 {{ $r12Tone }} {{ ($isOtd || $isFaiRej) && $r12Pct === null ? 'kpi-empty' : '' }}" title="{{ $r12Meta }}">
                                        @if(($isOtd || $isFaiRej) && $r12Pct !== null)
                                            {{ number_format((float) $r12Pct, 1) . '%' }}
                                        @endif
                                        @if($isOtd && !empty($otdR12['total']))
                                            <span class="kpi-cell-meta">({{ (int) $otdR12['total'] }})</span>
                                        @elseif($isFaiRej && !empty($faiRejR12['total']))
                                            <span class="kpi-cell-meta">({{ (int) ($faiRejR12['rejects'] ?? 0) }}/{{ (int) $faiRejR12['total'] }})</span>
                                        @endif
                                    </td>

                                    <td class="col-goal {{ $row['goal_class'] ?? '' }}">{{ $row['goal'] }}</td>
                                    <td class="col-trend {{ empty($row['trend']) ? 'kpi-empty' : '' }}">{{ $row['trend'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
                </div>

                <div class="col-12 col-lg-2 kpi-report-layout__side">
                    <div class="kpi-sidecards" aria-label="KPI summary">
                        <div class="info-box dashboard-kpi-box" data-accent="blue">
                            <span class="info-box-icon"><i class="fas fa-truck"></i></span>
                            <div class="info-box-content">
                                @php
                                    $pct = $otdYtd['pct'];
                                    $total = (int) $otdYtd['total'];
                                    $badge = $kpiBadge($pct ?? 0, $total);
                                    $delta = $pct !== null ? round($pct - $otdGoal, 1) : null;
                                @endphp
                                <div class="dashboard-kpi-top">
                                    <div class="dashboard-kpi-left">
                                        <div class="dashboard-kpi-label">OTD (YTD)</div>
                                        <div class="dashboard-kpi-value {{ $pct !== null ? $pctTone($pct) : '' }}">
                                            {{ $pct !== null ? number_format($pct, 1) . '%' : '-' }}
                                        </div>
                                        <div class="dashboard-kpi-meta">
                                            <span class="dashboard-kpi-goal">Goal {{ number_format($otdGoal, 0) }}%</span>
                                            <span class="dashboard-kpi-dot">&bull;</span>
                                            <span class="dashboard-kpi-delta {{ $delta !== null && $delta >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $delta !== null ? (($delta >= 0 ? '+' : '') . number_format($delta, 1) . ' pts') : '' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-right">
                                        <div class="dashboard-kpi-ratio">{{ (int) $otdYtd['on_time'] }} / {{ $total }}</div>
                                        <span class="kpi-badge {{ $badge['class'] }}">{{ $badge['text'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-box dashboard-kpi-box" data-accent="indigo">
                            <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                            <div class="info-box-content">
                                @php
                                    $pct = $otdThisMonth['pct'];
                                    $total = (int) $otdThisMonth['total'];
                                    $badge = $kpiBadge($pct ?? 0, $total);
                                    $delta = $pct !== null ? round($pct - $otdGoal, 1) : null;
                                @endphp
                                <div class="dashboard-kpi-top">
                                    <div class="dashboard-kpi-left">
                                        <div class="dashboard-kpi-label">OTD (Month)</div>
                                        <div class="dashboard-kpi-value {{ $pct !== null ? $pctTone($pct) : '' }}">
                                            {{ $pct !== null ? number_format($pct, 1) . '%' : '-' }}
                                        </div>
                                        <div class="dashboard-kpi-meta">
                                            <span class="dashboard-kpi-goal">Goal {{ number_format($otdGoal, 0) }}%</span>
                                            <span class="dashboard-kpi-dot">&bull;</span>
                                            <span class="dashboard-kpi-delta {{ $delta !== null && $delta >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $delta !== null ? (($delta >= 0 ? '+' : '') . number_format($delta, 1) . ' pts') : '' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-right">
                                        <div class="dashboard-kpi-ratio">{{ (int) $otdThisMonth['on_time'] }} / {{ $total }}</div>
                                        <span class="kpi-badge {{ $badge['class'] }}">{{ $badge['text'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-box dashboard-kpi-box" data-accent="blue">
                            <span class="info-box-icon"><i class="fas fa-history"></i></span>
                            <div class="info-box-content">
                                <div class="dashboard-kpi-top">
                                    <div class="dashboard-kpi-left">
                                        <div class="dashboard-kpi-label">OTD (R12)</div>
                                        <div class="dashboard-kpi-value">-</div>
                                        <div class="dashboard-kpi-meta">
                                            <span class="text-muted">Pending definition</span>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-right">
                                        <span class="kpi-badge kpi-badge--nodata">Pending</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-box dashboard-kpi-box" data-accent="green">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <div class="dashboard-kpi-top">
                                    <div class="dashboard-kpi-left">
                                        <div class="dashboard-kpi-label">Sent (Year)</div>
                                        <div class="dashboard-kpi-value">{{ (int) $sentThisMonth }}</div>
                                        <div class="dashboard-kpi-meta">
                                            <span class="text-muted">Due date year</span>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-right">
                                        <span class="kpi-badge kpi-badge--info">Sent</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-box dashboard-kpi-box" data-accent="slate">
                            <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
                            <div class="info-box-content">
                                <div class="dashboard-kpi-top">
                                    <div class="dashboard-kpi-left">
                                        <div class="dashboard-kpi-label">OTD (All Years)</div>
                                        <div class="dashboard-kpi-value">-</div>
                                        <div class="dashboard-kpi-meta">
                                            <span class="text-muted">Pending definition</span>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-right">
                                        <span class="kpi-badge kpi-badge--nodata">Pending</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-box dashboard-kpi-box" data-accent="amber">
                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                @php
                                    $pct = $faiRejYtd['pct'];
                                    $total = (int) $faiRejYtd['total'];
                                    $rejects = (int) ($faiRejYtd['rejects'] ?? 0);
                                    $badgeText = $total ? ($pct !== null && $pct <= $faiGoal ? 'On target' : 'Above goal') : 'No data';
                                    $badgeClass = !$total ? 'kpi-badge--nodata' : (($pct !== null && $pct <= $faiGoal) ? 'kpi-badge--good' : 'kpi-badge--bad');
                                @endphp
                                <div class="dashboard-kpi-top">
                                    <div class="dashboard-kpi-left">
                                        <div class="dashboard-kpi-label">FAI Rej (YTD)</div>
                                        <div class="dashboard-kpi-value {{ $pct !== null ? $pctToneLower($pct, $faiGoal) : '' }}">
                                            {{ $pct !== null ? number_format($pct, 1) . '%' : '-' }}
                                        </div>
                                        <div class="dashboard-kpi-meta">
                                            <span class="dashboard-kpi-goal">Goal {{ number_format($faiGoal, 0) }}%</span>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-right">
                                        <div class="dashboard-kpi-ratio">{{ $rejects }} / {{ $total }}</div>
                                        <span class="kpi-badge {{ $badgeClass }}">{{ $badgeText }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-box dashboard-kpi-box" data-accent="teal">
                            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <div class="dashboard-kpi-top">
                                    <div class="dashboard-kpi-left">
                                        <div class="dashboard-kpi-label">FAI Rej (R12)</div>
                                        <div class="dashboard-kpi-value">-</div>
                                        <div class="dashboard-kpi-meta">
                                            <span class="text-muted">Pending definition</span>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-right">
                                        <span class="kpi-badge kpi-badge--nodata">Pending</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- OTD details modal --}}
    <div class="modal fade dashboard-detail-modal" id="otdDetailModal" tabindex="-1" role="dialog" aria-labelledby="otdDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center w-100 pr-2 otd-modal-header-main">
                        <h5 class="modal-title mb-0 font-weight-bold" id="otdDetailModalLabel">
                            <i class="fas fa-truck-loading text-primary mr-1"></i> OTD Details
                        </h5>
                        <small class="text-muted mb-0 ml-2 text-nowrap" id="otdDetailMeta">Select a month.</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="otd-grid-shell">
                        <div class="d-flex align-items-center justify-content-between flex-wrap px-3 py-2 border-bottom">
                            <div class="btn-group btn-group-sm" role="group" aria-label="OTD filter">
                                <button type="button" class="btn btn-outline-secondary js-otd-filter" data-filter="all">All</button>
                                <button type="button" class="btn btn-outline-secondary js-otd-filter" data-filter="ontime">On time</button>
                                <button type="button" class="btn btn-outline-secondary js-otd-filter" data-filter="late">Late</button>
                            </div>
                            <div class="mx-2 my-1">
                                <div class="otd-search-box" style="min-width: 240px;">
                                    <span class="otd-search-icon" aria-hidden="true"><i class="fas fa-search"></i></span>
                                    <input
                                        type="text"
                                        id="otdDetailSearch"
                                        class="form-control form-control-sm"
                                        placeholder="Search..."
                                        aria-label="Search OTD details"
                                    >
                                    <button type="button" id="otdDetailSearchClear" class="otd-search-clear" aria-label="Clear search" title="Clear">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0 fai-dt-table">
                                <thead>
                                    <tr>
                                        <th class="text-center otd-col-workid">Work ID</th>
                                        <th class="text-center otd-col-pn">PN</th>
                                        <th>Part/Description</th>
                                        <th class="text-center otd-col-customer">Customer</th>
                                        <th class="text-center otd-col-due">Due</th>
                                        <th class="text-center otd-col-sent">Sent</th>
                                        <th class="text-center">Days</th>
                                    </tr>
                                </thead>
                                <tbody id="otdDetailTbody">
                                    <tr><td colspan="7" class="text-center text-muted py-3">Select a month.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="otdDetailPagination" class="d-flex align-items-center justify-content-between flex-wrap px-3 py-2 border-top"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- FAI rejection details modal --}}
    <div class="modal fade dashboard-detail-modal" id="faiRejDetailModal" tabindex="-1" role="dialog" aria-labelledby="faiRejDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center w-100 pr-2 otd-modal-header-main">
                        <h5 class="modal-title mb-0 font-weight-bold" id="faiRejDetailModalLabel">
                            <i class="fas fa-clipboard-check text-primary mr-1"></i> Internal FAI Rejection Details
                        </h5>
                        <small class="text-muted mb-0 ml-2 text-nowrap" id="faiRejDetailMeta">Select a month.</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="otd-grid-shell">
                        <div class="d-flex align-items-center justify-content-end flex-wrap px-3 py-2 border-bottom">
                            <div class="mx-2 my-1">
                                <div class="otd-search-box" style="min-width: 240px;">
                                    <span class="otd-search-icon" aria-hidden="true"><i class="fas fa-search"></i></span>
                                    <input
                                        type="text"
                                        id="faiRejDetailSearch"
                                        class="form-control form-control-sm"
                                        placeholder="Search..."
                                        aria-label="Search FAI rejection details"
                                    >
                                    <button type="button" id="faiRejDetailSearchClear" class="otd-search-clear" aria-label="Clear search" title="Clear">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0 fai-dt-table">
                                <thead>
                                    <tr>
                                        <th class="text-left fai-col-workid">Work ID</th>
                                        <th class="text-left fai-col-pn">PN</th>
                                        <th class="fai-col-desc">Part/Description</th>
                                        <th class="text-left fai-col-customer">Customer</th>
                                        <th class="text-center fai-col-due">Due</th>
                                        <th class="text-center fai-col-sent">Sent</th>
                                        <th class="text-left fai-col-failops">Fail Ops</th>
                                    </tr>
                                </thead>
                                <tbody id="faiRejDetailTbody">
                                    <tr><td colspan="7" class="text-center text-muted py-3">Select a month.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="faiRejDetailPagination" class="d-flex align-items-center justify-content-between flex-wrap px-3 py-2 border-top"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        window.__DASHBOARD = window.__DASHBOARD || {};
        window.__DASHBOARD.year = {{ (int) $dashboardYear }};
        window.__DASHBOARD.otdDetailsUrl = @json(route('dashboard.otdDetails'));
        window.__DASHBOARD.faiRejDetailsUrl = @json(route('dashboard.faiRejDetails'));
    </script>
    <script src="{{ asset('vendor/js/dashboard.js?v=' . filemtime(public_path('vendor/js/dashboard.js'))) }}"></script>
@stop
