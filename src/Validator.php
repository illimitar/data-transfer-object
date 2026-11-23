<?php

namespace Spatie\DataTransferObject;

use Spatie\DataTransferObject\Validation\ValidationResult;

interface Validator
{
    /**
     * @param mixed $value
     */
    public function validate($value): ValidationResult;
}
