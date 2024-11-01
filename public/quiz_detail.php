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

// Lấy danh sách câu hỏi trắc nghiệm của bài học từ cơ sở dữ liệu
$stmt = $conn->prepare("SELECT * FROM CauHoiTracNghiem WHERE MaBaiHoc = :maBaiHoc");
$stmt->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
$stmt->execute();
$cauHoiTracNghiem = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý khi người dùng nộp bài
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitQuiz'])) {
    $diem = 0;
    $soCauDung = 0;
    $tongSoCau = count($cauHoiTracNghiem);
    $ketQua = []; // Mảng lưu kết quả từng câu hỏi
    
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Làm bài tập trắc nghiệm</title>

    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Main Start -->
    <div class="container pt-5">
        <div class="container pb-5">
            <h1 class="text-center mb-4">Bài tập trắc nghiệm</h1>
            
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitQuiz'])): ?>
                <!-- Hiển thị kết quả sau khi nộp bài -->
                <div class="alert alert-info text-center">
                    <h4>Bạn đã trả lời đúng <?= $soCauDung; ?> / <?= $tongSoCau; ?> câu.</h4>
                    <p>Điểm của bạn: <?= number_format($diem, 2); ?>/10</p>
                </div>
                <div class="text-center">
                    <a href="quiz_detail.php?maBaiHoc=<?= $maBaiHoc; ?>" class="btn btn-primary">Làm lại bài tập</a>
                </div>

                <h3 class="text-center mt-4">Giải thích đáp án</h3>
                <div class="mt-3">
                    <?php foreach ($ketQua as $index => $result): ?>
                        <div class="card mb-2">
                            <div class="card-header" style="background-color: <?= $result['isCorrect'] ? '#d4edda' : '#f8d7da'; ?>">
                                Câu <?= $index + 1; ?>:
                            </div>
                            <div class="card-body">
                                <p><strong>Câu trả lời của bạn:</strong> <?= $result['dapAnChon'] ?? 'Chưa chọn'; ?></p>
                                <p><strong>Đáp án đúng:</strong> <?= $result['cauHoi']['DapAnDung']; ?></p>
                                <p><strong>Giải thích:</strong> <?= htmlspecialchars($result['cauHoi']['GiaiThich']); ?></p>
                                <p><strong>Kết quả:</strong> <?= $result['isCorrect'] ? 'Đúng' : 'Sai'; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Hiển thị câu hỏi và đáp án -->
                <form action="quiz_detail.php?maBaiHoc=<?= $maBaiHoc; ?>" method="POST">
                    <?php foreach ($cauHoiTracNghiem as $index => $cauHoi): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <p class="mb-0">Câu <?= $index + 1; ?>: <?= htmlspecialchars($cauHoi['NoiDung']); ?></p>
                            </div>
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer_<?= $cauHoi['MaCauHoi']; ?>" value="A" id="cau_<?= $cauHoi['MaCauHoi']; ?>_A">
                                    <label class="form-check-label" for="cau_<?= $cauHoi['MaCauHoi']; ?>_A"><?= htmlspecialchars($cauHoi['DapAnA']); ?></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer_<?= $cauHoi['MaCauHoi']; ?>" value="B" id="cau_<?= $cauHoi['MaCauHoi']; ?>_B">
                                    <label class="form-check-label" for="cau_<?= $cauHoi['MaCauHoi']; ?>_B"><?= htmlspecialchars($cauHoi['DapAnB']); ?></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer_<?= $cauHoi['MaCauHoi']; ?>" value="C" id="cau_<?= $cauHoi['MaCauHoi']; ?>_C">
                                    <label class="form-check-label" for="cau_<?= $cauHoi['MaCauHoi']; ?>_C"><?= htmlspecialchars($cauHoi['DapAnC']); ?></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer_<?= $cauHoi['MaCauHoi']; ?>" value="D" id="cau_<?= $cauHoi['MaCauHoi']; ?>_D">
                                    <label class="form-check-label" for="cau_<?= $cauHoi['MaCauHoi']; ?>_D"><?= htmlspecialchars($cauHoi['DapAnD']); ?></label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center">
                        <button type="submit" name="submitQuiz" class="btn btn-success">Nộp bài</button>
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
