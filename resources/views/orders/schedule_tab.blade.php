{{-- Tabs --}}
<ul class="nav nav-tabs border-bottom border-2 mb-4" id="scheduleTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.general') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('schedule.general') }}">
            <i class="fas fa-calendar-alt me-2"></i> General Schedule
        </a>
    </li>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.workhearst') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('schedule.workhearst') }}">
            <i class="fas fa-clock me-2"></i> Schedule Hearst
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.endyarnell') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('schedule.endyarnell') }}">
            <i class="fas fa-building me-2"></i> Orders Yarnell
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.finished') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('schedule.finished') }}">
            <i class="fas fa-clipboard-check"></i> Completed Orders
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('schedule.statistics') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('schedule.statistics') }}">
            <i class="fas fa-chart-line me-2"></i> Orders Statistics
        </a>

</ul>