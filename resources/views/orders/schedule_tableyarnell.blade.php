<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')
{{-- Colapsar u ocultar sidebar --}}
@section('classes_body', 'sidebar-collapse layout-top-nav') {{-- o 'layout-top-nav' para quitarlo completamente --}}

@section('title', 'Schedule Orders Yarnell')

@section('meta') {{-- ✅ Asegura que el token se inyecta en el <head> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="tab-content mt-3">
    {{-- Tab: General Schedule --}}
    <div class="tab-pane fade show active" id="byMachine">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">

                    <div class="card-body">
                        {{-- Filtros dinámicos --}}
                        <form id="upload-form" action="{{ route('schedule.orders.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                        </form>
                        @include('orders.schedule_table')
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Bootstrap para editar notas -->
        @include('orders.schedule_modaltable')
        @endsection

        @section('css')
        <link rel="stylesheet" href="{{ asset('vendor/css/orders-schedule.css') }}">
        <style>
            /* Ocultar navbar */
            .main-header {
                display: none !important;
            }
        </style>
        @endsection

        @push('js')
        <script src="{{ asset('vendor/js/orders-schedule.js') }}"></script>
        <script src="{{ asset('vendor/js/schedule-yarhea.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            window.currentLocation = '{{ $location }}'; // Ej: 'yarnell'
        </script>
        @endpush