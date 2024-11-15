<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['maBaiHoc'])) {
    header('Location: index.php');
    exit();
}

$maBaiHoc = $_GET['maBaiHoc'];

// Lấy danh sách câu hỏi trắc nghiệm và tự luận
try {
    // Lấy câu hỏi trắc nghiệm
    $stmtTN = $conn->prepare("
        SELECT MaCauHoi, NoiDung, DapAnA, DapAnB, DapAnC, DapAnD, DapAnDung, GiaiThich, 'TN' as LoaiCauHoi 
        FROM cauhoitracnghiem 
        WHERE MaBaiHoc = :maBaiHoc
    ");
    $stmtTN->bindParam(':maBaiHoc', $maBaiHoc);
    $stmtTN->execute();
    $cauHoiTN = $stmtTN->fetchAll(PDO::FETCH_ASSOC);

    // Lấy câu hỏi tự luận
    $stmtTL = $conn->prepare("
        SELECT MaCauHoi, NoiDung, LoiGiai, GiaiThich, 'TL' as LoaiCauHoi 
        FROM cauhoituluan 
        WHERE MaBaiHoc = :maBaiHoc
    ");
    $stmtTL->bindParam(':maBaiHoc', $maBaiHoc);
    $stmtTL->execute();
    $cauHoiTL = $stmtTL->fetchAll(PDO::FETCH_ASSOC);

    // Gộp hai loại câu hỏi
    $tatCaCauHoi = array_merge($cauHoiTN, $cauHoiTL);
    shuffle($tatCaCauHoi); // Xáo trộn câu hỏi

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
        .quiz-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .timer {
            font-size: 1.2em;
            font-weight: bold;
            color: #666;
        }

        .progress-bar {
            height: 10px;
            background: #f0f0f0;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .progress {
            height: 100%;
            background: #4CAF50;
            border-radius: 5px;
            transition: width 0.3s ease;
        }

        .question-content {
            margin-bottom: 20px;
        }

        .options-container {
            display: grid;
            gap: 10px;
        }

        .option {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option:hover {
            background: #f5f5f5;
        }

        .option.selected {
            border-color: #4CAF50;
            background: #e8f5e9;
        }

        .essay-answer {
            width: 100%;
            min-height: 150px;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background: #45a049;
        }

        .feedback {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }

        .feedback.correct {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .feedback.incorrect {
            background: #ffebee;
            color: #c62828;
        }

        .save-status {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            background: #4CAF50;
            color: white;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <div id="saveStatus" class="save-status">Đã lưu tiến độ</div>
        <div class="quiz-container">
            <div class="question-header">
                <div class="progress-info">
                    Câu hỏi <span id="currentQuestion">1</span>/<span id="totalQuestions"><?= count($tatCaCauHoi) ?></span>
                </div>
                <div class="timer" id="timer">00:00:00</div>
            </div>

            <div class="progress-bar">
                <div class="progress" id="progressBar"></div>
            </div>

            <div id="questionContainer">
                <!-- Câu hỏi sẽ được load động bằng JavaScript -->
            </div>

            <button class="submit-btn" id="submitBtn">Trả lời</button>
            
            <div class="feedback" id="feedback"></div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>

    <script>
        // Khởi tạo các biến
        const maBaiHoc = <?= $maBaiHoc ?>;
        const questions = <?= json_encode($tatCaCauHoi) ?>;
        let currentQuestionIndex = 0;
        let startTime = new Date();
        let correctAnswers = 0;
        let wrongAnswers = 0;
        let answeredQuestions = new Set();
        let totalTimeSpent = 0;
        let lastSaveTime = new Date();
        let isSubmitting = false;

        // Hiển thị trạng thái lưu
        function showSaveStatus(message) {
            const status = document.getElementById('saveStatus');
            status.textContent = message;
            status.style.display = 'block';
            setTimeout(() => {
                status.style.display = 'none';
            }, 2000);
        }

        // Lưu tiến độ
        async function saveProgress() {
            if (isSubmitting) return;

            const currentTime = new Date();
            totalTimeSpent += Math.floor((currentTime - lastSaveTime) / 1000);
            lastSaveTime = currentTime;

            const progress = {
                maBaiHoc: maBaiHoc,
                cauHoiDaLam: JSON.stringify([...answeredQuestions]),
                diemSo: correctAnswers / questions.length,
                thoiGianLamBai: totalTimeSpent
            };

            try {
                const response = await fetch('save_progressq.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(progress)
                });

                if (response.ok) {
                    showSaveStatus('Đã lưu tiến độ');
                }
            } catch (error) {
                console.error('Lỗi khi lưu tiến độ:', error);
                showSaveStatus('Lỗi khi lưu tiến độ');
            }
        }

        // Load tiến độ
        async function loadProgress() {
            try {
                const response = await fetch(`load_progress.php?maBaiHoc=${maBaiHoc}`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Khôi phục tiến độ
                    answeredQuestions = new Set(JSON.parse(data.data.CauHoiDaLam));
                    totalTimeSpent = parseInt(data.data.ThoiGianLamBai);
                    correctAnswers = Math.round(data.data.DiemSo * questions.length);
                    
                    // Tìm câu hỏi tiếp theo chưa làm
                    currentQuestionIndex = 0;
                    while (currentQuestionIndex < questions.length && 
                           answeredQuestions.has(questions[currentQuestionIndex].MaCauHoi)) {
                        currentQuestionIndex++;
                    }
                    
                    // Cập nhật timer
                    startTime = new Date(new Date() - totalTimeSpent * 1000);
                    
                    // Hiển thị câu hỏi tiếp theo
                    if (currentQuestionIndex < questions.length) {
                        displayQuestion();
                    } else {
                        showResults();
                    }
                }
            } catch (error) {
                console.error('Lỗi khi tải tiến độ:', error);
            }
        }

        // Hiển thị câu hỏi
        function displayQuestion() {
            const question = questions[currentQuestionIndex];
            const questionContainer = document.getElementById('questionContainer');
            questionContainer.innerHTML = ''; // Xóa câu hỏi cũ

            let questionHTML = `<div class="question-content">${question.NoiDung}</div>`;
            if (question.LoaiCauHoi === 'TN') {
                questionHTML += `
                    <div class="options-container">
                        <div class="option" data-answer="A">${question.DapAnA}</div>
                        <div class="option" data-answer="B">${question.DapAnB}</div>
                        <div class="option" data-answer="C">${question.DapAnC}</div>
                        <div class="option" data-answer="D">${question.DapAnD}</div>
                    </div>
                `;
            } else {
                questionHTML += `
                    <textarea class="essay-answer" id="essayAnswer" placeholder="Nhập câu trả lời của bạn"></textarea>
                `;
            }

            questionContainer.innerHTML = questionHTML;

            // Cập nhật chỉ số câu hỏi
            document.getElementById('currentQuestion').textContent = currentQuestionIndex + 1;

            // Cập nhật tiến độ
            document.getElementById('progressBar').style.width = ((currentQuestionIndex + 1) / questions.length * 100) + '%';

            // Lắng nghe sự kiện chọn đáp án
            if (question.LoaiCauHoi === 'TN') {
                const options = document.querySelectorAll('.option');
                options.forEach(option => {
                    option.addEventListener('click', () => {
                        option.classList.toggle('selected');
                    });
                });
            }
        }

        // Xử lý khi nhấn submit
        document.getElementById('submitBtn').addEventListener('click', () => {
            if (isSubmitting) return;

            isSubmitting = true;

            const question = questions[currentQuestionIndex];
            let isCorrect = false;
            
            if (question.LoaiCauHoi === 'TN') {
                // Kiểm tra câu trả lời trắc nghiệm
                const selectedOption = document.querySelector('.option.selected');
                if (selectedOption) {
                    isCorrect = selectedOption.getAttribute('data-answer') === question.DapAnDung;
                }
            } else {
                // Kiểm tra câu trả lời tự luận
                const essayAnswer = document.getElementById('essayAnswer').value.trim();
                if (essayAnswer) {
                    isCorrect = essayAnswer.toLowerCase() === question.LoiGiai.toLowerCase();
                }
            }

            if (isCorrect) {
                correctAnswers++;
                document.getElementById('feedback').classList.add('correct');
                document.getElementById('feedback').textContent = 'Đúng! Câu trả lời của bạn chính xác.';
            } else {
                wrongAnswers++;
                document.getElementById('feedback').classList.add('incorrect');
                document.getElementById('feedback').textContent = 'Sai! Câu trả lời của bạn chưa chính xác.';
            }
            document.getElementById('feedback').style.display = 'block';

            answeredQuestions.add(question.MaCauHoi);

            // Cập nhật câu hỏi tiếp theo hoặc kết thúc
            currentQuestionIndex++;
            if (currentQuestionIndex < questions.length) {
                setTimeout(() => {
                    document.getElementById('feedback').style.display = 'none';
                    displayQuestion();
                    isSubmitting = false;
                }, 2000);
            } else {
                setTimeout(() => {
                    showResults();
                }, 2000);
            }
        });

        // Hiển thị kết quả
        function showResults() {
            alert(`Kết thúc! Bạn đã trả lời đúng ${correctAnswers} câu, sai ${wrongAnswers} câu.`);
        }

        // Bắt đầu kiểm tra tiến độ và tải dữ liệu
        loadProgress();

        // Cập nhật thời gian mỗi giây
        setInterval(() => {
            const elapsedTime = Math.floor((new Date() - startTime) / 1000);
            const hours = String(Math.floor(elapsedTime / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((elapsedTime % 3600) / 60)).padStart(2, '0');
            const seconds = String(elapsedTime % 60).padStart(2, '0');
            document.getElementById('timer').textContent = `${hours}:${minutes}:${seconds}`;
        }, 1000);

        // Lưu tiến độ định kỳ
        setInterval(saveProgress, 60000); // Lưu mỗi phút
    </script>
</body>
</html>
