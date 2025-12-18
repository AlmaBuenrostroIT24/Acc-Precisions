{{-- Tabs (2025-12-17): estilo tarjetas en lugar de nav-tabs (mejor diseño) --}}
@php
    // 2025-12-17: inspecciones registradas en el año actual (qa_faisummary.date)
    $faiYear = now()->year;
    $faiInspectionsYear = \App\Models\QaFaiSummary::whereYear('date', $faiYear)->count();
@endphp

{{-- 2025-12-17: wrapper para evitar “corte” blanco/gris entre header y cards --}}
<div class="fai-tabs-header d-flex align-items-center mb-1 pb-1">

</div>

<div class="row mt-2 mb-3 fai-tabs-row">
    @can('qa/partsrevision')
        <div class="col-12 col-sm-6 mb-2 fai-tab-col">
            <a href="{{ route('faisummary.partsrevision') }}"
               class="card fai-tab-card fai-theme-info h-100 text-decoration-none {{ request()->routeIs('faisummary.partsrevision') ? 'fai-tab-active' : '' }}">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="fai-tab-icon bg-info">
                            <i class="fas fa-cog"></i>
                        </span>
                        <div class="ml-2">
                            <div class="fai-tab-title font-weight-semibold text-dark">Parts Revision</div>
                            <small class="text-muted fai-tab-desc">WIP / inspection progress</small>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </div>
            </a>
        </div>
    @endcan

    @can('qa/faigeneral')
        <div class="col-12 col-sm-6 mb-2 fai-tab-col">
            <a href="{{ route('faisummary.general') }}"
               class="card fai-tab-card fai-theme-primary h-100 text-decoration-none {{ request()->routeIs('faisummary.general') ? 'fai-tab-active' : '' }}">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="fai-tab-icon bg-primary">
                            <i class="fas fa-list-alt"></i>
                        </span>
                        <div class="ml-2">
                            <div class="fai-tab-title font-weight-semibold text-dark">Summary</div>
                            <small class="text-muted fai-tab-desc">FAI / IPI overview</small>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </div>
            </a>
        </div>
    @endcan

    @can('qa/faicompleted')
        <div class="col-12 col-sm-6 mb-2 fai-tab-col">
            <a href="{{ route('faisummary.completed') }}"
               class="card fai-tab-card fai-theme-success h-100 text-decoration-none {{ request()->routeIs('faisummary.completed') ? 'fai-tab-active' : '' }}">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="fai-tab-icon bg-success">
                            <i class="fas fa-clipboard-check"></i>
                        </span>
                        <div class="ml-2">
                            <div class="fai-tab-title font-weight-semibold text-dark">Completed</div>
                            <small class="text-muted fai-tab-desc">Completed inspections</small>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </div>
            </a>
        </div>
    @endcan

    @can('qa/rejectedfaiorders')
        <div class="col-12 col-sm-6 mb-2 fai-tab-col">
            <a href="{{ route('faisummary.rejectedfaiorders') }}"
               class="card fai-tab-card fai-theme-danger h-100 text-decoration-none {{ request()->routeIs('faisummary.rejectedfaiorders') ? 'fai-tab-active' : '' }}">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="fai-tab-icon bg-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="ml-2">
                            <div class="fai-tab-title font-weight-semibold text-dark">Rejected</div>
                            <small class="text-muted fai-tab-desc">Rejected FAI orders</small>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </div>
            </a>
        </div>
    @endcan

    @can('qa/faistatistics')
        <div class="col-12 col-sm-6 mb-2 fai-tab-col">
            <a href="{{ route('faisummary.statistics') }}"
               class="card fai-tab-card fai-theme-warning h-100 text-decoration-none {{ request()->routeIs('faisummary.statistics') ? 'fai-tab-active' : '' }}">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="fai-tab-icon bg-warning">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div class="ml-2">
                            <div class="fai-tab-title font-weight-semibold text-dark">Statistics</div>
                            <small class="text-muted fai-tab-desc">Trends & charts</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-pill badge-light border mr-2" title="Inspections {{ $faiYear }}">
                            {{ $faiYear }}: {{ $faiInspectionsYear }}
                        </span>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </div>
                </div>
            </a>
        </div>
    @endcan
</div>

