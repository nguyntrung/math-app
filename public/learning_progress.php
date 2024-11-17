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
        tiendoquiz.MaCauHoi, 
        tiendoquiz.CauTraLoi,
        tiendoquiz.ThoiGianLam
    FROM chuonghoc
    LEFT JOIN baihoc ON chuonghoc.MaChuong = baihoc.MaChuong
    LEFT JOIN tiendoquiz ON baihoc.MaBaiHoc = tiendoquiz.MaBaiHoc 
    AND tiendoquiz.MaNguoiDung = :maNguoiDung
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
    $isCompleted = !is_null($row['CauTraLoi']); // Kiểm tra nếu câu trả lời có tồn tại
    $timeSpent = $row['ThoiGianLam']; // Thời gian làm bài theo kiểu float

    // Chuyển thời gian từ giây hoặc phút sang định dạng giờ:phút:giây (nếu cần)
    $minutes = floor($timeSpent);
    $seconds = round(($timeSpent - $minutes) * 60);

    // Đảm bảo rằng thời gian làm bài không bị quá dài và hiển thị đúng
    $timeFormatted = sprintf("%02d:%02d", $minutes, $seconds);

    // Kiểm tra xem chương đã tồn tại chưa
    if (!isset($chuongData[$maChuong])) {
        $chuongData[$maChuong] = [
            'tenChuong' => $tenChuong,
            'baiHocList' => []
        ];
    }

    // Thêm bài học vào chương
    $chuongData[$maChuong]['baiHocList'][] = [
        'maBaiHoc' => $maBaiHoc,
        'tenBaiHoc' => $tenBaiHoc,
        'isCompleted' => $isCompleted,
        'timeSpent' => $timeFormatted // Lưu trữ thời gian đã định dạng
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
/* Các kiểu dáng của bảng */
.student_table {
    width: 80%;
    /* Giảm chiều rộng bảng xuống còn 80% */
    border-collapse: collapse;
    table-layout: fixed;
    /* Cố định chiều rộng của các cột */
    margin: 0 auto;
    /* Căn giữa bảng */
}

.student_table__header {
    background-color: #f8f8f8;
    font-weight: bold;
}

.student_table__header th {
    padding: 10px;
    text-align: left;
}

.student_table__item {
    background-color: #fff;
    border-bottom: 1px solid #f1f1f1;
}

.student_table__item td {
    padding: 10px;
    word-wrap: break-word;
    /* Đảm bảo văn bản dài sẽ xuống dòng */
    max-width: 250px;
    /* Giới hạn chiều rộng của các ô */
    overflow: hidden;
    /* Ẩn nội dung thừa */
    text-overflow: ellipsis;
    /* Hiển thị ba chấm khi nội dung bị tràn */
}

.student_table__link {
    text-decoration: none;
    color: #007bff;
    display: flex;
    align-items: center;
}

.icofont {
    margin-right: 5px;
}

.text-center {
    text-align: center;
}

.student_table__group {
    background-color: #f0f0f0;
    font-weight: bold;
    padding: 10px;
}

.student_table__lecture {
    background-color: #f7f7f7;
    padding: 10px;
    font-style: italic;
}

.score {
    position: relative;
    display: inline-block;
    padding: 3px 10px;
    background-color: #4caf50;
    color: white;
    border-radius: 20px;
}

.hidden_in_mobile {
    display: none;
}

.display_in_mobile {
    position: absolute;
    bottom: 0;
}

._2imii {
    position: relative;
    width: 100%;
    height: 20px;
}

._3NKna {
    position: absolute;
    bottom: 0;
    right: 10px;
}

/* Cột "Tên bài học" */
.student_table__item td:nth-child(1) {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Cột "Thời gian làm" */
.student_table__item td:nth-child(2) {
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Cột "Câu hỏi đã trả lời" */
.student_table__item td:nth-child(3) {
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Cột "Điểm" */
.student_table__item td:nth-child(4) {
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Căn giữa tiêu đề */
h1 {
    text-align: center;
    /* Đảm bảo tiêu đề được căn giữa */
}
</style>

<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Main Start -->
    <div class="container-fluid pt-5">
        <div class="container pb-5">
            <h1 class="text-center mb-4">Tiến độ học tập</h1>

            <?php if (!empty($chuongData)): ?>
            <div class="box">
                <table class="table table-responsive student_table">
                    <thead class="_2H-0L">
                        <tr class="student-table__header">
                            <th class="col-xs-4">Chủ điểm</th>
                            <th class="col-xs-3 text-center">Thời Gian Bỏ Ra</th>
                            <th class="col-xs-2 text-center">Câu Hỏi Đã Trả Lời</th>
                            <th class="col-xs-1 col-md-3">Điểm</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chuongData as $maChuong => $chuong): ?>
                        <tr class="student_table__group">
                            <td colspan="4"><?= htmlspecialchars($chuong['tenChuong']); ?></td>
                        </tr>

                        <!-- Kiểm tra nếu chương không có bài học -->
                        <?php if (empty($chuong['baiHocList'])): ?>
                        <tr>
                            <td colspan="4" class="text-center text-warning">Không có bài học thuộc chương này</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($chuong['baiHocList'] as $baiHoc): ?>
                        <tr class="student_table__item">
                            <td>
                                <a href="quiz_detail.php?maBaiHoc=<?= $baiHoc['maBaiHoc']; ?>"
                                    class="student_table__link">
                                    <i class="icofont icofont-book-alt"></i>
                                    <?= htmlspecialchars($baiHoc['tenBaiHoc']); ?>
                                </a>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($baiHoc['timeSpent']); ?></td>
                            <td class="text-center"><?= $baiHoc['isCompleted'] ? 'Đã trả lời' : 'Chưa trả lời'; ?></td>
                            <td class="text-center">
                                <div class="score"><?= $baiHoc['isCompleted'] ? '10' : '0'; ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-center">Hiện tại chưa có chương và bài học nào.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Main End -->

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
</body>

</html>