<?php

namespace App;

/**
 * Represents the context of a visitor
 */
class VisitorContext
{
    /**
     * Initialize the visitor context with IP address, user agent, and referer
     *
     * @param string $ipAddress The IP address of the visitor
     * @param string $userAgent The user agent string of the visitor
     * @param ?string $referer The referer URL of the visitor
     */
    public function __construct(
        private string $ipAddress,
        private string $userAgent,
        private ?string $referer
    ) {}

    /**
     * Get the IP address of the visitor
     *
     * @return string The IP address of the visitor
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * Get the user agent string of the visitor
     *
     * @return string The user agent string of the visitor
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * Get the referer URL of the visitor
     *
     * @return ?string The referer URL of the visitor
     */
    public function getReferer(): ?string
    {
        return $this->referer;
    }
}