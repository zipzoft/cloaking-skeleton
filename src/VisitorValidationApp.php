<?php

namespace App;

use App\Contracts\ResponseHandlerInterface;
use App\Contracts\VisitorValidatorInterface;
use App\SessionManager;
use App\VisitorContext;
use App\ValidatorFactory;

class VisitorValidationApp
{
    private const MAIN_TEMPLATE = './screens/main.html';
    private const FAKE_TEMPLATE = './screens/fake.html';
    private const DEBUG_PARAM = 'debug_page';
    private const IP_HEADERS = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR', 
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    public function __construct(
        private ValidatorFactory $validatorFactory,
        private ResponseHandlerInterface $responseHandler,
        private SessionManager $sessionManager
    ) {}

    public function run(): void
    {
        if ($this->handleDebugMode()) {
            return;
        }

        if ($this->sessionManager->isValid()) {
            $this->showMainTemplate();
            return;
        }

        $this->handleVisitorValidation();
    }

    private function handleDebugMode(): bool
    {
        $debugPage = $_GET[self::DEBUG_PARAM] ?? null;
        if ($debugPage === null) {
            return false;
        }

        match ($debugPage) {
            'main' => $this->showMainTemplate(),
            'fake' => $this->showFakeTemplate(),
            default => null
        };

        return true;
    }

    private function handleVisitorValidation(): void
    {
        $context = $this->createVisitorContext();
        
        if ($this->validateVisitor($context)) {
            $this->sessionManager->markAsValid();
            $this->showMainTemplate();
        } else {
            $this->showFakeTemplate();
        }
    }

    private function createVisitorContext(): VisitorContext
    {
        return new VisitorContext(
            $this->getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_REFERER'] ?? null
        );
    }

    private function validateVisitor(VisitorContext $context): bool
    {
        foreach ($this->validatorFactory->getValidators() as $validator) {
            if (!$validator->validate($context)) {
                return false;
            }
        }
        return true;
    }

    private function showMainTemplate(): void
    {
        $this->responseHandler->handle(self::MAIN_TEMPLATE);
    }

    private function showFakeTemplate(): void 
    {
        $this->responseHandler->handle(self::FAKE_TEMPLATE);
    }

    private function getClientIp(): string
    {
        foreach (self::IP_HEADERS as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }

        return $_SERVER['REMOTE_ADDR'];
    }
}