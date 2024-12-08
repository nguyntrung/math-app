<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Initialize variables
$showQuiz = true;
$diem = 0;
$ngayThi = date('Y-m-d H:i:s');

// Check if chapter ID is passed
if (!isset($_GET['maChuong'])) {
    echo "Không có mã chương được chọn.";
    exit();
}

$maChuong = intval($_GET['maChuong']);

// Fetch the chapter name
$stmt = $conn->prepare("SELECT TenChuong FROM chuonghoc WHERE MaChuong = :maChuong");
$stmt->bindParam(':maChuong', $maChuong);
$stmt->execute();
$chapter = $stmt->fetch(PDO::FETCH_ASSOC);

if ($chapter) {
    $chapterName = $chapter['TenChuong'];
} else {
    echo "Chương không tồn tại.";
    exit();
}

// Verify chapter exists and user has access
try {
    // Check if user is an active member (similar to the logic in the previous file)
    $isActiveMember = false;
    $stmt = $conn->prepare("
        SELECT COUNT(*) as active_count 
        FROM dangkythanhvien 
        WHERE MaNguoiDung = :maNguoiDung 
        AND TrangThai = 'DANG_HOAT_DONG' 
        AND NgayKetThuc >= CURRENT_DATE()
    ");
    $stmt->bindParam(':maNguoiDung', $_SESSION['MaNguoiDung']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if the chapter is free or user is an active member
    $stmt = $conn->prepare("
        SELECT MienPhi 
        FROM chuonghoc 
        WHERE MaChuong = :maChuong
    ");
    $stmt->bindParam(':maChuong', $maChuong);
    $stmt->execute();
    $chapterInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Determine access based on chapter's free status and membership
    $canAccess = ($chapterInfo['MienPhi'] == 1) || 
                 ($chapterInfo['MienPhi'] == 0 && $result['active_count'] > 0);

    if (!$canAccess) {
        // Redirect to membership registration or show error
        header('Location: registermember.php');
        exit();
    }

    // Process quiz submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['cauHoi'])) {
            // Receive submitted answers
            $dapAnHocsinh = $_POST['cauHoi'];
            $thoiGianLamBai = isset($_POST['thoiGianLamBai']) ? intval($_POST['thoiGianLamBai']) : 0;

            // Get all questions with correct answers for this submission
            $stmt = $conn->prepare("
                SELECT MaCauHoi, DapAnDung 
                FROM cauhoitracnghiem 
                WHERE MaCauHoi IN (" . implode(',', array_keys($dapAnHocsinh)) . ")
            "); 
            $stmt->execute();
            $cauHoiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate score
            foreach ($cauHoiList as $cauHoi) {
                if ($dapAnHocsinh[$cauHoi['MaCauHoi']] === $cauHoi['DapAnDung']) {
                    $diem++;
                }
            }

            // Save test history
            $stmt = $conn->prepare("
                INSERT INTO lichsukiemtra (MaChuong, MaNguoiDung, NgayKiemTra, ThoiGian, Diem) 
                VALUES (:maChuong, :maNguoiDung, :ngayThi, :thoiGianLamBai, :diem)
            ");
            $stmt->bindParam(':maChuong', $maChuong);
            $stmt->bindParam(':maNguoiDung', $_SESSION['MaNguoiDung']);
            $stmt->bindParam(':ngayThi', $ngayThi);
            $stmt->bindParam(':thoiGianLamBai', $thoiGianLamBai);
            $stmt->bindParam(':diem', $diem);
            $stmt->execute();

            $showQuiz = false;
        } else {
            $showQuiz = false;
            $diem = 0;
        }
    }

    // Retrieve 20 random questions from the specific chapter
    if ($showQuiz) {
        $stmt = $conn->prepare("
            SELECT cht.* 
            FROM cauhoitracnghiem cht
            JOIN baihoc bh ON cht.MaBaiHoc = bh.MaBaiHoc
            WHERE bh.MaChuong = :maChuong
            ORDER BY RAND() 
            LIMIT 20
        ");
        $stmt->bindParam(':maChuong', $maChuong);
        $stmt->execute();
        $cauHoiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If not enough questions in the chapter, supplement from other chapters
        if (count($cauHoiList) < 20) {
            $remainingQuestions = 20 - count($cauHoiList);
            $stmt = $conn->prepare("
                SELECT cht.* 
                FROM cauhoitracnghiem cht
                JOIN baihoc bh ON cht.MaBaiHoc = bh.MaBaiHoc
                WHERE bh.MaChuong != :maChuong
                ORDER BY RAND() 
                LIMIT :remainingQuestions
            ");
            $stmt->bindParam(':maChuong', $maChuong);
            $stmt->bindParam(':remainingQuestions', $remainingQuestions, PDO::PARAM_INT);
            $stmt->execute();
            $additionalQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $cauHoiList = array_merge($cauHoiList, $additionalQuestions);
        }
    }

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm Tra Chương Học</title>

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

        .answer-option-container {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        .answer-option {
            flex: 1;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            margin: 5px;
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
            padding: 5px;
            cursor: pointer;
            font-size: small;
            width: 100%;
        }

        .submit-btn {
            background: #66cb87;
            width: 25%;
            margin: 0 auto;
            display: block;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
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
        .timer-container {
            position: fixed;
            top: 130px;
            right: 50px;
            background-color: #4A90E2;
            color: white;
            padding: 20px;
            border-radius: 5px;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .timer-warning {
            background-color: #dc3545;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container pt-5">
        <?php if ($showQuiz): ?>
            <div class="timer-container">
                <p class="m-0">Thời gian làm bài còn lại: <span id="timer">30:00</span></p>
            </div>
        <?php endif; ?>

        <div class="quiz-container mb-5">
            <?php if ($showQuiz): ?>
                <h4 class="quiz-title">KIỂM TRA</h4>
                <h4 class="quiz-title"><?= htmlspecialchars($chapterName); ?></h4>
                <div class="quiz-header">
                    <p class="mb-0">Hãy chọn đáp án đúng nhất cho mỗi câu hỏi! Thời gian làm bài: 30 phút 📚</p>
                </div>
                
                <form method="POST" action="" id="quizForm">
                    <input type="hidden" name="thoiGianLamBai" id="thoiGianLamBai" value="0">
                    <?php foreach ($cauHoiList as $index => $cauHoi): ?>
                        <div class="question-card">
                            <div class="d-flex align-items-center">
                                <span class="question-number"><?= ($index + 1) ?></span>
                                <p class="question-content"><?= htmlspecialchars($cauHoi['NoiDung']); ?></p>
                            </div>

                            <div class="answer-option-container">
                                <div class="answer-option">
                                    <label>
                                        <input type="radio" name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" value="A" required>
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
                        <?= $diem; ?>/20
                    </div>
                    <p>Bạn đã hoàn thành bài kiểm tra! Hãy tiếp tục cố gắng nhé! 💪</p>
                    <a href="video_lessons.php" class="return-btn">
                        <i class="fas fa-arrow-left mr-2"></i> Trở Về Danh Sách Bài Học
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>

    <?php if ($showQuiz): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timerDisplay = document.getElementById('timer');
            const quizForm = document.getElementById('quizForm');
            const thoiGianLamBaiInput = document.getElementById('thoiGianLamBai');
            let timeLeft = 30 * 60; // 30 minutes in seconds

            function formatTime(seconds) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
            }

            function updateTimer() {
                timerDisplay.textContent = formatTime(timeLeft);

                // Add warning style when less than 5 minutes remain
                if (timeLeft <= 5 * 60) {
                    timerDisplay.classList.add('timer-warning');
                }

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    timerDisplay.textContent = '00:00';
                    quizForm.submit(); // Auto submit when time is up
                }

                timeLeft--;
            }

            // Initial timer update
            updateTimer();

            // Timer interval
            const timerInterval = setInterval(updateTimer, 1000);

            // Before form submission, set the time spent
            quizForm.addEventListener('submit', function() {
                const timeSpent = 30 * 60 - timeLeft;
                thoiGianLamBaiInput.value = timeSpent;
            });

            // Prevent accidental page navigation
            window.addEventListener('beforeunload', function(e) {
                if (timeLeft > 0) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>