<style>
    /* 2025-12-17: header de la sección (separador suave) */
    .fai-tabs-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }

    /* 2025-12-17: mejorar legibilidad del título + subtítulo en las cards */
    .fai-tab-title {
        font-size: 0.95rem;
        line-height: 1.1;
    }

    /* 2025-12-17: resaltar el título cuando la card está seleccionada */
    .fai-tab-active .fai-tab-title {
        font-weight: 800 !important;
    }

    /* 2025-12-17: fondo suave detrás de las cards para que no se vea una partición blanco/gris */
    /* 2025-12-17: estilo de tarjetas para tabs */
    .fai-tab-card {
        /* defaults (slate/indigo) */
        --fai-hover-border: rgba(37, 99, 235, 0.25);
        --fai-active-border: rgba(37, 99, 235, 0.45);
        --fai-active-shadow: rgba(30, 58, 138, 0.18);
        --fai-active-from: rgba(30, 58, 138, 0.95);
        --fai-active-to: rgba(37, 99, 235, 0.82);

        /* 2025-12-17: permitir indicador activo y foco accesible */
        position: relative;

        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
        /* 2025-12-17: fondo único (sin degradado) */
        background: #ffffff;
    }

    /* 2025-12-17: hover con un poco de “fill” para que se sienta más clickeable */
    .fai-tab-card .card-body {
        border-radius: 12px;
        transition: background-color .12s ease;
    }

    .fai-tab-card:hover .card-body {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .fai-tab-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 .25rem .75rem rgba(0, 0, 0, 0.08);
        border-color: var(--fai-hover-border);
    }

    /* 2025-12-17: feedback de teclado (tab) */
    .fai-tab-card:focus,
    .fai-tab-card:focus-within {
        outline: none;
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, 0.18);
        border-color: var(--fai-hover-border);
    }

    .fai-tab-active {
        /* 2025-12-17: paleta profesional (slate/indigo) */
        border-color: var(--fai-active-border) !important;
        box-shadow: 0 .20rem .65rem var(--fai-active-shadow) !important;
        /* 2025-12-17: fondo único para todas las cards */
        background: #fff !important;
    }

    /* 2025-12-17: indicador visual sutil para la card activa */
    .fai-tab-active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 10px;
        bottom: 10px;
        width: 4px;
        border-radius: 12px;
        /* 2025-12-17: indicador usando el color del tema */
        background: var(--fai-active-border);
    }

    /* 2025-12-17: tema por card (para que el activo combine con el color del ícono) */
    .fai-theme-info {
        --fai-hover-border: rgba(23, 162, 184, 0.25);
        --fai-active-border: rgba(23, 162, 184, 0.45);
        --fai-active-shadow: rgba(12, 135, 153, 0.18);
        --fai-active-from: rgba(23, 162, 184, 0.92);
        --fai-active-to: rgba(12, 135, 153, 0.82);
    }
    .fai-theme-primary {
        --fai-hover-border: rgba(13, 110, 253, 0.25);
        --fai-active-border: rgba(13, 110, 253, 0.45);
        --fai-active-shadow: rgba(13, 110, 253, 0.18);
        --fai-active-from: rgba(13, 110, 253, 0.92);
        --fai-active-to: rgba(11, 94, 215, 0.82);
    }
    .fai-theme-success {
        --fai-hover-border: rgba(40, 167, 69, 0.25);
        --fai-active-border: rgba(40, 167, 69, 0.45);
        --fai-active-shadow: rgba(40, 167, 69, 0.18);
        --fai-active-from: rgba(40, 167, 69, 0.92);
        --fai-active-to: rgba(25, 135, 84, 0.82);
    }
    .fai-theme-danger {
        --fai-hover-border: rgba(220, 53, 69, 0.25);
        --fai-active-border: rgba(220, 53, 69, 0.45);
        --fai-active-shadow: rgba(220, 53, 69, 0.18);
        --fai-active-from: rgba(220, 53, 69, 0.92);
        --fai-active-to: rgba(176, 42, 55, 0.82);
    }
    .fai-theme-warning {
        --fai-hover-border: rgba(255, 193, 7, 0.30);
        --fai-active-border: rgba(255, 193, 7, 0.55);
        --fai-active-shadow: rgba(255, 193, 7, 0.18);
        --fai-active-from: rgba(255, 193, 7, 0.92);
        --fai-active-to: rgba(245, 158, 11, 0.82);
    }

    /* 2025-12-17: cuando la card está seleccionada, pintar todo en azul */
    .fai-tab-active .text-dark,
    .fai-tab-active .text-muted,
    .fai-tab-active .font-weight-semibold,
    .fai-tab-active .font-weight-bold {
        color: #fff !important;
    }

    .fai-tab-active .fai-tab-desc {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    .fai-tab-active .fai-tab-icon {
        background: rgba(255, 255, 255, 0.22) !important;
    }

    .fai-tab-active .fa-chevron-right {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    .fai-tab-active .badge.badge-light {
        background: rgba(255, 255, 255, 0.22) !important;
        color: #fff !important;
        border-color: rgba(255, 255, 255, 0.35) !important;
    }

    /* 2025-12-17: contraste mejor para “warning” cuando está activa (amarillo + blanco = bajo contraste) */
    .fai-theme-warning.fai-tab-active .text-dark,
    .fai-theme-warning.fai-tab-active .text-muted,
    .fai-theme-warning.fai-tab-active .font-weight-semibold,
    .fai-theme-warning.fai-tab-active .font-weight-bold,
    .fai-theme-warning.fai-tab-active .fai-tab-desc,
    .fai-theme-warning.fai-tab-active .fa-chevron-right {
        color: #111827 !important;
    }

    .fai-theme-warning.fai-tab-active .badge.badge-light {
        background: rgba(17, 24, 39, 0.08) !important;
        color: #111827 !important;
        border-color: rgba(17, 24, 39, 0.18) !important;
    }

    /* 2025-12-17: hover del chevron acorde al color del tema */
    .fai-tab-card .fa-chevron-right {
        transition: transform .12s ease, color .12s ease;
    }

    .fai-tab-card:hover .fa-chevron-right {
        color: var(--fai-active-border) !important;
        transform: translateX(2px);
    }

    /* 2025-12-17: fondo único en cards -> revertir overrides de texto/ícono para que todo quede normal */
    .fai-tab-active .text-dark,
    .fai-tab-active .font-weight-semibold,
    .fai-tab-active .font-weight-bold {
        /* 2025-12-17: texto negro cuando está seleccionada */
        color: #000 !important;
    }

    .fai-tab-active .text-muted,
    .fai-tab-active .fai-tab-desc,
    .fai-tab-active .fa-chevron-right {
        color: #000 !important;
    }

    .fai-tab-active .badge.badge-light {
        background: #f8f9fa !important;
        color: #212529 !important;
        border-color: rgba(0, 0, 0, 0.12) !important;
    }

    .fai-tab-active .fai-tab-icon.bg-info { background-color: #17a2b8 !important; }
    .fai-tab-active .fai-tab-icon.bg-primary { background-color: #007bff !important; }
    .fai-tab-active .fai-tab-icon.bg-success { background-color: #28a745 !important; }
    .fai-tab-active .fai-tab-icon.bg-danger { background-color: #dc3545 !important; }
    .fai-tab-active .fai-tab-icon.bg-warning { background-color: #ffc107 !important; color: #212529 !important; }

    .fai-tab-icon {
        /* 2025-12-17: tabs más delgados (menos alto) */
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        flex: 0 0 auto;
    }

    .fai-tab-icon i {
        /* 2025-12-17: ajustar tamaño para cards más delgadas */
        font-size: 1.15rem;
    }

    .fai-tab-desc {
        font-size: 0.78rem;
        line-height: 1.1;
        /* 2025-12-17: limitar a 2 líneas para alinear alturas */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .fai-tab-active .fai-tab-desc {
        opacity: 0.8;
    }

    /* 2025-12-17: reducir padding interno para que las cards del tab sean más delgadas */
    .fai-tab-card .card-body {
        padding-top: 0.45rem !important;
        padding-bottom: 0.45rem !important;
        padding-left: 0.65rem !important;
        padding-right: 0.65rem !important;
    }

    /* 2025-12-17: 5 tarjetas por fila en desktop (>=992px) */
    @media (min-width: 992px) {
        .fai-tabs-row {
            display: flex;
            flex-wrap: wrap;
        }
        .fai-tab-col {
            flex: 0 0 20%;
            max-width: 20%;
        }
    }
</style>
