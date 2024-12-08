<?php
session_start();

// Kiểm tra nếu chưa đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Lấy danh sách các chương và bài học từ cơ sở dữ liệu
$stmt = $conn->prepare("
    SELECT ChuongHoc.MaChuong, ChuongHoc.TenChuong, BaiHoc.MaBaiHoc, BaiHoc.TenBai 
    FROM ChuongHoc
    LEFT JOIN BaiHoc ON ChuongHoc.MaChuong = BaiHoc.MaChuong
    ORDER BY ChuongHoc.ThuTu ASC, BaiHoc.ThuTu ASC
");
$stmt->execute();
$chuongBaiHocList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tạo mảng để nhóm các bài học theo chương
$chuongData = [];
foreach ($chuongBaiHocList as $row) {
    $maChuong = $row['MaChuong'];
    $tenChuong = $row['TenChuong'];
    $maBaiHoc = $row['MaBaiHoc'];
    $tenBaiHoc = $row['TenBai'];

    // Kiểm tra xem chương đã tồn tại chưa
    if (!isset($chuongData[$maChuong])) {
        $chuongData[$maChuong] = [
            'tenChuong' => $tenChuong,
            'baiHocList' => []
        ];
    }

    // Thêm bài học vào chương
    if ($maBaiHoc) {
        $chuongData[$maChuong]['baiHocList'][] = [
            'maBaiHoc' => $maBaiHoc,
            'tenBaiHoc' => $tenBaiHoc
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kho Bài Giải Vui Nhộn</title>
    
    <?php include '../includes/styles.php'; ?>
    
    <style>
        body {
            background-color: #f0f4f8;
        }
        .lesson-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease;
            height: 250px; 
        }

        .lesson-body {
            padding: 15px;
            height: calc(250px - 80px);
            overflow-y: auto;
        }
        .lesson-header {
            background-color: #66BB6A;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
        }
        .lesson-header img {
            width: 50px;
            height: 50px;
            margin-right: 15px;
        }
        .lesson-link {
            color: #2196F3;
            text-decoration: none;
            font-weight: bold;
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }
        .lesson-link:hover {
            color: #1976D2;
        }
        .lesson-link img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }
        .empty-state {
            text-align: center;
            padding: 50px;
            background-color: #E3F2FD;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container-fluid pt-5">
        <div class="container pb-5">
            <h1 class="text-center mb-5" style="color: #FF5722; font-size: 2.5em;">Bài giải sách giáo khoa</h1>
            
            <?php if (!empty($chuongData)): ?>
                <div class="row">
                    <?php foreach ($chuongData as $maChuong => $chuong): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="lesson-card">
                                <div class="lesson-header">
                                    <img src="../assets/img/robotlearn2.png" alt="Chương học">
                                    <h5 class="m-0 text-white"><?= htmlspecialchars(mb_strlen($chuong['tenChuong'], 'UTF-8') > 45 ? mb_substr($chuong['tenChuong'], 0, 45, 'UTF-8') . '...' : $chuong['tenChuong']); ?></h5>
                                </div>
                                <div class="lesson-body">
                                    <?php if (!empty($chuong['baiHocList'])): ?>
                                        <?php foreach ($chuong['baiHocList'] as $baiHoc): ?>
                                            <a href="solutions_detail.php?maBaiHoc=<?= $baiHoc['maBaiHoc']; ?>" class="lesson-link mb-2 d-flex align-items-center">
                                                <img src="../assets/img/robotlearn.png" alt="Bài học">
                                                <?= htmlspecialchars($baiHoc['tenBaiHoc']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">Chưa có bài học trong chương này 😢</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Chưa có chương và bài học 🤔</h3>
                    <p>Các bài học sẽ sớm được cập nhật. Hãy quay lại sau nhé!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>