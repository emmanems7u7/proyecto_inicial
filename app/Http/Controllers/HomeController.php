<?php

namespace App\Http\Controllers;

use App\Models\CamposForm;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ConfiguracionCredenciales;
use App\Models\ContenedorGrid;
use App\Models\Formulario;
use App\Models\RespuestasCampo;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{


    protected $FormularioRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $rolNombre = $user->roles->first()?->name;


        return view('home', [
            'breadcrumb' => [
                ['name' => 'Inicio', 'url' => route('home')],
            ],
        ]);
    }

}
