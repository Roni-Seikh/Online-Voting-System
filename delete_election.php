<?php
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method");
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("Invalid CSRF token");
}

// Check and sanitize election ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Invalid election ID");
}

$id = intval($_POST['id']);

// First delete candidates
$stmt = $conn->prepare("DELETE FROM candidates WHERE election_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Delete the election
$stmt = $conn->prepare("DELETE FROM elections WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header("Location: add_election.php?deleted=1");
    exit();
} else {
    die("Election not found or already deleted.");
}
?>
