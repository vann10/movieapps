<?php
// File: backend/upload_poster.php

// Memulai session dan memeriksa login melalui session_config.php
// Ini juga akan menangani auto-login via cookie jika session belum ada.
require_once 'session_config.php';
require_once 'db_connection.php'; // Koneksi ke database

header('Content-Type: application/json');

// 1. Cek jika user sudah login (dilakukan di session_config.php, tapi kita bisa periksa lagi di sini)
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

    // Validasi input: file poster dan ID film (favorit) harus ada
    if (!isset($_FILES['poster']) || !isset($_POST['film_id'])) {
        throw new Exception("Data tidak lengkap. File poster dan ID film diperlukan.");
    }

    $favorite_id = intval($_POST['film_id']); // Ini adalah ID dari tabel 'favorites'

    // 2. Verifikasi bahwa film favorit ini milik pengguna yang sedang login
    $stmt_check = $conn->prepare("SELECT id FROM favorites WHERE id = ? AND user_id = ?");
    $stmt_check->bind_param("ii", $favorite_id, $logged_in_user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        throw new Exception("Film favorit tidak ditemukan atau Anda tidak memiliki izin untuk mengubahnya.");
    }
    $stmt_check->close();

    // Proses file upload
    $file = $_FILES['poster'];
    $file_name = basename($file["name"]);
    $file_tmp = $file["tmp_name"];
    $file_size = $file["size"];
    $file_error = $file["error"];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Cek jika ada error saat upload
    if ($file_error !== UPLOAD_ERR_OK) {
        throw new Exception("Terjadi kesalahan saat mengunggah file. Kode Error: " . $file_error);
    }

    // Validasi ekstensi file
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_ext, $allowed_ext)) {
        throw new Exception("Format file tidak valid. Hanya JPG, JPEG, PNG, GIF yang diizinkan.");
    }

    // Validasi ukuran file (misalnya, maks 2MB)
    if ($file_size > 2 * 1024 * 1024) {
        throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
    }

    // Buat folder 'posters' jika belum ada
    $target_dir = "../posters/"; // Sesuaikan path jika perlu
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            throw new Exception("Gagal membuat folder untuk menyimpan poster.");
        }
    }

    // Buat nama file yang unik untuk menghindari penimpaan
    $new_file_name = "poster_fav" . $favorite_id . "_user" . $logged_in_user_id . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $new_file_name;

    // Pindahkan file yang diupload ke direktori tujuan
    if (!move_uploaded_file($file_tmp, $target_file)) {
        throw new Exception("Gagal memindahkan file yang diunggah.");
    }

    // Path yang akan disimpan ke database (relatif terhadap root aplikasi Anda)
    $poster_path_db = "posters/" . $new_file_name;

    // 3. Update path custom_poster di database HANYA untuk film favorit milik user ini
    $stmt_update = $conn->prepare("UPDATE favorites SET custom_poster = ? WHERE id = ? AND user_id = ?");
    $stmt_update->bind_param("sii", $poster_path_db, $favorite_id, $logged_in_user_id);
    
    if (!$stmt_update->execute()) {
        // Jika update gagal, hapus file yang sudah terupload untuk konsistensi
        if (file_exists($target_file)) {
            unlink($target_file);
        }
        throw new Exception("Gagal menyimpan path poster ke database: " . $stmt_update->error);
    }
    $stmt_update->close();

    echo json_encode([
        "success" => true,
        "message" => "Poster berhasil diunggah dan diperbarui.",
        "poster_path" => $poster_path_db
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