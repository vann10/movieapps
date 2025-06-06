<?php
header('Content-Type: application/json');

try {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'movie_database'; 
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    $debug_info = [];
    $debug_info['request_method'] = $_SERVER['REQUEST_METHOD'];
    $debug_info['post_data'] = $_POST;
    $debug_info['files_data'] = isset($_FILES) ? array_keys($_FILES) : 'No files';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode tidak diizinkan.');
    }

    if (!isset($_FILES['poster']) || !isset($_POST['film_id'])) {
        $debug_info['missing'] = [
            'poster' => !isset($_FILES['poster']),
            'film_id' => !isset($_POST['film_id'])
        ];
        throw new Exception("Data tidak lengkap.");
    }

    $film_id = intval($_POST['film_id']);
    $debug_info['film_id'] = $film_id;

    $check_query = "SELECT id FROM favorites WHERE id = $film_id";
    $result = $conn->query($check_query);
    
    if (!$result || $result->num_rows === 0) {
        $check_query = "SELECT id FROM favorites WHERE movie_id = $film_id";
        $result = $conn->query($check_query);
        
        if (!$result || $result->num_rows === 0) {
            throw new Exception("Film dengan ID $film_id tidak ditemukan.");
        }
        
        
        $row = $result->fetch_assoc();
        $film_id = $row['id'];
        $debug_info['film_id_adjusted'] = $film_id;
    }

    $file = $_FILES['poster'];
    $file_name = basename($file["name"]);
    $file_tmp = $file["tmp_name"];
    $file_size = $file["size"];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $debug_info['file_info'] = $file;

    // Validasi ekstensi
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_ext, $allowed_ext)) {
        throw new Exception("Format file tidak valid.");
    }

    // Validasi ukuran file (maks 2MB)
    if ($file_size > 2 * 1024 * 1024) {
        throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
    }

    // Create folder upload
    $target_dir = "../posters/";
    if (!is_dir($target_dir)) {
        $mkdir_result = mkdir($target_dir, 0777, true);
        $debug_info['mkdir_result'] = $mkdir_result ? "Success" : "Failed";
        if (!$mkdir_result) {
            throw new Exception("Gagal membuat folder upload.");
        }
    }

    // Nama file
    $new_file_name = "poster_" . $film_id . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $new_file_name;
    $debug_info['target_file'] = $target_file;

    // Upload file
    $upload_result = move_uploaded_file($file_tmp, $target_file);
    $debug_info['move_uploaded_file'] = $upload_result ? "Success" : "Failed";

    if (!$upload_result) {
        throw new Exception("Gagal memindahkan file.");
    }

    $poster_path = "posters/" . $new_file_name;
    $escaped_path = $conn->real_escape_string($poster_path);
    $sql = "UPDATE favorites SET custom_poster = '$escaped_path' WHERE id = $film_id";
    $debug_info['sql'] = $sql;

    $query_result = $conn->query($sql);
    $debug_info['query_result'] = $query_result ? "Success" : "Failed: " . $conn->error;

    if (!$query_result) {
        throw new Exception("Gagal menyimpan ke database: " . $conn->error);
    }

    echo json_encode([
        "success" => true,
        "message" => "Poster berhasil diunggah.",
        "poster_path" => $poster_path,
        "debug" => $debug_info
    ]);
    exit;
} catch (Exception $e) {
    $debug = ob_get_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
        "debug" => $debug_info ?? []
    ]);
    exit;
}