<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

switch($method) {
    case 'GET':
        $id = $_GET['id'] ?? 0;
        $album = $_GET['album'] ?? '';
        $limit = $_GET['limit'] ?? 0;
        
        if($id > 0) {
            $stmt = $conn->prepare("SELECT * FROM gallery WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
        } else {
            $sql = "SELECT * FROM gallery WHERE status = 'active'";
            $params = [];
            $types = "";
            
            if(!empty($album)) {
                $sql .= " AND album = ?";
                $params[] = $album;
                $types .= "s";
            }
            
            $sql .= " ORDER BY tanggal DESC";
            
            if($limit > 0) {
                $sql .= " LIMIT ?";
                $params[] = $limit;
                $types .= "i";
            }
            
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
        
        sendResponse(true, "Data gallery berhasil diambil", $data);
        break;
        
    case 'POST':
        $input = getPostData();
        
        validateRequiredFields(['judul', 'gambar'], $input);
        
        $judul = sanitizeInput($input['judul']);
        $deskripsi = sanitizeInput($input['deskripsi'] ?? '');
        $album = sanitizeInput($input['album'] ?? 'pemandangan');
        $gambar = sanitizeInput($input['gambar']);
        $status = sanitizeInput($input['status'] ?? 'active');
        $tanggal = sanitizeInput($input['tanggal'] ?? date('Y-m-d'));
        
        $stmt = $conn->prepare("INSERT INTO gallery (judul, deskripsi, album, gambar, status, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $judul, $deskripsi, $album, $gambar, $status, $tanggal);
        
        if($stmt->execute()) {
            $id = $conn->insert_id;
            sendResponse(true, "Foto berhasil ditambahkan ke gallery", ['id' => $id]);
        } else {
            sendResponse(false, "Gagal menambahkan foto: " . $conn->error);
        }
        break;
        
    case 'PUT':
        $input = getPostData();
        $id = $_GET['id'] ?? 0;
        
        if($id == 0) sendResponse(false, "ID gallery diperlukan");
        
        $fields = [];
        $params = [];
        $types = "";
        
        if(isset($input['judul'])) {
            $fields[] = "judul = ?";
            $params[] = sanitizeInput($input['judul']);
            $types .= "s";
        }
        if(isset($input['album'])) {
            $fields[] = "album = ?";
            $params[] = sanitizeInput($input['album']);
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
        
        $sql = "UPDATE gallery SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if($stmt->execute()) {
            sendResponse(true, "Gallery berhasil diupdate");
        } else {
            sendResponse(false, "Gagal update gallery: " . $conn->error);
        }
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? 0;
        
        if($id == 0) sendResponse(false, "ID gallery diperlukan");
        
        $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            sendResponse(true, "Foto berhasil dihapus dari gallery");
        } else {
            sendResponse(false, "Gagal menghapus foto: " . $conn->error);
        }
        break;
        
    default:
        sendResponse(false, "Method tidak didukung");
}

$conn->close();
?>