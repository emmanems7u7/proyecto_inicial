@extends('layouts.app')

@section('content')
    @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <div class="container">
        <h2>Verificación de código</h2>

        <form method="POST" action="{{ route('verify.store') }}">
            @csrf
            <div class="form-group">
                <label for="code">Ingresa el código enviado a tu correo:</label>
                <input type="text" name="code" class="form-control" required autofocus>
                @error('code')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary mt-3">Verificar</button>
        </form>

        <form method="POST" action="{{ route('verify.resend') }}" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-secondary">Reenviar código</button>
        </form>
    </div>
@endsection