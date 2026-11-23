<?php

namespace Spatie\DataTransferObject\Tests\Dummy;

use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

class Pessoa extends DataTransferObject
{
    public static $__mapFrom = [
        'versao' => '@attributes.versao',
        'nome' => 'xNome',
        'endereco' => 'end',
    ];

    public static $__mapTo = [
        'nome' => 'name',
    ];

    public static $__castWith = [
        'tipo' => [
            'caster' => Maiusculo::class,
            'args' => ['sufixo' => '!'],
        ],
        'filiais' => [
            'caster' => ArrayCaster::class,
            'args' => ['itemType' => Endereco::class],
        ],
    ];

    public static $__propertyTypes = [
        'versao' => '?string',
        'nome' => '?string',
        'idade' => '?int',
        'endereco' => '?Endereco',
        'tipo' => '?string',
        'filiais' => 'array',
    ];

    public $versao = null;

    public $nome = null;

    public $idade = null;

    public $endereco = null;

    public $tipo = null;

    public $filiais = [];
}
