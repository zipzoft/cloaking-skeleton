<?php

use App\VisitorValidationApp;
use App\ValidatorFactory;
use App\Contracts\ResponseHandlerInterface;
use App\SessionManager;
use App\Contracts\VisitorValidatorInterface;
use App\VisitorContext;

beforeEach(function () {
    // Reset superglobals
    $_SERVER = [];
    $_GET = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    $_SESSION = [];
});

test('shows main page in debug mode', function () {
    $validatorFactory = new ValidatorFactory();
    $responseHandler = \Mockery::mock(ResponseHandlerInterface::class);
    $sessionManager = new SessionManager();
    
    $responseHandler->shouldReceive('handle')
        ->once()
        ->with('./screens/main.html');
    
    $_GET['debug_page'] = 'main';
    
    $app = new VisitorValidationApp($validatorFactory, $responseHandler, $sessionManager);
    $app->run();
});

test('shows fake page in debug mode', function () {
    $validatorFactory = new ValidatorFactory();
    $responseHandler = \Mockery::mock(ResponseHandlerInterface::class);
    $sessionManager = new SessionManager();
    
    $responseHandler->shouldReceive('handle')
        ->once()
        ->with('./screens/fake.html');
    
    $_GET['debug_page'] = 'fake';
    
    $app = new VisitorValidationApp($validatorFactory, $responseHandler, $sessionManager);
    $app->run();
});

test('shows main page for valid session', function () {
    $validatorFactory = new ValidatorFactory();
    $responseHandler = \Mockery::mock(ResponseHandlerInterface::class);
    $sessionManager = new SessionManager();
    
    $responseHandler->shouldReceive('handle')
        ->once()
        ->with('./screens/main.html');
    
    $_SESSION['valid_visitor'] = true;
    
    $app = new VisitorValidationApp($validatorFactory, $responseHandler, $sessionManager);
    $app->run();
});

test('validates visitor and shows main page when all validators pass', function () {
    $validatorFactory = new ValidatorFactory();
    $responseHandler = \Mockery::mock(ResponseHandlerInterface::class);
    $sessionManager = new SessionManager();
    
    $validator = \Mockery::mock(VisitorValidatorInterface::class);
    $validator->shouldReceive('validate')
        ->once()
        ->andReturn(true);
    
    $validatorFactory->addValidator($validator);
    
    $responseHandler->shouldReceive('handle')
        ->once()
        ->with('./screens/main.html');
    
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTP_USER_AGENT'] = 'Test Browser';
    $_SERVER['HTTP_REFERER'] = 'https://google.com';
    
    $app = new VisitorValidationApp($validatorFactory, $responseHandler, $sessionManager);
    $app->run();
    
    expect($_SESSION['valid_visitor'])->toBeTrue();
});

test('shows fake page when validator fails', function () {
    $validatorFactory = new ValidatorFactory();
    $responseHandler = \Mockery::mock(ResponseHandlerInterface::class);
    $sessionManager = new SessionManager();
    
    $validator = \Mockery::mock(VisitorValidatorInterface::class);
    $validator->shouldReceive('validate')
        ->once()
        ->andReturn(false);
    
    $validatorFactory->addValidator($validator);
    
    $responseHandler->shouldReceive('handle')
        ->once()
        ->with('./screens/fake.html');
    
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTP_USER_AGENT'] = 'Test Browser';
    $_SERVER['HTTP_REFERER'] = 'https://example.com';
    
    $app = new VisitorValidationApp($validatorFactory, $responseHandler, $sessionManager);
    $app->run();
    
    expect(isset($_SESSION['valid_visitor']))->toBeFalse();
});

test('gets client IP from various headers', function () {
    $validatorFactory = new ValidatorFactory();
    $responseHandler = \Mockery::mock(ResponseHandlerInterface::class);
    $sessionManager = new SessionManager();
    
    $validator = \Mockery::mock(VisitorValidatorInterface::class);
    $validator->shouldReceive('validate')->andReturn(true);
    $validatorFactory->addValidator($validator);
    
    $responseHandler->shouldReceive('handle')->with('./screens/main.html');
    
    // Test different IP headers
    $ipHeaders = [
        'HTTP_CLIENT_IP' => '1.2.3.4',
        'HTTP_X_FORWARDED_FOR' => '5.6.7.8',
        'HTTP_X_FORWARDED' => '9.10.11.12',
        'HTTP_X_CLUSTER_CLIENT_IP' => '13.14.15.16',
        'HTTP_FORWARDED_FOR' => '17.18.19.20',
        'HTTP_FORWARDED' => '21.22.23.24',
        'REMOTE_ADDR' => '25.26.27.28'
    ];
    
    foreach ($ipHeaders as $header => $ip) {
        $_SERVER = [
            $header => $ip,
            'HTTP_USER_AGENT' => 'Test Browser',
            'HTTP_REFERER' => 'https://google.com'
        ];
        
        $app = new VisitorValidationApp($validatorFactory, $responseHandler, $sessionManager);
        $app->run();
        
        // The validator should receive the correct IP
        $validator->shouldHaveReceived('validate')
            ->with(\Mockery::on(function ($context) use ($ip) {
                return $context instanceof VisitorContext && $context->getIpAddress() === $ip;
            }));
    }
});

test('handles missing HTTP headers gracefully', function () {
    $validatorFactory = new ValidatorFactory();
    $responseHandler = \Mockery::mock(ResponseHandlerInterface::class);
    $sessionManager = new SessionManager();
    
    $validator = \Mockery::mock(VisitorValidatorInterface::class);
    $validator->shouldReceive('validate')->andReturn(true);
    $validatorFactory->addValidator($validator);
    
    $responseHandler->shouldReceive('handle')->with('./screens/main.html');
    
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    // Intentionally not setting HTTP_USER_AGENT and HTTP_REFERER
    
    $app = new VisitorValidationApp($validatorFactory, $responseHandler, $sessionManager);
    $app->run();
    
    $validator->shouldHaveReceived('validate')
        ->with(\Mockery::on(function ($context) {
            return $context instanceof VisitorContext 
                && $context->getUserAgent() === ''
                && $context->getReferer() === '';
        }));
});
