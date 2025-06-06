<?php

require_once 'session_config.php';
require_once 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); 
    echo json_encode(["error" => "Anda harus login untuk mengakses halaman ini"]);
    exit();
}

$logged_in_user_id = $_SESSION['user_id'];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // HANYA ambil data milik user yang sedang login
        $stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $logged_in_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $favorites = [];
        while ($row = $result->fetch_assoc()) {
            $favorites[] = $row;
        }
        echo json_encode($favorites);
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        if(!isset($input['movie_id'], $input['title'], $input['poster_path'], $input['status'])) {
            http_response_code(400);
            echo json_encode(["error" => "Data tidak lengkap"]);
            exit();
        }
        
        $stmt = $conn->prepare("INSERT INTO favorites (movie_id, title, poster_path, status, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('isssi', $input['movie_id'], $input['title'], $input['poster_path'], $input['status'], $logged_in_user_id);
        
        if (!$stmt->execute()) {
            if ($conn->errno == 1062) { // Error untuk duplikat entri
                http_response_code(409); // Conflict
                echo json_encode(["error" => "Film ini sudah ada di daftar favorit Anda."]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Gagal menyimpan ke database: " . $stmt->error]);
            }
            exit();
        }
        
        echo json_encode(["success" => true, "id" => $conn->insert_id, "message" => "Film berhasil ditambahkan!"]);
        break;

    case 'PUT':
        // Logika update yang lebih baik dan aman
        parse_str(file_get_contents("php://input"), $input);
        
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID favorit tidak ditemukan"]);
            exit();
        }
        
        $id = (int)$input['id'];
        
        $fields = [];
        $params = [];
        $types = "";

        if (isset($input['rating'])) {
            $fields[] = "rating = ?";
            $params[] = (int)$input['rating'];
            $types .= "i";
        }
        if (isset($input['loved'])) {
            $fields[] = "loved = ?";
            $params[] = (int)$input['loved'];
            $types .= "i";
        }
        if (isset($input['status'])) {
            $fields[] = "status = ?";
            $params[] = $input['status'];
            $types .= "s";
        }

        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(["error" => "Tidak ada data untuk diperbarui."]);
            exit();
        }
        
        $params[] = $id;
        $params[] = $logged_in_user_id;
        $types .= "ii";

        $sql = "UPDATE favorites SET " . implode(", ", $fields) . " WHERE id = ? AND user_id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["error" => "Gagal menyiapkan statement: " . $conn->error]);
            exit();
        }
        
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(["error" => "Gagal memperbarui data: " . $stmt->error]);
            exit();
        }

        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Favorit berhasil diperbarui."]);
        } else {
            // Ini terjadi jika ID tidak ditemukan atau data tidak berubah
            echo json_encode(["success" => false, "message" => "Tidak ada data yang diperbarui. Mungkin ID tidak ditemukan atau Anda tidak memiliki izin."]);
        }
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $input);
        if(!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID favorit tidak ditemukan"]);
            exit();
        }

        $id = (int)$input['id'];
        $stmt = $conn->prepare("DELETE FROM favorites WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $logged_in_user_id);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(["error" => "Gagal menghapus: " . $stmt->error]);
            exit();
        }

        if ($stmt->affected_rows > 0) {
            echo json_encode(["deleted" => true, "message" => "Film berhasil dihapus."]);
        } else {
            echo json_encode(["deleted" => false, "message" => "Film tidak ditemukan atau Anda tidak punya izin."]);
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["error" => "Metode request tidak diizinkan"]);
        break;
}

$conn->close();
?>