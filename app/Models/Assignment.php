<?php
/**
 * Assignment Model
 * Handles report assignments to branches (police, city_worker, utility_worker, other)
 */

class Assignment extends Model
{
    protected string $table = 'assignments';

    /**
     * Valid assignment branches
     */
    public const BRANCHES = [
        'police' => 'Police',
        'city_worker' => 'City Worker',
        'utility_worker' => 'Utility Worker',
        'other' => 'Other',
    ];

    /**
     * Get assignment for a report
     */
    public function getByReportId(int $reportId): ?array
    {
        $sql = "SELECT a.*, u.cin as assigned_by_cin
                FROM {$this->table} a
                LEFT JOIN users u ON a.assigned_by = u.id
                WHERE a.report_id = :report_id
                ORDER BY a.assigned_at DESC
                LIMIT 1";

        return $this->db->fetchOne($sql, ['report_id' => $reportId]);
    }

    /**
     * Assign or update report to a branch
     */
    public function assignReport(int $reportId, string $branch, int $assignedBy): int
    {
        if (!array_key_exists($branch, self::BRANCHES)) {
            throw new InvalidArgumentException("Invalid branch: {$branch}");
        }

        // Check if assignment already exists
        $existing = $this->getByReportId($reportId);

        if ($existing) {
            // Update existing assignment
            $sql = "UPDATE {$this->table} 
                    SET branch = :branch, assigned_at = NOW()
                    WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute(['branch' => $branch, 'id' => $existing['id']]);
            return $existing['id'];
        }

        // Create new assignment
        return $this->create([
            'report_id' => $reportId,
            'branch' => $branch,
            'assigned_by' => $assignedBy,
        ]);
    }
}
