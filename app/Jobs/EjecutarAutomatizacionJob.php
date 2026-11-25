<?php

namespace App\Jobs;

use App\Models\Automatizacion;
use App\Services\AutomatizacionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Ejecuta UNA automatización en la cola de Laravel.
 * ShouldBeUnique evita que el tick de cada minuto la encole varias veces.
 */
class EjecutarAutomatizacionJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;        // ejecutar() ya captura errores y los guarda en el historial
    public int $timeout = 1200;   // el respaldo de BD puede tardar
    public int $uniqueFor = 3600; // si el worker muere, el bloqueo expira en 1 hora

    public function __construct(public int $automatizacionId)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->automatizacionId;
    }

    public function handle(AutomatizacionService $servicio): void
    {
        $a = Automatizacion::find($this->automatizacionId);

        // Se vuelve a comprobar: si la ejecutaron a mano mientras esperaba, no se repite
        if ($a && $servicio->estaPendiente($a)) {
            $servicio->ejecutar($a);
        }
    }
}