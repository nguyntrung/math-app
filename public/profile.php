<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Lấy thông tin người dùng từ cơ sở dữ liệu
$maNguoiDung = $_SESSION['MaNguoiDung'];
$stmt = $conn->prepare("SELECT * FROM NguoiDung WHERE MaNguoiDung = :maNguoiDung");
$stmt->bindParam(':maNguoiDung', $maNguoiDung, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra xem người dùng có tồn tại không
if (!$user) {
    echo "Người dùng không tồn tại!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hồ sơ</title>
    <?php include '../includes/styles.php'; ?>

    <style>
    .card {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border: none;
    }

    .list-group-item {
        border: none;
        padding: 0.75rem 1rem;
        color: #666;
    }

    .list-group-item i {
        width: 20px;
    }

    .list-group-item.active {
        background-color: #f8f9fa;
        color: #000;
        font-weight: bold;
        border-color: transparent;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .text-muted {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .btn-outline-primary {
        border-color: #198754;
        color: #198754;
    }

    .btn-outline-primary:hover {
        background-color: #198754;
        border-color: #198754;
    }
    </style>

</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid pt-5">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <!-- Profile Picture -->
                        <div class="mb-3">
                            <img src="../assets/img/2.png" 
                                class="rounded-circle" 
                                alt="Avatar" 
                                style="width: 100px; height: 100px;">
                        </div>
                        
                        <!-- Name and Class -->
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($user['HoTen']); ?></h5>
                        <p class="text-muted small mb-3">Khối 5</p>
                        
                        <!-- Navigation Menu -->
                        <div class="list-group text-start">
                            <a href="#" class="list-group-item list-group-item-action active">
                                <i class="fas fa-user me-2" style="color: #65B446"></i> Thông tin cá nhân
                            </a>
                            <a href="edit_profile.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-key me-2" style="color: #6587ff"></i> Đổi mật khẩu
                            </a>
                            <a href="registermember.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-graduation-cap me-2" style="color: #e46356"></i> Khóa học của bạn
                            </a>
                            <a href="logout.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-sign-out-alt me-2" style="color: #687187"></i> Đăng xuất
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0" style="color: #65B446">Thông tin cá nhân</h5>
                            <a href="edit_profile.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i> Cập nhật
                            </a>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Họ tên:</label>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['HoTen']); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Ngày sinh:</label>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['NgaySinh'] ?? '-'); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Tên đăng nhập:</label>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['TenDangNhap']); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Điện Thoại:</label>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['DienThoai'] ?? '0943103101'); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Loại tài khoản:</label>
                                <div class="fw-bold">Học sinh</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Email:</label>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['Email'] ?? 'Chưa cập nhật'); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Lớp:</label>
                                <div class="fw-bold">5</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
