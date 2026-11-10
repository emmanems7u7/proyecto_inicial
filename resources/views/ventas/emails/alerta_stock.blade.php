@extends('ventas.emails._layout')

@section('titulo', 'Alerta de stock mínimo')

@section('contenido')
<p>Estos productos de <strong>{{ $datos['tienda'] }}</strong> llegaron o bajaron del stock mínimo:</p>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
    <tr><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Producto</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Almacén</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Stock</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Mínimo</th></tr>
    @foreach ($datos['items'] as $i)
        <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $i['producto'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $i['almacen'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $i['stock'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $i['minimo'] }}</td></tr>
    @endforeach
</table>
@endsection
