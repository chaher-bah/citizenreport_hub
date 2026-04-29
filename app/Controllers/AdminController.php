<?php
/**
 * Admin Controller
 * Handles worker/admin dashboard, report management, status updates, assignments,
 * and category/branch CRUD operations
 */

class AdminController extends Controller
{
    private Report $reportModel;
    private StatusUpdate $statusUpdateModel;
    private Assignment $assignmentModel;
    private Category $categoryModel;
    private Branch $branchModel;
    private GeoService $geoService;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new Report();
        $this->statusUpdateModel = new StatusUpdate();
        $this->assignmentModel = new Assignment();
        $this->categoryModel = new Category();
        $this->branchModel = new Branch();
        $this->geoService = new GeoService();
    }

    /**
     * Worker dashboard - shows stats, charts, and recent reports
     */
    public function dashboard(): void
    {
        $this->requireWorker();

        // Get filter params
        $page = max(1, (int) $this->get('page', 1));
        $perPage = 10;
        $categoryId = (int) $this->get('category_id', 0);
        $status = $this->get('status', '');
        $search = trim($this->get('search', ''));

        // Get paginated reports
        $reports = $this->reportModel->getPaginated($page, $perPage, $categoryId, $status, $search);
        $totalReports = $this->reportModel->getCountWithFilters($categoryId, $status, $search);
        $totalPages = ceil($totalReports / $perPage);

        // Format reports
        foreach ($reports as &$report) {
            $report['status_label'] = Report::STATUSES[$report['status']] ?? $report['status'];
        }

        // Get stats
        $statusCounts = $this->reportModel->getCountByStatus();
        $categoryCounts = $this->reportModel->getCountByCategory();
        $totalAllReports = $this->reportModel->count();

        // Format status counts
        $stats = [
            'pending' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'rejected' => 0,
            'total' => $totalAllReports,
        ];
        foreach ($statusCounts as $count) {
            $stats[$count['status']] = (int) $count['count'];
        }

        // Get all categories for filter dropdown
        $categories = $this->categoryModel->getAllOrdered();

        $this->viewWithLayout('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'reports' => $reports,
            'stats' => $stats,
            'categoryCounts' => $categoryCounts,
            'statuses' => Report::STATUSES,
            'categories' => $categories,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalReports' => $totalReports,
            ],
            'filters' => [
                'category_id' => $categoryId,
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    /**
     * View a single report (worker version) with status change and assignment
     */
    public function viewReport(): void
    {
        $this->requireWorker();

        $reportId = (int) $this->get('id', 0);

        if ($reportId <= 0) {
            $this->setFlash('error', 'Invalid report ID.');
            $this->redirect(BASE_URL . '/admin/dashboard');
            return;
        }

        $report = $this->reportModel->getWithMediaAndAssignment($reportId);

        if (!$report) {
            $this->setFlash('error', 'Report not found.');
            $this->redirect(BASE_URL . '/admin/dashboard');
            return;
        }

        // Get status updates
        $statusUpdates = $this->statusUpdateModel->getByReportId($reportId);

        // Get all branches for assignment dropdown
        $branches = $this->branchModel->getAllOrdered();

        $this->viewWithLayout('admin/report-view', [
            'title' => 'Report #' . $report['ticket_id'],
            'report' => $report,
            'statusUpdates' => $statusUpdates,
            'statuses' => Report::STATUSES,
            'branches' => $branches,
            'geoService' => $this->geoService,
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ]);
    }

    /**
     * Update report status
     */
    public function updateStatus(): void
    {
        $this->requireWorker();

        $reportId = (int) $this->post('report_id', 0);
        $newStatus = $this->post('status', '');
        $comment = trim($this->post('comment', ''));

        // Validate
        if ($reportId <= 0) {
            $this->setFlash('error', 'Invalid report ID.');
            $this->redirectBack();
            return;
        }

        if (!array_key_exists($newStatus, Report::STATUSES)) {
            $this->setFlash('error', 'Invalid status.');
            $this->redirectBack();
            return;
        }

        $report = $this->reportModel->find($reportId);
        if (!$report) {
            $this->setFlash('error', 'Report not found.');
            $this->redirectBack();
            return;
        }

        try {
            $this->reportModel->updateStatusWithLog($reportId, $newStatus, $comment, $_SESSION['user']['id']);
            $this->setFlash('success', "Status updated to: " . Report::STATUSES[$newStatus]);
        } catch (Exception $e) {
            error_log("Status update failed: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update status. Please try again.');
        }

        $this->redirect(BASE_URL . '/admin/report/view?id=' . $reportId);
    }

    /**
     * Assign report to a branch
     */
    public function assignBranch(): void
    {
        $this->requireWorker();

        $reportId = (int) $this->post('report_id', 0);
        $branchId = (int) $this->post('branch_id', 0);

        // Validate
        if ($reportId <= 0) {
            $this->setFlash('error', 'Invalid report ID.');
            $this->redirectBack();
            return;
        }

        if ($branchId <= 0) {
            $this->setFlash('error', 'Invalid branch.');
            $this->redirectBack();
            return;
        }

        $report = $this->reportModel->find($reportId);
        if (!$report) {
            $this->setFlash('error', 'Report not found.');
            $this->redirectBack();
            return;
        }

        $branch = $this->branchModel->find($branchId);
        if (!$branch) {
            $this->setFlash('error', 'Branch not found.');
            $this->redirectBack();
            return;
        }

        try {
            $this->assignmentModel->assignReport($reportId, $branchId);
            $this->setFlash('success', "Report assigned to: " . $branch['name']);
        } catch (Exception $e) {
            error_log("Assignment failed: " . $e->getMessage());
            $this->setFlash('error', 'Failed to assign report. Please try again.');
        }

        $this->redirect(BASE_URL . '/admin/report/view?id=' . $reportId);
    }

    // =============================================
    // Category Management
    // =============================================

    /**
     * List all categories
     */
    public function manageCategories(): void
    {
        $this->requireWorker();

        $categories = $this->categoryModel->getAllWithBranch();
        $branches = $this->branchModel->getAllOrdered();

        $this->viewWithLayout('admin/categories', [
            'title' => 'Manage Categories',
            'categories' => $categories,
            'branches' => $branches,
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ]);
    }

    /**
     * Create a new category
     */
    public function createCategory(): void
    {
        $this->requireWorker();

        $name = trim($this->post('name', ''));
        $description = trim($this->post('description', ''));
        $defaultBranchId = (int) $this->post('default_branch_id', 0);

        // Validate
        if (empty($name)) {
            $this->setFlash('error', 'Category name is required.');
            $this->redirect(BASE_URL . '/admin/categories');
            return;
        }

        if ($this->categoryModel->nameExists($name)) {
            $this->setFlash('error', 'A category with this name already exists.');
            $this->redirect(BASE_URL . '/admin/categories');
            return;
        }

        try {
            $this->categoryModel->createCategory($name, $description, $defaultBranchId > 0 ? $defaultBranchId : null);
            $this->setFlash('success', "Category '{$name}' created successfully.");
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to create category.');
        }

        $this->redirect(BASE_URL . '/admin/categories');
    }

    /**
     * Update a category
     */
    public function updateCategory(): void
    {
        $this->requireWorker();

        $id = (int) $this->post('id', 0);
        $name = trim($this->post('name', ''));
        $description = trim($this->post('description', ''));
        $defaultBranchId = (int) $this->post('default_branch_id', 0);

        if ($id <= 0 || empty($name)) {
            $this->setFlash('error', 'Invalid input.');
            $this->redirect(BASE_URL . '/admin/categories');
            return;
        }

        if ($this->categoryModel->nameExists($name, $id)) {
            $this->setFlash('error', 'A category with this name already exists.');
            $this->redirect(BASE_URL . '/admin/categories');
            return;
        }

        try {
            $this->categoryModel->updateCategory($id, $name, $description, $defaultBranchId > 0 ? $defaultBranchId : null);
            $this->setFlash('success', "Category '{$name}' updated successfully.");
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to update category.');
        }

        $this->redirect(BASE_URL . '/admin/categories');
    }

    /**
     * Delete a category
     */
    public function deleteCategory(): void
    {
        $this->requireWorker();

        $id = (int) $this->post('id', 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Invalid category ID.');
            $this->redirect(BASE_URL . '/admin/categories');
            return;
        }

        if ($this->categoryModel->isInUse($id)) {
            $this->setFlash('error', 'Cannot delete category: it is currently used by existing reports.');
            $this->redirect(BASE_URL . '/admin/categories');
            return;
        }

        try {
            $category = $this->categoryModel->find($id);
            $this->categoryModel->delete($id);
            $this->setFlash('success', "Category '{$category['name']}' deleted successfully.");
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to delete category.');
        }

        $this->redirect(BASE_URL . '/admin/categories');
    }

    // =============================================
    // Branch Management
    // =============================================

    /**
     * List all branches
     */
    public function manageBranches(): void
    {
        $this->requireWorker();

        $branches = $this->branchModel->getAllOrdered();

        $this->viewWithLayout('admin/branches', [
            'title' => 'Manage Branches',
            'branches' => $branches,
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ]);
    }

    /**
     * Create a new branch
     */
    public function createBranch(): void
    {
        $this->requireWorker();

        $name = trim($this->post('name', ''));
        $contactNumber = trim($this->post('contact_number', ''));

        if (empty($name)) {
            $this->setFlash('error', 'Branch name is required.');
            $this->redirect(BASE_URL . '/admin/branches');
            return;
        }

        if ($this->branchModel->nameExists($name)) {
            $this->setFlash('error', 'A branch with this name already exists.');
            $this->redirect(BASE_URL . '/admin/branches');
            return;
        }

        try {
            $this->branchModel->createBranch($name, $contactNumber);
            $this->setFlash('success', "Branch '{$name}' created successfully.");
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to create branch.');
        }

        $this->redirect(BASE_URL . '/admin/branches');
    }

    /**
     * Update a branch
     */
    public function updateBranch(): void
    {
        $this->requireWorker();

        $id = (int) $this->post('id', 0);
        $name = trim($this->post('name', ''));
        $contactNumber = trim($this->post('contact_number', ''));

        if ($id <= 0 || empty($name)) {
            $this->setFlash('error', 'Invalid input.');
            $this->redirect(BASE_URL . '/admin/branches');
            return;
        }

        if ($this->branchModel->nameExists($name, $id)) {
            $this->setFlash('error', 'A branch with this name already exists.');
            $this->redirect(BASE_URL . '/admin/branches');
            return;
        }

        try {
            $this->branchModel->updateBranch($id, $name, $contactNumber);
            $this->setFlash('success', "Branch '{$name}' updated successfully.");
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to update branch.');
        }

        $this->redirect(BASE_URL . '/admin/branches');
    }

    /**
     * Delete a branch
     */
    public function deleteBranch(): void
    {
        $this->requireWorker();

        $id = (int) $this->post('id', 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Invalid branch ID.');
            $this->redirect(BASE_URL . '/admin/branches');
            return;
        }

        if ($this->branchModel->isInUse($id)) {
            $this->setFlash('error', 'Cannot delete branch: it is currently used by categories or assignments.');
            $this->redirect(BASE_URL . '/admin/branches');
            return;
        }

        try {
            $branch = $this->branchModel->find($id);
            $this->branchModel->delete($id);
            $this->setFlash('success', "Branch '{$branch['name']}' deleted successfully.");
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to delete branch.');
        }

        $this->redirect(BASE_URL . '/admin/branches');
    }
    public function deleteReport(): void
{
    $this->requireWorker();

    $reportId = (int) $this->post('report_id', 0);

    if ($reportId <= 0) {
        $this->setFlash('error', 'Invalid report ID.');
        $this->redirect(BASE_URL . '/admin/dashboard');
        return;
    }

    try {
        $this->reportModel->delete($reportId);
        $this->setFlash('success', 'Report deleted successfully.');
    } catch (Exception $e) {
        $this->setFlash('error', 'Failed to delete report.');
    }

    $this->redirect(BASE_URL . '/admin/dashboard');
}
}
