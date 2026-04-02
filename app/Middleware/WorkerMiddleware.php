<?php
/**
 * Worker Middleware
 * Checks if user has worker role
 */

class WorkerMiddleware
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

        // Then check if user is a worker
        if ($_SESSION['user']['role'] !== 'worker') {
            $_SESSION['flash']['error'] = 'Access denied. Worker privileges required.';
            header('Location: ' . BASE_URL . '/dashboard');
            return false;
        }

        return true;
    }
}
