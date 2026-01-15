@push('css')
<style>
    .erp-navbar-tabs-wrap {
        max-width: min(72vw, 900px);
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .erp-navbar-tabs-wrap::-webkit-scrollbar {
        height: 6px;
    }

    .erp-navbar-tabs-wrap::-webkit-scrollbar-thumb {
        background: rgba(15, 23, 42, 0.18);
        border-radius: 999px;
    }

    .erp-schedule-navbar-tabs {
        gap: .35rem;
        margin: 0;
    }

    .erp-schedule-navbar-tabs .nav-link {
        padding: .22rem .65rem;
        border-radius: 999px;
        border: 1px solid transparent;
        font-weight: 800;
        font-size: 0.90rem;
        line-height: 1.2;
        color: #111827 !important;
        background: transparent;
        white-space: nowrap;
    }

    .erp-schedule-navbar-tabs .nav-link:hover {
        background: rgba(15, 23, 42, 0.06);
        border-color: rgba(15, 23, 42, 0.10);
        color: #111827 !important;
    }

    .erp-schedule-navbar-tabs .nav-link.active {
        background: rgba(11, 94, 215, 0.12);
        border-color: rgba(11, 94, 215, 0.35);
        color: #0b5ed7 !important;
    }

    @media (max-width: 992px) {
        .erp-schedule-navbar-tabs .nav-link {
            font-size: 0.85rem;
            padding: .18rem .55rem;
        }
    }
</style>
@endpush

<ul class="navbar-nav flex-row align-items-center erp-schedule-navbar-tabs" id="scheduleTabsNavbar" role="tablist">
    @can('schedule/general')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('schedule.general') ? 'active' : '' }}"
                href="{{ route('schedule.general') }}">
                <i class="fas fa-calendar-alt mr-1"></i> General Schedule
            </a>
        </li>
    @endcan

    @can('schedule/workhearst')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('schedule.workhearst') ? 'active' : '' }}"
                href="{{ route('schedule.workhearst') }}">
                <i class="fas fa-clock mr-1"></i> Schedule Hearst
            </a>
        </li>
    @endcan

    @can('schedule/endyarnell')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('schedule.endyarnell') ? 'active' : '' }}"
                href="{{ route('schedule.endyarnell') }}">
                <i class="fas fa-building mr-1"></i> Schedule Yarnell
            </a>
        </li>
    @endcan

    @can('schedule/finished')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('schedule.finished') ? 'active' : '' }}"
                href="{{ route('schedule.finished') }}">
                <i class="fas fa-clipboard-check mr-1"></i> Orders Completed
            </a>
        </li>
    @endcan

    @can('schedule/statistics')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('schedule.statistics') ? 'active' : '' }}"
                href="{{ route('schedule.statistics') }}">
                <i class="fas fa-chart-line mr-1"></i> Orders Statistics
            </a>
        </li>
    @endcan
</ul>
