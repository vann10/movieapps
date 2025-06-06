<?php
// File: backend/reset_poster.php

// Memulai session dan memeriksa login melalui session_config.php
require_once 'session_config.php';
require_once 'db_connection.php'; // Koneksi ke database

header('Content-Type: application/json');

// 1. Cek jika user sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["success" => false, "message" => "Akses ditolak. Anda harus login terlebih dahulu."]);
    exit();
}

$logged_in_user_id = $_SESSION['user_id']; // ID pengguna yang sedang login

try {
    // Pastikan request adalah POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode request tidak diizinkan.');
    }

    // Validasi input: ID film (favorit) harus ada
    if (!isset($_POST['id'])) { // 'id' adalah ID dari tabel favorites
        throw new Exception("ID film favorit tidak ditemukan dalam request.");
    }

    $favorite_id = intval($_POST['id']);

    // 2. Dapatkan path custom_poster saat ini dan verifikasi kepemilikan
    $stmt_select = $conn->prepare("SELECT custom_poster FROM favorites WHERE id = ? AND user_id = ?");
    $stmt_select->bind_param("ii", $favorite_id, $logged_in_user_id);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();

    if ($result_select->num_rows === 0) {
        throw new Exception("Film favorit tidak ditemukan atau Anda tidak memiliki izin untuk mereset posternya.");
    }

    $film_data = $result_select->fetch_assoc();
    $current_custom_poster = $film_data['custom_poster'];
    $stmt_select->close();

    // Hapus file poster kustom fisik jika ada
    if ($current_custom_poster) {
        $file_path_to_delete = "../" . $current_custom_poster; // Path relatif dari file PHP ini ke poster
        if (file_exists($file_path_to_delete)) {
            if (!unlink($file_path_to_delete)) {
                // Opsional: Log error ini, tapi jangan hentikan proses reset di DB
                error_log("Gagal menghapus file poster fisik: " . $file_path_to_delete);
            }
        }
    }

    // 3. Update custom_poster menjadi NULL di database HANYA untuk film favorit milik user ini
    $stmt_update = $conn->prepare("UPDATE favorites SET custom_poster = NULL WHERE id = ? AND user_id = ?");
    $stmt_update->bind_param("ii", $favorite_id, $logged_in_user_id);

    if (!$stmt_update->execute()) {
        throw new Exception("Gagal mereset poster di database: " . $stmt_update->error);
    }
    $stmt_update->close();

    echo json_encode([
        "success" => true,
        "message" => "Poster berhasil direset ke default."
    ]);

} catch (Exception $e) {
    http_response_code(400); // Bad Request atau error lainnya
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
