<?php

namespace App;

use App\Contracts\VisitorValidatorInterface;
use App\VisitorContext;

class ThailandIpValidator implements VisitorValidatorInterface
{
    private const THAILAND_IP_RANGES = [
        '1.0.128.0/17',
        '1.46.0.0/15',
        '1.179.128.0/17',
        // Add more Thailand IP ranges as needed
    ];

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

        // First try Cloudflare headers if available
        $headers = getallheaders();
        $countryCode = $headers['cf-ipcountry'] ?? null;
        
        if ($countryCode !== null) {
            return $countryCode === 'TH';
        }

        // Fallback to IP range check if not using Cloudflare
        foreach (self::THAILAND_IP_RANGES as $range) {
            if ($this->ipInRange($ipAddress, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP address is in a CIDR range
     * 
     * @param string $ip IP address to check
     * @param string $range CIDR range to check against
     * @return bool
     */
    private function ipInRange(string $ip, string $range): bool
    {
        list($range, $netmask) = explode('/', $range, 2);
        $rangeDecimal = ip2long($range);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
        $netmaskDecimal = ~$wildcardDecimal;

        return (($ipDecimal & $netmaskDecimal) == ($rangeDecimal & $netmaskDecimal));
    }
}
