<?php
include __DIR__ . '/../../db/connect.php';
$conn = connect::getInstance()->getConnection();

session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$anime_id = $input['anime_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập.']);
    exit;
}

if (!$anime_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu anime_id']);
    exit;   
}

$stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND anime_id = ?");
$stmt->bind_param("ii", $user_id, $anime_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $del = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND anime_id = ?");
    $del->bind_param("ii", $user_id, $anime_id);
    $del->execute();
    echo json_encode(['success' => true, 'liked' => false, 'message' => 'Đã bỏ thích']);
} else {
    $ins = $conn->prepare("INSERT INTO likes (user_id, anime_id) VALUES (?, ?)");
    $ins->bind_param("ii", $user_id, $anime_id);
    $ins->execute();
    echo json_encode(['success' => true, 'liked' => true, 'message' => 'Đã thêm yêu thích']);
}
