<?php
session_start();

$limit = 3;
$window = 60;

$mode = $_GET['mode'] ?? 'unsafe';
$password = $_GET['password'] ?? '';

if (!in_array($mode, ['unsafe', 'safe'], true)) {
    http_response_code(400);
    exit('Invalid mode');
}

if ($mode === 'safe') {
    $now = time();
    $attempts = $_SESSION['attempts'] ?? [];

    $attempts = array_filter(
        $attempts,
        fn($time) => $time > $now - $window
    );

    if (count($attempts) >= $limit) {
        http_response_code(429);
        exit('Too many attempts');
    }

    $_SESSION['attempts'] = $attempts;
}

if ($password === 'shadow') {
    if ($mode === 'safe') {
        $_SESSION['attempts'] = [];
    }
    exit('Login successful');
}

if ($mode === 'safe') {
    $_SESSION['attempts'][] = time();
}

http_response_code(401);
echo 'Invalid password';
