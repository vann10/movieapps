<?php
require_once 'session_config.php';
header('Content-Type: application/json');

if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    echo json_encode([
        'loggedIn' => true,
        'username' => $_SESSION['username']
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}
?>