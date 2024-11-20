<?php
session_start();
include '../../../database/db.php';

// Chuẩn bị câu truy vấn và thực thi
$sql = "SELECT * FROM lienhe";
$stmt = $conn->prepare($sql);
$stmt->execute();

// Kiểm tra số lượng hàng trả về
if ($stmt->rowCount() > 0) {
    $lienhes = $stmt->fetchAll();
} else {
    $lienhes = [];
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="../assets/" data-template="vertical-menu-template-free" data-style="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>Quản lý liên hệ</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"rel="stylesheet" />
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
                            <h1 class="card-header">Danh sách quản lý liên hệ</h1>
                            <div class="card-body">
                                <div class="table-responsive text-nowrap">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th class="text-truncate">Mã</th>
                                            <th class="text-truncate">Họ và tên</th>
                                            <th class="text-truncate">Email</th>
                                            <th class="text-truncate">Nội dung</th>
                                            <th class="text-truncate">Ngày giờ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (count($lienhes) > 0) {
                                            foreach ($lienhes as $contact) {
                                                echo "<tr>";
                                                echo "<td class='text-truncate'>" . htmlspecialchars($contact["MaLienHe"]) . "</td>";
                                                echo "<td class='text-truncate'>
                                                        <div class='d-flex align-items-center'>
                                                            <div class='avatar avatar-sm me-4'>
                                                                <img src='../assets/img/avatars/" . htmlspecialchars($contact['MaLienHe']) . ".png' alt='Avatar' class='rounded-circle' />
                                                            </div>
                                                            <div>
                                                                <h6 class='mb-0 text-truncate'>" . htmlspecialchars($contact["HoTen"]) . "</h6>
                                                            </div>
                                                        </div>
                                                    </td>";
                                                echo "<td class='text-truncate'>" . htmlspecialchars($contact["Email"]) . "</td>";
                                                echo "<td class='text-truncate'>" . htmlspecialchars($contact["NoiDung"]) . "</td>";
                                                echo "<td class='text-truncate'>" . date("d-m-Y H:i:s", strtotime($contact["NgayGio"])) . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>Không có liên hệ nào</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
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
    </div>
</body>
</html>

<?php
$conn = null;
?>