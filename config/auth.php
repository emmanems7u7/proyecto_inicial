<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
     |----------------------------------------------------------------------
     | Restablecimiento de Contraseñas
     |----------------------------------------------------------------------
     |
     | Estas opciones de configuración especifican el comportamiento de la 
     | funcionalidad de restablecimiento de contraseñas de Laravel, incluyendo 
     | la tabla utilizada para el almacenamiento de los tokens y el proveedor 
     | de usuarios que se invoca para recuperar realmente a los usuarios.
     |
     | El tiempo de expiración es el número de minutos que cada token de 
     | restablecimiento será considerado válido. Esta característica de seguridad 
     | mantiene los tokens de vida corta, de modo que tienen menos tiempo para 
     | ser adivinados. Puedes cambiar esto según sea necesario.
     |
     | La configuración de 'throttle' es el número de segundos que un usuario 
     | debe esperar antes de generar más tokens de restablecimiento de contraseñas. 
     | Esto evita que el usuario genere rápidamente una gran cantidad de tokens 
     | de restablecimiento de contraseñas.
     |
     */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
       |----------------------------------------------------------------------
       | Tiempo de Expiración de la Confirmación de Contraseña
       |----------------------------------------------------------------------
       |
       | Aquí puedes definir la cantidad de segundos antes de que venza la ventana 
       | de confirmación de contraseña y los usuarios sean solicitados a ingresar 
       | nuevamente su contraseña a través de la pantalla de confirmación. Por 
       | defecto, el tiempo de expiración dura tres horas.
       |
       */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
