<?php

namespace App\Http\Middleware;

use App\Support\TiendaContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ContextoTienda
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            View::share('tiendaActual', TiendaContext::actual());
            View::share('tiendasPermitidas', TiendaContext::permitidas());
        }

        return $next($request);
    }
}
