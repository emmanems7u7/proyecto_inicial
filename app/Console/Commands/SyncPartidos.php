<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncPartidos extends Command
{
    protected $signature = 'futbol:sync';

    public function handle(ApiFootballService $api)
    {
        $response = $api->fixtures(
            now()->format('Y-m-d'),
            now()->addMonths(2)->format('Y-m-d')
        );

        foreach ($response['response'] as $fixture) {

            $local = Equipo::updateOrCreate(
                [
                    'api_id' => $fixture['teams']['home']['id']
                ],
                [
                    'nombre' => $fixture['teams']['home']['name'],
                    'logo' => $fixture['teams']['home']['logo']
                ]
            );

            $visitante = Equipo::updateOrCreate(
                [
                    'api_id' => $fixture['teams']['away']['id']
                ],
                [
                    'nombre' => $fixture['teams']['away']['name'],
                    'logo' => $fixture['teams']['away']['logo']
                ]
            );

            Partido::updateOrCreate(
                [
                    'api_id' => $fixture['fixture']['id']
                ],
                [
                    'local_id' => $local->id,
                    'visitante_id' => $visitante->id,
                    'fecha' => $fixture['fixture']['date'],
                    'goles_local' => $fixture['goals']['home'],
                    'goles_visitante' => $fixture['goals']['away'],
                    'estado' => $fixture['fixture']['status']['short'],
                    'finalizado' =>
                        $fixture['fixture']['status']['short'] == 'FT'
                ]
            );
        }
    }
}