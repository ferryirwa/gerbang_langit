<?php
require_once '../config.php';

$conn = getConnection();

// Get dashboard statistics
$stats = [];

// Total bookings
$result = $conn->query("SELECT COUNT(*) as total FROM bookings");
$stats['total_bookings'] = $result->fetch_assoc()['total'];

// Active packages
$result = $conn->query("SELECT COUNT(*) as total FROM packages WHERE status = 'active'");
$stats['total_packages'] = $result->fetch_assoc()['total'];

// Active facilities
$result = $conn->query("SELECT COUNT(*) as total FROM facilities WHERE status = 'active'");
$stats['total_facilities'] = $result->fetch_assoc()['total'];

// Active testimonials
$result = $conn->query("SELECT COUNT(*) as total FROM testimonials WHERE status = 'active'");
$stats['total_testimonials'] = $result->fetch_assoc()['total'];

// Gallery items
$result = $conn->query("SELECT COUNT(*) as total FROM gallery WHERE status = 'active'");
$stats['total_gallery'] = $result->fetch_assoc()['total'];

// Recent bookings
$result = $conn->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5");
$recent_bookings = [];
while($row = $result->fetch_assoc()) {
    $recent_bookings[] = $row;
}

// Recent testimonials
$result = $conn->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY tanggal DESC LIMIT 3");
$recent_testimonials = [];
while($row = $result->fetch_assoc()) {
    $recent_testimonials[] = $row;
}

$conn->close();

sendResponse(true, "Dashboard data berhasil diambil", [
    'stats' => $stats,
    'recent_bookings' => $recent_bookings,
    'recent_testimonials' => $recent_testimonials
]);
?>