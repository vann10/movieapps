<?php
define('USE_REDIS', false); // Ubah ke true jika ingin pakai Redis

if (USE_REDIS && class_exists('Redis')) {
    require_once 'RedisSessionHandler.php';
    $handler = new RedisSessionHandler();
    session_set_save_handler($handler, true);
}

session_start();

// --- LOGIKA AUTO LOGIN DARI COOKIE ---
// Jalankan hanya jika user BELUM login via session, TAPI punya cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    require_once 'db_connection.php'; // Butuh koneksi DB

    list($user_id, $token) = explode(':', $_COOKIE['remember_me'], 2);

    if ($user_id && $token) {
        $stmt = $conn->prepare("SELECT remember_token, username FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $hashed_token_from_db = $user['remember_token'];
            $hashed_token_from_cookie = hash('sha256', $token);
            
            // Bandingkan token dengan aman
            if (hash_equals($hashed_token_from_db, $hashed_token_from_cookie)) {
                // Token cocok! Buat session untuk user
                $_SESSION['user_id'] = (int)$user_id;
                $_SESSION['username'] = $user['username'];
            }
        }
    }
}
?>