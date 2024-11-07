<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Lấy dữ liệu từ yêu cầu POST
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->maBaiHoc) || !isset($data->watchedDuration) || !is_numeric($data->watchedDuration)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

$maBaiHoc = $data->maBaiHoc;
$watchedDuration = $data->watchedDuration;
$maNguoiDung = $_SESSION['MaNguoiDung'];

try {
    // Kiểm tra xem người dùng đã có tiến độ học tập cho bài học này chưa
    $stmtCheckProgress = $conn->prepare("SELECT MaTienDo, ThoiLuongXem FROM TienDoHocTap WHERE MaNguoiDung = :maNguoiDung AND MaBaiHoc = :maBaiHoc");
    $stmtCheckProgress->bindParam(':maNguoiDung', $maNguoiDung, PDO::PARAM_INT);
    $stmtCheckProgress->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
    $stmtCheckProgress->execute();
    $progress = $stmtCheckProgress->fetch(PDO::FETCH_ASSOC);

    // Lấy thời gian tổng của video (Giả sử bạn có một bảng để lấy thời gian của video)
    $stmtGetVideoDuration = $conn->prepare("SELECT ThoiLuongVideo FROM BaiHoc WHERE MaBaiHoc = :maBaiHoc");
    $stmtGetVideoDuration->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
    $stmtGetVideoDuration->execute();
    $video = $stmtGetVideoDuration->fetch(PDO::FETCH_ASSOC);
    $videoDuration = $video['ThoiLuongVideo'];  // Thời gian video, có thể là dạng giây

    // Giới hạn thời gian xem không vượt quá tổng thời gian video
    if ($watchedDuration > $videoDuration) {
        $watchedDuration = $videoDuration;
    }

    if ($progress) {
        // Nếu có tiến độ, cập nhật thời gian đã xem
        $stmtUpdate = $conn->prepare("UPDATE TienDoHocTap SET ThoiLuongXem = :watchedDuration WHERE MaNguoiDung = :maNguoiDung AND MaBaiHoc = :maBaiHoc");
        $stmtUpdate->bindParam(':watchedDuration', $watchedDuration, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':maNguoiDung', $maNguoiDung, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
        $stmtUpdate->execute();
    } else {
        // Nếu không có tiến độ, thêm mới
        $stmtInsert = $conn->prepare("INSERT INTO TienDoHocTap (MaNguoiDung, MaBaiHoc, ThoiLuongXem) VALUES (:maNguoiDung, :maBaiHoc, :watchedDuration)");
        $stmtInsert->bindParam(':maNguoiDung', $maNguoiDung, PDO::PARAM_INT);
        $stmtInsert->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
        $stmtInsert->bindParam(':watchedDuration', $watchedDuration, PDO::PARAM_STR);
        $stmtInsert->execute();
    }

    // Kiểm tra nếu người dùng đã xem đủ video (ví dụ: xem 95% thời gian video trở lên)
    if ($watchedDuration >= $videoDuration * 1) {
        // Cập nhật ngày hoàn thành (Nếu người dùng đã xem 95% video trở lên)
        $stmtUpdateCompletion = $conn->prepare("UPDATE TienDoHocTap SET NgayHoanThanh = NOW() WHERE MaNguoiDung = :maNguoiDung AND MaBaiHoc = :maBaiHoc");
        $stmtUpdateCompletion->bindParam(':maNguoiDung', $maNguoiDung, PDO::PARAM_INT);
        $stmtUpdateCompletion->bindParam(':maBaiHoc', $maBaiHoc, PDO::PARAM_INT);
        $stmtUpdateCompletion->execute();
    }

    // Trả về kết quả thành công
    echo json_encode(['status' => 'success', 'message' => 'Progress saved successfully']);
} catch (PDOException $e) {
    // Nếu có lỗi, trả về thông báo lỗi
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
