<?php
session_start([
    'cookie_lifetime' => 86400, // 24 hours
    'cookie_secure' => true, // Only send cookies over HTTPS
    'cookie_httponly' => true, // Prevent JavaScript access to cookies
    'use_strict_mode' => true, // Prevent session fixation
]);
session_regenerate_id(true); // Regenerate session ID to prevent fixation

require_once 'DatabaseConnector.php';

class UserManager {
    private $db;

    public function __construct() {
        $dbConnector = new DatabaseConnector();
        $this->db = $dbConnector->getConnection();
    }

    public function getUser($userId) {
        try {
            $stmt = $this->db->prepare("SELECT id, username FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error in getUser: ' . $e->getMessage());
            return null;
        }
    }

    public function updateUser($userId, $username, $password) {
        try {
            // Validate username
            if (!preg_match('/^[a-zA-Z0-9_]{3,40}$/', $username)) {
                return ['error' => 'Username must be 3-40 characters and contain only letters, numbers, or underscores.'];
            }

            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                return ['error' => 'Failed to hash password.'];
            }

            $stmt = $this->db->prepare("UPDATE user SET username = ?, password = ? WHERE id = ?");
            return $stmt->execute([$username, $hashedPassword, $userId]) ? true : ['error' => 'Failed to update user.'];
        } catch (PDOException $e) {
            error_log('Update error: ' . $e->getMessage());
            return ['error' => 'Failed to update user due to database error.'];
        }
    }

    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

$userManager = new UserManager();
$userId = isset($_SESSION['user_id']) ? filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT) : null;

if (!$userId) {
    header('Location: index.php');
    exit;
}

$user = $userManager->getUser($userId);
$csrfToken = $userManager->generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $postedCsrfToken = $_POST['csrf_token'] ?? '';
    if (!$userManager->validateCsrfToken($postedCsrfToken)) {
        $error = 'Invalid CSRF token.';
    } else {
        $newUsername = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $newPassword = $_POST['password'] ?? '';

        // Additional validation
        if (empty($newUsername) || empty($newPassword)) {
            $error = 'Both username and password are required.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } else {
            $result = $userManager->updateUser($userId, $newUsername, $newPassword);
            if (is_array($result) && isset($result['error'])) {
                $error = $result['error'];
            } else {
                $success = 'User information updated successfully.';
                $user = $userManager->getUser($userId); // Refresh user data
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline'; font-src 'self' https://fonts.googleapis.com; script-src 'self' 'unsafe-inline'">
    <title>Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link href="styles_css/style_home.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Welcome to Your Dashboard</h1>
        <?php if ($user && !isset($user['error'])): ?>
            <div class="user-info">
                <p>Username: <?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <?php if (isset($success)): ?>
                <p class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <form class="edit-form" method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="text" name="username" placeholder="New Username" value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="40" required>
                <input type="password" name="password" placeholder="New Password" maxlength="40" required>
                <button type="submit">Update</button>
            </form>
        <?php else: ?>
            <p class="error">User not found in database. Please contact support.</p>
        <?php endif; ?>
    </div>

    <script>
        function validateForm() {
            const username = document.querySelector('input[name="username"]').value;
            const password = document.querySelector('input[name="password"]').value;
            const usernameRegex = /^[a-zA-Z0-9_]{3,40}$/;
            if (!username || !password) {
                alert('Please fill in both fields.');
                return false;
            }
            if (!usernameRegex.test(username)) {
                alert('Username must be 3-40 characters and contain only letters, numbers, or underscores.');
                return false;
            }
            if (password.length < 8) {
                alert('Password must be at least 8 characters long.');
                return false;
            }
            if (password.length > 40) {
                alert('Password must not exceed 40 characters.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>