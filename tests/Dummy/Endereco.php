<?php

namespace Spatie\DataTransferObject\Tests\Dummy;

use Spatie\DataTransferObject\DataTransferObject;

class Endereco extends DataTransferObject
{
    public static $__mapFrom = [
        'rua' => 'xLgr',
    ];

    public static $__propertyTypes = [
        'rua' => '?string',
        'cep' => '?string',
    ];

    public $rua = null;

    public $cep = null;
}
