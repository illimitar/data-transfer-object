<?php

namespace Spatie\DataTransferObject\Tests\Dummy;

use Spatie\DataTransferObject\Caster;

class Maiusculo implements Caster
{
    private $tipos;

    private $sufixo;

    public function __construct(array $tipos, $sufixo)
    {
        $this->tipos = $tipos;
        $this->sufixo = $sufixo;
    }

    public function cast($value)
    {
        return strtoupper($value) . $this->sufixo;
    }
}
