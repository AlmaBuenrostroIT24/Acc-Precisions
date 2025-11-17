<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary')

@section('content_header')
  <div class="d-flex align-items-center justify-content-between">
    <h1 class="mb-0">
      <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
      Inspection Plan
    </h1>
  </div>
@endsection


@section('content')


@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('qa.drawings.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-3">
                    <label>Customer</label>
                    <input type="text" name="customer" class="form-control" value="{{ old('customer') }}">
                </div>

                <div class="col-md-3">
                    <label>PN</label>
                    <input type="text" name="pn" class="form-control" value="{{ old('pn') }}">
                </div>

                <div class="col-md-2">
                    <label>Rev</label>
                    <input type="text" name="rev" class="form-control" value="{{ old('rev') }}">
                </div>

                <div class="col-md-4">
                    <label>Archivo del plano (PNG/JPG)</label>
                 <input type="file" name="file"
       class="form-control @error('file') is-invalid @enderror"
       accept=".pdf,image/*"
       required>
@error('file')
    <span class="invalid-feedback d-block">{{ $message }}</span>
@enderror
                </div>
            </div>

            <div class="text-right mt-3">
                <button class="btn btn-primary">
                    <i class="fas fa-upload mr-1"></i> Subir plano
                </button>
            </div>
        </form>
    </div>
</div>







<!--  {{-- Tab: By End Schedule --}}-->

@endsection



@section('css')
<!-- CSS complementario (puedes ponerlo en tu .css) -->

@endsection


@push('js')

<script>

</script>

@endpush