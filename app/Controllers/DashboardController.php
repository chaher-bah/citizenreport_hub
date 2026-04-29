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
            $report['status_label'] = Report::STATUSES[$report['status']] ?? $report['status'];
        }

        $this->viewWithLayout('dashboard/citizen', [
            'title' => 'My Dashboard',
            'reports' => $reports,
            'stats' => $this->getCitizenStats($userId),
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
            'unresolved' => 0, // pending + in_progress
        ];

        foreach ($reports as $report) {
            if (isset($stats[$report['status']])) {
                $stats[$report['status']]++;
            }
            // Count unresolved
            if (in_array($report['status'], ['pending', 'in_progress'])) {
                $stats['unresolved']++;
            }
        }

        return $stats;
    }
}
