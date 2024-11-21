<?php

namespace App;

use App\Contracts\ResponseHandlerInterface;
use App\Contracts\VisitorValidatorInterface;
use App\SessionManager;
use App\VisitorContext;
use App\ValidatorFactory;


/**
 * This class is the main application logic. It runs the validation, shows
 * the correct page (main or fake), and handles debug mode.
 *
 * @package App
 */
class VisitorValidationApp
{
    /**
     * The template for the main page.
     *
     * @const string
     */
    private const MAIN_TEMPLATE = './screens/main.html';

    /**
     * The template for the fake page.
     *
     * @const string
     */
    private const FAKE_TEMPLATE = './screens/fake.html';

    /**
     * The GET parameter for debug mode.
     *
     * @const string
     */
    private const DEBUG_PARAM = 'debug_page';

    /**
     * The HTTP headers to check for IP addresses.
     *
     * @const array<string>
     */
    private const IP_HEADERS = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR', 
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    /**
     * The constructor for the class.
     *
     * @param ValidatorFactory          $validatorFactory The factory for creating validators.
     * @param ResponseHandlerInterface $responseHandler   The response handler.
     * @param SessionManager            $sessionManager    The session manager.
     */
    public function __construct(
        private ValidatorFactory $validatorFactory,
        private ResponseHandlerInterface $responseHandler,
        private SessionManager $sessionManager
    ) {}

    /**
     * Runs the application.
     *
     * @return void
     */
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

    /**
     * Handles debug mode.
     *
     * @return bool
     */
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

    /**
     * Handles visitor validation.
     *
     * @return void
     */
    private function handleVisitorValidation(): void
    {
        $context = $this->createVisitorContext();
        
        // Debug logging
        error_log("Referrer: " . ($context->getReferer() ?? 'null'));
        error_log("IP: " . $context->getIpAddress());
        error_log("User Agent: " . $context->getUserAgent());
        
        if ($this->validateVisitor($context)) {
            $this->sessionManager->markAsValid();
            $this->showMainTemplate();
        } else {
            $this->showFakeTemplate();
        }
    }

    /**
     * Creates a visitor context.
     *
     * @return VisitorContext
     */
    private function createVisitorContext(): VisitorContext
    {
        // Get all headers for debugging
        $headers = getallheaders();
        error_log("All headers: " . print_r($headers, true));
        
        // Check if headers exist
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        // Log if headers are missing
        if (empty($userAgent)) {
            error_log("Warning: User Agent is empty");
        }
        if (empty($referer)) {
            error_log("Warning: Referer is empty");
        }
        
        return new VisitorContext(
            $this->getClientIp(),
            $userAgent,
            $referer
        );
    }

    /**
     * Validates a visitor.
     *
     * @param VisitorContext $context The visitor context.
     *
     * @return bool
     */
    private function validateVisitor(VisitorContext $context): bool
    {
        foreach ($this->validatorFactory->getValidators() as $validator) {
            if (!$validator->validate($context)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Shows the main template.
     *
     * @return void
     */
    private function showMainTemplate(): void
    {
        $this->responseHandler->handle(self::MAIN_TEMPLATE);
    }

    /**
     * Shows the fake template.
     *
     * @return void
     */
    private function showFakeTemplate(): void 
    {
        $this->responseHandler->handle(self::FAKE_TEMPLATE);
    }

    /**
     * Gets the client IP from the HTTP headers.
     *
     * @return string
     */
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