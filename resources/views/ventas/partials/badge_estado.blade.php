@php
    $mapa = [
        'completada' => 'success', 'recibida' => 'success', 'aceptada' => 'success', 'pagada' => 'success', 'abierta' => 'success',
        'pedido' => 'info', 'parcial' => 'info', 'en_transito' => 'info', 'convertida' => 'info',
        'pendiente' => 'warning',
        'vencida' => 'danger', 'anulada' => 'danger', 'rechazada' => 'danger',
        'cerrada' => 'secondary',
    ];
@endphp
<span class="badge bg-gradient-{{ $mapa[$estado] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $estado)) }}</span>
