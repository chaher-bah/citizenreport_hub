<?php
/**
 * Branch Model
 * Handles municipality branches/departments
 */

class Branch extends Model
{
    protected string $table = 'branches';

    /**
     * Get all branches
     */
    public function getAllOrdered(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Create a new branch
     */
    public function createBranch(string $name, ?string $contactNumber): int
    {
        return $this->create([
            'name' => $name,
            'contact_number' => $contactNumber,
        ]);
    }

    /**
     * Update a branch
     */
    public function updateBranch(int $id, string $name, ?string $contactNumber): int
    {
        return $this->update($id, [
            'name' => $name,
            'contact_number' => $contactNumber,
        ]);
    }

    /**
     * Check if branch name exists
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = :name";
        $params = ['name' => $name];

        if ($excludeId) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return (int) ($result['count'] ?? 0) > 0;
    }

    /**
     * Check if branch is in use by any categories or assignments
     */
    public function isInUse(int $id): bool
    {
        // Check categories
        $sql = "SELECT COUNT(*) as count FROM categories WHERE default_branch_id = :id";
        $result = $this->db->fetchOne($sql, ['id' => $id]);
        if ((int) ($result['count'] ?? 0) > 0) {
            return true;
        }

        // Check assignments
        $sql = "SELECT COUNT(*) as count FROM assignments WHERE branch_id = :id";
        $result = $this->db->fetchOne($sql, ['id' => $id]);
        return (int) ($result['count'] ?? 0) > 0;
    }
}
