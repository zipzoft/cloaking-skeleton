<?php

namespace App\Services\IpLocation;

use App\Contracts\IpLocationServiceInterface;

class DevelopmentService implements IpLocationServiceInterface
{
    private const DEVELOPMENT_IPS = [
        '127.0.0.1',
        '::1'
    ];

    private const PRIVATE_RANGES = [
        '10.0.0.0/8',      // RFC1918
        '172.16.0.0/12',   // RFC1918
        '192.168.0.0/16',  // RFC1918
        '169.254.0.0/16',  // RFC3927 Link-Local
        'fc00::/7',        // RFC4193 Unique-Local
        'fe80::/10'        // RFC4291 Link-Local
    ];

    public function isThailandIp(string $ipAddress): ?bool
    {
        // Check exact matches for development IPs
        if (in_array($ipAddress, self::DEVELOPMENT_IPS)) {
            return true;
        }

        // Check private network ranges
        foreach (self::PRIVATE_RANGES as $range) {
            if ($this->ipInRange($ipAddress, $range)) {
                return true;
            }
        }

        return null;
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
        // Handle IPv6 addresses
        if (str_contains($ip, ':') || str_contains($range, ':')) {
            return $this->ipv6InRange($ip, $range);
        }

        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~$wildcard_decimal;
        
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }

    /**
     * Check if an IPv6 address is in a CIDR range
     * 
     * @param string $ip IPv6 address to check
     * @param string $range CIDR range to check against
     * @return bool
     */
    private function ipv6InRange(string $ip, string $range): bool
    {
        // Basic implementation for common IPv6 cases
        if ($ip === '::1' && in_array($range, ['::1/128', 'fc00::/7', 'fe80::/10'])) {
            return true;
        }
        return false;
    }
}
