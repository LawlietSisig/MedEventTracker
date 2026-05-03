<?php
/**
 * Outreach Events Page
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user    = getCurrentUser();
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
$flash   = getFlash();

// ── Fetch events ───────────────────────────────────────────────────────────────
$conn   = getConnection();
$search = trim($_GET['search'] ?? '');
$filter = $_GET['status'] ?? 'all';

$params = [];
$types  = '';
$where  = ['1=1'];

if ($search !== '') {
    $where[]  = '(e.title LIKE ? OR e.location LIKE ?)';
    $like     = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

$validStatuses = ['upcoming', 'ongoing', 'completed', 'cancelled'];
if (in_array($filter, $validStatuses)) {
    $where[]  = 'e.status = ?';
    $params[] = $filter;
    $types   .= 's';
}

$whereSQL = implode(' AND ', $where);
$sql = "SELECT e.*, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
        FROM outreach_events e
        JOIN users u ON u.id = e.created_by
        WHERE {$whereSQL}
        ORDER BY e.event_date DESC, e.start_time DESC";

$stmt   = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Counts for summary cards ───────────────────────────────────────────────────
$counts = [];
foreach ($validStatuses as $s) {
    $r = $conn->query("SELECT COUNT(*) AS n FROM outreach_events WHERE status='{$s}'")->fetch_assoc();
    $counts[$s] = (int)$r['n'];
}
$counts['all'] = array_sum($counts);

// ── Fetch event for editing (if ?edit=id) ─────────────────────────────────────
$editEvent = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $es = $conn->prepare("SELECT * FROM outreach_events WHERE id = ?");
    $es->bind_param('i', $_GET['edit']);
    $es->execute();
    $editEvent = $es->get_result()->fetch_assoc();
    $es->close();
}

$conn->close();

// ── Status helpers ─────────────────────────────────────────────────────────────
function statusBadge(string $status): string {
    $map = [
        'upcoming'  => ['class' => 'badge-upcoming',   'label' => 'Upcoming'],
        'ongoing'   => ['class' => 'badge-ongoing',    'label' => 'Ongoing'],
        'completed' => ['class' => 'badge-completed',  'label' => 'Completed'],
        'cancelled' => ['class' => 'badge-cancelled',  'label' => 'Cancelled'],
    ];
    $b = $map[$status] ?? ['class' => '', 'label' => ucfirst($status)];
    return '<span class="event-badge ' . $b['class'] . '">' . $b['label'] . '</span>';
}

/**
 * Compute real-time status from event dates/times.
 * Returns 'cancelled' unchanged; otherwise derives upcoming/ongoing/completed.
 */
function computeStatus(string $eventDate, ?string $endEventDate, string $startTime, string $endTime, string $savedStatus): string {
    if ($savedStatus === 'cancelled') return 'cancelled';
    $now       = new DateTime();
    $startDt   = new DateTime($eventDate . ' ' . $startTime);
    $endDtDate = $endEventDate ?? $eventDate;
    $endDt     = new DateTime($endDtDate . ' ' . $endTime);
    if ($now < $startDt)                         return 'upcoming';
    if ($now >= $startDt && $now <= $endDt)      return 'ongoing';
    return 'completed';
}

