<?php
include __DIR__ . '/../../db/connect.php';
$conn = connect::getInstance()->getConnection();

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Không thể kết nối CSDL']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM anime WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Lỗi prepare: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi xoá: ' . $stmt->error]);
}
$stmt->close();
