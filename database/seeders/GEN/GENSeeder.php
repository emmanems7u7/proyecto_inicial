<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;


class GENSeeder extends Seeder
{
    public function run(): void
    {

        //Contenido minimo para levantar sistema
        $this->call(UserSeeder::class);
        $this->call(RolesPermissionsSeeder::class);
        $this->call(CategoriaSeeeder::class);
        $this->call(CatalogoSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(ConfiguracionSeeder::class);
        $this->call(ConfCorreoSeeder::class);
        $this->call(SeccionesSeeder::class);
        $this->call(MenusSeeder::class);
        $this->call(ConfiguracionCredencialesSeeder::class);

    }
}