@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Nuevo Usuario</h5>

                    <a href="{{ route('user.index') }}" class="btn btn-sm btn-secondary"><i
                            class="ri-arrow-left-line me-1"></i> Regresar</a>
                </div>
                <div class="card-body">
                    <form class="needs-validation" action="{{ route('user.store') }}" method="POST">
                        @csrf
                        <div class="row gy-5">
                            <div class="col-md-4">
                                <div class="form-floating form-floating-outline">
                                    <input
                                        type="text"
                                        class="form-control form-control-sm {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                        id="name"
                                        name="name"
                                        placeholder="Ingrese nombre de Usuario"
                                        value="{{ old('name') }}"
                                        autofocus>
                                    <label for="name">Usuario</label>

                                    @if($errors->has('name'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('name') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-0" id="typepartida">
                                <div class="form-floating form-floating-outline">
                                    <select id="roles" class="form-select" name="rol">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="roles">Roles</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating form-floating-outline">
                                    <input
                                        type="text"
                                        class="form-control form-control-sm {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                        id="email"
                                        name="email"
                                        placeholder="Ingrese correo"
                                        value="{{ old('email') }}"
                                        autofocus>
                                    <label for="email">Correo Electrónico</label>

                                    @if($errors->has('email'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('email') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating form-floating-outline">
                                    <input
                                        type="text"
                                        class="form-control form-control-sm {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                        id="password"
                                        name="password"
                                        placeholder="************"
                                        value="{{ old('password') }}"
                                        autofocus>
                                    <label for="password">Contraseña</label>

                                    @if($errors->has('password'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('password') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-end">
                            <div class="mt-3 mb-3 col-md-1">
                                <button type="submit" class="btn btn-primary float-end">
                                    <i class="ri-save-2-line me-1"></i>
                                    Guardar
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
