@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-12">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Devolución de la venta {{ $venta->numero }}</h5>
                <small><i class="fas fa-info-circle me-1"></i>El stock vuelve al almacén. Si la venta fue a crédito se descuenta de la deuda; si ya estaba pagada se reembolsa en efectivo desde tu caja abierta.</small>

                <div class="mt-3">@include('ventas.partials.alertas')</div>

                <form method="POST" action="{{ route('ventas.devolucion.store', $venta) }}" autocomplete="off">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-sm small-table align-items-center">
                            <thead><tr><th>Producto</th><th>Lote</th><th class="text-end">Vendido</th><th class="text-end">Devuelto</th><th class="text-end">Disponible</th><th class="text-end">Precio</th><th style="width: 140px;">A devolver</th></tr></thead>
                            <tbody>
                                @foreach ($pendientes as $p)
                                    <tr>
                                        <td>{{ $p->producto->codigo }} - {{ $p->producto->nombre }}</td>
                                        <td>{{ $p->lote->numero_lote ?? '—' }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::cantidad($p->vendido) }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::cantidad($p->devuelto) }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::cantidad($p->disponible) }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($p->precio) }}</td>
                                        <td>
                                            @if ($p->disponible > 0)
                                                <input type="number" step="0.001" min="0" max="{{ $p->disponible }}" name="cantidades[{{ $p->clave }}]"
                                                       value="{{ old('cantidades.' . $p->clave, 0) }}" class="form-control form-control-sm">
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        @include('ventas.partials.campo_texto', ['name' => 'motivo', 'label' => 'Motivo de la devolución', 'col' => 8, 'maxlength' => 255])
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('ventas.show', $venta) }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-undo"></i> Registrar devolución</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
