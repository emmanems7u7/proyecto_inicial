<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Partido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class PartidoController extends Controller
{

    public function index()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Partidos', 'url' => route('menus.index')],
        ];

        $partidos = Partido::with([
            'local',
            'visitante',
            'apuestas'
        ])
            ->withSum('apuestas', 'monto')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();



        return view('partidos.index', compact('partidos', 'breadcrumb'));
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

            $local = Equipo::where(
                'api_id',
                $evento['idHomeTeam']
            )->first();

            $visitante = Equipo::where(
                'api_id',
                $evento['idAwayTeam']
            )->first();

            if (!$local || !$visitante) {
                continue;
            }

            Partido::updateOrCreate(
                [
                    'api_event_id' => $evento['idEvent']
                ],
                [
                    'equipo_local_id' => $local->id,
                    'equipo_visitante_id' => $visitante->id,

                    'temporada' => $evento['strSeason'],
                    'liga' => $evento['strLeague'],
                    'ronda' => $evento['intRound'],

                    'fecha' => $evento['dateEvent'],
                    'hora' => $evento['strTime'],

                    'estadio' => $evento['strVenue'],
                    'pais' => $evento['strCountry'],

                    'goles_local' => $evento['intHomeScore'],
                    'goles_visitante' => $evento['intAwayScore'],

                    'estado' => $evento['strStatus'],

                    'thumbnail' => $evento['strThumb'],
                    'poster' => $evento['strPoster'],
                ]
            );
        }

        return redirect()
            ->route('partidos.index')
            ->with('success', 'Partidos sincronizados correctamente.');
    }
}
