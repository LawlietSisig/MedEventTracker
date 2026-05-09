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
                <div class="top-bar-right">
                    <button class="btn-outline-icon" id="btn-print-report" onclick="window.print()" title="Print this page">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                        </svg>
                        Print Report
                    </button>
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
                    /* Charts */
                    .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8); }
                    .chart-container { background: white; border-radius: var(--radius-xl); border: 1.5px solid var(--slate-200); padding: var(--space-6); box-shadow: var(--shadow-sm); min-height: 350px; display: flex; flex-direction: column; }
                    .chart-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; color: var(--slate-800); margin-bottom: var(--space-4); display: flex; align-items: center; gap: 10px; }
                    .chart-canvas-wrapper { flex: 1; position: relative; }
                    @media (max-width: 600px) { .charts-grid { grid-template-columns: 1fr; } }

                    /* Download Section */
                    .download-section { margin-bottom: var(--space-8); }
                    .download-section-title {
                        font-family: var(--font-display);
                        font-size: 1.15rem;
                        font-weight: 700;
                        color: var(--slate-800);
                        margin-bottom: var(--space-5);
                        display: flex;
                        align-items: center;
                        gap: var(--space-3);
                    }
                    .download-section-title svg {
                        width: 20px; height: 20px;
                        color: var(--primary-600);
                    }
                    .download-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                        gap: var(--space-5);
                    }
                    .download-card {
                        background: white;
                        border-radius: var(--radius-xl);
                        border: 1.5px solid var(--slate-200);
                        padding: var(--space-6);
                        box-shadow: var(--shadow-sm);
                        display: flex;
                        flex-direction: column;
                        gap: var(--space-4);
                        transition: box-shadow var(--transition-base), border-color var(--transition-base), transform var(--transition-base);
                        position: relative;
                        overflow: hidden;
                    }
                    .download-card::before {
                        content: '';
                        position: absolute;
                        top: 0; left: 0; right: 0;
                        height: 3px;
                        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
                    }
                    .download-card.dc-events::before   { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
                    .download-card.dc-patients::before { background: linear-gradient(90deg, var(--primary-500), var(--primary-400)); }
                    .download-card.dc-volunteers::before { background: linear-gradient(90deg, #22c55e, #4ade80); }
                    .download-card.dc-summary::before  { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
                    .download-card:hover {
                        border-color: var(--slate-300);
                        box-shadow: var(--shadow-md);
                        transform: translateY(-2px);
                    }
                    .download-card-icon {
                        width: 44px; height: 44px;
                        border-radius: var(--radius-lg);
                        display: flex; align-items: center; justify-content: center;
                        flex-shrink: 0;
                    }
                    .download-card-icon svg { width: 22px; height: 22px; }
                    .dc-events   .download-card-icon { background: rgba(59,130,246,0.1);  color: #2563eb; }
                    .dc-patients .download-card-icon { background: rgba(27,122,77,0.1);   color: var(--primary-600); }
                    .dc-volunteers .download-card-icon { background: rgba(34,197,94,0.1); color: #16a34a; }
                    .dc-summary  .download-card-icon { background: rgba(139,92,246,0.1);  color: #7c3aed; }
                    .download-card-info { flex: 1; }
                    .download-card-info h4 {
                        font-family: var(--font-display);
                        font-size: 1rem;
                        font-weight: 700;
                        color: var(--slate-800);
                        margin-bottom: 4px;
                    }
                    .download-card-info p {
                        font-size: 0.82rem;
                        color: var(--slate-500);
                        line-height: 1.5;
                    }
                    .download-card-count {
                        font-family: var(--font-display);
                        font-size: 1.6rem;
                        font-weight: 800;
                        color: var(--slate-700);
                        line-height: 1;
                        margin-bottom: 2px;
                    }
                    .download-card-count-label {
                        font-size: 0.75rem;
                        color: var(--slate-400);
                        text-transform: uppercase;
                        letter-spacing: 0.06em;
                        font-weight: 700;
                    }
                    .btn-download {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        gap: var(--space-2);
                        padding: var(--space-2) var(--space-4);
                        border-radius: var(--radius-md);
                        font-family: var(--font-display);
                        font-size: 0.82rem;
                        font-weight: 700;
                        letter-spacing: 0.03em;
                        text-decoration: none;
                        border: 1.5px solid transparent;
                        cursor: pointer;
                        transition: all var(--transition-base);
                        width: 100%;
                        margin-top: auto;
                    }
                    .btn-download svg { width: 15px; height: 15px; flex-shrink: 0; }
                    .dc-events   .btn-download { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
                    .dc-events   .btn-download:hover { background: #2563eb; color: white; border-color: #2563eb; }
                    .dc-patients .btn-download { background: var(--green-50); color: var(--primary-700); border-color: var(--green-200); }
                    .dc-patients .btn-download:hover { background: var(--primary-600); color: white; border-color: var(--primary-600); }
                    .dc-volunteers .btn-download { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
                    .dc-volunteers .btn-download:hover { background: #16a34a; color: white; border-color: #16a34a; }
                    .dc-summary  .btn-download { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
                    .dc-summary  .btn-download:hover { background: #7c3aed; color: white; border-color: #7c3aed; }
                    .dc-format-badge {
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                        font-size: 0.7rem;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.08em;
                        color: var(--slate-400);
                        margin-bottom: var(--space-3);
                    }
                    .dc-format-badge span {
                        background: var(--slate-100);
                        border: 1px solid var(--slate-200);
                        border-radius: var(--radius-sm);
                        padding: 1px 6px;
                        color: var(--slate-500);
                    }
                    /* Print styles */
                    @media print {
                        .sidebar, .top-bar .top-bar-right, .download-section { display: none !important; }
                        .main-content { margin-left: 0 !important; padding: 1rem !important; }
                        .chart-container { break-inside: avoid; }
                    }
                    /* Top bar outline icon button */
                    .btn-outline-icon {
                        display: inline-flex; align-items: center; gap: var(--space-2);
                        padding: var(--space-2) var(--space-4);
                        font-family: var(--font-display);
                        font-size: 0.82rem; font-weight: 700;
                        color: var(--slate-600);
                        background: white;
                        border: 1.5px solid var(--slate-200);
                        border-radius: var(--radius-md);
                        cursor: pointer;
                        transition: all var(--transition-base);
                        letter-spacing: 0.02em;
                    }
                    .btn-outline-icon svg { width: 16px; height: 16px; }
                    .btn-outline-icon:hover { background: var(--slate-50); border-color: var(--slate-300); color: var(--slate-800); box-shadow: var(--shadow-sm); }
                    /* PDF button */
                    .btn-row { display: flex; gap: var(--space-2); margin-top: auto; }
                    .btn-row .btn-download { flex: 1; margin-top: 0; }
                    .btn-download-pdf { background: #fff1f2 !important; color: #be123c !important; border-color: #fecdd3 !important; }
                    .btn-download-pdf:hover { background: #e11d48 !important; color: white !important; border-color: #e11d48 !important; }
                    .dc-format-badge span + span { margin-left: 4px; }
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

                <!-- ── Download Reports Section ───────────────────────────────── -->
                <div class="download-section">
                    <div class="download-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download Reports
                    </div>

                    <div class="download-grid">

                        <!-- Events CSV -->
                        <div class="download-card dc-events">
                            <div class="download-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <div class="download-card-info">
                                <div class="download-card-count"><?php echo $totalEvents; ?></div>
                                <div class="download-card-count-label">Records</div>
                            </div>
                            <div>
                                <h4>Outreach Events</h4>
                                <p>All events with title, location, dates, status &amp; volunteer capacity.</p>
                            </div>
                            <div class="dc-format-badge">Format <span>CSV</span><span>PDF</span></div>
                            <div class="btn-row">
                                <a href="../handlers/report_export_handler.php?type=events" class="btn-download" id="btn-download-events-csv">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                    CSV
                                </a>
                                <a href="../handlers/report_pdf_handler.php?type=events" class="btn-download btn-download-pdf" id="btn-download-events-pdf">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    PDF
                                </a>
                            </div>
                        </div>

                        <!-- Patients CSV -->
                        <div class="download-card dc-patients">
                            <div class="download-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                            </div>
                            <div class="download-card-info">
                                <div class="download-card-count"><?php echo $totalPatients; ?></div>
                                <div class="download-card-count-label">Records</div>
                            </div>
                            <div>
                                <h4>Patient Records</h4>
                                <p>Full patient list with demographics, blood type &amp; medical notes.</p>
                            </div>
                            <div class="dc-format-badge">Format <span>CSV</span><span>PDF</span></div>
                            <div class="btn-row">
                                <a href="../handlers/report_export_handler.php?type=patients" class="btn-download" id="btn-download-patients-csv">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                    CSV
                                </a>
                                <a href="../handlers/report_pdf_handler.php?type=patients" class="btn-download btn-download-pdf" id="btn-download-patients-pdf">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    PDF
                                </a>
                            </div>
                        </div>

                        <!-- Volunteers CSV -->
                        <div class="download-card dc-volunteers">
                            <div class="download-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                            </div>
                            <div class="download-card-info">
                                <div class="download-card-count"><?php echo $totalVolunteers; ?></div>
                                <div class="download-card-count-label">Records</div>
                            </div>
                            <div>
                                <h4>Volunteer Roster</h4>
                                <p>All volunteers with contact info, profession &amp; registration date.</p>
                            </div>
                            <div class="dc-format-badge">Format <span>CSV</span><span>PDF</span></div>
                            <div class="btn-row">
                                <a href="../handlers/report_export_handler.php?type=volunteers" class="btn-download" id="btn-download-volunteers-csv">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                    CSV
                                </a>
                                <a href="../handlers/report_pdf_handler.php?type=volunteers" class="btn-download btn-download-pdf" id="btn-download-volunteers-pdf">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    PDF
                                </a>
                            </div>
                        </div>

                        <!-- Full Summary CSV -->
                        <div class="download-card dc-summary">
                            <div class="download-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            <div class="download-card-info">
                                <div class="download-card-count" style="font-size:1.1rem; padding-top:4px;">Full Report</div>
                                <div class="download-card-count-label">All Sections</div>
                            </div>
                            <div>
                                <h4>Summary Report</h4>
                                <p>Overview metrics, breakdowns by status, gender, age groups &amp; monthly trends.</p>
                            </div>
                            <div class="dc-format-badge">Format <span>CSV</span><span>PDF</span></div>
                            <div class="btn-row">
                                <a href="../handlers/report_export_handler.php?type=summary" class="btn-download" id="btn-download-summary-csv">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                    CSV
                                </a>
                                <a href="../handlers/report_pdf_handler.php?type=summary" class="btn-download btn-download-pdf" id="btn-download-summary-pdf">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    PDF
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- ── End Download Section ───────────────────────────────────── -->

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
