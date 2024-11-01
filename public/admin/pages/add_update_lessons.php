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
$maBaiHoc = '';
$tenBai = '';
$noiDungLyThuyet = '';
$duongDanVideo = '';
$tenChuong = '';
$errorMessage = '';
$successMessage = '';

// Kiểm tra xem có mã bài học để chỉnh sửa không
if (isset($_GET['id'])) {
    $maBaiHoc = $_GET['id'];
    
    // Lấy thông tin bài học từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT b.TenBai, b.NoiDungLyThuyet, b.DuongDanVideo, c.TenChuong 
                             FROM baihoc b 
                             JOIN chuonghoc c ON b.MaChuong = c.MaChuong 
                             WHERE b.MaBaiHoc = ?");
    $stmt->execute([$maBaiHoc]);
    $baiHoc = $stmt->fetch();
    
    if ($baiHoc) {
        $tenBai = $baiHoc['TenBai'];
        $noiDungLyThuyet = $baiHoc['NoiDungLyThuyet'];
        $duongDanVideo = $baiHoc['DuongDanVideo'];
        $tenChuong = $baiHoc['TenChuong'];
    } else {
        $errorMessage = 'Bài học không tồn tại!';
    }
}

// Xử lý thêm hoặc cập nhật bài học
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenBai = $_POST['tenBai'];
    $noiDungLyThuyet = $_POST['noiDungLyThuyet'];
    $duongDanVideo = $_POST['duongDanVideo'];
    $maChuong = $_POST['maChuong']; // Cần truyền thêm mã chương

    if (empty($tenBai) || empty($noiDungLyThuyet)) {
        $errorMessage = 'Vui lòng điền tất cả các trường.';
    } else {
        if ($maBaiHoc) {
            // Cập nhật bài học
            $stmt = $conn->prepare("UPDATE baihoc SET TenBai = ?, NoiDungLyThuyet = ?, DuongDanVideo = ?, MaChuong = ? WHERE MaBaiHoc = ?");
            if ($stmt->execute([$tenBai, $noiDungLyThuyet, $duongDanVideo, $maChuong, $maBaiHoc])) {
                $successMessage = 'Cập nhật bài học thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi cập nhật bài học!';
            }
        } else {
            // Thêm bài học mới
            $stmt = $conn->prepare("INSERT INTO baihoc (TenBai, NoiDungLyThuyet, DuongDanVideo, MaChuong) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$tenBai, $noiDungLyThuyet, $duongDanVideo, $maChuong])) {
                $successMessage = 'Thêm bài học thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi thêm bài học!';
            }
        }
    }
}

// Lấy danh sách chương để hiển thị trong dropdown
$stmt = $conn->prepare("SELECT MaChuong, TenChuong FROM chuonghoc");
$stmt->execute();
$chuongList = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-style="light">
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Quản lý bài học</title>
    <meta name="description" content="" />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="../assets/vendor/fonts/remixicon/remixicon.css" />
    <!-- Menu waves for no-customizer fix -->
    <link rel="stylesheet" href="../assets/vendor/libs/node-waves/node-waves.css" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <!-- Page CSS -->
    <!-- Helpers -->
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
                            <h5 class="card-header"><?php echo $maBaiHoc ? 'Chỉnh sửa bài học' : 'Thêm bài học'; ?></h5>
                            <div class="card-body">
                                <?php if ($errorMessage): ?>
                                    <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                                <?php endif; ?>
                                <?php if ($successMessage): ?>
                                    <div class="alert alert-success"><?php echo $successMessage; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="tenBai" class="form-label">Tên bài học</label>
                                        <input type="text" class="form-control" id="tenBai" name="tenBai" value="<?php echo htmlspecialchars($tenBai); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="duongDanVideo" class="form-label">Đường dẫn video</label>
                                        <input type="text" class="form-control" id="duongDanVideo" name="duongDanVideo" value="<?php echo htmlspecialchars($duongDanVideo); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="maChuong" class="form-label">Chương</label>
                                        <select class="form-select" id="maChuong" name="maChuong" required>
                                            <?php foreach ($chuongList as $chuong): ?>
                                                <option value="<?php echo htmlspecialchars($chuong['MaChuong']); ?>" <?php echo ($tenChuong == $chuong['TenChuong']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($chuong['TenChuong']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="noiDungLyThuyet" class="form-label">Nội dung lý thuyết</label>
                                        <textarea class="form-control" id="noiDungLyThuyet" name="noiDungLyThuyet" rows="3" required><?php echo htmlspecialchars($noiDungLyThuyet); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><?php echo $maBaiHoc ? 'Cập nhật' : 'Thêm mới'; ?></button>
                                    <a href="manage_lessons.php" class="btn btn-secondary">Quay lại</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <?php include 'other.php'; ?>
</body>
</html>