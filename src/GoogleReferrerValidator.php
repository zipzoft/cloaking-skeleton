<?php

namespace App;

use App\Contracts\VisitorValidatorInterface;
use App\VisitorContext;

class GoogleReferrerValidator implements VisitorValidatorInterface
{
    public function validate(VisitorContext $context): bool
    {
        $referer = $context->getReferer();
        if (!$referer) {
            return false;
        }
        
        return str_contains(strtolower($referer), 'google.com/search');
    }
}