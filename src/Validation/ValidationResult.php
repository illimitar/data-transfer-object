<?php

namespace Spatie\DataTransferObject\Validation;

class ValidationResult
{
    /** @var bool */
    public $isValid;

    /** @var string|null */
    public $message;

    public function __construct(bool $isValid, ?string $message = null)
    {
        $this->isValid = $isValid;
        $this->message = $message;
    }

    public static function valid(): self
    {
        return new self(true);
    }

    public static function invalid(string $message): self
    {
        return new self(false, $message);
    }
}
