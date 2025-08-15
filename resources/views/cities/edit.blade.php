@extends('layouts.app')
@section('title', 'Ciudades - Editar')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Editar Ciudad</h5>

                        <a href="{{ route('city.index') }}" class="btn btn-sm btn-secondary"
                        ><i class="ri-arrow-left-line me-1"></i> Regresar</a>
                    </div>


                    <div class="card-body">
                        <form id="formTask" class="needs-validation" action="{{ route('city.update', $city->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row gy-5">
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-control form-control-sm select2 {{ $errors->has('country_id') ? 'is-invalid' : '' }}" id="country_id" name="country_id">
                                            <option value="">Seleccione el pais</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}" {{ $city->province->country_id == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="country_id">Pais</label>

                                        
                                        @if($errors->has('country_id'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('country_id') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-control form-control-sm select2 {{ $errors->has('province_id') ? 'is-invalid' : '' }}" id="province_id" name="province_id">
                                            <option value="">Seleccione una provincia</option>
                                            
                                        </select>
                                        <label for="province_id">Provincia</label>

                                        @if($errors->has('province_id'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('province_id') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                            id="name"
                                            name="name"
                                            placeholder="Ingrese nombre de Ciudad"
                                            value="{{ old('name', $city->name) }}"
                                            autofocus>
                                        <label for="name">Ciudad</label>

                                        @if($errors->has('name'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('name') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                            </div>
                            
                            <div class="row justify-content-end">
                                <div class="mt-3 col-md-1">
                                    <button type="submit" class="btn btn-primary float-end">
                                        <i class="ri-save-2-line me-1"></i>
                                        Actualizar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
    <!-- Page JS -->
    <script>
        // Función para esperar a que jQuery esté disponible
        function waitForJQuery(callback) {
            if (typeof $ !== 'undefined') {
                callback();
            } else {
                setTimeout(function() {
                    waitForJQuery(callback);
                }, 100);
            }
        }

        // Esperar a que jQuery esté completamente cargado
        waitForJQuery(function() {
            // Inicializar el cambio de país
            $('#country_id').change(function() {
                var country_id = $(this).val();
                $.ajax({
                    url: '{{ route('province.get-provinces') }}?country_id=' + country_id,
                    type: 'GET',
                    success: function(response) {
                        let province_id = '{{ $city->province_id }}';
                        $('#province_id').empty();
                        $('#province_id').append('<option value="">Seleccione una provincia</option>');
                        $.each(response, function(index, province) {
                            $('#province_id').append('<option value="' + province.id + '" ' + (province_id == province.id ? 'selected' : '') + '>' + province.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            });

            // Cargar las provincias iniciales si hay un país seleccionado
            var initialCountryId = $('#country_id').val();
            if (initialCountryId) {
                $('#country_id').trigger('change');
            }
        });
    </script>
