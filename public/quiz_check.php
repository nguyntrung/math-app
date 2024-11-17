<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

if (!isset($_SESSION['startTime'])) {
    $_SESSION['startTime'] = time();
}

// Khởi tạo biến để kiểm tra xem có cần hiển thị câu hỏi hay không
$showQuiz = true;
$diem = 0;
$ngayThi = date('Y-m-d H:i:s'); // Lưu ngày và giờ thi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra xem có dữ liệu từ biểu mẫu không
    if (isset($_POST['cauHoi'])) {
        // Nhận dữ liệu từ biểu mẫu
        $dapAnHocsinh = $_POST['cauHoi'];

        // Lấy tất cả câu hỏi cùng với đáp án đúng từ cơ sở dữ liệu
        $stmt = $conn->prepare("SELECT MaCauHoi, DapAnDung FROM cauhoitracnghiem WHERE MaCauHoi IN (" . implode(',', array_keys($dapAnHocsinh)) . ")");
        $stmt->execute();
        $cauHoiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính điểm
        foreach ($cauHoiList as $cauHoi) {
            if ($dapAnHocsinh[$cauHoi['MaCauHoi']] === $cauHoi['DapAnDung']) {
                $diem++;
            }
        }

        // Nhận thời gian bắt đầu (giả sử đây là thời điểm gửi form)
        $startTime = $_SESSION['startTime']; // Thời gian bắt đầu
        $endTime = $ngayThi; // Thời gian kết thúc (ngayThi)
        $thoiGianThi = strtotime($endTime) - strtotime($startTime); // Tính thời gian thi (giây)
    
            // Lưu kết quả vào bảng ketquakiemtra
            $stmtInsert = $conn->prepare("INSERT INTO ketquakiemtra (MaNguoiDung, Diem, ThoiGianThi, NgayThi) 
                                                    VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$_SESSION['MaNguoiDung'], $diem, $thoiGianThi, $ngayThi]);

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
        .quiz-container {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .quiz-title {
            text-align: center;
            margin-bottom: 30px;
            color: #4A90E2;
            position: relative;
            padding-bottom: 15px;
        }

        .quiz-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(45deg, #4A90E2, #67B26F);
            border-radius: 3px;
        }

        .quiz-header {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: center;
        }

        .question-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px solid #e9ecef;
            transition: transform 0.2s;
        }

        .question-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .question-number {
            background: #4A90E2;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }

        .question-content {
            font-size: 1.1rem;
            color: #333;
            margin: 15px 0;
            font-weight: 500;
        }

        .answer-option {
            margin: 10px 0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #e9ecef;
        }

        .answer-option:hover {
            background-color: #f8f9fa;
            border-color: #4A90E2;
        }

        .answer-option input[type="radio"] {
            margin-right: 10px;
            transform: scale(1.2);
        }

        .answer-option label {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }

        .submit-btn {
            background: linear-gradient(45deg, #4A90E2, #67B26F);
            border: none;
            padding: 15px 30px;
            font-size: 1.2rem;
            border-radius: 50px;
            color: white;
            width: 100%;
            margin-top: 30px;
            transition: all 0.3s;
            font-weight: bold;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
        }

        .result-container {
            text-align: center;
            padding: 30px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .result-score {
            font-size: 3rem;
            color: #4A90E2;
            margin: 20px 0;
            font-weight: bold;
        }

        .return-btn {
            background: #4A90E2;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .return-btn:hover {
            background: #357ABD;
            text-decoration: none;
            color: white;
            transform: translateY(-2px);
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>

</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container pt-5">
        <div class="quiz-container">
            <?php if ($showQuiz): ?>
                <h4 class="quiz-title">Kiểm Tra</h4>
                <div class="quiz-header">
                    <p class="mb-0">Hãy chọn đáp án đúng nhất cho mỗi câu hỏi nhé! 😊</p>
                </div>
                
                <form method="POST" action="">
                    <?php foreach ($cauHoiList as $index => $cauHoi): ?>
                    <div class="question-card">
                        <div class="d-flex align-items-center">
                            <span class="question-number"><?= ($index + 1) ?></span>
                            <p class="question-content"><?= htmlspecialchars($cauHoi['NoiDung']); ?></p>
                        </div>

                        
                        <div class="answer-option">
                            <label>
                                <input type="radio" name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" value="A">
                                <span>A. <?= htmlspecialchars($cauHoi['DapAnA']); ?></span>
                            </label>
                        </div>
                        
                        <div class="answer-option">
                            <label>
                                <input type="radio" name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" value="B">
                                <span>B. <?= htmlspecialchars($cauHoi['DapAnB']); ?></span>
                            </label>
                        </div>
                        
                        <div class="answer-option">
                            <label>
                                <input type="radio" name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" value="C">
                                <span>C. <?= htmlspecialchars($cauHoi['DapAnC']); ?></span>
                            </label>
                        </div>
                        
                        <div class="answer-option">
                            <label>
                                <input type="radio" name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" value="D">
                                <span>D. <?= htmlspecialchars($cauHoi['DapAnD']); ?></span>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane mr-2"></i> Nộp Bài
                    </button>
                </form>
                
            <?php else: ?>
                <div class="result-container">
                    <img src="/api/placeholder/100/100" alt="Trophy" style="width: 100px; animation: bounce 2s infinite;">
                    <h4 style="color: #4A90E2; margin: 20px 0;">Kết Quả Kiểm Tra</h4>
                    <div class="result-score">
                        <?= $diem; ?>/10
                    </div>
                    <p>Bạn đã hoàn thành bài kiểm tra! Hãy tiếp tục cố gắng nhé! 💪</p>
                    <a href="theory_lessons.php" class="return-btn">
                        <i class="fas fa-arrow-left mr-2"></i> Trở Về Bài Học
                    </a>
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