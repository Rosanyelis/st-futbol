@extends('layouts.app')
@section('title', 'Asignar Clubs - ' . $event->name)
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Asignar Clubs al Evento: {{ $event->name }}</h4>
                    <p class="card-text">Año: {{ $event->year }}</p>
                </div>
                <div class="card-body">
                    <!-- Formulario para asignar clubs -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Asignar Nuevo Club</h5>
                                </div>
                                <div class="card-body">
                                    <form id="assignClubForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="year" class="form-label">Año *</label>
                                            <input type="text" class="form-control" id="year" name="year" 
                                                   value="{{ $event->year }}" maxlength="4" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="club_id" class="form-label">Club *</label>
                                            <select class="form-select select2" id="club_id" name="club_id" required>
                                                <option value="">Seleccione un club</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-add-line me-1"></i>Asignar Club
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Información del Evento</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <img src="{{ asset('storage/' . $event->url_images) }}" 
                                                 alt="{{ $event->name }}" class="rounded-circle">
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $event->name }}</h6>
                                            <small class="text-muted">{{ $event->year }}</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Fecha de inicio:</small>
                                            <p class="mb-1">{{ \Carbon\Carbon::parse($event->start_date)->format('d/m/Y') }}</p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Fecha de fin:</small>
                                            <p class="mb-1">{{ \Carbon\Carbon::parse($event->end_date)->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de clubs asignados -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Clubs Asignados</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered datatables">
                                    <thead>
                                        <tr>
                                            <th>Club</th>
                                            <th>Año</th>
                                            <th>País</th>
                                            <th>Moneda</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                placeholder: 'Seleccione una opción',
                allowClear: true
            });

            // Inicializar DataTable
            const table = $('.datatables').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('event.assigned-clubs', $event->id) }}",
                    type: 'GET'
                },
                columns: [
                    { 
                        data: 'name',
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <img src="/storage/${row.logo}" alt="${row.name}" class="rounded-circle">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">${row.name}</h6>
                                        <small class="text-muted">${row.responsible}</small>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    { data: 'year' },
                    { data: 'country.name', defaultContent: '' },
                    { data: 'currency.name', defaultContent: '' },
                    { 
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json"
                }
            });

            // Cargar clubs disponibles cuando cambie el año
            $('#year').on('change', function() {
                loadAvailableClubs();
            });

            // Cargar clubs disponibles al inicio
            loadAvailableClubs();

            // Función para cargar clubs disponibles
            function loadAvailableClubs() {
                const year = $('#year').val();
                if (!year) return;

                $.ajax({
                    url: "{{ route('event.available-clubs', $event->id) }}",
                    type: 'GET',
                    data: { year: year },
                    success: function(response) {
                        const select = $('#club_id');
                        select.empty();
                        select.append('<option value="">Seleccione un club</option>');
                        
                        response.forEach(function(club) {
                            select.append(`<option value="${club.id}">${club.name}</option>`);
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Error al cargar los clubs disponibles', 'error');
                    }
                });
            }

            // Manejar envío del formulario
            $('#assignClubForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: "{{ route('event.assign-club', $event->id) }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Éxito', response.message, 'success');
                            $('#assignClubForm')[0].reset();
                            $('#club_id').empty().append('<option value="">Seleccione un club</option>');
                            table.ajax.reload();
                            loadAvailableClubs();
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response.message || 'Error al asignar el club', 'error');
                    }
                });
            });
        });

        // Función para desasignar club
        function detachClub(clubId, year) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea desasignar este club del evento?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, desasignar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/eventos/{{ $event->id }}/clubs/${clubId}/detach`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            year: year
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Éxito', response.message, 'success');
                                $('.datatables').DataTable().ajax.reload();
                                loadAvailableClubs();
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            Swal.fire('Error', response.message || 'Error al desasignar el club', 'error');
                        }
                    });
                }
            });
        }
    </script>
@endsection 