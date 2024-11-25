<?php

namespace App\Services\IpLocation;

use App\Contracts\IpLocationServiceInterface;

class CloudflareService implements IpLocationServiceInterface
{
    private const CF_HEADERS = [
        'HTTP_CF_IPCOUNTRY',
        'CF_IPCOUNTRY',
        'cf-ipcountry',
        'CF-IPCountry'
    ];

    public function isThailandIp(string $ipAddress): ?bool
    {
        // First check $_SERVER for headers
        foreach (self::CF_HEADERS as $header) {
            if (isset($_SERVER[$header])) {
                return strtoupper($_SERVER[$header]) === 'TH';
            }
        }

        // Then check getallheaders() if available
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach (self::CF_HEADERS as $header) {
                if (isset($headers[$header])) {
                    return strtoupper($headers[$header]) === 'TH';
                }
            }
        }
        
        return null;
    }
}
