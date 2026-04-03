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
        'drivers_disobey_rules' => 'Drivers Disobey Rules',
        'others' =>'Others'
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
            'category_detail' => $data['category_detail'] ?? null,
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
     * Get reports count by category
     */
    public function getCountByCategory(): array
    {
        $sql = "SELECT category, COUNT(*) as count
                FROM {$this->table}
                GROUP BY category";

        return $this->db->fetchAll($sql);
    }

    /**
     * Get reports with pagination, filters, and search
     */
    public function getPaginated(int $page = 1, int $perPage = 10, string $category = '', string $status = '', string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT r.*, u.cin as user_cin, u.email as user_email
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.id
                WHERE 1=1";
        $params = [];

        // Filter by category
        if (!empty($category)) {
            $sql .= " AND r.category = :category";
            $params['category'] = $category;
        }

        // Filter by status
        if (!empty($status)) {
            $sql .= " AND r.status = :status";
            $params['status'] = $status;
        }

        // Search by ticket_id or description
        if (!empty($search)) {
            $sql .= " AND (r.ticket_id LIKE :search OR r.description LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get total count with filters (for pagination)
     */
    public function getCountWithFilters(string $category = '', string $status = '', string $search = ''): int
    {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} r
                WHERE 1=1";
        $params = [];

        if (!empty($category)) {
            $sql .= " AND r.category = :category";
            $params['category'] = $category;
        }

        if (!empty($status)) {
            $sql .= " AND r.status = :status";
            $params['status'] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (r.ticket_id LIKE :search OR r.description LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->execute();
        $result = $stmt->fetch();

        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get report with media and assignment
     */
    public function getWithMediaAndAssignment(int $id): ?array
    {
        $report = $this->find($id);
        if (!$report) {
            return null;
        }

        $reportMediaModel = new ReportMedia();
        $report['media'] = $reportMediaModel->getByReportId($id);

        $assignmentModel = new Assignment();
        $report['assignment'] = $assignmentModel->getByReportId($id);

        return $report;
    }

    /**
     * Update report status (with status update logging)
     */
    public function updateStatusWithLog(int $reportId, string $newStatus, string $comment, int $updatedBy): bool
    {
        if (!array_key_exists($newStatus, self::STATUSES)) {
            throw new InvalidArgumentException("Invalid status: {$newStatus}");
        }

        $this->db->getConnection()->beginTransaction();

        try {
            // Update report status
            $this->update($reportId, ['status' => $newStatus]);

            // Log the status update
            $statusUpdateModel = new StatusUpdate();
            $statusUpdateModel->addStatusUpdate($reportId, $newStatus, $comment, $updatedBy);

            $this->db->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
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
