<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../../../database/db.php';

// Lấy mã người dùng từ session
$maNguoiDung = $_SESSION['MaNguoiDung'];

// Truy vấn thông tin người dùng từ cơ sở dữ liệu
$stmt = $conn->prepare("SELECT * FROM nguoidung WHERE MaNguoiDung = :maNguoiDung");
$stmt->bindParam(':maNguoiDung', $maNguoiDung, PDO::PARAM_INT); // Gán giá trị cho tham số
$stmt->execute();
$admin = $stmt->fetch();

// Nếu không tìm thấy người dùng, bạn có thể xử lý tùy ý ở đây (ví dụ: hiển thị lỗi)
if (!$admin) {
    echo "Không tìm thấy thông tin người dùng.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en"class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
data-assets-path="../assets/" data-template="vertical-menu-template-free" data-style="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>My Profile</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"rel="stylesheet" />
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
                <div class="card">
                    <div class="card-header">
                        <div class="profile-container">
                        <h2>My Profile</h2>
                    </div>
                        <div class="card-body">
                            <?php if ($admin): ?>
                            <div class="profile-info">
                                <p><strong>Tên Đăng Nhập:</strong> <?php echo htmlspecialchars($admin['TenDangNhap']); ?></p>
                                <p><strong>Họ Và Tên:</strong> <?php echo htmlspecialchars($admin['HoTen']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['Email']); ?></p>
                                <p><strong>Vai trò:</strong> <?php echo htmlspecialchars($admin['VaiTro']); ?></p>
                            </div>
                            <?php else: ?>
                             <p>Không tìm thấy thông tin người dùng.</p>
                            <?php endif; ?>
                        </div>
                        </div>
                </div>
                <?php include 'footer.php'; ?>
            </div>
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        <?php include 'other.php'; ?>
    </div>
</body>
</html>

<?php
$conn = null; // Đảm bảo đóng kết nối
?>