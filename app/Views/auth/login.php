<div class="card" style="max-width: 450px; margin: 2rem auto;">
    <h1 class="card-title text-center">🔐 Login</h1>

    <form method="POST" action="<?= BASE_URL ?>/auth/login">
        <div class="form-group">
            <label for="cin" class="form-label">CIN (National ID)</label>
            <input 
                type="text" 
                id="cin" 
                name="cin" 
                class="form-control" 
                placeholder="Enter your CIN"
                value="<?= htmlspecialchars($old['cin'] ?? '') ?>"
                required
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control" 
                placeholder="Enter your password"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">
            Login
        </button>
    </form>

    <p class="text-center mt-3">
        Don't have an account?
        <a href="<?= BASE_URL ?>/auth/register">Register here</a>
    </p>

    <div class="card mt-3" style="background: #f8f9fa;">
        <h4 style="margin-bottom: 0.5rem;">Test Accounts</h4>
        <p style="font-size: 0.875rem; color: #666;">
            <strong>Citizen:</strong> CIN: CITIZEN001, Password: password123<br>
            <strong>Worker:</strong> CIN: WORKER001, Password: password123
        </p>
    </div>
</div>

<script>
    // Clear flash messages on page load
    if (window.history && window.history.replaceState) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
