<?php
// db credentials for the mysqli connection used by the reviews table
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kpwraps');

// returns a shared mysqli connection, only creates it once per request
function get_db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        // stop and return an error if the connection fails
        if ($conn->connect_error) {
            http_response_code(500);
            die(json_encode(['success' => false, 'error' => 'DB connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}