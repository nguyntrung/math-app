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
$thoiGianThi = 0; // Đặt giá trị mặc định là 0

date_default_timezone_set('Asia/Ho_Chi_Minh'); // Múi giờ Việt Nam

// Lưu kết quả vào bảng ketquakiemtra
$maNguoiDung = $_SESSION['MaNguoiDung']; 
$ngayThi = date('Y-m-d H:i:s'); // Ngày giờ thi

if (!isset($_SESSION['startTime'])) {
    $_SESSION['startTime'] = time(); // Timestamp chuẩn
}

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

        // Đặt biến để không hiển thị câu hỏi nữa
        $showQuiz = false;

        // Lưu kết quả vào bảng ketqua
        $maNguoiDung = $_SESSION['MaNguoiDung'];
        $ngayThi = date('Y-m-d H:i:s');
        $startTime = strtotime($_SESSION['startTime']);
        $endTime = strtotime($ngayThi);
        $thoiGianThi = $endTime - $startTime; // Tính thời gian thi (giây)

        $sql = "INSERT INTO xephangthidau (MaNguoiDung, Diem, ThoiGianThi, NgayThi) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$maNguoiDung, $diem, $thoiGianThi, $ngayThi]);
    } else {
        $showQuiz = false;
        $diem = 0;
    }
}

