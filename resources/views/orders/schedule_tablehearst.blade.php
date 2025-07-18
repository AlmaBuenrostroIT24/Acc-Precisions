<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')
{{-- Colapsar u ocultar sidebar --}}
@section('classes_body', 'sidebar-collapse layout-top-nav') {{-- o 'layout-top-nav' para quitarlo completamente --}}


@section('title', 'Schedule Orders Hearst')

@section('meta') {{-- ✅ Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
{{-- Filtros dinámicos --}}
<form id="upload-form" action="{{ route('schedule.orders.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
</form>

@include('orders.schedule_table')
@include('orders.schedule_modaltable')
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/css/orders-schedule.css') }}">
<style>
    /* Ocultar navbar */
    .main-header {
        display: none !important;
    }

    .letra-grande {
        font-size: 18px;
        /* o 20px según prefieras */
    }
       .texsty {
        color: black !important;
        font-size: 16px !important;
      
    }

        table th {
    color: black !important;
    font-size: 16px !important;
   
}
</style>
@endsection

@push('js')
<script src="{{ asset('vendor/js/orders-schedule.js') }}"></script>
<script src="{{ asset('vendor/js/schedule-yarhea.js') }}"></script>
<script>
    window.currentLocation = '{{ $location }}'; // Ej: 'hearst'
</script>
@endpush