<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Typing Test Site</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles_css/style.css">
    <style>
        #logout-button {
            width: 10%;
            padding: 18px;
            background: linear-gradient(45deg, #283d94, #2341a9);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 19px;
            font-weight: 700;
            transition: all 0.5s ease;
            box-shadow: 0 6px 20px rgba(70, 97, 185, 0.3);
        }
        #login-section {
            display: block;
            text-align: center;
            margin-top: 50px;
        }
        #main-content {
            display: none;
        }
        #random-sentence {
            font-family: 'Open Sans', sans-serif;
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 20px;
            color: #60a5fa;
            text-align: center;
            user-select: none;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
        }
        #random-sentence.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
<header class="site-header">
    <div class="logo">Typing Master</div>
    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="leaderboard.php">Leaderboard</a></li>
        </ul>
    </nav>
</header>

<div id="login-section">
    <form id="login-form">
        <input type="text" id="username" placeholder="Enter your username" maxlength="40" required />
        <input type="password" id="password" placeholder="Enter your password" maxlength="40" required />
        <button type="submit">Start Typing</button>
    </form>
</div>

<div id="main-content">
    <header>
        <div>
            <h1>Test your typing skills</h1>
            <p>Welcome to the typing test site!</p>
        </div>
        <button id="go-to-scores">Go to Your Scores <strong>scroll down</strong> or <strong>click leaderboard</strong></button>
        <button id="logout-button">Logout</button>
    </header>

    <div id="middle-section">
        <div id="timer">00:00</div>
        <div id="random-sentence"></div>
        <textarea id="typing-area" placeholder="Start typing here..."></textarea>
        <button id="finish-test">Finish Test</button>
    </div>

    <div id="main-content-section">
        <div class="box"></div>
        <div class="box"></div>
        <div class="box"></div>
    </div>

    <svg width="100%" height="20" viewBox="0 0 100 20" preserveAspectRatio="none">
        <path d="M0 10 Q25 0 50 10 T100 10" fill="none" stroke="#60a5fa" stroke-width="1" />
    </svg>
    <div id="scores-section" style="display: none;">
        <h2>Your Scores</h2>
        <p>Time Taken: <span id="time-taken"></span></p>
        <p>Characters per Minute: <span id="cpm"></span></p>
        <p>Words per Minute: <span id="wpm"></span></p>
        <p>Mistakes: <span id="mistakes"></span></p>
    </div>
</div>

<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>About Us</h3>
            <p>Typing Master is your go-to platform to improve your typing speed and accuracy with fun challenges!</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="leaderboard.php">Leaderboard</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Follow Us</h3>
            <div class="social-icons">
                <a href="https://github.com" target="_blank"><i class="fab fa-github"></i></a>
            </div>
        </div>
    </div>
</footer>

