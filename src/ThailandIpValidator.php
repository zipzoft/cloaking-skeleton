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
        '14.128.8.0/22',
        '14.207.0.0/16',
        '27.130.0.0/16',
        '49.48.0.0/13',
        '49.228.0.0/14',
        '58.8.0.0/14',
        '58.136.0.0/15',
        '61.19.0.0/16',
        '61.90.0.0/15',
        '96.30.0.0/16',
        '101.51.0.0/16',
        '101.108.0.0/15',
        '103.7.56.0/22',
        '110.164.0.0/15',
        '111.84.0.0/16',
        '113.53.0.0/16',
        '115.87.0.0/16',
        '122.154.0.0/16',
        '124.109.0.0/17',
        '124.122.0.0/16',
        '125.24.0.0/14',
        '125.213.0.0/17',
        '159.192.0.0/14',
        '171.4.0.0/14',
        '171.96.0.0/13',
        '180.180.0.0/14',
        '182.52.0.0/14',
        '182.232.0.0/16',
        '183.88.0.0/14',
        '184.82.0.0/16',
        '202.28.0.0/15',
        '202.44.0.0/16',
        '202.60.192.0/19',
        '203.107.128.0/19',
        '203.113.0.0/17',
        '203.144.128.0/17',
        '203.146.0.0/16',
        '203.150.0.0/15',
        '203.170.48.0/20'
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

        // If ip address is localhost or in development, return true
        if (in_array($ipAddress, ['127.0.0.1', '::1']) || 
            strpos($ipAddress, '192.168.') === 0 || 
            strpos($ipAddress, '10.') === 0) {
            error_log("ThailandIpValidator: Development IP detected: " . $ipAddress);
            return true;
        }

        // First try Cloudflare headers if available
        $headers = getallheaders();
        
        // Try different Cloudflare country code headers
        $countryCode = null;
        $cfHeaders = ['cf-ipcountry', 'CF-IPCountry', 'HTTP_CF_IPCOUNTRY'];
        foreach ($cfHeaders as $header) {
            if (isset($headers[$header])) {
                $countryCode = strtoupper($headers[$header]);
                break;
            }
        }
        
        if ($countryCode !== null) {
            $isThailand = $countryCode === 'TH';
            error_log("ThailandIpValidator: Cloudflare country code: " . $countryCode . ", isThailand: " . ($isThailand ? 'true' : 'false'));
            return $isThailand;
        }

        // Fallback to IP range check if not using Cloudflare
        foreach (self::THAILAND_IP_RANGES as $range) {
            if ($this->ipInRange($ipAddress, $range)) {
                error_log("ThailandIpValidator: IP " . $ipAddress . " matched range " . $range);
                return true;
            }
        }

        error_log("ThailandIpValidator: IP " . $ipAddress . " not in any Thailand range");
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
