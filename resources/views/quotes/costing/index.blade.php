@extends('adminlte::page')

@section('title', 'Costing')

@section('css')
    <style>
        :root {
            --sp-1: 4px;
            --sp-2: 8px;
            --sp-3: 12px;
            --sp-4: 16px;
        }

        .costing-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .costing-kpi-card {
            position: relative;
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            background: #fff;
            border: 1px solid #d6dde6;
            border-radius: 14px;
            padding: 1rem 1rem 1rem 1.05rem;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .costing-kpi-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: #cbd5e1;
        }

        .costing-kpi-card.is-blue::before {
            background: #3b82f6;
        }

        .costing-kpi-card.is-green::before {
            background: #22c55e;
        }

        .costing-kpi-card.is-amber::before {
            background: #f59e0b;
        }

        .costing-kpi-card.is-slate::before {
            background: #64748b;
        }

        .costing-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
        }

        .costing-kpi-card.is-blue .costing-kpi-icon {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .costing-kpi-card.is-green .costing-kpi-icon {
            background: #dcfce7;
            color: #15803d;
        }

        .costing-kpi-card.is-amber .costing-kpi-icon {
            background: #fef3c7;
            color: #b45309;
        }

        .costing-kpi-card.is-slate .costing-kpi-icon {
            background: #e2e8f0;
            color: #475569;
        }

        .costing-kpi-copy {
            min-width: 0;
        }

        .costing-kpi-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #617182;
            margin-bottom: 0.3rem;
        }

        .costing-kpi-value {
            font-size: 1.72rem;
            font-weight: 800;
            line-height: 1;
            color: #17212b;
        }

        .fai-layout-row {
            margin-bottom: var(--sp-2) !important;
        }

        .fai-global-search-wrap {
            width: 100%;
        }

        .fai-global-search {
            max-width: 360px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #cfd8e3;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
            padding: 0 12px;
        }

        .costing-search-clear {
            border: 0;
            background: transparent;
            color: #94a3b8;
            padding: 0;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            cursor: pointer;
            opacity: 0;
            pointer-events: none;
            transition: color 0.15s ease, opacity 0.15s ease;
        }

        .costing-search-clear.is-visible {
            opacity: 1;
            pointer-events: auto;
        }

        .costing-search-clear:hover,
        .costing-search-clear:focus {
            color: #475569;
            outline: none;
        }

        .fai-global-search .input-group-text {
            border: 0;
            background: transparent;
            color: #64748b;
            padding: 0;
        }

        .fai-global-search .form-control {
            border: 0;
            font-weight: 600;
            box-shadow: none;
            padding: 0.62rem 0;
            height: auto;
            font-size: 1rem;
        }

        .fai-global-search .form-control:focus {
            box-shadow: none;
            background: transparent;
        }

        .fai-global-search:hover,
        .fai-global-search:focus-within {
            border-color: #b7c7d8;
            box-shadow: 0 3px 12px rgba(16, 24, 40, 0.08);
        }

        #costingSearchForm input[type="search"]::-webkit-search-cancel-button {
            -webkit-appearance: none;
            appearance: none;
            display: none;
        }

        .fai-card {
            border: 1px solid rgba(15, 23, 42, 0.10) !important;
            width: 100%;
            height: 100%;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
        }

        .fai-compact-body {
            padding: var(--sp-3) var(--sp-3) var(--sp-2);
            margin-bottom: 0;
        }

        .card-title-mini {
            font-size: .95rem;
            font-weight: 700;
            margin-bottom: var(--sp-2);
            display: flex;
            align-items: center;
            gap: var(--sp-2);
            flex-wrap: wrap;
            padding-bottom: var(--sp-1);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .fai-card-title {
            justify-content: space-between;
            margin-bottom: var(--sp-1);
            padding-bottom: var(--sp-1);
        }

        .fai-title-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.10);
        }

        .fai-title-text {
            font-size: 1.12rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .fai-title-sub {
            font-size: 0.96rem;
            line-height: 1.15;
        }

        .fai-chip {
            border-radius: 999px;
            padding: var(--sp-1) var(--sp-2);
            font-weight: 700;
            border: 1px solid #d5d8dd;
            background: #f8fafc;
            color: #334155;
            font-size: 0.94rem;
        }

        .costing-pagination-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .costing-results-summary {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            font-size: 0.9rem;
            font-weight: 700;
            color: #617182;
        }

        .costing-pagination {
            display: flex;
            justify-content: flex-end;
        }

        .costing-pagination .pagination {
            margin: 0;
            gap: 4px;
        }

        .costing-pagination .page-item {
            border: 1px solid rgba(15, 23, 42, 0.18);
            background: rgba(241, 245, 249, 0.95);
            color: #0f172a;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
            transition: background-color .12s ease, transform .08s ease, box-shadow .12s ease;
            border-radius: 8px;
            overflow: hidden;
        }

        .costing-pagination .page-link {
            padding: 0.34rem 0.68rem;
            font-size: 1rem;
            line-height: 1.4;
            border: 0;
            background: transparent;
            color: inherit;
            border-radius: 8px;
        }

        .costing-pagination .page-item:hover {
            background: rgba(226, 232, 240, 1);
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(16, 24, 40, 0.10);
        }

        .costing-pagination .page-link:focus {
            box-shadow: none;
            outline: none;
        }

        .costing-pagination .page-item.active .page-link {
            background: transparent;
            color: #fff;
        }

        .costing-pagination .page-item.active {
            background: #0b5ed7;
            border-color: #0b5ed7;
            color: #fff;
            font-weight: 700;
        }

        .costing-pagination .page-item.disabled {
            color: #94a3b8;
            background: #f8fafc;
            border-color: #e2e8f0;
            box-shadow: none;
            transform: none;
        }

        .costing-pagination .page-item.disabled .page-link {
            color: inherit;
            cursor: default;
        }

        .costing-results {
            position: relative;
        }

        .costing-results.is-loading {
            opacity: 0.55;
            pointer-events: none;
        }

        .fai-table-shell {
            min-height: 220px;
            overflow-x: auto;
            overflow-y: hidden;
            background: transparent;
        }

        .costing-table-tools {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.75rem;
            padding: 0 0 0.75rem;
            flex-wrap: wrap;
        }

        .costing-table-search {
            max-width: 360px;
            width: 100%;
        }

        .costing-results-summary {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin: 0 0 0.7rem;
            font-size: 0.9rem;
            font-weight: 700;
            color: #617182;
        }

        .fai-dt-table {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #d5d8dd;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: auto;
        }

        .fai-dt-table thead th,
        .fai-dt-table tbody td {
            border-top: 0;
            border-left: 0;
            border-right: 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .fai-dt-table thead th {
            white-space: normal;
            font-size: 0.94rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            color: #0b0b0b !important;
            padding: 0.82rem var(--sp-3);
            background: linear-gradient(180deg, #edf3f9 0%, #dde7f2 100%);
            border-bottom: 1px solid rgba(15, 23, 42, 0.16);
            vertical-align: middle;
            box-shadow:
                inset 0 -2px 0 rgba(15, 23, 42, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.65);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .fai-dt-table tbody td {
            font-size: 0.94rem;
            color: #0f172a;
            vertical-align: middle;
            padding: var(--sp-2) var(--sp-2);
        }

        .fai-dt-table tbody tr:nth-child(odd) {
            background: #fff !important;
        }

        .fai-dt-table tbody tr:nth-child(even) {
            background: rgba(248, 250, 252, 0.85) !important;
        }

        .fai-dt-table tbody tr:hover {
            background: rgba(2, 6, 23, 0.04) !important;
        }

        .fai-summary-table {
            margin-bottom: 0;
        }

        .fai-summary-table th:nth-child(1),
        .fai-summary-table td:nth-child(1) {
            width: 78px;
        }

        .fai-summary-table th:nth-child(2),
        .fai-summary-table td:nth-child(2) {
            width: 30%;
        }

        .fai-summary-table th:nth-child(3),
        .fai-summary-table td:nth-child(3) {
            width: 22%;
            text-align: center;
        }

        .fai-summary-table th:nth-child(4),
        .fai-summary-table td:nth-child(4) {
            width: 20%;
        }

        .fai-summary-table th:nth-child(5),
        .fai-summary-table td:nth-child(5) {
            width: 20%;
            text-align: center;
        }

        .fai-summary-table tbody td {
            padding-top: 0.72rem;
            padding-bottom: 0.72rem;
        }

        .costing-pn-cell {
            position: relative;
            padding-left: 1.05rem !important;
        }

        .costing-pn-cell::before {
            content: "";
            position: absolute;
            left: 0.45rem;
            top: 50%;
            width: 5px;
            height: 24px;
            border-radius: 999px;
            background: transparent;
            transform: translateY(-50%);
        }

        .costing-row-has-costing .costing-pn-cell::before {
            background: #8fd19e;
        }

        .costing-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.88rem;
            font-weight: 800;
            background: rgba(15, 95, 143, 0.12);
            color: #0a4366;
        }

        .costing-diff-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            padding: 0.28rem 0.6rem;
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 800;
            line-height: 1;
        }

        .costing-diff-pill.is-positive {
            background: #dcfce7;
            color: #166534;
        }

        .costing-diff-pill.is-negative {
            background: #fee2e2;
            color: #b91c1c;
        }

        .costing-cost-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            padding: 0.28rem 0.6rem;
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 800;
            line-height: 1;
            background: #fef3c7;
            color: #92400e;
        }

        .costing-toggle-btn {
            border-radius: 999px;
            border: 1px solid rgba(15, 95, 143, 0.22);
            color: #0f5f8f;
            background: #fff;
            font-weight: 700;
            min-width: 48px;
            min-height: 38px;
        }

        .costing-open-cell {
            text-align: left;
            padding-left: 0.95rem !important;
        }

        .costing-toggle-btn:hover,
        .costing-toggle-btn:focus {
            background: rgba(15, 95, 143, 0.06);
            color: #0a4366;
        }

        .costing-muted {
            color: #617182;
        }

        .costing-note-trigger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 88px;
            padding: 0.32rem 0.68rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            line-height: 1;
            border: 1px solid #fecaca;
            background: #fee2e2;
            color: #b91c1c;
            cursor: pointer;
        }

        .costing-note-trigger:hover {
            background: #fecaca;
            color: #991b1b;
        }

        .costing-notes-modal .modal-content {
            border: 1px solid #cfe0f5;
            border-radius: 10px;
            overflow: hidden;
        }

        .costing-notes-modal .modal-header {
            background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
            border-bottom: 1px solid #dbe7f5;
        }

        .costing-notes-modal .modal-body {
            background: #f8fbff;
            padding: 16px;
        }

        .costing-note-card {
            border: 1px solid #dbe7f5;
            border-radius: 10px;
            background: #fff;
            padding: 12px 14px;
            margin-bottom: 12px;
        }

        .costing-note-card:last-child {
            margin-bottom: 0;
        }

        .costing-note-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .costing-note-card-work {
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
        }

        .costing-note-card-date {
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
        }

        .costing-note-card-body {
            font-size: 0.9rem;
            color: #334155;
            white-space: pre-wrap;
        }

        .costing-detail-row td {
            background: #f7fafc;
            border-top: 0;
        }

        .costing-row-has-costing td {
            background: #e8f7ee !important;
        }

        .costing-row-has-costing:hover td {
            background: #dff1e7 !important;
        }

        .costing-detail-table {
            margin-bottom: 0;
            table-layout: fixed;
            width: 100%;
            border: 1px solid #d6dde6;
            border-radius: 10px;
            overflow: hidden;
        }

        .costing-detail-table.has-tree-column th:nth-child(1),
        .costing-detail-table.has-tree-column td:nth-child(1) { width: 66px; }
        .costing-detail-table.has-tree-column th:nth-child(2),
        .costing-detail-table.has-tree-column td:nth-child(2) { width: 110px; }
        .costing-detail-table.has-tree-column th:nth-child(3),
        .costing-detail-table.has-tree-column td:nth-child(3) { width: 88px; }
        .costing-detail-table.has-tree-column th:nth-child(4),
        .costing-detail-table.has-tree-column td:nth-child(4) { width: 92px; }
        .costing-detail-table.has-tree-column th:nth-child(5),
        .costing-detail-table.has-tree-column td:nth-child(5) { width: 118px; }
        .costing-detail-table.has-tree-column th:nth-child(6),
        .costing-detail-table.has-tree-column td:nth-child(6) { width: 300px; }
        .costing-detail-table.has-tree-column th:nth-child(7),
        .costing-detail-table.has-tree-column td:nth-child(7) { width: 96px; }
        .costing-detail-table.has-tree-column th:nth-child(8),
        .costing-detail-table.has-tree-column td:nth-child(8) { width: 70px; }
        .costing-detail-table.has-tree-column th:nth-child(9),
        .costing-detail-table.has-tree-column td:nth-child(9) { width: 78px; }
        .costing-detail-table.has-tree-column th:nth-child(10),
        .costing-detail-table.has-tree-column td:nth-child(10) { width: 86px; }
        .costing-detail-table.has-tree-column th:nth-child(11),
        .costing-detail-table.has-tree-column td:nth-child(11) { width: 112px; }
        .costing-detail-table.has-tree-column th:nth-child(12),
        .costing-detail-table.has-tree-column td:nth-child(12) { width: 112px; }
        .costing-detail-table.has-tree-column th:nth-child(13),
        .costing-detail-table.has-tree-column td:nth-child(13) { width: 114px; }
        .costing-detail-table.has-tree-column th:nth-child(14),
        .costing-detail-table.has-tree-column td:nth-child(14) { width: 110px; }
        .costing-detail-table.has-tree-column th:nth-child(15),
        .costing-detail-table.has-tree-column td:nth-child(15) { width: 96px; }
        .costing-detail-table.has-tree-column th:nth-child(16),
        .costing-detail-table.has-tree-column td:nth-child(16) { width: 92px; }

        .costing-detail-table.no-tree-column th:nth-child(1),
        .costing-detail-table.no-tree-column td:nth-child(1) { width: 110px; }
        .costing-detail-table.no-tree-column th:nth-child(2),
        .costing-detail-table.no-tree-column td:nth-child(2) { width: 88px; }
        .costing-detail-table.no-tree-column th:nth-child(3),
        .costing-detail-table.no-tree-column td:nth-child(3) { width: 92px; }
        .costing-detail-table.no-tree-column th:nth-child(4),
        .costing-detail-table.no-tree-column td:nth-child(4) { width: 118px; }
        .costing-detail-table.no-tree-column th:nth-child(5),
        .costing-detail-table.no-tree-column td:nth-child(5) { width: 300px; }
        .costing-detail-table.no-tree-column th:nth-child(6),
        .costing-detail-table.no-tree-column td:nth-child(6) { width: 96px; }
        .costing-detail-table.no-tree-column th:nth-child(7),
        .costing-detail-table.no-tree-column td:nth-child(7) { width: 70px; }
        .costing-detail-table.no-tree-column th:nth-child(8),
        .costing-detail-table.no-tree-column td:nth-child(8) { width: 78px; }
        .costing-detail-table.no-tree-column th:nth-child(9),
        .costing-detail-table.no-tree-column td:nth-child(9) { width: 86px; }
        .costing-detail-table.no-tree-column th:nth-child(10),
        .costing-detail-table.no-tree-column td:nth-child(10) { width: 112px; }
        .costing-detail-table.no-tree-column th:nth-child(11),
        .costing-detail-table.no-tree-column td:nth-child(11) { width: 112px; }
        .costing-detail-table.no-tree-column th:nth-child(12),
        .costing-detail-table.no-tree-column td:nth-child(12) { width: 114px; }
        .costing-detail-table.no-tree-column th:nth-child(13),
        .costing-detail-table.no-tree-column td:nth-child(13) { width: 110px; }
        .costing-detail-table.no-tree-column th:nth-child(14),
        .costing-detail-table.no-tree-column td:nth-child(14) { width: 96px; }
        .costing-detail-table.no-tree-column th:nth-child(15),
        .costing-detail-table.no-tree-column td:nth-child(15) { width: 92px; }

        .costing-detail-table thead th {
            background: #eff5fa;
            border-top: 0;
            border-bottom: 1px solid #c3ceda;
            font-size: 0.84rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .costing-detail-table thead th:nth-child(6),
        .costing-detail-table.no-tree-column thead th:nth-child(5) {
            text-align: left;
        }

        .costing-detail-table .costing-col-num {
            text-align: right;
        }

        .costing-detail-table.has-tree-column th:nth-child(10),
        .costing-detail-table.has-tree-column td:nth-child(10),
        .costing-detail-table.has-tree-column th:nth-child(11),
        .costing-detail-table.has-tree-column td:nth-child(11),
        .costing-detail-table.has-tree-column th:nth-child(12),
        .costing-detail-table.has-tree-column td:nth-child(12),
        .costing-detail-table.has-tree-column th:nth-child(13),
        .costing-detail-table.has-tree-column td:nth-child(13),
        .costing-detail-table.no-tree-column th:nth-child(8),
        .costing-detail-table.no-tree-column td:nth-child(8),
        .costing-detail-table.no-tree-column th:nth-child(9),
        .costing-detail-table.no-tree-column td:nth-child(9),
        .costing-detail-table.no-tree-column th:nth-child(10),
        .costing-detail-table.no-tree-column td:nth-child(10),
        .costing-detail-table.no-tree-column th:nth-child(11),
        .costing-detail-table.no-tree-column td:nth-child(11),
        .costing-detail-table.no-tree-column th:nth-child(12),
        .costing-detail-table.no-tree-column td:nth-child(12) {
            text-align: center !important;
        }

        .costing-due-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 104px;
            padding: 0.36rem 0.72rem;
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 800;
            line-height: 1;
            border: 1px solid #d5dde7;
            background: #f3f4f6;
            color: #111827;
        }

        .costing-detail-table td {
            white-space: nowrap;
            font-size: 0.94rem;
        }

        .costing-detail-table tbody tr.costing-kit-header-row td {
            background: #eef6ff !important;
            border-top: 1px solid #cfe0f5;
            border-bottom: 1px solid #cfe0f5;
        }

        .costing-kit-toggle-row {
            cursor: pointer;
        }

        .costing-detail-table tbody tr.costing-kit-child-row td {
            background: #fbfdff !important;
        }

        .costing-kit-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            padding: 0.18rem 0.42rem;
            margin-right: 0.45rem;
            border-radius: 999px;
            border: 1px solid #cfe0f5;
            background: #eef6ff;
            color: #1d4ed8;
            font-size: 0.72rem;
            font-weight: 800;
            line-height: 1;
            vertical-align: middle;
        }

        .costing-tree-cell {
            text-align: left;
            padding-left: 0.55rem !important;
            white-space: nowrap;
        }

        .costing-kit-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.08rem;
            border: 0;
            background: transparent;
            padding: 0;
            cursor: pointer;
            white-space: nowrap;
        }

        .costing-kit-chevron {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            margin-left: -1px;
            border-radius: 999px;
            color: #64748b;
            font-size: 0.7rem;
            transition: transform 0.16s ease, color 0.16s ease, background-color 0.16s ease;
        }

        .costing-kit-toggle-row.is-open .costing-kit-chevron {
            transform: rotate(90deg);
            color: #0f5f8f;
            background: rgba(15, 95, 143, 0.08);
        }

        .costing-kit-header-row .costing-workid-cell {
            font-weight: 900;
        }

        .costing-kit-branch {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            margin-right: 0.3rem;
            color: #64748b;
            font-size: 0.82rem;
            vertical-align: middle;
        }

        .costing-description-cell {
            min-width: 290px;
            max-width: 380px;
            white-space: normal !important;
            line-height: 1.3;
            text-align: left;
        }

        .costing-workid-cell,
        .costing-pn-detail-cell {
            font-weight: 800;
            color: #0f172a;
        }

        .costing-pn-detail-cell {
            white-space: normal !important;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.25;
        }

        .costing-customer-cell {
            color: #475569;
            font-weight: 600;
        }

        .costing-operation-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            padding: 0.28rem 0.52rem;
            border-radius: 999px;
            background: #eef2f7;
            border: 1px solid #d8e1eb;
            color: #334155;
            font-size: 0.88rem;
            font-weight: 800;
            line-height: 1;
        }

        .costing-detail-actions {
            display: inline-grid;
            grid-template-columns: repeat(2, 36px);
            gap: 8px;
            justify-content: center;
        }

        .costing-edit-btn {
            min-width: 36px;
            width: 36px;
            height: 36px;
            padding: 0;
            border-radius: 8px;
        }

        .erp-table-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0;
            border-radius: 8px;
            border: 1px solid #d7e3f0;
            box-shadow: 0 1px 1px rgba(16, 24, 40, 0.04);
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .erp-table-btn i {
            font-size: 0.95rem;
        }

        .erp-table-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(16, 24, 40, 0.12);
        }

        .btn-erp-primary {
            background: #ffffff;
            border-color: #d7e3f0;
            color: #0d6efd;
        }

        .btn-erp-primary:hover,
        .btn-erp-primary:focus {
            background: #f7fbff;
            border-color: #c6d8eb;
            color: #0b5ed7;
        }

        .costing-action-pdf {
            color: #2563eb;
        }

        .costing-action-edit {
            color: #f59e0b;
        }

        @media (max-width: 767.98px) {
            .fai-card-title {
                align-items: flex-start;
                gap: var(--sp-2);
            }
        }
    </style>
