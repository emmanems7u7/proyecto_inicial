<?php

namespace App\Jobs;

use App\Models\CajaSesion; // AJUSTA
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class CerrarSesionCajaJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;

    public function __construct(public int $sesionId)
    {
    }

    /** Evita que la misma sesión se cierre dos veces a la vez. */
    public function uniqueId(): string
    {
        return (string) $this->sesionId;
    }

    public function handle(): void
    {
        $sesion = CajaSesion::find($this->sesionId);

        // Idempotente: si ya se cerró (por el usuario o por otro job), no hacer nada
        if (!$sesion || $sesion->estado !== 'abierta') { // AJUSTA
            return;
        }

        // AJUSTA: llama aquí a la MISMA lógica que usa el cierre manual,
        // para que arqueo, movimientos y totales queden idénticos.
        // app(\App\Services\CajaService::class)->cerrarAutomatico($sesion);

        // Opcional: avisar por correo (también por la cola)
        // app(\App\Services\CorreoService::class)->encolar(
        //     $destinatario, 'Cierre automático de caja', plantilla: 'cierre_caja',
        //     datos: ['sesion' => $sesion->id], tiendaId: $sesion->tienda_id
        // );
    }
}