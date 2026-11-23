<?php

namespace Spatie\DataTransferObject\Exceptions;

use Exception;
use Spatie\DataTransferObject\DataTransferObject;

class ValidationException extends Exception
{
    /** @var DataTransferObject */
    public $dataTransferObject;

    /** @var array */
    public $validationErrors;

    public function __construct(DataTransferObject $dataTransferObject, array $validationErrors)
    {
        $this->dataTransferObject = $dataTransferObject;
        $this->validationErrors = $validationErrors;

        $className = get_class($dataTransferObject);

        $messages = [];

        foreach ($validationErrors as $fieldName => $errorsForField) {
            /** @var \Spatie\DataTransferObject\Validation\ValidationResult $errorForField */
            foreach ($errorsForField as $errorForField) {
                $messages[] = "\t - `{$className}->{$fieldName}`: {$errorForField->message}";
            }
        }

        parent::__construct("Validation errors:" . PHP_EOL . implode(PHP_EOL, $messages));
    }
}
