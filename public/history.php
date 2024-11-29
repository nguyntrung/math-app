<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php'; // Kết nối với cơ sở dữ liệu

// Lấy lịch sử kiểm tra của người dùng đã đăng nhập
$stmt = $conn->prepare("
    SELECT lsk.*, ch.TenChuong
    FROM lichsukiemtra lsk
    JOIN chuonghoc ch ON lsk.MaChuong = ch.MaChuong
    WHERE lsk.MaNguoiDung = :maNguoiDung
    ORDER BY lsk.NgayKiemTra DESC
");
$stmt->bindParam(':maNguoiDung', $_SESSION['MaNguoiDung']);
$stmt->execute();
$testHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lịch sử</title>

    <?php include '../includes/styles.php'; ?>
    <style>
        /* Table styles */
        body {
            margin: 0;
            background-color: #f0f0e6;
        }

        .test-history-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
        border: 1px solid #ccc;
        }

        .test-history-table th,
        .test-history-table td {
        padding: 10px 15px;
        text-align: left;
        border-bottom: 1px solid #ccc;
        }

        .test-history-table th {
        background-color: #f2f2f2;
        font-weight: bold;
        }

        .test-history-table tbody tr:nth-child(even) {
        background-color: #f2f2f2;
        }

        .test-history-table .bg-success {
        background-color: #d4edda !important;
        color: #155724;
        }

        .test-history-table .bg-danger {
        background-color: #f8d7da !important;
        color: #721c24;
        }

        /* Layout styles */
        .container {
        max-width: 960px;
        margin: 0 auto;
        padding: 2rem;
        }

        h2 {
        text-align: center;
        margin-bottom: 2rem;
        }
    </style>

</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container pt-5">
    <h2 class="mb-4">Lịch Sử Kiểm Tra</h2>

    <!-- Bảng hiển thị lịch sử kiểm tra -->
    <table class="test-history-table">
        <thead>
            <tr>
                <th class="text-center">Chương</th>
                <th>Ngày Kiểm Tra</th>
                <th>Thời Gian Làm Bài</th>
                <th>Điểm Số</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($testHistory as $test): ?>
            <tr>
                <td><?= htmlspecialchars($test['TenChuong']); ?></td>
                <td><?= date('d/m/Y H:i', strtotime($test['NgayKiemTra'])); ?></td>
                <td><?= floor($test['ThoiGian'] / 60) . ':' . ($test['ThoiGian'] % 60); ?></td>
                <td class="<?= $test['Diem'] >= 10 ? 'bg-success' : 'bg-danger'; ?> text-center">
                    <?= $test['Diem']; ?>/20
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>