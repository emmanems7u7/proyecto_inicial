<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación en dos pasos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fa;
        }

        .email-container {
            background-color: #e9ecef;
            padding: 40px 0;
            text-align: center;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            text-align: center;
        }

        h1 {
            color: #4CAF50;
            font-size: 28px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        p {
            font-size: 16px;
            color: #333;
            line-height: 1.6;
            margin: 10px 0;
        }

        .code {
            font-size: 40px;
            font-weight: bold;
            color: #4CAF50;
            display: inline-block;
            background-color: #f1f1f1;
            padding: 15px 25px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #888;
            text-align: center;
        }

        .footer a {
            color: #4CAF50;
            text-decoration: none;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #45a049;
        }

        @media only screen and (max-width: 600px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .code {
                font-size: 35px;
            }
        }
    </style>
</head>

<body>

    <div class="email-container">
        <div class="container">
            <h1>Verificación en dos pasos</h1>
            <p>Tu código de verificación es:</p>
            <p class="code">{{ $user->two_factor_code }}</p>
            <p>Este código expira en 10 minutos.</p>

            <p>Si no solicitaste este código, por favor ignora este mensaje.</p>

            <div class="footer">
                <p>Este email fue enviado automáticamente por <strong>{{ env('APP_NAME') }}</strong>.</p>
                <p>&copy; {{ date('Y') }} {{ env('APP_NAME') }}. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>

</body>

</html>