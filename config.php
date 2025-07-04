<?php
$host = 'localhost';
$db = 'voting_system';
$user = 'root';
$pass = '';

// Create database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
