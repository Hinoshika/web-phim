<?php
session_start();
require '../../db/connect.php';
$conn = connect::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu JSON
    $data = json_decode(file_get_contents("php://input"), true);

    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';
    $name = $data['name'] ?? '';
    $loai_tk = 'user';
    $avatar = null;

    // Kiểm tra dữ liệu đầu vào
    if ($username === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên đăng nhập và mật khẩu.']);
        exit;
    }

    // Kiểm tra username đã tồn tại chưa
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại.']);
        exit;
    }

    // Thêm người dùng (KHÔNG mã hóa mật khẩu)
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, name, loai_tk, avatar) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $password, $email, $name, $loai_tk, $avatar);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        $_SESSION['loai_tk'] = $loai_tk;

        echo json_encode([
            'success' => true,
            'message' => 'Đăng ký thành công!',
            'username' => $username,
            'loai_tk' => $loai_tk,
            'avatar' => $avatar
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi đăng ký: ' . $stmt->error]);
    }
}
?>
