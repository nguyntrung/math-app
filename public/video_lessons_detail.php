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
$maNguoiDung = $_SESSION['MaNguoiDung'];

try {
    // Lấy thông tin video và tên bài học
    $stmt = $conn->prepare("SELECT TenBai, DuongDanVideo, ThoiLuongVideo FROM BaiHoc WHERE MaBaiHoc = :maBaiHoc");
    $stmt->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
    $stmt->execute();
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson) {
        echo "Bài học không tồn tại!";
        exit();
    }

    $tenBaiHoc = $lesson['TenBai'] ?? "Bài học không có tên";
    $duongDanVideo = $lesson['DuongDanVideo'] ?? null;
    $videoDuration = $lesson['ThoiLuongVideo'] ?? 0; 

    // Lấy tiến độ học tập của người dùng
    $stmtProgress = $conn->prepare("SELECT ThoiLuongXem FROM TienDoHocTap WHERE MaNguoiDung = :maNguoiDung AND MaBaiHoc = :maBaiHoc");
    $stmtProgress->bindParam(':maNguoiDung', $maNguoiDung, PDO::PARAM_INT);
    $stmtProgress->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
    $stmtProgress->execute();
    $progress = $stmtProgress->fetch(PDO::FETCH_ASSOC);

    // Tính toán tiến độ học tập
    if ($progress) {
        $watchedTime = $progress['ThoiLuongXem']; // Thời gian đã xem
        $progressPercentage = ($watchedTime / $videoDuration) * 100; // Tính tiến độ học tập
    } else {
        $progressPercentage = 0; // Nếu chưa có tiến độ, mặc định là 0%
    }

    // Lấy bài học tiếp theo
    $stmtNext = $conn->prepare("SELECT MaBaiHoc, TenBai FROM BaiHoc WHERE MaBaiHoc > :maBaiHoc ORDER BY MaBaiHoc ASC LIMIT 1");
    $stmtNext->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
    $stmtNext->execute();
    $nextLesson = $stmtNext->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra xem người dùng đã hoàn thành bài học chưa
    $stmtCheckCompletion = $conn->prepare("SELECT NgayHoanThanh, ThoiLuongXem FROM TienDoHocTap WHERE MaNguoiDung = :maNguoiDung AND MaBaiHoc = :maBaiHoc");
    $stmtCheckCompletion->bindParam(':maNguoiDung', $maNguoiDung, PDO::PARAM_INT);
    $stmtCheckCompletion->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
    $stmtCheckCompletion->execute();
    $completion = $stmtCheckCompletion->fetch(PDO::FETCH_ASSOC);

    // Cập nhật trạng thái hoàn thành nếu người dùng chưa hoàn thành
    if ($completion && !$completion['NgayHoanThanh']) {
        $stmtUpdateCompletion = $conn->prepare("UPDATE TienDoHocTap SET NgayHoanThanh = NOW() WHERE MaNguoiDung = :maNguoiDung AND MaBaiHoc = :maBaiHoc");
        $stmtUpdateCompletion->bindParam(':maNguoiDung', $maNguoiDung, PDO::PARAM_INT);
        $stmtUpdateCompletion->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
        $stmtUpdateCompletion->execute();
    }

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
    <style>
        body {
        background-color: #f0f9ff;
        }

        h4 {
            color: #4a90e2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            font-size: 24px;
            margin: 20px 0;
            padding: 10px;
            border-radius: 15px;
            background: linear-gradient(135deg, #fff6e5, #ffe5e5);
            text-align: center;
        }

        .video-container {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            border: 3px solid #ffd700;
        }

        .related-links {
            background-color: #fff;
            padding: 20px;
            border-radius: 15px;
            margin-top: 25px;
            border: 2px dashed #4a90e2;
        }

        .related-links strong {
            color: #ff6b6b;
            font-size: 20px;
            display: block;
            margin-bottom: 15px;
        }

        .related-links ul li {
            margin: 15px 0;
            padding-left: 25px;
            position: relative;
        }

        .related-links ul li:before {
            content: "🌟";
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
        }

        .related-links a {
            color: #4a90e2;
            text-decoration: none;
            font-size: 18px;
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 10px;
        }

        .related-links a:hover {
            color: #ff6b6b;
            background-color: #fff6e5;
            transform: scale(1.05);
        }

        .next-lesson {
            background-color: #e5f5ff;
            padding: 20px;
            border-radius: 15px;
            margin-top: 30px;
            text-align: center;
            border: 3px solid #4a90e2;
        }

        .next-lesson strong {
            color: #4a90e2;
            font-size: 20px;
            display: block;
            margin-bottom: 10px;
        }

        .next-lesson a {
            display: inline-block;
            color: #fff;
            background-color: #4a90e2;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 18px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .next-lesson a:hover {
            background-color: #ff6b6b;
            transform: scale(1.05);
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #ff6b6b;
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .back-to-top:hover {
            background-color: #4a90e2;
            transform: translateY(-5px);
        }

        video {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        #progress {
            height: 20px;
            border-radius: 10px;
            background-color: #e5f5ff;
            border: 2px solid #4a90e2;
        }

        #progress::-webkit-progress-bar {
            background-color: #e5f5ff;
            border-radius: 10px;
        }

        #progress::-webkit-progress-value {
            background-color: #4a90e2;
            border-radius: 8px;
        }

        #progress-text {
            color: #4a90e2;
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Main Content Start -->
    <div class="container-fluid pt-5">
        <div class="container pb-5">
            <h4><?= htmlspecialchars($tenBaiHoc); ?></h4> <!-- Hiển thị tên bài học -->

            <!-- Video bài giảng -->
            <div class="video-container">
                <?php if ($duongDanVideo): ?>
                <video controls class="w-100 mb-3">
                    <source src="<?= htmlspecialchars($duongDanVideo); ?>" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
                <?php else: ?>
                <p>Không có video cho bài học này.</p>
                <?php endif; ?>
            </div>

            <!-- Liên kết đến các bài tập -->
            <div class="related-links">
                <strong>Bài học liên quan:</strong>
                <ul class="list-unstyled">
                    <li><a href="theory_lessons.php?maBaiHoc=<?= htmlspecialchars($maBaiHoc); ?>">Lý thuyết</a></li>
                    <li><a href="essay_detail.php?maBaiHoc=<?= htmlspecialchars($maBaiHoc); ?>">Bài tập tự luận</a></li>
                    <li><a href="quiz_detail.php?maBaiHoc=<?= htmlspecialchars($maBaiHoc); ?>">Bài tập trắc nghiệm</a></li>
                </ul>
            </div>

            <!-- Liên kết đến bài học tiếp theo -->
            <?php if ($nextLesson): ?>
            <div class="next-lesson">
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

    <!-- Lấy thời gian xem video và cập nhật tiến độ -->
    <script>
    const video = document.querySelector('video');
    const progressBar = document.querySelector('#progress');
    const progressText = document.querySelector('#progress-text');

    // Đảm bảo video đã tải và có thể lấy thời gian
    video.addEventListener('loadedmetadata', () => {
        const videoDuration = video.duration; // Thời gian tổng của video (tính bằng giây)

        // Cập nhật khi video đang phát
        video.addEventListener('timeupdate', () => {
            const watchedDuration = video.currentTime; // Thời gian đã xem (tính bằng giây)

            // Tính toán phần trăm đã xem
            const watchedPercentage = (watchedDuration / videoDuration) * 100;

            // Cập nhật thanh tiến độ
            progressBar.value = watchedPercentage;
            progressText.textContent = Math.round(watchedPercentage) + "%";
        });

        // Gửi tiến độ học tập lên server khi video kết thúc
        video.addEventListener('ended', () => {
            const watchedDuration = video
                .duration; // Nếu video đã xem hết, gán thời gian hoàn thành cho người dùng
            fetch('save_progress.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        maBaiHoc: <?= json_encode($maBaiHoc); ?>, // Mã bài học từ PHP
                        watchedDuration: watchedDuration // Gửi thời gian video đã hoàn thành
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert("Bạn đã hoàn thành bài học!");
                    } else {
                        alert("Có lỗi xảy ra khi lưu tiến độ.");
                    }
                })
                .catch(error => console.error("Lỗi khi lưu tiến độ:", error));
        });
    });
    </script>

</body>

</html>     