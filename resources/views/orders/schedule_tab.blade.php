{{-- Tabs (ERP) --}}
@push('css')
<style>
    .erp-tabs-bar {
        position: sticky;
        top: var(--erp-tabs-sticky-top, 0px);
        z-index: 1020;
        background: #fff;
        padding-top: .25rem;
        padding-bottom: .25rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    }

    .erp-tabs-bar .nav-tabs {
        margin-bottom: 0;
        background: transparent;
    }
</style>
@endpush

@push('js')
<script>
    (function() {
        if (window.__erpTabsStickyInit) return;
        window.__erpTabsStickyInit = true;

        function computeStickyTop() {
            const header = document.querySelector('.main-header');
            if (!header) return 0;
            const pos = window.getComputedStyle(header).position;
            if (pos === 'fixed' || pos === 'sticky') {
                return Math.round(header.getBoundingClientRect().height || 0);
            }
            return 0;
        }

        function setStickyTop() {
            const top = computeStickyTop();
            document.documentElement.style.setProperty('--erp-tabs-sticky-top', `${top}px`);
        }

        window.addEventListener('resize', setStickyTop, { passive: true });
        document.addEventListener('DOMContentLoaded', setStickyTop);
        setStickyTop();
    })();
</script>
@endpush

<div class="erp-tabs-bar mb-3">
<ul class="nav nav-tabs erp-schedule-tabs" id="scheduleTabs" role="tablist">
    @can('schedule/general')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.general') ? 'active' : '' }}"
            href="{{ route('schedule.general') }}">
            <i class="fas fa-calendar-alt"></i> General Schedule
        </a>
    </li>
    @endcan

    @can('schedule/workhearst')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.workhearst') ? 'active' : '' }}"
            href="{{ route('schedule.workhearst') }}">
            <i class="fas fa-clock"></i> Schedule Hearst
        </a>
    </li>
    @endcan

    @can('schedule/endyarnell')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.endyarnell') ? 'active' : '' }}"
            href="{{ route('schedule.endyarnell') }}">
            <i class="fas fa-building"></i> Orders Yarnell
        </a>
    </li>
    @endcan
    @can('schedule/finished')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.finished') ? 'active' : '' }}"
            href="{{ route('schedule.finished') }}">
            <i class="fas fa-clipboard-check"></i> Completed Orders
        </a>
    </li>
    @endcan
    @can('schedule/statistics')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.statistics') ? 'active' : '' }}"
            href="{{ route('schedule.statistics') }}">
            <i class="fas fa-chart-line"></i> Orders Statistics
        </a>
    </li>
    @endcan
</ul>
</div>
