<?php

namespace App\Http\Controllers;

use App\Models\Partido;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {

        $partidos = Partido::with([
            'local',
            'visitante',
            'apuestas'
        ])
            ->withSum('apuestas', 'monto')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();


        return view('welcome', compact('partidos'));
    }
}
