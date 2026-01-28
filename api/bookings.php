<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Simulasi database
$bookings = [
    [
        'id' => 1,
        'nama' => 'John Doe',
        'email' => 'john@example.com',
        'whatsapp' => '081234567890',
        'paket' => 'Paket Sunset Premium',
        'tanggal' => date('Y-m-d', strtotime('+3 days')),
        'jumlah' => 2,
        'total' => 500000,
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
    ],
    [
        'id' => 2,
        'nama' => 'Jane Smith',
        'email' => 'jane@example.com',
        'whatsapp' => '081298765432',
        'paket' => 'Paket Camping Reguler',
        'tanggal' => date('Y-m-d', strtotime('+5 days')),
        'jumlah' => 4,
        'total' => 800000,
        'status' => 'pending',
        'payment_status' => 'pending',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
    ]
];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode([
        'success' => true,
        'data' => $bookings,
        'pagination' => [
            'current_page' => 1,
            'total_pages' => 1,
            'total_items' => count($bookings)
        ]
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'Operation completed'
    ]);
}