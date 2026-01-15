{{-- 2025-12-17: Override AdminLTE navbar toggler para mostrar el título de la vista junto al botón --}}
<li class="nav-item d-flex align-items-center">
    <a class="nav-link" data-widget="pushmenu" href="#"
        @if(config('adminlte.sidebar_collapse_remember'))
            data-enable-remember="true"
        @endif
        @if(!config('adminlte.sidebar_collapse_remember_no_transition'))
            data-no-transition-after-reload="false"
        @endif
        @if(config('adminlte.sidebar_collapse_auto_size'))
            data-auto-collapse-size="{{ config('adminlte.sidebar_collapse_auto_size') }}"
        @endif>
        <i class="fas fa-bars"></i>
        <span class="sr-only">{{ __('adminlte::adminlte.toggle_navigation') }}</span>
    </a>

    @php
        // Preferible: usa un título simple para no romper el navbar con HTML del content_header
        $navbarTitle = trim($__env->yieldContent('title'));
        if ($navbarTitle === '') {
            $navbarTitle = trim($__env->yieldContent('content_header_title'));
        }

        $isScheduleRoute = request()->routeIs('schedule.*');
        $isFaiSummaryRoute = request()->routeIs('faisummary.*') || request()->routeIs('qa.faisummary.*');
    @endphp

    @if($isScheduleRoute)
        <div class="ms-3 d-flex align-items-center navbar-tabs-wrap" style="padding-left:.75rem; border-left:1px solid rgba(15, 23, 42, 0.12);">
            @include('orders.schedule_tab_navbar')
        </div>
    @elseif($isFaiSummaryRoute)
        <div class="ms-3 d-flex align-items-center navbar-tabs-wrap" style="padding-left:.75rem; border-left:1px solid rgba(15, 23, 42, 0.12);">
            @include('qa.faisummary.faisummary_tab_navbar')
        </div>
    @elseif($navbarTitle !== '')
        <span class="navbar-content-header ms-3" style="padding-left:.75rem; border-left:1px solid rgba(15, 23, 42, 0.12);">{{ $navbarTitle }}</span>
    @endif
</li>
