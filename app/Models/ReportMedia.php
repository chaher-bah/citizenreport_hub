<?php
/**
 * ReportMedia Model
 */

class ReportMedia extends Model
{
    protected string $table = 'report_media';

    /**
     * Get all media for a report
     */
    public function getByReportId(int $reportId): array
    {
        return $this->findAll(['report_id' => $reportId], 'created_at ASC');
    }

    /**
     * Add media to a report
     */
    public function addMedia(int $reportId, string $filePath, string $type): int
    {
        if (!in_array($type, ['photo', 'video'])) {
            throw new InvalidArgumentException("Invalid media type: {$type}");
        }
        
        return $this->create([
            'report_id' => $reportId,
            'file_path' => $filePath,
            'type' => $type,
        ]);
    }

    /**
     * Get media by ID
     */
    public function getMedia(int $id): ?array
    {
        return $this->find($id);
    }

    /**
     * Delete media
     */
    public function deleteMedia(int $id): int
    {
        $media = $this->find($id);
        if ($media) {
            $filePath = __DIR__ . '/../../public/' . $media['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        return $this->delete($id);
    }

    /**
     * Delete all media for a report
     */
    public function deleteByReportId(int $reportId): int
    {
        $mediaList = $this->getByReportId($reportId);
        foreach ($mediaList as $media) {
            $this->deleteMedia($media['id']);
        }
        
        return count($mediaList);
    }

    /**
     * Get photo count for a report
     */
    public function getPhotoCount(int $reportId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE report_id = :report_id AND type = 'photo'";
        
        $result = $this->db->fetchOne($sql, ['report_id' => $reportId]);
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get video count for a report
     */
    public function getVideoCount(int $reportId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE report_id = :report_id AND type = 'video'";
        
        $result = $this->db->fetchOne($sql, ['report_id' => $reportId]);
        return (int) ($result['count'] ?? 0);
    }
}
