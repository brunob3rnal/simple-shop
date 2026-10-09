<?php

namespace App\Support;

class Money
{
    /**
     * Formatea centavos USD para mostrarlos, por ejemplo 1999 => "$19.99".
     */
    public static function usd(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}
