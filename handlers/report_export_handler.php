<?php
/**
 * Report Export Handler
 * Generates downloadable CSV reports for Events, Patients, Volunteers, and Summary
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$type = $_GET['type'] ?? '';
$allowed = ['events', 'patients', 'volunteers', 'summary'];

if (!in_array($type, $allowed)) {
    http_response_code(400);
    die('Invalid report type.');
}

$conn = getConnection();
$filename = 'report_' . $type . '_' . date('Y-m-d') . '.csv';

// ── Output headers for CSV download ──────────────────────────────────────────
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Add UTF-8 BOM so Excel opens it correctly
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

// ── Events Report ─────────────────────────────────────────────────────────────
if ($type === 'events') {
    fputcsv($out, [
        'ID', 'Title', 'Description', 'Location',
        'Event Date', 'End Date', 'Start Time', 'End Time',
        'Status', 'Max Volunteers', 'Created By', 'Created At'
    ]);

    $sql = "
        SELECT
            e.id,
            e.title,
            COALESCE(e.description, '') AS description,
            e.location,
            e.event_date,
            COALESCE(e.end_event_date, '') AS end_event_date,
            e.start_time,
            e.end_time,
            e.status,
            COALESCE(e.max_volunteers, '') AS max_volunteers,
            CONCAT(u.first_name, ' ', u.last_name) AS created_by,
            e.created_at
        FROM outreach_events e
        LEFT JOIN users u ON e.created_by = u.id
        ORDER BY e.event_date DESC, e.created_at DESC
    ";
    $res = $conn->query($sql);
    while ($row = $res->fetch_row()) {
        fputcsv($out, $row);
    }

// ── Patients Report ───────────────────────────────────────────────────────────
} elseif ($type === 'patients') {
    fputcsv($out, [
        'ID', 'First Name', 'Last Name', 'Date of Birth', 'Age',
        'Gender', 'Contact Number', 'Address', 'Blood Type',
        'Medical Notes', 'Registered By', 'Registered At'
    ]);

    $sql = "
        SELECT
            p.id,
            p.first_name,
            p.last_name,
            p.dob,
            TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) AS age,
            p.gender,
            COALESCE(p.contact_number, '') AS contact_number,
            COALESCE(p.address, '')         AS address,
            COALESCE(p.blood_type, '')       AS blood_type,
            COALESCE(p.medical_notes, '')   AS medical_notes,
            CONCAT(u.first_name, ' ', u.last_name) AS registered_by,
            p.created_at
        FROM patients p
        LEFT JOIN users u ON p.created_by = u.id
        ORDER BY p.created_at DESC
    ";
    $res = $conn->query($sql);
    while ($row = $res->fetch_row()) {
        fputcsv($out, $row);
    }

// ── Volunteers Report ─────────────────────────────────────────────────────────
} elseif ($type === 'volunteers') {
    fputcsv($out, [
        'ID', 'First Name', 'Last Name', 'Email',
        'Contact Number', 'Profession', 'Address', 'Registered At'
    ]);

    $sql = "
        SELECT
            id,
            first_name,
            last_name,
            COALESCE(email, '')          AS email,
            COALESCE(contact_number, '') AS contact_number,
            COALESCE(profession, '')     AS profession,
            COALESCE(address, '')        AS address,
            created_at
        FROM volunteers
        ORDER BY created_at DESC
    ";
    $res = $conn->query($sql);
    while ($row = $res->fetch_row()) {
        fputcsv($out, $row);
    }

// ── Summary Report ────────────────────────────────────────────────────────────
} elseif ($type === 'summary') {
    // Header
    fputcsv($out, ['Medical Outreach Tracker — Summary Report']);
    fputcsv($out, ['Generated:', date('F j, Y \a\t g:i A')]);
    fputcsv($out, []);

    // ── Overview ──────────────────────────────────────────────────────────────
    fputcsv($out, ['=== OVERVIEW ===']);
    fputcsv($out, ['Metric', 'Count']);

    $totalEvents    = (int) $conn->query("SELECT COUNT(*) FROM outreach_events")->fetch_row()[0];
    $totalPatients  = (int) $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0];
    $totalVolunteers = (int) $conn->query("SELECT COUNT(*) FROM volunteers")->fetch_row()[0];

    fputcsv($out, ['Total Outreach Events',  $totalEvents]);
    fputcsv($out, ['Total Patients',         $totalPatients]);
    fputcsv($out, ['Total Volunteers',       $totalVolunteers]);
    fputcsv($out, []);

    // ── Event Status Breakdown ────────────────────────────────────────────────
    fputcsv($out, ['=== EVENT STATUS BREAKDOWN ===']);
    fputcsv($out, ['Status', 'Count']);

    $res = $conn->query("SELECT status, COUNT(*) as count FROM outreach_events GROUP BY status ORDER BY count DESC");
    while ($row = $res->fetch_assoc()) {
        fputcsv($out, [ucfirst($row['status']), $row['count']]);
    }
    fputcsv($out, []);

    // ── Patient Gender Distribution ───────────────────────────────────────────
    fputcsv($out, ['=== PATIENT GENDER DISTRIBUTION ===']);
    fputcsv($out, ['Gender', 'Count', 'Percentage']);

    $res = $conn->query("SELECT gender, COUNT(*) as count FROM patients GROUP BY gender ORDER BY count DESC");
    $genderRows = $res->fetch_all(MYSQLI_ASSOC);
    foreach ($genderRows as $row) {
        $pct = $totalPatients > 0 ? round(($row['count'] / $totalPatients) * 100, 1) : 0;
        fputcsv($out, [$row['gender'], $row['count'], $pct . '%']);
    }
    fputcsv($out, []);

    // ── Patient Age Groups ────────────────────────────────────────────────────
    fputcsv($out, ['=== PATIENT AGE GROUPS ===']);
    fputcsv($out, ['Age Group', 'Count']);

    $ageSql = "
        SELECT
            CASE
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18  THEN 'Under 18'
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 35  THEN '18 – 34'
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 50  THEN '35 – 49'
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 65  THEN '50 – 64'
                ELSE '65 and above'
            END AS age_group,
            COUNT(*) AS count
        FROM patients
        GROUP BY age_group
        ORDER BY MIN(TIMESTAMPDIFF(YEAR, dob, CURDATE()))
    ";
    $res = $conn->query($ageSql);
    while ($row = $res->fetch_assoc()) {
        fputcsv($out, [$row['age_group'], $row['count']]);
    }
    fputcsv($out, []);

    // ── Top Volunteer Professions ─────────────────────────────────────────────
    fputcsv($out, ['=== TOP VOLUNTEER PROFESSIONS ===']);
    fputcsv($out, ['Profession', 'Count']);

    $res = $conn->query("SELECT profession, COUNT(*) as count FROM volunteers GROUP BY profession ORDER BY count DESC LIMIT 10");
    while ($row = $res->fetch_assoc()) {
        fputcsv($out, [$row['profession'] ?: '(Unspecified)', $row['count']]);
    }
    fputcsv($out, []);

    // ── Monthly Patient Registrations (Last 12 Months) ────────────────────────
    fputcsv($out, ['=== MONTHLY PATIENT REGISTRATIONS (LAST 12 MONTHS) ===']);
    fputcsv($out, ['Month', 'New Patients']);

    for ($i = 11; $i >= 0; $i--) {
        $month      = date('Y-m', strtotime("-$i months"));
        $monthLabel = date('F Y', strtotime("-$i months"));
        $stmt       = $conn->prepare("SELECT COUNT(*) FROM patients WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt->bind_param("s", $month);
        $stmt->execute();
        $count = (int) $stmt->get_result()->fetch_row()[0];
        fputcsv($out, [$monthLabel, $count]);
    }
    fputcsv($out, []);

    // ── Monthly Events (Last 12 Months) ──────────────────────────────────────
    fputcsv($out, ['=== MONTHLY OUTREACH EVENTS (LAST 12 MONTHS) ===']);
    fputcsv($out, ['Month', 'Events Held']);

    for ($i = 11; $i >= 0; $i--) {
        $month      = date('Y-m', strtotime("-$i months"));
        $monthLabel = date('F Y', strtotime("-$i months"));
        $stmt       = $conn->prepare("SELECT COUNT(*) FROM outreach_events WHERE DATE_FORMAT(event_date, '%Y-%m') = ?");
        $stmt->bind_param("s", $month);
        $stmt->execute();
        $count = (int) $stmt->get_result()->fetch_row()[0];
        fputcsv($out, [$monthLabel, $count]);
    }
}

fclose($out);
$conn->close();
exit();
