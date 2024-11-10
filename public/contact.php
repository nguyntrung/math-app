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
                <label for="ho_ten">Họ và tên:</label>
                <input type="text" class="form-control" id="ho_ten" name="ho_ten">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="lop">Lớp:</label>
                <input type="text" class="form-control" id="lop" name="lop" value="5">
            </div>
            <div class="form-group">
                <label for="noi_dung">Nội dung:</label>
                <textarea class="form-control" id="noi_dung" name="noi_dung" rows="4"></textarea><br><br>
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