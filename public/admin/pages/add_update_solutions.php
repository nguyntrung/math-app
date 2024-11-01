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
$maBaiGiai = '';
$tenBai = '';
$bai = '';
$loiGiai = '';
$thuTu = '';
$errorMessage = '';
$successMessage = '';

// Kiểm tra xem có mã bài giải để chỉnh sửa không
if (isset($_GET['id'])) {
    $maBaiGiai = $_GET['id'];
    
    // Lấy thông tin bài giải từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT bg.Bai, bg.LoiGiai, bg.ThuTu, bh.TenBai 
                            FROM baigiai bg 
                            JOIN baihoc bh ON bg.MaBaiHoc = bh.MaBaiHoc 
                            WHERE bg.MaBaiGiai = ?");
    $stmt->execute([$maBaiGiai]);
    $baiGiai = $stmt->fetch();
    
    if ($baiGiai) {
        $bai = $baiGiai['Bai'];
        $loiGiai = $baiGiai['LoiGiai'];
        $thuTu = $baiGiai['ThuTu'];
        $tenBai = $baiGiai['TenBai'];
    } else {
        $errorMessage = 'Bài giải không tồn tại!';
    }
}

// Xử lý thêm hoặc cập nhật bài giải
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bai = $_POST['bai'];
    $loiGiai = $_POST['loiGiai'];
    $thuTu = $_POST['thuTu'];
    $maBaiHoc = $_POST['maBaiHoc']; // Mã bài học

    if (empty($bai) || empty($loiGiai)) {
        $errorMessage = 'Vui lòng điền tất cả các trường.';
    } else {
        if ($maBaiGiai) {
            // Cập nhật bài giải
            $stmt = $conn->prepare("UPDATE baigiai SET Bai = ?, LoiGiai = ?, ThuTu = ?, MaBaiHoc = ? WHERE MaBaiGiai = ?");
            if ($stmt->execute([$bai, $loiGiai, $thuTu, $maBaiHoc, $maBaiGiai])) {
                $successMessage = 'Cập nhật bài giải thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi cập nhật bài giải!';
            }
        } else {
            // Thêm bài giải mới
            $stmt = $conn->prepare("INSERT INTO baigiai (Bai, LoiGiai, ThuTu, MaBaiHoc) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$bai, $loiGiai, $thuTu, $maBaiHoc])) {
                $successMessage = 'Thêm bài giải thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi thêm bài giải!';
            }
        }
    }
}

// Lấy danh sách bài học để hiển thị trong dropdown
$stmt = $conn->prepare("SELECT MaBaiHoc, TenBai FROM baihoc");
$stmt->execute();
$baiHocList = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="../assets/" data-template="vertical-menu-template-free" data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Quản lý bài giải</title>
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
                            <h5 class="card-header"><?php echo $maBaiGiai ? 'Chỉnh sửa bài giải' : 'Thêm bài giải'; ?>
                            </h5>
                            <div class="card-body">
                                <?php if ($errorMessage): ?>
                                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                                <?php endif; ?>
                                <?php if ($successMessage): ?>
                                <div class="alert alert-success"><?php echo $successMessage; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="maBaiHoc" class="form-label">Bài học</label>
                                        <select class="form-select" id="maBaiHoc" name="maBaiHoc" required>
                                            <?php foreach ($baiHocList as $baiHoc): ?>
                                            <option value="<?php echo htmlspecialchars($baiHoc['MaBaiHoc']); ?>"
                                                <?php echo ($tenBai == $baiHoc['TenBai']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($baiHoc['TenBai']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bai" class="form-label">Câu Hỏi</label>
                                        <input type="text" class="form-control" id="bai" name="bai"
                                            value="<?php echo htmlspecialchars($bai); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="loiGiai" class="form-label">Lời giải</label>
                                        <input type="text" class="form-control" id="loiGiai" name="loiGiai"
                                            value="<?php echo htmlspecialchars($loiGiai); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="thuTu" class="form-label">Thứ tự</label>
                                        <input type="number" class="form-control" id="thuTu" name="thuTu"
                                            value="<?php echo htmlspecialchars($thuTu); ?>" required>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary"><?php echo $maBaiGiai ? 'Cập nhật' : 'Thêm mới'; ?></button>
                                    <a href="solutions_manager.php" class="btn btn-secondary">Quay lại</a>
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