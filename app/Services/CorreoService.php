<?php

namespace App\Services;

use App\Jobs\EnviarCorreoJob;
use App\Models\ColaCorreo;
use Illuminate\Support\Facades\Mail;

/** Registra correos en cola_correos y los envía mediante la cola real de Laravel. */
class CorreoService
{
    /**
     * Único punto de entrada para mandar un correo desde cualquier automatización.
     * Usa $cuerpo (HTML) o $plantilla (vista ventas.emails.{plantilla}) con $datos.
     */
    public function encolar(
        string $destinatario,
        string $asunto,
        ?string $cuerpo = null,
        ?string $plantilla = null,
        array $datos = [],
        ?int $tiendaId = null,
        $programadoAt = null,
    ): ColaCorreo {
        $correo = ColaCorreo::create([
            'tienda_id' => $tiendaId,
            'destinatario' => $destinatario,
            'asunto' => $asunto,
            'cuerpo' => $cuerpo,
            'plantilla' => $plantilla,
            'datos' => $datos,
            'estado' => 'pendiente',
            'intentos' => 0,
            'programado_at' => $programadoAt,
        ]);

        $this->despachar($correo);

        return $correo;
    }

    /** Pone el job en la cola de Laravel (con retraso si está programado a futuro). */
    public function despachar(ColaCorreo $correo): void
    {
        $pendiente = EnviarCorreoJob::dispatch($correo->id)->afterCommit();

        if ($correo->programado_at && $correo->programado_at->isFuture()) {
            $pendiente->delay($correo->programado_at);
        }
    }

    /** Botón "Reintentar" de la vista: vuelve a pendiente y lo manda a la cola real. */
    public function reintentar(ColaCorreo $correo): void
    {
        $correo->update(['estado' => 'pendiente', 'intentos' => 0, 'error' => null, 'programado_at' => null]);

        $this->despachar($correo);
    }

    /** Lo ejecuta el job. Envía con el mailer por defecto (conf_correos). */
    public function enviar(ColaCorreo $correo): void
    {
        $asunto = $correo->asunto;
        $destino = $correo->destinatario;

        if ($correo->cuerpo) {
            Mail::html($correo->cuerpo, fn($m) => $m->to($destino)->subject($asunto));

            return;
        }

        Mail::send(
            'ventas.emails.' . $correo->plantilla,
            ['datos' => $correo->datos ?? []],
            fn($m) => $m->to($destino)->subject($asunto)
        );
    }
}