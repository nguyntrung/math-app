<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
include '../../../database/db.php';

// Lấy danh sách câu hỏi trắc nghiệm từ cơ sở dữ liệu
$stmt = $conn->prepare("SELECT c.MaCauHoi, bh.TenBai, c.NoiDung, c.DapAnA, c.DapAnB, c.DapAnC, c.DapAnD, c.DapAnDung, c.GiaiThich 
                         FROM cauhoitracnghiem c 
                         JOIN baihoc bh ON c.MaBaiHoc = bh.MaBaiHoc 
                         ORDER BY c.MaCauHoi ASC");
$stmt->execute();
$cauHoiList = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="../assets/" data-template="vertical-menu-template-free" data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Quản lý câu hỏi trắc nghiệm</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="../assets/vendor/fonts/remixicon/remixicon.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include 'sidebar.php'; ?>
            <div class="layout-page">
                <?php include 'navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="card">
                            <h5 class="card-header">Danh sách câu hỏi trắc nghiệm</h5>
                            <div class="card-body">
                                <div class="table-responsive text-nowrap">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Mã</th>
                                                <th>Tên bài học</th>
                                                <th>Nội dung câu hỏi</th>
                                                <th>Đúng</th>
                                                <th>Các lựa chọn</th>
                                                <th>Giải Thích</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cauHoiList as $cauHoi): ?>
                                            <tr>
                                                <td class="text-center"><?php echo htmlspecialchars($cauHoi['MaCauHoi']); ?></td>
                                                <td>
                                                    <?php 
                                                    $tenBai = htmlspecialchars($cauHoi['TenBai']); 
                                                    echo mb_substr($tenBai, 0, 20) . (mb_strlen($tenBai) > 20 ? '...' : '');
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $noiDung = htmlspecialchars($cauHoi['NoiDung']); 
                                                    echo mb_substr($noiDung, 0, 30) . (mb_strlen($noiDung) > 20 ? '...' : '');
                                                    ?>
                                                </td>
                                                <td class="text-center"><?php echo htmlspecialchars($cauHoi['DapAnDung']); ?></td>
                                                <td>
                                                    A: <?php 
                                                        $dapAnA = htmlspecialchars($cauHoi['DapAnA']); 
                                                        echo mb_substr($dapAnA, 0, 2) . (mb_strlen($dapAnA) > 2 ? '...' : ''); 
                                                    ?>,
                                                    B: <?php 
                                                        $dapAnB = htmlspecialchars($cauHoi['DapAnB']); 
                                                        echo mb_substr($dapAnB, 0, 2) . (mb_strlen($dapAnB) > 2 ? '...' : ''); 
                                                    ?>,
                                                    C: <?php 
                                                        $dapAnC = htmlspecialchars($cauHoi['DapAnC']); 
                                                        echo mb_substr($dapAnC, 0, 2) . (mb_strlen($dapAnC) > 2 ? '...' : ''); 
                                                    ?>,
                                                    D: <?php 
                                                        $dapAnD = htmlspecialchars($cauHoi['DapAnD']); 
                                                        echo mb_substr($dapAnD, 0, 2) . (mb_strlen($dapAnD) > 2 ? '...' : ''); 
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $giaiThich = htmlspecialchars($cauHoi['GiaiThich']); 
                                                    echo mb_substr($giaiThich, 0, 15) . (mb_strlen($giaiThich) > 15 ? '...' : ''); 
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="ri-more-2-line"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item"
                                                                href="add_update_quiz.php?id=<?php echo $cauHoi['MaCauHoi']; ?>"><i
                                                                    class="ri-pencil-line me-1"></i> Chỉnh sửa</a>
                                                            <a class="dropdown-item" href="#"
                                                                onclick="confirmDelete('<?php echo $cauHoi['MaCauHoi']; ?>')"><i
                                                                    class="ri-delete-bin-6-line me-1"></i> Xóa</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <a href="add_update_quiz.php" class="btn btn-success mt-2 mb-2 " >Thêm câu hỏi</a>
                                </div>
                            </div>
                        </div>
                        <?php include 'footer.php'; ?>
                    </div>
                </div>
            </div>
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        <?php include 'other.php'; ?>
        <!-- Modal Xác Nhận Xóa -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Xác Nhận Xóa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Bạn có chắc chắn muốn xóa câu trắc nghiệm này?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    let maTracNghiemToDelete;

    function confirmDelete(maTracNghiem) {
        maTracNghiemToDelete = maTracNghiem; // Lưu mã câu hỏi vào biến toàn cục
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show(); // Hiển thị modal
    }

    // Xử lý khi nhấn nút xóa trong modal
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        window.location.href = 'delete.php?id=' + maTracNghiemToDelete +
            '&table=cauhoitracnghiem&location=quiz_manager.php&idColumn=MaCauHoi';
    });
    </script>


</body>

</html>