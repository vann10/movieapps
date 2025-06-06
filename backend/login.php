<?php
// session_config.php akan menangani session_start()
require_once 'session_config.php'; 
require 'db_connection.php';

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$login_identifier = $input['username']; // Bisa username atau email
$password = $input['password'];
$remember_me = isset($input['remember']) && $input['remember'] === true;

$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $login_identifier, $login_identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // Buat session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        

        // Cookie
        if ($remember_me) {
            $token = bin2hex(random_bytes(32));
            $hashed_token = hash('sha256', $token);
            $user_id = $user['id'];

            $update_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_token, $user_id);
            $update_stmt->execute();
            
            // Cookie berlaku selama 30 hari
            setcookie('remember_me', $user_id . ':' . $token, time() + (30 * 24 * 60 * 60), "/");
        }
        
        echo json_encode(['success' => true, 'message' => 'Login berhasil!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Password salah.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Username atau Email tidak ditemukan.']);
}
$stmt->close();
$conn->close();
?>