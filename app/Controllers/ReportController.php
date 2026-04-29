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
    private Assignment $assignmentModel;
    private Category $categoryModel;
    private FileUploadService $uploadService;
    private GeoService $geoService;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new Report();
        $this->reportMediaModel = new ReportMedia();
        $this->statusUpdateModel = new StatusUpdate();
        $this->assignmentModel = new Assignment();
        $this->categoryModel = new Category();
        $this->uploadService = new FileUploadService();
        $this->geoService = new GeoService();
    }

    /**
     * Show report creation page (citizens only)
     */
    public function showCreate(): void
    {
        $this->requireCitizen();

        $categories = $this->categoryModel->getAllWithBranch();

        $this->viewWithLayout('report/create', [
            'title' => 'Submit a Report',
            'categories' => $categories,
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

        $categoryId = (int) $this->post('category_id', 0);
        $categoryDetail = trim($this->post('category_detail', ''));
        $description = trim($this->post('description', ''));
        $latitude = $this->post('latitude', '');
        $longitude = $this->post('longitude', '');

        // Validate input
        $errors = $this->validateReport($categoryId, $categoryDetail, $description, $latitude, $longitude);

        if (!empty($errors)) {
            $_SESSION['old_input'] = [
                'category_id' => $categoryId,
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
                    'category_id' => $categoryId,
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
                'category_id' => $categoryId,
                'category_detail' => ($categoryDetail !== '') ? 'others-' . $categoryDetail : null,
                'description' => $description,
                'latitude' => $coords['latitude'],
                'longitude' => $coords['longitude'],
            ];

            $reportId = $this->reportModel->createReport($_SESSION['user']['id'], $reportData);

            if (!$reportId) {
                throw new Exception('Failed to create report.');
            }

            // Auto-assign to category's default branch
            $category = $this->categoryModel->find($categoryId);
            if (!empty($category['default_branch_id'])) {
                $this->assignmentModel->assignReport($reportId, (int) $category['default_branch_id']);
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

        $report = $this->reportModel->getWithMediaAndAssignment($reportId);

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
            'geoService' => $this->geoService,
            'isWorker' => $isWorker,
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
            'geoService' => $this->geoService,
            'isWorker' => $isWorker,
        ]);
    }

    /**
     * Validate report input
     */
    private function validateReport(
        int $categoryId,
        string $categoryDetail,
        string $description,
        $latitude,
        $longitude
    ): array {
        $errors = [];

        // Category validation
        if ($categoryId <= 0) {
            $errors[] = 'Please select a category.';
        } else {
            $category = $this->categoryModel->find($categoryId);
            if (!$category) {
                $errors[] = 'Invalid category selected.';
            } elseif ($category['name'] === 'Others' && empty($categoryDetail)) {
                $errors[] = 'Please specify the issue type when selecting Others.';
            }
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
