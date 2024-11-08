<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\ValidatorFactory;
use App\ThailandIpValidator;
use App\GoogleReferrerValidator;
use App\HtmlResponseHandler;
use App\SessionManager;
use App\VisitorValidationApp;

// Create and configure the validator factory
$validatorFactory = new ValidatorFactory();
$validatorFactory->addValidator(new ThailandIpValidator());
$validatorFactory->addValidator(new GoogleReferrerValidator());

// Create response handler
$responseHandler = new HtmlResponseHandler();

// Create session manager
$sessionManager = new SessionManager();

// Create and run the application
$app = new VisitorValidationApp(
    $validatorFactory,
    $responseHandler,
    $sessionManager
);

$app->run();
