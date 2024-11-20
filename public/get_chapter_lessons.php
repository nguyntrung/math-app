<?php
// get_chapter_lessons.php
session_start();
include '../database/db.php';

header('Content-Type: application/json');

if (!isset($_GET['maChuong'])) {
    echo json_encode(['error' => 'Missing chapter ID']);
    exit;
}

$maChuong = $_GET['maChuong'];

try {
    // Kiểm tra trạng thái thành viên
    $isActiveMember = false;
    $stmt = $conn->prepare("
        SELECT COUNT(*) as active_count 
        FROM dangkythanhvien 
        WHERE MaNguoiDung = :maNguoiDung 
        AND TrangThai = 'DANG_HOAT_DONG' 
        AND NgayKetThuc >= CURRENT_DATE()
    ");
    $stmt->bindParam(':maNguoiDung', $_SESSION['MaNguoiDung']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['active_count'] > 0) {
        $isActiveMember = true;
    }

    // Kiểm tra xem đây có phải là chương miễn phí không
    $stmt = $conn->prepare("SELECT MienPhi FROM chuonghoc WHERE MaChuong = :maChuong");
    $stmt->bindParam(':maChuong', $maChuong);
    $stmt->execute();
    $chuongInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nếu không phải chương miễn phí và không phải thành viên active
    if (!$chuongInfo['MienPhi'] && !$isActiveMember) {
        echo json_encode([
            'requiresMembership' => true
        ]);
        exit();
    }

    // Get chapter info
    $stmt = $conn->prepare("
        SELECT TenChuong 
        FROM ChuongHoc 
        WHERE MaChuong = :maChuong
    ");
    $stmt->bindParam(':maChuong', $maChuong);
    $stmt->execute();
    $chapter = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get lessons
    $stmt = $conn->prepare("
        SELECT 
            BaiHoc.MaBaiHoc,
            BaiHoc.TenBai,
            BaiHoc.ThoiLuongVideo,
            COALESCE(tiendohoctap.ThoiLuongXem, 0) as ThoiLuongXem
        FROM BaiHoc
        LEFT JOIN tiendohoctap ON BaiHoc.MaBaiHoc = tiendohoctap.MaBaiHoc 
            AND tiendohoctap.MaNguoiDung = :maNguoiDung
        WHERE BaiHoc.MaChuong = :maChuong
        ORDER BY BaiHoc.ThuTu ASC
    ");
    $stmt->bindParam(':maChuong', $maChuong);
    $stmt->bindParam(':maNguoiDung', $_SESSION['MaNguoiDung']);
    $stmt->execute();
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate progress
    $completedLessons = 0;
    foreach ($lessons as $lesson) {
        if ($lesson['ThoiLuongXem'] == $lesson['ThoiLuongVideo']) {
            $completedLessons++;
        }
    }

    echo json_encode([
        'chapterTitle' => $chapter['TenChuong'],
        'lessons' => $lessons,
        'completedLessons' => $completedLessons,
        'totalLessons' => count($lessons),
        'requiresMembership' => false
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>