<?php

namespace App;

use App\Contracts\VisitorValidatorInterface;
use App\VisitorContext;

class ThailandIpValidator implements VisitorValidatorInterface
{
    public function __construct()
    {
        //
    }

    /**
     * Validate visitor context
     * 
     * @param VisitorContext $context
     * @return bool
     */
    public function validate(VisitorContext $context): bool
    {
        $ipAddress = $context->getIpAddress();

        // If ip address is localhost, return true
        if (in_array($ipAddress, ['127.0.0.1', '::1'])) {
            return true;
        }

        // Get country code from cloudflare headers
        $headers = getallheaders();

        $countryCode = $headers['cf-ipcountry'] ?? null;

        return $countryCode === 'TH';
    }

}
