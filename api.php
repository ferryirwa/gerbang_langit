<?php
// api.php - Simple API untuk testing
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$table = $_GET['table'] ?? '';

$conn = getConnection();

switch($table) {
    case 'packages':
        if ($method == 'GET') {
            $result = $conn->query("SELECT * FROM packages WHERE status='active'");
            $data = [];
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            sendResponse(true, "Data packages", $data);
        }
        break;
        
    case 'bookings':
        if ($method == 'GET') {
            $result = $conn->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 10");
            $data = [];
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            sendResponse(true, "Data bookings", $data);
        }
        break;
        
    case 'testimonials':
        if ($method == 'GET') {
            $result = $conn->query("SELECT * FROM testimonials WHERE status='active'");
            $data = [];
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            sendResponse(true, "Data testimonials", $data);
        }
        break;
        
    default:
        sendResponse(false, "Table tidak ditemukan");
}

$conn->close();
?>