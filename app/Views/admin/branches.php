<div style="max-width: 800px; margin: 0 auto;">
    <div class="d-flex justify-between align-center mb-3">
        <h1>🏢 Manage Branches</h1>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <!-- Add New Branch -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h3 class="card-title">➕ Add New Branch</h3>
        <form method="POST" action="<?= BASE_URL ?>/admin/branches/create" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 2; min-width: 200px;">
                <label class="form-label">Branch Name *</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g., Police Department">
            </div>
            <div style="flex: 2; min-width: 200px;">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" placeholder="e.g., +1234567890">
            </div>
            <button type="submit" class="btn btn-primary">Add Branch</button>
        </form>
    </div>

    <!-- Branches List -->
    <div class="card">
        <h3 class="card-title">📋 Existing Branches (<?= count($branches) ?>)</h3>

        <?php if (empty($branches)): ?>
            <p style="text-align: center; color: #666; padding: 2rem;">No branches found.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Number</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($branches as $branch): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($branch['name']) ?></strong></td>
                            <td><?= htmlspecialchars($branch['contact_number'] ?? '—') ?></td>
                            <td><?= date('M d, Y', strtotime($branch['created_at'])) ?></td>
                            <td>
                                <button type="button" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                        onclick="openEditModal(<?= $branch['id'] ?>, '<?= addslashes($branch['name']) ?>', '<?= addslashes($branch['contact_number'] ?? '') ?>')">
                                    Edit
                                </button>
                                <form method="POST" action="<?= BASE_URL ?>/admin/branches/delete" style="display: inline;"
                                      onsubmit="return confirm('Are you sure? This will fail if the branch is in use.');">
                                    <input type="hidden" name="id" value="<?= $branch['id'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        Delete
                                    </button>
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
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
        <h3 style="margin-bottom: 1rem;">✏️ Edit Branch</h3>
        <form method="POST" action="<?= BASE_URL ?>/admin/branches/update">
            <input type="hidden" name="id" id="editId">
            <div class="form-group">
                <label class="form-label">Branch Name *</label>
                <input type="text" name="name" id="editName" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" id="editContact" class="form-control">
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name, contact) {
        document.getElementById('editId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editContact').value = contact;
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>
