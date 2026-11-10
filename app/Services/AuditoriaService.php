<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;

class AuditoriaService
{
    public static function registrar(string $modulo, string $accion, ?Model $modelo = null, ?array $antes = null, ?array $despues = null, ?int $tiendaId = null): void
    {
        try {
            Auditoria::create([
                'user_id' => auth()->id(),
                'tienda_id' => $tiendaId ?? ($modelo->tienda_id ?? null),
                'modulo' => $modulo,
                'accion' => $accion,
                'auditable_type' => $modelo?->getMorphClass(),
                'auditable_id' => $modelo?->getKey(),
                'valores_anteriores' => $antes,
                'valores_nuevos' => $despues,
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e); // la auditoría nunca debe romper la operación
        }
    }
}
