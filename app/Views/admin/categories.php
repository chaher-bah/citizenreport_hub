<div style="max-width: 1000px; margin: 0 auto;">
    <div class="d-flex justify-between align-center mb-3">
        <h1>📂 Manage Categories</h1>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <!-- Add New Category -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h3 class="card-title">➕ Add New Category</h3>
        <form method="POST" action="<?= BASE_URL ?>/admin/categories/create" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 2; min-width: 200px;">
                <label class="form-label">Category Name *</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g., Water Leak">
            </div>
            <div style="flex: 3; min-width: 250px;">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" placeholder="Brief description of this category">
            </div>
            <div style="flex: 2; min-width: 200px;">
                <label class="form-label">Default Branch</label>
                <select name="default_branch_id" class="form-control">
                    <option value="">None (manual assignment)</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?= $branch['id'] ?>">
                            <?= htmlspecialchars($branch['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>
    </div>

    <!-- Categories List -->
    <div class="card">
        <h3 class="card-title">📋 Existing Categories (<?= count($categories) ?>)</h3>

        <?php if (empty($categories)): ?>
            <p style="text-align: center; color: #666; padding: 2rem;">No categories found.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Default Branch</th>
                        <th>Branch Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                            <td><?= htmlspecialchars($cat['description'] ?? '—') ?></td>
                            <td>
                                <?php if (!empty($cat['default_branch_name'])): ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($cat['default_branch_name']) ?></span>
                                <?php else: ?>
                                    <span style="color: #999;">None</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($cat['branch_contact'] ?? '—') ?></td>
                            <td>
                                <!-- Edit inline -->
                                <button type="button" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                        onclick="openEditModal(<?= $cat['id'] ?>, '<?= addslashes($cat['name']) ?>', '<?= addslashes($cat['description'] ?? '') ?>', <?= $cat['default_branch_id'] ?? 'null' ?>)">
                                    Edit
                                </button>
                                <!-- Delete -->
                                <form method="POST" action="<?= BASE_URL ?>/admin/categories/delete" style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to delete this category?');">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 600px; width: 90%;">
        <h3 style="margin-bottom: 1rem;">✏️ Edit Category</h3>
        <form method="POST" action="<?= BASE_URL ?>/admin/categories/update">
            <input type="hidden" name="id" id="editId">
            <div class="form-group">
                <label class="form-label">Category Name *</label>
                <input type="text" name="name" id="editName" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <input type="text" name="description" id="editDescription" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Default Branch</label>
                <select name="default_branch_id" id="editBranch" class="form-control">
                    <option value="">None (manual assignment)</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?= $branch['id'] ?>"><?= htmlspecialchars($branch['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name, description, branchId) {
        document.getElementById('editId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editDescription').value = description;
        document.getElementById('editBranch').value = branchId || '';
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Close modal on outside click
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>
