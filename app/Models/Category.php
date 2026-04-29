<?php
/**
 * Category Model
 * Handles report categories with default branch assignment
 */

class Category extends Model
{
    protected string $table = 'categories';

    /**
     * Get all categories with their default branch info
     */
    public function getAllWithBranch(): array
    {
        $sql = "SELECT c.*, b.name as default_branch_name, b.contact_number as branch_contact
                FROM {$this->table} c
                LEFT JOIN branches b ON c.default_branch_id = b.id
                ORDER BY c.name ASC";

        return $this->db->fetchAll($sql);
    }

    public function getAllOrdered(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }
    /**
     * Get category by ID with default branch
     */
    public function getByIdWithBranch(int $id): ?array
    {
        $sql = "SELECT c.*, b.name as default_branch_name, b.contact_number as branch_contact
                FROM {$this->table} c
                LEFT JOIN branches b ON c.default_branch_id = b.id
                WHERE c.id = :id
                LIMIT 1";

        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    /**
     * Create a new category
     */
    public function createCategory(string $name, string $description, ?int $defaultBranchId): int
    {
        return $this->create([
            'name' => $name,
            'description' => $description,
            'default_branch_id' => $defaultBranchId,
        ]);
    }

    /**
     * Update a category
     */
    public function updateCategory(int $id, string $name, string $description, ?int $defaultBranchId): int
    {
        return $this->update($id, [
            'name' => $name,
            'description' => $description,
            'default_branch_id' => $defaultBranchId,
        ]);
    }

    /**
     * Check if category name exists
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
     * Check if category is in use by any reports
     */
    public function isInUse(int $id): bool
    {
        $sql = "SELECT COUNT(*) as count FROM reports WHERE category_id = :id";
        $result = $this->db->fetchOne($sql, ['id' => $id]);
        return (int) ($result['count'] ?? 0) > 0;
    }
}
