<?php
session_start();
require_once 'DatabaseConnector.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$wpm = $_POST['wpm'] ?? 0;
$cpm = $_POST['cpm'] ?? 0;
$mistakes = $_POST['mistakes'] ?? 0;
$userId = $_SESSION['user_id'];

if (!is_numeric($wpm) || !is_numeric($cpm) || !is_numeric($mistakes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid score data']);
    exit;
}

try {
    $dbConnector = new DatabaseConnector();
    $db = $dbConnector->getConnection();

    // Verify user_id exists in user table
    $stmt = $db->prepare('SELECT id FROM user WHERE id = ?');
    $stmt->execute([$userId]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['status' => 'error', 'message' => 'User not found in database']);
        exit;
    }

    // Insert score
    $stmt = $db->prepare('INSERT INTO scores (user_id, wpm, cpm, mistakes) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $wpm, $cpm, $mistakes]);
    echo json_encode(['status' => 'success', 'message' => 'Score saved successfully']);
} catch (PDOException $e) {
    error_log('Database error in save_score: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>