<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Lấy danh sách các chương, bài học, và tiến độ học tập từ cơ sở dữ liệu
$stmt = $conn->prepare("
    SELECT 
        chuonghoc.MaChuong, 
        chuonghoc.TenChuong, 
        baihoc.MaBaiHoc, 
        baihoc.TenBai, 
        SUM(tiendoquiz.ThoiGianLam) AS TotalTimeSpent,  -- Sum of time spent on each question in the lesson
        COUNT(tiendoquiz.MaCauHoi) AS AnsweredQuestions   -- Count the number of questions answered
    FROM chuonghoc
    LEFT JOIN baihoc ON chuonghoc.MaChuong = baihoc.MaChuong
    LEFT JOIN tiendoquiz ON baihoc.MaBaiHoc = tiendoquiz.MaBaiHoc 
    AND tiendoquiz.MaNguoiDung = :maNguoiDung
    GROUP BY chuonghoc.MaChuong, baihoc.MaBaiHoc
    ORDER BY chuonghoc.ThuTu ASC, baihoc.ThuTu ASC
");

$stmt->execute(['maNguoiDung' => $_SESSION['MaNguoiDung']]);
$chuongBaiHocList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tạo mảng để nhóm các bài học theo chương
$chuongData = [];
foreach ($chuongBaiHocList as $row) {
    $maChuong = $row['MaChuong'];
    $tenChuong = $row['TenChuong'];
    $maBaiHoc = $row['MaBaiHoc'];
    $tenBaiHoc = $row['TenBai'];
    $totalTimeSpent = $row['TotalTimeSpent']; // Tổng thời gian bỏ ra cho bài học
    $answeredQuestions = $row['AnsweredQuestions']; // Số câu hỏi đã trả lời

    // Tính toán thời gian bỏ ra và chuyển đổi sang phút và giây
    $minutes = floor($totalTimeSpent);
    $seconds = round(($totalTimeSpent - $minutes) * 60);
    $timeFormatted = sprintf("%02d:%02d", $minutes, $seconds);

    // Kiểm tra xem chương đã có chưa
    if (!isset($chuongData[$maChuong])) {
        $chuongData[$maChuong] = [
            'tenChuong' => $tenChuong,
            'baiHocList' => []
        ];
    }

    // Đánh dấu bài học hoàn thành nếu có câu hỏi đã trả lời
    $isCompleted = $answeredQuestions > 0;

    // Thêm bài học vào chương
    $chuongData[$maChuong]['baiHocList'][] = [
        'maBaiHoc' => $maBaiHoc,
        'tenBaiHoc' => $tenBaiHoc,
        'timeSpent' => $timeFormatted,
        'answeredQuestions' => $answeredQuestions,
        'isCompleted' => $isCompleted
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>Tiến độ học tập</title>
      <?php include '../includes/styles.php'; ?>
   </head>
   <style>
      .progress-container {
         background: white;
         border-radius: 8px;
         box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
         margin: 20px auto;
         max-width: 1200px;
      }
      .progress-table {
         width: 100%;
      }
      .progress-header {
         display: flex;
         background: #4CAF50;
         color: white;
         padding: 15px;
         border-radius: 8px 8px 0 0;
      }
      .header-item {
         font-weight: 600;
      }
      .chapter-title {
         background: #E8F5E9;
         padding: 12px 15px;
         font-weight: 600;
         color: #2E7D32;
      }
      .lesson-row {
         display: flex;
         padding: 12px 15px;
         border-bottom: 1px solid #E0E0E0;
         align-items: center;
      }
      .lesson-link {
         color: #1976D2;
         text-decoration: none;
      }
      .lesson-link:hover {
         text-decoration: underline;
      }
      .flex-1 { flex: 1; }
      .flex-2 { flex: 2; }
      .flex-3 { flex: 3; }
      .text-center {
         text-align: center;
      }
      .score-bar {
         background: #E0E0E0;
         border-radius: 10px;
         height: 20px;
         position: relative;
         overflow: hidden;
         width: 100%;
         max-width: 80px;
         margin: 0 auto;
      }
      .score-fill {
         background: #4CAF50;
         height: 100%;
         transition: width 0.3s ease;
      }
      .score-text {
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         color: white;
         font-size: 12px;
         font-weight: 600;
      }
      .question-status {
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 5px;
      }
      .status-dot {
         width: 8px;
         height: 8px;
         border-radius: 50%;
         background: #E0E0E0;
      }
      .status-dot.completed {
         background: #4CAF50;
      }
      .no-lessons, .no-content {
         padding: 20px;
         text-align: center;
         color: #757575;
      }
   </style>
   <body>
      <?php include '../includes/navbar.php'; ?>
      <!-- Main Start -->
      <div class="container-fluid pt-5" style="background-color: #f0f0e6;">
         <div class="container pb-5">
            <div class="progress-container">
               <?php if (!empty($chuongData)): ?>
               <div class="progress-table">
                  <!-- Header -->
                  <div class="progress-header">
                     <div class="header-item flex-3">Chủ điểm</div>
                     <div class="header-item flex-2 text-center">Thời Gian Bỏ Ra</div>
                     <div class="header-item flex-2 text-center">Câu Hỏi Đã Trả Lời</div>
                     <div class="header-item flex-1 text-center">Điểm</div>
                  </div>
                  <!-- Content -->
                  <?php foreach ($chuongData as $maChuong => $chuong): ?>
                  <div class="chapter-title">
                     <div class="title-text"><?= htmlspecialchars($chuong['tenChuong']); ?></div>
                  </div>
                  <?php if (empty($chuong['baiHocList'])): ?>
                  <div class="no-lessons">Không có bài học thuộc chương này</div>
                  <?php else: ?>
                  <?php foreach ($chuong['baiHocList'] as $baiHoc): ?>
                  <div class="lesson-row">
                     <div class="lesson-item flex-3">
                        <a href="quizdetail.php?maBaiHoc=<?= $baiHoc['maBaiHoc']; ?>" class="lesson-link">
                        <?= htmlspecialchars($baiHoc['tenBaiHoc']); ?>
                        </a>
                     </div>
                     <div class="lesson-item flex-2 text-center"><?= htmlspecialchars($baiHoc['timeSpent']); ?></div>
                     <div class="lesson-item flex-2 text-center">
                        <div class="question-status">
                           <span class="status-dot <?= $baiHoc['answeredQuestions'] > 0 ? 'completed' : '' ?>"></span>
                           <?= $baiHoc['answeredQuestions'] ?> Câu
                        </div>
                     </div>
                     <div class="lesson-item flex-1 text-center">
                        <div class="score-bar">
                           <div class="score-fill" style="width: <?= $baiHoc['answeredQuestions'] > 0 ? '100' : '0' ?>%"></div>
                           <span class="score-text"><?= $baiHoc['answeredQuestions'] > 0 ? '10' : '0' ?></span>
                        </div>
                     </div>
                  </div>
                  <?php endforeach; ?>
                  <?php endif; ?>
                  <?php endforeach; ?>
               </div>
               <?php else: ?>
               <div class="no-content">Hiện tại chưa có chương và bài học nào.</div>
               <?php endif; ?>
            </div>
         </div>
      </div>
      <!-- Main End -->
      <?php include '../includes/footer.php'; ?>
   </body>
</html>
