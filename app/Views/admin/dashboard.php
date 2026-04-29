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

<!-- Charts Section -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Reports by Category (Bar Chart) -->
    <div class="card">
        <h3 class="card-title">📊 Reports by Category</h3>
        <div style="padding: 1rem 0;">
            <?php 
                $maxCatCount = 0;
                foreach ($categoryCounts as $cc) { if ((int)$cc['count'] > $maxCatCount) $maxCatCount = (int)$cc['count']; }
            ?>
            <?php foreach ($categoryCounts as $catData): ?>
                <div style="display: flex; align-items: center; margin-bottom: 0.75rem;">
                    <span style="width: 160px; font-size: 0.875rem; color: #666;">
                        <?= htmlspecialchars($catData['name']) ?>
                    </span>
                    <div style="flex: 1; background: #e9ecef; height: 24px; border-radius: 4px; overflow: hidden;">
                        <?php 
                            $width = $maxCatCount > 0 ? ($catData['count'] / $maxCatCount) * 100 : 0;
                        ?>
                        <div style="width: <?= $width ?>%; height: 100%; background: #1a73e8; transition: width 0.3s;"></div>
                    </div>
                    <span style="width: 40px; text-align: right; font-weight: bold; font-size: 0.875rem;">
                        <?= $catData['count'] ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Reports by Status (Donut-like display) -->
    <div class="card">
        <h3 class="card-title">📈 Reports by Status</h3>
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 1.5rem; padding: 1rem 0;">
            <?php 
                $statusColors = [
                    'pending' => '#ffc107',
                    'in_progress' => '#17a2b8',
                    'resolved' => '#28a745',
                    'rejected' => '#dc3545',
                ];
                $totalForChart = max(1, array_sum($stats) - $stats['total']);
            ?>
            <?php foreach (['pending', 'in_progress', 'resolved', 'rejected'] as $statusKey): ?>
                <?php 
                    $percentage = $totalForChart > 0 ? round(($stats[$statusKey] / $totalForChart) * 100) : 0;
                ?>
                <div style="text-align: center;">
                    <div style="position: relative; width: 100px; height: 100px; margin: 0 auto 0.5rem;">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <circle cx="18" cy="18" r="15.915" fill="none" stroke="#e9ecef" stroke-width="3"></circle>
                            <circle cx="18" cy="18" r="15.915" fill="none" stroke="<?= $statusColors[$statusKey] ?>" stroke-width="3"
                                    stroke-dasharray="<?= $percentage ?>, 100" stroke-linecap="round"></circle>
                        </svg>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.25rem; font-weight: bold;">
                            <?= $percentage ?>%
                        </div>
                    </div>
                    <div style="font-size: 0.875rem; color: #666;">
                        <?= htmlspecialchars($statuses[$statusKey]) ?>
                    </div>
                    <div style="font-size: 1.25rem; font-weight: bold; color: <?= $statusColors[$statusKey] ?>;">
                        <?= $stats[$statusKey] ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Filtering Panel -->
<div class="card" style="margin-bottom: 1.5rem;">
    <h3 class="card-title">🔍 Filter & Search Reports</h3>
    <form method="GET" action="<?= BASE_URL ?>/admin/dashboard" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 180px;">
            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem; display: block;">Category</label>
            <select name="category_id" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex: 1; min-width: 180px;">
            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem; display: block;">Status</label>
            <select name="status" class="form-control">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= ($filters['status'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex: 2; min-width: 250px;">
            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem; display: block;">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Search by ticket ID or description..."
                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<!-- Reports Table -->
<div class="card">
    <h3 class="card-title">📋 Reports (<?= $pagination['totalReports'] ?> total)</h3>

    <?php if (empty($reports)): ?>
        <p style="text-align: center; color: #666; padding: 2rem;">
            No reports found matching your criteria.
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
                        <td><?= htmlspecialchars($report['category_name']) ?></td>
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
                            <a href="<?= BASE_URL ?>/admin/report/view?id=<?= $report['id'] ?>" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($pagination['total'] > 1): ?>
            <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem;">
                <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                    <?php 
                        $queryParams = $_GET;
                        $queryParams['page'] = $i;
                        $queryString = http_build_query($queryParams);
                    ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard?<?= $queryString ?>"
                       class="btn <?= $i === $pagination['current'] ? 'btn-primary' : 'btn-secondary' ?>"
                       style="padding: 0.5rem 0.75rem; min-width: 40px;">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
