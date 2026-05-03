<?php
/**
 * Settings Page
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../includes/session.php';
requireLogin();

$user = getCurrentUser();
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Settings — Medical Outreach Tracker">
    <title>Settings — Medical Outreach Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=3">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏥</text></svg>">
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
                <a href="settings.php" class="nav-link active" id="nav-settings">
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
                    <h2>Settings</h2>
                    <p>Manage your account layout and security preferences.</p>
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
                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" style="margin-bottom: var(--space-6);">
                        <?php if ($flash['type'] === 'error'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="settings-grid" style="max-width: 760px;">
                    <!-- Profile Card -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                                <h3>Profile Information</h3>
                                <p>Update your personal details and profile picture.</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="../handlers/settings_profile_handler.php" method="POST" enctype="multipart/form-data">

                                <!-- Avatar -->
                                <div class="avatar-upload-group" style="margin-bottom:var(--space-6);">
                                    <div class="avatar-preview-container">
                                        <?php if (!empty($user['avatar'])): ?>
                                            <img src="../<?php echo htmlspecialchars($user['avatar']); ?>" id="avatar-preview" class="avatar-preview-image" alt="Profile avatar">
                                        <?php else: ?>
                                            <div id="avatar-preview" class="avatar-preview-initials"><?php echo $initials; ?></div>
                                        <?php endif; ?>
                                        <div class="avatar-overlay">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                                        </div>
                                        <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/webp" class="hidden-file-input">
                                    </div>
                                    <div class="avatar-info">
                                        <label for="avatar-input" class="btn-outline btn-sm" style="cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                            Upload Photo
                                        </label>
                                        <span class="help-text">JPG, PNG or WebP · Max 2MB</span>
                                    </div>
                                </div>

                                <!-- Name row -->
                                <div class="form-row-3">
                                    <div class="form-group">
                                        <label class="form-label" for="first_name">First Name</label>
                                        <div class="input-wrapper">
                                            <span class="input-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                            </span>
                                            <input type="text" class="form-input" id="first_name" name="first_name"
                                                   value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="middle_name">Middle Name <span style="font-size:0.75rem; color:var(--slate-400); font-weight:normal; text-transform:none;">(Optional)</span></label>
                                        <div class="input-wrapper">
                                            <span class="input-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                            </span>
                                            <input type="text" class="form-input" id="middle_name" name="middle_name"
                                                   value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="last_name">Last Name</label>
                                        <div class="input-wrapper">
                                            <span class="input-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                            </span>
                                            <input type="text" class="form-input" id="last_name" name="last_name"
                                                   value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="form-group">
                                    <label class="form-label" for="email">Email Address</label>
                                    <div class="input-wrapper">
                                        <span class="input-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                                        </span>
                                        <input type="email" class="form-input" id="email" name="email"
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>

                                <!-- Role (read-only) -->
                                <div class="form-group">
                                    <label class="form-label">Role</label>
                                    <div style="display:inline-flex; align-items:center; gap:8px; padding:var(--space-3) var(--space-4); background:var(--slate-50); border:1.5px solid var(--slate-200); border-radius:var(--radius-md); font-size:0.9rem; color:var(--slate-600);">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;color:var(--slate-400);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                                        <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                        <span style="font-size:0.72rem; color:var(--slate-400); margin-left:4px;">(cannot be changed here)</span>
                                    </div>
                                </div>

                                <div style="border-top:1px solid var(--slate-100); margin-top:var(--space-6); padding-top:var(--space-5); display:flex; justify-content:flex-end;">
                                    <button type="submit" class="btn-primary" style="width:auto; padding:var(--space-2) var(--space-8);">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ── Security Card ── -->
                    <div class="settings-card">
                        <div class="settings-card-header" style="display:flex; align-items:center; gap:var(--space-4);">
                            <div style="width:36px; height:36px; background:var(--amber-50); border:1.5px solid var(--amber-100); border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:center; color:var(--amber-600); flex-shrink:0;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                            </div>
                            <div>
                                <h3>Change Password</h3>
                                <p>Use a long, random password to keep your account secure.</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="../handlers/settings_password_handler.php" method="POST" id="pw-form" onsubmit="return validatePwForm()">

                                <!-- Current Password -->
                                <div class="form-group">
                                    <label class="form-label" for="current_password">Current Password</label>
                                    <div class="input-wrapper" style="position:relative;">
                                        <span class="input-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                        </span>
                                        <input type="password" class="form-input" id="current_password" name="current_password" placeholder="Enter current password" required>
                                        <button type="button" class="password-toggle" onclick="toggleField('current_password')" tabindex="-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:17px;height:17px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- New Password -->
                                <div class="form-group">
                                    <label class="form-label" for="new_password">New Password</label>
                                    <div class="input-wrapper" style="position:relative;">
                                        <span class="input-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg>
                                        </span>
                                        <input type="password" class="form-input" id="new_password" name="new_password"
                                               placeholder="Min. 8 characters" required minlength="8">
                                        <button type="button" class="password-toggle" onclick="toggleField('new_password')" tabindex="-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:17px;height:17px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </button>
                                    </div>
                                    <span class="field-error" id="err-new-pw"></span>
                                </div>

                                <!-- Confirm Password -->
                                <div class="form-group">
                                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                                    <div class="input-wrapper" style="position:relative;">
                                        <span class="input-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                                        </span>
                                        <input type="password" class="form-input" id="confirm_password" name="confirm_password"
                                               placeholder="Repeat new password" required minlength="8">
                                        <button type="button" class="password-toggle" onclick="toggleField('confirm_password')" tabindex="-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:17px;height:17px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </button>
                                    </div>
                                    <span class="field-error" id="err-confirm-pw"></span>
                                </div>

                                <div style="border-top:1px solid var(--slate-100); margin-top:var(--space-6); padding-top:var(--space-5); display:flex; justify-content:flex-end;">
                                    <button type="submit" class="btn-primary" style="width:auto; padding:var(--space-2) var(--space-8);">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div><!-- /settings-grid --> </div>
            </div>
        </main>
    </div>

    <script>
        // ── Avatar upload preview ────────────────────────────────
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreview = document.getElementById('avatar-preview');

        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (avatarPreview.tagName.toLowerCase() === 'div') {
                        const img = document.createElement('img');
                        img.id = 'avatar-preview';
                        img.className = 'avatar-preview-image';
                        img.src = e.target.result;
                        img.alt = 'Profile avatar';
                        avatarPreview.parentNode.replaceChild(img, avatarPreview);
                    } else {
                        avatarPreview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // ── Show / hide password toggle ──────────────────────────
        function toggleField(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // ── Password form client-side validation ─────────────────
        function validatePwForm() {
            const newPw  = document.getElementById('new_password').value;
            const confPw = document.getElementById('confirm_password').value;
            const errNew  = document.getElementById('err-new-pw');
            const errConf = document.getElementById('err-confirm-pw');
            errNew.textContent = '';
            errConf.textContent = '';
            let ok = true;
            if (newPw.length < 8) {
                errNew.textContent = 'Password must be at least 8 characters.';
                ok = false;
            }
            if (newPw !== confPw) {
                errConf.textContent = 'Passwords do not match.';
                ok = false;
            }
            return ok;
        }

        // ── Auto-dismiss flash alert ─────────────────────────────
        const flash = document.querySelector('.alert');
        if (flash) {
            setTimeout(() => {
                flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                flash.style.opacity = '0';
                flash.style.transform = 'translateY(-10px)';
                setTimeout(() => flash.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>
