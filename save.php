<?php
session_start(); // Start the session
require_once 'DatabaseConnector.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
    $maxLength = 40;

    if (empty($username) || empty($password)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Username and password are required.'
        ]);
        exit;
    }
    if (strlen($username) > $maxLength) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Username is too long.'
        ]);
        exit;
    }
    if (strlen($password) > $maxLength) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Password is too long.'
        ]);
        exit;
    }

    try {
        $db = new DatabaseConnector();
        $conn = $db->getConnection();

        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Username already exists.'
            ]);
            exit;
        }



        // Insert the new user
        $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (:username, :password)");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->execute();

        // Get the ID of the newly created user
        $userId = $conn->lastInsertId();

        // Set session for automatic login
        $_SESSION['user_id'] = $userId;

        echo json_encode([
            'status' => 'success',
            'message' => 'User registered and logged in successfully.',
            'user_id' => $userId
        ]);
    } catch (PDOException $e) {
        error_log('Error in save.php: ' . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to insert data.'
        ]);
    }
}
?>