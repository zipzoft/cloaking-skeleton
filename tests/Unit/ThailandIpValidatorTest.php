<?php

namespace Tests\Unit;

use App\Contracts\IpLocationServiceInterface;
use App\Services\IpLocation\CloudflareService;
use App\Services\IpLocation\DevelopmentService;
use App\Services\IpLocation\IpApiService;
use App\Services\IpLocation\IpRangeService;
use App\ThailandIpValidator;
use App\VisitorContext;
use Mockery;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

beforeEach(function () {
    // Clear Mockery after each test
    Mockery::close();
});

test('validator uses cache when available', function () {
    $mockCache = new ArrayAdapter();
    $item = $mockCache->getItem(md5('1.2.3.4'));
    $item->set(true);
    $mockCache->save($item);

    $validator = new class(['cache' => $mockCache]) extends ThailandIpValidator {
        public function __construct(array $dependencies = [])
        {
            parent::__construct();
            if (isset($dependencies['cache'])) {
                $this->cache = $dependencies['cache'];
            }
        }
    };

    $context = Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getIpAddress')->andReturn('1.2.3.4');

    expect($validator->validate($context))->toBeTrue();
});

test('validator tries services in order until result found', function () {
    // Mock services
    $service1 = Mockery::mock(IpLocationServiceInterface::class);
    $service1->shouldReceive('isThailandIp')->andReturn(null);

    $service2 = Mockery::mock(IpLocationServiceInterface::class);
    $service2->shouldReceive('isThailandIp')->andReturn(true);

    $service3 = Mockery::mock(IpLocationServiceInterface::class);
    $service3->shouldNotReceive('isThailandIp');

    $validator = new ThailandIpValidator([$service1, $service2, $service3]);

    $context = Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getIpAddress')->andReturn('1.2.3.4');

    expect($validator->validate($context))->toBeTrue();
});

test('validator returns false when no service returns result', function () {
    // Mock services that all return null
    $service1 = Mockery::mock(IpLocationServiceInterface::class);
    $service1->shouldReceive('isThailandIp')->andReturn(null);

    $service2 = Mockery::mock(IpLocationServiceInterface::class);
    $service2->shouldReceive('isThailandIp')->andReturn(null);

    $validator = new ThailandIpValidator([$service1, $service2]);

    $context = Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getIpAddress')->andReturn('1.2.3.4');

    expect($validator->validate($context))->toBeFalse();
});

test('validator uses default services when none provided', function () {
    $validator = new ThailandIpValidator();
    
    $context = Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getIpAddress')->andReturn('127.0.0.1');

    // Should return true for localhost due to DevelopmentService
    expect($validator->validate($context))->toBeTrue();
});

test('validator caches results from services', function () {
    $mockCache = new ArrayAdapter();
    
    $validator = new class(['cache' => $mockCache]) extends ThailandIpValidator {
        public function __construct(array $dependencies = [])
        {
            parent::__construct();
            if (isset($dependencies['cache'])) {
                $this->cache = $dependencies['cache'];
            }
        }
    };

    $context = Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getIpAddress')->andReturn('127.0.0.1');

    // First call should cache the result
    $validator->validate($context);

    // Verify result was cached
    $item = $mockCache->getItem(md5('127.0.0.1'));
    expect($item->isHit())->toBeTrue();
    expect($item->get())->toBeTrue();
});

test('validator handles cache expiration', function () {
    $mockCache = new ArrayAdapter();
    
    $validator = new class(['cache' => $mockCache]) extends ThailandIpValidator {
        public function __construct(array $dependencies = [])
        {
            parent::__construct();
            if (isset($dependencies['cache'])) {
                $this->cache = $dependencies['cache'];
            }
        }
    };

    $context = Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getIpAddress')->andReturn('1.2.3.4');

    // Set expired cache item
    $item = $mockCache->getItem(md5('1.2.3.4'));
    $item->set(true);
    $item->expiresAt(new \DateTime('-1 hour'));
    $mockCache->save($item);

    // Should ignore expired cache and revalidate
    $validator->validate($context);
    
    $newItem = $mockCache->getItem(md5('1.2.3.4'));
    expect($newItem->get())->toBeFalse(); // Should have new value
});