function canAdmin(array $user): bool {
    return in_array($user['role'], ['admin', 'coordinator']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Outreach Events — Medical Outreach Tracker">
    <title>Outreach Events — Medical Outreach Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏥</text></svg>">
</head>
<body>
<div class="dashboard-layout">

    <!-- ═══ SIDEBAR ═══ -->
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
            <a href="outreach_events.php" class="nav-link active" id="nav-outreach">
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
            <a href="reports.php" class="nav-link" id="nav-reports">
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Settings
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-menu">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="../<?php echo htmlspecialchars($user['avatar']); ?>" class="user-avatar" alt="Avatar" style="object-fit:cover;">
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

    <!-- ═══ MAIN CONTENT ═══ -->
    <main class="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <h2>Outreach Events</h2>
                <p><?php echo date('l, F j, Y'); ?></p>
            </div>
            <div class="top-bar-right">
                <?php if (canAdmin($user)): ?>
                <button class="btn-primary btn-add-event" id="btn-open-create" onclick="openCreateModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Event
                </button>
                <?php endif; ?>
                <a href="../handlers/logout_handler.php" class="btn-outline" id="logout-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>

        <div class="page-content">

            <!-- Flash -->
            <?php if ($flash): ?>
            <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> alert-dismissible" id="flash-alert">
                <?php if ($flash['type'] === 'error'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($flash['message']); ?></span>
            </div>
            <?php endif; ?>

            <!-- ── Summary Cards ── -->
            <div class="oe-summary-grid">
                <?php
                $cards = [
                    ['label'=>'Total Events',  'key'=>'all',       'icon'=>'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5', 'color'=>'teal'],
                    ['label'=>'Upcoming',      'key'=>'upcoming',  'icon'=>'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',                                                                                                                                                                                                                                                                                    'color'=>'blue'],
                    ['label'=>'Ongoing',       'key'=>'ongoing',   'icon'=>'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z',                                                                                                                                                                                                     'color'=>'green'],
                    ['label'=>'Completed',     'key'=>'completed', 'icon'=>'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',                                                                                                                                                                                                                                                                      'color'=>'purple'],
                ];
                foreach ($cards as $c): ?>
                <a href="?status=<?php echo $c['key']; ?>" class="oe-summary-card oe-color-<?php echo $c['color']; ?><?php echo ($filter === $c['key'] || ($c['key']==='all' && $filter==='all' && !$search)) ? ' oe-summary-active' : ''; ?>">
                    <div class="oe-summary-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo $c['icon']; ?>" />
                        </svg>
                    </div>
                    <div class="oe-summary-info">
                        <div class="oe-summary-value"><?php echo $counts[$c['key']]; ?></div>
                        <div class="oe-summary-label"><?php echo $c['label']; ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- ── Toolbar ── -->
            <div class="oe-toolbar">
                <form method="GET" action="" class="oe-search-form" id="search-form">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter); ?>">
                    <div class="oe-search-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input type="text" name="search" id="search-input" placeholder="Search events by title or location…" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                        <?php if ($search): ?>
                        <a href="?status=<?php echo htmlspecialchars($filter); ?>" class="oe-clear-search" title="Clear search">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
                <div class="oe-filter-tabs">
                    <?php
                    $tabs = ['all'=>'All', 'upcoming'=>'Upcoming', 'ongoing'=>'Ongoing', 'completed'=>'Completed', 'cancelled'=>'Cancelled'];
                    foreach ($tabs as $key => $label):
                        $active = ($filter === $key || ($key === 'all' && !in_array($filter, $validStatuses)));
                    ?>
                    <a href="?status=<?php echo $key; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                       class="oe-filter-tab<?php echo $active ? ' active' : ''; ?>">
                        <?php echo $label; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ── Events Grid / Empty State ── -->
            <?php if (empty($events)): ?>
            <div class="oe-empty">
                <div class="oe-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <h3>No events found</h3>
                <p><?php echo $search ? 'No events match your search. Try different keywords.' : 'There are no events yet. Create the first one!'; ?></p>
                <?php if (canAdmin($user)): ?>
                <button class="btn-primary" onclick="openCreateModal()" style="margin-top:var(--space-4);width:auto;padding:var(--space-3) var(--space-6);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Create Event
                </button>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="oe-grid">
                <?php foreach ($events as $ev): ?>
                <?php
                    $isPast         = strtotime($ev['event_date']) < strtotime('today');
                    $startDateLabel = date('M j, Y', strtotime($ev['event_date']));
                    $endDate        = !empty($ev['end_event_date']) ? $ev['end_event_date'] : null;
                    $dateLabel      = $endDate
                        ? $startDateLabel . ' – ' . date('M j, Y', strtotime($endDate))
                        : $startDateLabel;
                    $timeLabel      = date('g:i A', strtotime($ev['start_time'])) . ' – ' . date('g:i A', strtotime($ev['end_time']));
                    // Always show real-time computed status
                    $liveStatus     = computeStatus($ev['event_date'], $ev['end_event_date'] ?? null, $ev['start_time'], $ev['end_time'], $ev['status']);
                ?>
                <div class="oe-card" id="event-card-<?php echo $ev['id']; ?>">
                    <div class="oe-card-header">
                        <?php echo statusBadge($liveStatus); ?>
                        <?php if (canAdmin($user)): ?>
                        <div class="oe-card-actions">
                            <button class="oe-action-btn" title="Edit event"
                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($ev), ENT_QUOTES); ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                </svg>
                            </button>
                            <button class="oe-action-btn oe-action-danger" title="Delete event"
                                onclick="confirmDelete(<?php echo $ev['id']; ?>, '<?php echo htmlspecialchars(addslashes($ev['title'])); ?>')">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="oe-card-body">
                        <h3 class="oe-card-title"><?php echo htmlspecialchars($ev['title']); ?></h3>
                        <?php if (!empty($ev['description'])): ?>
                        <p class="oe-card-desc"><?php echo htmlspecialchars(mb_strimwidth($ev['description'], 0, 100, '…')); ?></p>
                        <?php endif; ?>

                        <div class="oe-card-meta">
                            <div class="oe-meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                <span><?php echo $dateLabel; ?></span>
                            </div>
                            <div class="oe-meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <span><?php echo $timeLabel; ?></span>
                            </div>
                            <div class="oe-meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                <span><?php echo htmlspecialchars($ev['location']); ?></span>
                            </div>
                            <?php if ($ev['max_volunteers']): ?>
                            <div class="oe-meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                                <span>Max <?php echo $ev['max_volunteers']; ?> volunteers</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="oe-card-footer">
                        <span class="oe-created-by">Added by <?php echo htmlspecialchars($ev['created_by_name']); ?></span>
                        <span class="oe-created-date"><?php echo date('M j', strtotime($ev['created_at'])); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- /page-content -->
    </main>
</div><!-- /dashboard-layout -->

<!-- ═══ CREATE / EDIT MODAL ═══ -->
<div class="modal-overlay" id="event-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modal-title">New Outreach Event</h3>
            <button class="modal-close" onclick="closeModal()" aria-label="Close">&times;</button>
        </div>
        <form id="event-form" method="POST" action="../handlers/outreach_event_handler.php" novalidate>
            <input type="hidden" name="action" id="form-action" value="create">
            <input type="hidden" name="event_id" id="form-event-id" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="f-title">Event Title <span class="req">*</span></label>
                    <input type="text" class="form-input modal-input" id="f-title" name="title" placeholder="e.g. Barangay Health Mission" required maxlength="255">
                    <span class="field-error" id="err-title"></span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="f-description">Description</label>
                    <textarea class="form-input form-textarea modal-input" id="f-description" name="description" placeholder="Brief description of this event…" rows="3"></textarea>
                    <span class="field-error" id="err-description"></span>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="f-barangay-city">Barangay, City <span class="req">*</span></label>
                        <input type="text" class="form-input modal-input" id="f-barangay-city" name="barangay_city" placeholder="e.g. Tetuan, Zamboanga City" required maxlength="150">
                        <span class="field-error" id="err-barangay-city"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="f-venue">Venue <span class="req">*</span></label>
                        <input type="text" class="form-input modal-input" id="f-venue" name="venue" placeholder="e.g. Covered Court" required maxlength="100">
                        <span class="field-error" id="err-venue"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="f-date">Start Date <span class="req">*</span></label>
                        <input type="date" class="form-input modal-input" id="f-date" name="event_date" required max="9999-12-31">
                        <span class="field-error" id="err-date"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="f-end-date">End Date <span style="font-weight:400;color:var(--slate-400)">(optional)</span></label>
                        <input type="date" class="form-input modal-input" id="f-end-date" name="end_event_date" max="9999-12-31">
                        <span class="field-error" id="err-end-date"></span>
                    </div>
                </div>
                <!-- Status is computed automatically; only allow marking as cancelled -->
                <div class="form-group" style="margin-top:var(--space-2);">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; padding:var(--space-3) var(--space-4); background:var(--slate-50); border:1.5px solid var(--slate-200); border-radius:var(--radius-md); transition:all 0.2s;" id="cancel-label">
                        <input type="checkbox" id="f-cancelled" name="is_cancelled" value="1"
                               style="width:16px;height:16px;accent-color:var(--rose-600);cursor:pointer;">
                        <span style="font-size:0.85rem; font-weight:600; color:var(--slate-600);">Mark as Cancelled</span>
                        <span style="font-size:0.78rem; color:var(--slate-400); margin-left:auto;">Status is otherwise automatic</span>
                    </label>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="f-start">Start Time <span class="req">*</span></label>
                        <input type="time" class="form-input modal-input" id="f-start" name="start_time" required>
                        <span class="field-error" id="err-start"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="f-end">End Time <span class="req">*</span></label>
                        <input type="time" class="form-input modal-input" id="f-end" name="end_time" required>
                        <span class="field-error" id="err-end"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="f-volunteers">Max Volunteers <span style="font-weight:400;color:var(--slate-400)">(optional)</span></label>
                    <input type="number" class="form-input modal-input" id="f-volunteers" name="max_volunteers" placeholder="Leave blank for unlimited" min="1" max="9999">
                    <span class="field-error" id="err-volunteers"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary btn-modal-submit" id="modal-submit-btn">
                    <span class="btn-text">Create Event</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ DELETE CONFIRM MODAL ═══ -->
<div class="modal-overlay" id="delete-modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
    <div class="modal-box modal-box-sm">
        <div class="modal-header">
            <h3 id="delete-modal-title">Delete Event</h3>
            <button class="modal-close" onclick="closeDeleteModal()" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="delete-confirm-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <p class="delete-confirm-text">Are you sure you want to delete <strong id="delete-event-name"></strong>? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <form method="POST" action="../handlers/outreach_event_handler.php" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="event_id" id="delete-event-id">
                <button type="submit" class="btn-danger" id="confirm-delete-btn">Delete</button>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/outreach_events.js?v=<?php echo time(); ?>"></script>
</body>
</html>
