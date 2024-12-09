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
$maNoiCot = '';
$cauHoi = '';
$cauTraLoi = '';
$errorMessage = '';
$successMessage = '';

// Kiểm tra xem có mã nối cột để chỉnh sửa không
if (isset($_GET['id'])) {
    $maNoiCot = $_GET['id'];
    
    // Lấy thông tin nối cột từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT MaNoiCot, CauHoi, CauTraLoi FROM cauhoivui WHERE MaNoiCot = ?");
    $stmt->execute([$maNoiCot]);
    $noiCot = $stmt->fetch();
    
    if ($noiCot) {
        $cauHoi = $noiCot['CauHoi'];
        $cauTraLoi = $noiCot['CauTraLoi']; 
    } else {
        $errorMessage = 'Bài tập không tồn tại!';
    }
} 

// Xử lý thêm hoặc cập nhật bài tập nối cột
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cauHoi = $_POST['CauHoi'];
    $cauTraLoi = $_POST['CauTraLoi'];

    if (empty($cauHoi)) {
        $errorMessage = 'Vui lòng điền câu hỏi.';
    } else {
        if ($maNoiCot) {
            // Cập nhật bài tập
            $stmt = $conn->prepare("UPDATE cauhoivui SET CauHoi = ?, CauTraLoi = ? WHERE MaNoiCot = ?");
            if ($stmt->execute([$cauHoi, $cauTraLoi, $maNoiCot])) {
                $successMessage = 'Cập nhật bài tập thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi cập nhật bài tập!';
            }
        } else {
            // Thêm bài tập mới
            $stmt = $conn->prepare("INSERT INTO cauhoivui (CauHoi, CauTraLoi) VALUES (?, ?)");
            if ($stmt->execute([$cauHoi, $cauTraLoi])) {
                $successMessage = 'Thêm bài tập thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi thêm bài tập!';
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
    <title>Quản lý câu hỏi vui</title>
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
                            <h5 class="card-header"><?php echo $maNoiCot ? 'Chỉnh sửa câu hỏi vui' : 'Thêm câu hỏi vui'; ?></h5>
                            <div class="card-body">
                                <?php if ($errorMessage): ?>
                                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                                <?php endif; ?>
                                <?php if ($successMessage): ?>
                                <div class="alert alert-success"><?php echo $successMessage; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="CauHoi" class="form-label">Câu Hỏi</label>
                                        <input type="text" class="form-control" id="CauHoi" name="CauHoi"
                                            value="<?php echo htmlspecialchars($cauHoi); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="CauTraLoi" class="form-label">Câu Trả Lời</label>
                                        <input type="text" class="form-control" id="CauTraLoi" name="CauTraLoi"
                                            value="<?php echo htmlspecialchars($cauTraLoi); ?>" required >
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary"><?php echo $maNoiCot ? 'Cập nhật' : 'Thêm mới'; ?></button>
                                    <a href="exercises_manage.php" class="btn btn-secondary">Quay lại</a>
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
