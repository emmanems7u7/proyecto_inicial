@extends('ventas.emails._layout')

@section('titulo', 'Bienvenido al sistema')

@section('contenido')
<p>Hola {{ $datos['nombre'] }},</p>
<p>Se creó tu usuario para el sistema de ventas.</p>
<p>Usuario: <strong>{{ $datos['email'] }}</strong>
@if (! empty($datos['password']))
    <br>Contraseña temporal: <strong>{{ $datos['password'] }}</strong>
@endif
</p>
<p><a href="{{ $datos['url'] }}">Ingresar al sistema</a></p>
<p style="font-size: 12px; color: #777;">Por seguridad, cambia tu contraseña en tu primer ingreso.</p>
@endsection
