@extends('layouts.argon')

@section('content')

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Cerrar caja {{ $sesion->caja->nombre }}</h5>
                <p class="mb-1">Efectivo esperado según el sistema: <strong>Bs {{ \App\Support\Formato::dinero($esperado) }}</strong></p>
                <small class="text-muted">Efectivo inicial + ingresos en efectivo - egresos en efectivo.</small>

                <div class="mt-3">@include('ventas.partials.alertas')</div>

                <form method="POST" action="{{ route('caja.cerrar.store', $sesion) }}" autocomplete="off">
                    @csrf
                    <div class="row">
                        @include('ventas.partials.campo_texto', ['name' => 'monto_real', 'label' => 'Efectivo contado (Bs)', 'col' => 6, 'type' => 'number', 'step' => '0.01', 'min' => '0', 'required' => true])
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Diferencia</label>
                            <input type="text" id="diferencia" class="form-control" value="—" readonly>
                        </div>
                        @include('ventas.partials.campo_texto', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 12, 'maxlength' => 500])
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('caja.sesiones.show', $sesion) }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Cerrar la caja? Esta acción no se puede deshacer.');"><i class="fas fa-lock"></i> Cerrar caja</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('monto_real').addEventListener('input', function () {
    const esperado = {{ number_format($esperado, 2, '.', '') }};
    const real = parseFloat(this.value);
    document.getElementById('diferencia').value = isNaN(real) ? '—' : (real - esperado).toFixed(2);
});
</script>

@endsection
