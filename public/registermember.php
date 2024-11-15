<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

$packages = [
    ['months' => 1, 'price' => 100000, 'name' => '1 tháng'],
    ['months' => 3, 'price' => 300000, 'name' => '3 tháng'],
    ['months' => 6, 'price' => 600000, 'name' => '6 tháng'],
    ['months' => 12, 'price' => 1200000, 'name' => '1 năm']
];

// Lấy thông tin người dùng và gói đăng ký
$stmt = $conn->prepare("
    SELECT n.*, d.*, 
           d.NgayBatDau as NgayDangKy, 
           DATEDIFF(d.NgayKetThuc, CURRENT_DATE()) as SoNgayConLai,
           t.NgayThanhToan,
           CASE 
               WHEN d.TrangThai = 'DANG_HOAT_DONG' AND d.NgayKetThuc >= CURRENT_DATE() 
               THEN 'DANG_HOAT_DONG'
               ELSE 'HET_HAN'
           END as TrangThaiThuc
    FROM nguoidung n
    LEFT JOIN (
        SELECT * FROM dangkythanhvien 
        WHERE TrangThai = 'DANG_HOAT_DONG'
        AND MaNguoiDung = :MaNguoiDung
        ORDER BY NgayKetThuc DESC 
        LIMIT 1
    ) d ON n.MaNguoiDung = d.MaNguoiDung
    LEFT JOIN thanhtoan t ON d.MaDangKy = t.MaDangKy
    WHERE n.MaNguoiDung = :MaNguoiDung
");
$stmt->bindParam(':MaNguoiDung', $_SESSION['MaNguoiDung'], PDO::PARAM_INT);
$stmt->execute();
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Đăng ký khóa học</title>
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
        color: #000000;
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

    .package-card {
        transition: transform 0.2s;
    }

    .package-card:hover {
        transform: translateY(-5px);
    }

    .price-tag {
        font-size: 1.5rem;
        color: #65B446;
        font-weight: bold;
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: normal;
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
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($userInfo['HoTen']); ?></h5>
                        <p class="text-muted small mb-3">Khối 5</p>
                        
                        <!-- Navigation Menu -->
                        <div class="list-group text-start">
                            <a href="profile.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-user me-2" style="color: #65B446"></i> Thông tin cá nhân
                            </a>
                            <a href="edit_profile.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-key me-2" style="color: #6587ff"></i> Đổi mật khẩu
                            </a>
                            <a href="#" class="list-group-item list-group-item-action active">
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
                            <h5 class="card-title mb-0" style="color: #65B446">Thông tin đăng ký khóa học</h5>
                        </div>

                        <?php if ($userInfo['TrangThai'] == 'DANG_HOAT_DONG'): ?>
                        <!-- Current Subscription Info -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Loại gói:</label>
                                <div class="fw-bold">
                                    <?php echo $userInfo['LoaiDangKy'] == 'NAM' ? 'Gói 1 năm' : 'Gói theo tháng'; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Ngày đăng ký:</label>
                                <div class="fw-bold">
                                    <?php echo date('d/m/Y', strtotime($userInfo['NgayDangKy'])); ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Ngày hết hạn:</label>
                                <div class="fw-bold">
                                    <?php echo date('d/m/Y', strtotime($userInfo['NgayKetThuc'])); ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Trạng thái:</label>
                                <div>
                                    <?php if ($userInfo['SoNgayConLai'] > 0): ?>
                                        <span class="badge bg-success">Đang hoạt động</span>
                                        <div class="small text-muted mt-1">
                                            Còn <?php echo $userInfo['SoNgayConLai']; ?> ngày
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Hết hạn</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($userInfo['SoNgayConLai'] <= 7): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Gói thành viên của bạn sắp hết hạn. Vui lòng gia hạn để tiếp tục sử dụng dịch vụ.
                            </div>
                        <?php endif; ?>
                        <?php endif; ?>

                        <!-- Package Options -->
                        <?php if (!$userInfo['TrangThai'] || $userInfo['SoNgayConLai'] <= 7): ?>
                            <h6 class="mb-4" style="color: #65B446">
                                <?php echo $userInfo['TrangThai'] == 'DANG_HOAT_DONG' ? 'Gia hạn gói thành viên' : 'Đăng ký gói thành viên'; ?>
                            </h6>
                            <div class="row">
                                <?php foreach ($packages as $package): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card package-card">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Gói <?php echo $package['name']; ?></h6>
                                            <p class="price-tag mb-3">
                                                <?php echo number_format($package['price'], 0, ',', '.'); ?> VNĐ
                                            </p>
                                            <form action="payment.php" method="POST">
                                                <input type="hidden" name="months" value="<?php echo $package['months']; ?>">
                                                <input type="hidden" name="price" value="<?php echo $package['price']; ?>">
                                                <button type="submit" class="btn btn-outline-primary">
                                                    <?php echo $userInfo['TrangThai'] == 'DANG_HOAT_DONG' ? 'Gia hạn ngay' : 'Đăng ký ngay'; ?>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>