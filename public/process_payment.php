<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registermember.php');
    exit();
}

try {
    $conn->beginTransaction();

    // Kiểm tra gói hiện tại
    $stmt = $conn->prepare("
        SELECT * FROM dangkythanhvien 
        WHERE MaNguoiDung = :MaNguoiDung 
        AND TrangThai = 'DANG_HOAT_DONG'
    ");
    $stmt->bindParam(':MaNguoiDung', $_SESSION['MaNguoiDung'], PDO::PARAM_INT);
    $stmt->execute();
    $currentSubscription = $stmt->fetch(PDO::FETCH_ASSOC);

    $startDate = date('Y-m-d');
    
    // Nếu có gói hiện tại và chưa hết hạn, tính ngày bắt đầu từ ngày hết hạn cũ
    if ($currentSubscription && strtotime($currentSubscription['NgayKetThuc']) > strtotime($startDate)) {
        $startDate = $currentSubscription['NgayKetThuc'];
    }

    // Tính ngày kết thúc mới
    $endDate = date('Y-m-d', strtotime($startDate . " + " . $_POST['months'] . " months"));

    // Nếu có gói cũ, cập nhật trạng thái thành 'HET_HAN'
    if ($currentSubscription) {
        $stmt = $conn->prepare("
            UPDATE dangkythanhvien 
            SET TrangThai = 'HET_HAN' 
            WHERE MaDangKy = :MaDangKy
        ");
        $stmt->bindParam(':MaDangKy', $currentSubscription['MaDangKy'], PDO::PARAM_INT);
        $stmt->execute();
    }

    // Thêm đăng ký mới
    $stmt = $conn->prepare("
        INSERT INTO dangkythanhvien (
            MaNguoiDung, 
            LoaiDangKy, 
            NgayBatDau, 
            NgayKetThuc, 
            GiaTien, 
            TrangThai
        ) VALUES (
            :MaNguoiDung,
            :LoaiDangKy,
            :NgayBatDau,
            :NgayKetThuc,
            :GiaTien,
            'DANG_HOAT_DONG'
        )
    ");

    $loaiDangKy = ($_POST['months'] == 12) ? 'NAM' : 'THANG';
    
    $stmt->bindParam(':MaNguoiDung', $_SESSION['MaNguoiDung'], PDO::PARAM_INT);
    $stmt->bindParam(':LoaiDangKy', $loaiDangKy, PDO::PARAM_STR);
    $stmt->bindParam(':NgayBatDau', $startDate, PDO::PARAM_STR);
    $stmt->bindParam(':NgayKetThuc', $endDate, PDO::PARAM_STR);
    $stmt->bindParam(':GiaTien', $_POST['price'], PDO::PARAM_STR);
    $stmt->execute();
    
    $dangkyId = $conn->lastInsertId();

    // Thêm vào bảng thanh toán
    $stmt = $conn->prepare("
        INSERT INTO thanhtoan (
            MaDangKy, 
            SoTien, 
            PhuongThuc
        ) VALUES (
            :MaDangKy,
            :SoTien,
            :PhuongThuc
        )
    ");
    
    $stmt->bindParam(':MaDangKy', $dangkyId, PDO::PARAM_INT);
    $stmt->bindParam(':SoTien', $_POST['price'], PDO::PARAM_STR);
    $stmt->bindParam(':PhuongThuc', $_POST['payment_method'], PDO::PARAM_STR);
    $stmt->execute();

    // Cập nhật trạng thái thành viên trong bảng người dùng
    $stmt = $conn->prepare("
        UPDATE nguoidung 
        SET ThanhVien = 1 
        WHERE MaNguoiDung = :MaNguoiDung
    ");
    $stmt->bindParam(':MaNguoiDung', $_SESSION['MaNguoiDung'], PDO::PARAM_INT);
    $stmt->execute();

    $conn->commit();
    
    $_SESSION['success_message'] = "Đăng ký thành viên thành công!";
    header('Location: registermember.php');

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại!";
    header('Location: registermember.php');
}

exit();
?>