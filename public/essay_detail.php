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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bài Tập Tự Luận</title>

    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Main Start -->
    <div class="container pt-5">
        <div class="container pb-5">
            <h1 class="text-center mb-4">Bài Tập Tự Luận</h1>
            
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitEssay'])): ?>
                <!-- Hiển thị kết quả sau khi nộp bài -->
                <h3 class="text-center">Kết Quả</h3>
                <div class="mt-3">
                    <?php foreach ($ketQua as $index => $result): ?>
                        <div class="card mb-2">
                            <div class="card-header" style="background-color: <?= $result['isCorrect'] ? '#d4edda' : '#f8d7da'; ?>">
                                Câu <?= $index + 1; ?>:
                            </div>
                            <div class="card-body">
                                <p><strong>Câu trả lời của bạn:</strong> <?= htmlspecialchars($result['dapAnChon']); ?></p>
                                <p><strong>Đáp án đúng:</strong> <?= htmlspecialchars($result['cauHoi']['LoiGiai']); ?></p>
                                <p><strong>Kết quả:</strong> <?= $result['isCorrect'] ? 'Đúng' : 'Sai'; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center">
                    <a href="essay_detail.php?maBaiHoc=<?= $maBaiHoc; ?>" class="btn btn-primary">Làm lại bài tập</a>
                </div>
            <?php else: ?>
                <!-- Hiển thị câu hỏi và nhập câu trả lời -->
                <form action="essay_detail.php?maBaiHoc=<?= $maBaiHoc; ?>" method="POST">
                    <?php foreach ($cauHoiTuLuan as $index => $cauHoi): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <p class="mb-0">Câu <?= $index + 1; ?>: <?= htmlspecialchars($cauHoi['NoiDung']); ?></p>
                            </div>
                            <div class="card-body">
                                <input name="answer_<?= $cauHoi['MaCauHoi']; ?>" rows="4" class="form-control" placeholder="Nhập kết quả" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center">
                        <button type="submit" name="submitEssay" class="btn btn-success">Nộp bài</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <!-- Main End -->

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
</body>
</html>
