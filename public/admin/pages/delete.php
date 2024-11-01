<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
include '../../../database/db.php';

/**
 * Hàm xóa bản ghi trong cơ sở dữ liệu.
 *
 * @param PDO $conn Kết nối cơ sở dữ liệu.
 * @param string $table Tên bảng trong cơ sở dữ liệu.
 * @param mixed $id Giá trị của khóa chính.
 * @param string $idColumn Tên cột khóa chính.
 * @return bool Trả về true nếu xóa thành công, false nếu không.
 */
function deleteRecord($conn, $table, $id, $idColumn) {
    // Chuẩn bị câu lệnh xóa
    $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$idColumn` = ?");
    return $stmt->execute([$id]);
}

// Kiểm tra thông tin từ URL
if (isset($_GET['id'], $_GET['table'], $_GET['location'], $_GET['idColumn'])) {
    $table = $_GET['table']; 
    $id = $_GET['id'];  
    $location = $_GET['location']; // Lấy giá trị từ tham số GET
    $idColumn = $_GET['idColumn']; // Lấy tên cột khóa chính từ tham số GET

    // Kiểm tra nếu bảng và ID không trống
    if (!empty($table) && !empty($id) && !empty($idColumn)) {
        // Gọi hàm xóa
        if (deleteRecord($conn, $table, $id, $idColumn)) {
            $successMessage = 'Xóa bản ghi thành công!';
        } else {
            $errorMessage = 'Có lỗi xảy ra khi xóa bản ghi!';
        }
    } else {
        $errorMessage = 'Thiếu thông tin cần thiết để xóa!';
    }
} else {
    $errorMessage = 'Không có thông tin để xóa!';
}

// Chuyển hướng về trang trước với thông báo
header('Location: ' . $location . '?' . http_build_query(['success' => $successMessage ?? '', 'error' => $errorMessage ?? '']));
exit();
?>
