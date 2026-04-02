<?php
/**
 * Citizen Middleware
 * Checks if user has citizen role
 */

class CitizenMiddleware
{
    /**
     * Handle the middleware request
     * @return bool True to continue, false to stop
     */
    public function handle(): bool
    {
        // First check if user is authenticated
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash']['error'] = 'Please login to access this page.';
            header('Location: ' . BASE_URL . '/auth/login');
            return false;
        }

        // Then check if user is a citizen
        if ($_SESSION['user']['role'] !== 'citizen') {
            $_SESSION['flash']['error'] = 'Access denied. Citizen privileges required.';
            header('Location: ' . BASE_URL . '/admin/dashboard');
            return false;
        }

        return true;
    }
}
