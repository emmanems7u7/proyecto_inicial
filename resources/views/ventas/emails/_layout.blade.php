<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('titulo')</title>
</head>
<body style="margin: 0; padding: 0; background: #f4f5f7; font-family: Arial, Helvetica, sans-serif; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f4f5f7; padding: 20px 0;">
        <tr><td align="center">
            <table width="620" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 6px; overflow: hidden;">
                <tr><td style="background: #1f3a8a; color: #ffffff; padding: 16px 24px; font-size: 18px;">@yield('titulo')</td></tr>
                <tr><td style="padding: 24px; font-size: 14px; line-height: 1.5;">
                    @yield('contenido')
                </td></tr>
                <tr><td style="padding: 12px 24px; font-size: 11px; color: #888; border-top: 1px solid #eee;">
                    Mensaje automático del sistema de ventas. No respondas a este correo.
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
