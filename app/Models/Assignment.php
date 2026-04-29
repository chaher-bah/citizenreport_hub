<?php
/**
 * Assignment Model
 * Handles report assignments to branches
 */

class Assignment extends Model
{
    protected string $table = 'assignments';

    /**
     * Get assignment for a report with branch info
     */
    public function getByReportId(int $reportId): ?array
    {
        $sql = "SELECT a.*, b.name as branch_name, b.contact_number as branch_contact
                FROM {$this->table} a
                JOIN branches b ON a.branch_id = b.id
                WHERE a.report_id = :report_id
                ORDER BY a.assigned_at DESC
                LIMIT 1";

        return $this->db->fetchOne($sql, ['report_id' => $reportId]);
    }

    /**
     * Assign or update report to a branch
     */
    public function assignReport(int $reportId, int $branchId): int
    {
        // Check if assignment already exists
        $existing = $this->getByReportId($reportId);

        if ($existing) {
            // Update existing assignment
            $sql = "UPDATE {$this->table} 
                    SET branch_id = :branch_id, assigned_at = NOW()
                    WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute(['branch_id' => $branchId, 'id' => $existing['id']]);
            return $existing['id'];
        }

        // Create new assignment
        return $this->create([
            'report_id' => $reportId,
            'branch_id' => $branchId,
        ]);
    }
}
