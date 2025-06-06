<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "movie_database";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Koneksi gagal: " . $conn->connect_error]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


switch ($method) {
    case 'GET':
        $result = $conn->query("SELECT * FROM favorites ORDER BY created_at DESC");
        $favorites = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $favorites[] = $row;
            }
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
        
        $stmt = $conn->prepare("INSERT INTO favorites (movie_id, title, poster_path, status) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            http_response_code(500);
            echo json_encode(["error" => "Prepare statement error: " . $conn->error]);
            exit();
        }
        
        $stmt->bind_param('isss', $input['movie_id'], $input['title'], $input['poster_path'], $input['status']);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(["error" => "Execute error: " . $stmt->error]);
            exit();
        }
        
        echo json_encode(["success" => true, "id" => $conn->insert_id]);
        break;

    case 'PUT':
        parse_str(file_get_contents("php://input"), $input);
        if(!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID favorit tidak ditemukan"]);
            exit();
        }
        $id = (int) $input['id'];
        $rating = (isset($input['rating']) ? (int)$input['rating'] : null);
        $loved = (isset($input['loved']) ? (int)$input['loved'] : 0);
        $status = (isset($input['status'])) ? $input['status'] : 'watchlist';

        $stmt = $conn->prepare("UPDATE favorites SET rating=?, loved=?, status=? WHERE id=?");
        if ($stmt === false) {
            http_response_code(500);
            echo json_encode(["error" => "Prepare statement error: " . $conn->error]);
            exit();
        }
        
        $stmt->bind_param("iisi", $rating, $loved, $status, $id);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(["error" => "Execute error: " . $stmt->error]);
            exit();
        }

        echo json_encode(["updated" => true]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $input);
        if(!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID favorit tidak ditemukan"]);
            exit();
        }

        $id = (int) $input['id'];
        $stmt = $conn->prepare("DELETE FROM favorites WHERE id=?");
        if ($stmt === false) {
            http_response_code(500);
            echo json_encode(["error" => "Prepare statement error: " . $conn->error]);
            exit();
        }
        
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(["error" => "Execute error: " . $stmt->error]);
            exit();
        }

        echo json_encode(["deleted" => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method Error"]);
        break;
    }

$conn->close();
?>