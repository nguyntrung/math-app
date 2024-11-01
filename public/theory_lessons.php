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
            'NoiDungLyThuyet' => $bai['NoiDungLyThuyet'], // Nội dung lý thuyết từ bảng BaiHoc
            'MaBaiHoc' => $bai['MaBaiHoc']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lý thuyết</title>

    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Main Start -->
    <div class="container-fluid pt-5">
        <div class="container pb-5">
            <h1 class="text-center mb-4">Bài Học Lý Thuyết</h1>
            <?php foreach ($chuongBaiHoc as $tenChuong => $baiHocList): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h5><?php echo htmlspecialchars($tenChuong); ?></h5>
                    </div>
                    <ul class="list-group">
                        <?php foreach ($baiHocList as $baiHoc): ?>
                            <li class="list-group-item">
                                <strong class="ten-bai text-primary" style="cursor: pointer;">
                                    <?php echo htmlspecialchars($baiHoc['TenBai']); ?>
                                </strong>
                                <div id="lyThuyet" style="display: none;">
                                    <p class="mt-4"><?php echo nl2br($baiHoc['NoiDungLyThuyet']); ?></p>
                                    <a href="quiz_detail.php?maBaiHoc=<?= htmlspecialchars($baiHoc['MaBaiHoc']); ?>">- Làm bài tập trắc nghiệm</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Main End -->

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>

    <script>
        document.querySelectorAll('.ten-bai').forEach(function(element) {
            element.addEventListener('click', function() {
                const lyThuyet = this.nextElementSibling;
                if (lyThuyet.style.display === 'none') {
                    lyThuyet.style.display = 'block';
                } else {
                    lyThuyet.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
