<?php

namespace App\Exceptions;

use DomainException;

/** Error de negocio esperado (caja cerrada, límite de crédito, etc.). Se muestra al usuario. */
class OperacionException extends DomainException
{
}
