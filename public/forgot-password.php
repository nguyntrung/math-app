<?php
// forgot-password.php
session_start();
include '../database/db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['forgot_password'])) {
    $email = trim($_POST['email']);
    
    if (!empty($email)) {
        // Kiểm tra email có tồn tại và tài khoản đang hoạt động
        $stmt = $conn->prepare("SELECT * FROM nguoidung WHERE Email = :email AND TrangThaiHoatDong = 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            // Tạo token reset password
            $token = bin2hex(random_bytes(32));
            $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Lưu token vào database
            $stmt = $conn->prepare("UPDATE nguoidung SET reset_token = :token, reset_token_expiry = :expiry WHERE Email = :email");
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expiry', $token_expiry);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute()) {
                // Gửi email
                $mail = new PHPMailer(true);
                try {
                    // Cấu hình mail server
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'nguyntrung203@gmail.com'; // Thay thế bằng email của bạn
                    $mail->Password = 'cixn qtfa zjxl nczs'; // Thay thế bằng mật khẩu ứng dụng của bạn
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';

                    // Người nhận
                    $mail->setFrom('your-email@gmail.com', 'ToanLop5.vn');
                    $mail->addAddress($email, $user['HoTen']);

                    // Nội dung
                    $mail->isHTML(true);
                    $mail->Subject = 'Yêu cầu đặt lại mật khẩu';
                    $reset_link = "http://localhost/math-app/public/reset-password.php?token=" . $token;
                    
                    $mailContent = "
                        <h2>Xin chào {$user['HoTen']},</h2>
                        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                        <p>Vui lòng click vào link dưới đây để đặt lại mật khẩu:</p>
                        <p><a href='$reset_link'>Đặt lại mật khẩu</a></p>
                        <p>Hoặc copy đường link sau vào trình duyệt: $reset_link</p>
                        <p>Link này sẽ hết hạn sau 1 giờ.</p>
                        <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
                        <p>Trân trọng,<br>ToanLop5.vn</p>
                    ";
                    
                    $mail->Body = $mailContent;
                    $mail->send();
                    $success = 'Vui lòng kiểm tra email của bạn để được hướng dẫn đặt lại mật khẩu.';
                } catch (Exception $e) {
                    $error = "Không thể gửi email. Vui lòng thử lại sau.";
                }
            } else {
                $error = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
            }
        } else {
            $error = 'Email không tồn tại hoặc tài khoản đã bị vô hiệu hóa.';
        }
    } else {
        $error = 'Vui lòng nhập email.';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container-fluid p-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 border rounded p-4">
                <h3 class="text-center">Quên mật khẩu</h3>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group mb-3">
                        <label for="email">Email đăng ký</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <button type="submit" name="forgot_password" class="btn btn-primary">Gửi yêu cầu</button>
                </form>
                <p class="mt-3"><a href="login.php">Quay lại đăng nhập</a></p>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>