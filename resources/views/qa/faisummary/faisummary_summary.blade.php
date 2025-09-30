<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary')
@section('content_header')
<div class="card shadow-sm mb-2 border-0 bg-light">
    <div class="card-body d-flex align-items-center py-2 px-3">
        <h4 class="mb-0 text-dark">
            <i class="fas fa-clipboard-list me-2" aria-hidden="true"></i>
            FAI Summary
        </h4>

        <nav aria-label="breadcrumb" class="mb-0 ml-auto">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">FAI Summary</li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content_header')
<div class="card bg-light d-flex justify-content-center align-items-center" style="height: 50px; padding: 0 15px;">
    <h2 class="text-dark" style="font-size: 24px; margin: 0;">
        <i class="fas fa-box"></i> Schedule Orders
    </h2>
</div>
@endsection


@section('content')



{{-- Tabs --}}
@include('qa.faisummary.faisummary_tab')

{{-- Tab: By Active Schedules --}}

<div class="row">
    <div class="col-md-12">
        {{-- ====== FILTROS ====== --}}
        <div class="card shadow-sm mb-3 filters-card-fixed">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('faisummary.general') }}" id="filtersForm">



                    {{-- ====== TABLA ====== --}}
                    <div class="mt-3">
                        <div class="table-responsive">
                            {{-- Usa colgroup para anchos consistentes --}}
                            <table id="faiTable" class="table table-sm table-striped table-bordered align-middle mb-0">
                                <colgroup>
                                    <col style="width:150px">
                                    <col style="width:140px">
                                    <col style="width:100px">
                                    <col style="width:70px">
                                    <col style="width:90px">
                                    <col style="width:110px">
                                    <col style="width:90px">
                                    <col style="width:160px">
                                    <col style="width:160px">
                                    <col style="width:90px">
                                    <col style="width:100px">
                                    <col style="width:120px">
                                    <col style="width:100px">
                                </colgroup>
                                <thead class="thead-light sticky-thead">
                                    <tr class="text-uppercase text-muted small">
                                        <th>Fecha</th>
                                        <th>Part/Revision</th>
                                        <th>Job</th>
                                        <th>Tipo</th>
                                        <th>Operación</th>
                                        <th>Operador</th>
                                        <th>Resultado</th>
                                        <th>SB/IS</th>
                                        <th>Observación</th>
                                        <th>Estación</th>
                                        <th>Método</th>
                                        <th>Inspector</th>
                                        <th>Ubicación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inspections as $inspection)
                                    @php
                                    // created_at ya es una instancia de Carbon
                                    $dtCreated = $inspection->created_at;
                                    $dtLogicalDate = $inspection->date ? \Carbon\Carbon::parse($inspection->date) : null;
                                    // Comparaciones robustas
                                    $isPass = strcasecmp(trim((string)$inspection->results), 'pass') === 0;
                                    $isFAI = strcasecmp(trim((string)$inspection->insp_type), 'FAI') === 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ optional($dtLogicalDate ?? $dtCreated)->format('M-d-y') }}
                                            @if($dtCreated)
                                            <span class="badge badge-light">{{ $dtCreated->format('H:i') }}</span>
                                            @endif
                                        </td>
                                        <td class="truncate" title="{{ $inspection->orderSchedule->PN ?? '' }}">
                                            {{ $inspection->orderSchedule->PN ?? '' }}
                                        </td>
                                        <td>{{ $inspection->orderSchedule->work_id ?? '' }}</td>
                                        <td>
                                            <span class="badge {{ $isFAI ? 'badge-info' : 'badge-secondary' }}">
                                                {{ $inspection->insp_type }}
                                            </span>
                                        </td>

                                        <td>{{ $inspection->operation }}</td>
                                        <td class="truncate" title="{{ $inspection->operator }}">{{ $inspection->operator }}</td>
                                        <td>
                                            <span class="badge {{ $isPass ? 'badge-success' : 'badge-danger' }}">
                                                {{ ucfirst($inspection->results) }}
                                            </span>
                                        </td>
                                        <td class="cell-paragraph" data-toggle="tooltip" title="{{ $inspection->sb_is }}">
                                            {{ $inspection->sb_is }}
                                        </td>
                                        <td class="cell-paragraph" data-toggle="tooltip" title="{{ $inspection->observation }}">
                                            {{ $inspection->observation }}
                                        </td>
                                        <td>{{ $inspection->station }}</td>
                                        <td>{{ $inspection->method }}</td>
                                        <td class="truncate" title="{{ $inspection->inspector }}">{{ $inspection->inspector }}</td>
                                        <td>{{ strtoupper($inspection->orderSchedule->location ?? '') }}</td>
                                    </tr>
                                    @empty
                                    {{-- vacío: DataTables muestra su mensaje --}}
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>


<!--   <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="fas fa-plus"></i> New Order
                        </button> -->



<!--  {{-- Tab: By End Schedule --}}-->




@endsection


@section('css')
<style>
    #faiTable,
    #faiTable td,
    #faiTable th {
        white-space: normal;
    }

    /* Párrafo dentro de la celda */
    .cell-paragraph {
        white-space: pre-line;
        /* respeta \n y envuelve */
        overflow-wrap: anywhere;
        /* rompe palabras largas/URLs */
        word-break: break-word;
        /* respaldo */
    }



    .table-responsive--sticky {
        max-height: calc(140vh - 260px);
        /* ajusta con tu header */
        overflow: auto;
    }

    .sticky-thead th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        /* acorde a .thead-light */
        z-index: 2;
    }


    .align-middle td,
    .align-middle th {
        vertical-align: middle !important;
    }

    .truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .table-sm td,
    .table-sm th {
        padding-top: .45rem;
        padding-bottom: .45rem;
    }
</style>
@endsection


@push('js')
<script>
    // resources/js/faisummary-all.js (o en la vista)
    $(function() {
        const $tbl = $('#faiTable');

        if ($.fn.DataTable.isDataTable($tbl)) {
            $tbl.DataTable().clear().destroy();
        }

        $tbl.DataTable({
            searching: false, // ← evita doble búsqueda
            lengthChange: false, // ❌ oculta el select "N registros"
            pageLength: 20,
            responsive: true,
            autoWidth: false,
            ordering: false // 👈 respeta el orden que viene del servidor
        });

        $('[data-toggle="tooltip"]').tooltip();

        const $form = $('#filtersForm');

        // Si seleccionas un día, enviamos y "anulamos" año/mes visualmente
        $('#day').on('change', function() {
            if (this.value) {
                // Opcional: limpia año/mes para que quede claro en la UI
                $('#year').val('');
                $('#month').val('');
            }
            $form.submit();
        });

        // Cambios en año o mes => enviar (si no hay día seleccionado)
        $('#year, #month').on('change', function() {
            if (!$('#day').val()) {
                $form.submit();
            }
        });

        // Auto-submit en inspector y operador
        $('#inspector, #operator, #location').on('change', function() {
            $form.submit();
        });
    });
</script>
@endpush