<?php

namespace App\Interfaces;

interface UserInterface
{
    public function CrearUsuario($request);
    public function EditarUsuario($request, $id, $perfil);
    public function EditarDatosPersonales($request, $id);
    public function GetUsuario($id);
    public function GetUsuarios();

}
