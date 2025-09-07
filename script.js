let isTimerRunning = false;
let startTime;
let timerInterval;
let currentSentence = '';

function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

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

function generateRandomSentence() {
    const randomWords = getRandomWords(words, 10);
    currentSentence = randomWords.join(' ');
    const randomSentence = document.getElementById('random-sentence');
    randomSentence.textContent = currentSentence;
    randomSentence.classList.remove('visible');
    setTimeout(() => randomSentence.classList.add('visible'), 100);
}

function countMistakes(inputText, targetText) {
    let mistakes = 0;
    for (let i = 0; i < Math.max(inputText.length, targetText.length); i++) {
        if (inputText[i] !== targetText[i]) {
            mistakes++;
        }
    }
    return mistakes;
}

document.addEventListener('DOMContentLoaded', () => {
    fetch('check_session.php', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`);
            return res.json();
        })
        .then(data => {
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
            alert('Failed to check session status: ' + err.message);
        });
    const randomSentence = document.getElementById('random-sentence');
    randomSentence.addEventListener('copy', e => e.preventDefault());
    randomSentence.addEventListener('contextmenu', e => e.preventDefault());
});

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
            if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`);
            return res.json();
        })
        .then(data => {
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
            alert('Failed to connect to the server: ' + err.message);
        });
});

document.getElementById('typing-area').addEventListener('keydown', function () {
    if (!isTimerRunning) {
        isTimerRunning = true;
        startTime = Date.now();
        timerInterval = setInterval(updateTimer, 1000);
    }
});

function updateTimer() {
    const currentTime = Date.now();
    const elapsedTime = (currentTime - startTime) / 1000;
    document.getElementById('timer').textContent = formatTime(elapsedTime);
}

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
        localStorage.setItem('timeTaken', formatTime(timeElapsed));
        localStorage.setItem('cpm', cpm);
        localStorage.setItem('wpm', wpm);
        localStorage.setItem('mistakes', mistakes);
        fetch('save_score.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `wpm=${encodeURIComponent(wpm)}&cpm=${encodeURIComponent(cpm)}&mistakes=${encodeURIComponent(mistakes)}`
        })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    alert('Score saved successfully!');
                } else {
                    alert('Error saving score: ' + data.message);
                }
            })
            .catch(err => {
                alert('Failed to connect to the server: ' + err.message);
            });
        document.getElementById('time-taken').textContent = formatTime(timeElapsed);
        document.getElementById('cpm').textContent = cpm;
        document.getElementById('wpm').textContent = wpm;
        document.getElementById('mistakes').textContent = mistakes;
        document.getElementById('typing-area').value = '';
        generateRandomSentence();
    }
});

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
