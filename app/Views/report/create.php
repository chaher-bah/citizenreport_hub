<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h1 class="card-title">📝 Submit a New Report</h1>
    <p style="color: #666; margin-bottom: 1.5rem;">
        Report an issue in your community. Please provide accurate information and location.
    </p>

    <form method="POST" action="<?= BASE_URL ?>/report/create" enctype="multipart/form-data" id="reportForm">
        <div class="form-group">
            <label for="category" class="form-label">Category *</label>
            <select id="category" name="category_id" class="form-control" required>
                <option value="">Select a category...</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                            <?= ($old['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                        <?php if (!empty($cat['default_branch_name'])): ?>
                            (Assigned to: <?= htmlspecialchars($cat['default_branch_name']) ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" id="categoryDetailGroup" style="display: none;">
            <label for="category_detail" class="form-label">Please specify the issue *</label>
            <input
                type="text"
                id="category_detail"
                name="category_detail"
                class="form-control"
                placeholder="Describe the issue type..."
                value="<?= htmlspecialchars($old['category_detail'] ?? '') ?>"
            >
            <small style="color: #666;">Please provide a brief description of the issue.</small>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description *</label>
            <textarea 
                id="description" 
                name="description" 
                class="form-control" 
                placeholder="Please describe the issue in detail..."
                required
                minlength="10"
                maxlength="2000"
            ><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            <small style="color: #666;">Minimum 10 characters, maximum 2000 characters.</small>
        </div>

        <div class="form-group">
            <label for="location" class="form-label">Location *</label>
            <div id="map" style="margin-bottom: 0.5rem;"></div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label for="latitude" style="font-size: 0.875rem; color: #666;">Latitude</label>
                    <input 
                        type="text" 
                        id="latitude" 
                        name="latitude" 
                        class="form-control" 
                        readonly
                        required
                    >
                </div>
                <div>
                    <label for="longitude" style="font-size: 0.875rem; color: #666;">Longitude</label>
                    <input 
                        type="text" 
                        id="longitude" 
                        name="longitude" 
                        class="form-control" 
                        readonly
                        required
                    >
                </div>
            </div>
            <button type="button" class="btn btn-secondary mt-2" onclick="getCurrentLocation()">
                📍 Use My Current Location
            </button>
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                Click on the map to select the exact location of the issue.
            </small>
        </div>

        <div class="form-group">
            <label for="media" class="form-label">Photos/Videos (Optional)</label>
            <input 
                type="file" 
                id="media" 
                name="media[]" 
                class="form-control" 
                accept="image/*,video/*"
                multiple
            >
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                Maximum 2 files. Photos: max 5MB each. Videos: max 50MB each.
                <br>Allowed formats: JPG, PNG, GIF, WebP, MP4, WebM, MOV.
            </small>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                Submit Report
            </button>
            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Initialize map
    let map;
    let marker;
    
    // Default location (can be changed to your city's coordinates)
    const defaultLat = 35.640222;
    const defaultLng = 10.888000;
    
    function initMap() {
        map = L.map('map').setView([defaultLat, defaultLng], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add click handler
        map.on('click', function(e) {
            setMarker(e.latlng.lat, e.latlng.lng);
        });
        
        // Try to get user's location on load
        getCurrentLocation();
    }
    
    function setMarker(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
        
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }
        
        map.setView([lat, lng], 16);
    }
    
    function getCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                setMarker(position.coords.latitude, position.coords.longitude);
            },
            (error) => {
                console.error('Geolocation error:', error);
                // Set default marker if geolocation fails
                setMarker(defaultLat, defaultLng);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }
    
    // Form validation
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        
        if (!lat || !lng) {
            e.preventDefault();
            alert('Please select a location on the map.');
            return false;
        }
        
        // Validate file sizes
        const fileInput = document.getElementById('media');
        const files = fileInput.files;
        
        if (files.length > 2) {
            e.preventDefault();
            alert('Maximum 2 files allowed.');
            return false;
        }
        
        for (let file of files) {
            const isPhoto = file.type.startsWith('image/');
            const isVideo = file.type.startsWith('video/');
            const maxSize = isPhoto ? 5 * 1024 * 1024 : (isVideo ? 50 * 1024 * 1024 : 5 * 1024 * 1024);
            
            if (file.size > maxSize) {
                e.preventDefault();
                alert(`File "${file.name}" exceeds the maximum size limit.`);
                return false;
            }
        }
    });

    // Toggle category detail field
    document.getElementById('category').addEventListener('change', function() {
        const detailGroup = document.getElementById('categoryDetailGroup');
        const detailInput = document.getElementById('category_detail');
        const selectedOption = this.options[this.selectedIndex];
        const selectedText = selectedOption ? selectedOption.text : '';

        if (selectedText.toLowerCase().includes('others')) {
            detailGroup.style.display = 'block';
            detailInput.required = true;
        } else {
            detailGroup.style.display = 'none';
            detailInput.required = false;
            detailInput.value = '';
        }
    });

    // Initialize category detail on page load if "others" is pre-selected
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category');
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const selectedText = selectedOption ? selectedOption.text : '';
        if (selectedText.toLowerCase().includes('others')) {
            document.getElementById('categoryDetailGroup').style.display = 'block';
            document.getElementById('category_detail').required = true;
        }
    });

    // Initialize map when page loads
    document.addEventListener('DOMContentLoaded', initMap);
</script>
