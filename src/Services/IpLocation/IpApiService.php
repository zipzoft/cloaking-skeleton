<?php

namespace App\Services\IpLocation;

use App\Contracts\IpLocationServiceInterface;

class IpApiService implements IpLocationServiceInterface
{
    /**
     * The URL template for the IP API service.
     *
     * This service returns a JSON response with the IP address's country code.
     * See the API documentation for more information:
     * http://ip-api.com/docs/api:json
     *
     * @var string
     */
    private const IP_API_URL = 'http://ip-api.com/json/%s?fields=status,countryCode';

    /**
     * The URL template for the IPAPI.co service.
     *
     * This service returns a JSON response with the IP address's country code.
     * See the API documentation for more information:
     * https://ipapi.co/api/
     *
     * @var string
     */
    private const IPAPI_CO_URL = 'https://ipapi.co/%s/json/';

    /**
     * Check if the given IP address is a Thai IP.
     *
     * This method tries both IP API services in order until it gets a result.
     * If both services fail, it returns null.
     *
     * @param string $ipAddress The IP address to check.
     *
     * @return bool|null True if the IP address is Thai, false otherwise, or null if the check fails.
     */
    public function isThailandIp(string $ipAddress): ?bool
    {
        // Try services in order until we get a result
        $services = [
            [
                'url' => self::IP_API_URL,
                'handler' => [$this, 'handleIpApiResponse'], // Handle the response from IP API
            ],
            [
                'url' => self::IPAPI_CO_URL,
                'handler' => [$this, 'handleIpapiCoResponse'], // Handle the response from IPAPI.co
            ],
        ];

        foreach ($services as $service) {
            $result = $this->tryService($ipAddress, $service['url'], $service['handler']);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Try to get the country code for the given IP address from the given service.
     *
     * @param string $ipAddress The IP address to check.
     * @param string $urlTemplate The URL template for the service.
     * @param callable $responseHandler The callback to handle the response from the service.
     *
     * @return bool|null True if the IP address is Thai, false otherwise, or null if the check fails.
     */
    private function tryService(string $ipAddress, string $urlTemplate, callable $responseHandler): ?bool
    {
        try {
            $url = sprintf($urlTemplate, $ipAddress);
            // Try to get the response from the service
            $response = @file_get_contents($url);
            if ($response === false) {
                // If the response is false, the service failed, so return null
                return null;
            }

            // Decode the response as JSON
            $data = json_decode($response, true);
            if (!$data) {
                // If the decode fails, the service failed, so return null
                return null;
            }

            // Handle the response from the service
            return $responseHandler($data);
        } catch (\Exception $e) {
            // If an exception occurs, the service failed, so return null
            return null;
        }
    }

    /**
     * Handle the response from the IP API service.
     *
     * @param array $data The decoded JSON response from the service.
     *
     * @return bool|null True if the IP address is Thai, false otherwise, or null if the check fails.
     */
    private function handleIpApiResponse(array $data): ?bool
    {
        if (!isset($data['status']) || $data['status'] !== 'success') {
            // If the response is not successful, the service failed, so return null
            return null;
        }

        // Check if the country code is Thai
        return $data['countryCode'] === 'TH';
    }

    /**
     * Handle the response from the IPAPI.co service.
     *
     * @param array $data The decoded JSON response from the service.
     *
     * @return bool|null True if the IP address is Thai, false otherwise, or null if the check fails.
     */
    private function handleIpapiCoResponse(array $data): ?bool
    {
        if (isset($data['error'])) {
            // If the response contains an error, the service failed, so return null
            return null;
        }

        // Check if the country code is Thai
        return $data['country_code'] === 'TH';
    }
}
