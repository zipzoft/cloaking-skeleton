<?php

use App\ThailandIpValidator;
use App\VisitorContext;

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

test('handles invalid IP addresses gracefully', function () {
    $validator = new ThailandIpValidator();
    
    $invalidIPs = [
        'invalid',
        '256.256.256.256',
        '1.2.3',
        '1.2.3.4.5',
        ''
    ];
    
    foreach ($invalidIPs as $ip) {
        $context = new VisitorContext($ip, 'Test Browser', 'https://example.com');
        expect($validator->validate($context))
            ->toBeFalse()
            ->and($ip)->not->toBeEmpty();
    }
});
