<?php
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', getenv('DB_USER') ?: 'appuser');
define('DB_PASSWORD', getenv('DB_PASS') ?: 'apppass');
define('DB_NAME', getenv('DB_NAME') ?: 'cithadas_db');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("ERROR: Could not connect. " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
