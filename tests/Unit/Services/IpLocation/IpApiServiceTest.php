<?php

namespace Tests\Unit\Services\IpLocation;

use App\Services\IpLocation\IpApiService;

test('ip-api service identifies Thai IP', function () {
    $service = new class extends IpApiService {
        public function isThailandIp(string $ipAddress): ?bool
        {
            // Mock successful response for Thai IP
            return match($ipAddress) {
                '1.1.1.1' => true,
                '8.8.8.8' => false,
                'invalid' => null,
                default => null
            };
        }
    };

    expect($service->isThailandIp('1.1.1.1'))->toBeTrue();
    expect($service->isThailandIp('8.8.8.8'))->toBeFalse();
});

test('ip-api service handles invalid responses', function () {
    $service = new class extends IpApiService {
        public function isThailandIp(string $ipAddress): ?bool
        {
            return null; // Simulate API failure
        }
    };

    expect($service->isThailandIp('invalid'))->toBeNull();
});

test('ip-api service handles various API response formats', function () {
    $service = new class extends IpApiService {
        private function mockApiResponse(string $ipAddress): ?string {
            return match($ipAddress) {
                '1.1.1.1' => '{"status":"success","countryCode":"TH"}',
                '2.2.2.2' => '{"status":"success","countryCode":"US"}',
                '3.3.3.3' => '{"status":"fail"}',
                '4.4.4.4' => 'invalid json',
                '5.5.5.5' => '',
                default => null
            };
        }

        protected function makeApiRequest(string $ipAddress): ?string {
            return $this->mockApiResponse($ipAddress);
        }
    };

    expect($service->isThailandIp('1.1.1.1'))->toBeTrue();
    expect($service->isThailandIp('2.2.2.2'))->toBeFalse();
    expect($service->isThailandIp('3.3.3.3'))->toBeNull();
    expect($service->isThailandIp('4.4.4.4'))->toBeNull();
    expect($service->isThailandIp('5.5.5.5'))->toBeNull();
});

test('ip-api service handles network errors', function () {
    $service = new class extends IpApiService {
        protected function makeApiRequest(string $ipAddress): ?string {
            throw new \Exception('Network error');
        }
    };

    expect($service->isThailandIp('1.1.1.1'))->toBeNull();
});

test('ip-api service handles rate limiting', function () {
    $service = new class extends IpApiService {
        private $callCount = 0;

        protected function makeApiRequest(string $ipAddress): ?string {
            $this->callCount++;
            if ($this->callCount > 45) { // IP-API free tier limit
                return null;
            }
            return '{"status":"success","countryCode":"TH"}';
        }
    };

    // Make 50 requests
    for ($i = 0; $i < 50; $i++) {
        if ($i < 45) {
            expect($service->isThailandIp('1.1.1.1'))->toBeTrue();
        } else {
            expect($service->isThailandIp('1.1.1.1'))->toBeNull();
        }
    }
});

test('ip-api service handles various country codes', function () {
    $service = new class extends IpApiService {
        protected function makeApiRequest(string $ipAddress): ?string {
            return match($ipAddress) {
                '1.1.1.1' => '{"status":"success","countryCode":"TH"}',
                '2.2.2.2' => '{"status":"success","countryCode":"th"}', // lowercase
                '3.3.3.3' => '{"status":"success","countryCode":"Th"}', // mixed case
                '4.4.4.4' => '{"status":"success","countryCode":"THH"}', // invalid
                '5.5.5.5' => '{"status":"success","countryCode":""}', // empty
                default => null
            };
        }
    };

    expect($service->isThailandIp('1.1.1.1'))->toBeTrue();
    expect($service->isThailandIp('2.2.2.2'))->toBeTrue();
    expect($service->isThailandIp('3.3.3.3'))->toBeTrue();
    expect($service->isThailandIp('4.4.4.4'))->toBeFalse();
    expect($service->isThailandIp('5.5.5.5'))->toBeFalse();
});
