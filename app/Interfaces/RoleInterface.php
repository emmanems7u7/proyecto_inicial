<?php

namespace App\Interfaces;

interface RoleInterface
{
    public function CrearRol($request);
    public function EditarRol($request, $id);
}
