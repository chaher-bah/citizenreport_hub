<?php
/**
 * Base Model Class
 * All models should extend this class
 */

abstract class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find a record by ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    /**
     * Find all records
     */
    public function findAll(array $conditions = [], string $orderBy = null): array
    {
        $sql = "SELECT * FROM {$this->table}";
        
        $where = [];
        $params = [];
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Find one record by conditions
     */
    public function findOne(array $conditions): ?array
    {
        $where = [];
        $params = [];
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) . " LIMIT 1";
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Create a new record
     */
    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Update a record
     */
    public function update(int $id, array $data): int
    {
        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }

    /**
     * Delete a record
     */
    public function delete(int $id): int
    {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }

    /**
     * Count records
     */
    public function count(array $conditions = []): int
    {
        $where = [];
        $params = [];
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Execute a custom query
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        return $this->db->query($sql, $params);
    }
}
