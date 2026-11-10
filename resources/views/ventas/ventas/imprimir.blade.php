<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $venta->numero }}</title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #222; margin: 24px; }
    h1 { font-size: 20px; margin: 0; } h2 { font-size: 14px; margin: 0 0 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border-bottom: 1px solid #ccc; padding: 5px 6px; text-align: left; }
    th { background: #f0f0f0; } .r { text-align: right; }
    .cab { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 8px; }
    .totales { width: 45%; margin-left: auto; }
    .no-print { margin-bottom: 12px; }
    @media print { .no-print { display: none; } body { margin: 0; } }
</style>
</head>
<body>
    <div class="no-print"><button onclick="window.print()">Imprimir / Guardar PDF</button></div>

    <div class="cab">
        <div>
            <h1>{{ $venta->tienda->empresa->nombre ?? $venta->tienda->nombre }}</h1>
            @if ($venta->tienda->empresa?->nit) <div>NIT: {{ $venta->tienda->empresa->nit }}</div> @endif
            <div>{{ $venta->tienda->nombre }} · {{ $venta->tienda->direccion }}</div>
            <div>{{ $venta->tienda->telefono }}</div>
        </div>
        <div class="r">
            <h2>COMPROBANTE DE VENTA</h2>
            <div><strong>{{ $venta->numero }}</strong></div>
            <div>{{ $venta->fecha->format('d/m/Y H:i') }}</div>
            @if ($venta->estado === 'anulada') <div style="color: #b00;"><strong>ANULADA</strong></div> @endif
        </div>
    </div>

    <p><strong>Cliente:</strong> {{ $venta->cliente->nombre ?? 'Consumidor final' }} {{ $venta->cliente->numero_documento ?? '' }}<br>
       <strong>Vendedor:</strong> {{ $venta->vendedor->nombre_completo ?? '' }} · <strong>Pago:</strong> {{ ucfirst($venta->tipo_pago) }}</p>

    <table>
        <thead><tr><th>Producto</th><th>Lote</th><th class="r">Cant.</th><th class="r">Precio</th><th class="r">Desc.</th><th class="r">Subtotal</th></tr></thead>
        <tbody>
            @foreach ($venta->detalles as $d)
                <tr>
                    <td>{{ $d->producto->nombre }}</td>
                    <td>{{ $d->lote->numero_lote ?? '' }}</td>
                    <td class="r">{{ \App\Support\Formato::cantidad($d->cantidad) }}</td>
                    <td class="r">{{ \App\Support\Formato::dinero($d->precio_unitario) }}</td>
                    <td class="r">{{ \App\Support\Formato::dinero($d->descuento) }}</td>
                    <td class="r">{{ \App\Support\Formato::dinero($d->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        <tr><td>Subtotal</td><td class="r">Bs {{ \App\Support\Formato::dinero($venta->subtotal) }}</td></tr>
        <tr><td>Descuento</td><td class="r">Bs {{ \App\Support\Formato::dinero($venta->descuento) }}</td></tr>
        <tr><td><strong>Total</strong></td><td class="r"><strong>Bs {{ \App\Support\Formato::dinero($venta->total) }}</strong></td></tr>
        <tr><td>Pagado</td><td class="r">Bs {{ \App\Support\Formato::dinero($venta->pagado) }}</td></tr>
        <tr><td>Saldo</td><td class="r">Bs {{ \App\Support\Formato::dinero($venta->saldo) }}</td></tr>
    </table>

    <p style="margin-top: 40px;">Gracias por su compra.</p>
</body>
</html>
