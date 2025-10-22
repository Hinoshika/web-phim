<?php
include __DIR__ . '/../../db/connect.php';
$conn = connect::getInstance()->getConnection();

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? 0);
$tieu_de = $data['tieu_de'] ?? '';
$the_loai = $data['the_loai'] ?? '';
$anh_bia = $data['anh_bia'] ?? '';
$diem_trung_binh = $data['diem_trung_binh'] ?? 0;

if ($id <= 0 || !$tieu_de || !$the_loai || !$anh_bia) {
    echo json_encode(['success' => false, 'message' => 'Thiếu hoặc sai dữ liệu']);
    exit;
}

$stmt = $conn->prepare("UPDATE anime SET tieu_de=?, the_loai=?, anh_bia=?, diem_trung_binh=? WHERE id=?");
$stmt->bind_param("sssdi", $tieu_de, $the_loai, $anh_bia, $diem_trung_binh, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
}
