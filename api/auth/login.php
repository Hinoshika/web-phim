<?php
session_start();
require '../../db/connect.php';
$conn = connect::getInstance()->getConnection();

header('Content-Type: application/json');

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin.']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && $password === $user['password']) {
    $_SESSION['user_id'] = $user['id'];

    echo json_encode([
        'success' => true,
        'username' => $user['username'],
        'avatar' => $user['avatar'] ?? null,
        'loai_tk' => $user['loai_tk'] ?? 'user' 
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Sai tài khoản hoặc mật khẩu.']);
}
