<?php

namespace App;

class SessionRateLimiter {

    public function __construct(private int $limit, private int $window, private string $sessionKey = 'rate_limit_data') {
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }
    }

    public function allowRequest() {
        $currentTime = time();
        $requests = &$_SESSION[$this->sessionKey];

        // Remove requests older than the window
        $requests = array_filter($requests, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < $this->window;
        });

        // Check if the limit is exceeded
        if (count($requests) >= $this->limit) {
            return false; // Rate limit exceeded
        }

        // Add the current request timestamp
        $requests[] = $currentTime;
        return true; // Request allowed
    }

    public function getRemainingAttempts() {
        $currentTime = time();
        $requests = &$_SESSION[$this->sessionKey];

        // Remove requests older than the window (re-filtering for accuracy if allowRequest wasn't just called)
        $requests = array_filter($requests, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < $this->window;
        });

        return max(0, $this->limit - count($requests));
    }

    public function getTimeUntilReset() {
        $requests = &$_SESSION[$this->sessionKey];
        if (empty($requests)) {
            return 0;
        }
        $oldestRequestTime = min($requests);
        $resetTime = $oldestRequestTime + $this->window;
        return max(0, $resetTime - time());
    }
}
?>