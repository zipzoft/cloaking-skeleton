<?php

namespace App;

/**
 * Manages the session for the application.
 *
 * The session manager stores a key in the session and in a cookie to
 * mark the visitor as valid. It also provides methods to check if a
 * visitor is valid.
 */
class SessionManager
{
    private const VALID_VISITOR_KEY = 'valid_visitor';
    
    /**
     * Constructs the session manager.
     *
     * If the session is not already started, it starts the session.
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Marks a visitor as valid.
     *
     * Sets a key in the session and a cookie to mark the visitor as valid.
     */
    public function markAsValid(): void
    {
        $_SESSION[self::VALID_VISITOR_KEY] = true;
        setcookie(self::VALID_VISITOR_KEY, '1', time() + 86400, '/'); // 24 hours
    }

    /**
     * Checks if a visitor is valid.
     *
     * Checks if the key is set in the session or if the cookie is set.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return isset($_SESSION[self::VALID_VISITOR_KEY]) || 
               (isset($_COOKIE[self::VALID_VISITOR_KEY]) && $_COOKIE[self::VALID_VISITOR_KEY] === '1');
    }
}