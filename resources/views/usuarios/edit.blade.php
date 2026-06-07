@extends('layouts.argon')

@section('content')

    <div class="card shadow-lg mx-4 card-profile-bottom">
        <div class="card-body p-3">
            <h3 class="font-weight-bolder text-info text-gradient">Actualizar Usuario</h3>

            <div class="row mt-3">
                <div class="col">

                </div>

            </div>

        </div>

    </div>

    <div class="card mt-3">
        <div class="card-body ">
            <div class="row mt-3">
                <form method="POST" id="form_edit" action="{{ route('users.update', ['id' => $user->id, 'perfil' => 0]) }}">
                    @csrf
                    @method('PUT')

                    @include('usuarios._form', [
                        'user' => $user,

                        'btnText' => 'Actualizar Usuario',
                    ])
                        </form>
                    </div>
                </div>
            </div>




@endsection