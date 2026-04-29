<?php
class Broadcast extends Model
{
    protected string $table = 'broadcasts';

    public function getAll(): array
    {
        $sql = "SELECT b.*, u.cin as created_by_cin 
                FROM {$this->table} b
                JOIN users u ON b.created_by = u.id
                WHERE is_active = 1 
                AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                ORDER BY b.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getAllAdmin(): array
    {
        $sql = "SELECT b.*, u.cin as created_by_cin 
                FROM {$this->table} b
                JOIN users u ON b.created_by = u.id
                ORDER BY b.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE is_active = 1 
                AND (scheduled_at IS NULL OR scheduled_at <= NOW())";
        $result = $this->db->fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }

    public function createBroadcast(string $title, string $message, ?string $zone, int $createdBy, ?string $scheduledAt): int
    {
        return $this->create([
            'title' => $title,
            'message' => $message,
            'zone' => $zone,
            'created_by' => $createdBy,
            'scheduled_at' => $scheduledAt,
            'is_active' => 1,
        ]);
    }

    public function updateBroadcast(int $id, string $title, string $message, ?string $zone, ?string $scheduledAt): int
    {
        return $this->update($id, [
            'title' => $title,
            'message' => $message,
            'zone' => $zone,
            'scheduled_at' => $scheduledAt,
        ]);
    }
}