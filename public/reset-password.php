<?php
// reset-password.php
session_start();
include '../database/db.php';

$error = '';
$success = '';

if (!isset($_GET['token'])) {
    header('Location: login.php');
    exit();
}

$token = $_GET['token'];

// Kiểm tra token có hợp lệ không và tài khoản có đang hoạt động không
$stmt = $conn->prepare("SELECT * FROM nguoidung WHERE reset_token = :token AND reset_token_expiry > NOW() AND TrangThaiHoatDong = 1");
$stmt->bindParam(':token', $token);
$stmt->execute();
$user = $stmt->fetch();

if (!$user) {
    $error = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($newPassword === $confirmPassword) {
        if (strlen($newPassword) >= 6) {
            // Cập nhật mật khẩu mới
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE nguoidung SET MatKhau = :password, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = :token");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':token', $token);
            
            if ($stmt->execute()) {
                $success = 'Mật khẩu đã được đặt lại thành công. Vui lòng <a href="login.php">đăng nhập</a> với mật khẩu mới.';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại sau.';
            }
        } else {
            $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }
    } else {
        $error = 'Mật khẩu xác nhận không khớp.';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container-fluid p-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 border rounded p-4">
                <h3 class="text-center">Đặt lại mật khẩu</h3>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                    <form method="POST" action="" id="resetPasswordForm">
                        <div class="form-group mb-3">
                            <label for="new_password">Mật khẩu mới</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required 
                                   minlength="6">
                        </div>
                        <div class="form-group mb-3">
                            <label for="confirm_password">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   required minlength="6">
                        </div>
                        <button type="submit" name="reset_password" class="btn btn-primary">Đặt lại mật khẩu</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
    
    <script>
    document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
        const password = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Mật khẩu xác nhận không khớp!');
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Mật khẩu phải có ít nhất 6 ký tự!');
        }
    });
    </script>
</body>
</html>