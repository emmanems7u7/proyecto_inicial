@extends('ventas.emails._layout')

@section('titulo', 'Recordatorio de pago')

@section('contenido')
<p>Estimado(a) {{ $datos['cliente'] }},</p>
<p>Le recordamos que el documento <strong>{{ $datos['documento'] }}</strong> de <strong>{{ $datos['tienda'] }}</strong> tiene un saldo pendiente de <strong>Bs {{ $datos['saldo'] }}</strong>.</p>
<p>
    Fecha de vencimiento: {{ $datos['vence'] }}
    @if ($datos['dias'] < 0)
        <span style="color: #b00020;">(vencido hace {{ abs($datos['dias']) }} días)</span>
    @elseif ($datos['dias'] === 0)
        (vence hoy)
    @else
        (vence en {{ $datos['dias'] }} días)
    @endif
</p>
<p>Si ya realizó el pago, por favor ignore este mensaje. Gracias.</p>
@endsection
