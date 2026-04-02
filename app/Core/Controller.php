<?php
/**
 * Base Controller Class
 * All controllers should extend this class
 */

abstract class Controller
{
    protected Database $db;
    protected array $appConfig;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->appConfig = require BASE_PATH . '/config/app.php';
    }

    /**
     * Render a view template
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new Exception("View not found: {$view}");
        }
    }

    /**
     * Render a view with layout
     */
    protected function viewWithLayout(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        
        $contentPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        $layoutPath = __DIR__ . '/../Views/layouts/' . $layout . '.php';
        
        if (!file_exists($contentPath)) {
            throw new Exception("View not found: {$view}");
        }
        
        ob_start();
        require $contentPath;
        $content = ob_get_clean();
        
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect back to previous page
     */
    protected function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    /**
     * Set a flash message
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get and clear flash message
     */
    protected function getFlash(string $type): ?string
    {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }

    /**
     * Get the currently logged in user
     */
    protected function getCurrentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is logged in
     */
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    /**
     * Check if current user is a worker
     */
    protected function isWorker(): bool
    {
        return $this->isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'worker';
    }

    /**
     * Check if current user is a citizen
     */
    protected function isCitizen(): bool
    {
        return $this->isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'citizen';
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            $this->setFlash('error', 'Please login to access this page.');
            $this->redirect(BASE_URL . '/auth/login');
        }
    }   

    /**
     * Require worker role
     */
    protected function requireWorker(): void
    {
        $this->requireAuth();
        if (!$this->isWorker()) {
            $this->setFlash('error', 'Access denied. Worker privileges required.');
            $this->redirect(BASE_URL . '/dashboard');
        }
    }

    /**
     * Require citizen role
     */
    protected function requireCitizen(): void
    {
        $this->requireAuth();
        if (!$this->isCitizen()) {
            $this->setFlash('error', 'Access denied. Citizen privileges required.');
            $this->redirect(BASE_URL . '/admin/dashboard');
        }
    }

    /**
     * Get POST data
     */
    protected function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Validate required fields
     */
    protected function validateRequired(array $fields, array $data): array
    {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field]) && $data[$field] !== '0') {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }
        return $errors;
    }
}
