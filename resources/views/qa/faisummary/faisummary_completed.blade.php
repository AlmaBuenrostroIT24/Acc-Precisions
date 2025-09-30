<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary Completed')
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
                <li class="breadcrumb-item active" aria-current="page">FAI Summary Completed</li>
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
                                <th style="width: 50px;">DATE</th>
                                <th style="width: 50px;">LOCATION</th>
                                <th style="width: 40px;">WORD ID</th>
                                <th style="width: 50px;">PN</th>
                                <th style="width: 90px;">DESCRIPTION</th>
                                <th style="width: 40px;">SAMP. PLAN</th>
                                <th style="width: 40px;">WO QTY</th>
                                <th style="width: 40px;">SAMPLING</th>
                                <th style="width: 20px;">OPS.</th>
                                <th style="width: 20px;">FAI</th>
                                <th style="width: 20px;">IPI</th>
                                <th style="width: 30px;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderscompleted as $o)
                            <tr id="row-{{ $o->id }}">
                                {{-- No traes fecha aún: deja en blanco o usa updated_at si lo agregas al select --}}
                                <td>
                                    {{ optional($o->inspection_endate)->format('M-d-y') }}
                                    @if($o->inspection_endate)
                                    <span class="badge badge-light">
                                        {{ $o->inspection_endate->format('H:i') }}
                                    </span>
                                    @endif
                                </td>
                                <td>{{ ucfirst($o->location) }}</td>
                                <td>{{ $o->work_id }}</td>
                                <td>{{ $o->PN }}</td>
                                <td>{{ \Illuminate\Support\Str::before($o->Part_description, ',') }}</td>
                                <td>{{ ucfirst($o->sampling_check) }}</td>
                                <td>{{ $o->group_wo_qty }}</td>
                                <td>{{ $o->sampling }}</td>
                                <td>{{ $o->operation }}</td>
                                <td>{{ $o->total_fai }}</td>
                                <td>{{ $o->total_ipi }}</td>
                                <td class="text-nowrap">
                                    <a href="#"
                                        class="btn btn-sm btn-primary btn-open-pdf"
                                        data-pdf-url="{{ route('qa.faisummary.pdf', $o->id) }}">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    
                                    {{-- Botón para descargar --}}
                                    <a href="{{ route('qa.faisummary.pdf', $o->id) }}?download=1"
                                        class="btn btn-sm btn-warning">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No hay registros completados.</td>
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
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    PDF Preview
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-0" style="height:80vh;">
                <embed id="pdfEmbed" src="" type="application/pdf" width="100%" height="100%">
            </div>
        </div>
    </div>
</div>



@endsection


@section('css')

@endsection


@push('js')
<script>
    $(document).on('click', '.btn-open-pdf', function(e) {
        e.preventDefault();
        const url = $(this).data('pdf-url'); // ← usa la URL del botón
        $('#pdfEmbed').attr('src', url + '#zoom=page-width');
        $('#pdfModal').modal('show');
    });
    $('#pdfModal').on('hidden.bs.modal', function() {
        $('#pdfEmbed').attr('src', '');
    });
</script>
@endpush