@extends('layouts.app')
@section('title', 'Estado de Resultados por Evento y por Moneda')
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
            <h5 class="mb-0 me-2">Estado de Resultados por Evento y por Moneda</h5>

            <div class="card-header-elements ms-auto">
            </div>
        </div>
        <div class="card-header d-flex align-items-center border-bottom">
            <form action="{{ route('report.eventCurrencyStatement') }}" method="GET" 
                class="d-flex align-items-center w-100">
                @csrf
                <div class="row w-100">
                    <div class="col-md-3">
                        <div class="form-floating form-floating-outline">
                            <select id="evento" class="form-select form-select-sm select2" 
                            data-allow-clear="true" name="event_id">
                                <option value="">Seleccione un evento</option>
                                @foreach($events as $evento)
                                    <option value="{{ $evento->id }}" @if( $evento->id == request('event_id') ) selected @endif>{{ $evento->name }} - {{ $evento->year }}</option>
                                @endforeach
                            </select>
                            <label for="evento">Evento</label>
                        </div>  
                    </div>
                    <div class="col-md-2">
                        <div class="form-floating form-floating-outline">
                            <input type="date" id="start_date" class="form-control form-control-sm" 
                            placeholder="Seleccione un rango de fecha" name="start_date" value="{{ request('start_date') }}" />
                            <label for="start_date">Desde</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-floating form-floating-outline">
                            <input type="date" id="end_date" class="form-control form-control-sm" 
                            placeholder="Seleccione un rango de fecha" name="end_date" value="{{ request('end_date') }}" />
                            <label for="end_date">hasta</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                        <button type="reset" class="btn btn-secondary btn-sm">Limpiar</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-datatable text-nowrap">
            <table class="datatables-history table table-sm">
                <thead>
                    <tr>
                        <th>Ingresos</th>
                        @foreach($monedas as $moneda)
                            <th>{{ strtoupper($moneda->name) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($categorias as $categoria)
                        <tr>
                            <th>{{ $categoria->name }}</th>
                            @foreach($monedas as $moneda)
                                <td>
                                    $ 
                                    {{
                                        number_format(
                                            optional($totales->first(function($t) use ($categoria, $moneda) {
                                                return $t->categoria === $categoria->name && $t->moneda === $moneda->name;
                                            }))->total ?? 0,
                                            0,
                                            '.',
                                            ','
                                        )
                                    }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr>
                        <th>Total Ingresos</th>
                        @foreach($monedas as $moneda)
                            <th>
                                $ {{
                                    number_format(
                                        $totales->where('moneda', $moneda->name)->sum('total') ?? 0,
                                        0,
                                        '.',
                                        ','
                                    )
                                }}
                            </th>
                        @endforeach
                    </tr>
                </tbody>
                <thead>
                    <tr>
                        <th>Egresos</th>
                        @foreach($monedas as $moneda)
                            <th>{{ strtoupper($moneda->name) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($categoriasEgreso as $categoria)
                        <tr>
                            <td>{{ $categoria->name }}</td>
                            @foreach($monedas as $moneda)
                                <td>
                                    $ 
                                    {{
                                        number_format(
                                            optional($totalesEgreso->first(function($t) use ($categoria, $moneda) {
                                                return $t->categoria === $categoria->name && $t->moneda === $moneda->name;
                                            }))->total ?? 0,
                                            0,
                                            ',',
                                            '.'
                                        )
                                    }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr>
                        <th>Total Egresos</th>
                        @foreach($monedas as $moneda)
                            <th>
                                $ {{
                                    number_format(
                                        $totalesEgreso->where('moneda', $moneda->name)->sum('total') ?? 0,
                                        0,
                                        '.',
                                        ','
                                    )
                                }}
                            </th>
                        @endforeach
                    </tr>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th>Resultado</th>
                        @foreach($monedas as $moneda)
                            <th>
                                $ {{
                                    number_format(
                                        ($totales->where('moneda', $moneda->name)->sum('total') ?? 0) -
                                        ($totalesEgreso->where('moneda', $moneda->name)->sum('total') ?? 0),
                                        0,
                                        '.',
                                        ','
                                    )
                                }}
                            </th>
                        @endforeach
                    </tr>

                </tfoot>
            </table>

            
                
                
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script>
        $(document).ready(function() {
            // cuando haga click en el boton reset que se limpie los campos y recargue el sitio sin datos de busqueda
            $('form').on('reset', function() {
                // Recargar la página sin parámetros de búsqueda
                window.location.href = "{{ route('report.eventCurrencyStatement') }}";
            });

            $('.select2').select2({
                placeholder: 'Seleccione una opción',
                allowClear: true
            });
        });
    </script>

@endsection
