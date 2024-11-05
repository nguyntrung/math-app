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
$dapAnA = '';
$dapAnB = '';
$dapAnC = '';
$dapAnD = '';
$dapAnDung = '';
$tenBai = '';
$giaiThich = '';
$errorMessage = '';
$successMessage = '';

// Kiểm tra xem có mã câu hỏi để chỉnh sửa không
if (isset($_GET['id'])) {
    $maCauHoi = $_GET['id'];
    
    // Lấy thông tin câu hỏi từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT c.NoiDung, c.DapAnA, c.DapAnB, c.DapAnC, c.DapAnD, c.DapAnDung, c.GiaiThich, bh.TenBai 
                            FROM cauhoitracnghiem c 
                            JOIN baihoc bh ON c.MaBaiHoc = bh.MaBaiHoc 
                            WHERE c.MaCauHoi = ?");
    $stmt->execute([$maCauHoi]);
    $cauHoi = $stmt->fetch();
    
    if ($cauHoi) {
        $noiDung = $cauHoi['NoiDung'];
        $dapAnA = $cauHoi['DapAnA'];
        $dapAnB = $cauHoi['DapAnB'];
        $dapAnC = $cauHoi['DapAnC'];
        $dapAnD = $cauHoi['DapAnD'];
        $dapAnDung = $cauHoi['DapAnDung'];
        $giaiThich = $cauHoi['GiaiThich'];
        $tenBai = $cauHoi['TenBai'];
    } else {
        $errorMessage = 'Câu hỏi không tồn tại!';
    }
}

// Xử lý thêm hoặc cập nhật câu hỏi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $noiDung = $_POST['noiDung'];
    $dapAnA = $_POST['dapAnA'];
    $dapAnB = $_POST['dapAnB'];
    $dapAnC = $_POST['dapAnC'];
    $dapAnD = $_POST['dapAnD'];
    $dapAnDung = $_POST['dapAnDung'];
    $giaiThich = $_POST['giaiThich']; // Lấy giá trị giải thích từ POST
    $maBaiHoc = $_POST['maBaiHoc']; // Mã bài học

    if (empty($noiDung) || empty($dapAnA) || empty($dapAnB) || empty($dapAnC) || empty($dapAnD) || empty($dapAnDung) || empty($giaiThich)) {
        $errorMessage = 'Vui lòng điền tất cả các trường.';
    } else {
        if ($maCauHoi) {
            // Cập nhật câu hỏi
            $stmt = $conn->prepare("UPDATE cauhoitracnghiem SET NoiDung = ?, DapAnA = ?, DapAnB = ?, DapAnC = ?, DapAnD = ?, DapAnDung = ?, GiaiThich = ?, MaBaiHoc = ? WHERE MaCauHoi = ?");
            if ($stmt->execute([$noiDung, $dapAnA, $dapAnB, $dapAnC, $dapAnD, $dapAnDung, $giaiThich, $maBaiHoc, $maCauHoi])) {
                $successMessage = 'Cập nhật câu hỏi thành công!';
            } else {
                $errorMessage = 'Có lỗi xảy ra khi cập nhật câu hỏi!';
            }
        } else {
            // Thêm câu hỏi mới
            $stmt = $conn->prepare("INSERT INTO cauhoitracnghiem (NoiDung, DapAnA, DapAnB, DapAnC, DapAnD, DapAnDung, GiaiThich, MaBaiHoc) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$noiDung, $dapAnA, $dapAnB, $dapAnC, $dapAnD, $dapAnDung, $giaiThich, $maBaiHoc])) {
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
    <title>Quản lý câu hỏi trắc nghiệm</title>
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
                            <h5 class="card-header"><?php echo $maCauHoi ? 'Chỉnh sửa câu hỏi' : 'Thêm câu hỏi'; ?></h5>
                            <div class="card-body">
                                <?php if ($errorMessage): ?>
                                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                                <?php endif; ?>
                                <?php if ($successMessage): ?>
                                <div class="alert alert-success"><?php echo $successMessage; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                                            <label class="form-check-label" for="flexSwitchCheckDefault">Nhập từ excel</label>
                                        </div>
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
                                        <label for="dapAnA" class="form-label">Đáp án A</label>
                                        <input type="text" class="form-control" id="dapAnA" name="dapAnA"
                                            value="<?php echo htmlspecialchars($dapAnA); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="dapAnB" class="form-label">Đáp án B</label>
                                        <input type="text" class="form-control" id="dapAnB" name="dapAnB"
                                            value="<?php echo htmlspecialchars($dapAnB); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="dapAnC" class="form-label">Đáp án C</label>
                                        <input type="text" class="form-control" id="dapAnC" name="dapAnC"
                                            value="<?php echo htmlspecialchars($dapAnC); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="dapAnD" class="form-label">Đáp án D</label>
                                        <input type="text" class="form-control" id="dapAnD" name="dapAnD"
                                            value="<?php echo htmlspecialchars($dapAnD); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="dapAnDung" class="form-label">Đáp án đúng</label>
                                        <select class="form-control" id="dapAnDung" name="dapAnDung" required>
                                            <option value="A" <?php if ($dapAnDung === 'A') echo 'selected'; ?>>A</option>
                                            <option value="B" <?php if ($dapAnDung === 'B') echo 'selected'; ?>>B</option>
                                            <option value="C" <?php if ($dapAnDung === 'C') echo 'selected'; ?>>C</option>
                                            <option value="D" <?php if ($dapAnDung === 'D') echo 'selected'; ?>>D</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="giaiThich" class="form-label">Giải Thích</label>
                                        <textarea class="form-control" id="giaiThich" name="giaiThich" rows="4" required><?php echo htmlspecialchars($giaiThich); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><?php echo $maCauHoi ? 'Cập nhật' : 'Thêm mới'; ?></button>
                                    <a href="quiz_manager.php" class="btn btn-secondary">Quay lại</a>
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
