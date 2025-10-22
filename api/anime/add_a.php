<?php
include __DIR__ . '/../../db/connect.php';
$conn = connect::getInstance()->getConnection();

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$tieu_de = $data['tieu_de'] ?? '';
$the_loai = $data['the_loai'] ?? '';
$anh_bia = $data['anh_bia'] ?? '';
$diem_trung_binh = $data['diem_trung_binh'] ?? 0;

if (!$tieu_de || !$the_loai || !$anh_bia) {
    echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO anime (tieu_de, the_loai, anh_bia, diem_trung_binh) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssd", $tieu_de, $the_loai, $anh_bia, $diem_trung_binh);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Thêm thất bại']);
}
