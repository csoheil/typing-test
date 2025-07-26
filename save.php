<?php
require_once 'DatabaseConnector.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
    $char = 40;
}
    if (empty($username) || empty($password)) {
        echo "Username and password are required.";
        exit;
    }
    if (strlen($username) > $char) {
        echo "Username is too long.";
        exit;
    }
    if (strlen($password) > $char) {
        echo "Password is too long.";
        exit;
    }

    try {
        $db = new DatabaseConnector();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (:username, :password)");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $password);
        $stmt->execute();

        echo "Data inserted successfully.";
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "problem in inserting data.";
    }
?>