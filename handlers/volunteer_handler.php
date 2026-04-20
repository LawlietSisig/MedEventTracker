<?php
/**
 * Volunteer Action Handler
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $conn = getConnection();

    if ($action === 'create') {
        $firstName     = trim($_POST['first_name'] ?? '');
        $lastName      = trim($_POST['last_name'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $contactNumber = trim($_POST['contact_number'] ?? '');
        $profession    = trim($_POST['profession'] ?? '');
        $status        = trim($_POST['status'] ?? 'active');
        $skillsNotes   = trim($_POST['skills_notes'] ?? '');

        $stmt = $conn->prepare("INSERT INTO volunteers (first_name, last_name, email, contact_number, profession, status, skills_notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssi", $firstName, $lastName, $email, $contactNumber, $profession, $status, $skillsNotes, $user['id']);

        if ($stmt->execute()) {
            setFlash('Volunteer created successfully.', 'success');
        } else {
            setFlash('Error creating volunteer: ' . $conn->error, 'error');
        }
        $stmt->close();

    } elseif ($action === 'update') {
        $id            = (int) ($_POST['volunteer_id'] ?? 0);
        $firstName     = trim($_POST['first_name'] ?? '');
        $lastName      = trim($_POST['last_name'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $contactNumber = trim($_POST['contact_number'] ?? '');
        $profession    = trim($_POST['profession'] ?? '');
        $status        = trim($_POST['status'] ?? 'active');
        $skillsNotes   = trim($_POST['skills_notes'] ?? '');

        $stmt = $conn->prepare("UPDATE volunteers SET first_name = ?, last_name = ?, email = ?, contact_number = ?, profession = ?, status = ?, skills_notes = ? WHERE id = ?");
        $stmt->bind_param("sssssssi", $firstName, $lastName, $email, $contactNumber, $profession, $status, $skillsNotes, $id);

        if ($stmt->execute()) {
            setFlash('Volunteer updated successfully.', 'success');
        } else {
            setFlash('Error updating volunteer: ' . $conn->error, 'error');
        }
        $stmt->close();

    } elseif ($action === 'delete') {
        $id = (int) ($_POST['volunteer_id'] ?? 0);

        $stmt = $conn->prepare("DELETE FROM volunteers WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            setFlash('Volunteer deleted successfully.', 'success');
        } else {
            setFlash('Error deleting volunteer: ' . $conn->error, 'error');
        }
        $stmt->close();
    }

    $conn->close();
    header('Location: ../pages/volunteers.php');
    exit();
}
