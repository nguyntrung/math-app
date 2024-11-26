<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang liên hệ</title>

    <?php include '../includes/styles.php'; ?>
    <style>
        /* Contact Page Container */
        .contact-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        /* Page Title */
        .contact-title {
            text-align: center;
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 15px;
        }

        .contact-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(to right, #3498db, #2ecc71);
            border-radius: 2px;
        }

        /* Form Styling */
        .contact-form {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            background-color: #ffffff;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        /* Submit Button */
        .btn-submit {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 30px auto 0;
            padding: 12px 30px;
            background: linear-gradient(to right, #3498db, #2ecc71);
            border: none;
            border-radius: 25px;
            color: white;
            font-size: 1.1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .contact-container {
                padding: 20px 15px;
            }

            .contact-title {
                font-size: 2rem;
            }

            .btn-submit {
                width: 100%;
            }
        }

        /* Animation for form elements */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            animation: fadeIn 0.5s ease forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }

        /* Error state styling */
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-control.is-invalid + .invalid-feedback {
            display: block;
        }

        /* Success message styling */
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <!-- Main Start -->
    <div class="container-fluid pt-5" style="background-color: #f0f0e6;">
        <div class="contact-container">
            <h1 class="contact-title">Liên Hệ</h1>
            
            <form class="contact-form" action="submit_contact.php" method="post">
                <div class="form-group">
                    <label for="HoTen">Họ và tên:</label>
                    <input type="text" class="form-control" id="HoTen" name="HoTen" required>
                    <div class="invalid-feedback">
                        Vui lòng nhập họ tên của bạn
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="Email">Email:</label>
                    <input type="email" class="form-control" id="Email" name="Email" required>
                    <div class="invalid-feedback">
                        Vui lòng nhập email hợp lệ
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="NoiDung">Nội dung:</label>
                    <textarea class="form-control" id="NoiDung" name="NoiDung" rows="4" required></textarea>
                    <div class="invalid-feedback">
                        Vui lòng nhập nội dung tin nhắn
                    </div>
                </div>
                
                <button type="submit" class="btn btn-submit">Gửi đi</button>
            </form>
        </div>
    </div>
    <!-- Main End -->

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
</body>
</html>