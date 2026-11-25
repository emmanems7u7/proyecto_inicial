<?php

namespace App\Listeners;

use App\Support\TiendaContext;
use Illuminate\Auth\Events\Login;

/**
 * Al iniciar sesión, si el usuario tiene acceso a más de una tienda, se le muestra "Mis tiendas".
 * Usa la URL "intended" de Laravel (la respeta el login estándar: redirect()->intended()).
 * Laravel 10: registrar en EventServiceProvider::$listen => Login::class => [[IrAMisTiendas::class, 'handleLogin']]
 */
class IrAMisTiendas
{
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        if (! $user || ! $user->empresa_id) {
            return;
        }
        if ($user->hasAnyRole(TiendaContext::ROLES_GLOBALES) || $user->tiendas()->wherePivot('estado', 1)->count() > 1) {
            session()->forget('tienda_id');
            session()->put('url.intended', route('mis_tiendas.index'));
        }
    }
}
