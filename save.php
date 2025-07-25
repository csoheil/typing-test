<?php
require_once 'DatabaseConnector.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
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