<?php

namespace App\Http\Controllers\Concerns;

use App\Support\TiendaContext;

trait UsaContexto
{
    /** Arma el $breadcrumb con el mismo formato del sistema. */
    protected function breadcrumb(array $items): array
    {
        $breadcrumb = [['name' => 'Inicio', 'url' => route('home')]];

        foreach ($items as $nombre => $url) {
            $breadcrumb[] = ['name' => $nombre, 'url' => $url ?? request()->url()];
        }

        return $breadcrumb;
    }

    protected function empresaId(): int
    {
        $id = auth()->user()->empresa_id;
        abort_if(! $id, 403, 'Tu usuario no tiene una empresa asignada.');

        return (int) $id;
    }

    protected function autorizarEmpresa($modelo): void
    {
        abort_unless((int) $modelo->empresa_id === $this->empresaId(), 404);
    }

    protected function autorizarTienda(int $tiendaId): void
    {
        abort_unless(in_array($tiendaId, array_map('intval', TiendaContext::ids()), true), 403, 'No tienes acceso a esta tienda.');
    }
}
