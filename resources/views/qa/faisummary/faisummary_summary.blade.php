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
        <div class="card mb-4">
            <div class="card-body">
                {{-- Filtros dinámicos --}}
                {{-- Tabla --}}
                <div class="table-responsive">
                    <table class="table  table-bordered table-striped" style="table-layout: fixed; width: 100%;">
                        <thead class="table-light thead-custom">
                            <tr>
                                <th style="width: 80px;">DATE</th>
                                <th style="width: 80px;">Part/Revision</th>
                                <th style="width: 40px;">JOB</th>
                                <th style="width: 30px;">Type</th>
                                <th style="width: 60px;">Operation</th>
                                <th style="width: 50px;">Operator</th>
                                <th style="width: 40px;">Results</th>
                                <th style="width: 110px;">SB/IS</th>
                                <th style="width: 110px;">Observation</th>
                                <th style="width: 40px;">Station</th>
                                <th style="width: 60px;">Method</th>
                                <th style="width: 70px;">Inspector</th>
                                <th style="width: 50px;">Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspections as $inspection)
                            <tr>
                                <td>
                                    {{ optional($inspection->created_at)->format('M-d-y') }}
                                    @if($inspection->created_at)
                                    <span class="badge badge-light">
                                        {{ $inspection->created_at->format('H:i') }}
                                    </span>
                                    @endif
                                </td>
                                <td>{{ $inspection->orderSchedule->PN ?? '' }}</td>
                                <td>{{ $inspection->orderSchedule->work_id ?? '' }}</td>
                                <td>{{ $inspection->insp_type }}</td>
                                <td>{{ $inspection->operation }}</td>
                                <td>{{ $inspection->operator }}</td>
                                <td>{{ $inspection->results }}</td>
                                <td class="truncate" data-toggle="tooltip" title="{{ $inspection->sb_is }}">{{ $inspection->sb_is }}</td>
                                <td  class="truncate" data-toggle="tooltip" title="{{ $inspection->observation }}">
                                    {{ $inspection->observation }}
                                </td>
                                <td>{{ $inspection->station }}</td>
                                <td>{{ $inspection->method }}</td>
                                <td>{{ $inspection->inspector }}</td>
                                <td>{{ $inspection->orderSchedule->location ?? '' }}</td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="16" class="text-center text-muted">No hay registros</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!--   <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="fas fa-plus"></i> New Order
                        </button> -->

            </div>
        </div>
    </div>
</div>


<!--  {{-- Tab: By End Schedule --}}-->




@endsection


@section('css')
<style>

.truncate {
    max-width: 240px;    /* ajusta al ancho deseado */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 14px;
}
</style>
@endsection


@push('js')
<script>
    // resources/js/faisummary-all.js (o en la vista)
    $(function() {
        $('table').DataTable({
            pageLength: 15,
            order: [
                [1, 'desc'],
                [0, 'desc']
            ], // date desc, id desc
            responsive: true
        });

         $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush