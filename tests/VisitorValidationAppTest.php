<?php

use App\VisitorValidationApp;
use App\ValidatorFactory;

test('app can be instantiated', function () {
    $app = new VisitorValidationApp();
    expect($app)->toBeInstanceOf(VisitorValidationApp::class);
});

test('app can validate visitor', function () {
    $app = new VisitorValidationApp();
    $result = $app->validate([
        'HTTP_REFERER' => 'https://www.google.com/search'
    ]);
    
    expect($result)->toBeTrue();
});

test('app rejects invalid visitor', function () {
    $app = new VisitorValidationApp();
    $result = $app->validate([
        'HTTP_REFERER' => 'https://www.bing.com/search'
    ]);
    
    expect($result)->toBeFalse();
});
