<?php

use App\GoogleReferrerValidator;
use App\VisitorContext;

test('validates google.com referrer', function () {
    $validator = new GoogleReferrerValidator();
    $context = \Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getReferer')->andReturn('https://www.google.com/search?q=test');
    
    expect($validator->validate($context))->toBeTrue();
});

test('validates other google domain referrers', function () {
    $validator = new GoogleReferrerValidator();
    $context = \Mockery::mock(VisitorContext::class);
    
    $googleDomains = [
        'https://www.google.co.uk/search?q=test',
        'https://www.google.co.jp/search?q=test',
        'https://www.google.de/search?q=test'
    ];
    
    foreach ($googleDomains as $domain) {
        $context->shouldReceive('getReferer')->andReturn($domain);
        expect($validator->validate($context))->toBeTrue();
    }
});

test('rejects non-google referrers', function () {
    $validator = new GoogleReferrerValidator();
    $context = \Mockery::mock(VisitorContext::class);
    
    $nonGoogleDomains = [
        'https://www.bing.com/search?q=test',
        'https://www.yahoo.com/search?q=test',
        'https://example.com'
    ];
    
    foreach ($nonGoogleDomains as $domain) {
        $context->shouldReceive('getReferer')->andReturn($domain);
        expect($validator->validate($context))->toBeFalse();
    }
});

test('rejects empty referrer', function () {
    $validator = new GoogleReferrerValidator();
    $context = \Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getReferer')->andReturn('');
    
    expect($validator->validate($context))->toBeFalse();
});

test('rejects invalid URL format', function () {
    $validator = new GoogleReferrerValidator();
    $context = \Mockery::mock(VisitorContext::class);
    $context->shouldReceive('getReferer')->andReturn('not-a-valid-url');
    
    expect($validator->validate($context))->toBeFalse();
});
