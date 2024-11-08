<?php

namespace App\Contracts;

use App\VisitorContext;

interface VisitorValidatorInterface
{
    /**
     * Validate the visitor context
     *
     * @param VisitorContext $context The visitor context
     * @return bool True if the visitor is valid, false otherwise
     */
    public function validate(VisitorContext $context): bool;
}
