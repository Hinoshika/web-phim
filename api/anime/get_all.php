<?php
require '../../db/connect.php';
$conn = connect::getInstance()->getConnection();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$the_loai = isset($_GET['the_loai']) ? trim($_GET['the_loai']) : '';

$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
$types = ''; 

if ($search !== '') {
  $where .= " AND tieu_de LIKE ?";
  $params[] = "%$search%";
  $types .= 's';
}

if ($the_loai !== '') {
  $where .= " AND JSON_CONTAINS(genres, ?)";
  $params[] = json_encode($the_loai);
  $types .= 's';
}


$sqlCount = "SELECT COUNT(*) FROM anime $where";
$stmt = $conn->prepare($sqlCount);
if ($params) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();


$sqlData = "SELECT * FROM anime $where ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sqlData);


$paramsWithLimit = $params;
$typesWithLimit = $types . 'ii';
$paramsWithLimit[] = $limit;
$paramsWithLimit[] = $offset;


$stmt->bind_param($typesWithLimit, ...$paramsWithLimit);

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {

  if (isset($row['genres']) && $row['genres'] !== null) {
    $row['genres'] = json_decode($row['genres'], true);
  } else {
    $row['genres'] = [];
  }
  $data[] = $row;
}
$stmt->close();


echo json_encode([
  'total' => (int)$total,
  'items' => $data
]);
