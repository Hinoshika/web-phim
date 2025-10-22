<?php
session_start();
include '../../db/connect.php';
$conn = connect::getInstance()->getConnection();

if (!isset($_SESSION['user_id'])) {
    echo "Bạn chưa đăng nhập.";
    exit;
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, username, name, email, avatar, loai_tk FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $avatar = $_POST['avatar'] ?? '';

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, avatar = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $email, $avatar, $id);
    if ($stmt->execute()) {
        $message = "Cập nhật thành công!";
        $user['name'] = $name;
        $user['email'] = $email;
        $user['avatar'] = $avatar;
    } else {
        $message = "Lỗi khi cập nhật thông tin!";
    }
}
?>