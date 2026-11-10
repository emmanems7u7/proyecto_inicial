<?php

namespace App\Listeners;

use App\Models\Acceso;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/**
 * Historial de accesos. Laravel 11+ lo detecta solo (métodos handle*).
 * En Laravel 10 registra los tres eventos en EventServiceProvider (ver LEEME).
 */
class RegistrarAcceso
{
    public function handleLogin(Login $event): void
    {
        $this->guardar($event->user, null, 'login');
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            $this->guardar($event->user, null, 'logout');
        }
    }

    public function handleFailed(Failed $event): void
    {
        $credenciales = $event->credentials ?? [];
        $email = $credenciales['email'] ?? $credenciales['usuario'] ?? $credenciales['username'] ?? null;

        $this->guardar($event->user, is_string($email) ? $email : null, 'fallido');
    }

    private function guardar($user, ?string $email, string $evento): void
    {
        try {
            Acceso::create([
                'user_id' => $user?->id,
                'tienda_id' => session('tienda_id'),
                'email_intentado' => $email ?? ($evento === 'fallido' ? null : ($user->email ?? null)),
                'evento' => $evento,
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
