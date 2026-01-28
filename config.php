<?php
// config.php - UPDATED FOR XAMPP
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// ✅ XAMPP DEFAULT SETTINGS
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // KOSONG untuk XAMPP
define('DB_NAME', 'gerbang_langit');

// Create connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        // Return JSON error instead of dying
        header('Content-Type: application/json');
        echo json_encode([
            "success" => false,
            "message" => "Database tidak terhubung. Pastikan MySQL XAMPP aktif!",
            "error" => $conn->connect_error
        ]);
        exit();
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Helper function to send JSON response
function sendResponse($success, $message = "", $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit();
}

// Validate required fields
function validateRequiredFields($fields, $data) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            sendResponse(false, "Field '$field' harus diisi");
        }
    }
}

// Sanitize input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

// Generate unique ID
function generateId($prefix = 'GL') {
    return $prefix . '-' . date('Ymd') . '-' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
}

// Get current timestamp
function getCurrentTimestamp() {
    return date('Y-m-d H:i:s');
}

// Get POST data as array
function getPostData() {
    $input = file_get_contents("php://input");
    if (!empty($input)) {
        return json_decode($input, true);
    }
    return $_POST;
}
?>