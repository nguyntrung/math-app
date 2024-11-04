<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

try {
    $stmt = $conn->prepare("
        SELECT ChuongHoc.MaChuong, ChuongHoc.TenChuong, BaiHoc.MaBaiHoc, BaiHoc.TenBai 
        FROM ChuongHoc
        LEFT JOIN BaiHoc ON ChuongHoc.MaChuong = BaiHoc.MaChuong
        ORDER BY ChuongHoc.ThuTu ASC, BaiHoc.ThuTu ASC
    ");
    $stmt->execute();
    $chuongBaiHocList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $chuongData = [];
    foreach ($chuongBaiHocList as $row) {
        $maChuong = $row['MaChuong'];
        $tenChuong = $row['TenChuong'];
        $maBaiHoc = $row['MaBaiHoc'];
        $tenBaiHoc = $row['TenBai'];

        if (!isset($chuongData[$maChuong])) {
            $chuongData[$maChuong] = [
                'tenChuong' => $tenChuong,
                'baiHocList' => []
            ];
        }

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
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .chapter-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease;
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
            border-radius: 10px
            /* animation: wiggle 2s infinite; */
        }

        @keyframes wiggle {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
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
                        </div>
                        <div class="p-3">
                            <?php if (!empty($chuong['baiHocList'])): ?>
                                <?php foreach ($chuong['baiHocList'] as $baiHoc): ?>
                                    <div class="lesson-item">
                                        <a href="video_lessons_detail.php?maBaiHoc=<?= htmlspecialchars($baiHoc['maBaiHoc']); ?>" 
                                           class="lesson-link">
                                            <img src="../assets/img/learning.png" 
                                                 alt="Bài học" 
                                                 class="lesson-icon">
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
        </div>
    </div>

    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>