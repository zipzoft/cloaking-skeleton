<?php

namespace App;

use App\Contracts\VisitorValidatorInterface;

class ValidatorFactory
{
    private array $validators = [];

    public function addValidator(VisitorValidatorInterface $validator): void
    {
        $this->validators[] = $validator;
    }

    public function getValidators(): array
    {
        return $this->validators;
    }
}