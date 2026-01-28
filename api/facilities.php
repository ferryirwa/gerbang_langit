<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

switch($method) {
    case 'GET':
        $id = $_GET['id'] ?? 0;
        $kategori = $_GET['kategori'] ?? '';
        
        if($id > 0) {
            $stmt = $conn->prepare("SELECT * FROM facilities WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
        } else {
            $sql = "SELECT * FROM facilities";
            $params = [];
            $types = "";
            
            if(!empty($kategori)) {
                $sql .= " WHERE kategori = ?";
                $params[] = $kategori;
                $types .= "s";
            }
            
            $sql .= " ORDER BY kategori, nama";
            
            if(!empty($params)) {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($sql);
            }
            
            $data = [];
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        sendResponse(true, "Data fasilitas berhasil diambil", $data);
        break;
        
    case 'POST':
        $input = getPostData();
        
        validateRequiredFields(['nama', 'kategori', 'deskripsi'], $input);
        
        $nama = sanitizeInput($input['nama']);
        $kategori = sanitizeInput($input['kategori']);
        $lokasi = sanitizeInput($input['lokasi'] ?? '');
        $deskripsi = sanitizeInput($input['deskripsi']);
        $status = sanitizeInput($input['status'] ?? 'active');
        
        $stmt = $conn->prepare("INSERT INTO facilities (nama, kategori, lokasi, deskripsi, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nama, $kategori, $lokasi, $deskripsi, $status);
        
        if($stmt->execute()) {
            $id = $conn->insert_id;
            sendResponse(true, "Fasilitas berhasil ditambahkan", ['id' => $id]);
        } else {
            sendResponse(false, "Gagal menambahkan fasilitas: " . $conn->error);
        }
        break;
        
    case 'PUT':
        $input = getPostData();
        $id = $_GET['id'] ?? 0;
        
        if($id == 0) sendResponse(false, "ID fasilitas diperlukan");
        
        $fields = [];
        $params = [];
        $types = "";
        
        if(isset($input['nama'])) {
            $fields[] = "nama = ?";
            $params[] = sanitizeInput($input['nama']);
            $types .= "s";
        }
        if(isset($input['kategori'])) {
            $fields[] = "kategori = ?";
            $params[] = sanitizeInput($input['kategori']);
            $types .= "s";
        }
        if(isset($input['status'])) {
            $fields[] = "status = ?";
            $params[] = sanitizeInput($input['status']);
            $types .= "s";
        }
        if(isset($input['deskripsi'])) {
            $fields[] = "deskripsi = ?";
            $params[] = sanitizeInput($input['deskripsi']);
            $types .= "s";
        }
        
        if(empty($fields)) sendResponse(false, "Tidak ada data yang diupdate");
        
        $params[] = $id;
        $types .= "i";
        
        $sql = "UPDATE facilities SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if($stmt->execute()) {
            sendResponse(true, "Fasilitas berhasil diupdate");
        } else {
            sendResponse(false, "Gagal update fasilitas: " . $conn->error);
        }
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? 0;
        
        if($id == 0) sendResponse(false, "ID fasilitas diperlukan");
        
        $stmt = $conn->prepare("DELETE FROM facilities WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            sendResponse(true, "Fasilitas berhasil dihapus");
        } else {
            sendResponse(false, "Gagal menghapus fasilitas: " . $conn->error);
        }
        break;
        
    default:
        sendResponse(false, "Method tidak didukung");
}

$conn->close();
?>