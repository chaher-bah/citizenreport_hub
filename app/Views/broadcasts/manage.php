<div style="max-width: 900px; margin: 0 auto;">
    <div class="d-flex justify-between align-center mb-3">
        <h1>📢 Manage Broadcasts</h1>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary">← Back</a>
    </div>

    <!-- Create Form -->
    <div class="card" style="margin-bottom:1.5rem;">
        <h3 class="card-title">➕ New Broadcast</h3>
        <form method="POST" action="<?= BASE_URL ?>/admin/broadcasts/create">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" required placeholder="Broadcast title">
                </div>
                <div class="form-group">
                    <label class="form-label">Zone (optional)</label>
                    <input type="text" name="zone" class="form-control" placeholder="e.g. Downtown, Zone A">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Message *</label>
                <textarea name="message" class="form-control" rows="3" required placeholder="Broadcast message..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Schedule for later (optional)</label>
                <input type="datetime-local" name="scheduled_at" class="form-control">
                <small style="color:#666;">Leave empty to publish immediately.</small>
            </div>
            <button type="submit" class="btn btn-primary">Publish Broadcast</button>
        </form>
    </div>

    <!-- List -->
    <div class="card">
        <h3 class="card-title">📋 All Broadcasts (<?= count($broadcasts) ?>)</h3>
        <?php if (empty($broadcasts)): ?>
            <p style="text-align:center; color:#666; padding:2rem;">No broadcasts yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Zone</th>
                        <th>Scheduled</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($broadcasts as $b): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($b['title']) ?></strong>
                                <div style="font-size:0.8rem; color:#666; margin-top:0.25rem;">
                                    <?= htmlspecialchars(mb_strimwidth($b['message'], 0, 60, '...')) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($b['zone'] ?? '—') ?></td>
                            <td>
                                <?php if ($b['scheduled_at']): ?>
                                    <span style="color:#856404;">⏰ <?= date('M d, Y H:i', strtotime($b['scheduled_at'])) ?></span>
                                <?php else: ?>
                                    <span style="color:#28a745;">✅ Live</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($b['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-secondary" style="padding:0.25rem 0.5rem; font-size:0.8rem;"
                                    onclick="openEdit(<?= $b['id'] ?>, '<?= addslashes($b['title']) ?>', '<?= addslashes($b['message']) ?>', '<?= addslashes($b['zone'] ?? '') ?>', '<?= $b['scheduled_at'] ? date('Y-m-d\TH:i', strtotime($b['scheduled_at'])) : '' ?>')">
                                    Edit
                                </button>
                                <form method="POST" action="<?= BASE_URL ?>/admin/broadcasts/delete" style="display:inline;"
                                      onsubmit="return confirm('Delete this broadcast?');">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.8rem;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:white; padding:2rem; border-radius:8px; max-width:600px; width:90%;">
        <h3 style="margin-bottom:1rem;">✏️ Edit Broadcast</h3>
        <form method="POST" action="<?= BASE_URL ?>/admin/broadcasts/update">
            <input type="hidden" name="id" id="editId">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" id="editTitle" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Zone</label>
                <input type="text" name="zone" id="editZone" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Message *</label>
                <textarea name="message" id="editMessage" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Scheduled At</label>
                <input type="datetime-local" name="scheduled_at" id="editScheduled" class="form-control">
            </div>
            <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeEdit()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, title, message, zone, scheduled) {
    document.getElementById('editId').value = id;
    document.getElementById('editTitle').value = title;
    document.getElementById('editMessage').value = message;
    document.getElementById('editZone').value = zone;
    document.getElementById('editScheduled').value = scheduled;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() {
    document.getElementById('editModal').style.display = 'none';
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
</script>