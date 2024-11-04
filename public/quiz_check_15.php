<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Khởi tạo biến để kiểm tra xem có cần hiển thị câu hỏi hay không
$showQuiz = true;
$diem = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra xem có dữ liệu từ biểu mẫu không
    if (isset($_POST['cauHoi'])) {
        // Nhận dữ liệu từ biểu mẫu
        $dapAnHocsinh = $_POST['cauHoi'];

        // Lấy tất cả câu hỏi cùng với đáp án đúng từ cơ sở dữ liệu
        $stmt = $conn->prepare("SELECT MaCauHoi, DapAn FROM cauhoitracnghiem WHERE MaCauHoi IN (" . implode(',', array_keys($dapAnHocsinh)) . ")");
        $stmt->execute();
        $cauHoiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính điểm
        foreach ($cauHoiList as $cauHoi) {
            if ($dapAnHocsinh[$cauHoi['MaCauHoi']] === $cauHoi['DapAn']) {
                $diem++;
            }
        }

        // Đặt biến để không hiển thị câu hỏi nữa
        $showQuiz = false;
    } else {
        // Nếu không có dữ liệu câu hỏi, có thể là lỗi trong việc gửi biểu mẫu
        $showQuiz = false;
        $diem = 0; // Hoặc có thể thông báo lỗi
    }
}

// Nếu cần thiết, lấy 10 câu hỏi ngẫu nhiên từ cơ sở dữ liệu
if ($showQuiz) {
    $stmt = $conn->prepare("SELECT * FROM cauhoitracnghiem ORDER BY RAND() LIMIT 10");
    $stmt->execute();
    $cauHoiList = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kiểm tra 15 phút</title>

    <?php include '../includes/styles.php'; ?>
    <style>
        body {
            background-color: #e9f5ff;
        }
        .result {
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .question {
            margin-bottom: 20px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .btn-success {
        background-color: #ff6347;
        border-color: #ff6347;
        }
        .btn-success:hover {
            background-color: #ff8560;
            border-color: #ff8560;
        }
    </style>

</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container pt-5">
        <div class="pb-5">
            <h4 class="text-center mb-4" style="color: #ff6347;">Kiểm tra 15 phút</h4>
            
            <?php if ($showQuiz): ?>
                <form method="POST" action="">
                    <?php foreach ($cauHoiList as $index => $cauHoi): ?>
                        <div class="question">
                            <p class="font-weight-bold"><?= ($index + 1) . '. ' . htmlspecialchars($cauHoi['NoiDung']); ?></p>
                            <div>
                                <label><input type="radio" name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" value="A" class="mr-2"> A: <?= htmlspecialchars($cauHoi['DapAnA']); ?></label><br>
                                <label><input type="radio" name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" value="B" class="mr-2"> B: <?= htmlspecialchars($cauHoi['DapAnB']); ?></label><br>
                                <label><input type="radio" name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" value="C" class="mr-2"> C: <?= htmlspecialchars($cauHoi['DapAnC']); ?></label><br>
                                <label><input type="radio" name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" value="D" class="mr-2"> D: <?= htmlspecialchars($cauHoi['DapAnD']); ?></label><br>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-success btn-lg btn-block mt-4">Nộp bài</button>
                </form>
            <?php else: ?>
                <div class="result">
                    <h4 style="color: #ff6347;">Kết quả kiểm tra</h4>
                    <p>Bạn đã trả lời đúng <?= $diem; ?>/10 câu hỏi.</p>
                    <a href="theory_lessons.php" class="btn btn-primary">Trở về bài học</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
