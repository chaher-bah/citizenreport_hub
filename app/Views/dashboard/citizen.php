<div class="d-flex justify-between align-center mb-3">
    <h1>📊 My Dashboard</h1>
    <a href="<?= BASE_URL ?>/report/create" class="btn btn-primary">+ New Report</a>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card text-center">
        <h3 style="font-size: 2.5rem; color: #1a73e8;"><?= $stats['total'] ?></h3>
        <p style="color: #666;">Total Reports</p>
    </div>
    <div class="card text-center">
        <h3 style="font-size: 2.5rem; color: #ffc107;"><?= $stats['pending'] ?></h3>
        <p style="color: #666;">Pending</p>
    </div>
    <div class="card text-center">
        <h3 style="font-size: 2.5rem; color: #17a2b8;"><?= $stats['in_progress'] ?></h3>
        <p style="color: #666;">In Progress</p>
    </div>
    <div class="card text-center">
        <h3 style="font-size: 2.5rem; color: #28a745;"><?= $stats['resolved'] ?></h3>
        <p style="color: #666;">Resolved</p>
    </div>
    <?php if ($stats['unresolved'] > 0): ?>
    <div class="card text-center" style="border: 2px solid #dc3545;">
        <h3 style="font-size: 2.5rem; color: #dc3545;"><?= $stats['unresolved'] ?></h3>
        <p style="color: #666; font-weight: bold;">Unresolved</p>
    </div>
    <?php endif; ?>
</div>

<!-- Reports Table -->
<div class="card">
    <h2 class="card-title">My Reports</h2>
    
    <?php if (empty($reports)): ?>
        <p style="text-align: center; color: #666; padding: 2rem;">
            You haven't submitted any reports yet.
            <br><br>
            <a href="<?= BASE_URL ?>/report/create" class="btn btn-primary">Submit Your First Report</a>
        </p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($report['ticket_id']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($report['category_name'] ?? 'N/A') ?></td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($report['status']) ?>">
                                <?= htmlspecialchars($report['status_label']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($report['created_at'])) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/report/view?id=<?= $report['id'] ?>" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
