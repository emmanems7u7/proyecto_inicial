<?php

namespace App\Jobs;

use App\Models\CajaSesion; // AJUSTA al nombre real de tu modelo
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class CerrarCajasAutomaticoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        CajaSesion::where('estado', 'abierta') // AJUSTA la columna y el valor
            ->pluck('id')
            ->each(fn($id) => CerrarSesionCajaJob::dispatch($id));
    }
}