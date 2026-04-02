<?php
/**
 * Auth Middleware
 * Checks if user is authenticated
 */

class AuthMiddleware
{
    /**
     * Handle the middleware request
     * @return bool True to continue, false to stop
     */
    public function handle(): bool
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash']['error'] = 'Please login to access this page.';
            header('Location: ' . BASE_URL . '/auth/login');
            return false;
        }

        return true;
    }
}
