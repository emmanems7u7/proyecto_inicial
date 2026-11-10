@extends('ventas.emails._layout')

@section('titulo', 'Alerta de vencimiento de lotes')

@section('contenido')
<p>Lotes de <strong>{{ $datos['tienda'] }}</strong> próximos a vencer o ya vencidos:</p>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
    <tr><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Producto</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Almacén</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Lote</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Vence</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Días</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Cant.</th></tr>
    @foreach ($datos['items'] as $i)
        <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $i['producto'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $i['almacen'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $i['lote'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $i['vence'] }}</td>
            <td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;"><span style="color: {{ $i['dias'] < 0 ? '#b00020' : '#333' }};">{{ $i['dias'] }}</span></td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $i['cantidad'] }}</td></tr>
    @endforeach
</table>
@endsection
