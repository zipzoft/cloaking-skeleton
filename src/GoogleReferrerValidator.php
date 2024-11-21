<?php

namespace App;

use App\Contracts\VisitorValidatorInterface;
use App\VisitorContext;

class GoogleReferrerValidator implements VisitorValidatorInterface
{
    public function validate(VisitorContext $context): bool
    {
        $referer = $context->getReferer();
        
        // Log empty referrer
        if (empty($referer)) {
            error_log("GoogleReferrerValidator: Referrer is empty or null");
            return false;
        }
        
        // Parse the referrer URL
        $parsedUrl = parse_url(strtolower($referer));
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            error_log("GoogleReferrerValidator: Invalid referrer URL format: " . $referer);
            return false;
        }
        
        // Check for various Google domains
        $host = $parsedUrl['host'];
        $googleDomains = [
            'google.com',
            'google.co.th',
            'google.co.uk',
            'google.ca',
            'google.com.au',
            'google.de',
            'google.fr',
            'google.co.jp',
            'google.co.kr',
            'google.co.in',
            'google.com.br',
            'google.ru',
            'google.it',
            'google.es',
            'google.com.mx',
            'google.cn'
        ];
        
        // Check if the host ends with any Google domain
        foreach ($googleDomains as $domain) {
            if (str_ends_with($host, $domain)) {
                error_log("GoogleReferrerValidator: Valid Google domain found: " . $host);
                return true;
            }
        }
        
        error_log("GoogleReferrerValidator: Non-Google domain: " . $host);
        return false;
    }
}