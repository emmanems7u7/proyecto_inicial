<?php

namespace App\Services;

use App\Models\ConfiguracionCredenciales;

class CredencialesService
{
    /**
     * Valida la contraseña según la configuración de credenciales.
     *
     * @param string $password
     * @return array
     */
    public static function validarPassword(string $password): array
    {
        // Obtener configuración por defecto
        $config = ConfiguracionCredenciales::first();

        if (!$config) {
            return [
                'success' => false,
                'message' => 'Configuración de credenciales no definida.'
            ];
        }

        $errors = [];

        $length = strlen($password);

        if ($length < $config->conf_long_min) {
            $errors[] = "La contraseña debe tener al menos {$config->conf_long_min} caracteres.";
        }

        if ($length > $config->conf_long_max) {
            $errors[] = "La contraseña no debe exceder {$config->conf_long_max} caracteres.";
        }

        if ($config->conf_req_upper && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe incluir al menos una letra mayúscula.";
        }

        if ($config->conf_req_num && !preg_match('/[0-9]/', $password)) {
            $errors[] = "La contraseña debe incluir al menos un número.";
        }

        if ($config->conf_req_esp && !preg_match('/[\W_]/', $password)) {
            $errors[] = "La contraseña debe incluir al menos un carácter especial.";
        }

        if (empty($errors)) {
            return [
                'success' => true,
                'message' => 'Contraseña válida.'
            ];
        }

        return [
            'success' => false,
            'errors' => $errors,
        ];
    }
}
