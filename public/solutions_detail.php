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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Giải bài tập SGK</title>

    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Main Start -->
    <div class="container-fluid pt-5">
        <div class="container pb-5">
            <h4 class="text-center">BÀI GIẢI SÁCH GIÁO KHOA</h4>
            <h4 class="text-center mb-4"><?= htmlspecialchars($baiHoc['TenBai']); ?></h4>
            <?php if (!empty($baiGiaiList)): ?>
                <ul class="list-group">
                    <?php foreach ($baiGiaiList as $baiGiai): ?>
                        <li class="list-group-item">
                            <a href="#" class="text-primary ten-bai-giai text-decoration-none" data-ma-bai-giai="<?= $baiGiai['MaBaiGiai']; ?>">
                                <?= htmlspecialchars($baiGiai['Bai']); ?>
                            </a>
                            <div id="noidung-<?= $baiGiai['MaBaiGiai']; ?>" class="noidung" style="display: none;">
                                <p><?php echo nl2br($baiGiai['LoiGiai']); ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-center">Chưa có bài giảng nào cho bài học này.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Main End -->

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    
    <script>
        document.querySelectorAll('.ten-bai-giai').forEach(function(element) {
            element.addEventListener('click', function(event) {
                event.preventDefault();
                const maBaiGiai = this.getAttribute('data-ma-bai-giai');
                const noidung = document.getElementById('noidung-' + maBaiGiai);
                
                if (noidung.style.display === 'none') {
                    noidung.style.display = 'block';
                } else {
                    noidung.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
