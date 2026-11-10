@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')
<style>@media print { .no-print { display: none !important; } }</style>

@php
    $reportes = [
        ['reportes.ventas', 'Ventas', 'reportes.ventas'],
        ['reportes.inventario', 'Inventario', 'reportes.inventario'],
        ['reportes.compras', 'Compras', 'reportes.compras'],
        ['reportes.cuentas', 'Cuentas', 'reportes.cuentas'],
        ['reportes.caja', 'Caja', 'reportes.caja'],
        ['reportes.globales', 'Globales', 'reportes.globales'],
        ['reportes.auditoria', 'Auditoría', 'reportes.auditoria'],
        ['reportes.accesos', 'Accesos', 'reportes.auditoria'],
    ];
@endphp

<div class="row no-print">
    <div class="col-md-8">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>{{ $titulo }}</h5>
                <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-sm btn-success"><i class="fas fa-file-excel"></i> Exportar Excel (CSV)</a>
                <button type="button" class="btn btn-sm btn-info" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / PDF</button>
                <div class="mt-2">
                    @foreach ($reportes as [$ruta_r, $etiqueta, $permiso])
                        @can($permiso)
                            <a href="{{ route($ruta_r) }}" class="badge bg-gradient-{{ $ruta_r === $ruta ? 'primary' : 'secondary' }} text-decoration-none me-1">{{ $etiqueta }}</a>
                        @endcan
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <small><i class="fas fa-list me-1"></i>Registros: <strong>{{ count($filas) }}</strong></small><br>
                @foreach ($resumen as $etiqueta => $valor)
                    <small><i class="fas fa-info-circle me-1"></i>{{ $etiqueta }}: <strong>{{ $valor }}</strong></small><br>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route($ruta) }}" class="row g-2 mb-3 no-print">
                    @foreach ($filtros as $f)
                        <div class="col-md-2">
                            <label class="form-label mb-0 small">{{ $f['label'] }}</label>
                            @if ($f['type'] === 'select')
                                <select name="{{ $f['name'] }}" class="form-select form-select-sm">
                                    @if (! empty($f['vacio'])) <option value="">{{ $f['vacio'] }}</option> @endif
                                    @foreach ($f['options'] as $k => $v)
                                        <option value="{{ $k }}" @selected((string) ($f['value'] ?? '') === (string) $k)>{{ $v }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $f['type'] }}" name="{{ $f['name'] }}" value="{{ $f['value'] ?? '' }}" class="form-control form-control-sm">
                            @endif
                        </div>
                    @endforeach
                    <div class="col-auto d-flex align-items-end">
                        <button class="btn btn-sm btn-info mb-0"><i class="fas fa-search"></i> Generar</button>
                    </div>
                </form>

                <h6 class="d-none d-print-block">{{ $titulo }} - {{ now()->format('d/m/Y H:i') }}</h6>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-items-center small-table">
                        <thead>
                            <tr>
                                @foreach ($cabeceras as $i => $c)
                                    <th class="{{ $alineacion[$i] }}">{{ $c }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($filas as $fila)
                                <tr>
                                    @foreach ($fila as $i => $celda)
                                        <td class="{{ $alineacion[$i] }}">{{ $celda }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($cabeceras) }}" class="text-center text-muted py-3">Sin datos para los filtros elegidos.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($totales)
                            <tfoot>
                                <tr>
                                    @foreach ($totales as $i => $celda)
                                        <td class="{{ $alineacion[$i] }}"><strong>{{ $celda }}</strong></td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
