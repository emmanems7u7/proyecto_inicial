<h1 style="text-align: center; font-size: 24px; font-weight: bold; margin-bottom: 10px;">
    Listado de {{ $export }}
</h1>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="min-width: 120px; text-align: left;">Usuario</th>
            <th style="min-width: 180px; text-align: left;">Email</th>
            <th style="min-width: 200px; text-align: left;">Nombre Completo</th>
            <th style="min-width: 150px; text-align: left;">Rol</th>
            <th style="min-width: 120px; text-align: left;">Teléfono</th>
            <th style="min-width: 180px; text-align: left;">Último Acceso</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $usuario)
            <tr>
                <td style="white-space: nowrap;">{{ $usuario->name }}</td>
                <td style="white-space: nowrap;">{{ $usuario->email }}</td>
                <td style="white-space: nowrap;">
                    {{ $usuario->usuario_nombres }} {{ $usuario->usuario_app }} {{ $usuario->usuario_apm }}
                </td>
                <td style="white-space: nowrap;">
                    {{ $usuario->getRoleNames()->first() ?? 'Sin Rol Asignado' }}
                </td>
                <td style="white-space: nowrap;">{{ $usuario->usuario_telefono }}</td>
                <td style="white-space: nowrap;">{{ $usuario->usuario_fecha_ultimo_acceso }}</td>
            </tr>
        @endforeach
    </tbody>
</table>