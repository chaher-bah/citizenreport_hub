<div style="max-width: 800px; margin: 0 auto;">
    <h1 style="margin-bottom: 1.5rem;">📢 Broadcasts</h1>

    <?php if (empty($broadcasts)): ?>
        <div class="card" style="text-align: center; color: #666; padding: 3rem;">
            No broadcasts at this time.
        </div>
    <?php else: ?>
        <?php foreach ($broadcasts as $b): ?>
            <div class="card" style="margin-bottom: 1rem;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.5rem;">
                    <h3 style="color:#1a73e8; margin:0;"><?= htmlspecialchars($b['title']) ?></h3>
                    <?php if ($b['zone']): ?>
                        <span style="background:#e8f0fe; color:#1a73e8; padding:0.2rem 0.75rem; border-radius:50px; font-size:0.8rem;">
                            📍 <?= htmlspecialchars($b['zone']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <p style="margin: 0.75rem 0; color:#333;"><?= nl2br(htmlspecialchars($b['message'])) ?></p>
                <small style="color:#999;">
                    Posted by <?= htmlspecialchars($b['created_by_cin']) ?> 
                    on <?= date('M d, Y', strtotime($b['created_at'])) ?>
                </small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>