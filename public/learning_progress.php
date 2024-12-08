<?php
session_start();

// Check login
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Prepare a query to get the total number of questions for each lesson
$stmtTotalQuestions = $conn->prepare("
    SELECT 
        MaBaiHoc, 
        COUNT(MaCauHoi) AS TotalQuestions
    FROM (
        SELECT MaBaiHoc, MaCauHoi FROM cauhoitracnghiem
        UNION
        SELECT MaBaiHoc, MaCauHoi FROM cauhoituluan
    ) AS AllQuestions
    GROUP BY MaBaiHoc
");
$stmtTotalQuestions->execute();
$totalQuestionsMap = $stmtTotalQuestions->fetchAll(PDO::FETCH_KEY_PAIR);

// Modify the main query to include progress information
$stmt = $conn->prepare("
    SELECT 
        chuonghoc.MaChuong, 
        chuonghoc.TenChuong, 
        baihoc.MaBaiHoc, 
        baihoc.TenBai, 
        SUM(tiendohoctap.ThoiGianLam) AS TotalTimeSpent,
        COUNT(tiendohoctap.MaCauHoi) AS AnsweredQuestions
    FROM chuonghoc
    LEFT JOIN baihoc ON chuonghoc.MaChuong = baihoc.MaChuong
    LEFT JOIN tiendohoctap ON baihoc.MaBaiHoc = tiendohoctap.MaBaiHoc 
    AND tiendohoctap.MaNguoiDung = :maNguoiDung
    GROUP BY chuonghoc.MaChuong, baihoc.MaBaiHoc
    ORDER BY chuonghoc.ThuTu ASC, baihoc.ThuTu ASC
");

$stmt->execute(['maNguoiDung' => $_SESSION['MaNguoiDung']]);
$chuongBaiHocList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create an array to group lessons by chapter
$chuongData = [];
foreach ($chuongBaiHocList as $row) {
    $maChuong = $row['MaChuong'];
    $tenChuong = $row['TenChuong'];
    $maBaiHoc = $row['MaBaiHoc'];
    $tenBaiHoc = $row['TenBai'];
    $totalTimeSpent = $row['TotalTimeSpent'];
    $answeredQuestions = $row['AnsweredQuestions'];

    // Calculate total questions for this lesson
    $totalQuestions = isset($totalQuestionsMap[$maBaiHoc]) ? $totalQuestionsMap[$maBaiHoc] : 0;

    // Format time spent
    $minutes = floor($totalTimeSpent);
    $seconds = round(($totalTimeSpent - $minutes) * 60);
    $timeFormatted = sprintf("%02d:%02d", $minutes, $seconds);

    // Check if chapter exists
    if (!isset($chuongData[$maChuong])) {
        $chuongData[$maChuong] = [
            'tenChuong' => $tenChuong,
            'baiHocList' => []
        ];
    }

    // Calculate score
    $score = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 20, 1) : 0;

    // Add lesson to chapter
    $chuongData[$maChuong]['baiHocList'][] = [
        'maBaiHoc' => $maBaiHoc,
        'tenBaiHoc' => $tenBaiHoc,
        'timeSpent' => $timeFormatted,
        'answeredQuestions' => $answeredQuestions,
        'totalQuestions' => $totalQuestions,
        'score' => $score,
        'isCompleted' => $answeredQuestions > 0
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
         max-width: 1130px;
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
                           <?= $baiHoc['answeredQuestions'] ?>/20 Câu
                        </div>
                     </div>
                     <div class="lesson-item flex-1 text-center">
                        <div class="score-bar">
                           <div class="score-fill" style="width: <?= ($baiHoc['score'] / 20) * 100 ?>%"></div>
                           <span class="score-text"><?= $baiHoc['score'] ?></span>
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
      <!-- Footer Scripts -->
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

      <script>
         $(document).ready(function() {
            // Đảm bảo dropdown hoạt động với Bootstrap
            $('.dropdown-toggle').dropdown();
         });
      </script>
   </body>
</html>
