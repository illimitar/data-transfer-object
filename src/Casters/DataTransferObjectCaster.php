<?php

namespace Spatie\DataTransferObject\Casters;

use Spatie\DataTransferObject\Caster;
use Spatie\DataTransferObject\DataTransferObject;

class DataTransferObjectCaster implements Caster
{
    /** @var array */
    private $classNames;

    public function __construct(array $classNames)
    {
        $this->classNames = $classNames;
    }

    public function cast($value): DataTransferObject
    {
        foreach ($this->classNames as $className) {
            if ($value instanceof $className) {
                return $value;
            }
        }

        return new $this->classNames[0]($value);
    }
}
