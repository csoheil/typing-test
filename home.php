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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0a0a23, #1b1b4d);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 123, 255, 0.5);
            width: 100%;
            max-width: 500px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            color: #007bff;
            margin-bottom: 1.5rem;
            text-shadow: 0 0 10px rgba(0, 123, 255, 0.7);
        }

        .user-info {
            margin-bottom: 2rem;
        }

        .user-info p {
            font-size: 1.2rem;
            margin: 0.5rem 0;
            opacity: 0;
            animation: slideIn 0.5s forwards;
        }

        .user-info p:nth-child(1) { animation-delay: 0.2s; }
        .user-info p:nth-child(2) { animation-delay: 0.4s; }

        .edit-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        input[type="text"],
        input[type="password"] {
            padding: 0.8rem;
            border: none;
            border-radius: 5px;
            background: #2a2a5a;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
            max-width: 100%;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            box-shadow: 0 0 10px #007bff;
            transform: scale(1.02);
        }

        button {
            padding: 0.8rem;
            border: none;
            border-radius: 5px;
            background: #007bff;
            color: #ffffff;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        .error {
            color: #ff4444;
            margin-top: 1rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
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