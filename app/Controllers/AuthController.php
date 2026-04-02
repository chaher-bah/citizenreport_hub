<?php
/**
 * Auth Controller
 * Handles user authentication (login, register, logout)
 */

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Show login page
     */
    public function showLogin(): void
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->isLoggedIn()) {
            if ($this->isWorker()) {
                $this->redirect(BASE_URL . '/admin/dashboard');
            } else {
                $this->redirect(BASE_URL . '/dashboard');
            }
            return;
        }

        $this->viewWithLayout('auth/login', [
            'title' => 'Login',
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
            'old' => $_SESSION['old_input'] ?? [],
        ]);

        $_SESSION['old_input'] = [];
    }

    /**
     * Process login
     */
    public function login(): void
    {
        $cin = trim($this->post('cin', ''));
        $password = $this->post('password', '');

        // Validation
        $errors = $this->validateLogin($cin, $password);

        if (!empty($errors)) {
            $_SESSION['old_input'] = ['cin' => $cin];
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect(BASE_URL . '/auth/login');
            return;
        }

        // Authenticate
        $user = $this->userModel->authenticate($cin, $password);

        if (!$user) {
            error_log("Login failed for CIN: {$cin}");
            $_SESSION['old_input'] = ['cin' => $cin];
            $this->setFlash('error', 'Invalid CIN or password.');
            $this->redirect(BASE_URL . '/auth/login');
            return;
        }

        // Set session
        $_SESSION['user'] = $user;
        $_SESSION['old_input'] = [];

        // Redirect based on role
        if ($user['role'] === 'worker') {
            $this->setFlash('success', "Welcome back, {$user['cin']}!");
            $this->redirect(BASE_URL . '/admin/dashboard');
        } else {
            $this->setFlash('success', "Welcome back, {$user['cin']}!");
            $this->redirect(BASE_URL . '/dashboard');
        }
    }

    /**
     * Show registration page
     */
    public function showRegister(): void
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->isLoggedIn()) {
            if ($this->isWorker()) {
                $this->redirect(BASE_URL . '/admin/dashboard');
            } else {
                $this->redirect(BASE_URL . '/dashboard');
            }
            return;
        }

        $this->viewWithLayout('auth/register', [
            'title' => 'Register',
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
            'old' => $_SESSION['old_input'] ?? [],
        ]);
        
        $_SESSION['old_input'] = [];
    }

    /**
     * Process registration
     */
    public function register(): void
    {
        $cin = trim($this->post('cin', ''));
        $email = trim($this->post('email', ''));
        $phone = trim($this->post('phone', ''));
        $password = $this->post('password', '');
        $confirmPassword = $this->post('confirm_password', '');
        $role = $this->post('role', 'citizen');
        $workId = trim($this->post('work_id', ''));

        // Validation
        $errors = $this->validateRegistration($cin, $email, $password, $confirmPassword, $role, $workId);

        if (!empty($errors)) {
            $_SESSION['old_input'] = [
                'cin' => $cin,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'work_id' => $workId,
            ];
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect(BASE_URL . '/auth/register');
            return;
        }

        // Check for existing CIN and email
        if ($this->userModel->cinExists($cin)) {
            $_SESSION['old_input'] = ['cin' => $cin, 'email' => $email, 'phone' => $phone, 'role' => $role];
            $this->setFlash('error', 'This CIN is already registered.');
            $this->redirect(BASE_URL . '/auth/register');
            return;
        }

        if ($this->userModel->emailExists($email)) {
            $_SESSION['old_input'] = ['cin' => $cin, 'email' => $email, 'phone' => $phone, 'role' => $role];
            $this->setFlash('error', 'This email is already registered.');
            $this->redirect(BASE_URL . '/auth/register');
            return;
        }

        // For workers, check work_id uniqueness
        if ($role === 'worker' && !empty($workId)) {
            $existingWorker = $this->userModel->findOne(['work_id' => $workId]);
            if ($existingWorker) {
                $_SESSION['old_input'] = ['cin' => $cin, 'email' => $email, 'phone' => $phone, 'role' => $role, 'work_id' => $workId];
                $this->setFlash('error', 'This Work ID is already registered.');
                $this->redirect(BASE_URL . '/auth/register');
                return;
            }
        }

        // Create user
        try {
            $userData = [
                'cin' => $cin,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'role' => $role,
            ];

            if ($role === 'worker' && !empty($workId)) {
                $userData['work_id'] = $workId;
            }

            $userId = $this->userModel->register($userData);

            if ($userId) {
                $this->setFlash('success', 'Registration successful! Please login.');
                $this->redirect(BASE_URL . '/auth/login');
            } else {
                throw new Exception('Failed to create user account.');
            }
        } catch (Exception $e) {
            $_SESSION['old_input'] = ['cin' => $cin, 'email' => $email, 'phone' => $phone, 'role' => $role];
            $this->setFlash('error', 'Registration failed. Please try again.');
            $this->redirect(BASE_URL . '/auth/register');
        }
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        session_destroy();
        session_start();
        $this->setFlash('success', 'You have been logged out successfully.');
        $this->redirect(BASE_URL . '/auth/login');
    }

    /**
     * Validate login input
     */
    private function validateLogin(string $cin, string $password): array
    {
        $errors = [];

        if (empty($cin)) {
            $errors[] = 'CIN is required.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        }

        return $errors;
    }

    /**
     * Validate registration input
     */
    private function validateRegistration(
        string $cin,
        string $email,
        string $password,
        string $confirmPassword,
        string $role,
        ?string $workId
    ): array {
        $errors = [];

        // CIN validation
        if (empty($cin)) {
            $errors[] = 'CIN is required.';
        } elseif (strlen($cin) < 3) {
            $errors[] = 'CIN must be at least 3 characters.';
        }

        // Email validation
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        // Phone validation (optional but must be valid if provided)
        if (!empty($phone) && !preg_match('/^[\d\+\-\s\(\)]{8,20}$/', $phone)) {
            $errors[] = 'Please enter a valid phone number.';
        }

        // Password validation
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        // Confirm password
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        // Role validation
        if (!in_array($role, ['citizen', 'worker'])) {
            $errors[] = 'Invalid role selected.';
        }

        // Work ID required for workers
        if ($role === 'worker' && empty($workId)) {
            $errors[] = 'Work ID is required for municipality workers.';
        }

        return $errors;
    }
}
