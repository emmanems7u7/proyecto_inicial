<?php

namespace Database\Seeders\GEN;

use App\Models\Catalogo;
use App\Models\Categoria;
use Database\Seeders\Traits\RunsOnce;
use Illuminate\Database\Seeder;

/**
 * Categorías y catálogos que usa el sistema de ventas.
 * Los códigos usan prefijo (UM-, MP-, TD-, MA-, CD-) porque catalogo_codigo es único global.
 */
class CatalogoSisSeeder extends Seeder
{
    use RunsOnce;

    /**
     * Equivalencias "tipo.codigo corto" => catalogo_codigo real.
     * Permite que los demás seeders GEN sigan llamando CatalogoSeeder::id('ciudad', 'LPZ').
     */
    private const ALIAS = [
        'ciudad.LPZ' => 'CD-001',
        'ciudad.EAL' => 'CD-002',
        'ciudad.CBB' => 'CD-003',
        'ciudad.SCZ' => 'CD-004',
        'tipo_documento.CI' => 'TD-001',
        'tipo_documento.PAS' => 'TD-003',
        'tipo_documento.NIT' => 'TD-005',
        'unidad_medida.UND' => 'UM-001',
        'unidad_medida.CJA' => 'UM-002',
        'unidad_medida.KG' => 'UM-006',
        'unidad_medida.LT' => 'UM-008',
    ];

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
            'Ciudad' => [
                ['CD-001', 'La Paz'],
                ['CD-002', 'El Alto'],
                ['CD-003', 'Cochabamba'],
                ['CD-004', 'Santa Cruz'],
                ['CD-005', 'Oruro'],
                ['CD-006', 'Sucre'],
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

    /**
     * Devuelve el id de un catálogo.
     *  - id('ciudad', 'LPZ')  -> usa el alias corto
     *  - id('TD-001')         -> usa el catalogo_codigo real
     */
    public static function id(string $tipo, ?string $codigo = null): ?int
    {
        $real = $codigo === null ? $tipo : (self::ALIAS["{$tipo}.{$codigo}"] ?? $codigo);

        return Catalogo::where('catalogo_codigo', $real)->value('id');
    }
}