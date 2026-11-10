<?php

namespace App\Services;

/**
 * Definición de las automatizaciones disponibles.
 * parametros: clave => [etiqueta, tipo (bool|int|texto), valor por defecto]
 * alcance: 'tienda' (una configuración por tienda) o 'global'.
 * frecuencia: 'diaria' (a la hora indicada), 'cada_hora' o 'evento' (la dispara el sistema).
 */
class AutomatizacionCatalogo
{
    public const CORREOS = ['Correos de destino (separados por coma; vacío = administradores)', 'texto', ''];

    public static function definiciones(): array
    {
        return [
            'cierre_caja_automatico' => [
                'nombre' => 'Cierre de caja automático',
                'descripcion' => 'Cierra las cajas que quedaron abiertas y envía el resumen por correo.',
                'alcance' => 'tienda', 'frecuencia' => 'diaria', 'hora' => '23:30', 'activo' => true,
                'parametros' => [
                    'solo_dias_anteriores' => ['Cerrar solo cajas abiertas en días anteriores', 'bool', false],
                    'correos' => self::CORREOS,
                ],
            ],
            'resumen_diario_ventas' => [
                'nombre' => 'Resumen diario de ventas',
                'descripcion' => 'Correo con ventas, devoluciones y estado de cajas del día.',
                'alcance' => 'tienda', 'frecuencia' => 'diaria', 'hora' => '22:00', 'activo' => true,
                'parametros' => ['correos' => self::CORREOS],
            ],
            'comprobante_venta' => [
                'nombre' => 'Comprobante de venta por correo',
                'descripcion' => 'Envía el comprobante al cliente (si tiene correo) al completar una venta.',
                'alcance' => 'tienda', 'frecuencia' => 'evento', 'hora' => null, 'activo' => true,
                'parametros' => [],
            ],
            'alerta_stock_minimo' => [
                'nombre' => 'Alerta de stock mínimo',
                'descripcion' => 'Avisa de los productos que llegaron o bajaron del stock mínimo.',
                'alcance' => 'tienda', 'frecuencia' => 'diaria', 'hora' => '08:00', 'activo' => true,
                'parametros' => ['correos' => self::CORREOS],
            ],
            'alerta_vencimiento_lotes' => [
                'nombre' => 'Alerta de vencimiento de lotes',
                'descripcion' => 'Avisa de lotes próximos a vencer o ya vencidos con stock.',
                'alcance' => 'tienda', 'frecuencia' => 'diaria', 'hora' => '08:05', 'activo' => true,
                'parametros' => [
                    'dias_aviso' => ['Días de anticipación', 'int', 30],
                    'correos' => self::CORREOS,
                ],
            ],
            'recordatorio_cxc' => [
                'nombre' => 'Recordatorio de cuentas por cobrar',
                'descripcion' => 'Recuerda por correo a los clientes con pagos por vencer o vencidos y resume al administrador.',
                'alcance' => 'tienda', 'frecuencia' => 'diaria', 'hora' => '09:00', 'activo' => true,
                'parametros' => [
                    'dias_antes' => ['Avisar desde N días antes del vencimiento', 'int', 3],
                    'cada_dias' => ['Repetir recordatorio cada N días', 'int', 7],
                    'max_recordatorios' => ['Máximo de recordatorios por documento', 'int', 5],
                    'correos' => self::CORREOS,
                ],
            ],
            'aviso_cxp' => [
                'nombre' => 'Aviso de cuentas por pagar',
                'descripcion' => 'Avisa al administrador de pagos a proveedores próximos o vencidos.',
                'alcance' => 'tienda', 'frecuencia' => 'diaria', 'hora' => '09:10', 'activo' => true,
                'parametros' => [
                    'dias_antes' => ['Avisar desde N días antes del vencimiento', 'int', 3],
                    'correos' => self::CORREOS,
                ],
            ],
            'actualizar_estados' => [
                'nombre' => 'Actualización de estados',
                'descripcion' => 'Marca como vencidas las cuentas, lotes y cotizaciones que pasaron su fecha.',
                'alcance' => 'tienda', 'frecuencia' => 'diaria', 'hora' => '00:10', 'activo' => true,
                'parametros' => [],
            ],
            'bienvenida_usuario' => [
                'nombre' => 'Correo de bienvenida a usuarios',
                'descripcion' => 'Se envía al crear un usuario (llamar a AutomatizacionService::encolarBienvenida).',
                'alcance' => 'global', 'frecuencia' => 'evento', 'hora' => null, 'activo' => true,
                'parametros' => [],
            ],
            'respaldo_bd' => [
                'nombre' => 'Respaldo de base de datos',
                'descripcion' => 'Genera una copia comprimida de la base de datos en storage/app/backups.',
                'alcance' => 'global', 'frecuencia' => 'diaria', 'hora' => '02:00', 'activo' => true,
                'parametros' => [
                    'conservar' => ['Copias a conservar', 'int', 7],
                    'ruta_mysqldump' => ['Ruta de mysqldump (vacío = "mysqldump")', 'texto', ''],
                ],
            ],
        ];
    }

    public static function valoresPorDefecto(string $codigo): array
    {
        return collect(self::definiciones()[$codigo]['parametros'] ?? [])->map(fn ($p) => $p[2])->all();
    }
}
