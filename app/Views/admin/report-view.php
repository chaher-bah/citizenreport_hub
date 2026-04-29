<div style="max-width: 900px; margin: 0 auto;">
    <div class="d-flex justify-between align-center mb-3">
        <h1>📋 Report Details (Worker View)</h1>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary">
    ← Back to Dashboard
</a>
<form method="POST" action="<?= BASE_URL ?>/admin/report/delete" style="display:inline;"
      onsubmit="return confirm('Are you sure you want to delete this report? This cannot be undone.');">
    <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
    <button type="submit" class="btn btn-danger">🗑️ Delete Report</button>
</form>
    </div>

    <!-- Report Header -->
    <div class="card">
        <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($report['ticket_id']) ?></h2>
                <p style="color: #666; margin: 0;">
                    Submitted on <?= date('F d, Y \a\t g:i A', strtotime($report['created_at'])) ?>
                </p>
            </div>
            <span class="badge badge-<?= htmlspecialchars($report['status']) ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                <?= htmlspecialchars($statuses[$report['status']] ?? $report['status']) ?>
            </span>
        </div>
    </div>

    <!-- Report Details -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="card">
            <h3 class="card-title">Report Information</h3>

            <div class="form-group">
                <label style="font-weight: 600; color: #666;">Category</label>
                <p><?= htmlspecialchars($report['category_name'] ?? 'N/A') ?></p>
                <?php if (!empty($report['category_detail'])): ?>
                    <p style="color: #666; font-style: italic; margin-top: 0.25rem;">
                        Detail: <?= htmlspecialchars($report['category_detail']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label style="font-weight: 600; color: #666;">Description</label>
                <p style="white-space: pre-wrap;"><?= htmlspecialchars($report['description']) ?></p>
            </div>

            <div class="form-group">
                <label style="font-weight: 600; color: #666;">Status</label>
                <p>
                    <span class="badge badge-<?= htmlspecialchars($report['status']) ?>">
                        <?= htmlspecialchars($statuses[$report['status']] ?? $report['status']) ?>
                    </span>
                </p>
            </div>
            <?php echo "<script>console.log(" . json_encode($report) . ");</script>"; ?>
            <div class="form-group">
                <label style="font-weight: 600; color: #666;">Citizen</label>
                <p><?= htmlspecialchars($report['user_cin']) ?></p>
                <p style="color: #666; font-size: 0.875rem;"><?= htmlspecialchars($report['user_email']) ?></p>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Location</h3>
            <div id="map" style="height: 300px; margin-bottom: 1rem;"></div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.875rem;">
                <div>
                    <strong>Latitude:</strong> <?= number_format($report['latitude'], 6) ?>
                </div>
                <div>
                    <strong>Longitude:</strong> <?= number_format($report['longitude'], 6) ?>
                </div>
            </div>
            <div style="margin-top: 1rem;">
                <a href="<?= $geoService->getGoogleMapsUrl($report['latitude'], $report['longitude']) ?>"
                   target="_blank"
                   class="btn btn-secondary"
                   style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                    🗺️ Open in Google Maps
                </a>
            </div>
        </div>
    </div>

    <!-- Assignment Info -->
    <?php if (!empty($report['assignment'])): ?>
    <div class="card">
        <h3 class="card-title">📌 Current Assignment</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div>
                <strong>Branch:</strong>
                <span class="badge badge-info">
                    <?= htmlspecialchars($report['assignment']['branch_name']) ?>
                </span>
            </div>
            <div>
                <strong>Contact:</strong>
                <?= htmlspecialchars($report['assignment']['branch_contact'] ?? 'N/A') ?>
            </div>
            <div>
                <strong>Assigned At:</strong>
                <?= date('M d, Y g:i A', strtotime($report['assignment']['assigned_at'])) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Media Gallery -->
    <?php if (!empty($report['media'])): ?>
        <div class="card">
            <h3 class="card-title">📎 Attached Media (<?= count($report['media']) ?>)</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                <?php foreach ($report['media'] as $media): ?>
                    <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                        <?php if ($media['type'] === 'photo'): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($media['file_path']) ?>"
                                 alt="Report media"
                                 style="width: 100%; height: 150px; object-fit: cover; cursor: pointer;"
                                 onclick="openLightbox(this.src)">
                        <?php else: ?>
                            <video controls style="width: 100%; height: 150px; object-fit: cover;">
                                <source src="<?= BASE_URL ?>/<?= htmlspecialchars($media['file_path']) ?>"
                                        type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>
                        <div style="padding: 0.5rem; font-size: 0.75rem; color: #666; text-align: center;">
                            <?= ucfirst($media['type']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Worker Actions -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <!-- Change Status -->
        <div class="card">
            <h3 class="card-title">🔄 Change Status</h3>
            <form method="POST" action="<?= BASE_URL ?>/admin/report/update-status">
                <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                <div class="form-group">
                    <label class="form-label">New Status</label>
                    <select name="status" class="form-control" required>
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= $key === $report['status'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Comment (optional)</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Add a comment about this status change..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Status</button>
            </form>
        </div>

        <!-- Assign Branch -->
        <div class="card">
            <h3 class="card-title">📌 Assign to Branch</h3>
            <form method="POST" action="<?= BASE_URL ?>/admin/report/assign">
                <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                <div class="form-group">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-control" required>
                        <option value="">Select a branch...</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?= $branch['id'] ?>" <?= (!empty($report['assignment']) && $report['assignment']['branch_id'] == $branch['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($branch['name']) ?>
                                <?php if (!empty($branch['contact_number'])): ?>
                                    (<?= htmlspecialchars($branch['contact_number']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <?= !empty($report['assignment']) ? 'Update Assignment' : 'Assign Report' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Status History -->
    <div class="card" style="margin-top: 1.5rem;">
        <h3 class="card-title">📜 Status History</h3>

        <?php if (empty($statusUpdates)): ?>
            <p style="color: #666; text-align: center; padding: 2rem;">
                No status updates yet.
            </p>
        <?php else: ?>
            <div style="position: relative; padding-left: 2rem;">
                <div style="position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #ddd;"></div>

                <?php foreach ($statusUpdates as $index => $update): ?>
                    <div style="position: relative; margin-bottom: 1.5rem; padding-bottom: 1.5rem; <?= $index < count($statusUpdates) - 1 ? 'border-bottom: 1px solid #eee;' : '' ?>">
                        <div style="position: absolute; left: -2rem; top: 0; width: 24px; height: 24px; background: #1a73e8; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span class="badge badge-<?= htmlspecialchars($update['status']) ?>">
                                <?= htmlspecialchars($statuses[$update['status']] ?? $update['status']) ?>
                            </span>
                            <small style="color: #666;">
                                <?= date('M d, Y g:i A', strtotime($update['created_at'])) ?>
                            </small>
                        </div>

                        <?php if (!empty($update['comment'])): ?>
                            <p style="margin: 0.5rem 0 0 0; color: #666; font-style: italic;">
                                "<?= htmlspecialchars($update['comment']) ?>"
                            </p>
                        <?php endif; ?>

                        <small style="color: #999;">
                            Updated by: <?= htmlspecialchars($update['updated_by_cin']) ?>
                            (<?= ucfirst($update['updated_by_role']) ?>)
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lightbox for images -->
<div id="lightbox" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.9); z-index: 1000;" onclick="closeLightbox()">
    <img id="lightbox-img" src="" alt="" style="max-width: 90%; max-height: 90%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <button onclick="closeLightbox()" style="position: absolute; top: 20px; right: 20px; background: white; border: none; font-size: 2rem; cursor: pointer; padding: 0.5rem 1rem; border-radius: 4px;">&times;</button>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Initialize map
    const map = L.map('map').setView([<?= $report['latitude'] ?>, <?= $report['longitude'] ?>], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    L.marker([<?= $report['latitude'] ?>, <?= $report['longitude'] ?>]).addTo(map)
        .bindPopup('Report Location')
        .openPopup();

    // Lightbox functions
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Close lightbox on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });
</script>
