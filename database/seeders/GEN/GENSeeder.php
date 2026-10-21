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
        $this->call(PermisosSistemaSeeder::class);
        $this->call(RolesSistemaSeeder::class);

        $this->call(CatalogosSistemaSeeder::class);

        // para pruebas de desarrollo, se puede comentar en producción
        $this->call([
            CatalogoSisSeeder::class,           // 1. Catálogos (documentos, ciudades, unidades, motivos)
            EmpresaSeeder::class,            // 2. Empresa
            TiendaSeeder::class,             // 3. Tiendas
            AlmacenSeeder::class,            // 4. Almacenes (2 por tienda)
            TiendaUsuarioSeeder::class,      // 5. Usuarios <-> Tiendas
            CategoriaProductoSeeder::class,  // 6. Categorías
            ProductoSeeder::class,           // 7. Productos
            ClienteSeeder::class,            // 8. Clientes
            ProveedorSeeder::class,          // 9. Proveedores
            InventarioSeeder::class,         // 10. Stock, lotes y kardex
        ]);
    }
}