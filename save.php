<?php
session_start(); // Start a new or resume an existing session
require_once 'DatabaseConnector.php'; // Include database connection class

// Set JSON as the response type
header('Content-Type: application/json');

// Only allow POST requests for security reasons
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve input values sa fely with null coalescing operator
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
    $maxLength = 40;

    // ------------------------- Input Validation -------------------------
    if (empty($username) || empty($password)) {
        // Check for missing username or password
        echo json_encode([
            'status' => 'error',
            'message' => 'Username and password are required.'
        ]);
        exit;
    }

    // Validate maximum username length
    if (strlen($username) > $maxLength) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Username is too long.'
        ]);
        exit;
    }

    // Validate maximum password length
    if (strlen($password) > $maxLength) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Password is too long.'
        ]);
        exit;
    }

    // ------------------------- Database Operations -------------------------
    try {
        // Initialize database connection
        $db = new DatabaseConnector();
        $conn = $db->getConnection();

        // Check if the username already exists in the database
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            // Return error if username already taken
            echo json_encode([
                'status' => 'error',
                'message' => 'Username already exists.'
            ]);
            exit;
        }

        // ------------------------- User Creation -------------------------
        // Securely hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (:username, :password)");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->execute();

        // Retrieve the newly inserted user's ID
        $userId = $conn->lastInsertId();

        // Store user ID in session for automatic login
        $_SESSION['user_id'] = $userId;

        // Send success response as JSON
        echo json_encode([
            'status' => 'success',
            'message' => 'User registered and logged in successfully.',
            'user_id' => $userId
        ]);
    } catch (PDOException $e) {
        // Log error details for server-side debugging
        error_log('Error in save.php: ' . $e->getMessage());

        // Return a generic error message to client
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to insert data: ' . $e->getMessage()
        ]);
    }
}
?>
