<?php

use App\SessionManager;

beforeEach(function () {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    $_SESSION = [];
    $_COOKIE = [];
});

test('marks visitor as valid in session', function () {
    $manager = new SessionManager();
    $manager->markAsValid();
    
    expect($_SESSION['valid_visitor'])->toBeTrue();
});

test('detects valid visitor from session', function () {
    $manager = new SessionManager();
    $_SESSION['valid_visitor'] = true;
    
    expect($manager->isValid())->toBeTrue();
});

test('detects valid visitor from cookie', function () {
    $manager = new SessionManager();
    $_COOKIE['valid_visitor'] = '1';
    
    expect($manager->isValid())->toBeTrue();
});

test('detects invalid visitor when no session or cookie', function () {
    $manager = new SessionManager();
    
    expect($manager->isValid())->toBeFalse();
});

test('detects invalid visitor with wrong cookie value', function () {
    $manager = new SessionManager();
    $_COOKIE['valid_visitor'] = '0';
    
    expect($manager->isValid())->toBeFalse();
});
