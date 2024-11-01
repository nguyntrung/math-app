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
$maCauHoi = '';
$noiDung = '';
$loiGiai = '';
$giaiThich = '';
$tenBai = '';
$errorMessage = '';
$successMessage = '';

// Kiểm tra xem có mã câu hỏi để chỉnh sửa không
if (isset($_GET['id'])) {
    $maCauHoi = $_GET['id'];
    
    // Lấy thông tin câu hỏi từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT c.MaCauHoi, c.MaBaiHoc, c.NoiDung, c.LoiGiai, c.GiaiThich, bh.TenBai 
                            FROM cauhoituluan c 
                            JOIN baihoc bh ON c.MaBaiHoc = bh.MaBaiHoc 
                            WHERE c.MaCauHoi = ?");
    $stmt->execute([$maCauHoi]);
    $cauHoi = $stmt->fetch();
    
    if ($cauHoi) {
        $noiDung = $cauHoi['NoiDung'];
        $loiGiai = $cauHoi['LoiGiai'];
        $giaiThich = $cauHoi['GiaiThich'];
        $tenBai = $cauHoi['TenBai'];
    } else {
        $errorMessage = 'Câu hỏi không tồn tại!';
    }
}

// Xử lý thêm hoặc cập nhật câu hỏi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $noiDung = $_POST['noiDung'];
    $loiGiai = $_POST['loiGiai'];
    $giaiThich = $_POST['giaiThich']; 
    $maBaiHoc = $_POST['maBaiHoc']; // Mã bài học

    if (empty($noiDung) || empty($giaiThich)) {
        $errorMessage = 'Vui lòng điền tất cả các trường.';
    } else {
        if ($maCauHoi) {
            // Cập nhật câu hỏi
            $stmt = $conn->prepare("UPDATE cauhoituluan SET NoiDung = ?, LoiGiai = ?, GiaiThich = ?, MaBaiHoc = ? WHERE MaCauHoi = ?");
            if ($stmt->execute([$noiDung, $loiGiai, $giaiThich, $maBaiHoc, $maCauHoi])) {
                $successMessage = 'Cập nhật câu hỏi thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi cập nhật câu hỏi!';
            }
        } else {
            // Thêm câu hỏi mới
            $stmt = $conn->prepare("INSERT INTO cauhoituluan (NoiDung, LoiGiai, GiaiThich, MaBaiHoc) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$noiDung, $loiGiai ,$giaiThich, $maBaiHoc])) {
                $successMessage = 'Thêm câu hỏi thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi thêm câu hỏi!';
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
    <title>Quản lý câu hỏi tự luận</title>
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
                            <h5 class="card-header"><?php echo $maCauHoi ? 'Chỉnh sửa câu hỏi tự luận' : 'Thêm câu hỏi tự luận'; ?></h5>
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
                                        <label for="noiDung" class="form-label">Nội dung câu hỏi</label>
                                        <input type="text" class="form-control" id="noiDung" name="noiDung"
                                            value="<?php echo htmlspecialchars($noiDung); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="giaiThich" class="form-label">Lời Giải</label>
                                        <textarea class="form-control" id="loiGiai" name="loiGiai" rows="4" required><?php echo htmlspecialchars($loiGiai); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="giaiThich" class="form-label">Giải Thích</label>
                                        <textarea class="form-control" id="giaiThich" name="giaiThich" rows="4" required><?php echo htmlspecialchars($giaiThich); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><?php echo $maCauHoi ? 'Cập nhật' : 'Thêm mới'; ?></button>
                                    <a href="essay_manager.php" class="btn btn-secondary">Quay lại</a>
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
