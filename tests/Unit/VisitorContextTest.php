<?php

use App\VisitorContext;

test('creates visitor context with all parameters', function () {
    $context = new VisitorContext(
        '192.168.1.1',
        'Mozilla/5.0',
        'https://google.com'
    );
    
    expect($context->getIpAddress())->toBe('192.168.1.1')
        ->and($context->getUserAgent())->toBe('Mozilla/5.0')
        ->and($context->getReferer())->toBe('https://google.com');
});

test('handles empty parameters', function () {
    $context = new VisitorContext('', '', '');
    
    expect($context->getIpAddress())->toBe('')
        ->and($context->getUserAgent())->toBe('')
        ->and($context->getReferer())->toBe('');
});

test('handles null referer', function () {
    $context = new VisitorContext(
        '192.168.1.1',
        'Mozilla/5.0',
        null
    );
    
    expect($context->getIpAddress())->toBe('192.168.1.1')
        ->and($context->getUserAgent())->toBe('Mozilla/5.0')
        ->and($context->getReferer())->toBeNull();
});
