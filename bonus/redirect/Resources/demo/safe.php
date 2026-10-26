<?php
$allowed = [
    "facebook" => "https://facebook.com/42Born2Code/",
    "twitter" => "https://twitter.com/"
];

$site = $_GET["site"] ?? "";

if (!isset($allowed[$site])) {
    http_response_code(400);
    exit("Invalid redirect target");
}

header("Location: " . $allowed[$site]);
exit;
