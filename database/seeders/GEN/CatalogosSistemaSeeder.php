<?php

namespace Database\Seeders\GEN;


use App\Models\Catalogo;
use App\Models\Categoria;
use Database\Seeders\Traits\RunsOnce;
use Illuminate\Database\Seeder;

/**
 * Categorías y catálogos que usa el sistema de ventas.
 * Los códigos usan prefijo (UM-, MP-, TD-, MA-) porque catalogo_codigo es único global.
 */
class CatalogosSistemaSeeder extends Seeder
{
    use RunsOnce;

    protected function handle()
    {
        $definicion = [
            'Unidad de medida' => [
                ['UM-001', 'Unidad'],
                ['UM-002', 'Caja'],
                ['UM-003', 'Paquete'],
                ['UM-004', 'Docena'],
                ['UM-005', 'Bolsa'],
                ['UM-006', 'Kilogramo'],
                ['UM-007', 'Gramo'],
                ['UM-008', 'Litro'],
                ['UM-009', 'Metro'],
            ],
            'Método de pago' => [
                ['MP-001', 'Efectivo'],
                ['MP-002', 'Transferencia bancaria'],
                ['MP-003', 'QR'],
                ['MP-004', 'Tarjeta'],
                ['MP-005', 'Cheque'],
            ],
            'Tipo de documento' => [
                ['TD-001', 'CI - Cédula de identidad'],
                ['TD-002', 'CEX - Cédula de extranjero'],
                ['TD-003', 'PAS - Pasaporte'],
                ['TD-004', 'OD - Otro documento'],
                ['TD-005', 'NIT'],
            ],
            'Motivo de ajuste' => [
                ['MA-001', 'Stock inicial'],
                ['MA-002', 'Merma'],
                ['MA-003', 'Producto vencido'],
                ['MA-004', 'Daño o rotura'],
                ['MA-005', 'Robo o pérdida'],
                ['MA-006', 'Error de conteo'],
                ['MA-007', 'Muestra o donación'],
            ],
        ];

        foreach ($definicion as $nombre => $items) {
            $categoria = Categoria::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => "Catálogo de {$nombre}", 'estado' => 1]
            );

            foreach ($items as [$codigo, $descripcion]) {
                Catalogo::firstOrCreate(
                    ['catalogo_codigo' => $codigo],
                    [
                        'catalogo_parent' => null,
                        'catalogo_descripcion' => $descripcion,
                        'catalogo_estado' => 1,
                        'categoria_id' => $categoria->id,
                    ]
                );
            }
        }
    }
}
