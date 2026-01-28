<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

switch($method) {
    case 'GET':
        $id = $_GET['id'] ?? 0;
        $status = $_GET['status'] ?? 'active';
        
        if($id > 0) {
            $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
        } else {
            $stmt = $conn->prepare("SELECT * FROM testimonials WHERE status = ? ORDER BY rating DESC, tanggal DESC");
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        sendResponse(true, "Data testimoni berhasil diambil", $data);
        break;
        
    case 'POST':
        $input = getPostData();
        
        validateRequiredFields(['nama', 'testimoni', 'rating'], $input);
        
        $nama = sanitizeInput($input['nama']);
        $email = sanitizeInput($input['email'] ?? '');
        $rating = floatval($input['rating']);
        $testimoni = sanitizeInput($input['testimoni']);
        $status = sanitizeInput($input['status'] ?? 'pending');
        $tanggal = sanitizeInput($input['tanggal'] ?? date('Y-m-d'));
        
        $stmt = $conn->prepare("INSERT INTO testimonials (nama, email, rating, testimoni, status, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsss", $nama, $email, $rating, $testimoni, $status, $tanggal);
        
        if($stmt->execute()) {
            $id = $conn->insert_id;
            sendResponse(true, "Testimoni berhasil ditambahkan", ['id' => $id]);
        } else {
            sendResponse(false, "Gagal menambahkan testimoni: " . $conn->error);
        }
        break;
        
    case 'PUT':
        $input = getPostData();
        $id = $_GET['id'] ?? 0;
        
        if($id == 0) sendResponse(false, "ID testimoni diperlukan");
        
        // Update status saja (untuk verifikasi)
        if(isset($input['status'])) {
            $status = sanitizeInput($input['status']);
            $stmt = $conn->prepare("UPDATE testimonials SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            
            if($stmt->execute()) {
                sendResponse(true, "Status testimoni berhasil diupdate");
            } else {
                sendResponse(false, "Gagal update status: " . $conn->error);
            }
        } else {
            sendResponse(false, "Tidak ada data yang diupdate");
        }
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? 0;
        
        if($id == 0) sendResponse(false, "ID testimoni diperlukan");
        
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            sendResponse(true, "Testimoni berhasil dihapus");
        } else {
            sendResponse(false, "Gagal menghapus testimoni: " . $conn->error);
        }
        break;
        
    default:
        sendResponse(false, "Method tidak didukung");
}

$conn->close();
?>