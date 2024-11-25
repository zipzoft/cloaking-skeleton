<?php

namespace App;

use App\Contracts\IpLocationServiceInterface;
use App\Contracts\VisitorValidatorInterface;
use App\Services\IpLocation\CloudflareService;
use App\Services\IpLocation\DevelopmentService;
use App\Services\IpLocation\IpApiService;
use App\Services\IpLocation\IpRangeService;
use App\VisitorContext;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class ThailandIpValidator implements VisitorValidatorInterface
{
    private const CACHE_DURATION = 86400; // 24 hours in seconds
    private FilesystemAdapter $cache;
    private array $locationServices;

    /**
     * @param IpLocationServiceInterface[] $locationServices Optional array of location services
     */
    public function __construct(array $locationServices = [])
    {
        $this->cache = new FilesystemAdapter(
            namespace: 'ip_validation',
            defaultLifetime: self::CACHE_DURATION,
            directory: __DIR__ . '/../storage/cache'
        );

        // If no services provided, use default services in priority order
        $this->locationServices = $locationServices ?: [
            new CloudflareService(), // Prioritize Cloudflare headers
            new DevelopmentService(),
            new IpApiService(),
            new IpRangeService()
        ];
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

        // Try to get from cache first
        $cachedResult = $this->getCachedResult($ipAddress);
        if ($cachedResult !== null) {
            return $cachedResult;
        }

        // Try each service in order until we get a definitive result
        foreach ($this->locationServices as $service) {
            $result = $service->isThailandIp($ipAddress);
            if ($result !== null) {
                $this->cacheResult($ipAddress, $result);
                return $result;
            }
        }

        // If no service could determine the location, default to false
        $this->cacheResult($ipAddress, false);
        return false;
    }

    /**
     * Get cached result for an IP address
     * 
     * @param string $ipAddress
     * @return bool|null
     */
    private function getCachedResult(string $ipAddress): ?bool
    {
        $cacheKey = $this->getCacheKey($ipAddress);
        $item = $this->cache->getItem($cacheKey);

        if (!$item->isHit()) {
            return null;
        }

        error_log("ThailandIpValidator: Using cached result for IP: " . $ipAddress);
        return $item->get();
    }

    /**
     * Cache the validation result for an IP address
     * 
     * @param string $ipAddress
     * @param bool $result
     */
    private function cacheResult(string $ipAddress, bool $result): void
    {
        $cacheKey = $this->getCacheKey($ipAddress);
        $item = $this->cache->getItem($cacheKey);
        $item->set($result);
        $item->expiresAfter(self::CACHE_DURATION);
        $this->cache->save($item);
        error_log("ThailandIpValidator: Cached result for IP: " . $ipAddress);
    }

    /**
     * Get cache key for an IP address
     * 
     * @param string $ipAddress
     * @return string
     */
    private function getCacheKey(string $ipAddress): string
    {
        $key = $ipAddress;
        
        // Include Cloudflare headers in cache key
        if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            $key .= '_CF_' . $_SERVER['HTTP_CF_IPCOUNTRY'];
        }
        
        return md5($key);
    }
}
