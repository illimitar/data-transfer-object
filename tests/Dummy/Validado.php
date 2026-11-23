<?php

namespace Spatie\DataTransferObject\Tests\Dummy;

use Spatie\DataTransferObject\DataTransferObject;

class Validado extends DataTransferObject
{
    public static $__validators = [
        'valor' => [Positivo::class],
    ];

    public $valor = 1;
}
