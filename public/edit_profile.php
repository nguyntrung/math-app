<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

$maNguoiDung = $_SESSION['MaNguoiDung'];

// Xử lý form khi người dùng gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoTen = $_POST['hoTen'];
    $email = $_POST['email'];
    $matKhauCu = $_POST['matKhauCu'] ?? null; // Mật khẩu cũ có thể không có
    $matKhauMoi = $_POST['matKhauMoi'] ?? null;
    $xacNhanMatKhau = $_POST['xacNhanMatKhau'] ?? null;

    // Lấy mật khẩu hiện tại từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT MatKhau FROM nguoidung WHERE MaNguoiDung = :maNguoiDung");
    $stmt->bindParam(':maNguoiDung', $maNguoiDung);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nếu mật khẩu cũ được cung cấp, kiểm tra nó
    if ($matKhauCu && !password_verify($matKhauCu, $user['MatKhau'])) {
        $error = "Mật khẩu cũ không đúng.";
    } else {
        // Kiểm tra mật khẩu mới và xác nhận nếu có
        if ($matKhauMoi && $matKhauMoi !== $xacNhanMatKhau) {
            $error = "Mật khẩu mới và xác nhận không khớp.";
        } else {
            // Cập nhật thông tin vào cơ sở dữ liệu
            $stmt = $conn->prepare("UPDATE nguoidung SET HoTen = :hoTen, Email = :email" . ($matKhauMoi ? ", MatKhau = :matKhauMoi" : "") . " WHERE MaNguoiDung = :maNguoiDung");
            $stmt->bindParam(':hoTen', $hoTen);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':maNguoiDung', $maNguoiDung);
            if ($matKhauMoi) {
                $hashedMatKhauMoi = password_hash($matKhauMoi, PASSWORD_DEFAULT);
                $stmt->bindParam(':matKhauMoi', $hashedMatKhauMoi);
            }
            $stmt->execute();
            
            header('Location: profile.php');
            exit();
        }
    }
}

// Lấy thông tin người dùng
$stmt = $conn->prepare("SELECT * FROM nguoidung WHERE MaNguoiDung = :maNguoiDung");
$stmt->bindParam(':maNguoiDung', $maNguoiDung);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Chỉnh Sửa Hồ Sơ</title>
    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid pt-5">
        <div class="container col-12 col-md-6 p-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center">Chỉnh sửa thông tin</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" onsubmit="return validateForm()">
                        <div class="form-group">
                            <label for="hoTen">Tên</label>
                            <input type="text" class="form-control" id="hoTen" name="hoTen" value="<?php echo htmlspecialchars($user['HoTen']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                        </div>
                        <div class="form-group" id="passwordGroup" style="display: none;">
                            <label for="matKhauCu">Mật Khẩu Cũ (để thay đổi mật khẩu)</label>
                            <input type="password" class="form-control" id="matKhauCu" name="matKhauCu">
                        </div>
                        <div class="form-group">
                            <label for="matKhauMoi">Mật Khẩu Mới</label>
                            <input type="password" class="form-control" id="matKhauMoi" name="matKhauMoi">
                        </div>
                        <div class="form-group">
                            <label for="xacNhanMatKhau">Xác Nhận Mật Khẩu Mới</label>
                            <input type="password" class="form-control" id="xacNhanMatKhau" name="xacNhanMatKhau">
                            <div id="error-message" class="text-danger" style="display:none;"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Cập Nhật Thông Tin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>

    <script>
        function validateForm() {
            const matKhauCu = document.getElementById('matKhauCu').value;
            const matKhauMoi = document.getElementById('matKhauMoi').value;
            const xacNhanMatKhau = document.getElementById('xacNhanMatKhau').value;
            const errorMessage = document.getElementById('error-message');

            // Kiểm tra nếu mật khẩu mới và xác nhận được nhập
            if (matKhauMoi || xacNhanMatKhau) {
                if (!matKhauCu) {
                    errorMessage.textContent = "Bạn cần nhập mật khẩu cũ để thay đổi mật khẩu.";
                    errorMessage.style.display = "block";
                    return false; // Ngăn không cho gửi form
                }

                // Kiểm tra mật khẩu mới và xác nhận
                if (matKhauMoi && matKhauMoi !== xacNhanMatKhau) {
                    errorMessage.textContent = "Mật khẩu mới và xác nhận không khớp.";
                    errorMessage.style.display = "block";
                    return false; // Ngăn không cho gửi form
                }
            }

            errorMessage.style.display = "none"; // Ẩn thông báo lỗi nếu khớp
            return true; // Cho phép gửi form
        }

        // Hiển thị trường mật khẩu cũ khi nhập mật khẩu mới
        document.getElementById('matKhauMoi').addEventListener('input', function() {
            const passwordGroup = document.getElementById('passwordGroup');
            if (this.value) {
                passwordGroup.style.display = "block";
            } else {
                passwordGroup.style.display = "none";
                document.getElementById('matKhauCu').value = ''; // Xóa mật khẩu cũ nếu không có mật khẩu mới
            }
        });
    </script>
</body>
</html>
