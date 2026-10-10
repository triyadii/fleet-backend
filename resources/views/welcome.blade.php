<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --bg-color: #0f172a;
            --panel-bg: rgba(30, 41, 59, 0.7);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
            --item-bg: rgba(255, 255, 255, 0.03);
            --item-hover: rgba(255, 255, 255, 0.08);
            --input-bg: rgba(15, 23, 42, 0.6);
            --login-overlay-bg: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        body.light-mode {
            --bg-color: #f8fafc;
            --panel-bg: rgba(255, 255, 255, 0.85);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-border: rgba(0, 0, 0, 0.1);
            --item-bg: rgba(0, 0, 0, 0.03);
            --item-hover: rgba(0, 0, 0, 0.06);
            --input-bg: rgba(255, 255, 255, 0.8);
            --login-overlay-bg: linear-gradient(135deg, #e2e8f0 0%, #f8fafc 100%);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Login Overlay */
        #login-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: var(--login-overlay-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        
        .glass-card {
            background: var(--panel-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .glass-card h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(to right, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass-card p {
            color: var(--text-muted);
            margin-bottom: 30px;
            font-size: 14px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 16px;
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--text-main);
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
        }

        .input-group input:focus, .input-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        button.btn-primary {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button.btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        /* Dashboard Layout */
        #dashboard {
            display: none;
            width: 100vw;
            height: 100vh;
            flex-direction: row;
        }

        /* Sidebar */
        .sidebar {
            width: 320px;
            background: var(--panel-bg);
            backdrop-filter: blur(10px);
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 4px 0 24px rgba(0,0,0,0.1);
            transition: background 0.3s ease;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
        }

        .action-btn {
            width: auto;
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
        }
        
        .logout-btn {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #ef4444;
        }
        
        .theme-btn {
            background: var(--item-bg);
            color: var(--text-main);
            border: 1px solid var(--glass-border);
            padding: 6px 10px;
            font-size: 14px;
        }
        .theme-btn:hover {
            background: var(--item-hover);
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            padding: 16px;
            gap: 4px;
            border-bottom: 1px solid var(--glass-border);
        }

        .nav-item {
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-muted);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-item:hover {
            background: var(--item-hover);
            color: var(--text-main);
        }

        .nav-item.active {
            background: rgba(59, 130, 246, 0.15);
            color: var(--primary);
            font-weight: 600;
        }

        /* Monitor Grid */
        .monitor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .driver-card {
            background: var(--panel-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .driver-cam {
            width: 100%;
            height: 180px;
            background: #000;
            object-fit: cover;
            border-bottom: 1px solid var(--glass-border);
        }
        .driver-info {
            padding: 16px;
        }
        .driver-plate {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .driver-status-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .status-tag {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-fokus { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
        .status-mengantuk { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
        .status-berisik { background: rgba(234, 179, 8, 0.15); color: #eab308; }
        .status-tidak-ditempat { background: rgba(249, 115, 22, 0.15); color: #f97316; }
        .status-default { background: rgba(100, 116, 139, 0.15); color: #64748b; }

        /* Views */
        .main-view {
            display: none;
            flex: 1;
            height: 100%;
            background: var(--bg-color);
            z-index: 1;
            position: relative;
        }
        
        .main-view.active-view {
            display: flex;
            flex-direction: column;
        }

        .vehicle-list {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--glass-border);
            border-radius: 4px;
        }

        .vehicle-item {
            background: var(--item-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .vehicle-item:hover, .vehicle-item.active {
            background: var(--item-hover);
            border-color: var(--primary);
            transform: translateX(4px);
        }
        
        .vehicle-item.active {
            box-shadow: -4px 0 0 var(--primary);
        }

        .vehicle-plate {
            font-weight: 600;
            font-size: 16px;
            color: var(--text-main);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .vehicle-details {
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
        }

        /* Map Container */
        #map {
            width: 100%;
            height: 100%;
            background: var(--bg-color);
        }
        
        /* Dark Mode Filter for Standard OSM Tiles */
        body:not(.light-mode) .leaflet-tile-pane {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }
        
        body:not(.light-mode) .leaflet-popup-content-wrapper,
        body:not(.light-mode) .leaflet-popup-tip {
            background: rgba(30, 41, 59, 0.9) !important;
            backdrop-filter: blur(8px) !important;
            color: white !important;
            border: 1px solid var(--glass-border) !important;
        }
        
        body.light-mode .leaflet-popup-content-wrapper,
        body.light-mode .leaflet-popup-tip {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(8px) !important;
            color: #0f172a !important;
            border: 1px solid var(--glass-border) !important;
        }
        
        .leaflet-popup-content-wrapper {
            border-radius: 12px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3) !important;
        }
        
        .vehicle-label-card {
            background: rgba(30, 41, 59, 0.85) !important;
            backdrop-filter: blur(4px) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: 8px !important;
            color: white !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3) !important;
            padding: 4px 8px !important;
        }
        body.light-mode .vehicle-label-card {
            background: rgba(255, 255, 255, 0.9) !important;
            color: #0f172a !important;
        }

        /* CRUD Tables */
        .crud-container {
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            overflow-y: auto;
        }

        .crud-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .crud-header h2 {
            font-size: 24px;
            font-weight: 600;
        }

        .btn-add {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-add:hover { background: var(--primary-hover); }

        .crud-table-wrapper {
            background: var(--panel-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: rgba(0,0,0,0.2);
            padding: 16px;
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        body.light-mode th {
            background: rgba(0,0,0,0.05);
        }

        td {
            padding: 16px;
            border-bottom: 1px solid var(--glass-border);
            font-size: 14px;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover { background: var(--item-hover); }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(59, 130, 246, 0.15);
            color: var(--primary);
        }
        .badge.admin {
            background: rgba(168, 85, 247, 0.15);
            color: #a855f7;
        }

        .action-btns button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            margin-right: 8px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .action-btns button:hover { opacity: 1; }
        .btn-edit { color: var(--primary); }
        .btn-delete { color: #ef4444; }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .modal-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background: var(--panel-bg);
            padding: 32px;
            width: 100%;
            max-width: 500px;
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            text-align: left;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .modal-header h2 { font-size: 20px; }
        .modal-close {
            background: none; border: none; color: var(--text-muted);
            font-size: 24px; cursor: pointer;
        }
        .modal-close:hover { color: var(--text-main); }
        
        .modal-actions {
            display: flex; gap: 12px; margin-top: 24px;
        }
        .btn-cancel {
            flex: 1; padding: 12px; border-radius: 8px;
            background: transparent; border: 1px solid var(--glass-border);
            color: var(--text-main); cursor: pointer;
        }
        .btn-cancel:hover { background: var(--item-bg); }
        .btn-save {
            flex: 1; padding: 12px; border-radius: 8px;
            background: var(--primary); border: none;
            color: white; cursor: pointer; font-weight: 600;
        }
        .btn-save:hover { background: var(--primary-hover); }

        #error-msg, #modal-error-msg {
            color: #ef4444;
            font-size: 13px;
            margin-top: 12px;
            display: none;
            background: rgba(239, 68, 68, 0.1);
            padding: 8px;
            border-radius: 6px;
        }
        .empty-state {
            text-align: center; color: var(--text-muted); padding: 40px 20px; font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- Login Overlay -->
    <div id="login-overlay">
        <div class="glass-card">
            <h1>Fleet Dashboard</h1>
            <p>Enter your credentials to continue</p>
            
            <form id="login-form">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" id="username" required placeholder="admin">
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" id="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn-primary" id="login-btn">Sign In</button>
                <div id="error-msg">Invalid credentials.</div>
            </form>
        </div>
    </div>

    <!-- Dashboard -->
    <div id="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Fleet Admin</h2>
                <div style="display: flex; gap: 8px;">
                    <button class="action-btn theme-btn" onclick="toggleTheme()" id="theme-btn" title="Toggle Light/Dark Mode">☀️</button>
                    <button class="action-btn logout-btn" onclick="logout()">Logout</button>
                </div>
            </div>
            
            <div class="nav-menu">
                <div class="nav-item active" onclick="switchTab('tracking')" id="nav-tracking">🗺️ Live Tracking</div>
                <div class="nav-item" onclick="switchTab('monitor')" id="nav-monitor">📷 Driver Monitor</div>
                <div class="nav-item" onclick="switchTab('users')" id="nav-users">👥 Manage Users</div>
                <div class="nav-item" onclick="switchTab('vehicles')" id="nav-vehicles">🚗 Manage Vehicles</div>
            </div>

            <!-- Vehicle list only shown in tracking tab -->
            <div class="vehicle-list" id="vehicle-list">
                <!-- Vehicle items will be injected here -->
            </div>
        </div>
        
        <!-- Live Tracking View -->
        <div id="tracking-view" class="main-view active-view">
            <div id="map"></div>
            <!-- Overlay Reset Button inside map area -->
            <button class="action-btn" style="position: absolute; top: 20px; right: 20px; z-index: 1000; background: var(--primary); color: white; padding: 10px 16px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.3);" onclick="resetView()" id="reset-btn" title="Reset View & Show All">📍 Reset Map View</button>
        </div>

        <!-- Driver Monitor View -->
        <div id="monitor-view" class="main-view">
            <div class="crud-container" style="max-width: 1600px;">
                <div class="crud-header">
                    <h2>Realtime Driver Monitor</h2>
                </div>
                <div class="monitor-grid" id="monitor-grid">
                    <!-- driver cards will be injected here -->
                </div>
            </div>
        </div>

        <!-- Users View -->
        <div id="users-view" class="main-view">
            <div class="crud-container">
                <div class="crud-header">
                    <h2>User Management</h2>
                    <button class="btn-add" onclick="openUserModal()">+ Add User</button>
                </div>
                <div class="crud-table-wrapper">
                    <table>
                        <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Actions</th></tr></thead>
                        <tbody id="users-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vehicles View -->
        <div id="vehicles-view" class="main-view">
            <div class="crud-container">
                <div class="crud-header">
                    <h2>Vehicle Management</h2>
                    <button class="btn-add" onclick="openVehicleModal()">+ Add Vehicle</button>
                </div>
                <div class="crud-table-wrapper">
                    <table>
                        <thead><tr><th>Plate Number</th><th>Description</th><th>Actions</th></tr></thead>
                        <tbody id="vehicles-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Generic CRUD Modal -->
    <div id="crud-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Title</h2>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <form id="crud-form">
                <div id="modal-body"></div>
                <div id="modal-error-msg"></div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-save" id="modal-save-btn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const API_URL = '/api';
        let map = null;
        let markers = {};
        let fetchInterval = null;
        let currentPath = null;
        let activeVehicleId = null;
        let allBounds = [];

        // CRUD state
        let currentModalMode = ''; // 'user' or 'vehicle'
        let currentEditingId = null; 
        
        // Theme initialization
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('fleet_theme');
            if (savedTheme === 'light') {
                document.body.classList.add('light-mode');
                document.getElementById('theme-btn').innerText = '🌙';
            }
            
            const token = localStorage.getItem('fleet_token');
            if (token) {
                showDashboard(token);
            }
        });

        // Toggle Theme
        function toggleTheme() {
            document.body.classList.toggle('light-mode');
            const isLight = document.body.classList.contains('light-mode');
            localStorage.setItem('fleet_theme', isLight ? 'light' : 'dark');
            document.getElementById('theme-btn').innerText = isLight ? '🌙' : '☀️';
        }

        // Tabs
        function switchTab(tab) {
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            document.getElementById(`nav-${tab}`).classList.add('active');
            
            document.querySelectorAll('.main-view').forEach(el => el.classList.remove('active-view'));
            document.getElementById(`${tab}-view`).classList.add('active-view');
            
            const listEl = document.getElementById('vehicle-list');
            if (tab === 'tracking') {
                listEl.style.display = 'block';
                if (map) setTimeout(() => map.invalidateSize(), 100);
            } else {
                listEl.style.display = 'none';
            }
            
            if (tab === 'users') fetchUsers();
            if (tab === 'vehicles') fetchVehicles();
        }

        // Login
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('login-btn');
            const errorMsg = document.getElementById('error-msg');
            
            btn.innerHTML = 'Signing in...';
            btn.disabled = true;
            errorMsg.style.display = 'none';

            try {
                const res = await fetch(`${API_URL}/login`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        username: document.getElementById('username').value,
                        password: document.getElementById('password').value
                    })
                });
                const data = await res.json();
                if (res.ok && data.access_token) {
                    localStorage.setItem('fleet_token', data.access_token);
                    showDashboard(data.access_token);
                } else {
                    throw new Error(data.message || 'Login failed.');
                }
            } catch (err) {
                errorMsg.innerText = err.message;
                errorMsg.style.display = 'block';
            } finally {
                btn.innerHTML = 'Sign In';
                btn.disabled = false;
            }
        });

        async function logout() {
            const token = localStorage.getItem('fleet_token');
            if (token) {
                try {
                    await fetch(`${API_URL}/logout`, {
                        method: 'POST',
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                } catch(e) {}
            }
            localStorage.removeItem('fleet_token');
            location.reload();
        }

        function showDashboard(token) {
            document.getElementById('login-overlay').style.opacity = '0';
            setTimeout(() => {
                document.getElementById('login-overlay').style.display = 'none';
                document.getElementById('dashboard').style.display = 'flex';
                initMap();
                fetchTelemetry();
                fetchInterval = setInterval(() => fetchTelemetry(), 5000);
            }, 500);
        }

        // ====== LIVE TRACKING ======
        function initMap() {
            if (map) return;
            map = L.map('map', {zoomControl: false}).setView([-6.200000, 106.816666], 12);
            L.control.zoom({ position: 'bottomright' }).addTo(map);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors', subdomains: 'abc', maxZoom: 20
            }).addTo(map);
        }

        async function fetchTelemetry() {
            const token = localStorage.getItem('fleet_token');
            try {
                const res = await fetch(`${API_URL}/telemetry/latest`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                if (res.status === 401) { logout(); return; }
                const json = await res.json();
                updateTrackingUI(json.data);
                updateMonitorUI(json.data);
            } catch (err) {}
        }

        function updateTrackingUI(vehicles) {
            const listEl = document.getElementById('vehicle-list');
            if (!vehicles || vehicles.length === 0) {
                if (Object.keys(markers).length === 0) listEl.innerHTML = '<div class="empty-state">No vehicles tracked yet.</div>';
                return;
            }
            
            listEl.innerHTML = '';
            let bounds = [];

            vehicles.forEach(v => {
                const logTime = new Date(v.timestamp);
                const isOnline = (new Date() - logTime) < (5 * 60 * 1000);
                const statusColor = isOnline ? '#ef4444' : '#991b1b';

                const item = document.createElement('div');
                item.className = 'vehicle-item';
                item.innerHTML = `
                    <div class="vehicle-plate">
                        <div style="width:8px; height:8px; border-radius:50%; background:${statusColor}; box-shadow:0 0 8px ${statusColor};"></div>
                        ${v.plate_number}
                    </div>
                    <div class="vehicle-details">
                        <span>Speed: ${v.speed} km/h</span>
                        <span>${logTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                    </div>
                `;
                
                item.onclick = () => {
                    activeVehicleId = v.vehicle_id;
                    document.querySelectorAll('.vehicle-item').forEach(el => el.classList.remove('active'));
                    item.classList.add('active');
                    
                    Object.keys(markers).forEach(id => {
                        if (parseInt(id) !== activeVehicleId) {
                            map.removeLayer(markers[id]);
                        } else {
                            if (!map.hasLayer(markers[id])) map.addLayer(markers[id]);
                            markers[id].openPopup();
                        }
                    });
                    fetchVehicleHistory(v.vehicle_id);
                };
                
                if (activeVehicleId === v.vehicle_id) item.classList.add('active');
                listEl.appendChild(item);

                if (v.lat && v.lng) {
                    bounds.push([v.lat, v.lng]);
                    const popupContent = `
                        <div style="text-align:center;">
                            <strong style="font-size:16px; color:inherit;">${v.plate_number}</strong><br>
                            <span style="opacity:0.8; font-size:12px;">Speed: ${v.speed} km/h</span><br>
                            <span style="opacity:0.8; font-size:12px;">${logTime.toLocaleString()}</span>
                            ${v.snapshot_url ? `<br><img src="${v.snapshot_url}" style="width:100%; max-width:200px; border-radius:8px; margin-top:8px; border:1px solid var(--glass-border);">` : ''}
                        </div>`;
                        
                    const tooltipContent = `
                        <div style="text-align:left; line-height:1.4;">
                            <strong style="font-size:13px; color:inherit;">${v.plate_number}</strong><br>
                            <span style="opacity:0.8; font-size:11px;">Speed: ${v.speed} km/h</span><br>
                            <span style="opacity:0.8; font-size:11px;">${logTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                        </div>
                    `;

                    const icon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div style="background:${statusColor}; width:16px; height:16px; border-radius:50%; border:3px solid white; box-shadow:0 0 12px ${statusColor};"></div>`,
                        iconSize: [16, 16], iconAnchor: [8, 8]
                    });
                        
                    if (markers[v.vehicle_id]) {
                        markers[v.vehicle_id].setLatLng([v.lat, v.lng]);
                        markers[v.vehicle_id].setPopupContent(popupContent);
                        markers[v.vehicle_id].setTooltipContent(tooltipContent);
                        markers[v.vehicle_id].setIcon(icon);
                        
                        if (activeVehicleId === v.vehicle_id && currentPath) {
                            const latlngs = currentPath.getLatLngs();
                            if (latlngs.length > 0) {
                                const lastLatLng = latlngs[latlngs.length - 1];
                                if (lastLatLng.lat !== v.lat || lastLatLng.lng !== v.lng) {
                                    currentPath.addLatLng([v.lat, v.lng]);
                                    map.panTo([v.lat, v.lng]);
                                }
                            }
                        }
                    } else {
                        markers[v.vehicle_id] = L.marker([v.lat, v.lng], {icon: icon})
                            .addTo(map)
                            .bindPopup(popupContent)
                            .bindTooltip(tooltipContent, {permanent: true, direction: 'right', offset: [10, 0], className: 'vehicle-label-card'});
                    }
                    
                    if (activeVehicleId !== null && activeVehicleId !== v.vehicle_id) {
                        map.removeLayer(markers[v.vehicle_id]);
                    } else {
                        if (!map.hasLayer(markers[v.vehicle_id])) map.addLayer(markers[v.vehicle_id]);
                    }
                }
            });

            allBounds = bounds;
            if (bounds.length > 0 && Object.keys(markers).length <= bounds.length && !window.mapFitted) {
                map.fitBounds(bounds, {padding: [50, 50], maxZoom: 15});
                window.mapFitted = true;
            }
        }
        
        function updateMonitorUI(vehicles) {
            const grid = document.getElementById('monitor-grid');
            if (!vehicles || vehicles.length === 0) {
                grid.innerHTML = '<div class="empty-state" style="grid-column: 1 / -1;">No vehicles active.</div>';
                return;
            }
            grid.innerHTML = '';
            vehicles.forEach(v => {
                const logTime = new Date(v.timestamp);
                const isOnline = (new Date() - logTime) < (5 * 60 * 1000);
                
                let focusTag = v.fokus ? '<span class="status-tag status-fokus">Fokus</span>' : '';
                let ngantukTag = v.mengantuk ? '<span class="status-tag status-mengantuk">Mengantuk</span>' : '';
                let berisikTag = v.berisik ? '<span class="status-tag status-berisik">Berisik</span>' : '';
                let awayTag = v.tidak_ditempat ? '<span class="status-tag status-tidak-ditempat">Tidak Ditempat</span>' : '';
                
                if (!v.fokus && !v.mengantuk && !v.berisik && !v.tidak_ditempat) {
                    focusTag = '<span class="status-tag status-default">Normal</span>';
                }

                const card = document.createElement('div');
                card.className = 'driver-card';
                
                // For preview without image we use placehold.co
                const imgUrl = v.snapshot_url ? v.snapshot_url : 'https://placehold.co/600x400/1e293b/94a3b8?text=No+Camera';
                
                card.innerHTML = `
                    <img src="${imgUrl}" class="driver-cam" alt="Driver Cam">
                    <div class="driver-info">
                        <div class="driver-plate">🚗 ${v.plate_number}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.5;">
                            Speed: ${v.speed} km/h <br>
                            Updated: ${logTime.toLocaleTimeString()} <br>
                            Status: ${isOnline ? '<span style="color:#22c55e; font-weight:600;">Online</span>' : '<span style="color:#ef4444; font-weight:600;">Offline</span>'}
                        </div>
                        <div class="driver-status-tags">
                            ${focusTag}
                            ${ngantukTag}
                            ${berisikTag}
                            ${awayTag}
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
        }
        
        let pathMarkers = [];

        function resetView() {
            activeVehicleId = null;
            document.querySelectorAll('.vehicle-item').forEach(el => el.classList.remove('active'));
            if (currentPath) { currentPath.remove(); currentPath = null; }
            pathMarkers.forEach(m => m.remove());
            pathMarkers = [];
            
            Object.values(markers).forEach(marker => {
                if (!map.hasLayer(marker)) map.addLayer(marker);
            });
            if (allBounds.length > 0) map.fitBounds(allBounds, {padding: [50, 50], maxZoom: 15});
        }

        async function fetchVehicleHistory(vehicleId) {
            const token = localStorage.getItem('fleet_token');
            try {
                const res = await fetch(`${API_URL}/telemetry/${vehicleId}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const json = await res.json();
                    drawPath(json.data.logs, json.data.plate_number);
                }
            } catch (err) {}
        }

        function drawPath(logs, plateNumber) {
            if (currentPath) { currentPath.remove(); currentPath = null; }
            pathMarkers.forEach(m => m.remove());
            pathMarkers = [];
            
            if (!logs || logs.length === 0) return;
            const latlngs = logs.map(log => [log.lat, log.lng]);
            currentPath = L.polyline(latlngs, { color: '#3b82f6', weight: 4, opacity: 0.8 }).addTo(map);
            
            logs.forEach((log, index) => {
                const logTime = new Date(log.timestamp);
                const popup = `
                    <div style="text-align:center;">
                        <strong style="font-size:14px;">${plateNumber}</strong><br>
                        <span style="opacity:0.8; font-size:12px;">Speed: ${log.speed} km/h</span><br>
                        <span style="opacity:0.8; font-size:12px;">${logTime.toLocaleString()}</span>
                        ${log.snapshot_url ? `<br><img src="${log.snapshot_url}" style="width:100%; max-width:200px; border-radius:8px; margin-top:8px; border:1px solid var(--glass-border);">` : ''}
                    </div>
                `;
                
                const circle = L.circleMarker([log.lat, log.lng], {
                    radius: 5,
                    fillColor: '#0f172a',
                    color: '#3b82f6',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 1
                }).addTo(map).bindPopup(popup);
                
                pathMarkers.push(circle);
            });
            
            map.fitBounds(currentPath.getBounds(), {padding: [50, 50], maxZoom: 16});
        }

        // ====== CRUD OPERATIONS ======
        function authHeaders() {
            return {
                'Authorization': `Bearer ${localStorage.getItem('fleet_token')}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
        }

        // Users
        async function fetchUsers() {
            try {
                const res = await fetch(`${API_URL}/users`, { headers: authHeaders() });
                const json = await res.json();
                const tbody = document.getElementById('users-tbody');
                tbody.innerHTML = '';
                if(json.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="empty-state">No users found</td></tr>';
                    return;
                }
                json.data.forEach(u => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${u.name}</td>
                            <td>${u.username}</td>
                            <td><span class="badge ${u.role === 'admin' ? 'admin' : ''}">${u.role}</span></td>
                            <td class="action-btns">
                                <button class="btn-edit" onclick="editUser('${u.uuid}')" title="Edit">✎</button>
                                <button class="btn-delete" onclick="deleteItem('users', '${u.uuid}')" title="Delete">🗑</button>
                            </td>
                        </tr>
                    `;
                });
            } catch(e) {}
        }

        // Vehicles
        async function fetchVehicles() {
            try {
                const res = await fetch(`${API_URL}/vehicles`, { headers: authHeaders() });
                const json = await res.json();
                const tbody = document.getElementById('vehicles-tbody');
                tbody.innerHTML = '';
                if(json.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="empty-state">No vehicles found</td></tr>';
                    return;
                }
                json.data.forEach(v => {
                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${v.plate_number}</strong></td>
                            <td>${v.description || '-'}</td>
                            <td class="action-btns">
                                <button class="btn-edit" onclick="editVehicle('${v.uuid}')" title="Edit">✎</button>
                                <button class="btn-delete" onclick="deleteItem('vehicles', '${v.uuid}')" title="Delete">🗑</button>
                            </td>
                        </tr>
                    `;
                });
            } catch(e) {}
        }

        // Modal Handlers
        function openModal(title) {
            document.getElementById('modal-title').innerText = title;
            document.getElementById('modal-error-msg').style.display = 'none';
            document.getElementById('crud-modal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('crud-modal').classList.remove('show');
            currentEditingId = null;
        }

        function openUserModal(uuid = null) {
            currentModalMode = 'user';
            currentEditingId = uuid;
            const body = document.getElementById('modal-body');
            body.innerHTML = `
                <div class="input-group"><label>Name</label><input type="text" id="m_name" required></div>
                <div class="input-group"><label>Username</label><input type="text" id="m_username" required></div>
                <div class="input-group"><label>Password ${uuid ? '(Leave blank to keep current)' : ''}</label><input type="password" id="m_password" ${uuid ? '' : 'required'}></div>
                <div class="input-group">
                    <label>Role</label>
                    <select id="m_role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            `;
            openModal(uuid ? 'Edit User' : 'Add User');
        }

        function openVehicleModal(uuid = null) {
            currentModalMode = 'vehicle';
            currentEditingId = uuid;
            const body = document.getElementById('modal-body');
            body.innerHTML = `
                <div class="input-group"><label>Plate Number</label><input type="text" id="m_plate" required></div>
                <div class="input-group"><label>Description</label><input type="text" id="m_desc"></div>
            `;
            openModal(uuid ? 'Edit Vehicle' : 'Add Vehicle');
        }

        async function editUser(uuid) {
            try {
                const res = await fetch(`${API_URL}/users/${uuid}`, { headers: authHeaders() });
                const json = await res.json();
                openUserModal(uuid);
                document.getElementById('m_name').value = json.data.name;
                document.getElementById('m_username').value = json.data.username;
                document.getElementById('m_role').value = json.data.role;
            } catch(e) {}
        }

        async function editVehicle(uuid) {
            try {
                const res = await fetch(`${API_URL}/vehicles/${uuid}`, { headers: authHeaders() });
                const json = await res.json();
                openVehicleModal(uuid);
                document.getElementById('m_plate').value = json.data.plate_number;
                document.getElementById('m_desc').value = json.data.description || '';
            } catch(e) {}
        }

        async function deleteItem(type, uuid) {
            if(!confirm(`Are you sure you want to delete this ${type.slice(0,-1)}?`)) return;
            try {
                await fetch(`${API_URL}/${type}/${uuid}`, { method: 'DELETE', headers: authHeaders() });
                if(type === 'users') fetchUsers();
                if(type === 'vehicles') fetchVehicles();
            } catch(e) {}
        }

        document.getElementById('crud-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('modal-save-btn');
            const err = document.getElementById('modal-error-msg');
            btn.disabled = true;
            btn.innerText = 'Saving...';
            err.style.display = 'none';

            let endpoint = '';
            let payload = {};
            let method = currentEditingId ? 'PUT' : 'POST';

            if (currentModalMode === 'user') {
                endpoint = currentEditingId ? `${API_URL}/users/${currentEditingId}` : `${API_URL}/users`;
                payload = {
                    name: document.getElementById('m_name').value,
                    username: document.getElementById('m_username').value,
                    role: document.getElementById('m_role').value
                };
                const pw = document.getElementById('m_password').value;
                if(pw) payload.password = pw;
            } else {
                endpoint = currentEditingId ? `${API_URL}/vehicles/${currentEditingId}` : `${API_URL}/vehicles`;
                payload = {
                    plate_number: document.getElementById('m_plate').value,
                    description: document.getElementById('m_desc').value
                };
            }

            try {
                const res = await fetch(endpoint, {
                    method: method,
                    headers: authHeaders(),
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if(!res.ok) throw new Error(data.message || 'An error occurred');
                
                closeModal();
                if (currentModalMode === 'user') fetchUsers();
                if (currentModalMode === 'vehicle') fetchVehicles();
            } catch(e) {
                err.innerText = e.message;
                err.style.display = 'block';
            } finally {
                btn.disabled = false;
                btn.innerText = 'Save';
            }
        });
    </script>
</body>
</html>
