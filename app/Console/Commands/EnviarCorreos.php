<?php

namespace App\Console\Commands;

use App\Services\CorreoService;
use Illuminate\Console\Command;

class EnviarCorreos extends Command
{
    protected $signature = 'ventas:enviar-correos {--limite=50}';

    protected $description = 'Envía los correos pendientes de la cola (cola_correos)';

    public function handle(CorreoService $correos): int
    {
        $r = $correos->procesarPendientes((int) $this->option('limite'));
        $this->info("Enviados: {$r['enviados']} · Con error: {$r['errores']}");

        return self::SUCCESS;
    }
}
