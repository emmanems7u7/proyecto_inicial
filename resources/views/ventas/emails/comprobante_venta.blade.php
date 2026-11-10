@extends('ventas.emails._layout')

@section('titulo', 'Comprobante de venta ' . $datos['numero'])

@section('contenido')
<p>Hola {{ $datos['cliente'] }},</p>
<p>Gracias por tu compra en <strong>{{ $datos['empresa'] }}</strong> ({{ $datos['tienda'] }}). Este es el detalle de tu operación del {{ $datos['fecha'] }}:</p>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
    <tr><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Producto</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Cant.</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Precio</th><th style="text-align: left; padding: 6px; background: #f0f0f0; border-bottom: 1px solid #ddd;">Subtotal</th></tr>
    @foreach ($datos['items'] as $i)
        <tr><td style="padding: 6px; border-bottom: 1px solid #eee;">{{ $i['producto'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $i['cantidad'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $i['precio'] }}</td><td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right;">{{ $i['subtotal'] }}</td></tr>
    @endforeach
</table>
<p style="text-align: right;">
    Subtotal: Bs {{ $datos['subtotal'] }}<br>
    Descuento: Bs {{ $datos['descuento'] }}<br>
    <strong>Total: Bs {{ $datos['total'] }}</strong><br>
    Pagado: Bs {{ $datos['pagado'] }} · Saldo: Bs {{ $datos['saldo'] }}
</p>
<p style="font-size: 12px; color: #777;">Comprobante {{ $datos['numero'] }} · Pago {{ $datos['tipo_pago'] }}.</p>
@endsection
