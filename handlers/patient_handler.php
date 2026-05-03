<?php
/**
 * Patient Action Handler
 * Handles Create, Update, Delete for Patients
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$action = $_POST['action'] ?? '';
$user   = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/patients.php');
    exit();
}

try {
    $conn = getConnection();
    
    if ($action === 'create' || $action === 'update') {
        $data = sanitisePatient($_POST);

        // ── Minimum age check (13 years) ─────────────────────────────
        if (!empty($data['dob'])) {
            try {
                $dob   = new DateTime($data['dob']);
                $today = new DateTime('today');
                $age   = $today->diff($dob)->y;
                if ($age < 13) {
                    setFlash('Patient must be at least 13 years old.', 'error');
                    header('Location: ../pages/patients.php');
                    exit();
                }
                // Also reject future dates
                if ($dob > $today) {
                    setFlash('Date of birth cannot be in the future.', 'error');
                    header('Location: ../pages/patients.php');
                    exit();
                }
            } catch (Exception $e) {
                setFlash('Invalid date of birth provided.', 'error');
                header('Location: ../pages/patients.php');
                exit();
            }
        }
        
        if ($action === 'create') {
            $stmt = $conn->prepare("INSERT INTO patients (first_name, middle_name, last_name, dob, gender, contact_number, address, blood_type, medical_notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssi", $data['first_name'], $data['middle_name'], $data['last_name'], $data['dob'], $data['gender'], $data['contact_number'], $data['address'], $data['blood_type'], $data['medical_notes'], $user['id']);
            
            if ($stmt->execute()) {
                setFlash('Patient successfully registered.', 'success');
            } else {
                setFlash('Failed to register patient: ' . $stmt->error, 'error');
            }
            $stmt->close();
        } else {
            // Update
            $id = (int)$_POST['patient_id'];
            $stmt = $conn->prepare("UPDATE patients SET first_name=?, middle_name=?, last_name=?, dob=?, gender=?, contact_number=?, address=?, blood_type=?, medical_notes=? WHERE id=?");
            $stmt->bind_param("sssssssssi", $data['first_name'], $data['middle_name'], $data['last_name'], $data['dob'], $data['gender'], $data['contact_number'], $data['address'], $data['blood_type'], $data['medical_notes'], $id);
            
            if ($stmt->execute()) {
                setFlash('Patient profile updated.', 'success');
            } else {
                setFlash('Failed to update patient: ' . $stmt->error, 'error');
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        if (!in_array($user['role'], ['admin', 'coordinator'])) {
            setFlash('You do not have permission to delete patients.', 'error');
            header('Location: ../pages/patients.php');
            exit();
        }
        $id = (int)$_POST['patient_id'];
        $stmt = $conn->prepare("DELETE FROM patients WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            setFlash('Patient deleted successfully.', 'success');
        } else {
            setFlash('Failed to delete patient.', 'error');
        }
        $stmt->close();
    }
    
    $conn->close();
} catch (Exception $e) {
    setFlash('An unexpected error occurred: ' . $e->getMessage(), 'error');
}

header('Location: ../pages/patients.php');
exit();

function sanitisePatient(array $post): array {
    return [
        'first_name'     => trim($post['first_name'] ?? ''),
        'middle_name'    => trim($post['middle_name'] ?? ''),
        'last_name'      => trim($post['last_name'] ?? ''),
        'dob'            => trim($post['dob'] ?? ''),
        'gender'         => in_array($post['gender'] ?? '', ['Male','Female','Other']) ? $post['gender'] : 'Other',
        'contact_number' => trim($post['contact_number'] ?? ''),
        'address'        => trim($post['address'] ?? ''),
        'blood_type'     => trim($post['blood_type'] ?? ''),
        'medical_notes'  => trim($post['medical_notes'] ?? '')
    ];
}
