<?php
/**
 * Volunteers Page
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user    = getCurrentUser();
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
$flash   = getFlash();

// ── Fetch Volunteers ─────────────────────────────────────────────────────────────
$conn   = getConnection();
$search = trim($_GET['search'] ?? '');

$where  = ['1=1'];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = '(v.first_name LIKE ? OR v.last_name LIKE ? OR v.profession LIKE ?)';
    $like     = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}

$whereSQL = implode(' AND ', $where);
$sql = "SELECT v.*, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
        FROM volunteers v
        LEFT JOIN users u ON u.id = v.created_by
        WHERE {$whereSQL}
        ORDER BY v.last_name ASC, v.first_name ASC";

$stmt   = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$volunteers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

function canAdmin(array $user): bool {
    return in_array($user['role'], ['admin', 'coordinator']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Volunteers — Medical Outreach Tracker">
    <title>Volunteers — Medical Outreach Tracker</title>
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
            <a href="volunteers.php" class="nav-link active" id="nav-volunteers">
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

    <!-- ═══ MAIN CONTENT ═══ -->
    <main class="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <h2>Volunteers Directory</h2>
                <p>Manage medical staff and outreach volunteers</p>
            </div>
            <div class="top-bar-right">
                <a href="../handlers/logout_handler.php" class="btn-outline" id="logout-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>

        <div class="page-content">

            <!-- ── Flash Alert ── -->
            <?php if ($flash): ?>
            <div class="flash-alert flash-<?php echo $flash['type']; ?>" id="flash-alert">
                <span><?php echo htmlspecialchars($flash['message']); ?></span>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;">&times;</button>
            </div>
            <?php endif; ?>

            <!-- ── Controls ── -->
            <div class="oe-controls" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: var(--space-6); flex-wrap:wrap; gap: var(--space-4);">
                <form method="GET" action="" class="oe-search-form" id="search-form" style="display:flex; flex:1; max-width: 400px; position:relative;">
                    <span style="position:absolute; left:var(--space-4); top:50%; transform:translateY(-50%); color:var(--slate-400);">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </span>
                    <input type="text" class="form-input" name="search" id="search-input" placeholder="Search by name or profession..." value="<?php echo htmlspecialchars($search); ?>" style="padding-left:2.8rem; border-radius:var(--radius-full);">
                </form>

                <?php if (canAdmin($user)): ?>
                <button class="btn-primary" id="btn-open-create" onclick="openCreateModal()" style="display:flex; align-items:center; gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Volunteer
                </button>
                <?php endif; ?>
            </div>

            <!-- ── Volunteer Grid ── -->
            <?php if (empty($volunteers)): ?>
            <div class="oe-empty" style="text-align:center; padding:var(--space-12) 0; color:var(--slate-500);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:64px;height:64px;margin:0 auto var(--space-4);color:var(--slate-300);">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                <h3>No volunteers found</h3>
                <p>Add your first volunteer to start building your outreach team.</p>
                <?php if (canAdmin($user)): ?>
                <button class="btn-primary" onclick="openCreateModal()" style="margin-top:var(--space-4);width:auto;padding:var(--space-3) var(--space-6);">Add Volunteer</button>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="oe-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:var(--space-6);">
                <?php foreach ($volunteers as $v): ?>
                <div class="oe-card" style="background:white; border-radius:var(--radius-xl); border:1.5px solid var(--slate-200); overflow:hidden; box-shadow:var(--shadow-sm); display:flex; flex-direction:column; transition:all var(--transition-base);">
                    <div style="padding:var(--space-5); border-bottom:1px solid var(--slate-100);">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:var(--space-2);">
                            <h3 style="font-family:var(--font-display); font-size:1.15rem; font-weight:700; color:var(--slate-900);"><?php echo htmlspecialchars($v['first_name'] . ' ' . $v['last_name']); ?></h3>
                            <span style="font-size:0.8rem; font-weight:600; color:<?php echo $v['status'] === 'active' ? 'var(--success-600)' : 'var(--slate-400)'; ?>; background:<?php echo $v['status'] === 'active' ? 'rgba(34, 197, 94, 0.1)' : 'var(--slate-100)'; ?>; padding:2px 8px; border-radius:var(--radius-full);">
                                <?php echo ucfirst(htmlspecialchars($v['status'])); ?>
                            </span>
                        </div>
                        <div style="font-size:0.9rem; color:var(--slate-500); display:flex; flex-direction:column; gap:4px;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 .621-.504 1.125-1.125 1.125H4.875c-.621 0-1.125-.504-1.125-1.125v-4.25m16.5 0a2.25 2.25 0 0 0-2.25-2.25H4.875a2.25 2.25 0 0 0-2.25 2.25m16.5 0V10.125c0-.621-.504-1.125-1.125-1.125H4.875c-.621 0-1.125.504-1.125 1.125v4.025M12 9V3m0 0 3 3m-3-3-3 3" /></svg>
                                <span>Profession: <strong><?php echo htmlspecialchars($v['profession']); ?></strong></span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                                <span><?php echo htmlspecialchars($v['email']); ?></span>
                            </div>
                            <?php if ($v['contact_number']): ?>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.896-1.596-5.265-3.965-6.861-6.861l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                                <span><?php echo htmlspecialchars($v['contact_number']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($v['skills_notes']): ?>
                    <div style="padding:var(--space-4) var(--space-5); background:var(--slate-50); flex:1; font-size:0.85rem; color:var(--slate-600); border-bottom:1px solid var(--slate-100);">
                        <strong>Skills / Notes:</strong><br>
                        <?php echo nl2br(htmlspecialchars($v['skills_notes'])); ?>
                    </div>
                    <?php else: ?>
                    <div style="flex:1;"></div>
                    <?php endif; ?>
                    <div style="padding:var(--space-3) var(--space-5); display:flex; justify-content:space-between; align-items:center; background:white;">
                        <span style="font-size:0.75rem; color:var(--slate-400);">Joined <?php echo date('M j, Y', strtotime($v['created_at'])); ?></span>
                        <?php if (canAdmin($user)): ?>
                        <div style="display:flex; gap:8px;">
                            <button class="oe-action-btn oe-action-edit" title="Edit volunteer"
                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($v), ENT_QUOTES); ?>)"
                                style="background:none; border:none; cursor:pointer; color:var(--primary-600); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:background 0.2s;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1-2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                            </button>
                            <button class="oe-action-btn oe-action-danger" title="Delete volunteer"
                                onclick="confirmDelete(<?php echo $v['id']; ?>, '<?php echo htmlspecialchars(addslashes($v['first_name'] . ' ' . $v['last_name'])); ?>')"
                                style="background:none; border:none; cursor:pointer; color:var(--danger-500); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:background 0.2s;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- /page-content -->
    </main>

    <!-- ═══ CREATE / EDIT MODAL ═══ -->
    <div class="modal-overlay" id="volunteer-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modal-title">New Volunteer Profile</h3>
                <button class="modal-close" onclick="closeModal()" aria-label="Close">&times;</button>
            </div>
            <form id="volunteer-form" method="POST" action="../handlers/volunteer_handler.php" novalidate>
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="volunteer_id" id="form-volunteer-id" value="">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="f-fname">First Name <span class="req">*</span></label>
                            <input type="text" class="form-input modal-input" id="f-fname" name="first_name" required maxlength="100">
                            <span class="field-error" id="err-fname"></span>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="f-lname">Last Name <span class="req">*</span></label>
                            <input type="text" class="form-input modal-input" id="f-lname" name="last_name" required maxlength="100">
                            <span class="field-error" id="err-lname"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="f-email">Email Address <span class="req">*</span></label>
                        <input type="email" class="form-input modal-input" id="f-email" name="email" required maxlength="255">
                        <span class="field-error" id="err-email"></span>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="f-contact">Contact Number</label>
                            <input type="tel" class="form-input modal-input" id="f-contact" name="contact_number">
                            <span class="field-error" id="err-contact"></span>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="f-profession">Profession / Role <span class="req">*</span></label>
                            <input type="text" class="form-input modal-input" id="f-profession" name="profession" required placeholder="e.g. Doctor, Nurse, Support">
                            <span class="field-error" id="err-profession"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="f-status">Status <span class="req">*</span></label>
                        <select class="form-input form-select modal-input" id="f-status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="f-notes">Skills / Notes</label>
                        <textarea class="form-input form-textarea modal-input" id="f-notes" name="skills_notes" rows="3" placeholder="Special certifications, availability, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary btn-modal-submit" id="modal-submit-btn">
                        <span class="btn-text">Add Volunteer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ DELETE CONFIRM MODAL ═══ -->
    <div class="modal-overlay" id="delete-modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
        <div class="modal-box modal-box-sm">
            <div class="modal-header" style="border-bottom:none; padding-bottom:0;">
                <h3 id="delete-modal-title" style="color:var(--danger-600); display:flex; align-items:center; gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" /></svg>
                    Confirm Deletion
                </h3>
            </div>
            <div class="modal-body">
                <p style="color:var(--slate-600); line-height:1.5; margin-bottom:var(--space-2);">Are you sure you want to remove <strong id="delete-volunteer-name" style="color:var(--slate-900);"></strong> from the volunteers list?</p>
                <p style="color:var(--slate-500); font-size:0.85rem;">This action will permanently delete their profile from the records.</p>
            </div>
            <div class="modal-footer" style="border-top:none; background:transparent;">
                <button type="button" class="btn-outline" onclick="closeDeleteModal()">Cancel</button>
                <form method="POST" action="../handlers/volunteer_handler.php" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="volunteer_id" id="delete-volunteer-id">
                    <button type="submit" class="btn-danger" id="confirm-delete-btn">Delete Volunteer</button>
                </form>
            </div>
        </div>
    </div>

</div><!-- /dashboard-layout -->

<script src="../assets/js/volunteers.js?v=<?php echo time(); ?>"></script>
</body>
</html>
