<?php

namespace App;

class SessionManager
{
    private const VALID_VISITOR_KEY = 'valid_visitor';
    
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function markAsValid(): void
    {
        $_SESSION[self::VALID_VISITOR_KEY] = true;
        setcookie(self::VALID_VISITOR_KEY, '1', time() + 86400, '/'); // 24 hours
    }

    public function isValid(): bool
    {
        return isset($_SESSION[self::VALID_VISITOR_KEY]) || 
               (isset($_COOKIE[self::VALID_VISITOR_KEY]) && $_COOKIE[self::VALID_VISITOR_KEY] === '1');
    }
}