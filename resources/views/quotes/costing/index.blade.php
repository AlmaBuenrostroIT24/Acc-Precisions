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

        .costing-hero {
            background:
                radial-gradient(circle at right top, rgba(217, 143, 43, 0.18), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f5f8fb 100%);
            border: 1px solid rgba(15, 95, 143, 0.12);
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            padding: 1.25rem 1.35rem;
            margin-bottom: 1rem;
        }

        .costing-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.85rem;
        }

        .costing-kpi-card {
            background: #fff;
            border: 1px solid #d6dde6;
            border-radius: 12px;
            padding: 0.95rem 1rem;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .costing-kpi-label {
            display: block;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #617182;
            margin-bottom: 0.35rem;
        }

        .costing-kpi-value {
            font-size: 1.45rem;
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
        }

        .fai-global-search .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 1px solid #d5d8dd;
            background: #eef2f7;
            color: #334155;
        }

        .fai-global-search .form-control {
            border-top: 1px solid #d5d8dd;
            border-bottom: 1px solid #d5d8dd;
            border-left: 0;
            border-right: 0;
            font-weight: 600;
        }

        .fai-global-search .form-control,
        .fai-global-search .btn,
        .fai-global-search .input-group-text {
            height: 36px;
        }

        .fai-global-search .btn {
            border: 1px solid #d5d8dd;
        }

        .fai-global-search .btn-secondary {
            background: #e2e8f0;
            border-color: #d5d8dd;
            color: #334155;
        }

        .fai-global-search .btn-secondary:hover,
        .fai-global-search .btn-secondary:focus {
            background: #cbd5e1;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .fai-global-search .input-group-append .btn:last-child {
            border-radius: 0 10px 10px 0;
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
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .fai-title-sub {
            font-size: 0.9rem;
            line-height: 1.15;
        }

        .fai-chip {
            border-radius: 999px;
            padding: var(--sp-1) var(--sp-2);
            font-weight: 700;
            border: 1px solid #d5d8dd;
            background: #f8fafc;
            color: #334155;
        }

        .costing-pagination {
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
        }

        .costing-pagination .pagination {
            margin-bottom: 0;
        }

        .costing-pagination .page-link {
            color: #0f5f8f;
            border-color: #d6dde6;
        }

        .costing-pagination .page-item.active .page-link {
            background-color: #0f5f8f;
            border-color: #0f5f8f;
        }

        .costing-pagination .page-item.disabled .page-link {
            color: #94a3b8;
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
            font-size: 0.86rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            color: #0b0b0b !important;
            padding: var(--sp-2) var(--sp-3);
            background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
            border-bottom: 1px solid rgba(15, 23, 42, 0.12);
            vertical-align: middle;
            box-shadow: inset 0 -2px 0 rgba(15, 23, 42, 0.06);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .fai-dt-table tbody td {
            font-size: 0.85rem;
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

        .costing-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            background: rgba(15, 95, 143, 0.12);
            color: #0a4366;
        }

        .costing-toggle-btn {
            border-radius: 999px;
            border: 1px solid rgba(15, 95, 143, 0.22);
            color: #0f5f8f;
            background: #fff;
            font-weight: 700;
            min-width: 42px;
        }

        .costing-toggle-btn:hover,
        .costing-toggle-btn:focus {
            background: rgba(15, 95, 143, 0.06);
            color: #0a4366;
        }

        .costing-muted {
            color: #617182;
        }

        .costing-detail-row td {
            background: #f7fafc;
            border-top: 0;
        }

        .costing-detail-panel {
            background: #ffffff;
            border: 1px solid #d6dde6;
            border-radius: 14px;
            padding: 0.85rem;
        }

        .costing-detail-table {
            margin-bottom: 0;
        }

        .costing-detail-table thead th {
            background: #eff5fa;
            border-top: 0;
            border-bottom: 1px solid #c3ceda;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .costing-detail-table td {
            white-space: nowrap;
        }

        .costing-description-cell {
            min-width: 260px;
            white-space: normal !important;
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
        $pnCount = $pnOrders->total();
        $orderCount = $pnOrders->getCollection()->sum('total_orders');
        $latestDueDate = $pnOrders->getCollection()->pluck('latest_due_date')->filter()->sortDesc()->first();
    @endphp

    <div class="costing-hero">
        <div class="costing-kpi-grid">
            <div class="costing-kpi-card">
                <span class="costing-kpi-label">Part Numbers</span>
                <span class="costing-kpi-value">{{ number_format($pnCount) }}</span>
            </div>
            <div class="costing-kpi-card">
                <span class="costing-kpi-label">Orders On Page</span>
                <span class="costing-kpi-value">{{ number_format($orderCount) }}</span>
            </div>
            <div class="costing-kpi-card">
                <span class="costing-kpi-label">Latest Due Date</span>
                <span class="costing-kpi-value" style="font-size: 1.05rem;">
                    {{ $latestDueDate ? \Carbon\Carbon::parse($latestDueDate)->format('Y-m-d') : 'N/A' }}
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
                            <div class="input-group fai-global-search">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input
                                    name="search"
                                    type="search"
                                    class="form-control"
                                    placeholder="Search PN, customer, work_id..."
                                    autocomplete="off"
                                    value="{{ $search }}"
                                >
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-secondary">Search</button>
                                    @if($search !== '')
                                        <a href="{{ route('costing') }}" class="btn btn-outline-secondary">Clear</a>
                                    @endif
                                </div>
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
@stop

@section('js')
    <script>
        $(function () {
            let activeRequest = null;
            let searchDebounce = null;
            const $results = $('#costingResults');
            const $searchForm = $('#costingSearchForm');
            const $searchInput = $searchForm.find('input[name="search"]');

            function bindResultEvents() {
                const totalRecords = $results.find('[data-total-records]').data('total-records');
                if (typeof totalRecords !== 'undefined') {
                    $('#costingRecordCount').text(`Total ${totalRecords}`);
                }

                $results.find('.pagination a').off('click.costing').on('click.costing', function (event) {
                    event.preventDefault();
                    loadResults($(this).attr('href'));
                });
            }

            function loadResults(url) {
                if (!url) {
                    return;
                }

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
                    const nextUrl = new URL(url, window.location.origin);
                    window.history.replaceState({}, '', nextUrl.toString());
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
                loadResults(url);
            });

            $searchInput.on('input', function () {
                clearTimeout(searchDebounce);

                searchDebounce = setTimeout(function () {
                    const query = $searchForm.serialize();
                    const url = `${$searchForm.attr('action')}?${query}`;
                    loadResults(url);
                }, 300);
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

            bindResultEvents();
        });
    </script>
@stop
