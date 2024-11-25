<?php

namespace Tests\Unit\Services\IpLocation;

use App\Services\IpLocation\DevelopmentService;

test('development service identifies localhost IPv4', function () {
    $service = new DevelopmentService();
    expect($service->isThailandIp('127.0.0.1'))->toBeTrue();
});

test('development service identifies localhost IPv6', function () {
    $service = new DevelopmentService();
    expect($service->isThailandIp('::1'))->toBeTrue();
});

test('development service identifies private network IPs', function () {
    $service = new DevelopmentService();
    
    // Test various private network ranges
    expect($service->isThailandIp('192.168.1.1'))->toBeTrue();
    expect($service->isThailandIp('192.168.0.1'))->toBeTrue();
    expect($service->isThailandIp('192.168.255.255'))->toBeTrue();
    expect($service->isThailandIp('10.0.0.1'))->toBeTrue();
    expect($service->isThailandIp('10.10.10.10'))->toBeTrue();
    expect($service->isThailandIp('10.255.255.255'))->toBeTrue();
});

test('development service returns null for non-development IPs', function () {
    $service = new DevelopmentService();
    
    // Public IPs
    expect($service->isThailandIp('8.8.8.8'))->toBeNull();
    expect($service->isThailandIp('1.1.1.1'))->toBeNull();
    expect($service->isThailandIp('203.150.0.1'))->toBeNull();
});

test('development service handles invalid IP formats', function () {
    $service = new DevelopmentService();
    
    // Invalid formats
    expect($service->isThailandIp(''))->toBeNull();
    expect($service->isThailandIp('invalid-ip'))->toBeNull();
    expect($service->isThailandIp('256.256.256.256'))->toBeNull();
    expect($service->isThailandIp('192.168'))->toBeNull();
});

test('development service handles edge cases of private ranges', function () {
    $service = new DevelopmentService();
    
    // Edge cases
    expect($service->isThailandIp('192.167.1.1'))->toBeNull(); // Just before 192.168.x.x
    expect($service->isThailandIp('192.169.1.1'))->toBeNull(); // Just after 192.168.x.x
    expect($service->isThailandIp('9.255.255.255'))->toBeNull(); // Just before 10.x.x.x
    expect($service->isThailandIp('11.0.0.0'))->toBeNull(); // Just after 10.x.x.x
});
