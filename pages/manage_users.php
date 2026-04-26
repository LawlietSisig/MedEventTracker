<?php
/**
 * Manage Users Page
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser();
if ($user['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
$flash = getFlash();

$conn = getConnection();
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$usersList = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users — Medical Outreach Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏥</text></svg>">
    <style>
        .mu-actions { display: flex; gap: 6px; }
        .mu-btn { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border: none; border-radius: var(--radius-md); font-size: 0.78rem; font-weight: 700; font-family: var(--font-display); cursor: pointer; text-transform: uppercase; letter-spacing: 0.04em; transition: all var(--transition-fast); }
        .mu-btn-edit   { background: var(--blue-50); color: var(--blue-700); }
        .mu-btn-edit:hover { background: var(--blue-100); }
        .mu-btn-toggle { background: var(--amber-50); color: var(--amber-700); }
        .mu-btn-toggle:hover { background: var(--amber-100); }
        .mu-btn-delete { background: #fff1f2; color: var(--rose-600); }
        .mu-btn-delete:hover { background: #fecdd3; }
        .mu-btn-reset { background: #f0fdf4; color: var(--green-700); }
        .mu-btn-reset:hover { background: var(--green-100); }
        .mu-btn svg { width: 13px; height: 13px; }
        .pw-toggle { position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--slate-400); padding:0; }
        .pw-toggle:hover { color:var(--slate-600); }
        .pw-toggle svg { width:16px; height:16px; }
    </style>
</head>
<body>
<div class="dashboard-layout">

    <!-- SIDEBAR -->
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
            <a href="dashboard.php" class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                Dashboard
            </a>
            <a href="outreach_events.php" class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                Outreach Events
            </a>
            <a href="patients.php" class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                Patients
            </a>
            <div class="nav-section-title">Management</div>
            <a href="volunteers.php" class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                Volunteers
            </a>
            <a href="reports.php" class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                Reports
            </a>
            <div class="nav-section-title">Admin</div>
            <a href="manage_users.php" class="nav-link active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                Manage Users
            </a>
            <div class="nav-section-title">Personal</div>
            <a href="settings.php" class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.108 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
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

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <h2>Manage Users</h2>
                <p>View and manage registered accounts</p>
            </div>
            <div class="top-bar-right">
                <a href="../handlers/logout_handler.php" class="btn-outline" id="logout-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                    Logout
                </a>
            </div>
        </div>

        <div class="page-content">

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" style="margin-bottom:var(--space-6);">
                    <span><?php echo htmlspecialchars($flash['message']); ?></span>
                </div>
            <?php endif; ?>

            <div class="mu-card">
                <table class="mu-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usersList as $u):
                            $uInitials  = strtoupper(substr($u['first_name'], 0, 1) . substr($u['last_name'], 0, 1));
                            $roleClass  = 'mu-role-' . strtolower($u['role']);
                            $isActive   = (bool)($u['is_active'] ?? 1);
                            $isSelf     = ((int)$u['id'] === (int)$user['id']);
                        ?>
                        <tr>
                            <td>
                                <div class="mu-user-cell">
                                    <?php if (!empty($u['avatar'])): ?>
                                        <img src="../<?php echo htmlspecialchars($u['avatar']); ?>" class="mu-avatar" alt="Avatar">
                                    <?php else: ?>
                                        <div class="mu-avatar"><?php echo htmlspecialchars($uInitials); ?></div>
                                    <?php endif; ?>
                                    <div class="mu-info">
                                        <span class="mu-name"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></span>
                                        <span class="mu-email"><?php echo htmlspecialchars($u['email']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="mu-role-badge <?php echo $roleClass; ?>"><?php echo htmlspecialchars($u['role']); ?></span>
                            </td>
                            <td>
                                <div class="mu-status <?php echo $isActive ? 'mu-status-active' : 'mu-status-inactive'; ?>">
                                    <span class="mu-status-dot"></span>
                                    <?php echo $isActive ? 'Active' : 'Inactive'; ?>
                                </div>
                            </td>
                            <td style="color:var(--slate-500); font-size:0.9rem;">
                                <?php echo date('M j, Y', strtotime($u['created_at'])); ?>
                            </td>
                            <td>
                                <?php if ($isSelf): ?>
                                    <span style="font-size:0.8rem; color:var(--slate-400);">You</span>
                                <?php else: ?>
                                <div class="mu-actions">
                                    <!-- Reset Password -->
                                    <button class="mu-btn mu-btn-reset"
                                            onclick="openResetModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg>
                                        Reset Password
                                    </button>
                                    <!-- Edit Role -->
                                    <button class="mu-btn mu-btn-edit"
                                            onclick="openEditModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>', '<?php echo $u['role']; ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                        Edit Role
                                    </button>
                                    <!-- Toggle Status -->
                                    <form method="POST" action="../handlers/manage_users_handler.php" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="mu-btn mu-btn-toggle">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9" /></svg>
                                            <?php echo $isActive ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                    <!-- Delete -->
                                    <button class="mu-btn mu-btn-delete"
                                            onclick="openDeleteModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        Delete
                                    </button>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /page-content -->
    </main>
</div><!-- /dashboard-layout -->

<!-- ══ Edit Role Modal ══ -->
<div class="modal-overlay" id="editModal" style="display:none;" onclick="if(event.target===this)closeEditModal()">
    <div class="modal-box modal-box-sm">
        <div class="modal-header">
            <h3 id="editModalTitle">Edit Role</h3>
            <button class="modal-close" onclick="closeEditModal()">✕</button>
        </div>
        <form method="POST" action="../handlers/manage_users_handler.php">
            <input type="hidden" name="action" value="edit_role">
            <input type="hidden" name="user_id" id="editUserId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="editRole">Role</label>
                    <div class="input-wrapper">
                        <select class="form-input form-select modal-input" name="role" id="editRole" required>
                            <option value="volunteer">Volunteer</option>
                            <option value="coordinator">Coordinator</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto; padding: var(--space-2) var(--space-6);">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Delete Confirm Modal ══ -->
<div class="modal-overlay" id="deleteModal" style="display:none;" onclick="if(event.target===this)closeDeleteModal()">
    <div class="modal-box modal-box-sm">
        <div class="modal-header">
            <h3>Delete Account</h3>
            <button class="modal-close" onclick="closeDeleteModal()">✕</button>
        </div>
        <form method="POST" action="../handlers/manage_users_handler.php">
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" id="deleteUserId">
            <div class="modal-body">
                <div class="delete-confirm-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                </div>
                <p class="delete-confirm-text" style="text-align:center; margin-top:var(--space-4);">
                    Are you sure you want to permanently delete <strong id="deleteUserName"></strong>? This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-danger">Delete Account</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Reset Password Modal ══ -->
<div class="modal-overlay" id="resetModal" style="display:none;" onclick="if(event.target===this)closeResetModal()">
    <div class="modal-box modal-box-sm">
        <div class="modal-header">
            <h3 id="resetModalTitle">Reset Password</h3>
            <button class="modal-close" onclick="closeResetModal()">✕</button>
        </div>
        <form method="POST" action="../handlers/manage_users_handler.php" onsubmit="return validateResetForm()">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" id="resetUserId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="newPassword">New Password</label>
                    <div class="input-wrapper" style="position:relative;">
                        <input type="password" class="form-input modal-input" id="newPassword" name="new_password"
                               placeholder="Min. 8 characters" required minlength="8">
                        <button type="button" class="pw-toggle" onclick="togglePw('newPassword', this)" tabindex="-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                    </div>
                    <span class="field-error" id="resetPwError"></span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirm Password</label>
                    <div class="input-wrapper" style="position:relative;">
                        <input type="password" class="form-input modal-input" id="confirmPassword" name="confirm_password"
                               placeholder="Repeat new password" required>
                        <button type="button" class="pw-toggle" onclick="togglePw('confirmPassword', this)" tabindex="-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                    </div>
                    <span class="field-error" id="resetConfirmError"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeResetModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto; padding:var(--space-2) var(--space-6);">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, role) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editModalTitle').textContent = 'Edit Role — ' + name;
    document.getElementById('editRole').value = role;
    const m = document.getElementById('editModal');
    m.style.display = 'flex';
    requestAnimationFrame(() => m.querySelector('.modal-box').classList.add('open'));
}
function closeEditModal() {
    const m = document.getElementById('editModal');
    m.querySelector('.modal-box').classList.remove('open');
    setTimeout(() => m.style.display = 'none', 220);
}
function openDeleteModal(id, name) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('deleteUserName').textContent = name;
    const m = document.getElementById('deleteModal');
    m.style.display = 'flex';
    requestAnimationFrame(() => m.querySelector('.modal-box').classList.add('open'));
}
function closeDeleteModal() {
    const m = document.getElementById('deleteModal');
    m.querySelector('.modal-box').classList.remove('open');
    setTimeout(() => m.style.display = 'none', 220);
}
function openResetModal(id, name) {
    document.getElementById('resetUserId').value = id;
    document.getElementById('resetModalTitle').textContent = 'Reset Password — ' + name;
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('resetPwError').textContent = '';
    document.getElementById('resetConfirmError').textContent = '';
    const m = document.getElementById('resetModal');
    m.style.display = 'flex';
    requestAnimationFrame(() => m.querySelector('.modal-box').classList.add('open'));
    setTimeout(() => document.getElementById('newPassword').focus(), 250);
}
function closeResetModal() {
    const m = document.getElementById('resetModal');
    m.querySelector('.modal-box').classList.remove('open');
    setTimeout(() => m.style.display = 'none', 220);
}
function validateResetForm() {
    const pw  = document.getElementById('newPassword').value;
    const cpw = document.getElementById('confirmPassword').value;
    let ok = true;
    document.getElementById('resetPwError').textContent = '';
    document.getElementById('resetConfirmError').textContent = '';
    if (pw.length < 8) {
        document.getElementById('resetPwError').textContent = 'Password must be at least 8 characters.';
        ok = false;
    }
    if (pw !== cpw) {
        document.getElementById('resetConfirmError').textContent = 'Passwords do not match.';
        ok = false;
    }
    return ok;
}
function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.style.color = isHidden ? 'var(--green-600)' : 'var(--slate-400)';
}
const alert = document.querySelector('.alert');
if (alert) setTimeout(() => alert.style.opacity = '0', 3500);
</script>
</body>
</html>
