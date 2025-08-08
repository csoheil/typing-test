<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

<!--    style-->

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
        }

        .container {
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 123, 255, 0.5);
            width: 100%;
            max-width: 600px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            color: #007bff;
            margin-bottom: 1.5rem;
            text-shadow: 0 0 10px rgba(0, 123, 255, 0.7);
        }

        p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            opacity: 0;
            animation: slideIn 0.5s forwards;
        }

        p:nth-child(2) { animation-delay: 0.2s; }
        p:nth-child(3) { animation-delay: 0.4s; }
        p:nth-child(4) { animation-delay: 0.6s; }

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
        <h1>About Typing Master</h1>
        <p>Typing Master is a platform designed to help you improve your typing speed and accuracy with fun and engaging challenges.</p>
        <p>Our features include personalized dashboards, real-time typing tests, leaderboards to compete with others, and more.</p>
        <p>by developer of this web</p>
    </div>
</body>
</html>