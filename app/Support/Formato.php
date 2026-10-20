<?php

namespace App\Support;

class Formato
{
    /** 12.500 => "12.5" | 3.000 => "3" */
    public static function cantidad($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 3, '.', ','), '0'), '.');
    }

    public static function dinero($n): string
    {
        return number_format((float) $n, 2, '.', ',');
    }
}
