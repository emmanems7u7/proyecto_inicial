@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Transferencia {{ $transferencia->numero }} @include('ventas.partials.badge_estado', ['estado' => $transferencia->estado])</h5>
                <a href="{{ route('transferencias.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>

                @if ($transferencia->estado === 'pendiente' && $puedeEnviar)
                    @can('transferencias.enviar')
                        <form method="POST" action="{{ route('transferencias.enviar', $transferencia) }}" class="d-inline" onsubmit="return confirm('¿Enviar? El stock saldrá del almacén de origen.');">
                            @csrf <button class="btn btn-sm btn-primary"><i class="fas fa-paper-plane"></i> Enviar</button>
                        </form>
                    @endcan
                @endif
                @if ($transferencia->estado === 'en_transito' && $puedeRecibir)
                    @can('transferencias.recibir')
                        <form method="POST" action="{{ route('transferencias.recibir', $transferencia) }}" class="d-inline" onsubmit="return confirm('¿Confirmar recepción? El stock ingresará al almacén de destino.');">
                            @csrf <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Recibir</button>
                        </form>
                    @endcan
                @endif
                @if (in_array($transferencia->estado, ['pendiente', 'en_transito']) && $puedeEnviar)
                    @can('transferencias.anular')
                        <form method="POST" action="{{ route('transferencias.anular', $transferencia) }}" class="d-inline" onsubmit="return confirm('¿Anular la transferencia?');">
                            @csrf <button class="btn btn-sm btn-danger"><i class="fas fa-ban"></i> Anular</button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card"><div class="card-body">
            <small>Tipo: <strong>{{ $transferencia->tipo === 'entre_almacenes' ? 'Entre almacenes' : 'Entre sucursales' }}</strong> · Fecha: <strong>{{ $transferencia->fecha->format('d/m/Y') }}</strong></small><br>
            <small>Origen: <strong>{{ $transferencia->tiendaOrigen->nombre }} / {{ $transferencia->almacenOrigen->nombre }}</strong></small><br>
            <small>Destino: <strong>{{ $transferencia->tiendaDestino->nombre }} / {{ $transferencia->almacenDestino->nombre }}</strong></small><br>
            <small>Envía: <strong>{{ $transferencia->usuarioEnvia->nombre_completo ?? '—' }}</strong>
                @if ($transferencia->usuarioRecibe) · Recibe: <strong>{{ $transferencia->usuarioRecibe->nombre_completo }}</strong> ({{ $transferencia->fecha_recepcion?->format('d/m/Y H:i') }}) @endif</small>
            @if ($transferencia->observaciones) <br><small>Obs.: {{ $transferencia->observaciones }}</small> @endif
        </div></div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-12">
        <div class="card"><div class="card-body">
            @include('ventas.partials.alertas')
            <div class="table-responsive">
                <table class="table table-sm small-table">
                    <thead><tr><th>Producto</th><th>Lote</th><th>Vence</th><th class="text-end">Cantidad</th><th class="text-end">Recibido</th></tr></thead>
                    <tbody>
                        @foreach ($transferencia->detalles as $d)
                            <tr>
                                <td>{{ $d->producto->codigo }} - {{ $d->producto->nombre }}</td>
                                <td>{{ $d->lote->numero_lote ?? '—' }}</td>
                                <td>{{ $d->lote?->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-end">{{ \App\Support\Formato::cantidad($d->cantidad) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::cantidad($d->cantidad_recibida) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($transferencia->estado === 'pendiente')
                <small class="text-muted">Los lotes se asignan automáticamente (primero los que vencen antes) al enviar.</small>
            @endif
        </div></div>
    </div>
</div>

@endsection
