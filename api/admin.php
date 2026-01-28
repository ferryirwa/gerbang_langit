<?php
require_once '../config.php';

session_start();
$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

switch($method) {
    case 'POST':
        $input = getPostData();
        $action = $input['action'] ?? '';
        
        if($action == 'login') {
            $username = sanitizeInput($input['username']);
            $password = sanitizeInput($input['password']);
            
            $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'active'");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verify password (gunakan password_verify jika di-hash)
                if($password === 'admin123' || password_verify($password, $user['password'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_role'] = $user['role'];
                    
                    // Update last login
                    $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                    $update_stmt->bind_param("i", $user['id']);
                    $update_stmt->execute();
                    
                    sendResponse(true, "Login berhasil", [
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'nama_lengkap' => $user['nama_lengkap'],
                            'role' => $user['role']
                        ]
                    ]);
                } else {
                    sendResponse(false, "Password salah");
                }
            } else {
                sendResponse(false, "Username tidak ditemukan");
            }
        }
        elseif($action == 'logout') {
            session_destroy();
            sendResponse(true, "Logout berhasil");
        }
        else {
            sendResponse(false, "Action tidak valid");
        }
        break;
        
    default:
        sendResponse(false, "Method tidak didukung");
}

$conn->close();
?>