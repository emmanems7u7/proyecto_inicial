@extends('layouts.argon')

@section('content')

    <div class="card shadow-lg mx-4 card-profile-bottom">
        <div class="card-body p-3">
            <h3 class="font-weight-bolder text-info text-gradient">Crear un Nuevo Usuario</h3>
            <div class="row mt-3">
                <div class="col">
                    <p class="mb-0">Ingresa todos los datos obligatorios</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('usuarios._form', [
                    'btnText' => 'Registrar Usuario',

                ])
        </form>

                </div>
            </div>


@endsection