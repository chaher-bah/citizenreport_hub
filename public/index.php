<?php


// Start session
session_start();


// Define base path
define('BASE_PATH', dirname(__DIR__));

// Define base URL for redirects (adjust if your project is in a subdirectory)
define('BASE_URL', '/citizenreport_hub/public');

// Autoloader
spl_autoload_register(function ($class) {
    // Core classes
    $corePath = BASE_PATH . '/app/Core/' . $class . '.php';
    if (file_exists($corePath)) {
        require_once $corePath;
        return;
    }
    
    // Controllers
    $controllerPath = BASE_PATH . '/app/Controllers/' . $class . '.php';
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        return;
    }
    
    // Models
    $modelPath = BASE_PATH . '/app/Models/' . $class . '.php';
    if (file_exists($modelPath)) {
        require_once $modelPath;
        return;
    }
    
    // Services
    $servicePath = BASE_PATH . '/app/Services/' . $class . '.php';
    if (file_exists($servicePath)) {
        require_once $servicePath;
        return;
    }
    
    // Middleware
    $middlewarePath = BASE_PATH . '/app/Middleware/' . $class . '.php';
    if (file_exists($middlewarePath)) {
        require_once $middlewarePath;
        return;
    }
});

// Get URL from query string
$url = $_GET['url'] ?? '/';
$url = '/' . trim($url, '/');
if ($url === '/') {
    $url = '/';
}

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Create router and define routes
$router = new Router();

// ============== Public Routes ==============

// Home - redirect to login or dashboard
$router->get('/', function () {
    if (isset($_SESSION['user'])) {
        if ($_SESSION['user']['role'] === 'worker') {
            header('Location: ' . BASE_URL . '/admin/dashboard');
        } else {
            header('Location: ' . BASE_URL . '/dashboard');
        }
    } else {
        header('Location: ' . BASE_URL . '/auth/login');
    }
    exit;
});

// ============== Auth Routes ==============

// Login
$router->get('/auth/login', [AuthController::class, 'showLogin']);
$router->post('/auth/login', [AuthController::class, 'login']);

// Register
$router->get('/auth/register', [AuthController::class, 'showRegister']);
$router->post('/auth/register', [AuthController::class, 'register']);

// Logout
$router->get('/auth/logout', [AuthController::class, 'logout']);

// ============== Citizen Routes ==============

// Citizen Dashboard
$router->get('/dashboard', [DashboardController::class, 'citizenDashboard'], [AuthMiddleware::class, CitizenMiddleware::class]);

// Report Creation
$router->get('/report/create', [ReportController::class, 'showCreate'], [AuthMiddleware::class, CitizenMiddleware::class]);
$router->post('/report/create', [ReportController::class, 'create'], [AuthMiddleware::class, CitizenMiddleware::class]);

// Report Success
$router->get('/report/success', [ReportController::class, 'showSuccess'], [AuthMiddleware::class, CitizenMiddleware::class]);

// View Report
$router->get('/report/view', [ReportController::class, 'viewReport'], [AuthMiddleware::class]);

// View Report by Ticket ID
$router->get('/report/ticket', [ReportController::class, 'viewByTicket'], [AuthMiddleware::class]);

// ============== Worker/Admin Routes ==============

// Worker Dashboard
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], [AuthMiddleware::class, WorkerMiddleware::class]);

// View Report (Worker)
$router->get('/admin/report/view', [AdminController::class, 'viewReport'], [AuthMiddleware::class, WorkerMiddleware::class]);

// Update Status (Worker)
$router->post('/admin/report/update-status', [AdminController::class, 'updateStatus'], [AuthMiddleware::class, WorkerMiddleware::class]);
$router->post('/admin/report/delete', [AdminController::class, 'deleteReport'], [AuthMiddleware::class, WorkerMiddleware::class]);
// Assign Branch (Worker)
$router->post('/admin/report/assign', [AdminController::class, 'assignBranch'], [AuthMiddleware::class, WorkerMiddleware::class]);

// Category Management
$router->get('/admin/categories', [AdminController::class, 'manageCategories'], [AuthMiddleware::class, WorkerMiddleware::class]);
$router->post('/admin/categories/create', [AdminController::class, 'createCategory'], [AuthMiddleware::class, WorkerMiddleware::class]);
$router->post('/admin/categories/update', [AdminController::class, 'updateCategory'], [AuthMiddleware::class, WorkerMiddleware::class]);
$router->post('/admin/categories/delete', [AdminController::class, 'deleteCategory'], [AuthMiddleware::class, WorkerMiddleware::class]);

// Branch Management
$router->get('/admin/branches', [AdminController::class, 'manageBranches'], [AuthMiddleware::class, WorkerMiddleware::class]);
$router->post('/admin/branches/create', [AdminController::class, 'createBranch'], [AuthMiddleware::class, WorkerMiddleware::class]);
$router->post('/admin/branches/update', [AdminController::class, 'updateBranch'], [AuthMiddleware::class, WorkerMiddleware::class]);
$router->post('/admin/branches/delete', [AdminController::class, 'deleteBranch'], [AuthMiddleware::class, WorkerMiddleware::class]);


// Broadcasts
$router->get('/broadcasts', [BroadcastController::class, 'index'], [AuthMiddleware::class]);
$router->get('/admin/broadcasts', [BroadcastController::class, 'manage'], [AuthMiddleware::class, WorkerMiddleware::class]);
$router->post('/admin/broadcasts/create', [BroadcastController::class, 'create'], [AuthMiddleware::class, WorkerMiddleware::class]);
$router->post('/admin/broadcasts/update', [BroadcastController::class, 'update'], [AuthMiddleware::class, WorkerMiddleware::class]);
$router->post('/admin/broadcasts/delete', [BroadcastController::class, 'delete'], [AuthMiddleware::class, WorkerMiddleware::class]);
// ============== Dispatch ==============

try {
    $router->dispatch($url, $method);
} catch (Exception $e) {
    error_log("Router error: " . $e->getMessage());
    http_response_code(500);
    echo "<h1>Internal Server Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
