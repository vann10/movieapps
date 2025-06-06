<?php
header('Content-Type: application/json');

try {
    ob_start();

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

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode tidak diizinkan.');
    }

    if (!isset($_POST['id'])) {
        throw new Exception("ID film tidak ditemukan.");
    }

    $id = intval($_POST['id']);
    $debug_info['id'] = $id;

    $check_query = "SELECT id, custom_poster FROM favorites WHERE id = $id";
    $result = $conn->query($check_query);
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception("Film dengan ID $id tidak ditemukan.");
    }
    
    $film_data = $result->fetch_assoc();
    $custom_poster_path = $film_data['custom_poster'];
    $debug_info['current_poster'] = $custom_poster_path;
    
    if ($custom_poster_path && file_exists("../" . $custom_poster_path)) {
        $delete_result = unlink("../" . $custom_poster_path);
        $debug_info['file_delete'] = $delete_result ? "Success" : "Failed";
    }

    $sql = "UPDATE favorites SET custom_poster = NULL WHERE id = $id";
    $debug_info['sql'] = $sql;

    $query_result = $conn->query($sql);
    $debug_info['query_result'] = $query_result ? "Success" : "Failed: " . $conn->error;

    if (!$query_result) {
        throw new Exception("Gagal mereset poster di database: " . $conn->error);
    }

    echo json_encode([
        "success" => true,
        "message" => "Poster berhasil direset ke default.",
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