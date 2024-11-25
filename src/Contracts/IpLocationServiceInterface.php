<?php

namespace App\Contracts;

interface IpLocationServiceInterface
{
    /**
     * Check if IP address is from Thailand
     * 
     * @param string $ipAddress
     * @return bool|null Returns null if service is unavailable
     */
    public function isThailandIp(string $ipAddress): ?bool;
}
