{{-- Tabs --}}
<ul class="nav nav-tabs border-bottom border-2 mb-4" id="scheduleTabs" role="tablist">
    @can('schedule/general')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.general') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('schedule.general') }}">
            <i class="fas fa-calendar-alt me-2"></i> General Schedule
        </a>
    </li>
    @endcan
    @can('schedule/endyarnell')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.endyarnell') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('schedule.endyarnell') }}">
            <i class="fas fa-building me-2"></i> Orders Yarnell
        </a>
    </li>
    @endcan
    @can('schedule/finished')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.finished') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('schedule.finished') }}">
            <i class="fas fa-clipboard-check"></i> Completed Orders
        </a>
    </li>
    @endcan
    @can('schedule/statistics')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.statistics') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('schedule.statistics') }}">
            <i class="fas fa-chart-line me-2"></i> Orders Statistics
        </a>
    </li>
    @endcan
</ul>