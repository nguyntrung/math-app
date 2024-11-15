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
    $matKhauCu = $_POST['matKhauCu'] ?? null;
    $matKhauMoi = $_POST['matKhauMoi'] ?? null;
    $xacNhanMatKhau = $_POST['xacNhanMatKhau'] ?? null;

    // Lấy mật khẩu hiện tại từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT MatKhau FROM nguoidung WHERE MaNguoiDung = :maNguoiDung");
    $stmt->bindParam(':maNguoiDung', $maNguoiDung);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($matKhauCu && !password_verify($matKhauCu, $user['MatKhau'])) {
        $error = "Mật khẩu cũ không đúng.";
    } else {
        if ($matKhauMoi && $matKhauMoi !== $xacNhanMatKhau) {
            $error = "Mật khẩu mới và xác nhận không khớp.";
        } else {
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

    <style>
    .card {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border: none;
    }

    .list-group-item {
        border: none;
        padding: 0.75rem 1rem;
        color: #666;
    }

    .list-group-item i {
        width: 20px;
    }

    .list-group-item.active {
        background-color: #f8f9fa;
        color: #000;
        font-weight: bold;
        border-color: transparent;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .text-muted {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-control {
        border-radius: 8px;
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
    }

    .form-control:focus {
        border-color: #65B446;
        box-shadow: 0 0 0 0.2rem rgba(101, 180, 70, 0.25);
    }

    .btn-primary {
        background-color: #65B446;
        border-color: #65B446;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
    }

    .btn-primary:hover {
        background-color: #528f38;
        border-color: #528f38;
    }

    .alert {
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid pt-5">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <!-- Profile Picture -->
                        <div class="mb-3">
                            <img src="../assets/img/2.png" 
                                class="rounded-circle" 
                                alt="Avatar" 
                                style="width: 100px; height: 100px;">
                        </div>
                        
                        <!-- Name and Class -->
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($user['HoTen']); ?></h5>
                        <p class="text-muted small mb-3">Khối 5</p>
                        
                        <!-- Navigation Menu -->
                        <div class="list-group text-start">
                            <a href="profile.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-user me-2" style="color: #65B446"></i> Thông tin cá nhân
                            </a>
                            <a href="#" class="list-group-item list-group-item-action active">
                                <i class="fas fa-key me-2" style="color: #6587ff"></i> Đổi mật khẩu
                            </a>
                            <a href="registermember.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-graduation-cap me-2" style="color: #e46356"></i> Khóa học của bạn
                            </a>
                            <a href="logout.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-sign-out-alt me-2" style="color: #687187"></i> Đăng xuất
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0" style="color: #65B446">Chỉnh sửa thông tin cá nhân</h5>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" onsubmit="return validateForm()">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Họ tên</label>
                                    <input type="text" class="form-control" id="hoTen" name="hoTen" 
                                           value="<?php echo htmlspecialchars($user['HoTen']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3" id="passwordGroup" style="display: none;">
                                    <label class="text-muted">Mật khẩu cũ</label>
                                    <input type="password" class="form-control" id="matKhauCu" name="matKhauCu">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="matKhauMoi" name="matKhauMoi">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Xác nhận mật khẩu mới</label>
                                    <input type="password" class="form-control" id="xacNhanMatKhau" name="xacNhanMatKhau">
                                    <div id="error-message" class="text-danger mt-2" style="display:none;"></div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
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

            if (matKhauMoi || xacNhanMatKhau) {
                if (!matKhauCu) {
                    errorMessage.textContent = "Bạn cần nhập mật khẩu cũ để thay đổi mật khẩu.";
                    errorMessage.style.display = "block";
                    return false;
                }

                if (matKhauMoi && matKhauMoi !== xacNhanMatKhau) {
                    errorMessage.textContent = "Mật khẩu mới và xác nhận không khớp.";
                    errorMessage.style.display = "block";
                    return false;
                }
            }

            errorMessage.style.display = "none";
            return true;
        }

        document.getElementById('matKhauMoi').addEventListener('input', function() {
            const passwordGroup = document.getElementById('passwordGroup');
            if (this.value) {
                passwordGroup.style.display = "block";
            } else {
                passwordGroup.style.display = "none";
                document.getElementById('matKhauCu').value = '';
            }
        });
    </script>
</body>
</html>