<div class="card" style="max-width: 550px; margin: 2rem auto;">
    <h1 class="card-title text-center">📝 Register</h1>

    <form method="POST" action="<?= BASE_URL ?>/auth/register" id="registerForm">
        <div class="form-group">
            <label for="role" class="form-label">Account Type</label>
            <select id="role" name="role" class="form-control" required onchange="toggleWorkIdField()">
                <option value="citizen" <?= ($old['role'] ?? '') === 'citizen' ? 'selected' : '' ?>>Citizen</option>
                <option value="worker" <?= ($old['role'] ?? '') === 'worker' ? 'selected' : '' ?>>Municipality Worker</option>
            </select>
        </div>

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
                minlength="3"
            >
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-control" 
                placeholder="Enter your email"
                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="phone" class="form-label">Phone Number (Optional)</label>
            <input 
                type="tel" 
                id="phone" 
                name="phone" 
                class="form-control" 
                placeholder="+1 234 567 8900"
                value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
            >
        </div>

        <div class="form-group" id="workIdGroup" style="display: none;">
            <label for="work_id" class="form-label">Work ID</label>
            <input 
                type="text" 
                id="work_id" 
                name="work_id" 
                class="form-control" 
                placeholder="Enter your Work ID"
                value="<?= htmlspecialchars($old['work_id'] ?? '') ?>"
            >
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control" 
                placeholder="Create a password (min 6 characters)"
                required
                minlength="6"
            >
        </div>

        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                class="form-control" 
                placeholder="Confirm your password"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">
            Register
        </button>
    </form>

    <p class="text-center mt-3">
        Already have an account?
        <a href="<?= BASE_URL ?>/auth/login">Login here</a>
    </p>
</div>

<script>
    function toggleWorkIdField() {
        const role = document.getElementById('role').value;
        const workIdGroup = document.getElementById('workIdGroup');
        const workIdInput = document.getElementById('work_id');
        
        if (role === 'worker') {
            workIdGroup.style.display = 'block';
            workIdInput.required = true;
        } else {
            workIdGroup.style.display = 'none';
            workIdInput.required = false;
        }
    }

    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters.');
            return false;
        }
    });

    // Initialize work ID field visibility
    toggleWorkIdField();
</script>
