@extends('ventas.emails._layout')

@section('titulo', $datos['titulo'] . ' por vencer o vencidas')

@section('contenido')
<p><strong>{{ $datos['tienda'] }}</strong>: documentos con vencimiento próximo o vencido.</p>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
    <tr><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">{{ $datos['tercero'] }}</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Documento</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Vence</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Días</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Saldo (Bs)</th></tr>
    @foreach ($datos['items'] as $i)
        <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $i['tercero'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $i['documento'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $i['vence'] }}</td>
            <td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;"><span style="color: {{ $i['dias'] < 0 ? '#b00020' : '#333' }};">{{ $i['dias'] }}</span></td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $i['saldo'] }}</td></tr>
    @endforeach
</table>
<p style="font-size: 12px; color: #777;">Días negativos = ya vencido.</p>
@endsection
