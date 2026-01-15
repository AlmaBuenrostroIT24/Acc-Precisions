<ul class="navbar-nav flex-row align-items-center" id="scheduleTabsNavbar" role="tablist">
    @can('schedule/general')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('schedule.general') ? 'active font-weight-bold' : '' }}"
                href="{{ route('schedule.general') }}">
                <i class="fas fa-calendar-alt mr-1"></i> General Schedule
            </a>
        </li>
    @endcan

    @can('schedule/workhearst')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('schedule.workhearst') ? 'active font-weight-bold' : '' }}"
                href="{{ route('schedule.workhearst') }}">
                <i class="fas fa-clock mr-1"></i> Schedule Hearst
            </a>
        </li>
    @endcan

    @can('schedule/endyarnell')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('schedule.endyarnell') ? 'active font-weight-bold' : '' }}"
                href="{{ route('schedule.endyarnell') }}">
                <i class="fas fa-building mr-1"></i> Schedule Yarnell
            </a>
        </li>
    @endcan

    @can('schedule/finished')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('schedule.finished') ? 'active font-weight-bold' : '' }}"
                href="{{ route('schedule.finished') }}">
                <i class="fas fa-clipboard-check mr-1"></i> Orders Completed
            </a>
        </li>
    @endcan

    @can('schedule/statistics')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('schedule.statistics') ? 'active font-weight-bold' : '' }}"
                href="{{ route('schedule.statistics') }}">
                <i class="fas fa-chart-line mr-1"></i> Orders Statistics
            </a>
        </li>
    @endcan
</ul>
