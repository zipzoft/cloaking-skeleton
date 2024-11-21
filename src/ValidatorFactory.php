<?php

namespace App;

use App\Contracts\VisitorValidatorInterface;

/**
 * A factory for creating validators.
 *
 * The factory stores a list of all the validators it has created and provides
 * a way to retrieve them.
 *
 * @package App
 */
class ValidatorFactory
{
    /**
     * The list of validators created by the factory.
     *
     * @var VisitorValidatorInterface[]
     */
    private array $validators = [];

    /**
     * Adds a validator to the list.
     *
     * @param VisitorValidatorInterface $validator The validator to add.
     *
     * @return void
     */
    public function addValidator(VisitorValidatorInterface $validator): void
    {
        $this->validators[] = $validator;
    }

    /**
     * Retrieves the list of validators.
     *
     * @return VisitorValidatorInterface[]
     */
    public function getValidators(): array
    {
        return $this->validators;
    }
}