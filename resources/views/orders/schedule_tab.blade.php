{{-- Tabs (ERP) --}}
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
