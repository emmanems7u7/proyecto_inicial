@extends('layouts.argon')

@section('content')
    <div class="card shadow-lg mx-4 card-profile-bottom">
        <div class="card-body">
            <div class="container">
                <h2><i class="fas fa-edit"></i> {{ __('ui.edit_text') }} {{ __('lo.categoria') }}</h2>
                <form action="{{ route('categorias.update', $id) }}" method="POST">
                    @method('PUT')
                    @include('catalogo._formCategoria')
                </form>
            </div>
        </div>
    </div>

@endsection