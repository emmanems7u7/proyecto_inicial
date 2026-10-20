<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Support\TiendaContext;
use Illuminate\Http\Request;

class TiendaActivaController extends Controller
{
    public function cambiar(Request $request)
    {
        $request->validate(['tienda_id' => ['required', 'integer']]);

        abort_unless(TiendaContext::establecer((int) $request->input('tienda_id')), 403, 'No tienes acceso a esa tienda.');

        return back()->with('success', 'Tienda activa: ' . TiendaContext::actual()->nombre);
    }
}
