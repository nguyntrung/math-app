<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
include '../../../database/db.php';

// Lấy danh sách chương học từ cơ sở dữ liệu
$stmt = $conn->prepare("SELECT MaChuong, TenChuong, ThuTu, MienPhi FROM chuonghoc ORDER BY ThuTu ASC");
$stmt->execute();
$chuongHocList = $stmt->fetchAll();
?>

<!doctype html>
<html
   lang="en"
   class="light-style layout-menu-fixed layout-compact"
   dir="ltr"
   data-theme="theme-default"
   data-assets-path="../assets/"
   data-template="vertical-menu-template-free"
   data-style="light">
   <head>
      <meta charset="utf-8" />
      <meta
         name="viewport"
         content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
      <title>Quản lý chương học</title>
      <meta name="description" content="" />
      <!-- Favicon -->
      <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
      <!-- Fonts -->
      <link rel="preconnect" href="https://fonts.googleapis.com" />
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
      <link
         href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
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
                     <h5 class="card-header">Danh sách chương học</h5>
                     <div class="card-body">
                        <div class="table-responsive text-nowrap">
                          <table class="table table-bordered">
                              <thead>
                                  <tr>
                                      <th>Mã chương</th>
                                      <th>Tên chương</th>
                                      <th>Thứ tự</th>
                                      <th>Phí</th>

                                      <th></th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php foreach ($chuongHocList as $chuongHoc): ?>
                                      <tr>
                                          <td><?php echo htmlspecialchars($chuongHoc['MaChuong']); ?></td>
                                          <td><?php echo htmlspecialchars($chuongHoc['TenChuong']); ?></td>
                                          <td><?php echo htmlspecialchars($chuongHoc['ThuTu']); ?></td>
                                          <td><?php echo htmlspecialchars($chuongHoc['MienPhi']); ?></td>
                                          <td>
                                              <div class="dropdown">
                                                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                      <i class="ri-more-2-line"></i>
                                                  </button>
                                                  <div class="dropdown-menu">
                                                      <a class="dropdown-item" href="add_update_chapter.php?id=<?php echo $chuongHoc['MaChuong']; ?>"><i class="ri-pencil-line me-1"></i> Chỉnh sửa</a>
                                                      <a class="dropdown-item" href="#" onclick="confirmDelete('<?php echo $chuongHoc['MaChuong']; ?>')"><i class="ri-delete-bin-6-line me-1"></i> Xóa</a>
                                                  </div>
                                              </div>
                                          </td>
                                      </tr>
                                  <?php endforeach; ?>
                              </tbody>
                          </table>
                          <a href="add_update_chapter.php" class="btn btn-success mt-2">Thêm chương học</a>
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
      <script>
          function confirmDelete(maChuong) {
              if (confirm('Bạn có chắc chắn muốn xóa chương học này?')) {
                  window.location.href = 'delete_chuonghoc.php?id=' + maChuong;
              }
          }
      </script>
   </body>
</html>
