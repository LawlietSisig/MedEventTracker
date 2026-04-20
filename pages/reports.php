<?php
/**
 * Reports and Analytics Page
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user    = getCurrentUser();
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

$conn = getConnection();

// ── 1. Top Level Metrics ───────────────────────────────────────────────────────
$totalEvents    = (int) $conn->query("SELECT COUNT(*) FROM outreach_events")->fetch_row()[0];
$totalPatients  = (int) $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0];
$totalVolunteers = (int) $conn->query("SELECT COUNT(*) FROM volunteers")->fetch_row()[0];

// ── 2. Event Analytics ────────────────────────────────────────────────────────
$eventStats = [
    'upcoming'  => 0,
    'ongoing'   => 0,
    'completed' => 0,
    'cancelled' => 0
];
$res = $conn->query("SELECT status, COUNT(*) as count FROM outreach_events GROUP BY status");
while($row = $res->fetch_assoc()) {
    $eventStats[$row['status']] = (int)$row['count'];
}

// ── 3. Patient Analytics ──────────────────────────────────────────────────────
$patientGender = [
    'Male'   => 0,
    'Female' => 0,
    'Other'  => 0
];
$res = $conn->query("SELECT gender, COUNT(*) as count FROM patients GROUP BY gender");
while($row = $res->fetch_assoc()) {
    $patientGender[$row['gender']] = (int)$row['count'];
}

// ── 4. Volunteer Profession Stats ─────────────────────────────────────────────
$professions = [];
$res = $conn->query("SELECT profession, COUNT(*) as count FROM volunteers GROUP BY profession ORDER BY count DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    $professions[] = [
        'label' => $row['profession'],
        'value' => (int)$row['count']
    ];
}

// ── 5. Monthly Patient Growth (Last 6 Months) ─────────────────────────────────
$monthlyGrowth = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M Y', strtotime("-$i months"));
    $res = $conn->prepare("SELECT COUNT(*) FROM patients WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
    $res->bind_param("s", $month);
    $res->execute();
    $count = (int)$res->get_result()->fetch_row()[0];
    $monthlyGrowth[] = ['label' => $monthLabel, 'value' => $count];
}

$conn->close();

// Prepare data for JS
$chartData = [
    'events' => array_values($eventStats),
    'eventLabels' => array_map('ucfirst', array_keys($eventStats)),
    'patients' => array_values($patientGender),
    'patientLabels' => array_keys($patientGender),
    'volunteers' => array_column($professions, 'value'),
    'volunteerLabels' => array_column($professions, 'label'),
    'growth' => array_column($monthlyGrowth, 'value'),
    'growthLabels' => array_column($monthlyGrowth, 'label'),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reports and Analytics — Medical Outreach Tracker">
    <title>Reports — Medical Outreach Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <span class="sidebar-title">MedOutreach</span>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <a href="dashboard.php" class="nav-link" id="nav-dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Dashboard
                </a>
                <a href="outreach_events.php" class="nav-link" id="nav-outreach">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    Outreach Events
                </a>
                <a href="patients.php" class="nav-link" id="nav-patients">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Patients
                </a>

                <div class="nav-section-title">Management</div>
                <a href="volunteers.php" class="nav-link" id="nav-volunteers">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    Volunteers
                </a>
                <a href="reports.php" class="nav-link active" id="nav-reports">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    Reports
                </a>

                <?php if ($user['role'] === 'admin'): ?>
                <div class="nav-section-title">Admin</div>
                <a href="manage_users.php" class="nav-link" id="nav-users">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Manage Users
                </a>
                <?php endif; ?>

                <div class="nav-section-title">Personal</div>
                <a href="settings.php" class="nav-link" id="nav-settings">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854-.108-1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Settings
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-menu">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="../<?php echo htmlspecialchars($user['avatar']); ?>" class="user-avatar" alt="Avatar" style="object-fit: cover;">
                    <?php else: ?>
                        <div class="user-avatar"><?php echo $initials; ?></div>
                    <?php endif; ?>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <div class="top-bar-left">
                    <h2>Reports & Analytics</h2>
                    <p>Overview of system performance and data trends</p>
                </div>
            </div>

            <div class="page-content">
                <!-- Stats Grid -->
                <div class="stats-grid" style="margin-bottom: var(--space-8);">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon" style="background: rgba(14, 165, 165, 0.1); color: var(--primary-600);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo $totalEvents; ?></div>
                        <div class="stat-card-label">Total Events</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon" style="background: rgba(59, 130, 246, 0.1); color: #2563eb;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo $totalPatients; ?></div>
                        <div class="stat-card-label">Total Patients</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon" style="background: rgba(34, 197, 94, 0.1); color: var(--success-500);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo $totalVolunteers; ?></div>
                        <div class="stat-card-label">Total Volunteers</div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <style>
                    .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8); }
                    .chart-container { background: white; border-radius: var(--radius-xl); border: 1.5px solid var(--slate-200); padding: var(--space-6); box-shadow: var(--shadow-sm); min-height: 350px; display: flex; flex-direction: column; }
                    .chart-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; color: var(--slate-800); margin-bottom: var(--space-4); display: flex; align-items: center; gap: 10px; }
                    .chart-canvas-wrapper { flex: 1; position: relative; }
                    @media (max-width: 600px) { .charts-grid { grid-template-columns: 1fr; } }
                </style>
                
                <div class="charts-grid">
                    <!-- Events Status -->
                    <div class="chart-container">
                        <div class="chart-title">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary-500);"></span>
                            Outreach Event Status
                        </div>
                        <div class="chart-canvas-wrapper">
                            <canvas id="eventChart"></canvas>
                        </div>
                    </div>

                    <!-- Patient Demographics -->
                    <div class="chart-container">
                        <div class="chart-title">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #2563eb;"></span>
                            Patient Gender Distribution
                        </div>
                        <div class="chart-canvas-wrapper">
                            <canvas id="patientChart"></canvas>
                        </div>
                    </div>

                    <!-- Volunteer Professions -->
                    <div class="chart-container">
                        <div class="chart-title">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--success-500);"></span>
                            Volunteer Professional Breakdown
                        </div>
                        <div class="chart-canvas-wrapper">
                            <canvas id="volunteerChart"></canvas>
                        </div>
                    </div>

                    <!-- Growth Trend -->
                    <div class="chart-container">
                        <div class="chart-title">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #7c3aed;"></span>
                            Monthly Patient Growth
                        </div>
                        <div class="chart-canvas-wrapper">
                            <canvas id="growthChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Chart Configuration -->
    <script>
        const chartData = <?php echo json_encode($chartData); ?>;
    </script>
    <script src="../assets/js/reports.js?v=<?php echo time(); ?>"></script>
</body>
</html>
