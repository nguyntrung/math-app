<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
include '../../../database/db.php';

// Lấy danh sách câu hỏi tự luận từ cơ sở dữ liệu
$stmt = $conn->prepare("SELECT cl.MaCauHoi, cl.NoiDung, cl.LoiGiai, cl.GiaiThich, bh.TenBai 
                         FROM cauhoituluan cl 
                         JOIN baihoc bh ON cl.MaBaiHoc = bh.MaBaiHoc 
                         ORDER BY cl.MaCauHoi ASC");
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
    <title>Quản lý câu hỏi tự luận</title>
    <meta name="description" content="" />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="../assets/vendor/fonts/remixicon/remixicon.css" />
    <!-- Menu waves for no-customizer fix -->
    <link rel="stylesheet" href="../assets/vendor/libs/node-waves/node-waves.css" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <!-- Page CSS -->
    <!-- Helpers -->
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include 'sidebar.php'; ?>
            <!-- Layout container -->
            <div class="layout-page">
                <?php include 'navbar.php'; ?>
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="card">
                            <h5 class="card-header">Danh sách câu hỏi tự luận</h5>
                            <div class="card-body">
                                <div class="table-responsive text-nowrap">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Mã</th>
                                                <th>Tên bài</th>
                                                <th>Nội dung câu hỏi</th>
                                                <th>Lời giải câu hỏi</th>
                                                <th>Giải thích</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cauHoiList as $cauHoi): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cauHoi['MaCauHoi']); ?></td>
                                                <td>
                                                    <?php 
                                                    $tenBai = htmlspecialchars($cauHoi['TenBai']); 
                                                    echo mb_substr($tenBai, 0, 20) . (mb_strlen($tenBai) > 20 ? '...' : '');
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $noiDung = htmlspecialchars($cauHoi['NoiDung']); 
                                                    echo mb_substr($noiDung, 0, 40) . (mb_strlen($noiDung) > 20 ? '...' : '');
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($cauHoi['LoiGiai']); ?></td>
                                                <td><?php echo htmlspecialchars($cauHoi['GiaiThich']); ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="ri-more-2-line"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item"
                                                                href="add_update_essay.php?id=<?php echo $cauHoi['MaCauHoi']; ?>"><i
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
                                    <a href="add_update_essay.php" class="btn btn-success mt-2">Thêm câu hỏi</a>
                                </div>
                            </div>
                        </div>
                        <?php include 'footer.php'; ?>
                    </div>
                    <!-- Content wrapper -->
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
                        Bạn có chắc chắn muốn xóa câu hỏi này?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        let maCauHoiToDelete; 

        function confirmDelete(maCauHoi) {
            maCauHoiToDelete = maCauHoi; // Lưu mã câu hỏi vào biến toàn cục
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show(); // Hiển thị modal
        }

        // Xử lý khi nhấn nút xóa trong modal
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            window.location.href = 'delete.php?id=' + maCauHoiToDelete +
                '&table=cauhoituluan&location=essay_manager.php&idColumn=MaCauHoi';
        });
        </script>
</body>

</html>