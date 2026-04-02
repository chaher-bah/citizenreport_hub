<?php
/**
 * User Model
 */

class User extends Model
{
    protected string $table = 'users';

    /**
     * Find user by CIN
     */
    public function findByCin(string $cin): ?array
    {
        return $this->findOne(['cin' => $cin]);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findOne(['email' => $email]);
    }

    /**
     * Check if CIN exists
     */
    public function cinExists(string $cin, ?int $excludeId = null): bool
    {
        $user = $this->findByCin($cin);
        if ($user && $excludeId && $user['id'] === $excludeId) {
            return false;
        }
        return $user !== null;
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $user = $this->findByEmail($email);
        if ($user && $excludeId && $user['id'] === $excludeId) {
            return false;
        }
        return $user !== null;
    }

    /**
     * Register a new user
     */
    public function register(array $data): int
    {
        return $this->create([
            'cin' => $data['cin'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'role' => $data['role'],
            'work_id' => $data['work_id'] ?? null,
        ]);
    }

    /**
     * Authenticate user by CIN and password
     */
    public function authenticate(string $cin, string $password): ?array
    {
        $user = $this->findByCin($cin);

        if ($user && $password === $user['password']) {
            // Return user without password
            unset($user['password']);
            return $user;
        }

        return null;
    }

    /**
     * Get user by ID
     */
    public function getById(int $id): ?array
    {
        $user = $this->find($id);
        if ($user) {
            unset($user['password_hash']);
        }
        return $user;
    }

    /**
     * Get all citizens
     */
    public function getAllCitizens(): array
    {
        return $this->findAll(['role' => 'citizen'], 'created_at DESC');
    }

    /**
     * Get all workers
     */
    public function getAllWorkers(): array
    {
        $sql = "SELECT id, cin, email, phone, role, work_id, created_at 
                FROM {$this->table} 
                WHERE role = 'worker' 
                ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $id, string $newPassword): int
    {
        return $this->update($id, [
            'password' => $newPassword
        ]);
    }
}
