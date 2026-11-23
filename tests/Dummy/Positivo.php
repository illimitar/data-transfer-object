<?php

namespace Spatie\DataTransferObject\Tests\Dummy;

use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;

class Positivo implements Validator
{
    public function validate($value): ValidationResult
    {
        if ($value > 0) {
            return ValidationResult::valid();
        }

        return ValidationResult::invalid('Deve ser maior que zero.');
    }
}
