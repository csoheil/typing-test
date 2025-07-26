<?php
require_once 'DatabaseConnector.php';

try {
    $dbConnector = new DatabaseConnector();
    $conn = $dbConnector->getConnection();

    $username = isset($_POST['username']) ? $_POST['username'] : '';

    if (empty($username)) {
        echo "error: username cannot be empty";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM user WHERE username = ?");
    $stmt->execute([$username]);

    echo "success: user has been deleted";
} catch (PDOException $e) {
    echo "error: error in delete user " . $e->getMessage();
}

$conn = null;
