<?php

namespace Tests\Unit\Services\IpLocation;

use App\Services\IpLocation\CloudflareService;
use Mockery;

beforeEach(function () {
    // Clear all headers before each test
    if (function_exists('header_remove')) {
        header_remove();
    }
});

test('cloudflare service identifies Thai IP from cf-ipcountry header', function () {
    $headers = ['cf-ipcountry' => 'TH'];
    Mockery::mock('alias:getallheaders')->andReturn($headers);

    $service = new CloudflareService();
    expect($service->isThailandIp('1.2.3.4'))->toBeTrue();
});

test('cloudflare service identifies non-Thai IP from cf-ipcountry header', function () {
    $headers = ['cf-ipcountry' => 'US'];
    Mockery::mock('alias:getallheaders')->andReturn($headers);

    $service = new CloudflareService();
    expect($service->isThailandIp('1.2.3.4'))->toBeFalse();
});

test('cloudflare service returns null when no headers present', function () {
    Mockery::mock('alias:getallheaders')->andReturn([]);

    $service = new CloudflareService();
    expect($service->isThailandIp('1.2.3.4'))->toBeNull();
});

test('cloudflare service handles case-insensitive country codes', function () {
    $cases = [
        'th',
        'Th',
        'tH',
        'TH'
    ];

    foreach ($cases as $code) {
        $headers = ['cf-ipcountry' => $code];
        Mockery::mock('alias:getallheaders')->andReturn($headers);

        $service = new CloudflareService();
        expect($service->isThailandIp('1.2.3.4'))->toBeTrue();
    }
});

test('cloudflare service handles different header variations', function () {
    $headerVariations = [
        ['cf-ipcountry' => 'TH'],
        ['CF-IPCountry' => 'TH'],
        ['HTTP_CF_IPCOUNTRY' => 'TH']
    ];

    foreach ($headerVariations as $headers) {
        Mockery::mock('alias:getallheaders')->andReturn($headers);

        $service = new CloudflareService();
        expect($service->isThailandIp('1.2.3.4'))->toBeTrue();
    }
});

test('cloudflare service handles invalid country codes', function () {
    $invalidCodes = [
        '',
        'INVALID',
        '123',
        'T',
        'THH'
    ];

    foreach ($invalidCodes as $code) {
        $headers = ['cf-ipcountry' => $code];
        Mockery::mock('alias:getallheaders')->andReturn($headers);

        $service = new CloudflareService();
        expect($service->isThailandIp('1.2.3.4'))->toBeFalse();
    }
});

test('cloudflare service handles missing getallheaders function', function () {
    // Simulate environment where getallheaders doesn't exist
    if (function_exists('getallheaders')) {
        Mockery::mock('alias:getallheaders')->andReturn(null);
    }

    $service = new CloudflareService();
    expect($service->isThailandIp('1.2.3.4'))->toBeNull();
});

test('cloudflare service prioritizes headers correctly', function () {
    $headers = [
        'cf-ipcountry' => 'US',
        'CF-IPCountry' => 'TH',
        'HTTP_CF_IPCOUNTRY' => 'JP'
    ];

    Mockery::mock('alias:getallheaders')->andReturn($headers);

    $service = new CloudflareService();
    expect($service->isThailandIp('1.2.3.4'))->toBeFalse(); // Should use first header (US)
});
