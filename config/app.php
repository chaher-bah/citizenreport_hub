<?php
/**
 * Application Configuration
 */

return [
    'app_name' => 'CitizenReport Hub',
    'base_url' => 'http://localhost/citizenreport_hub',
    'upload_max_photo' => 5 * 1024 * 1024, // 5MB
    'upload_max_video' => 50 * 1024 * 1024, // 50MB
    'upload_max_files' => 2,
    'allowed_photo_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'allowed_video_types' => ['video/mp4', 'video/webm', 'video/quicktime'],
    'upload_path' => BASE_PATH . '/public/uploads/reports/',
    'session_lifetime' => 3600, // 1 hour
];
