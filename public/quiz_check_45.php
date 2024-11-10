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
    } else {
        // Nếu không có dữ liệu câu hỏi, có thể là lỗi trong việc gửi biểu mẫu
        $showQuiz = false;
        $diem = 0; // Hoặc có thể thông báo lỗi
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
            background: linear-gradient(45deg, #FF512F, #DD2476);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
        }

        .progress-container {
            background: rgba(255,255,255,0.1);
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

        .answer-option {
            transition: all 0.3s;
        }

        .answer-option:hover {
            background-color: #f8f9fa;
        }

        .result-card {
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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
                                <div class="card-header bg-primary text-white d-flex align-items-center">
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
                                                    <input type="radio" 
                                                        id="q<?= $cauHoi['MaCauHoi'] ?>_<?= $key ?>" 
                                                        name="cauHoi[<?= $cauHoi['MaCauHoi']; ?>]" 
                                                        value="<?= $key ?>" 
                                                        class="custom-control-input">
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
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0 text-white"><i class="fas fa-list-ol mr-2 text-white"></i>Tiến Độ</h5>
                            </div>
                            <div class="card-body">
                                <div class="progress mb-3" style="height: 20px;">
                                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                        role="progressbar" style="width: 0%;">0%</div>
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
                                    <h5 class="card-title text-success">Câu đúng</h5>
                                    <p class="card-text h2"><?= $diem ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-danger">Câu sai</h5>
                                    <p class="card-text h2"><?= 40 - $diem ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="quiz_check_45.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Trở Về Bài Học
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update progress when radio buttons are clicked
        const radioButtons = document.querySelectorAll('input[type="radio"]');
        const progressBar = document.getElementById('progressBar');
        const answeredCount = document.getElementById('answeredCount');
        const remainingCount = document.getElementById('remainingCount');
        
        function updateProgress() {
            const totalQuestions = 40;
            const answered = new Set(Array.from(document.querySelectorAll('input[type="radio"]:checked')).map(input => 
                input.name)).size;
            const progress = (answered / totalQuestions) * 100;
            
            progressBar.style.width = progress + '%';
            progressBar.textContent = Math.round(progress) + '%';
            answeredCount.textContent = answered;
            remainingCount.textContent = totalQuestions - answered;
        }
        
        radioButtons.forEach(radio => {
            radio.addEventListener('change', updateProgress);
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const countdownDisplay = document.getElementById('countdown'); // Phần tử hiển thị thời gian
        const totalTime = 45 * 60; // 45 phút = 2700 giây
        let remainingTime = totalTime;

        // Đếm ngược thời gian
        const countdown = setInterval(function() {
            // Tính số phút và giây còn lại
            let minutes = Math.floor(remainingTime / 60);
            let seconds = remainingTime % 60;

            // Đảm bảo hiển thị 2 chữ số
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            // Cập nhật hiển thị thời gian còn lại
            countdownDisplay.textContent = `${minutes}:${seconds}`;

            // Giảm số giây còn lại
            remainingTime--;

            // Khi hết thời gian
            if (remainingTime < 0) {
                clearInterval(countdown);
                
                // Sử dụng SweetAlert2 để hiển thị thông báo đẹp
                Swal.fire({
                    title: 'Thời gian đã hết!',
                    text: 'Bài thi sẽ tự động nộp.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    showCloseButton: true,
                    timer: 5000, // Đóng thông báo sau 5 giây (có thể bỏ nếu muốn người dùng nhấn OK)
                    customClass: {
                        popup: 'alert-popup',
                        title: 'alert-title',
                        content: 'alert-content'
                    }
                }).then(() => {
                    document.querySelector('form').submit();  // Tự động nộp bài
                });
            }
        }, 1000); // Cập nhật mỗi giây
    });
    </script>

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script type="text/javascript" async
        src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML">
    </script>
</body>
</html>
