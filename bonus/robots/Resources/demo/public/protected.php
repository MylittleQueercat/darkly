<?php
session_start();

if (empty($_SESSION['authorized'])) {
    http_response_code(403);
    exit("Access denied");
}

header("Content-Type: text/plain");
echo "INTERNAL TEST DATA";
