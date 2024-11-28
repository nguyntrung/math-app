<?php
session_start();

// Kiểm tra nếu không có kết quả từ phiên làm việc
if (!isset($_SESSION['ketQua'])) {
    echo "Không có kết quả bài làm!";
    exit();
}

// Lấy kết quả từ session
$ketQua = $_SESSION['ketQua'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả bài kiểm tra</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Kết quả bài kiểm tra</h1>
        <div class="result-container">
            <?php foreach ($ketQua as $index => $result): ?>
                <div class="result-item">
                    <h3>Câu <?= $index + 1 ?>:</h3>
                    <p><strong>Đáp án đã chọn:</strong> <?= htmlspecialchars($result['dapAnChon']); ?></p>
                    <p><strong>Kết quả:</strong> <?= $result['isCorrect'] ? 'Đúng' : 'Sai'; ?></p>
                    <p><strong>Giải thích:</strong> <?= htmlspecialchars($result['GiaiThich']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
<style>
    .result-container {
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.result-item {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.result-item h3 {
    margin-bottom: 10px;
}

.result-item p {
    margin: 5px 0;
}

.result-item strong {
    font-weight: bold;
}

.result-item .correct {
    color: green;
}

.result-item .incorrect {
    color: red;
}

</style>