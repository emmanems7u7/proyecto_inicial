<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EquipoController extends Controller
{
    public function index()
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Equipos', 'url' => route('menus.index')],
        ];

        $equipos = Equipo::orderBy('nombre')->get();

        return view('equipos.index', compact('equipos', 'breadcrumb'));
    }

    public function sincronizar()
    {
        $response = Http::get(
            'https://www.thesportsdb.com/api/v1/json/3/eventsseason.php',
            [
                'id' => 4429
            ]
        );

        $eventos = $response->json()['events'] ?? [];

        foreach ($eventos as $evento) {

            Equipo::updateOrCreate(
                [
                    'api_id' => $evento['idHomeTeam']
                ],
                [
                    'nombre' => $evento['strHomeTeam'],
                    'logo' => $evento['strHomeTeamBadge']
                ]
            );

            Equipo::updateOrCreate(
                [
                    'api_id' => $evento['idAwayTeam']
                ],
                [
                    'nombre' => $evento['strAwayTeam'],
                    'logo' => $evento['strAwayTeamBadge']
                ]
            );
        }

        return redirect()
            ->route('equipos.index')
            ->with('status', 'Equipos sincronizados correctamente');
    }
}
