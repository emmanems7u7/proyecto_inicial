<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cotización {{ $cotizacion->numero }}</title>
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
            <h1>{{ $cotizacion->tienda->empresa->nombre ?? $cotizacion->tienda->nombre }}</h1>
            <div>{{ $cotizacion->tienda->nombre }} · {{ $cotizacion->tienda->direccion }}</div>
            <div>{{ $cotizacion->tienda->telefono }} {{ $cotizacion->tienda->email }}</div>
        </div>
        <div class="r">
            <h2>COTIZACIÓN</h2>
            <div><strong>{{ $cotizacion->numero }}</strong></div>
            <div>Fecha: {{ $cotizacion->fecha->format('d/m/Y') }}</div>
            <div>Válida hasta: {{ $cotizacion->fecha_validez?->format('d/m/Y') ?? '—' }}</div>
        </div>
    </div>

    <p><strong>Cliente:</strong> {{ $cotizacion->cliente->nombre ?? '—' }} {{ $cotizacion->cliente->numero_documento ?? '' }}<br>
       <strong>Atendió:</strong> {{ $cotizacion->vendedor->nombre_completo ?? '' }}</p>

    <table>
        <thead><tr><th>Producto</th><th>Unidad</th><th class="r">Cant.</th><th class="r">Precio</th><th class="r">Desc.</th><th class="r">Subtotal</th></tr></thead>
        <tbody>
            @foreach ($cotizacion->detalles as $d)
                <tr>
                    <td>{{ $d->producto->codigo }} - {{ $d->producto->nombre }}</td>
                    <td>{{ $d->producto->unidad->catalogo_descripcion ?? '' }}</td>
                    <td class="r">{{ \App\Support\Formato::cantidad($d->cantidad) }}</td>
                    <td class="r">{{ \App\Support\Formato::dinero($d->precio_unitario) }}</td>
                    <td class="r">{{ \App\Support\Formato::dinero($d->descuento) }}</td>
                    <td class="r">{{ \App\Support\Formato::dinero($d->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        <tr><td>Subtotal</td><td class="r">Bs {{ \App\Support\Formato::dinero($cotizacion->subtotal) }}</td></tr>
        <tr><td>Descuento</td><td class="r">Bs {{ \App\Support\Formato::dinero($cotizacion->descuento) }}</td></tr>
        <tr><td><strong>Total</strong></td><td class="r"><strong>Bs {{ \App\Support\Formato::dinero($cotizacion->total) }}</strong></td></tr>
    </table>

    @if ($cotizacion->observaciones) <p>{{ $cotizacion->observaciones }}</p> @endif
</body>
</html>
