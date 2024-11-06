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
    <title>Đăng ký thành viên</title>
    <?php include '../includes/styles.php'; ?>

    <style>
        /* Main container styles */
        .container-fluid.pt-5 {
            background-color: #f0f9ff;
            min-height: 100vh;
            padding: 2rem;
        }

        /* Main content container */
        .container.pb-5 {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        /* Main heading */
        h2.text-center {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        /* Card styles */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            color: #2c3e50;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        /* Table styles */
        .table {
            border-radius: 10px;
            overflow: hidden;
            font-size: 1.1rem;
        }

        .table th {
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            border: 1px solid #e1e8ed;
            background-color: #ffffff;
        }

        /* Badge styles */
        .badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        .bg-success {
            background-color: #2ecc71 !important;
        }

        .bg-danger {
            background-color: #e74c3c !important;
        }

        /* Alert styles */
        .alert-warning {
            background-color: #fff3cd;
            border: 2px dashed #ffc107;
            border-radius: 15px;
            font-size: 1.1rem;
            padding: 1rem;
        }

        /* Package cards */
        .col-md-6 .card {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border: 3px solid #e1e8ed;
        }

        .card-text strong {
            color: #2ecc71;
            font-size: 1.4rem;
        }

        /* Button styles */
        .btn-primary {
            border: none;
            border-radius: 25px;
            font-size: 1.1rem;
            padding: 0.8rem 2rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(74,144,226,0.3);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container.pb-5 {
                padding: 1rem;
            }
            
            h2.text-center {
                font-size: 2rem;
            }
            
            .table {
                font-size: 1rem;
            }
            
            .card-text strong {
                font-size: 1.2rem;
            }
        }
    </style>

</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!--Main-->
    <div class="container-fluid pt-5">
        <div class="container pb-5 col-12 col-md-6 mb-1">
            <h2 class="text-center mb-4">Thông tin thành viên</h2>
            
            <?php if ($userInfo['TrangThai'] == 'DANG_HOAT_DONG'): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin gói thành viên hiện tại</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Họ tên:</th>
                                    <td><?php echo htmlspecialchars($userInfo['HoTen']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlspecialchars($userInfo['Email']); ?></td>
                                </tr>
                                <tr>
                                    <th>Loại gói:</th>
                                    <td><?php echo $userInfo['LoaiDangKy'] == 'NAM' ? 'Gói 1 năm' : 'Gói theo tháng'; ?></td>
                                </tr>
                                <tr>
                                    <th>Ngày đăng ký:</th>
                                    <td><?php echo date('d/m/Y', strtotime($userInfo['NgayDangKy'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Ngày hết hạn:</th>
                                    <td><?php echo date('d/m/Y', strtotime($userInfo['NgayKetThuc'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Thời gian còn lại:</th>
                                    <td>
                                        <?php 
                                        if ($userInfo['SoNgayConLai'] > 0) {
                                            echo $userInfo['SoNgayConLai'] . ' ngày';
                                        } else {
                                            echo '<span class="text-danger">Đã hết hạn</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Trạng thái:</th>
                                    <td>
                                        <?php
                                        if ($userInfo['SoNgayConLai'] > 0) {
                                            echo '<span class="badge bg-success">Đang hoạt động</span>';
                                        } else {
                                            echo '<span class="badge bg-danger">Hết hạn</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <?php if ($userInfo['SoNgayConLai'] <= 7 && $userInfo['SoNgayConLai'] > 0): ?>
                            <div class="alert alert-warning mt-3">
                                Gói thành viên của bạn sắp hết hạn. Vui lòng gia hạn để tiếp tục sử dụng dịch vụ.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($userInfo['SoNgayConLai'] <= 7): ?>
                    <h5 class="text-center mb-3">Gia hạn gói thành viên</h5>
                <?php endif; ?>
            <?php else: ?>
                <h5 class="text-center mb-3">Đăng ký gói thành viên</h5>
            <?php endif; ?>

            <?php if (!$userInfo['TrangThai'] || $userInfo['SoNgayConLai'] <= 7): ?>
                <div class="row">
                    <?php foreach ($packages as $package): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo $package['name']; ?></h5>
                                <p class="card-text">
                                    <strong><?php echo number_format($package['price'], 0, ',', '.'); ?> VNĐ</strong>
                                </p>
                                <form action="payment.php" method="POST">
                                    <input type="hidden" name="months" value="<?php echo $package['months']; ?>">
                                    <input type="hidden" name="price" value="<?php echo $package['price']; ?>">
                                    <button type="submit" class="btn btn-primary">
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
    <!--End Main-->

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>