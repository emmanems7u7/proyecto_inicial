<?php

namespace App\Console\Commands;

use App\Services\AutomatizacionService;
use Illuminate\Console\Command;

class EjecutarAutomatizaciones extends Command
{
    protected $signature = 'ventas:automatizaciones';

    protected $description = 'Ejecuta las automatizaciones del sistema de ventas que ya les toca (cierre de caja, alertas, recordatorios, respaldo...)';

    public function handle(AutomatizacionService $servicio): int
    {
        $n = $servicio->ejecutarPendientes();
        $this->info("Automatizaciones ejecutadas: {$n}");

        return self::SUCCESS;
    }
}
