<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Kiểm tra nếu có mã bài học trong URL
if (!isset($_GET['maBaiHoc'])) {
    echo "Bài học không tồn tại!";
    exit();
}

$maBaiHoc = $_GET['maBaiHoc'];

try {
    // Lấy thông tin video và tên bài học
    $stmt = $conn->prepare("SELECT TenBai, DuongDanVideo FROM BaiHoc WHERE MaBaiHoc = :maBaiHoc");
    $stmt->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
    $stmt->execute();
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson) {
        echo "Bài học không tồn tại!";
        exit();
    }

    $tenBaiHoc = $lesson['TenBai'] ?? "Bài học không có tên";
    $duongDanVideo = $lesson['DuongDanVideo'] ?? null;

    // Lấy bài học tiếp theo
    $stmtNext = $conn->prepare("SELECT MaBaiHoc, TenBai FROM BaiHoc WHERE MaBaiHoc > :maBaiHoc ORDER BY MaBaiHoc ASC LIMIT 1");
    $stmtNext->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
    $stmtNext->execute();
    $nextLesson = $stmtNext->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết bài giảng</title>
    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Main Content Start -->
    <div class="container-fluid pt-5">
        <div class="container pb-5">
            <h4><?= htmlspecialchars($tenBaiHoc); ?></h4> <!-- Hiển thị tên bài học -->

            <!-- Video bài giảng -->
            <?php if ($duongDanVideo): ?>
                <video controls class="w-100 mb-3">
                    <source src="<?= htmlspecialchars($duongDanVideo); ?>" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
            <?php else: ?>
                <p>Không có video cho bài học này.</p>
            <?php endif; ?>

            <!-- Liên kết đến các bài tập -->
            <div>
                <strong>Bài học liên quan:</strong>
            </div>
            <ul class="list-unstyled">
                <li>- <a href="theory_lessons.php?maBaiHoc=<?= htmlspecialchars($maBaiHoc); ?>">Lý thuyết</a></li>
                <li>- <a href="essay_detail.php?maBaiHoc=<?= htmlspecialchars($maBaiHoc); ?>">Bài tập tự luận</a></li>
                <li>- <a href="quiz_detail.php?maBaiHoc=<?= htmlspecialchars($maBaiHoc); ?>">Bài tập trắc nghiệm</a></li>
            </ul>

            <!-- Liên kết đến bài học tiếp theo -->
            <?php if ($nextLesson): ?>
                <div class="mt-3">
                    <strong>Bài học tiếp theo:</strong><br>
                    <a href="video_lessons_detail.php?maBaiHoc=<?= htmlspecialchars($nextLesson['MaBaiHoc']); ?>">
                        <?= htmlspecialchars($nextLesson['TenBai']); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Main Content End -->

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top Button -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
