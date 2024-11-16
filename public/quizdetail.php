<?php
session_start();
include '../database/db.php';

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

$maBaiHoc = isset($_GET['maBaiHoc']) ? $_GET['maBaiHoc'] : null;
$maNguoiDung = $_SESSION['MaNguoiDung'];

if (!$maBaiHoc) {
    header('Location: index.php');
    exit();
}

try {
    // Lấy tất cả câu hỏi từ hai bảng (Câu hỏi trắc nghiệm và tự luận)
    $stmt = $conn->prepare("
        (SELECT 'MC' as type, MaCauHoi, NoiDung, DapAnA, DapAnB, DapAnC, DapAnD, DapAnDung, NULL as LoiGiai
        FROM cauhoitracnghiem 
        WHERE MaBaiHoc = :maBaiHoc)
        UNION ALL
        (SELECT 'SA' as type, MaCauHoi, NoiDung, NULL, NULL, NULL, NULL, NULL, LoiGiai
        FROM cauhoituluan 
        WHERE MaBaiHoc = :maBaiHoc)
        ORDER BY RAND()
    ");
    $stmt->bindParam(':maBaiHoc', $maBaiHoc);
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy tên bài học từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT TenBai FROM baihoc WHERE MaBaiHoc = :maBaiHoc");
    $stmt->bindParam(':maBaiHoc', $maBaiHoc);
    $stmt->execute();
    $baiHoc = $stmt->fetch(PDO::FETCH_ASSOC);
    $tenBaiHoc = $baiHoc['TenBai'];

    // Lấy tiến độ làm bài của người dùng
    $stmt = $conn->prepare("
        SELECT MaCauHoi, CauTraLoi, ThoiGianLam
        FROM tiendoquiz
        WHERE MaNguoiDung = :maNguoiDung 
        AND MaBaiHoc = :maBaiHoc
    ");
    $stmt->bindParam(':maNguoiDung', $maNguoiDung);
    $stmt->bindParam(':maBaiHoc', $maBaiHoc);
    $stmt->execute();
    $progress = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Chuyển đổi tiến độ thành mảng với key là MaCauHoi
    $progressMap = [];
    foreach ($progress as $p) {
        $progressMap[$p['MaCauHoi']] = [
            'answer' => $p['CauTraLoi'],
            'time' => $p['ThoiGianLam']
        ];
    }

    // Lọc các câu hỏi chưa trả lời
    $remainingQuestions = array_filter($questions, function($question) use ($progressMap) {
        return !isset($progressMap[$question['MaCauHoi']]);
    });

    // Kiểm tra nếu đã trả lời hết tất cả câu hỏi
    $completed = count($remainingQuestions) == 0;

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
    <title>Bài tập</title>
    <?php include '../includes/styles.php'; ?>
    <style>
        body {
            background-color: #f0f0e6;
        }

        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .question-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header-container {
            background: #fff;
            border-radius: 15px;
            padding: 12px 20px;
            margin: 15px auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            max-width: 1200px;
        }

        /* Tiêu đề bài học với icon ngôi sao */
        .header-item:first-child {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            flex: 2;
        }

        .header-item:first-child::before {
            content: '';
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #FF6B6B 0%, #FFE66D 100%);
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'/%3E%3C/svg%3E") no-repeat center;
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'/%3E%3C/svg%3E") no-repeat center;
        }

        /* Đồng hồ đếm ngược */
        .timer {
            background: #fff;
            border: 2px dashed #eee;
            border-radius: 25px;
            padding: 8px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #666;
        }

        .timer i {
            color: #FF6B6B;
        }

        /* Container điểm số và tiến độ */
        .progress-container {
            flex: 3;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Thanh tiến độ */
        .progress-bar {
            flex-grow: 1;
            background: #f5f5f5;
            height: 8px;
            border-radius: 10px;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: #4CAF50;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                padding: 15px;
                gap: 15px;
            }
            
            .progress-container {
                width: 100%;
                flex-direction: column;
            }
            
            .progress-bar {
                width: 100%;
            }
        }

        .option-button {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            text-align: left;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        .option-button:hover {
            background: #f5f5f5;
        }
        .option-button.selected {
            background: #e3f2fd;
            border-color: #2196F3;
        }
        .progress-bar {
            height: 20px;
            background: #f0f0f0;
            border-radius: 5px;
            margin: 20px 0;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #4CAF50;
            transition: width 0.3s;
        }
        #submitAnswer {
            width: 50%;
            max-width: 200px;
            margin: 10px auto;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="quiz-container">
            <?php if ($completed): ?>
                <div class="alert alert-success" role="alert">
                    Bạn đã hoàn thành tất cả câu hỏi!
                </div>
            <?php else: ?>
                <div class="header-container">
                    <p class="header-item m-0"><?php echo htmlspecialchars($tenBaiHoc); ?></p>
                    <div class="timer header-item">
                        <i class="fa-regular fa-clock"></i>
                        <span id="timer">00:00:00</span>
                    </div>
                    <div class="progress-bar header-item">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                </div>
                <div class="question-card">
                    <div id="questionContainer">
                        <!-- Question content will be loaded here -->
                    </div>
                    <button id="submitAnswer" class="btn btn-success w-100">Trả lời</button>
                </div>
                <div id="questionStatus" class="text-center mt-3">
                    <!-- Show progress: X out of Y questions -->
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/scripts.php'; ?>
    <script>
        const allQuestions = <?php echo json_encode($questions); ?>;
        const remainingQuestions = <?php echo json_encode(array_values($remainingQuestions)); ?>;
        const progress = <?php echo json_encode($progressMap); ?>;
        let currentQuestionIndex = 0;
        let startTime = new Date();
        let answeredCount = Object.keys(progress).length;
        let totalQuestions = allQuestions.length;

        // Cập nhật lại hàm updateProgress để hiển thị chi tiết hơn
        function updateProgress() {
            const progressPercent = (answeredCount / totalQuestions) * 100;
            document.getElementById('progressFill').style.width = `${progressPercent}%`;
            document.getElementById('questionStatus').textContent = 
                `Đã làm ${answeredCount} / ${totalQuestions} câu (Còn ${totalQuestions - answeredCount} câu)`;
        }

        // Cập nhật đồng hồ đếm giờ
        function updateTimer() {
            const now = new Date();
            const diff = Math.floor((now - startTime) / 1000);
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;
            document.getElementById('timer').textContent = 
                `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        // Hiển thị câu hỏi
        function displayQuestion() {
            const question = remainingQuestions[currentQuestionIndex];
            const container = document.getElementById('questionContainer');
            
            let html = `<p>${question.NoiDung}</p>`;
            
            if (question.type === 'MC') {
                html += `
                    <button class="option-button" data-answer="A">${question.DapAnA}</button>
                    <button class="option-button" data-answer="B">${question.DapAnB}</button>
                    <button class="option-button" data-answer="C">${question.DapAnC}</button>
                    <button class="option-button" data-answer="D">${question.DapAnD}</button>
                `;
            } else {
                html += `
                    <input class="form-control" type="text" placeholder="Nhập câu trả lời của bạn" id="textAnswer">
                `;
            }
            
            container.innerHTML = html;

            // Add click handlers for multiple choice
            if (question.type === 'MC') {
                document.querySelectorAll('.option-button').forEach(button => {
                    button.addEventListener('click', function() {
                        document.querySelectorAll('.option-button').forEach(b => b.classList.remove('selected'));
                        this.classList.add('selected');
                    });
                });
            }
        }

        // Lưu câu trả lời và tiến độ
        async function submitAnswer() {
            const question = remainingQuestions[currentQuestionIndex];
            let answer = '';
            
            if (question.type === 'MC') {
                const selected = document.querySelector('.option-button.selected');
                if (!selected) {
                    alert('Vui lòng chọn một đáp án');
                    return;
                }
                answer = selected.dataset.answer;
            } else {
                answer = document.getElementById('textAnswer').value.trim();
                if (!answer) {
                    alert('Vui lòng nhập câu trả lời');
                    return;
                }
            }

            const now = new Date();
            const timeSpent = (now - startTime) / 60000;

            try {
                const response = await fetch('save_progresss.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        maBaiHoc: <?php echo $maBaiHoc; ?>,
                        maCauHoi: question.MaCauHoi,
                        cauTraLoi: answer,
                        thoiGianLam: timeSpent
                    })
                });

                if (response.ok) {
                    progress[question.MaCauHoi] = {
                        answer: answer,
                        time: timeSpent
                    };
                    answeredCount++;
                    updateProgress();

                    currentQuestionIndex++;
                    
                    if (currentQuestionIndex < remainingQuestions.length) {
                        startTime = new Date();
                        displayQuestion();
                    } else {
                        if (answeredCount < totalQuestions) {
                            alert('Bạn đã hoàn thành ' + answeredCount + '/' + totalQuestions + ' câu hỏi');
                            window.location.reload();
                        } else {
                            alert('Bạn đã hoàn thành tất cả câu hỏi!');
                            window.location.href = 'index.php';
                        }
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi lưu câu trả lời');
            }
        }

        // Khởi tạo chỉ hiển thị câu hỏi nếu chưa hoàn thành
        if (!<?php echo $completed ? 'true' : 'false'; ?>) {
            displayQuestion();
            updateProgress();
            setInterval(updateTimer, 1000);
            document.getElementById('submitAnswer').addEventListener('click', submitAnswer);
        }
    </script>
</body>
</html>
