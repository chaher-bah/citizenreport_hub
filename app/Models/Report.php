<?php
/**
 * Report Model
 */

class Report extends Model
{
    protected string $table = 'reports';

    /**
     * Valid report categories
     */
    public const CATEGORIES = [
        'pothole' => 'Pothole',
        'road_illumination' => 'Road Illumination',
        'security_concerns' => 'Security Concerns',
        'drivers_disobey_rules' => 'Drivers Disobey Rules'
    ];

    /**
     * Valid report statuses
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected'
    ];

    /**
     * Generate unique ticket ID
     */
    public function generateTicketId(): string
    {
        $date = date('Ymd');
        $prefix = 'CIT';
        
        do {
            $random = strtoupper(substr(uniqid(), -4));
            $ticketId = "{$prefix}-{$date}-{$random}";
            $existing = $this->findOne(['ticket_id' => $ticketId]);
        } while ($existing);
        
        return $ticketId;
    }

    /**
     * Create a new report
     */
    public function createReport(int $userId, array $data): int
    {
        $ticketId = $this->generateTicketId();
        
        return $this->create([
            'user_id' => $userId,
            'category' => $data['category'],
            'description' => $data['description'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'status' => 'pending',
            'ticket_id' => $ticketId,
        ]);
    }

    /**
     * Get report by ticket ID
     */
    public function findByTicketId(string $ticketId): ?array
    {
        return $this->findOne(['ticket_id' => $ticketId]);
    }

    /**
     * Get all reports for a user
     */
    public function getByUserId(int $userId): array
    {
        $sql = "SELECT r.*, u.cin as user_cin 
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.id
                WHERE r.user_id = :user_id
                ORDER BY r.created_at DESC";
        
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }

    /**
     * Get report with media
     */
    public function getWithMedia(int $id): ?array
    {
        $report = $this->find($id);
        if (!$report) {
            return null;
        }
        
        $reportMediaModel = new ReportMedia();
        $report['media'] = $reportMediaModel->getByReportId($id);
        
        return $report;
    }

    /**
     * Get report by ticket ID with media
     */
    public function getByTicketIdWithMedia(string $ticketId): ?array
    {
        $report = $this->findByTicketId($ticketId);
        if (!$report) {
            return null;
        }
        
        $reportMediaModel = new ReportMedia();
        $report['media'] = $reportMediaModel->getByReportId($report['id']);
        
        return $report;
    }

    /**
     * Update report status
     */
    public function updateStatus(int $id, string $status): int
    {
        if (!array_key_exists($status, self::STATUSES)) {
            throw new InvalidArgumentException("Invalid status: {$status}");
        }
        
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Get reports by status
     */
    public function getByStatus(string $status): array
    {
        return $this->findAll(['status' => $status], 'created_at DESC');
    }

    /**
     * Get all reports with user info
     */
    public function getAllWithUserInfo(): array
    {
        $sql = "SELECT r.*, u.cin as user_cin, u.email as user_email
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Get reports count by status
     */
    public function getCountByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY status";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Get recent reports
     */
    public function getRecent(int $limit = 10): array
    {
        $sql = "SELECT r.*, u.cin as user_cin 
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
