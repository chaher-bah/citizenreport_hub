<div class="d-flex justify-between align-center mb-3">
    <h1>🏛️ Admin Dashboard</h1>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
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
    <div class="card text-center">
        <h3 style="font-size: 2.5rem; color: #dc3545;"><?= $stats['rejected'] ?></h3>
        <p style="color: #666;">Rejected</p>
    </div>
</div>

<!-- Reports Table -->
<div class="card">
    <h2 class="card-title">All Reports</h2>
    
    <?php if (empty($reports)): ?>
        <p style="text-align: center; color: #666; padding: 2rem;">
            No reports have been submitted yet.
        </p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Category</th>
                    <th>Citizen</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($report['ticket_id']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($report['category_label']) ?></td>
                        <td>
                            <?= htmlspecialchars($report['user_cin']) ?><br>
                            <small style="color: #666;"><?= htmlspecialchars($report['user_email']) ?></small>
                        </td>
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
