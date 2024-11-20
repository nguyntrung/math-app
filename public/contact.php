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
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <!-- Main Start -->
    <div class="container pt-5">
        <div class="container pb-5">
            <h1 class="text-center mb-4">Liên Hệ</h1>
            
            <form action="submit_contact.php" method="post">
            <div class="form-group">
                <label for="HoTen">Họ và tên:</label>
                <input type="text" class="form-control" id="HoTen" name="HoTen">
            </div>
            <div class="form-group">
                <label for="Email">Email:</label>
                <input type="Email" class="form-control" id="Email" name="Email">
            </div>
            <div class="form-group">
                <label for="NoiDung">Nội dung:</label>
                <textarea class="form-control" id="NoiDung" name="NoiDung" rows="4"></textarea><br><br>
            </div>
                <button type="submit" class="btn btn-primary">GỬI ĐI</button>
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