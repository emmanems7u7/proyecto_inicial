<?php

namespace App\Services;
class ApiFootballService
{
    private string $url;

    public function __construct()
    {
        $this->url = 'https://v3.football.api-sports.io';
    }

    public function fixtures($from, $to)
    {
        return Http::withHeaders([
            'x-apisports-key' => env('API_FOOTBALL_KEY')
        ])->get(
                $this->url . '/fixtures',
                [
                    'from' => $from,
                    'to' => $to
                ]
            )->json();
    }
}