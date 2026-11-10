@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Cotización {{ $cotizacion->numero }} @include('ventas.partials.badge_estado', ['estado' => $cotizacion->estado])</h5>
                <a href="{{ route('cotizaciones.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                <a href="{{ route('cotizaciones.imprimir', $cotizacion) }}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-print"></i> Imprimir / PDF</a>
                @if ($cotizacion->estado !== 'convertida')
                    @can('ventas.crear')
                        <a href="{{ route('ventas.create', ['cotizacion_id' => $cotizacion->id]) }}" class="btn btn-sm btn-success"><i class="fas fa-cash-register"></i> Convertir en venta</a>
                    @endcan
                @endif
                @if ($cotizacion->estado === 'pendiente')
                    @can('cotizaciones.editar')
                        <a href="{{ route('cotizaciones.edit', $cotizacion) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Editar</a>
                    @endcan
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card"><div class="card-body">
            <small>Tienda: <strong>{{ $cotizacion->tienda->nombre }}</strong></small><br>
            <small>Fecha: <strong>{{ $cotizacion->fecha->format('d/m/Y') }}</strong> · Válida hasta: <strong>{{ $cotizacion->fecha_validez?->format('d/m/Y') ?? '—' }}</strong></small><br>
            <small>Cliente: <strong>{{ $cotizacion->cliente->nombre ?? 'Sin cliente' }}</strong></small><br>
            <small>Vendedor: <strong>{{ $cotizacion->vendedor->nombre_completo ?? '—' }}</strong></small>
        </div></div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-12">
        <div class="card"><div class="card-body">
            @include('ventas.partials.alertas')
            <div class="table-responsive">
                <table class="table table-sm small-table">
                    <thead><tr><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Desc.</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($cotizacion->detalles as $d)
                            <tr>
                                <td>{{ $d->producto->codigo }} - {{ $d->producto->nombre }}</td>
                                <td class="text-end">{{ \App\Support\Formato::cantidad($d->cantidad) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->precio_unitario) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->descuento) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="4" class="text-end">Subtotal</td><td class="text-end">{{ \App\Support\Formato::dinero($cotizacion->subtotal) }}</td></tr>
                        <tr><td colspan="4" class="text-end">Descuento</td><td class="text-end">{{ \App\Support\Formato::dinero($cotizacion->descuento) }}</td></tr>
                        <tr><td colspan="4" class="text-end"><strong>Total</strong></td><td class="text-end"><strong>{{ \App\Support\Formato::dinero($cotizacion->total) }}</strong></td></tr>
                    </tfoot>
                </table>
            </div>
            @if ($cotizacion->observaciones) <p class="mb-2"><small>Obs.: {{ $cotizacion->observaciones }}</small></p> @endif

            @if ($cotizacion->estado !== 'convertida')
                @can('cotizaciones.editar')
                    <form method="POST" action="{{ route('cotizaciones.estado', $cotizacion) }}" class="d-flex gap-2 align-items-center">
                        @csrf
                        <select name="estado" class="form-select form-select-sm" style="max-width: 200px;">
                            <option value="pendiente" @selected($cotizacion->estado === 'pendiente')>Pendiente</option>
                            <option value="aceptada" @selected($cotizacion->estado === 'aceptada')>Aceptada</option>
                            <option value="rechazada" @selected($cotizacion->estado === 'rechazada')>Rechazada</option>
                        </select>
                        <button class="btn btn-sm btn-outline-primary mb-0">Cambiar estado</button>
                    </form>
                @endcan
            @endif
        </div></div>
    </div>
</div>

@endsection
