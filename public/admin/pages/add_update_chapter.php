<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
include '../../../database/db.php';

// Khởi tạo biến cho các trường
$maChuong = '';
$tenChuong = '';
$mienPhi = '';
$thuTu = '';
$errorMessage = '';
$successMessage = '';

// Kiểm tra xem có mã chương để chỉnh sửa không
if (isset($_GET['id'])) {
    $maChuong = $_GET['id'];
    
    // Lấy thông tin chương học từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT MaChuong, TenChuong, ThuTu, MienPhi FROM chuonghoc WHERE MaChuong = ?");
    $stmt->execute([$maChuong]);
    $chuongHoc = $stmt->fetch();
    
    if ($chuongHoc) {
        $tenChuong = $chuongHoc['TenChuong'];
        $thuTu = $chuongHoc['ThuTu']; // Lấy thứ tự khi chỉnh sửa
        $mienPhi = $chuongHoc['MienPhi'];
    } else {
        $errorMessage = 'Chương học không tồn tại!';
    }
} else {
    // Lấy thứ tự lớn nhất nếu không có mã chương để chỉnh sửa
    $stmt = $conn->prepare("SELECT MAX(ThuTu) AS MaxThuTu FROM chuonghoc");
    $stmt->execute();
    $result = $stmt->fetch();
    $thuTu = $result['MaxThuTu'] ? $result['MaxThuTu'] + 1 : 1; // Tăng lên 1 hoặc đặt thành 1 nếu không có
}

// Xử lý thêm hoặc cập nhật chương học
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenChuong = $_POST['tenChuong'];
    $thuTu = $_POST['thuTu'];
    $mienPhi = $_POST['mienPhi'];

    if (empty($tenChuong)) {
        $errorMessage = 'Vui lòng điền tên chương học.';
    } else {
        if ($maChuong) {
            // Cập nhật chương học
            $stmt = $conn->prepare("UPDATE chuonghoc SET TenChuong = ?, ThuTu = ?, MienPhi = ? WHERE MaChuong = ?");
            if ($stmt->execute([$tenChuong, $thuTu, $mienPhi, $maChuong])) {
                $successMessage = 'Cập nhật chương học thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi cập nhật chương học!';
            }
        } else {
            // Thêm chương học mới
            $stmt = $conn->prepare("INSERT INTO chuonghoc (TenChuong, ThuTu, MienPhi) VALUES (?, ?, ?)");
            if ($stmt->execute([$tenChuong, $thuTu, $mienPhi])) {
                $successMessage = 'Thêm chương học thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi thêm chương học!';
            }
        }
    }
}
?>

<!doctype html>
<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="../assets/" data-template="vertical-menu-template-free" data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Quản lý chương học</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="../assets/vendor/fonts/remixicon/remixicon.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include 'sidebar.php'; ?>
            <div class="layout-page">
                <?php include 'navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="card">
                            <h5 class="card-header"><?php echo $maChuong ? 'Chỉnh sửa chương học' : 'Thêm chương học'; ?></h5>
                            <div class="card-body">
                                <?php if ($errorMessage): ?>
                                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                                <?php endif; ?>
                                <?php if ($successMessage): ?>
                                <div class="alert alert-success"><?php echo $successMessage; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="tenChuong" class="form-label">Tên chương</label>
                                        <input type="text" class="form-control" id="tenChuong" name="tenChuong"
                                            value="<?php echo htmlspecialchars($tenChuong); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tenChuong" class="form-label">Thứ Tự</label>
                                        <input type="text" class="form-control" id="thuTu" name="thuTu"
                                            value="<?php echo htmlspecialchars($thuTu); ?>" required readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mienPhi" class="form-label">Miễn Phí</label>
                                        <select class="form-control" id="mienPhi" name="mienPhi" required>
                                            <option value="0" <?php echo ($mienPhi == 0) ? 'selected' : ''; ?>>Miễn Phí</option>
                                            <option value="1" <?php echo ($mienPhi == 1) ? 'selected' : ''; ?>>Tính Phí</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><?php echo $maChuong ? 'Cập nhật' : 'Thêm mới'; ?></button>
                                    <a href="chapter_manager.php" class="btn btn-secondary">Quay lại</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include 'footer.php'; ?>
            </div>
        </div>
    </div>
</body>

</html>
