<?php

// app/Support/CorreoConfig.php

namespace App\Support;

use App\Models\ConfCorreo;
use Illuminate\Support\Facades\Mail;

class CorreoConfig
{
    /**
     * Toma la última fila de conf_correos y la deja como mailer por defecto.
     * Devuelve false si no hay configuración (queda lo que diga el .env).
     */
    public static function aplicar(): bool
    {
        try {
            $c = ConfCorreo::query()->latest('id')->first();
        } catch (\Throwable $e) {
            return false; // tabla no migrada, sin conexión, etc.
        }

        if (! $c) {
            return false;
        }

        $smtps = $c->encryption === 'ssl' || (int) $c->port === 465;

        config([
            'mail.default' => 'conf_correo',
            'mail.mailers.conf_correo' => [
                'transport'  => $c->mailer ?: 'smtp',
                'host'       => $c->host,
                'port'       => (int) $c->port,
                'username'   => $c->username,
                'password'   => $c->password,
                'encryption' => $c->encryption ?: null,        
                'scheme'     => $smtps ? 'smtps' : 'smtp',   
                'timeout'    => 30,
            ],
            'mail.from.address' => $c->from_address,
            'mail.from.name'    => $c->from_name,
        ]);

        Mail::purge('conf_correo'); 

        return true;
    }
}