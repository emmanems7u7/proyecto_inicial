<?php

namespace App\Services;

use App\Models\ColaCorreo;
use Illuminate\Support\Facades\Mail;

/** Envía los correos pendientes de la cola (tabla cola_correos) con reintentos. */
class CorreoService
{
    public const MAX_INTENTOS = 3;

    /** @return array{enviados: int, errores: int} */
    public function procesarPendientes(int $limite = 50): array
    {
        $pendientes = ColaCorreo::where('estado', 'pendiente')
            ->where('intentos', '<', self::MAX_INTENTOS)
            ->where(fn ($q) => $q->whereNull('programado_at')->orWhere('programado_at', '<=', now()))
            ->orderBy('id')
            ->limit($limite)
            ->get();

        $enviados = 0;
        $errores = 0;

        foreach ($pendientes as $correo) {
            try {
                $this->enviar($correo);
                $correo->update(['estado' => 'enviado', 'enviado_at' => now(), 'intentos' => $correo->intentos + 1, 'error' => null]);
                $enviados++;
            } catch (\Throwable $e) {
                $intentos = $correo->intentos + 1;
                $correo->update([
                    'intentos' => $intentos,
                    'estado' => $intentos >= self::MAX_INTENTOS ? 'error' : 'pendiente',
                    'error' => mb_substr($e->getMessage(), 0, 1000),
                    'programado_at' => now()->addMinutes(5 * $intentos),
                ]);
                $errores++;
            }
        }

        return ['enviados' => $enviados, 'errores' => $errores];
    }

    public function reintentar(ColaCorreo $correo): void
    {
        $correo->update(['estado' => 'pendiente', 'intentos' => 0, 'error' => null, 'programado_at' => null]);
    }

    private function enviar(ColaCorreo $correo): void
    {
        $asunto = $correo->asunto;
        $destino = $correo->destinatario;

        if ($correo->cuerpo) {
            Mail::html($correo->cuerpo, fn ($m) => $m->to($destino)->subject($asunto));

            return;
        }

        Mail::send('ventas.emails.' . $correo->plantilla, ['datos' => $correo->datos ?? []], fn ($m) => $m->to($destino)->subject($asunto));
    }
}
