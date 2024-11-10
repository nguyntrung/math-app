<?php
    session_start();

    if (!isset($_SESSION['MaNguoiDung'])) {
        header('Location: login.php');
        exit();
    }
    
    include '../database/db.php';
    
    if (!isset($_SESSION['startTime'])) {
        $_SESSION['startTime'] = time();
    }
       
    // Truy vấn bảng xếp hạng (dựa trên điểm số và thời gian thi)
    $stmtRanking = $conn->prepare("SELECT nguoidung.TenDangNhap, ketquakiemtra.Diem, ketquakiemtra.NgayThi 
                                            FROM ketquakiemtra 
                                            JOIN nguoidung ON ketquakiemtra.MaNguoiDung = nguoidung.MaNguoiDung
                                            WHERE ketquakiemtra.LoaiKiemTra = '15p'
                                            ORDER BY ketquakiemtra.Diem DESC, ketquakiemtra.NgayThi ASC");
    $stmtRanking->execute();
    $rankingList = $stmtRanking->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kiểm tra 15 phút</title>

    <?php include '../includes/styles.php'; ?>

</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container pt-5">
        <div class="pb-5">
            <h4 class="text-center mb-4" style="color: #ff6347;">Bảng Xếp Hạng</h4> 
        </div>
        <!-- Hiển thị bảng xếp hạng -->

        <?php if (!empty($rankingList)): ?>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Thứ tự</th>
                    <th>Tên người dùng</th>
                    <th>Điểm</th>
                    <th>Ngày thi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rankingList as $index => $rank): ?>
                <tr>
                    <td><?= ($index + 1); ?></td>
                    <td><?= htmlspecialchars($rank['TenDangNhap']); ?></td>
                    <td><?= $rank['Diem']; ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($rank['NgayThi'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color: #ff6347;">Bảng xếp hạng trống.</p>
        <?php endif; ?>


    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>

</html>