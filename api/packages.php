<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

switch($method) {
    case 'GET':
        $id = $_GET['id'] ?? 0;
        
        if($id > 0) {
            $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
        } else {
            $result = $conn->query("SELECT * FROM packages ORDER BY created_at DESC");
            $data = [];
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        sendResponse(true, "Data paket berhasil diambil", $data);
        break;
        
    case 'POST':
        $input = getPostData();
        
        validateRequiredFields(['nama', 'harga', 'deskripsi'], $input);
        
        $nama = sanitizeInput($input['nama']);
        $harga = floatval($input['harga']);
        $deskripsi = sanitizeInput($input['deskripsi']);
        $gambar = sanitizeInput($input['gambar'] ?? '');
        $badge = sanitizeInput($input['badge'] ?? '');
        $status = sanitizeInput($input['status'] ?? 'active');
        
        $stmt = $conn->prepare("INSERT INTO packages (nama, harga, deskripsi, gambar, badge, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssss", $nama, $harga, $deskripsi, $gambar, $badge, $status);
        
        if($stmt->execute()) {
            $id = $conn->insert_id;
            sendResponse(true, "Paket berhasil ditambahkan", ['id' => $id]);
        } else {
            sendResponse(false, "Gagal menambahkan paket: " . $conn->error);
        }
        break;
        
    case 'PUT':
        $input = getPostData();
        $id = $_GET['id'] ?? 0;
        
        if($id == 0) sendResponse(false, "ID paket diperlukan");
        
        $fields = [];
        $params = [];
        $types = "";
        
        if(isset($input['nama'])) {
            $fields[] = "nama = ?";
            $params[] = sanitizeInput($input['nama']);
            $types .= "s";
        }
        if(isset($input['harga'])) {
            $fields[] = "harga = ?";
            $params[] = floatval($input['harga']);
            $types .= "d";
        }
        if(isset($input['deskripsi'])) {
            $fields[] = "deskripsi = ?";
            $params[] = sanitizeInput($input['deskripsi']);
            $types .= "s";
        }
        if(isset($input['status'])) {
            $fields[] = "status = ?";
            $params[] = sanitizeInput($input['status']);
            $types .= "s";
        }
        
        if(empty($fields)) sendResponse(false, "Tidak ada data yang diupdate");
        
        $params[] = $id;
        $types .= "i";
        
        $sql = "UPDATE packages SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if($stmt->execute()) {
            sendResponse(true, "Paket berhasil diupdate");
        } else {
            sendResponse(false, "Gagal update paket: " . $conn->error);
        }
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? 0;
        
        if($id == 0) sendResponse(false, "ID paket diperlukan");
        
        $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            sendResponse(true, "Paket berhasil dihapus");
        } else {
            sendResponse(false, "Gagal menghapus paket: " . $conn->error);
        }
        break;
        
    default:
        sendResponse(false, "Method tidak didukung");
}

$conn->close();
?>