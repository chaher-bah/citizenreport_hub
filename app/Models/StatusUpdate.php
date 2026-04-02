<?php
/**
 * StatusUpdate Model
 */

class StatusUpdate extends Model
{
    protected string $table = 'status_updates';

    /**
     * Get all status updates for a report
     */
    public function getByReportId(int $reportId): array
    {
        $sql = "SELECT su.*, u.cin as updated_by_cin, u.role as updated_by_role
                FROM {$this->table} su
                JOIN users u ON su.updated_by = u.id
                WHERE su.report_id = :report_id
                ORDER BY su.created_at ASC";
        
        return $this->db->fetchAll($sql, ['report_id' => $reportId]);
    }

    /**
     * Add a status update
     */
    public function addStatusUpdate(int $reportId, string $status, ?string $comment, int $updatedBy): int
    {
        return $this->create([
            'report_id' => $reportId,
            'status' => $status,
            'comment' => $comment,
            'updated_by' => $updatedBy,
        ]);
    }

    /**
     * Get the latest status update for a report
     */
    public function getLatest(int $reportId): ?array
    {
        $sql = "SELECT su.*, u.cin as updated_by_cin, u.role as updated_by_role
                FROM {$this->table} su
                JOIN users u ON su.updated_by = u.id
                WHERE su.report_id = :report_id
                ORDER BY su.created_at DESC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, ['report_id' => $reportId]);
    }

    /**
     * Get status updates count for a report
     */
    public function getCount(int $reportId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE report_id = :report_id";
        $result = $this->db->fetchOne($sql, ['report_id' => $reportId]);
        return (int) ($result['count'] ?? 0);
    }
}
