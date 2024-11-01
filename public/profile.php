<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Lấy thông tin người dùng từ cơ sở dữ liệu
$maNguoiDung = $_SESSION['MaNguoiDung'];
$stmt = $conn->prepare("SELECT * FROM NguoiDung WHERE MaNguoiDung = :maNguoiDung");
$stmt->bindParam(':maNguoiDung', $maNguoiDung, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra xem người dùng có tồn tại không
if (!$user) {
    echo "Người dùng không tồn tại!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hồ sơ</title>
    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid pt-5">
        <div class="container pb-5 col-12 col-md-6 mb-1">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center">Thông tin của bạn</h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Thông Tin Người Dùng</h5>
                    <p><strong>Tên: </strong><?php echo htmlspecialchars($user['HoTen']); ?></p>
                    <p><strong>Email: </strong><?php echo htmlspecialchars($user['Email']); ?></p>
                    <p><strong>Ngày Đăng Ký: </strong><?php echo htmlspecialchars($user['NgayTao']); ?></p>
                    <a href="edit_profile.php" class="btn btn-primary">Chỉnh Sửa Thông Tin</a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
