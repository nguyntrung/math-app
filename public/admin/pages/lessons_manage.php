<?php
   session_start();
   
   // Kiểm tra xem người dùng đã đăng nhập chưa
   if (!isset($_SESSION['MaNguoiDung'])) {
       header('Location: login.php');
       exit();
   }
   
   // Kết nối cơ sở dữ liệu
   include '../../../database/db.php';
   
   // Lấy danh sách bài học từ cơ sở dữ liệu
   $stmt = $conn->prepare("SELECT b.MaBaiHoc, b.TenBai, b.NoiDungLyThuyet, b.DuongDanVideo, b.ThoiLuongVideo, c.TenChuong 
                            FROM baihoc b 
                            JOIN chuonghoc c ON b.MaChuong = c.MaChuong 
                            ORDER BY b.MaBaiHoc ASC");
   $stmt->execute();
   $baiHocList = $stmt->fetchAll();
   
   function formatDuration($seconds) {
       $hours = floor($seconds / 3600); // Lấy số giờ
       $minutes = floor(($seconds % 3600) / 60); // Lấy số phút
       $seconds = $seconds % 60; // Lấy số giây
   
       // Đảm bảo rằng giờ, phút, giây luôn có 2 chữ số
       return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
   }
   ?>
<!doctype html>
<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
   data-assets-path="../assets/" data-template="vertical-menu-template-free" data-style="light">
   <head>
      <meta charset="utf-8" />
      <meta name="viewport"
         content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
      <title>Quản lý bài học</title>
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
                     <h5 class="card-header">Danh sách các bài học</h5>
                     <div class="card-body">
                        <div class="table-responsive text-nowrap">
                           <table class="table table-bordered">
                              <thead>
                                 <tr>
                                    <th>Mã</th>
                                    <th>Chương</th>
                                    <th>Tên bài</th>
                                    <th>Lý thuyết</th>
                                    <th>Đường dẫn</th>
                                    <th>Thời Lượng Video</th>
                                    <th></th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <?php foreach ($baiHocList as $baiHoc): ?>
                                 <tr>
                                    <td><?php echo htmlspecialchars($baiHoc['MaBaiHoc']); ?></td>
                                    <td>
                                       <?php 
                                          $tenChuong = htmlspecialchars($baiHoc['TenChuong']);
                                          echo mb_substr($tenChuong, 0, 8) . (mb_strlen($tenChuong) > 8 ? '' : '');
                                          ?>
                                    </td>
                                    <td>
                                       <?php 
                                          $tenBai = htmlspecialchars($baiHoc['TenBai']);
                                          echo mb_substr($tenBai, 0,30) . (mb_strlen($tenBai) > 30 ? '...' : '');
                                          ?>
                                    </td>
                                    <td>
                                       <?php 
                                          $lyThuyet = htmlspecialchars($baiHoc['NoiDungLyThuyet']);
                                          echo mb_substr($lyThuyet, 0, 20) . (mb_strlen($lyThuyet) > 20 ? '...' : ''); 
                                          ?>
                                    </td>
                                    <td>
                                       <?php 
                                          $duongDanVideo = htmlspecialchars($baiHoc['DuongDanVideo']);
                                          echo mb_substr($duongDanVideo, 0, 20) . (mb_strlen($duongDanVideo) > 20 ? '...' : ''); 
                                          ?>
                                    </td>
                                    <td>
                                       <?php 
                                          $thoiLuongVideo = htmlspecialchars($baiHoc['ThoiLuongVideo']);
                                          echo formatDuration($thoiLuongVideo);
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
                                                href="add_update_lessons.php?id=<?php echo $baiHoc['MaBaiHoc']; ?>"><i
                                                class="ri-pencil-line me-1"></i> Chỉnh sửa</a>
                                             <a class="dropdown-item" href="#"
                                                onclick="confirmDelete('<?php echo $baiHoc['MaBaiHoc']; ?>')"><i
                                                class="ri-delete-bin-6-line me-1"></i> Xóa</a>
                                          </div>
                                       </div>
                                    </td>
                                 </tr>
                                 <?php endforeach; ?>
                              </tbody>
                           </table>
                           <a href="add_update_lessons.php" class="btn btn-success mt-2">Thêm bài học</a>
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
                  Bạn có chắc chắn muốn xóa bài học này?
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                  <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
               </div>
            </div>
         </div>
      </div>
      <script>
         let maBaiHocToDelete; 
         
         function confirmDelete(maBaiHoc) {
             maBaiHocToDelete = maBaiHoc; // Lưu mã câu hỏi vào biến toàn cục
             const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
             deleteModal.show(); // Hiển thị modal
         }
         
         // Xử lý khi nhấn nút xóa trong modal
         document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
             window.location.href = 'delete.php?id=' + maBaiHocToDelete + '&table=baihoc&location=lessons_manage.php&idColumn=MaBaiHoc';
         });
      </script>
   </body>
   </body>
</html>