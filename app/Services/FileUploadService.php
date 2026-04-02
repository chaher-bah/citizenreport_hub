<?php
/**
 * File Upload Service
 * Handles file uploads with validation
 */

class FileUploadService
{
    private array $config;
    private array $errors = [];

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/app.php';
    }

    /**
     * Upload files for a report
     * @param array $files $_FILES array
     * @return array ['success' => bool, 'files' => array, 'errors' => array]
     */
    public function uploadReportFiles(array $files): array
    {
        $uploadedFiles = [];
        $this->errors = [];

        // Ensure upload directory exists
        $uploadDir = $this->config['upload_path'];
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Handle files array (could be single or multiple)
        $fileArray = $this->normalizeFilesArray($files);

        // Check max files limit
        if (count($fileArray) > $this->config['upload_max_files']) {
            $this->errors[] = "Maximum {$this->config['upload_max_files']} files allowed.";
            return [
                'success' => false,
                'files' => [],
                'errors' => $this->errors
            ];
        }

        foreach ($fileArray as $file) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->errors[] = "File upload error: " . $this->getUploadErrorMessage($file['error']);
                continue;
            }

            // Validate file
            $validation = $this->validateFile($file);
            if ($validation['valid'] === false) {
                $this->errors[] = $validation['message'];
                continue;
            }

            // Generate unique filename and move file
            $result = $this->moveFile($file);
            if ($result['success']) {
                $uploadedFiles[] = [
                    'file_path' => $result['file_path'],
                    'type' => $result['type'],
                    'original_name' => $file['name']
                ];
            } else {
                $this->errors[] = $result['message'];
            }
        }

        return [
            'success' => empty($this->errors),
            'files' => $uploadedFiles,
            'errors' => $this->errors
        ];
    }

    /**
     * Normalize $_FILES array to handle multiple files
     */
    private function normalizeFilesArray(array $files): array
    {
        // If it's a single file input
        if (isset($files['name']) && is_string($files['name'])) {
            return [$files];
        }

        // Handle multiple files
        $normalized = [];
        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $key => $name) {
                $normalized[] = [
                    'name' => $files['name'][$key] ?? null,
                    'type' => $files['type'][$key] ?? null,
                    'tmp_name' => $files['tmp_name'][$key] ?? null,
                    'error' => $files['error'][$key] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $files['size'][$key] ?? 0
                ];
            }
        }

        return $normalized;
    }

    /**
     * Validate a single file
     */
    private function validateFile(array $file): array
    {
        // Check file size
        $maxSize = $this->getMaxSizeForType($file['type']);
        if ($file['size'] > $maxSize) {
            return [
                'valid' => false,
                'message' => "File size exceeds maximum allowed (" . $this->formatBytes($maxSize) . ")"
            ];
        }

        // Check file type
        $allowedTypes = $this->getAllowedTypesForFile($file);
        if (!in_array($file['type'], $allowedTypes)) {
            return [
                'valid' => false,
                'message' => "Invalid file type. Allowed: " . implode(', ', $allowedTypes)
            ];
        }

        // Check if it's actually an image/video using mime_content_type
        $actualMimeType = mime_content_type($file['tmp_name']);
        if ($actualMimeType !== $file['type']) {
            // Re-validate with actual mime type
            if (!in_array($actualMimeType, array_merge(
                $this->config['allowed_photo_types'],
                $this->config['allowed_video_types']
            ))) {
                return [
                    'valid' => false,
                    'message' => "Invalid file content."
                ];
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Move uploaded file to destination
     */
    private function moveFile(array $file): array
    {
        $type = $this->getFileType($file['type']);
        $extension = $this->getExtensionForType($file['type']);
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $destination = $this->config['upload_path'] . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => false,
                'message' => "Failed to save file."
            ];
        }

        return [
            'success' => true,
            'file_path' => 'uploads/reports/' . $filename,
            'type' => $type
        ];
    }

    /**
     * Get file type (photo/video) from mime type
     */
    private function getFileType(string $mimeType): string
    {
        if (in_array($mimeType, $this->config['allowed_photo_types'])) {
            return 'photo';
        }
        if (in_array($mimeType, $this->config['allowed_video_types'])) {
            return 'video';
        }
        return 'photo'; // Default
    }

    /**
     * Get file extension from mime type
     */
    private function getExtensionForType(string $mimeType): string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/quicktime' => 'mov',
        ];
        
        return $extensions[$mimeType] ?? 'bin';
    }

    /**
     * Get max file size for a given mime type
     */
    private function getMaxSizeForType(string $mimeType): int
    {
        if (in_array($mimeType, $this->config['allowed_photo_types'])) {
            return $this->config['upload_max_photo'];
        }
        if (in_array($mimeType, $this->config['allowed_video_types'])) {
            return $this->config['upload_max_video'];
        }
        return $this->config['upload_max_photo'];
    }

    /**
     * Get allowed types for a file
     */
    private function getAllowedTypesForFile(array $file): array
    {
        if (in_array($file['type'], $this->config['allowed_photo_types'])) {
            return $this->config['allowed_photo_types'];
        }
        if (in_array($file['type'], $this->config['allowed_video_types'])) {
            return $this->config['allowed_video_types'];
        }
        return array_merge(
            $this->config['allowed_photo_types'],
            $this->config['allowed_video_types']
        );
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Delete a file
     */
    public function deleteFile(string $filePath): bool
    {
        $fullPath = __DIR__ . '/../../public/' . $filePath;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}
