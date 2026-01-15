<ul class="navbar-nav flex-row align-items-center" id="faiTabsNavbar" role="tablist">
    @can('qa/partsrevision')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('faisummary.partsrevision') ? 'active font-weight-bold' : '' }}"
                href="{{ route('faisummary.partsrevision') }}">
                <i class="fas fa-cog mr-1"></i> Parts
            </a>
        </li>
    @endcan

    @can('qa/faigeneral')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('faisummary.general') ? 'active font-weight-bold' : '' }}"
                href="{{ route('faisummary.general') }}">
                <i class="fas fa-list-alt mr-1"></i> Summary
            </a>
        </li>
    @endcan

    @can('qa/faicompleted')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('faisummary.completed') ? 'active font-weight-bold' : '' }}"
                href="{{ route('faisummary.completed') }}">
                <i class="fas fa-clipboard-check mr-1"></i> Completed
            </a>
        </li>
    @endcan

    @can('qa/rejectedfaiorders')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('faisummary.rejectedfaiorders') ? 'active font-weight-bold' : '' }}"
                href="{{ route('faisummary.rejectedfaiorders') }}">
                <i class="fas fa-exclamation-triangle mr-1"></i> Rejected
            </a>
        </li>
    @endcan

    @can('qa/faistatistics')
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('faisummary.statistics') ? 'active font-weight-bold' : '' }}"
                href="{{ route('faisummary.statistics') }}">
                <i class="fas fa-chart-pie mr-1"></i> Stats
            </a>
        </li>
    @endcan
</ul>
