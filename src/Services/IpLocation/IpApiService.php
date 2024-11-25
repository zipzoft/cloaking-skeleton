<?php

namespace App\Services\IpLocation;

use App\Contracts\IpLocationServiceInterface;

class IpApiService implements IpLocationServiceInterface
{
    public function isThailandIp(string $ipAddress): ?bool
    {
        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ipAddress}?fields=status,countryCode");
            if ($response === false) {
                return null;
            }
            
            $data = json_decode($response, true);
            if (!$data || $data['status'] !== 'success') {
                return null;
            }

            return $data['countryCode'] === 'TH';
        } catch (\Exception $e) {
            return null;
        }
    }
}
