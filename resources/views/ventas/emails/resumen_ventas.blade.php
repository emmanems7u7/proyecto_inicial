@extends('ventas.emails._layout')

@section('titulo', 'Resumen de ventas ' . $datos['fecha'])

@section('contenido')
<p>Resumen del día para <strong>{{ $datos['tienda'] }}</strong>:</p>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size: 14px;">
    <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">Ventas realizadas</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $datos['cantidad'] }}</td></tr>
    <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">Total vendido</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;"><strong>Bs {{ $datos['total'] }}</strong></td></tr>
    <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">Contado</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">Bs {{ $datos['contado'] }}</td></tr>
    <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">Crédito</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">Bs {{ $datos['credito'] }}</td></tr>
    <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">Devoluciones</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">Bs {{ $datos['devoluciones'] }}</td></tr>
    <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">Cajas aún abiertas</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $datos['cajas_abiertas'] }}</td></tr>
</table>
@endsection
