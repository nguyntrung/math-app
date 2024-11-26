<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['MaNguoiDung'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit();
}

try {
    $stmt = $conn->prepare("
        INSERT INTO tiendohoctap 
        (MaNguoiDung, MaBaiHoc, MaCauHoi, CauTraLoi, ThoiGianLam) 
        VALUES 
        (:maNguoiDung, :maBaiHoc, :maCauHoi, :cauTraLoi, :thoiGianLam)
    ");

    $stmt->execute([
        ':maNguoiDung' => $_SESSION['MaNguoiDung'],
        ':maBaiHoc' => $data['maBaiHoc'],
        ':maCauHoi' => $data['maCauHoi'],
        ':cauTraLoi' => $data['cauTraLoi'],
        ':thoiGianLam' => $data['thoiGianLam']
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>