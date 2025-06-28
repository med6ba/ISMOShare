<?php
// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ismoshare');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
}

catch (mysqli_sql_exception $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>
