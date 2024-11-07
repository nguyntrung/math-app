<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

try {
    // Kiểm tra trạng thái đăng ký thành viên
    $isActiveMember = false;
    $stmt = $conn->prepare("
        SELECT COUNT(*) as active_count 
        FROM dangkythanhvien 
        WHERE MaNguoiDung = :maNguoiDung 
        AND TrangThai = 'DANG_HOAT_DONG' 
        AND NgayKetThuc >= CURRENT_DATE()
    ");
    $stmt->bindParam(':maNguoiDung', $_SESSION['MaNguoiDung']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['active_count'] > 0) {
        $isActiveMember = true;
    }

    // Lấy danh sách chương và bài học, kèm theo ThoiLuongVideo từ BaiHoc
    $stmt = $conn->prepare("
        SELECT ChuongHoc.MaChuong, ChuongHoc.TenChuong, ChuongHoc.MienPhi,
               BaiHoc.MaBaiHoc, BaiHoc.TenBai, BaiHoc.ThoiLuongVideo
        FROM ChuongHoc
        LEFT JOIN BaiHoc ON ChuongHoc.MaChuong = BaiHoc.MaChuong
        ORDER BY ChuongHoc.ThuTu ASC, BaiHoc.ThuTu ASC
    ");
    $stmt->execute();
    $chuongBaiHocList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $chuongData = [];
    $totalLessons = 0; // Tổng số bài học

    foreach ($chuongBaiHocList as $row) {
        $maChuong = $row['MaChuong'];
        $tenChuong = $row['TenChuong'];
        $maBaiHoc = $row['MaBaiHoc'];
        $tenBaiHoc = $row['TenBai'];
        $mienPhi = $row['MienPhi'];
        $thoiLuongVideo = $row['ThoiLuongVideo']; 

        if (!isset($chuongData[$maChuong])) {
            $chuongData[$maChuong] = [
                'tenChuong' => $tenChuong,
                'mienPhi' => $mienPhi,
                'baiHocList' => []
            ];
        }

        if ($maBaiHoc) {
            $chuongData[$maChuong]['baiHocList'][] = [
                'maBaiHoc' => $maBaiHoc,
                'tenBaiHoc' => $tenBaiHoc,
                'thoiLuongVideo' => $thoiLuongVideo 
            ];
            $totalLessons++; // Tăng tổng số bài học
        }
    }

    $completedLessons = 0;

    // Đếm số bài học đã hoàn thành
    foreach ($chuongBaiHocList as $row) {
        $queryProgress = "SELECT ThoiLuongXem FROM tiendohoctap WHERE MaNguoiDung = :maNguoiDung AND MaBaiHoc = :maBaiHoc";
        $stmtProgress = $conn->prepare($queryProgress);
        $stmtProgress->bindParam(':maNguoiDung', $_SESSION['MaNguoiDung']);
        $stmtProgress->bindParam(':maBaiHoc', $row['MaBaiHoc']);
        $stmtProgress->execute();
        
        $progress = $stmtProgress->fetch(PDO::FETCH_ASSOC);

        if ($progress && $progress['ThoiLuongXem'] == $row['ThoiLuongVideo']) {
            $completedLessons++;
        }
    }

    // Tính tiến độ dựa trên số lượng bài học đã hoàn thành
    $progressPercent = ($totalLessons > 0) ? ($completedLessons / $totalLessons) * 100 : 0;

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
    exit();
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kho Báu Kiến Thức</title>

    <?php include '../includes/styles.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <style>
    body {
        background: linear-gradient(135deg, #c2e9fb 0%, #a1c4fd 100%);
    }

    .main-title {
        color: #ff6b6b;
        text-align: center;
        font-size: 2.5rem;
        margin: 2rem 0;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        animation: bounce 2s infinite;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .chapter-card {
        background: white;
        border-radius: 20px;
        border: none;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        overflow: hidden;
        transition: transform 0.3s ease;
        position: relative;
    }

    .chapter-card:hover {
        transform: translateY(-5px);
    }

    .chapter-header {
        background: linear-gradient(45deg, #ff9a9e 0%, #fad0c4 100%);
        color: white;
        padding: 1rem;
        border-radius: 20px 20px 0 0;
        font-size: 1.5rem;
        text-align: center;
    }

    .lesson-item {
        border: none;
        margin: 10px;
        border-radius: 15px;
        background: #f8f9fa;
        transition: all 0.3s ease;
        padding: 12px;
        position: relative;
    }

    .lesson-item:hover {
        background: #fff3f3;
        transform: scale(1.02);
    }

    .lesson-link {
        color: #5b6c8d;
        text-decoration: none;
        display: flex;
        align-items: center;
    }

    .lesson-link:hover {
        color: #ff6b6b;
        text-decoration: none;
    }

    .lesson-icon {
        width: 150px;
        margin-right: 15px;
        border-radius: 10px;
    }

    .premium-badge {
        background: #ffd700;
        color: #000;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.9rem;
        margin-left: 10px;
    }

    .locked-content {
        opacity: 0.7;
        pointer-events: none;
    }

    .upgrade-message {
        position: relative;
        padding: 1rem;
        background: rgba(255, 243, 205, 0.95);
        border-radius: 10px;
        margin: 1rem 0;
        text-align: center;
        z-index: 1;
        width: 100%;
    }

    .upgrade-button {
        display: inline-block;
        background: #ff6b6b;
        color: white !important;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        text-decoration: none;
        margin-top: 0.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
        pointer-events: auto !important;
    }

    .upgrade-button:hover {
        background: #ff8787;
        transform: scale(1.05);
        color: white !important;
        text-decoration: none;
    }

    .locked-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.7);
        z-index: 5;
    }

    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #ff6b6b;
        color: white;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .back-to-top:hover {
        background: #ff8787;
        transform: translateY(-5px);
    }

    .empty-message {
        text-align: center;
        padding: 2rem;
        font-size: 1.2rem;
        color: #666;
        background: white;
        border-radius: 20px;
        margin: 2rem auto;
        max-width: 500px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .progress-circle-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 2rem;
    }

    .progress-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: conic-gradient(#ff6b6b 0%, #e0e0e0 0%);
        /* Mặc định 100% màu xám */
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    .progress-text {
        font-size: 1.5rem;
        color: #333;
        font-weight: bold;
    }

    .progress-circle .progress-text {
        position: absolute;
    }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid pt-4">
        <div class="container pb-5">
            <h1 class="main-title">
                <i class="fas fa-book-reader mr-2"></i>
                Kho Báu Kiến Thức
            </h1>

            <?php if (!empty($chuongData)): ?>
            <?php foreach ($chuongData as $maChuong => $chuong): ?>
            <div class="chapter-card">
                <div class="chapter-header">
                    <i class="fas fa-star mr-2"></i>
                    <?= htmlspecialchars($chuong['tenChuong']); ?>
                    <?php if ($chuong['mienPhi'] == 1): ?>
                    <span class="premium-badge">
                        <i class="fas fa-crown mr-1"></i>Premium
                    </span>
                    <?php endif; ?>
                </div>
                <div class="p-3">
                    <?php if ($chuong['mienPhi'] == 1 && !$isActiveMember): ?>
                    <div class="upgrade-message">
                        <i class="fas fa-lock mr-2"></i>
                        Nội dung này chỉ dành cho thành viên Premium
                        <br>
                        <a href="registermember.php" class="upgrade-button">
                            <i class="fas fa-crown mr-1"></i>
                            Nâng cấp ngay
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($chuong['baiHocList'])): ?>
                    <?php foreach ($chuong['baiHocList'] as $baiHoc): ?>
                    <div class="lesson-item">
                        <?php
                                        $lessonUrl = ($chuong['mienPhi'] == 0 || $isActiveMember) 
                                            ? "video_lessons_detail.php?maBaiHoc=" . htmlspecialchars($baiHoc['maBaiHoc'])
                                            : "registermember.php";
                                        ?>
                        <a href="<?= $lessonUrl ?>" class="lesson-link">
                            <img src="../assets/img/learning.png" alt="Bài học" class="lesson-icon">
                            <span><?= htmlspecialchars($baiHoc['tenBaiHoc']); ?></span>
                        </a>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="empty-message">
                        <i class="fas fa-info-circle mr-2"></i>
                        Chưa có bài học nào trong chương này
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-message">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Hiện tại chưa có bài học nào
            </div>
            <?php endif; ?>

            <!-- Hiển thị vòng tròn tiến độ -->
            <div class="progress-circle-container">
                <div class="progress-circle"
                    style="background: conic-gradient(#ff6b6b <?= $progressPercent; ?>%, #e0e0e0 <?= $progressPercent; ?>% 100%);">
                    <span class="progress-text"><?= round($progressPercent); ?>%</span>
                </div>
            </div>


        </div>
    </div>

    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>

</html>