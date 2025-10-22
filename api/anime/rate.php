<?php
include __DIR__ . '/../../db/connect.php';
$conn = connect::getInstance()->getConnection();

session_start();

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$anime_id = intval($data['anime_id'] ?? 0);
$rating = intval($data['rating'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($anime_id === 0 || $rating < 1 || $rating > 10) {
  echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
  exit;
}

$rating_percent = $rating * 10;

$stmt = $conn->prepare("INSERT INTO rate (user_id, anime_id, rating) VALUES (?, ?, ?) 
  ON DUPLICATE KEY UPDATE rating = VALUES(rating)");
$stmt->bind_param("iii", $user_id, $anime_id, $rating_percent);
$stmt->execute();

$result = $conn->query("SELECT AVG(rating) AS avg_rating FROM rate WHERE anime_id = $anime_id");
$avg = $result->fetch_assoc();
$new_avg = round($avg['avg_rating']);

$stmt = $conn->prepare("UPDATE anime SET diem_trung_binh = ? WHERE id = ?");
$stmt->bind_param("ii", $new_avg, $anime_id);
$stmt->execute();

echo json_encode(['success' => true, 'new_average' => $new_avg]);
?>
