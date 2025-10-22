<?php
require '../../db/connect.php';
$conn = connect::getInstance()->getConnection();

header('Content-Type: application/json');

$sql = "SELECT id, tieu_de, the_loai, diem_trung_binh, image FROM anime ORDER BY id DESC";
$result = $conn->query($sql);

$animes = [];
while ($row = $result->fetch_assoc()) {
    $animes[] = $row;
}

echo json_encode($animes);
