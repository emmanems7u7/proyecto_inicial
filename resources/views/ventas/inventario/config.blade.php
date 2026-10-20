@extends('layouts.argon')

@section('content')

<div class="row">
    <div class="col-lg-7">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Configurar stock</h5>
                <p class="mb-1"><strong>{{ $inventario->producto->codigo }}</strong> - {{ $inventario->producto->nombre }}</p>
                <p class="text-muted">{{ $inventario->almacen->nombre_completo }} · Stock actual: {{ \App\Support\Formato::cantidad($inventario->cantidad) }}</p>

                @include('ventas.partials.alertas')

                <form method="POST" action="{{ route('inventario.config.update', $inventario) }}" autocomplete="off">
                    @csrf
                    @method('PUT')
                    @include('ventas.inventario._form_config')

                    <div class="d-flex gap-2">
                        <a href="{{ route('inventario.index', ['almacen_id' => $inventario->almacen_id]) }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
