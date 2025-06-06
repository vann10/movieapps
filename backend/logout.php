<?php
require_once 'session_config.php';
require 'db_connection.php';

// Hapus remember_token dari database
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
}

// Hapus cookie
if (isset($_COOKIE['remember_me'])) {
    unset($_COOKIE['remember_me']);
    setcookie('remember_me', '', time() - 3600, '/'); 
}

// Hancurkan session
session_unset();
session_destroy();

header("Location: ../auth.html");
exit();
?>