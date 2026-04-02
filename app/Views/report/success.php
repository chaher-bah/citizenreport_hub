<div class="card" style="max-width: 600px; margin: 2rem auto; text-align: center;">
    <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
    
    <h1 class="card-title">Report Submitted Successfully!</h1>
    
    <p style="color: #666; margin-bottom: 1.5rem;">
        Your report has been received and will be reviewed by our team.
    </p>
    
    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <p style="color: #666; margin-bottom: 0.5rem;">Your Ticket ID:</p>
        <p style="font-size: 1.5rem; font-weight: bold; color: #1a73e8; margin: 0;">
            <?= htmlspecialchars($report['ticket_id']) ?>
        </p>
    </div>
    
    <div style="text-align: left; background: #fff3cd; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
        <strong>📌 What happens next?</strong>
        <ul style="margin: 0.5rem 0 0 1.5rem; color: #666;">
            <li>Your report will be reviewed by our team.</li>
            <li>You'll receive updates on the status of your report.</li>
            <li>You can track your report anytime from your dashboard.</li>
        </ul>
    </div>
    
    <div style="display: flex; gap: 1rem; justify-content: center;">
        <a href="<?= BASE_URL ?>/report/view?ticket=<?= htmlspecialchars($report['ticket_id']) ?>" class="btn btn-primary">
            View Report
        </a>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">
            Go to Dashboard
        </a>
    </div>
    
    <p style="margin-top: 1.5rem; color: #666; font-size: 0.875rem;">
        Please save your ticket ID for future reference.
    </p>
</div>
