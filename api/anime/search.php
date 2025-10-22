<?php
include __DIR__ . '/../../db/connect.php';
$conn = connect::getInstance()->getConnection();

$genreFilter = $_GET['the_loai'] ?? '';
$searchKeyword = $_GET['search'] ?? '';

$sql = "SELECT * FROM anime WHERE 1=1";
$params = [];
$types = "";

if ($searchKeyword !== '') {
    $sql .= " AND tieu_de LIKE ?";
    $types .= "s";
    $params[] = "%$searchKeyword%";
}

if ($genreFilter !== '') {
    $sql .= " AND the_loai LIKE ?";
    $types .= "s";
    $params[] = "%$genreFilter%";
}

$sql .= " ORDER BY diem_trung_binh DESC";

$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
