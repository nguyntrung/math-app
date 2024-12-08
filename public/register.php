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
    <style>
        .register-container {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.2);
            padding: 30px;
            max-width: 450px;
            margin: 20px auto;
            text-align: center;
            border: 4px solid #4caf50;
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '✏️';
            position: absolute;
            top: -30px;
            left: -30px;
            font-size: 80px;
            opacity: 0.2;
            z-index: 1;
        }

        .register-title {
            color: #4caf50;
            font-size: 2rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid #4caf50;
            padding: 10px 15px;
            font-size: 1rem;
            background-color: #fff3e0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #FF9800;
            box-shadow: 0 0 10px rgba(255, 152, 0, 0.3);
        }

        .register-btn {
            background-color: #4caf50;
            border: none;
            color: white;
            padding: 12px 25px;
            font-size: 1.1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .register-btn:hover {
            background-color: #45a049;
            color: white;
            transform: scale(1.05);
        }

        .register-links {
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .register-links a {
            color: #4caf50;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .register-links a:hover {
            color: #F4511E;
            text-decoration: underline;
        }

        .alert-danger {
            background-color: #ffebee;
            color: #d32f2f;
            border: 2px solid #d32f2f;
            border-radius: 15px;
            padding: 10px;
            margin-bottom: 20px;
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .register-container {
                width: 95%;
                margin: 30px auto;
                padding: 20px;
            }

            .register-title {
                font-size: 2rem;
            }
        }
    </style>
    
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Main Start -->
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 register-container">
                <h4 class="register-title">Đăng Ký Tài Khoản</h4>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="hoTen">Họ và tên</label>
                        <input type="text" class="form-control" id="hoTen" name="hoTen" required placeholder="Nhập họ và tên của bạn" value="<?php echo isset($hoTen) ? htmlspecialchars($hoTen) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Nhập địa chỉ email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tenDangNhap">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="tenDangNhap" name="tenDangNhap" required placeholder="Chọn tên đăng nhập" value="<?php echo isset($tenDangNhap) ? htmlspecialchars($tenDangNhap) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="matKhau">Mật khẩu</label>
                        <input type="password" class="form-control" id="matKhau" name="matKhau" required placeholder="Tạo mật khẩu" minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required placeholder="Nhập lại mật khẩu" minlength="6">
                    </div>
                    
                    <button type="submit" name="register" class="btn register-btn">
                        <i class="fa-solid fa-user-plus"></i> Đăng Ký
                    </button>
                </form>
                
                <div class="register-links mt-3">
                    Đã có tài khoản? 
                    <a href="login.php">Đăng nhập ngay</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Main End -->

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const password = document.getElementById('matKhau');
            const confirmPassword = document.getElementById('confirmPassword');

            form.addEventListener('submit', function(event) {
                if (password.value !== confirmPassword.value) {
                    alert('Mật khẩu không khớp. Vui lòng nhập lại.');
                    event.preventDefault();
                }

                if (password.value.length < 6) {
                    alert('Mật khẩu phải có ít nhất 6 ký tự.');
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>