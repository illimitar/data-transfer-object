<?php

namespace Spatie\DataTransferObject\Tests\Dummy;

use Spatie\DataTransferObject\DataTransferObject;

class Estrito extends DataTransferObject
{
    public static $__strict = true;

    public $nome = null;
}
