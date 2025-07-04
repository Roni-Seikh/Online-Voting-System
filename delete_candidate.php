<?php
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Invalid candidate ID.");
}

$candidate_id = intval($_POST['id']);

// Delete the candidate
$stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
$stmt->bind_param("i", $candidate_id);

if ($stmt->execute()) {
    header("Location: add_candidate.php?msg=Candidate+deleted+successfully");
    exit;
} else {
    header("Location: add_candidate.php?error=Failed+to+delete+candidate");
    exit;
}
?>
