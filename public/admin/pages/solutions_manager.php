<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
include '../../../database/db.php';

// Lấy danh sách bài giải từ cơ sở dữ liệu
$stmt = $conn->prepare("SELECT bg.MaBaiGiai, bg.MaBaiHoc, bg.Bai, bg.LoiGiai, bg.ThuTu, bh.TenBai 
                        FROM baigiai bg
                        JOIN baihoc bh ON bg.MaBaiHoc = bh.MaBaiHoc 
                        ORDER BY bg.ThuTu ASC");
$stmt->execute();
$baiGiaiList = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="../assets/" data-template="vertical-menu-template-free" data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Quản lý bài giải</title>
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
                            <h5 class="card-header">Danh sách các bài giải</h5>
                            <div class="card-body">
                                <div class="table-responsive text-nowrap">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Mã Bài Giải</th>
                                                <th>Mã Bài Học</th>
                                                <th>Tên Bài Học</th>
                                                <th>Bài</th>
                                                <th>Lời Giải</th>
                                                <th>Thứ Tự</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($baiGiaiList as $baiGiai): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($baiGiai['MaBaiGiai']); ?></td>
                                                <td><?php echo htmlspecialchars($baiGiai['MaBaiHoc']); ?></td>
                                                <td>
                                                    <?php 
                                                        $tenBai = htmlspecialchars($baiGiai['TenBai']); 
                                                        echo mb_substr($tenBai, 0, 47) . (mb_strlen($tenBai) > 50 ? '...' : '');
                                                    ?>
                                                </td>
                                                <td><?php echo mb_substr(htmlspecialchars($baiGiai['Bai']), 0, 30) . (mb_strlen($baiGiai['Bai']) > 30 ? '...' : ''); ?>
                                                </td>
                                                <td><?php echo mb_substr(htmlspecialchars($baiGiai['LoiGiai']), 0, 30) . (mb_strlen($baiGiai['LoiGiai']) > 30 ? '...' : ''); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($baiGiai['ThuTu']); ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="ri-more-2-line"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item"
                                                                href="add_update_solutions.php?id=<?php echo $baiGiai['MaBaiGiai']; ?>"><i
                                                                    class="ri-pencil-line me-1"></i> Chỉnh sửa</a>
                                                            <a class="dropdown-item" href="#"
                                                                onclick="confirmDelete('<?php echo $baiGiai['MaBaiGiai']; ?>')"><i
                                                                    class="ri-delete-bin-6-line me-1"></i> Xóa</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <a href="add_update_solutions.php" class="btn btn-success mt-2">Thêm bài giải</a>
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
                        Bạn có chắc chắn muốn xóa bài giải này?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        let maBaiGiaiToDelete;

        function confirmDelete(maBaiGiai) {
            maBaiGiaiToDelete = maBaiGiai; // Lưu mã câu hỏi vào biến toàn cục
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show(); // Hiển thị modal
        }

        // Xử lý khi nhấn nút xóa trong modal
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            window.location.href = 'delete.php?id=' + maBaiGiaiToDelete +
                '&table=baigiai&location=solutions_manager.php&idColumn=MaBaiGiai';
        });
        </script>
</body>

</html>