<?php

namespace App\Jobs;

use App\Models\ColaCorreo;
use App\Services\CorreoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class EnviarCorreoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(public int $colaCorreoId)
    {
    }

    /** Espera entre reintentos: 1 min, 5 min, 15 min. */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(CorreoService $service): void
    {
        $correo = ColaCorreo::find($this->colaCorreoId);

        // Ya enviado o borrado: no hacer nada (evita duplicados si el job se repite)
        if (!$correo || $correo->estado === 'enviado') {
            return;
        }

        $correo->update(['intentos' => $this->attempts()]);

        try {
            $service->enviar($correo);
        } catch (\Throwable $e) {
            $correo->update(['error' => mb_substr($e->getMessage(), 0, 1000)]);
            throw $e; // Laravel reintenta según tries y backoff
        }

        $correo->update(['estado' => 'enviado', 'enviado_at' => now(), 'error' => null]);
    }

    /** Se ejecuta cuando se agotaron todos los intentos. */
    public function failed(\Throwable $e): void
    {
        ColaCorreo::whereKey($this->colaCorreoId)->update([
            'estado' => 'error',
            'error' => mb_substr($e->getMessage(), 0, 1000),
        ]);
    }
}