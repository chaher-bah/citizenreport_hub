<?php
/**
 * Dashboard Controller
 * Handles citizen and worker dashboards
 */

class DashboardController extends Controller
{
    private Report $reportModel;
    private StatusUpdate $statusUpdateModel;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new Report();
        $this->statusUpdateModel = new StatusUpdate();
    }

    /**
     * Citizen dashboard
     */
    public function citizenDashboard(): void
    {
        $this->requireCitizen();

        $userId = $_SESSION['user']['id'];
        $reports = $this->reportModel->getByUserId($userId);

        // Format reports for display
        foreach ($reports as &$report) {
            $report['category_label'] = Report::CATEGORIES[$report['category']] ?? $report['category'];
            $report['status_label'] = Report::STATUSES[$report['status']] ?? $report['status'];
        }

        $this->viewWithLayout('dashboard/citizen', [
            'title' => 'My Dashboard',
            'reports' => $reports,
            'stats' => $this->getCitizenStats($userId),
        ]);
    }

    /**
     * Worker dashboard (admin)
     */
    public function workerDashboard(): void
    {
        $this->requireWorker();

        $reports = $this->reportModel->getAllWithUserInfo();
        $statusCounts = $this->reportModel->getCountByStatus();

        // Format status counts
        $stats = [
            'pending' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'rejected' => 0,
            'total' => count($reports),
        ];

        foreach ($statusCounts as $count) {
            $stats[$count['status']] = (int) $count['count'];
        }

        // Format reports for display
        foreach ($reports as &$report) {
            $report['category_label'] = Report::CATEGORIES[$report['category']] ?? $report['category'];
            $report['status_label'] = Report::STATUSES[$report['status']] ?? $report['status'];
        }

        $this->viewWithLayout('dashboard/worker', [
            'title' => 'Admin Dashboard',
            'reports' => $reports,
            'stats' => $stats,
            'statuses' => Report::STATUSES,
            'categories' => Report::CATEGORIES,
        ]);
    }

    /**
     * Get citizen statistics
     */
    private function getCitizenStats(int $userId): array
    {
        $reports = $this->reportModel->getByUserId($userId);
        
        $stats = [
            'total' => count($reports),
            'pending' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'rejected' => 0,
        ];

        foreach ($reports as $report) {
            if (isset($stats[$report['status']])) {
                $stats[$report['status']]++;
            }
        }

        return $stats;
    }
}
