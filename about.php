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