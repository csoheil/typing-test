<?php
session_start();
require_once 'DatabaseConnector.php';

header('Content-Type: application/json');

try {
    $isLoggedIn = false;
    if (isset($_SESSION['user_id'])) {
        $dbConnector = new DatabaseConnector();
        $db = $dbConnector->getConnection();
        $stmt = $db->prepare('SELECT id FROM user WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $isLoggedIn = !!$user;
    }
    echo json_encode(['isLoggedIn' => $isLoggedIn]);
} catch (PDOException $e) {
    error_log('Database error in check_session: ' . $e->getMessage());
    echo json_encode(['isLoggedIn' => false, 'error' => 'Database error']);
}
?>