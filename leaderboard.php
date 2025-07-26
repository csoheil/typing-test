<?php
session_start();
require_once 'DatabaseConnector.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

class ScoreService {
    private $db;

    public function __construct() {
        $dbConnector = new DatabaseConnector();
        $this->db = $dbConnector->getConnection();
    }

    public function getUserScores($userId) {
        try {
            // Get best score (highest WPM)
            $stmt = $this->db->prepare('SELECT wpm, cpm, mistakes FROM scores WHERE user_id = ? ORDER BY wpm DESC LIMIT 1');
            $stmt->execute([$userId]);
            $bestScore = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get lowest score (lowest WPM)
            $stmt = $this->db->prepare('SELECT wpm, cpm, mistakes FROM scores WHERE user_id = ? ORDER BY wpm ASC LIMIT 1');
            $stmt->execute([$userId]);
            $lowestScore = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get all scores
            $stmt = $this->db->prepare('SELECT wpm, cpm, mistakes FROM scores WHERE user_id = ? ORDER BY wpm DESC');
            $stmt->execute([$userId]);
            $allScores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'best_score' => $bestScore ?: [],
                'lowest_score' => $lowestScore ?: [],
                'all_scores' => $allScores
            ];
        } catch (PDOException $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }
}

$scoreService = new ScoreService();
$scores = $scoreService->getUserScores($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0a0a23, #1b1b4d);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 123, 255, 0.5);
            width: 100%;
            max-width: 800px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            color: #007bff;
            margin-bottom: 1.5rem;
            text-shadow: 0 0 10px rgba(0, 123, 255, 0.7);
        }

        .score-section {
            margin-bottom: 2rem;
            opacity: 0;
            animation: slideIn 0.5s forwards;
        }

        .score-section p {
            font-size: 1.2rem;
            margin: 0.5rem 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            opacity: 0;
            animation: slideIn 0.5s forwards;
            animation-delay: 0.2s;
        }

        th, td {
            padding: 0.8rem;
            border: 1px solid #2a2a5a;
            text-align: center;
        }

        th {
            background: #2a2a5a;
            color: #007bff;
        }

        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }

        .error {
            color: #ff4444;
            margin-top: 2rem;
        }

        .no-scores {
            font-size: 1.1rem;
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
        <h1>Your Leaderboard</h1>
        <?php if (is_array($scores) && !isset($scores['error'])): ?>
            <div class="score-section">
                <h3>Best Score</h3>
                <?php if (!empty($scores['best_score'])): ?>
                    <p>WPM: <?php echo htmlspecialchars($scores['best_score']['wpm']); ?></p>
                    <p>CPM: <?php echo htmlspecialchars($scores['best_score']['cpm']); ?></p>
                    <p>Mistakes: <?php echo htmlspecialchars($scores['best_score']['mistakes']); ?></p>
                <?php else: ?>
                    <p class="no-scores">No best score yet.</p>
                <?php endif; ?>
            </div>
            <div class="score-section">
                <h3>Lowest Score</h3>
                <?php if (!empty($scores['lowest_score'])): ?>
                    <p>WPM: <?php echo htmlspecialchars($scores['lowest_score']['wpm']); ?></p>
                    <p>CPM: <?php echo htmlspecialchars($scores['lowest_score']['cpm']); ?></p>
                    <p>Mistakes: <?php echo htmlspecialchars($scores['lowest_score']['mistakes']); ?></p>
                <?php else: ?>
                    <p class="no-scores">No lowest score yet.</p>
                <?php endif; ?>
            </div>
            <div class="score-section">
                <h3>All Scores</h3>
                <?php if (!empty($scores['all_scores'])): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>WPM</th>
                                <th>CPM</th>
                                <th>Mistakes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scores['all_scores'] as $score): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($score['wpm']); ?></td>
                                    <td><?php echo htmlspecialchars($score['cpm']); ?></td>
                                    <td><?php echo htmlspecialchars($score['mistakes']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-scores">No scores recorded yet.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="error"><?php echo htmlspecialchars($scores['error'] ?? 'Error loading scores'); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>