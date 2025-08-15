<?php
session_start();
require_once 'DatabaseConnector.php';

class UserManager {
    private $db;

    public function __construct() {
        $dbConnector = new DatabaseConnector();
        $this->db = $dbConnector->getConnection();
    }

    public function getUser($userId) {
        try {
            $stmt = $this->db->prepare("SELECT username, password FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updateUser($userId, $username, $password) {
        try {
            $username = substr($username, 0, 40);
            $password = substr($password, 0, 40);
            $stmt = $this->db->prepare("UPDATE user SET username = ?, password = ? WHERE id = ?");
            return $stmt->execute([$username, $password, $userId]);
        } catch (PDOException $e) {
            return ['error' => 'Update error: ' . $e->getMessage()];
        }
    }
}

$userManager = new UserManager();
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$userId) {
    header('Location: index.php');
    exit;
}

$user = $userManager->getUser($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = $_POST['username'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    if ($newUsername && $newPassword) {
        $userManager->updateUser($userId, $newUsername, $newPassword);
        $user = $userManager->getUser($userId);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
<link href="styles_css/style_home.css" rel="stylesheet" />
</head>
<body>
    <div class="container">
        <h1>Welcome to Your Dashboard</h1>
        <?php if ($user && !isset($user['error'])): ?>
            <div class="user-info">
                <p>Username: <?php echo htmlspecialchars($user['username']); ?></p>
                <p>Password: <?php echo htmlspecialchars($user['password']); ?></p>
            </div>
            <form class="edit-form" method="POST" onsubmit="return validateForm()">
                <input type="text" name="username" placeholder="New Username" value="<?php echo htmlspecialchars($user['username']); ?>" maxlength="40" required>
                <input type="password" name="password" placeholder="New Password" maxlength="40" required>
                <button type="submit">Update</button>
            </form>
        <?php elseif (isset($user['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($user['error']); ?></p>
        <?php else: ?>
            <p class="error">User not found in database. Please contact support.</p>
        <?php endif; ?>
    </div>

    <script>
        function validateForm() {
            const username = document.querySelector('input[name="username"]').value;
            const password = document.querySelector('input[name="password"]').value;
            if (!username || !password) {
                alert('Please fill in both fields.');
                return false;
            }
            if (username.length > 40 || password.length > 40) {
                alert('Username and password must not exceed 40 characters.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>