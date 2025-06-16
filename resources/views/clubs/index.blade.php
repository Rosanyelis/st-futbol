@extends('layouts.app')
@section('title', 'Clubs')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <div class="card-header header-elements border-bottom">
            <h5 class="mb-0 me-2">Clubs</h5>

            <div class="card-header-elements ms-auto">
                <a href="{{ route('club.create') }}" class="btn btn-sm btn-primary"
                >Crear Club</a>
            </div>
        </div>
        <div class="card-datatable text-nowrap">
            <table class="datatables table table-sm">
                <thead>
                    <tr>
                        <th>Club</th>
                        <th>Evento</th>
                        <th>Responsable</th>
                        <th>Telefono</th>
                        <th>Correo</th>
                        <th>País</th>
                        <th>Hotel</th>
                        <th>Cat. Hotel</th>
                        <th>Moneda</th> 
                        <th>Jugadores</th>
                        <th>Precio x <br>jugador</th>
                        <th>Total</th>
                        <th>Profesores</th>
                        <th>Precio x <br>profesor</th>
                        <th>Total</th>
                        <th>Acompañantes</th>
                        <th>Precio x <br>acompañante</th>
                        <th>Total</th>
                        <th>Choferes</th>
                        <th>Precio x <br>chofer</th>
                        <th>Total</th>
                        <th>Liberados</th>
                        <th>Total <br>Delegación</th>
                        <th>Total $</th>
                        <th style="width: 10px"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Ajax Sourced Server-side -->
</div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('pagesjs/clubs/index.js') }}"></script>
@endsection
