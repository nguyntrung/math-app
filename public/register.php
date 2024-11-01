<?php
session_start();
include '../database/db.php';

$error = '';

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $tenDangNhap = trim($_POST['tenDangNhap']);
    $email = trim($_POST['email']);
    $hoTen = trim($_POST['hoTen']);
    $matKhau = $_POST['matKhau'];
    $confirmPassword = $_POST['confirmPassword'];

    if (!empty($tenDangNhap) && !empty($email) && !empty($hoTen) && !empty($matKhau) && !empty($confirmPassword)) {
        if ($matKhau === $confirmPassword) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM NguoiDung WHERE TenDangNhap = :tenDangNhap OR Email = :email");
            $stmt->bindParam(':tenDangNhap', $tenDangNhap);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $existingUser = $stmt->fetchColumn();

            if ($existingUser > 0) {
                $error = 'Tên đăng nhập hoặc email đã tồn tại.';
            } else {
                $hashedPassword = password_hash($matKhau, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO NguoiDung (TenDangNhap, MatKhau, Email, HoTen) VALUES (:tenDangNhap, :matKhau, :email, :hoTen)");
                $stmt->bindParam(':tenDangNhap', $tenDangNhap);
                $stmt->bindParam(':matKhau', $hashedPassword);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':hoTen', $hoTen);

                if ($stmt->execute()) {
                    header('Location: login.php');
                    exit();
                } else {
                    $error = 'Có lỗi xảy ra, vui lòng thử lại sau.';
                }
            }
        } else {
            $error = 'Mật khẩu không khớp.';
        }
    } else {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Đăng ký</title>

    <?php include '../includes/styles.php'; ?>
    
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Main Start -->
    <div class="container-fluid p-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 border rounded p-4">
                <h3 class="text-center">Đăng Ký</h3>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="hoTen">Họ và tên</label>
                        <input type="text" class="form-control" id="hoTen" name="hoTen" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="tenDangNhap">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="tenDangNhap" name="tenDangNhap" required>
                    </div>
                    <div class="form-group">
                        <label for="matKhau">Mật khẩu</label>
                        <input type="password" class="form-control" id="matKhau" name="matKhau" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary">Đăng Ký</button>
                </form>
                <p class="mt-3">Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
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