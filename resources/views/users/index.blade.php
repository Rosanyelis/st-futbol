@extends('layouts.app')
@section('title', 'Usuarios')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <div class="card-header header-elements border-bottom">
            <h5 class="mb-0 me-2">Usuarios</h5>

            <div class="card-header-elements ms-auto">
                <a href="{{ route('user.create') }}" class="btn btn-sm btn-primary"
                >Crear usuario</a>
            </div>
        </div>

        <div class="card-datatable text-nowrap">
            <table class="datatables-user table table-sm">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th style="width: 10px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach ($user->getRoleNames() as $d)
                                <span class="badge bg-secondary">{{ $d }}</span>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('user.edit', $user->id) }}" class="btn btn-sm btn-icon btn-text-secondary
                                    rounded-pill"
                                    data-bs-toggle="tooltip" title="Actualizar Usuario">
                                    <i class="ri-pencil-fill ri-20px"></i>
                                </a>
                                <a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary
                                    rounded-pill text-danger" onclick="deleteRecord({{ $user->id }})"
                                    data-bs-toggle="tooltip" title="Eliminar Usuario">
                                    <i class="ri-delete-bin-7-fill ri-20px"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!--/ Ajax Sourced Server-side -->

</div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('pagesjs/user.js') }}"></script>
@endsection