test('validator handles concurrent requests', function () {
    $mockCache = new ArrayAdapter();
    $validator = new class(['cache' => $mockCache]) extends ThailandIpValidator {
        public function __construct(array $dependencies = [])
        {
            parent::__construct();
            if (isset($dependencies['cache'])) {
                $this->cache = $dependencies['cache'];
            }
        }
    };

    $context = Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getIpAddress')->andReturn('1.2.3.4');

    // Simulate multiple concurrent requests
    $results = [];
    for ($i = 0; $i < 10; $i++) {
        $results[] = $validator->validate($context);
    }

    // All requests should get the same result
    expect(count(array_unique($results)))->toBe(1);
});

test('validator handles service errors gracefully', function () {
    $service = Mockery::mock(IpLocationServiceInterface::class);
    $service->shouldReceive('isThailandIp')->andThrow(new \Exception('Service error'));

    $validator = new ThailandIpValidator([$service]);

    $context = Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getIpAddress')->andReturn('1.2.3.4');

    // Should not throw and return false
    expect($validator->validate($context))->toBeFalse();
});

test('validator handles invalid IP addresses', function () {
    $validator = new ThailandIpValidator();
    
    $invalidIps = [
        '',
        'invalid',
        '256.256.256.256',
        '1.2.3',
        '1.2.3.4.5'
    ];

    foreach ($invalidIps as $ip) {
        $context = Mockery::mock(VisitorContext::class);
        $context->shouldReceive('getIpAddress')->andReturn($ip);
        
        expect($validator->validate($context))->toBeFalse();
    }
});

test('validates localhost IP addresses', function () {
    $validator = new ThailandIpValidator();
    
    $localhostIpv4 = new VisitorContext('127.0.0.1', 'Test Browser', 'https://example.com');
    $localhostIpv6 = new VisitorContext('::1', 'Test Browser', 'https://example.com');
    
    expect($validator->validate($localhostIpv4))->toBeTrue();
    expect($validator->validate($localhostIpv6))->toBeTrue();
});

test('validates Thailand IP ranges', function () {
    $validator = new ThailandIpValidator();
    
    $thaiIPs = [
        '1.0.128.1',    // From 1.0.128.0/17
        '1.0.255.255',  // From 1.0.128.0/17
        '1.46.0.1',     // From 1.46.0.0/15
        '1.47.255.255', // From 1.46.0.0/15
        '1.179.128.1',  // From 1.179.128.0/17
        '1.179.255.255' // From 1.179.128.0/17
    ];
    
    foreach ($thaiIPs as $ip) {
        $context = new VisitorContext($ip, 'Test Browser', 'https://example.com');
        expect($validator->validate($context))
            ->toBeTrue()
            ->and($ip)->not->toBeEmpty();
    }
});

test('rejects non-Thailand IP addresses', function () {
    $validator = new ThailandIpValidator();
    
    $nonThaiIPs = [
        '8.8.8.8',      // Google DNS
        '1.0.127.255',  // Just before Thai range
        '1.0.0.1',      // Not in Thai range
        '2.2.2.2',      // Random IP
        '192.168.1.1',  // Private network
        '10.0.0.1'      // Private network
    ];
    
    foreach ($nonThaiIPs as $ip) {
        $context = new VisitorContext($ip, 'Test Browser', 'https://example.com');
        expect($validator->validate($context))
            ->toBeFalse()
            ->and($ip)->not->toBeEmpty();
    }
});

test('validates with Cloudflare country header', function () {
    $validator = new ThailandIpValidator();
    $context = new VisitorContext('8.8.8.8', 'Test Browser', 'https://example.com');
    
    // Mock getallheaders() to return Thailand
    global $_SERVER;
    $_SERVER['HTTP_CF_IPCOUNTRY'] = 'TH';
    
    expect($validator->validate($context))->toBeTrue();
    
    // Mock getallheaders() to return non-Thailand
    $_SERVER['HTTP_CF_IPCOUNTRY'] = 'US';
    expect($validator->validate($context))->toBeFalse();
    
    // Clean up
    unset($_SERVER['HTTP_CF_IPCOUNTRY']);
});
