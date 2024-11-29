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
$maBaiTap = '';
$so1 = '';
$so2 = '';
$so3 = '';
$so4 = '';
$so5 = '';
$ketQua = '';
$phepToan = '';
$thuTu = '';
$errorMessage = '';
$successMessage = '';

// Kiểm tra xem có mã bài tập để chỉnh sửa không
if (isset($_GET['id'])) {
    $maBaiTap = $_GET['id'];
    
    // Lấy thông tin chương học từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT MaBaiTap, So1, So2, So3, So4, So5, KetQua, PhepToan, ThuTu FROM cauhoiontap WHERE MaBaiTap = ?");
    $stmt->execute([$maBaiTap]);
    $cauhoiOnTap = $stmt->fetch();
    
    if ($cauhoiOnTap) {
        $so1 = $cauhoiOnTap['So1'];
        $so2 = $cauhoiOnTap['So2'];
        $so3 = $cauhoiOnTap['So3'];
        $so4 = $cauhoiOnTap['So4'];
        $so5 = $cauhoiOnTap['So5'];
        $ketQua = $cauhoiOnTap['KetQua'];
        $phepToan = $cauhoiOnTap['PhepToan'];
        $thuTu = $cauhoiOnTap['ThuTu']; // Lấy thứ tự khi chỉnh sửa
    } else {
        $errorMessage = 'Câu hỏi ôn tập không tồn tại!';
    }
} else {
    // Lấy thứ tự lớn nhất nếu không có mã bài tập để chỉnh sửa
    $stmt = $conn->prepare("SELECT MAX(ThuTu) AS MaxThuTu FROM cauhoiontap");
    $stmt->execute();
    $result = $stmt->fetch();
    $thuTu = $result['MaxThuTu'] ? $result['MaxThuTu'] + 1 : 1; // Tăng lên 1 hoặc đặt thành 1 nếu không có
}

// Xử lý thêm hoặc cập nhật chương học
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $so1 = $_POST['So1'];
    $so2 = $_POST['So2'];
    $so3 = $_POST['So3'];
    $so4 = $_POST['So4'];
    $so5 = $_POST['So5'];
    $ketQua = $_POST['KetQua'];
    $phepToan = $_POST['PhepToan'];
    $thuTu = $_POST['ThuTu'];

        if ($maBaiTap) {
            // Cập nhật câu hỏi ôn tập
            $stmt = $conn->prepare("UPDATE cauhoiontap SET So1 = ?, So2 = ?, So3 = ?, So4 = ?, So5 = ?, KetQua = ?, PhepToan = ?, ThuTu = ? WHERE MaBaiTap = ?");
            if ($stmt->execute([$so1, $so2, $so3, $so4, $so5, $ketQua, $phepToan, $thuTu, $maBaiTap])) {
                $successMessage = 'Cập nhật câu hỏi ôn tập thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi cập nhật câu hỏi ôn tập!';
            }
        } else {
            // Thêm câu hỏi ôn tập mới
            $stmt = $conn->prepare("INSERT INTO cauhoiontap (So1, So2, So3, So4, So5, KetQua, PhepToan, ThuTu) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$so1, $so2, $so3, $so4, $so5, $ketQua, $phepToan, $thuTu])) {
                $successMessage = 'Thêm câu hỏi ôn tập thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi thêm câu hỏi ôn tập!';
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
    <title>Quản lý câu hỏi ôn tập</title>
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
                            <h5 class="card-header"><?php echo $maBaiTap ? 'Chỉnh sửa câu hỏi' : 'Thêm câu hỏi'; ?></h5>
                            <div class="card-body">
                                <?php if ($errorMessage): ?>
                                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                                <?php endif; ?>
                                <?php if ($successMessage): ?>
                                <div class="alert alert-success"><?php echo $successMessage; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="So1" class="form-label">Số 1</label>
                                        <input type="text" class="form-control" id="So1" name="So1"
                                            value="<?php echo htmlspecialchars($so1); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="So2" class="form-label">Số 2</label>
                                        <input type="text" class="form-control" id="So2" name="So2"
                                            value="<?php echo htmlspecialchars($so2); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="So3" class="form-label">Số 3</label>
                                        <input type="text" class="form-control" id="So3" name="So3"
                                            value="<?php echo htmlspecialchars($so3); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="So4" class="form-label">Số 4</label>
                                        <input type="text" class="form-control" id="So4" name="So4"
                                            value="<?php echo htmlspecialchars($so4); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="So5" class="form-label">Số 5</label>
                                        <input type="text" class="form-control" id="So5" name="So5"
                                            value="<?php echo htmlspecialchars($so5); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="KetQua" class="form-label">Kết Quả</label>
                                        <input type="text" class="form-control" id="KetQua" name="KetQua"
                                            value="<?php echo htmlspecialchars($ketQua); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="PhepToan" class="form-label">Phép Toán</label>
                                        <input type="text" class="form-control" id="PhepToan" name="PhepToan"
                                            value="<?php echo htmlspecialchars($phepToan); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ThuTu" class="form-label">Thứ Tự</label>
                                        <input type="text" class="form-control" id="ThuTu" name="ThuTu"
                                            value="<?php echo htmlspecialchars($thuTu); ?>" required readonly>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><?php echo $maBaiTap ? 'Cập nhật' : 'Thêm mới'; ?></button>
                                    <a href="question_management.php" class="btn btn-secondary">Quay lại</a>
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