// Nếu cần thiết, lấy 10 câu hỏi ngẫu nhiên từ cơ sở dữ liệu
if ($showQuiz) {
    $stmt = $conn->prepare("SELECT * FROM cauhoitracnghiem ORDER BY RAND() LIMIT 40");
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
    <title>Thi đấu</title>
    <?php include '../includes/styles.php'; ?>
    <style>
        .quiz-header {
            background: linear-gradient(45deg,rgb(88, 195, 211), #17a2b8);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
        }

        .progress-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }

        .question-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
        }

        .question-card:hover {
            transform: translateY(-3px);
        }

        .card-header {
            background: #17a2b8;
        }

        .btn-primary {
            background: #17a2b8;
            max-width: 200px;
            margin: 0 auto;
            border-radius: 25px;
            border: none;
        }

        .btn-primary:hover {
            background: #7de381;
        }

        .answer-option {
            transition: all 0.3s;
        }

        .answer-option:hover {
            background-color: #f8f9fa;
        }

        .result-card {
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            background: linear-gradient(45deg, #FF512F, #DD2476);
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(221, 36, 118, 0.3);
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container pt-5">
        <?php if ($showQuiz): ?>
        <!-- Quiz Header -->
        <div class="quiz-header mb-4 text-center">
            <h2 class="display-4 mb-3 text-white">THI ĐẤU</h2>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="fas fa-tasks fa-2x mr-3"></i>
                        <div class="text-left">
                            <h5 class="mb-0 text-white">Tổng số câu hỏi</h5>
                            <p class="mb-0 h4 text-white">40 câu</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="fas fa-clock fa-2x mr-3"></i>
                        <div class="text-left">
                            <h5 class="mb-0 text-white">Thời gian</h5>
                            <p class="mb-0 h4 text-white">45:00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quiz Form -->
        <form method="POST" action="">
            <div class="row">
                <div class="col-lg-9">
                    <!-- Questions Section -->
                    <?php foreach ($cauHoiList as $index => $cauHoi): ?>
                    <div class="card question-card mb-4 shadow-sm">
                        <div class="card-header text-white d-flex align-items-center">
                            <span class="badge badge-light mr-2 p-2">Câu <?= ($index + 1) ?></span>
                            <h5 class="mb-0 flex-grow-1 text-white"><?= htmlspecialchars($cauHoi['NoiDung']); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <?php
                                        $options = [
                                            'A' => $cauHoi['DapAnA'],
                                            'B' => $cauHoi['DapAnB'],
                                            'C' => $cauHoi['DapAnC'],
                                            'D' => $cauHoi['DapAnD']
                                        ];
                                        foreach ($options as $key => $value):
                                        ?>
                                <label class="list-group-item list-group-item-action answer-option d-flex align-items-center">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="q<?= $cauHoi['MaCauHoi'] ?>_<?= $key ?>" name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" value="<?= $key ?>" class="custom-control-input">
                                        <span class="custom-control-label"><?= $key ?>. <?= htmlspecialchars($value); ?></span>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-lg btn-block mb-5">
                        <i class="fas fa-paper-plane mr-2"></i>Nộp Bài
                    </button>

                </div>

                <!-- Sidebar Progress -->
                <div class="col-lg-3">
                    <div class="card shadow-sm position-sticky" style="top: 20px;">
                        <div class="card-header text-white">
                            <h5 class="mb-0 text-white"><i class="fas fa-list-ol mr-2 text-white"></i>Tiến Độ</h5>
                        </div>
                        <div class="card-body">
                            <div class="progress mb-3" style="height: 20px;">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;">0%</div>
                            </div>
                            <div class="d-flex justify-content-between text-muted">
                                <small>Đã trả lời: <span id="answeredCount">0</span>/40</small>
                                <small>Còn lại: <span id="remainingCount">40</span></small>
                            </div>
                            <div class="d-flex justify-content-between text-muted">
                                <small>Thời gian còn lại: <span id="countdown" class="text-danger"></span></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <?php else: ?>
        <!-- Result Section -->
        <div class="card result-card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <div class="score-circle mb-3">
                        <?= $diem; ?>/40
                    </div>
                </div>

                <h3 class="mb-4">Kết Quả Của Bạn</h3>

                <div class="row justify-content-center mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Điểm số</h5>
                                <p class="card-text"><?= $diem; ?>/40</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Thời gian thi</h5>
                                <p class="card-text"><?= gmdate("i:s", $thoiGianThi); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="index.php" class="btn btn-primary">Trở về trang chủ</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Timer countdown functionality (optional)
        const countdownElement = document.getElementById('countdown');
        const totalTime = 45 * 60; // Total time in seconds (45 minutes)
        // Lấy các phần tử cần thiết
        const answeredCountElement = document.getElementById('answeredCount');
        const remainingCountElement = document.getElementById('remainingCount');
        const progressBarElement = document.getElementById('progressBar');
        const totalQuestions = 40; // Tổng số câu hỏi

        let answeredCount = 0; // Số câu trả lời đã chọn
        let questionAnswered = new Array(totalQuestions).fill(false); // Mảng theo dõi trạng thái câu trả lời

        // Hàm cập nhật tiến độ
        function updateProgress() {
            const remainingCount = totalQuestions - answeredCount;
            answeredCountElement.innerText = answeredCount;
            remainingCountElement.innerText = remainingCount;
            const progress = (answeredCount / totalQuestions) * 100;
            progressBarElement.style.width = `${progress}%`;
            progressBarElement.innerText = `${Math.round(progress)}%`;
        }

        // Xử lý khi người dùng chọn câu trả lời
        document.querySelectorAll('input[type="radio"]').forEach(input => {
            input.addEventListener('change', function () {
                const questionId = this.name.split('[')[1].split(']')[0]; // Lấy ID câu hỏi từ name attribute
                const questionIndex = parseInt(questionId) - 1; // Chuyển ID sang chỉ số mảng

                // Kiểm tra trạng thái của câu hỏi này
                if (!questionAnswered[questionIndex]) {
                    answeredCount++; // Tăng số câu trả lời đã chọn
                    questionAnswered[questionIndex] = true; // Đánh dấu câu hỏi này là đã trả lời
                }

                // Cập nhật lại tiến độ
                updateProgress();
            });
        });

        // Ban đầu cập nhật tiến độ khi trang được tải
        updateProgress();

        let timeRemaining = totalTime;

        function updateCountdown() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            countdownElement.innerText = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
            timeRemaining--;
            if (timeRemaining < 0) {
                clearInterval(countdownInterval);
                document.querySelector('form').submit(); // Auto-submit when time is up
            }
        }

        const countdownInterval = setInterval(updateCountdown, 1000);
    </script>

    <?php include '../includes/scripts.php'; ?>
</body>
</html>
