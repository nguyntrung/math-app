<?php
session_start();

// Kiểm tra nếu chưa đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

try {
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
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Danh sách bài giảng</title>

    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Phần Chính Bắt Đầu -->
    <div class="container-fluid pt-5">
        <div class="container pb-5">
            <h1 class="text-center mb-4">Danh sách bài học</h1>
            
            <?php if (!empty($chuongData)): ?>
                <?php foreach ($chuongData as $maChuong => $chuong): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><?= htmlspecialchars($chuong['tenChuong']); ?></h5>
                        </div>
                        <ul class="list-group">
                            <?php if (!empty($chuong['baiHocList'])): ?>
                                <?php foreach ($chuong['baiHocList'] as $baiHoc): ?>
                                    <li class="list-group-item">
                                        <a href="video_lessons_detail.php?maBaiHoc=<?= htmlspecialchars($baiHoc['maBaiHoc']); ?>" class="text-primary d-flex align-items-center">
                                            <img src="../assets/img/video.png" alt="<?= htmlspecialchars($baiHoc['tenBaiHoc']); ?>" class="img-fluid mr-2" style="width: 20px;">
                                            <strong><?= htmlspecialchars($baiHoc['tenBaiHoc']); ?></strong>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item">Không có bài học trong chương này.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Hiện tại chưa có chương và bài học nào.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Phần Chính Kết Thúc -->

    <?php include '../includes/footer.php'; ?>

    <!-- Quay Lên Trên -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
</body>
</html>
