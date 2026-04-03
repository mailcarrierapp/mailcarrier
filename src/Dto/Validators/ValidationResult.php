<?php

namespace MailCarrier\Dto\Validators;

class ValidationResult
{
    public function __construct(
        public readonly bool $isValid,
        public readonly ?string $message = null,
    ) {}

    public static function valid(): self
    {
        return new self(isValid: true);
    }

    public static function invalid(string $message): self
    {
        return new self(isValid: false, message: $message);
    }
}