<script src="words.js"></script>
<script>
    let isTimerRunning = false;
    let startTime;
    let timerInterval;
    let currentSentence = '';

    // Format time as mm:ss
    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    // Select random words
    function getRandomWords(arr, num) {
        const copy = [...arr];
        const result = [];
        for (let i = copy.length - 1; i > 0 && result.length < num; i--) {
            const randomIndex = Math.floor(Math.random() * (i + 1));
            [copy[i], copy[randomIndex]] = [copy[randomIndex], copy[i]];
            result.push(copy[i]);
        }
        return result.slice(0, num);
    }

    // Generate random sentence
    function generateRandomSentence() {
        const randomWords = getRandomWords(words, 10);
        currentSentence = randomWords.join(' ');
        const randomSentence = document.getElementById('random-sentence');
        randomSentence.textContent = currentSentence;
        randomSentence.classList.remove('visible');
        setTimeout(() => randomSentence.classList.add('visible'), 100);
    }

    // Count mistakes
    function countMistakes(inputText, targetText) {
        let mistakes = 0;
        for (let i = 0; i < Math.max(inputText.length, targetText.length); i++) {
            if (inputText[i] !== targetText[i]) {
                mistakes++;
            }
        }
        return mistakes;
    }

    // Check login status
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Checking login status...');
        fetch('check_session.php', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! Status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('Session check response:', data);
                if (data.isLoggedIn) {
                    document.getElementById('login-section').style.display = 'none';
                    document.getElementById('main-content').style.display = 'block';
                    generateRandomSentence();
                } else {
                    localStorage.removeItem('isLoggedIn');
                    localStorage.removeItem('loginTime');
                    localStorage.removeItem('username');
                    document.getElementById('login-section').style.display = 'block';
                    document.getElementById('main-content').style.display = 'none';
                }
            })
            .catch(err => {
                console.error('Error checking session:', err);
                alert('Failed to check session status: ' + err.message);
            });

        // Prevent copying the sentence
        const randomSentence = document.getElementById('random-sentence');
        randomSentence.addEventListener('copy', e => e.preventDefault());
        randomSentence.addEventListener('contextmenu', e => e.preventDefault());
    });

    // Handle login form
    document.getElementById('login-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();

        if (username.length > 40 || password.length > 40) {
            alert('Username and password must not exceed 40 characters');
            return;
        }

        fetch('save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
        })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! Status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('Login response:', data);
                alert(data.message);
                if (data.status === 'success') {
                    localStorage.setItem('isLoggedIn', 'true');
                    localStorage.setItem('loginTime', Date.now());
                    localStorage.setItem('username', username);
                    document.getElementById('login-section').style.display = 'none';
                    document.getElementById('main-content').style.display = 'block';
                    generateRandomSentence();
                }
            })
            .catch(err => {
                console.error('Error sending login data:', err);
                alert('Failed to connect to the server: ' + err.message);
            });
    });

    // Logout button
    document.getElementById('logout-button').addEventListener('click', () => {
        fetch('logout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `username=${encodeURIComponent(localStorage.getItem('username'))}`
        })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! Status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('Logout response:', data);
                alert(data.message);
                if (data.status === 'success') {
                    localStorage.removeItem('isLoggedIn');
                    localStorage.removeItem('loginTime');
                    localStorage.removeItem('username');
                    document.getElementById('login-section').style.display = 'block';
                    document.getElementById('main-content').style.display = 'none';
                }
            })
            .catch(err => {
                console.error('Error during logout:', err);
                alert('Failed to connect to the server: ' + err.message);
            });
    });

    // Start timer
    document.getElementById('typing-area').addEventListener('keydown', function () {
        if (!isTimerRunning) {
            isTimerRunning = true;
            startTime = Date.now();
            timerInterval = setInterval(updateTimer, 1000);
        }
    });

    // Update timer
    function updateTimer() {
        const currentTime = Date.now();
        const elapsedTime = (currentTime - startTime) / 1000;
        document.getElementById('timer').textContent = formatTime(elapsedTime);
    }

    // End typing test
    document.getElementById('finish-test').addEventListener('click', function () {
        if (isTimerRunning) {
            clearInterval(timerInterval);
            isTimerRunning = false;
            const endTime = Date.now();
            const timeElapsed = (endTime - startTime) / 1000;
            const text = document.getElementById('typing-area').value.trim();
            const charCount = text.length;
            const minutes = timeElapsed / 60;
            const cpm = minutes > 0 ? (charCount / minutes).toFixed(2) : 0;
            const wpm = minutes > 0 ? (cpm / 5).toFixed(2) : 0;
            const mistakes = countMistakes(text, currentSentence);

            // Store results in localStorage
            localStorage.setItem('timeTaken', formatTime(timeElapsed));
            localStorage.setItem('cpm', cpm);
            localStorage.setItem('wpm', wpm);
            localStorage.setItem('mistakes', mistakes);

            // Send results to server
            fetch('save_score.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `wpm=${encodeURIComponent(wpm)}&cpm=${encodeURIComponent(cpm)}&mistakes=${encodeURIComponent(mistakes)}`
            })
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! Status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('Score save response:', data);
                    if (data.status === 'success') {
                        alert('Score saved successfully!');
                    } else {
                        alert('Error saving score: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error saving score:', err);
                    alert('Failed to connect to the server: ' + err.message);
                });

            // Display results
            document.getElementById('time-taken').textContent = formatTime(elapsedTime);
            document.getElementById('cpm').textContent = cpm;
            document.getElementById('wpm').textContent = wpm;
            document.getElementById('mistakes').textContent = mistakes;

            // Reset test
            document.getElementById('typing-area').value = '';
            generateRandomSentence();
        }
    });

    // Show scores
    document.getElementById('go-to-scores').addEventListener('click', function () {
        const scoresSection = document.getElementById('scores-section');
        scoresSection.style.display = 'block';
        setTimeout(() => scoresSection.classList.add('show'), 10);

        const timeTaken = localStorage.getItem('timeTaken');
        if (timeTaken) {
            document.getElementById('time-taken').textContent = timeTaken;
            document.getElementById('cpm').textContent = localStorage.getItem('cpm');
            document.getElementById('wpm').textContent = localStorage.getItem('wpm');
            document.getElementById('mistakes').textContent = localStorage.getItem('mistakes');
        } else {
            scoresSection.innerHTML = '<p>No scores yet. Please complete a typing test first.</p>';
        }
    });
</script>
</body>
</html>