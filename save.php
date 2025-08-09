<?php
require_once 'DatabaseConnector.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
    $char = 40;

    if (empty($username) || empty($password)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Username and password are required.'
        ]);
        exit;
    }
    if (strlen($username) > $char) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Username is too long.'
        ]);
        exit;
    }
    if (strlen($password) > $char) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Password is too long.'
        ]);
        exit;
    }

    try {
        $db = new DatabaseConnector();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (:username, :password)");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $password);
        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Data inserted successfully.'
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Problem in inserting data.'
        ]);
    }
}
?>