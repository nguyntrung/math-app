<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

$stmt = $conn->prepare("SELECT * FROM ChuongHoc ORDER BY ThuTu ASC");
$stmt->execute();
$chuongHoc = $stmt->fetchAll();

$chuongBaiHoc = [];
foreach ($chuongHoc as $chuong) {
    $stmt = $conn->prepare("SELECT * FROM BaiHoc WHERE MaChuong = :maChuong ORDER BY ThuTu ASC");
    $stmt->bindParam(':maChuong', $chuong['MaChuong']);
    $stmt->execute();
    $baiHoc = $stmt->fetchAll();

    foreach ($baiHoc as $bai) {
        $chuongBaiHoc[$chuong['TenChuong']][] = [
            'TenBai' => $bai['TenBai'],
            'NoiDungLyThuyet' => $bai['NoiDungLyThuyet'],
            'MaBaiHoc' => $bai['MaBaiHoc']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khám Phá Kiến Thức</title>
    <?php include '../includes/styles.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            /* background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%); */
            background-color: #f0f0e6;
            color: #333;
        }

        .main-title {
            color: #2c6d84;
            text-align: center;
            font-size: 2.5rem;
            margin: 2rem 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .chapter-card {
            background: #ffffff;
            border-radius: 20px;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }

        .chapter-card:hover {
            transform: translateY(-5px);
        }

        .chapter-header {
            background: #e6d5b8; /* Softer, warmer tone to match background */
            color: #444;
            font-weight: bold;
            padding: 1.2rem;
            border-radius: 20px 20px 0 0;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chapter-header i {
            font-size: 1.6rem;
        }

        .lesson-item {
            border: none;
            background: #f5f5f0; /* Very light, slightly warm neutral */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin: 15px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .lesson-item:hover {
            background: #f0f4f4;
            transform: scale(1.01);
        }

        .lesson-title {
            color: #2c6d84;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .lesson-title:hover {
            color: #1e4d5f;
        }

        .lesson-title i {
            font-size: 1.4rem;
            transition: transform 0.3s ease;
        }

        .theory-content {
            display: none;
            margin-top: 15px;
            padding: 20px;
            border-radius: 15px;
            background: white;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.05);
            font-size: 1.1rem;
            line-height: 1.6;
            color: #37474f;
        }

        .quiz-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #3e7a6c; /* Muted green with earthy undertones */
    color: white;

            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .quiz-link:hover {
            background: #2c5c51;
            transform: scale(1.05);
            color: white;
            text-decoration: none;
        }

        .quiz-link i {
            margin-right: 8px;
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #2c6d84;
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            border: none;
        }

        .back-to-top:hover {
            background: #1e4d5f;
            transform: translateY(-5px);
            color: white;
        }

        /* Animation for showing content */
        .theory-content.show {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .rotate-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid pt-4">
        <div class="container pb-5">
            <h1 class="main-title">
                <i class="fas fa-book-open mr-2"></i>
                Khám Phá Kiến Thức
            </h1>

            <?php foreach ($chuongBaiHoc as $tenChuong => $baiHocList): ?>
                <div class="chapter-card">
                    <div class="chapter-header">
                        <i class="fas fa-graduation-cap"></i>
                        <?php echo htmlspecialchars($tenChuong); ?>
                    </div>
                    <div class="p-3">
                        <?php foreach ($baiHocList as $baiHoc): ?>
                            <div class="lesson-item">
                                <div class="lesson-title">
                                    <i class="fas fa-lightbulb"></i>
                                    <?php echo htmlspecialchars($baiHoc['TenBai']); ?>
                                </div>
                                <div class="theory-content">
                                    <?php echo nl2br($baiHoc['NoiDungLyThuyet']); ?>
                                    <div class="text-center">
                                        <a href="quiz_detail.php?maBaiHoc=<?= htmlspecialchars($baiHoc['MaBaiHoc']); ?>" 
                                           class="quiz-link">
                                            <i class="fas fa-pencil-alt"></i>
                                            Làm Bài Tập
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>

    <script>
        document.querySelectorAll('.lesson-title').forEach(function(element) {
            element.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('i');
                
                // Toggle content visibility with animation
                if (content.classList.contains('show')) {
                    content.classList.remove('show');
                    icon.classList.remove('rotate-icon');
                } else {
                    content.classList.add('show');
                    icon.classList.add('rotate-icon');
                }
            });
        });
    </script>
</body>
</html>