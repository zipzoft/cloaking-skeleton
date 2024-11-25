<?php

namespace Tests\Feature;

use App\ThailandIpValidator;
use App\VisitorContext;
use App\Services\IpLocation\CloudflareService;
use Mockery;

beforeEach(function() {
    $cacheDir = __DIR__ . '/../../storage/cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }
});

afterEach(function() {
    Mockery::close();
});

test('validator handles real world scenarios', function () {
    // Test development IPs
    $context = new VisitorContext('127.0.0.1', 'Test Browser', 'https://example.com');
    $validator = new ThailandIpValidator();
    expect($validator->validate($context))->toBeTrue();

    // Test private network
    $context = new VisitorContext('192.168.1.1', 'Test Browser', 'https://example.com');
    expect($validator->validate($context))->toBeTrue();

    // Test Thai IP range (1.46.0.0/15)
    $context = new VisitorContext('1.46.0.1', 'Test Browser', 'https://example.com');
    expect($validator->validate($context))->toBeTrue();

    // Test non-Thai IP (Google DNS)
    $context = new VisitorContext('8.8.8.8', 'Test Browser', 'https://example.com');
    expect($validator->validate($context))->toBeFalse();
});

test('validator caches results across requests', function () {
    $ip = '1.46.0.1';
    $context = new VisitorContext($ip, 'Test Browser', 'https://example.com');

    // First request should cache the result
    $startTime = microtime(true);
    $result1 = (new ThailandIpValidator())->validate($context);
    $firstDuration = microtime(true) - $startTime;

    // Second request should be faster due to caching
    $startTime = microtime(true);
    $result2 = (new ThailandIpValidator())->validate($context);
    $secondDuration = microtime(true) - $startTime;

    expect($result1)->toBe($result2);
    expect($firstDuration)->toBeGreaterThan($secondDuration);
});

test('validator handles high load', function () {
    $ips = [
        '127.0.0.1',
        '192.168.1.1',
        '1.46.0.1',
        '8.8.8.8',
        '1.1.1.1'
    ];

    $startTime = microtime(true);
    
    // Simulate multiple concurrent requests
    for ($i = 0; $i < 100; $i++) {
        $ip = $ips[array_rand($ips)];
        $context = new VisitorContext($ip, 'Test Browser', 'https://example.com');
        (new ThailandIpValidator())->validate($context);
    }

    $duration = microtime(true) - $startTime;
    
    // 100 requests should complete in less than 1 second
    expect($duration)->toBeLessThan(1.0);
});

test('validator handles cloudflare headers', function () {
    // Test Thai IP with Cloudflare header
    $_SERVER['HTTP_CF_IPCOUNTRY'] = 'TH';
    $context = new VisitorContext('1.2.3.4', 'Test Browser', 'https://example.com');
    $validator = new ThailandIpValidator(); 
    
    // Debug logging
    error_log("Testing Thai IP with CF_IPCOUNTRY = TH");
    error_log("Headers: " . print_r($_SERVER, true));
    
    expect($validator->validate($context))->toBeTrue();

    // Test non-Thai IP with Cloudflare header
    $_SERVER['HTTP_CF_IPCOUNTRY'] = 'US';
    $context = new VisitorContext('5.6.7.8', 'Test Browser', 'https://example.com');
    $validator = new ThailandIpValidator(); 
    
    // Debug logging
    error_log("Testing non-Thai IP with CF_IPCOUNTRY = US");
    error_log("Headers: " . print_r($_SERVER, true));
    
    expect($validator->validate($context))->toBeFalse();

    unset($_SERVER['HTTP_CF_IPCOUNTRY']);
});

test('validator handles service failures gracefully', function () {
    // Simulate a scenario where all services might fail
    $context = new VisitorContext('invalid-ip', 'Test Browser', 'https://example.com');
    
    // Should not throw any exceptions
    $result = null;
    $exception = null;
    
    try {
        $result = (new ThailandIpValidator())->validate($context);
    } catch (\Exception $e) {
        $exception = $e;
    }

    expect($exception)->toBeNull();
    expect($result)->toBeFalse();
});
