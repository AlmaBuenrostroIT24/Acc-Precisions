{{-- Tabs --}}
<ul class="nav nav-tabs border-bottom border-2 mb-4" id="faisummaryTabs" role="tablist">
    @can('qa/faigeneral')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('faisummary.general') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('faisummary.general') }}">
            <i class="fas fa-calendar-alt me-2"></i> Summary
        </a>
    </li>
    @endcan
    @can('qa/partsrevision')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('faisummary.partsrevision') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('faisummary.partsrevision') }}">
            <i class="fas fa-exclamation-triangle me-2"></i> Parts Revision
        </a>
    </li>
    @endcan
    @can('qa/faicompleted')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('faisummary.completed') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('faisummary.completed') }}">
            <i class="fas fa-clipboard-check"></i> Completed FAI Summary
        </a>
    </li>
    @endcan
    @can('qa/faistatistics')
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('faisummary.statistics') ? 'active text-dark fw-semibold border-bottom border-primary' : 'text-secondary' }}"
            href="{{ route('faisummary.statistics') }}">
            <i class="fas fa-chart-line me-2"></i> FAI Summary Statistics
        </a>
    </li>
    @endcan
</ul>