<?php

namespace App\Providers;

use App\Interfaces\CategoriaInterface;
use App\Repositories\CategoriaRepository;
use App\Interfaces\SeederInterface;
use App\Repositories\SeederRepository;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\UserInterface;
use App\Repositories\UserRepository;
use App\Interfaces\RoleInterface;
use App\Repositories\RoleRepository;
use App\Interfaces\PermisoInterface;
use App\Repositories\PermisoRepository;
use App\Interfaces\MenuInterface;
use App\Repositories\MenuRepository;
use App\Interfaces\CorreoInterface;
use App\Repositories\CorreoRepository;
use App\Interfaces\CatalogoInterface;
use App\Repositories\CatalogoRepository;


use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Seccion;
use App\Models\Configuracion;
use App\Models\ConfiguracionCredenciales;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CatalogoInterface::class, CatalogoRepository::class);
        $this->app->bind(MenuInterface::class, MenuRepository::class);
        $this->app->bind(CorreoInterface::class, CorreoRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(RoleInterface::class, RoleRepository::class);
        $this->app->bind(PermisoInterface::class, PermisoRepository::class);
        $this->app->bind(SeederInterface::class, SeederRepository::class);
        $this->app->bind(CategoriaInterface::class, CategoriaRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $agent = new Agent();

        // Mobile
        View::share('isMobile', $agent->isMobile());

        // Secciones
        $secciones = Cache::rememberForever('sidebar_secciones', function () {

            return Seccion::with('menus')
                ->orderBy('posicion')
                ->get();
        });

        // Config credenciales
        $config = Cache::rememberForever('config_credenciales', function () {

            return ConfiguracionCredenciales::first();
        });

        // Config general
        $configuracion = Cache::rememberForever('configuracion_general', function () {

            return Configuracion::first();
        });


        // Globales
        View::share([
            'secciones' => $secciones,
            'config' => $config,
            'configuracion' => $configuracion,
        ]);
    }
}
