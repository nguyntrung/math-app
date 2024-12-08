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
    <style>
        .login-container {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.2);
            padding: 30px;
            max-width: 400px;
            margin: 50px auto;
            text-align: center;
            border: 4px solid #4CAF50;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '🏫';
            position: absolute;
            top: -30px;
            left: -30px;
            font-size: 80px;
            opacity: 0.2;
            z-index: 1;
        }

        .login-title {
            color: #2196F3;
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid #4CAF50;
            padding: 10px 15px;
            font-size: 1rem;
            background-color: #f1f8e9;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #2196F3;
            box-shadow: 0 0 10px rgba(33, 150, 243, 0.3);
        }

        .login-btn {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 12px 25px;
            font-size: 1.1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .login-btn:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        .login-links {
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .login-links a {
            color: #2196F3;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .login-links a:hover {
            color: #45a049;
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
            .login-container {
                width: 90%;
                margin: 30px auto;
                padding: 20px;
            }

            .login-title {
                font-size: 2rem;
            }
        }
    </style>
    
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Main Start -->
    <div class="container-fluid p-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 login-container">
                <h3 class="login-title">🎒 Đăng Nhập</h3>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="tenDangNhap">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="tenDangNhap" name="tenDangNhap" required placeholder="Nhập tên đăng nhập">
                    </div>
                    <div class="form-group">
                        <label for="matKhau">Mật khẩu</label>
                        <input type="password" class="form-control" id="matKhau" name="matKhau" required placeholder="Nhập mật khẩu">
                    </div>
                    <button type="submit" name="login" class="btn login-btn">
                        <i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập
                    </button>
                </form>
                
                <div class="login-links">
                    Chưa có tài khoản? 
                    <a href="register.php">Đăng ký</a> | 
                    <a href="forgot-password.php">Quên mật khẩu</a>
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
</body>
</html>
