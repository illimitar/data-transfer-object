<?php

namespace Spatie\DataTransferObject;

interface Caster
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function cast($value);
}
