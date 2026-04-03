<?php
/**
 * Report Controller
 * Handles report creation, viewing, and management
 */

class ReportController extends Controller
{
    private Report $reportModel;
    private ReportMedia $reportMediaModel;
    private StatusUpdate $statusUpdateModel;
    private FileUploadService $uploadService;
    private GeoService $geoService;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new Report();
        $this->reportMediaModel = new ReportMedia();
        $this->statusUpdateModel = new StatusUpdate();
        $this->uploadService = new FileUploadService();
        $this->geoService = new GeoService();
    }

    /**
     * Show report creation page (citizens only)
     */
    public function showCreate(): void
    {
        $this->requireCitizen();

        $this->viewWithLayout('report/create', [
            'title' => 'Submit a Report',
            'categories' => Report::CATEGORIES,
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
            'old' => $_SESSION['old_input'] ?? [],
        ]);

        $_SESSION['old_input'] = [];
    }

    /**
     * Process report submission
     */
    public function create(): void
    {
        $this->requireCitizen();

        $category = $this->post('category', '');
        $categoryDetail = trim($this->post('category_detail', ''));
        $description = trim($this->post('description', ''));
        $latitude = $this->post('latitude', '');
        $longitude = $this->post('longitude', '');

        // Validate input
        $errors = $this->validateReport($category, $categoryDetail, $description, $latitude, $longitude);

        if (!empty($errors)) {
            $_SESSION['old_input'] = [
                'category' => $category,
                'category_detail' => $categoryDetail,
                'description' => $description,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect(BASE_URL . '/report/create');
            return;
        }

        // Handle file uploads
        $uploadedFiles = [];
        if (!empty($_FILES['media']['name'][0]) || !empty($_FILES['media']['name'])) {
            $uploadResult = $this->uploadService->uploadReportFiles($_FILES['media']);
            
            if (!$uploadResult['success'] && !empty($uploadResult['errors'])) {
                $_SESSION['old_input'] = [
                    'category' => $category,
                    'category_detail' => $categoryDetail,
                    'description' => $description,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
                $this->setFlash('error', implode('<br>', $uploadResult['errors']));
                $this->redirect(BASE_URL . '/report/create');
                return;
            }
            
            $uploadedFiles = $uploadResult['files'];
        }

        // Sanitize coordinates
        $coords = $this->geoService->sanitizeCoordinates($latitude, $longitude);

        // Create report with transaction
        $this->db->beginTransaction();
        
        try {
            $reportData = [
                'category' => $category,
                'category_detail' => ($category === 'others') ? 'others-' . $categoryDetail : null,
                'description' => $description,
                'latitude' => $coords['latitude'],
                'longitude' => $coords['longitude'],
            ];

            $reportId = $this->reportModel->createReport($_SESSION['user']['id'], $reportData);

            if (!$reportId) {
                throw new Exception('Failed to create report.');
            }

            // Save media files
            foreach ($uploadedFiles as $file) {
                $this->reportMediaModel->addMedia(
                    $reportId,
                    $file['file_path'],
                    $file['type']
                );
            }

            // Create initial status update
            $this->statusUpdateModel->addStatusUpdate(
                $reportId,
                'pending',
                'Report submitted by citizen.',
                $_SESSION['user']['id']
            );

            $this->db->commit();

            // Get the ticket ID for the success page
            $report = $this->reportModel->find($reportId);
            
            $this->setFlash('success', 'Report submitted successfully!');
            $this->redirect(BASE_URL . '/report/success?ticket=' . $report['ticket_id']);

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Report creation failed: " . $e->getMessage());
            $this->setFlash('error', 'Failed to submit report. Please try again.');
            $this->redirect(BASE_URL . '/report/create');
        }
    }

    /**
     * Show report success page
     */
    public function showSuccess(): void
    {
        $this->requireCitizen();

        $ticketId = $this->get('ticket', '');

        if (empty($ticketId)) {
            $this->redirect(BASE_URL . '/dashboard');
            return;
        }

        $report = $this->reportModel->findByTicketId($ticketId);

        if (!$report || $report['user_id'] !== $_SESSION['user']['id']) {
            $this->setFlash('error', 'Report not found.');
            $this->redirect(BASE_URL . '/dashboard');
            return;
        }

        $this->viewWithLayout('report/success', [
            'title' => 'Report Submitted',
            'report' => $report,
        ]);
    }

    /**
     * View a single report
     */
    public function viewReport(): void
    {
        $this->requireAuth();

        $reportId = (int) $this->get('id', 0);
        
        if ($reportId <= 0) {
            $this->setFlash('error', 'Invalid report ID.');
            $this->redirectBack();
            return;
        }

        $report = $this->reportModel->getWithMedia($reportId);

        if (!$report) {
            $this->setFlash('error', 'Report not found.');
            $this->redirectBack();
            return;
        }

        // Check authorization
        $isOwner = $report['user_id'] === $_SESSION['user']['id'];
        $isWorker = $this->isWorker();

        if (!$isOwner && !$isWorker) {
            $this->setFlash('error', 'Access denied.');
            $this->redirectBack();
            return;
        }

        // Get status updates
        $statusUpdates = $this->statusUpdateModel->getByReportId($reportId);

        $this->viewWithLayout('report/view', [
            'title' => 'Report #' . $report['ticket_id'],
            'report' => $report,
            'statusUpdates' => $statusUpdates,
            'statuses' => Report::STATUSES,
            'categories' => Report::CATEGORIES,
            'geoService' => $this->geoService,
        ]);
    }

    /**
     * View report by ticket ID
     */
    public function viewByTicket(): void
    {
        $this->requireAuth();

        $ticketId = $this->get('ticket', '');
        
        if (empty($ticketId)) {
            $this->setFlash('error', 'Invalid ticket ID.');
            $this->redirectBack();
            return;
        }

        $report = $this->reportModel->getByTicketIdWithMedia($ticketId);

        if (!$report) {
            $this->setFlash('error', 'Report not found.');
            $this->redirectBack();
            return;
        }

        // Check authorization
        $isOwner = $report['user_id'] === $_SESSION['user']['id'];
        $isWorker = $this->isWorker();

        if (!$isOwner && !$isWorker) {
            $this->setFlash('error', 'Access denied.');
            $this->redirectBack();
            return;
        }

        // Get status updates
        $statusUpdates = $this->statusUpdateModel->getByReportId($report['id']);

        $this->viewWithLayout('report/view', [
            'title' => 'Report #' . $report['ticket_id'],
            'report' => $report,
            'statusUpdates' => $statusUpdates,
            'statuses' => Report::STATUSES,
            'categories' => Report::CATEGORIES,
            'geoService' => $this->geoService,
        ]);
    }

    /**
     * Validate report input
     */
    private function validateReport(
        string $category,
        string $categoryDetail,
        string $description,
        $latitude,
        $longitude
    ): array {
        $errors = [];

        // Category validation
        if (empty($category)) {
            $errors[] = 'Please select a category.';
        } elseif (!array_key_exists($category, Report::CATEGORIES)) {
            $errors[] = 'Invalid category selected.';
        } elseif ($category === 'others' && empty($categoryDetail)) {
            $errors[] = 'Please specify the issue type when selecting Others.';
        }

        // Description validation
        if (empty($description)) {
            $errors[] = 'Description is required.';
        } elseif (strlen($description) < 10) {
            $errors[] = 'Description must be at least 10 characters.';
        } elseif (strlen($description) > 2000) {
            $errors[] = 'Description must not exceed 2000 characters.';
        }

        // Coordinates validation
        if ($latitude === '' || $longitude === '') {
            $errors[] = 'Please select a location on the map.';
        } elseif (!$this->geoService->validateCoordinates($latitude, $longitude)) {
            $errors[] = 'Invalid coordinates.';
        }

        return $errors;
    }
}
