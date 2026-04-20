<?php
/**
 * Outreach Event Handler (Create / Update / Delete)
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser();

// Only admins and coordinators may mutate events
if (!in_array($user['role'], ['admin', 'coordinator'])) {
    setFlash('error', 'You do not have permission to manage events.');
    header('Location: /Medical Outreach Tracker/pages/outreach_events.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Medical Outreach Tracker/pages/outreach_events.php');
    exit();
}

$action   = $_POST['action'] ?? '';
$conn     = getConnection();
$redirect = '/Medical Outreach Tracker/pages/outreach_events.php';

// ── Helper: validate & sanitise ───────────────────────────────────────────────
function sanitiseEvent(array $post): array|false
{
    $title      = trim($post['title'] ?? '');
    $desc       = trim($post['description'] ?? '');
    $location   = trim($post['location'] ?? '');
    $eventDate  = trim($post['event_date'] ?? '');
    $startTime  = trim($post['start_time'] ?? '');
    $endTime    = trim($post['end_time'] ?? '');
    $status     = trim($post['status'] ?? 'upcoming');
    $maxVols    = isset($post['max_volunteers']) && $post['max_volunteers'] !== ''
                    ? (int)$post['max_volunteers'] : null;

    $validStatuses = ['upcoming', 'ongoing', 'completed', 'cancelled'];

    if (!$title || !$location || !$eventDate || !$startTime || !$endTime) {
        return false;
    }
    if (!in_array($status, $validStatuses)) $status = 'upcoming';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) return false;
    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $startTime)) return false;
    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $endTime))   return false;

    return compact('title', 'desc', 'location', 'eventDate', 'startTime', 'endTime', 'status', 'maxVols');
}

// ── Helper: nullable bind_param for max_volunteers ─────────────────────────────
function bindNullableEvent(mysqli_stmt $stmt, string $typePrefix, array $data, int $lastInt): void
{
    // We use PHP 8.1+ execute([]) style for clean NULL support
    $stmt->execute([
        $data['title'],
        $data['desc'],
        $data['location'],
        $data['eventDate'],
        $data['startTime'],
        $data['endTime'],
        $data['status'],
        $data['maxVols'],   // null-safe
        $lastInt,
    ]);
}

// ── CREATE ────────────────────────────────────────────────────────────────────
if ($action === 'create') {
    $data = sanitiseEvent($_POST);
    if (!$data) {
        setFlash('error', 'Please fill in all required fields correctly.');
        header("Location: {$redirect}");
        exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO outreach_events
         (title, description, location, event_date, start_time, end_time, status, max_volunteers, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($stmt->execute([
        $data['title'], $data['desc'], $data['location'],
        $data['eventDate'], $data['startTime'], $data['endTime'],
        $data['status'], $data['maxVols'], $user['id'],
    ])) {
        setFlash('success', 'Event "' . htmlspecialchars($data['title']) . '" created successfully!');
    } else {
        setFlash('error', 'Failed to create event. Please try again.');
    }
    $stmt->close();

// ── UPDATE ────────────────────────────────────────────────────────────────────
} elseif ($action === 'update') {
    $eventId = (int)($_POST['event_id'] ?? 0);
    $data    = sanitiseEvent($_POST);

    if (!$eventId || !$data) {
        setFlash('error', 'Please fill in all required fields correctly.');
        header("Location: {$redirect}");
        exit();
    }

    // Verify event exists
    $check = $conn->prepare("SELECT id FROM outreach_events WHERE id = ?");
    $check->bind_param('i', $eventId);
    $check->execute();
    if (!$check->get_result()->fetch_assoc()) {
        setFlash('error', 'Event not found.');
        header("Location: {$redirect}");
        exit();
    }
    $check->close();

    $stmt = $conn->prepare(
        "UPDATE outreach_events
         SET title=?, description=?, location=?, event_date=?, start_time=?, end_time=?,
             status=?, max_volunteers=?
         WHERE id=?"
    );

    if ($stmt->execute([
        $data['title'], $data['desc'], $data['location'],
        $data['eventDate'], $data['startTime'], $data['endTime'],
        $data['status'], $data['maxVols'], $eventId,
    ])) {
        setFlash('success', 'Event "' . htmlspecialchars($data['title']) . '" updated successfully!');
    } else {
        setFlash('error', 'Failed to update event. Please try again.');
    }
    $stmt->close();

// ── DELETE ────────────────────────────────────────────────────────────────────
} elseif ($action === 'delete') {
    $eventId = (int)($_POST['event_id'] ?? 0);

    if (!$eventId) {
        setFlash('error', 'Invalid event.');
        header("Location: {$redirect}");
        exit();
    }

    // Get title for feedback
    $row = $conn->query("SELECT title FROM outreach_events WHERE id=" . $eventId)->fetch_assoc();

    if ($row) {
        $del = $conn->prepare("DELETE FROM outreach_events WHERE id = ?");
        $del->bind_param('i', $eventId);
        if ($del->execute()) {
            setFlash('success', 'Event "' . htmlspecialchars($row['title']) . '" deleted.');
        } else {
            setFlash('error', 'Failed to delete event.');
        }
        $del->close();
    } else {
        setFlash('error', 'Event not found.');
    }

} else {
    setFlash('error', 'Unknown action.');
}

$conn->close();
header("Location: {$redirect}");
exit();
