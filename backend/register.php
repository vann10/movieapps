<?php
require 'db_connection.php';

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Semua kolom harus diisi']);
    exit;
}

$username = $input['username'];
$email = $input['email'];
$password = password_hash($input['password'], PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registrasi berhasil! Silakan login.']);
} else {
    // Cek error duplikat
    if ($conn->errno == 1062) {
         echo json_encode(['success' => false, 'message' => 'Username atau Email sudah digunakan.']);
    } else {
         echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan.']);
    }
}
$stmt->close();
$conn->close();
?>