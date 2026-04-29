<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Sanitize input string
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 */
function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

/**
 * Check if request is POST
 */
function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Get POST value
 */
function post(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

/**
 * Get GET value
 */
function get(string $key, $default = null)
{
    return $_GET[$key] ?? $default;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF input field
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

/**
 * Format date
 */
function formatDate(string $date, string $format = 'M d, Y'): string
{
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDatetime(string $date, string $format = 'M d, Y g:i A'): string
{
    return date($format, strtotime($date));
}

/**
 * Get time ago
 */
function timeAgo(string $date): string
{
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($date);
    }
}

/**
 * Truncate string
 */
function truncate(string $string, int $length = 100, string $suffix = '...'): string
{
    if (strlen($string) <= $length) {
        return $string;
    }
    return substr($string, 0, $length) . $suffix;
}

/**
 * Generate random string
 */
function randomString(int $length = 10): string
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Slugify a string
 */
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    return $text;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

/**
 * Get current user
 */
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Check if current user is worker
 */
function isWorker(): bool
{
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'worker';
}

/**
 * Check if current user is citizen
 */
function isCitizen(): bool
{
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'citizen';
}

/**
 * Require authentication
 */
function requireAuth(): void
{
    if (!isLoggedIn()) {
        $_SESSION['flash']['error'] = 'Please login to access this page.';
        redirect(BASE_URL . '/auth/login');
    }
}

/**
 * Require worker role
 */
function requireWorker(): void
{
    requireAuth();
    if (!isWorker()) {
        $_SESSION['flash']['error'] = 'Access denied. Worker privileges required.';
        redirect(BASE_URL . '/dashboard');
    }
}

/**
 * Require citizen role
 */
function requireCitizen(): void
{
    requireAuth();
    if (!isCitizen()) {
        $_SESSION['flash']['error'] = 'Access denied. Citizen privileges required.';
        redirect(BASE_URL.'/admin/dashboard');
    }
}

/**
 * Set flash message
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get and clear flash message
 */
function getFlash(string $type): ?string
{
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Debug helper
 */
function dd(...$args): void
{
    echo '<pre>';
    foreach ($args as $arg) {
        var_dump($arg);
        echo "\n";
    }
    echo '</pre>';
    exit;
}

/**
 * Log message
 */
function logMessage(string $message, string $level = 'INFO'): void
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
    error_log($logMessage);
}

/**
 * Get base URL
 */
function baseUrl(): string
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return "{$protocol}://{$host}";
}

/**
 * Get asset URL
 */
function asset(string $path): string
{
    return baseUrl() . '/' . ltrim($path, '/');
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic)
 */
function isValidPhone(string $phone): bool
{
    return preg_match('/^[\d\+\-\s\(\)]{8,20}$/', $phone);
}

/**
 * Format file size
 */
function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
