<?php

namespace Tests\Unit\Services\IpLocation;

use App\Services\IpLocation\IpRangeService;

test('ip range service identifies Thai IP in range', function () {
    $service = new IpRangeService();
    
    // Test IPs from different Thai ranges
    expect($service->isThailandIp('1.0.128.1'))->toBeTrue();
    expect($service->isThailandIp('1.46.0.1'))->toBeTrue();
    expect($service->isThailandIp('14.207.0.1'))->toBeTrue();
});

test('ip range service identifies non-Thai IP', function () {
    $service = new IpRangeService();
    
    // Test known non-Thai IPs
    expect($service->isThailandIp('8.8.8.8'))->toBeFalse(); // Google DNS
    expect($service->isThailandIp('1.1.1.1'))->toBeFalse(); // Cloudflare DNS
});

test('ip range service handles CIDR ranges correctly', function () {
    $service = new IpRangeService();
    
    // Test edge cases of ranges
    expect($service->isThailandIp('1.0.128.0'))->toBeTrue(); // Start of range
    expect($service->isThailandIp('1.0.255.255'))->toBeTrue(); // End of range
    expect($service->isThailandIp('1.0.127.255'))->toBeFalse(); // Just before range
    expect($service->isThailandIp('1.1.0.0'))->toBeFalse(); // Just after range
});

test('ip range service handles invalid IP addresses', function () {
    $service = new IpRangeService();
    
    // Test invalid IP formats
    expect($service->isThailandIp('invalid'))->toBeFalse();
    expect($service->isThailandIp('256.256.256.256'))->toBeFalse();
    expect($service->isThailandIp(''))->toBeFalse();
});

test('ip range service handles all Thai ranges', function () {
    $service = new IpRangeService();
    
    // Test sample IPs from each major Thai range
    $thaiRanges = [
        '1.0.128.0/17' => '1.0.129.1',
        '1.46.0.0/15' => '1.47.1.1',
        '14.207.0.0/16' => '14.207.1.1',
        '27.130.0.0/16' => '27.130.1.1',
        '49.48.0.0/13' => '49.49.1.1',
        '58.8.0.0/14' => '58.9.1.1',
        '101.51.0.0/16' => '101.51.1.1',
        '110.164.0.0/15' => '110.164.1.1',
        '171.96.0.0/13' => '171.97.1.1',
        '203.150.0.0/15' => '203.150.1.1'
    ];

    foreach ($thaiRanges as $range => $testIp) {
        expect($service->isThailandIp($testIp))
            ->toBeTrue("IP {$testIp} from range {$range} should be identified as Thai");
    }
});

test('ip range service handles different netmask sizes', function () {
    $service = new IpRangeService();
    
    // Test different CIDR netmask sizes
    $testCases = [
        // /13 network (large)
        '49.48.0.0' => true,
        '49.55.255.255' => true,
        '49.56.0.0' => false,
        
        // /15 network (medium)
        '110.164.0.0' => true,
        '110.165.255.255' => true,
        '110.166.0.0' => false,
        
        // /16 network (small)
        '14.207.0.0' => true,
        '14.207.255.255' => true,
        '14.208.0.0' => false,
        
        // /17 network (very small)
        '1.0.128.0' => true,
        '1.0.255.255' => true,
        '1.1.0.0' => false
    ];

    foreach ($testCases as $ip => $expected) {
        expect($service->isThailandIp($ip))
            ->toBe($expected, "IP {$ip} validation failed");
    }
});

test('ip range service performance with multiple checks', function () {
    $service = new IpRangeService();
    $startTime = microtime(true);
    
    // Test 1000 IPs
    for ($i = 0; $i < 1000; $i++) {
        $ip = rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
        $service->isThailandIp($ip);
    }
    
    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    
    // Should complete 1000 checks in less than 1 second
    expect($duration)->toBeLessThan(1.0, 'IP range checking should be performant');
});
