<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['months']) || !isset($_POST['price'])) {
    header('Location: registermember.php');
    exit();
}

$months = (int)$_POST['months'];
$price = (float)$_POST['price'];
$userId = $_SESSION['MaNguoiDung'];

// Tính ngày bắt đầu và kết thúc
$startDate = date('Y-m-d');
$endDate = date('Y-m-d', strtotime("+$months months"));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Thanh toán</title>
    <?php include '../includes/styles.php'; ?>
    <style>
        /* Minimal custom CSS */
        body {
            background-color: #f8f9fa;
        }
        
        .custom-card {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .custom-title {
            color: #17a2b8;
        }
        
        .info-item {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            border-radius: 0.5rem;
        }
        
        .price-info {
            border-left-color: #198754;
            background-color: #f8fff9;
        }
        
        .date-info {
            border-left-color: #ffc107;
            background-color: #fffdf8;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    
                    <div class="card custom-card">
                        <div class="card-body p-4">
                            <h4 class="text-center mb-4 custom-title display-6 fw-bold">Thanh toán</h4>
                            <h5 class="card-title mb-4">Thông tin đăng ký</h5>
                            
                            <!-- Package Info -->
                            <div class="info-item p-3 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-box-seam fs-4 me-2 text-primary"></i>
                                    <p class="mb-0 fs-5">Gói thành viên: <?php echo $months ?> tháng</p>
                                </div>
                            </div>
                            
                            <!-- Price Info -->
                            <div class="info-item price-info p-3 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-currency-dollar fs-4 me-2 text-success"></i>
                                    <p class="mb-0 fs-5 fw-bold text-success">
                                        Số tiền: <?php echo number_format($price, 0, ',', '.'); ?> VNĐ
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Dates Info -->
                            <div class="date-info info-item p-3 mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar-check fs-4 me-2 text-warning"></i>
                                    <p class="mb-0 fs-5">
                                        Ngày bắt đầu: <?php echo date('d/m/Y', strtotime($startDate)); ?>
                                    </p>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-x fs-4 me-2 text-warning"></i>
                                    <p class="mb-0 fs-5">
                                        Ngày kết thúc: <?php echo date('d/m/Y', strtotime($endDate)); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <form action="process_payment.php" method="POST" class="mt-4">
                                <input type="hidden" name="months" value="<?php echo $months; ?>">
                                <input type="hidden" name="price" value="<?php echo $price; ?>">
                                <input type="hidden" name="start_date" value="<?php echo $startDate; ?>">
                                <input type="hidden" name="end_date" value="<?php echo $endDate; ?>">
                                
                                <div class="mb-4">
                                    <label for="payment_method" class="form-label fs-5 mb-2 text-primary">
                                        <i class="bi bi-credit-card me-2"></i>
                                        Phương thức thanh toán
                                    </label>
                                    <select class="form-select form-select-lg rounded-pill" 
                                            id="payment_method" 
                                            name="payment_method" 
                                            required>
                                        <option value="THANH_TOAN_TRUC_TUYEN">💳 Thanh toán trực tuyến</option>
                                        <option value="CHUYEN_KHOAN">🏦 Chuyển khoản ngân hàng</option>
                                    </select>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" 
                                            class="btn btn-primary rounded-pill py-3 fs-5">
                                        <i class="bi bi-check2-circle me-2"></i>
                                        Xác nhận thanh toán
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>