<?php
session_start();
include '../database/db.php';

$error = '';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $tenDangNhap = trim($_POST['tenDangNhap']);
    $matKhau = trim($_POST['matKhau']);

    if (!empty($tenDangNhap) && !empty($matKhau)) {
        $stmt = $conn->prepare("SELECT * FROM NguoiDung WHERE TenDangNhap = :tenDangNhap");
        $stmt->bindParam(':tenDangNhap', $tenDangNhap);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($matKhau, $user['MatKhau'])) {
            $_SESSION['MaNguoiDung'] = $user['MaNguoiDung'];
            $_SESSION['HoTen'] = $user['HoTen'];
            $_SESSION['VaiTro'] = $user['VaiTro'];

            if ($user['VaiTro'] === 'QuanLy') {
                header('Location: ../public/admin/pages/index.php');
            } else {
                header('Location: ../public/index.php');
            }
            exit();
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin đăng nhập.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Đăng nhập</title>

    <?php include '../includes/styles.php'; ?>
    
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Main Start -->
    <div class="container-fluid p-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 border rounded p-4">
                <h3 class="text-center">Đăng Nhập</h3>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="tenDangNhap">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="tenDangNhap" name="tenDangNhap" required>
                    </div>
                    <div class="form-group">
                        <label for="matKhau">Mật khẩu</label>
                        <input type="password" class="form-control" id="matKhau" name="matKhau" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary">Đăng Nhập</button>
                </form>
                <p class="mt-3">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
            </div>
        </div>
    </div>
    <!-- Main End -->

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
