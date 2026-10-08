<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Admin Dashboard</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        :root {
            --primary: #2563eb;
            --bg-color: #f8fafc;
            --panel-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
        }
        .dashboard-container {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }
        .sidebar {
            width: 350px;
            background-color: var(--panel-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            z-index: 1000; /* Above map */
        }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--primary);
            color: white;
        }
        .sidebar-header h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }
        .vehicle-card {
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .vehicle-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .vehicle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .plate-number {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--primary);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 12px;
            background-color: #10b981;
            color: white;
            font-weight: 500;
        }
        .vehicle-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 0.875rem;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        .stat-value {
            font-weight: 500;
        }
        .snapshot-preview {
            margin-top: 10px;
            width: 100%;
            height: 120px;
            border-radius: 6px;
            object-fit: cover;
            display: none;
            background-color: #e2e8f0;
        }
        #map {
            flex: 1;
            height: 100%;
            z-index: 1;
        }
        
        /* Loading Overlay */
        .loading {
            text-align: center;
            color: var(--text-muted);
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>Fleet Live Dashboard</h1>
            <div style="font-size: 0.8rem; margin-top: 5px; opacity: 0.8;">Real-time Vehicle Tracking</div>
        </div>
        <div class="sidebar-content" id="vehicle-list">
            <div class="loading">Loading fleet data...</div>
        </div>
    </div>
    <div id="map"></div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // Initialize Map
    const map = L.map('map').setView([-6.200000, 106.816666], 12); // Default to Jakarta
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    const markers = {};
    const vehicleListEl = document.getElementById('vehicle-list');

    // Custom Icon for Vehicle
    const carIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background-color: #2563eb; width: 14px; height: 14px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.4);"></div>`,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    async function fetchTelemetryData() {
        try {
            const response = await fetch('/api/telemetry/latest');
            const result = await response.json();
            updateDashboard(result.data);
        } catch (error) {
            console.error('Error fetching telemetry data:', error);
        }
    }

    function updateDashboard(vehicles) {
        if(!vehicles || vehicles.length === 0) {
            vehicleListEl.innerHTML = '<div class="loading">No active vehicles found.</div>';
            return;
        }

        let listHtml = '';
        const bounds = [];

        vehicles.forEach(vehicle => {
            // Update or Create Marker
            if (markers[vehicle.vehicle_id]) {
                markers[vehicle.vehicle_id].setLatLng([vehicle.lat, vehicle.lng]);
            } else {
                const marker = L.marker([vehicle.lat, vehicle.lng], {icon: carIcon}).addTo(map);
                marker.bindPopup(`<b>${vehicle.plate_number}</b><br>Speed: ${vehicle.speed} km/h`);
                markers[vehicle.vehicle_id] = marker;
            }
            bounds.push([vehicle.lat, vehicle.lng]);

            // Format timestamp
            const date = new Date(vehicle.timestamp);
            const timeString = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});

            // Build Sidebar Item
            listHtml += `
                <div class="vehicle-card" onclick="focusVehicle(${vehicle.lat}, ${vehicle.lng})">
                    <div class="vehicle-header">
                        <div class="plate-number">${vehicle.plate_number}</div>
                        <div class="status-badge">Active</div>
                    </div>
                    <div class="vehicle-stats">
                        <div class="stat-item">
                            <span class="stat-label">Speed</span>
                            <span class="stat-value">${vehicle.speed} km/h</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Last Update</span>
                            <span class="stat-value">${timeString}</span>
                        </div>
                    </div>
                    ${vehicle.snapshot_url ? `<img src="${vehicle.snapshot_url}" class="snapshot-preview" style="display:block" alt="Camera Snapshot">` : ''}
                </div>
            `;
        });

        vehicleListEl.innerHTML = listHtml;
    }

    function focusVehicle(lat, lng) {
        map.setView([lat, lng], 16, { animate: true });
    }

    // Initial fetch
    fetchTelemetryData();

    // Poll every 5 seconds
    setInterval(fetchTelemetryData, 5000);
</script>
</body>
</html>
