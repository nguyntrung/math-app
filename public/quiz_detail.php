<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

if (!isset($_GET['maBaiHoc'])) {
    echo "Không tìm thấy bài học này, con hãy quay lại nhé!";
    exit();
}

$maBaiHoc = $_GET['maBaiHoc'];

$stmt = $conn->prepare("SELECT * FROM CauHoiTracNghiem WHERE MaBaiHoc = :maBaiHoc");
$stmt->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
$stmt->execute();
$cauHoiTracNghiem = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitQuiz'])) {
    $diem = 0;
    $soCauDung = 0;
    $tongSoCau = count($cauHoiTracNghiem);
    $ketQua = [];
    
    foreach ($cauHoiTracNghiem as $index => $cauHoi) {
        $dapAnChon = $_POST['answer_' . $cauHoi['MaCauHoi']] ?? null;
        $isCorrect = $dapAnChon == $cauHoi['DapAnDung'];
        $ketQua[] = [
            'cauHoi' => $cauHoi,
            'dapAnChon' => $dapAnChon,
            'isCorrect' => $isCorrect,
        ];
        if ($isCorrect) {
            $soCauDung++;
        }
    }
    
    $diem = ($soCauDung / $tongSoCau) * 10;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài tập vui - Học mà chơi, chơi mà học</title>
    <?php include '../includes/styles.php'; ?>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #4895ef;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4CAF50;
            --warning-color: #ff9f1c;
            --error-color: #e71d36;
            --background-color: #f8f9fa;
            --text-color: #2b2d42;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-size: 16px;
            color: var(--text-color);
        }

        .quiz-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            padding: 25px;
            margin: 20px auto;
            max-width: 800px;
        }

        .question-card {
            border: none;
            border-radius: 15px;
            margin-bottom: 25px;
            background-color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .question-header {
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0;
            font-size: 18px;
            font-weight: 500;
        }

        /* Thay đổi style để hiển thị 4 đáp án trên 1 dòng */
        .answer-options-row {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .answer-option {
            flex: 1;
            position: relative;
            padding: 12px;
            margin: 5px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            min-width: 100px;
        }

        .answer-option:hover {
            border-color: var(--primary-light);
            background-color: #f8f9fa;
            transform: scale(1.02);
        }

        /* Ẩn input radio mặc định */
        .answer-option input[type="radio"] {
            display: none;
        }

        /* Style cho label khi được chọn */
        .answer-option.selected {
            border-color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.1);
            font-weight: bold;
        }

        /* Responsive cho màn hình nhỏ */
        @media (max-width: 576px) {
            .answer-options-row {
                flex-direction: column;
            }

            .answer-option {
                margin: 5px 0;
                width: 100%;
            }
        }

        .answer-option.selected::before {
            position: absolute;
            left: 10px;
            color: var(--primary-color);
            font-weight: bold;
        }

        .submit-btn {
            background: linear-gradient(45deg, var(--warning-color), #f3722c);
            border: none;
            padding: 15px 40px;
            font-size: 20px;
            border-radius: 30px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 159, 28, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 159, 28, 0.4);
        }

        .retry-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .retry-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .result-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-top: 20px;
        }

        .result-emoji {
            font-size: 64px;
            margin-bottom: 20px;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Thêm animation cho đáp án đúng/sai */
        .alert-success, .text-danger {
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container pt-4">
        <div class="quiz-container">
            <div class="text-center mb-4">
                <h1 style="color: #4CAF50; font-size: 2em;">🌟 Cùng làm bài tập nào! 🌟</h1>
            </div>
            
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitQuiz'])): ?>
                <!-- Phần kết quả giữ nguyên như ở phiên bản trước -->
                <div class="result-container">
                    <div class="result-emoji text-center">
                        <?php echo $diem >= 5 ? '🎉' : '💪'; ?>
                    </div>
                    <h3 class="text-center">
                        <?php 
                        if ($diem == 10) {
                            echo "Tuyệt vời! Bạn đã trả lời đúng tất cả!";
                        } elseif ($diem >= 8) {
                            echo "Giỏi lắm! Bạn đã làm rất tốt!";
                        } elseif ($diem >= 5) {
                            echo "Cố gắng lên! Bạn đã làm được nhiều câu đúng rồi!";
                        } else {
                            echo "Đừng buồn nhé! Hãy thử lại lần nữa!";
                        }
                        ?>
                    </h3>
                    <p class="fs-5 text-center">Con đã trả lời đúng <?= $soCauDung; ?> trong số <?= $tongSoCau; ?> câu</p>

                    <div class="mt-4">
                        <h4 class="text-center mb-4">✨ Cùng xem đáp án đúng nhé! ✨</h4>
                        <?php foreach ($ketQua as $index => $result): ?>
                            <div class="question-card">
                                <div class="question-header">
                                    Câu <?= $index + 1; ?>: <?= htmlspecialchars($result['cauHoi']['NoiDung']); ?>
                                </div>
                                <div class="card-body">
                                    <?php if ($result['isCorrect']): ?>
                                        <div class="text-success">
                                            <strong>🌟 Tuyệt vời!</strong> Bạn đã trả lời đúng!
                                        </div>
                                    <?php else: ?>
                                        <div class="text-danger p-0">
                                            <strong>💡 Ghi nhớ:</strong> Đáp án đúng là: 
                                            <?= htmlspecialchars($result['cauHoi']['DapAn' . $result['cauHoi']['DapAnDung']]); ?>
                                        </div>
                                    <?php endif; ?>
                                    <p class="mt-3">
                                        <strong>💭 Giải thích:</strong> 
                                        <?= htmlspecialchars($result['cauHoi']['GiaiThich']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="d-flex justify-content-center mb-4">
                            <a href="quiz_detail.php?maBaiHoc=<?= $maBaiHoc; ?>" class="btn btn-primary text-decoration-none">
                                🔄 Làm lại bài tập
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <form action="quiz_detail.php?maBaiHoc=<?= $maBaiHoc; ?>" method="POST">
                    <?php foreach ($cauHoiTracNghiem as $index => $cauHoi): ?>
                        <div class="question-card">
                            <div class="question-header">
                                Câu <?= $index + 1; ?>: <?= htmlspecialchars($cauHoi['NoiDung']); ?>
                            </div>
                            <div class="card-body">
                                <div class="answer-options-row">
                                    <?php
                                    $dapAn = ['A', 'B', 'C', 'D'];
                                    foreach ($dapAn as $option):
                                    ?>
                                        <label class="answer-option" onclick="selectAnswer(this)">
                                            <input type="radio" 
                                                   name="answer_<?= $cauHoi['MaCauHoi']; ?>" 
                                                   value="<?= $option ?>">
                                            <?= htmlspecialchars($cauHoi['DapAn' . $option]); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mb-4">
                        <button type="submit" name="submitQuiz" class="submit-btn">
                            ✨ Nộp bài ✨
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>
    <?php include '../includes/scripts.php'; ?>

    <script>
        function selectAnswer(label) {
            // Xóa class selected từ tất cả các options trong cùng một câu hỏi
            const questionCard = label.closest('.card-body');
            const options = questionCard.getElementsByClassName('answer-option');
            Array.from(options).forEach(option => option.classList.remove('selected'));
            
            // Thêm class selected cho option được chọn
            label.classList.add('selected');
            
            // Chọn radio button
            const radio = label.querySelector('input[type="radio"]');
            radio.checked = true;
        }
    </script>
</body>
</html>