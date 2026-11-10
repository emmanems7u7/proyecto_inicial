@extends('ventas.emails._layout')

@section('titulo', 'Cierre automático de cajas')

@section('contenido')
<p>Se cerraron automáticamente las siguientes cajas de <strong>{{ $datos['tienda'] }}</strong> ({{ $datos['fecha'] }}). El arqueo se asumió igual al esperado: revisa el conteo físico.</p>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
    <tr><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Caja</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Cajero</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Apertura</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Ingresos</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Egresos</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Esperado</th></tr>
    @foreach ($datos['cajas'] as $c)
        <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $c['caja'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $c['usuario'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $c['apertura'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $c['ingresos'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $c['egresos'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $c['esperado'] }}</td></tr>
    @endforeach
</table>
@endsection
