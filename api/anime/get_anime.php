<?php
include __DIR__ . '/../../db/connect.php';
$conn = connect::getInstance()->getConnection();

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['error' => 'ID không hợp lệ']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM anime WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Anime không tồn tại']);
    exit;
}
$anime = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT so_tap, link FROM tap_phim WHERE id_anime = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$anime['episodes'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($anime);
