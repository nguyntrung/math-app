<?php
session_start();

// Kiểm tra nếu chưa đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Lấy mã bài học từ URL
$maBaiHoc = $_GET['maBaiHoc'] ?? null;

if ($maBaiHoc) {
    // Lấy thông tin bài học
    $stmt = $conn->prepare("SELECT * FROM BaiHoc WHERE MaBaiHoc = :maBaiHoc");
    $stmt->bindParam(':maBaiHoc', $maBaiHoc);
    $stmt->execute();
    $baiHoc = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy danh sách bài giảng theo bài học
    $stmtGiang = $conn->prepare("SELECT * FROM BaiGiai WHERE MaBaiHoc = :maBaiHoc ORDER BY MaBaiGiai ASC");
    $stmtGiang->bindParam(':maBaiHoc', $maBaiHoc);
    $stmtGiang->execute();
    $baiGiaiList = $stmtGiang->fetchAll(PDO::FETCH_ASSOC);
} else {
    header('Location: index.php'); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cùng Nhau Học Bài</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../includes/styles.php'; ?>
    <style>
        body {
            background-color: #f0f4f8;
        }
        .lesson-container {
            max-width: 800px;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .lesson-title {
            color: #3498db;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .lesson-item {
            background-color: #e6f2ff;
            border: 1px solid #3498db;
            border-radius: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .lesson-item:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .lesson-link {
            color: #2980b9;
            font-weight: bold;
            padding: 10px;
            display: block;
        }
        .solution-content {
            background-color: #f9f3e0;
            border-radius: 10px;
            padding: 15px;
            margin-top: 10px;
            border: 1px solid #f1c40f;
        }
        .btn-back-to-top {
            background-color: #2ecc71;
            color: white;
        }
        .emoji-title::after {
            content: ' 📚';
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container lesson-container mb-3">
        <h3 class="text-center lesson-title emoji-title">
            Bài Giải Sách Giáo Khoa
        </h3>
        <h4 class="text-center mb-4 text-primary">
            <?= htmlspecialchars($baiHoc['TenBai']); ?>
        </h4>

        <?php if (!empty($baiGiaiList)): ?>
            <div class="row">
                <?php foreach ($baiGiaiList as $baiGiai): ?>
                    <div class="col-12 mb-3">
                        <div class="lesson-item">
                            <a href="#" 
                               class="lesson-link ten-bai-giai text-decoration-none" 
                               data-ma-bai-giai="<?= $baiGiai['MaBaiGiai']; ?>">
                                🖊️ <?= htmlspecialchars($baiGiai['Bai']); ?>
                            </a>
                            <div id="noidung-<?= $baiGiai['MaBaiGiai']; ?>" 
                                 class="noidung solution-content" 
                                 style="display: none;">
                                <p class="text">
                                    📝 Lời giải: 
                                    <?php echo nl2br($baiGiai['LoiGiai']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center alert alert-info">
                🤔 Chưa có bài giảng nào cho bài học này. Hãy quay lại sau nhé!
            </p>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-success p-3 back-to-top position-fixed bottom-0 end-0 m-3">
        🚀 Lên Đầu Trang
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.ten-bai-giai').forEach(function(element) {
            element.addEventListener('click', function(event) {
                event.preventDefault();
                const maBaiGiai = this.getAttribute('data-ma-bai-giai');
                const noidung = document.getElementById('noidung-' + maBaiGiai);
                
                if (noidung.style.display === 'none') {
                    noidung.style.display = 'block';
                    this.innerHTML = '📖 ' + this.textContent.replace('🖊️ ', '');
                } else {
                    noidung.style.display = 'none';
                    this.innerHTML = '🖊️ ' + this.textContent.replace('📖 ', '');
                }
            });
        });
    </script>
</body>
</html>