@stop

@section('content')
    @php
        $pnCount = $summary['pn_count'] ?? $pnOrders->total();
        $orderCount = $summary['order_count'] ?? $pnOrders->getCollection()->sum('total_orders');
        $latestCostingDate = $summary['latest_costing_date'] ?? null;
        $costedPnCount = $summary['costed_pn_count'] ?? $pnOrders->getCollection()->filter(fn ($item) => !empty($item->has_costing))->count();
        $notesPnCount = $summary['notes_pn_count'] ?? $pnOrders->getCollection()->filter(fn ($item) => (int) ($item->notes_count ?? 0) > 0)->count();
    @endphp

    <div class="costing-kpi-grid">
        <div class="costing-kpi-card is-blue">
            <span class="costing-kpi-icon">
                <i class="fas fa-layer-group"></i>
            </span>
            <div class="costing-kpi-copy">
                <span class="costing-kpi-label">Part Numbers</span>
                <span class="costing-kpi-value" id="costingKpiPn">{{ number_format($pnCount) }}</span>
            </div>
        </div>
        <div class="costing-kpi-card is-green">
            <span class="costing-kpi-icon">
                <i class="fas fa-calculator"></i>
            </span>
            <div class="costing-kpi-copy">
                <span class="costing-kpi-label">PN With Costing</span>
                <span class="costing-kpi-value" id="costingKpiCosted">{{ number_format($costedPnCount) }}</span>
            </div>
        </div>
        <div class="costing-kpi-card is-amber">
            <span class="costing-kpi-icon">
                <i class="fas fa-sticky-note"></i>
            </span>
            <div class="costing-kpi-copy">
                <span class="costing-kpi-label">PN With Notes</span>
                <span class="costing-kpi-value" id="costingKpiNotes">{{ number_format($notesPnCount) }}</span>
            </div>
        </div>
        <div class="costing-kpi-card is-slate">
            <span class="costing-kpi-icon">
                <i class="fas fa-calendar-alt"></i>
            </span>
            <div class="costing-kpi-copy">
                <span class="costing-kpi-label">Latest Costing Date</span>
                <span class="costing-kpi-value" id="costingKpiLatestDue" style="font-size: 1.1rem;">
                    {{ $latestCostingDate ? \Carbon\Carbon::parse($latestCostingDate)->format('M-d-Y') : 'N/A' }}
                </span>
            </div>
        </div>
    </div>

    <div class="row mx-0">
        <div class="col-12 px-0">
            <div class="card shadow-sm rounded-3 fai-card">
                <div class="card-body fai-compact-body">
                    <div class="card-title-mini fai-card-title">
                        <div class="d-flex align-items-center">
                            <span class="fai-title-icon bg-success text-white">
                                <i class="fas fa-file-invoice"></i>
                            </span>
                            <div class="ml-2">
                                <div class="fai-title-text">Costing</div>
                                <small class="text-muted fai-title-sub">PN breakdown with order detail</small>
                            </div>
                        </div>
                        <span id="costingRecordCount" class="btn fai-chip ml-auto" style="pointer-events:none;">
                            Total {{ $pnOrders->total() }}
                        </span>
                    </div>

                    <div class="costing-table-tools">
                        <form method="GET" action="{{ route('costing') }}" class="costing-table-search" id="costingSearchForm">
                            <div class="fai-global-search">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input
                                    name="search"
                                    type="search"
                                    class="form-control"
                                    placeholder="Search..."
                                    autocomplete="off"
                                    value="{{ $search }}"
                                >
                                <button type="button" class="costing-search-clear {{ $search !== '' ? 'is-visible' : '' }}" id="costingSearchClear" aria-label="Clear search" title="Clear search">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="costingResults" class="costing-results">
                        @include('quotes.costing._results', ['pnOrders' => $pnOrders])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade costing-notes-modal" id="costingNotesModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="costingNotesModalTitle">PN Notes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="costingNotesModalBody"></div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function () {
            let activeRequest = null;
            let searchDebounce = null;
            const $results = $('#costingResults');
            const $searchForm = $('#costingSearchForm');
            const $searchInput = $searchForm.find('input[name="search"]');
            const $searchClear = $('#costingSearchClear');

            function syncSearchClear() {
                $searchClear.toggleClass('is-visible', $.trim($searchInput.val()) !== '');
            }

            function bindResultEvents() {
                const $summaryData = $results.find('[data-total-records]');
                const totalRecords = $summaryData.data('total-records');
                if (typeof totalRecords !== 'undefined') {
                    $('#costingRecordCount').text(`Total ${totalRecords}`);
                }

                if ($summaryData.length) {
                    $('#costingKpiPn').text(Number($summaryData.data('summary-pn') || 0).toLocaleString());
                    $('#costingKpiCosted').text(Number($summaryData.data('summary-costed') || 0).toLocaleString());
                    $('#costingKpiNotes').text(Number($summaryData.data('summary-notes') || 0).toLocaleString());
                    $('#costingKpiLatestDue').text($summaryData.data('summary-latest-due') || 'N/A');
                }

                $results.find('.pagination a').off('click.costing').on('click.costing', function (event) {
                    event.preventDefault();
                    loadResults($(this).attr('href'), { syncUrl: false });
                });
            }

            function loadResults(url, options = {}) {
                if (!url) {
                    return;
                }

                const settings = {
                    syncUrl: true,
                    ...options
                };

                if (activeRequest) {
                    activeRequest.abort();
                }

                $results.addClass('is-loading');

                activeRequest = $.ajax({
                    url: url,
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function (html) {
                    $results.html(html);
                    if (settings.syncUrl) {
                        const nextUrl = new URL(url, window.location.origin);
                        nextUrl.searchParams.delete('page');
                        window.history.replaceState({}, '', nextUrl.toString());
                    } else {
                        window.history.replaceState({}, '', $searchForm.attr('action'));
                    }
                    bindResultEvents();
                }).always(function () {
                    $results.removeClass('is-loading');
                    activeRequest = null;
                });
            }

            $searchForm.on('submit', function (event) {
                event.preventDefault();
                const query = $searchForm.serialize();
                const url = `${$searchForm.attr('action')}?${query}`;
                loadResults(url, { syncUrl: false });
            });

            $searchInput.on('input', function () {
                syncSearchClear();
                clearTimeout(searchDebounce);

                searchDebounce = setTimeout(function () {
                    const query = $searchForm.serialize();
                    const url = `${$searchForm.attr('action')}?${query}`;
                    loadResults(url, { syncUrl: false });
                }, 300);
            });

            $searchClear.on('click', function () {
                $searchInput.val('');
                syncSearchClear();
                loadResults($searchForm.attr('action'), { syncUrl: false });
                $searchInput.trigger('focus');
            });

            $(document).on('click', '.toggle-detail', function () {
                const target = $(this).data('target');
                const $detailRow = $(target);
                const isHidden = $detailRow.hasClass('d-none');

                $detailRow.toggleClass('d-none', !isHidden);
                $(this).attr('aria-expanded', isHidden ? 'true' : 'false');
                $(this).find('.label-show').toggleClass('d-none', isHidden);
                $(this).find('.label-hide').toggleClass('d-none', !isHidden);
            });

            $(document).on('click', '.costing-kit-toggle-row', function (event) {
                if ($(event.target).closest('a').length) {
                    return;
                }

                const targetSelector = $(this).data('kit-target');
                if (!targetSelector) {
                    return;
                }

                const $children = $(this).closest('tbody').find(targetSelector);
                const shouldOpen = $children.first().hasClass('d-none');

                $children.toggleClass('d-none', !shouldOpen);
                $(this).toggleClass('is-open', shouldOpen);
                $(this).attr('aria-expanded', shouldOpen ? 'true' : 'false');
            });

            $(document).on('click', '.js-costing-notes-trigger', function () {
                const pn = $(this).data('pn');
                const notes = $(this).data('notes') || [];
                const $body = $('#costingNotesModalBody');

                $('#costingNotesModalTitle').text(`Notes - ${pn}`);

                if (!notes.length) {
                    $body.html('<div class="text-center text-muted py-4">No notes available.</div>');
                    $('#costingNotesModal').modal('show');
                    return;
                }

                const html = notes.map(function (item) {
                    return `
                        <div class="costing-note-card">
                            <div class="costing-note-card-head">
                                <div class="costing-note-card-work">${item.work_id || 'N/A'}</div>
                                <div class="costing-note-card-date">${item.date || 'N/A'}</div>
                            </div>
                            <div class="costing-note-card-body">${$('<div>').text(item.note || '').html()}</div>
                        </div>
                    `;
                }).join('');

                $body.html(html);
                $('#costingNotesModal').modal('show');
            });

            bindResultEvents();
            syncSearchClear();
        });
    </script>
@stop
