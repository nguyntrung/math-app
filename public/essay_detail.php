<?php
session_start();

// Kiểm tra nếu chưa đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Kiểm tra nếu có bài học cụ thể được yêu cầu
if (!isset($_GET['maBaiHoc'])) {
    echo "Bài học không tồn tại!";
    exit();
}

$maBaiHoc = $_GET['maBaiHoc'];

// Lấy câu hỏi tự luận của bài học từ cơ sở dữ liệu
$stmt = $conn->prepare("SELECT * FROM CauHoiTuLuan WHERE MaBaiHoc = :maBaiHoc");
$stmt->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
$stmt->execute();
$cauHoiTuLuan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý khi người dùng nộp bài
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitEssay'])) {
    $ketQua = [];
    
    foreach ($cauHoiTuLuan as $index => $cauHoi) {
        $dapAnChon = $_POST['answer_' . $cauHoi['MaCauHoi']] ?? '';
        $isCorrect = strtolower(trim($dapAnChon)) == strtolower(trim($cauHoi['LoiGiai']));
        
        $ketQua[] = [
            'cauHoi' => $cauHoi,
            'dapAnChon' => $dapAnChon,
            'isCorrect' => $isCorrect,
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài Tập Tự Luận</title>

    <?php include '../includes/styles.php'; ?>
    <style>
        .essay-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 20px auto;
            max-width: 1130px;
        }

        .question-card {
            display: flex;
            align-items: center;
            background-color: #e6f2ff;
            border-radius: 10px;
            margin-bottom: 15px;
            padding: 10px;
        }

        .question-text {
            flex-grow: 1;
            margin-right: 10px;
            font-size: 16px;
        }

        .form-control {
            width: 200px;
            padding: 5px;
            border-radius: 5px;
            border: 2px solid #4CAF50;
            font-size: 14px;
        }        
        
        .form-control:focus {
            border-color: #2196F3;
            box-shadow: 0 0 10px rgba(33, 150, 243, 0.3);
        }

        .result-card {
            border-radius: 10px;
            margin-bottom: 15px;
            padding: 15px;
        }

        .result-card-correct {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .result-card-incorrect {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .btn-submit {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container essay-container">
        <h1 class="text-center mb-4" style="color: #2196F3;">Bài Tập Vui Vẻ</h1>
        
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitEssay'])): ?>
            <!-- Hiển thị kết quả sau khi nộp bài -->
            <h3 class="text-center">Kết Quả Của Bạn</h3>
            <div class="mt-3">
                <?php foreach ($ketQua as $index => $result): ?>
                    <div class="result-card <?= $result['isCorrect'] ? 'result-card-correct' : 'result-card-incorrect'; ?>">
                        <p><strong>Câu <?= $index + 1; ?>:</strong> <?= htmlspecialchars($result['cauHoi']['NoiDung']); ?></p>
                        <p><strong>Câu trả lời của bạn:</strong> <?= htmlspecialchars($result['dapAnChon']); ?></p>
                        <p><strong>Đáp án đúng:</strong> <?= htmlspecialchars($result['cauHoi']['LoiGiai']); ?></p>
                        <p><strong>Kết quả:</strong> <?= $result['isCorrect'] ? '✅ Đúng rồi!' : '❌ Chưa đúng'; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="essay_detail.php?maBaiHoc=<?= $maBaiHoc; ?>" class="btn-submit">Làm lại</a>
            </div>
        <?php else: ?>
            <!-- Hiển thị câu hỏi và nhập câu trả lời -->
            <form action="essay_detail.php?maBaiHoc=<?= $maBaiHoc; ?>" method="POST">
                <?php foreach ($cauHoiTuLuan as $index => $cauHoi): ?>
                    <div class="question-card">
                        <div class="question-text">
                            Câu <?= $index + 1; ?>: <?= htmlspecialchars($cauHoi['NoiDung']); ?>
                        </div>
                        <input 
                            type="text" 
                            name="answer_<?= $cauHoi['MaCauHoi']; ?>" 
                            class="form-control" 
                            placeholder="Trả lời tại đây" 
                            required
                        >
                    </div>
                <?php endforeach; ?>
                
                <div class="text-center">
                    <button type="submit" name="submitEssay" class="btn-submit">Nộp bài</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>