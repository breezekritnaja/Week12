<?php
// For local development only. Use environment variables on a shared server.
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 's68160193';
$pass = getenv('DB_PASS') ?: 'myw4DuHp';
$dbname = getenv('DB_NAME') ?: 's68160193';
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) { error_log(mysqli_connect_error()); http_response_code(500); exit('Database unavailable'); }
mysqli_set_charset($conn, 'utf8mb4');
