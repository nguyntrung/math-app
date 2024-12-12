<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}
// Kết nối cơ sở dữ liệu
include '../../../database/db.php';

// Lấy danh sách người dùng từ cơ sở dữ liệu
$stmt = $conn->prepare("SELECT MaNguoiDung, HoTen, TenDangNhap, Email, VaiTro, TrangThaiHoatDong FROM nguoidung ORDER BY HoTen ASC");
$stmt->execute();
$nguoiDungList = $stmt->fetchAll();

// Function to get transaction statistics
function getTransactionStatistics($conn) {
   // Total sales quantity
   $salesQuery = "SELECT COUNT(*) AS total_sales FROM thanhtoan WHERE TrangThai = 'THANH_CONG'";
   $salesStmt = $conn->prepare($salesQuery);
   $salesStmt->execute();
   $salesResult = $salesStmt->fetch(PDO::FETCH_ASSOC);
   $totalSales = $salesResult['total_sales'];

   // Total customers
   $customerQuery = "SELECT COUNT(DISTINCT dk.MaNguoiDung) AS total_customers FROM dangkythanhvien dk JOIN thanhtoan tt ON dk.MaDangKy = tt.MaDangKy WHERE tt.TrangThai = 'THANH_CONG'";
   $customerStmt = $conn->prepare($customerQuery);
   $customerStmt->execute();
   $customerResult = $customerStmt->fetch(PDO::FETCH_ASSOC);
   $totalCustomers = $customerResult['total_customers'];

   // Total courses
   $courseQuery = "SELECT COUNT(DISTINCT MaDangKy) AS total_courses FROM dangkythanhvien";
   $courseStmt = $conn->prepare($courseQuery);
   $courseStmt->execute();
   $courseResult = $courseStmt->fetch(PDO::FETCH_ASSOC);
   $totalCourses = $courseResult['total_courses'];

   // Total revenue
   $revenueQuery = "SELECT SUM(SoTien) AS total_revenue FROM thanhtoan WHERE TrangThai = 'THANH_CONG'";
   $revenueStmt = $conn->prepare($revenueQuery);
   $revenueStmt->execute();
   $revenueResult = $revenueStmt->fetch(PDO::FETCH_ASSOC);
   $totalRevenue = $revenueResult['total_revenue'];

   // Calculate growth percentage (example calculation)
   $previousMonthRevenueQuery = "
       SELECT SUM(SoTien) AS previous_month_revenue 
       FROM thanhtoan 
       WHERE TrangThai = 'THANH_CONG' 
       AND MONTH(NgayThanhToan) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
       AND YEAR(NgayThanhToan) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
   ";
   $previousMonthStmt = $conn->prepare($previousMonthRevenueQuery);
   $previousMonthStmt->execute();
   $previousMonthResult = $previousMonthStmt->fetch(PDO::FETCH_ASSOC);
   $previousMonthRevenue = $previousMonthResult['previous_month_revenue'] ?? 0;

   $growthPercentage = $previousMonthRevenue > 0 
       ? round((($totalRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
       : 0;

   return [
       'total_sales' => number_format($totalSales),
       'total_customers' => number_format($totalCustomers),
       'total_courses' => number_format($totalCourses),
       'total_revenue' => number_format($totalRevenue, 2),
       'growth_percentage' => $growthPercentage
   ];
}

// Use the function in your existing code
$transactionStats = getTransactionStatistics($conn);

?>

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
      <title>Quản lý bài học</title>
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
                  <div class="row gy-6">
                     <!-- Transactions -->
                     <div class="col-lg-12">
                        <div class="card h-100">
                        <div class="card-header">
                           <div class="d-flex align-items-center justify-content-between">
                              <h5 class="card-title m-0 me-2">Thống kê</h5>
                              <div class="dropdown">
                              <button
                                 class="btn text-muted p-0"
                                 type="button"
                                 id="transactionID"
                                 data-bs-toggle="dropdown"
                                 aria-haspopup="true"
                                 aria-expanded="false">
                                 <i class="ri-more-2-line ri-24px"></i>
                              </button>
                              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                                 <a class="dropdown-item" href="javascript:void(0);" id="day">Ngày</a>
                                 <a class="dropdown-item" href="javascript:void(0);" id="month">Tháng</a>
                                 <a class="dropdown-item" href="javascript:void(0);" id="year">Năm</a>
                                 <a class="dropdown-item" href="javascript:void(0);" id="total">Tổng</a>
                              </div>
                              </div>
                           </div>
                           <p class="small mb-0"><span class="h6 mb-0">Tổng mức tăng trưởng <?php echo $transactionStats['growth_percentage']; ?>%</span> 😎 trong tháng này</p>
                        </div>
                        <div class="card-body pt-lg-10">
                           <div class="row g-6">
                              <div class="col-md-3 col-6">
                              <div class="d-flex align-items-center">
                                 <div class="avatar">
                                    <div class="avatar-initial bg-primary rounded shadow-xs">
                                    <i class="ri-pie-chart-2-line ri-24px"></i>
                                    </div>
                                 </div>
                                 <div class="ms-3">
                                    <p class="mb-0">Số lượng bán ra</p>
                                    <h5 class="mb-0"><?php echo $transactionStats['total_sales']; ?></h5>
                                 </div>
                              </div>
                              </div>
                              <div class="col-md-3 col-6">
                              <div class="d-flex align-items-center">
                                 <div class="avatar">
                                    <div class="avatar-initial bg-success rounded shadow-xs">
                                    <i class="ri-group-line ri-24px"></i>
                                    </div>
                                 </div>
                                 <div class="ms-3">
                                    <p class="mb-0">Khách hàng</p>
                                    <h5 class="mb-0"><?php echo $transactionStats['total_customers']; ?></h5>
                                 </div>
                              </div>
                              </div>
                              <div class="col-md-3 col-6">
                              <div class="d-flex align-items-center">
                                 <div class="avatar">
                                    <div class="avatar-initial bg-warning rounded shadow-xs">
                                    <i class="ri-macbook-line ri-24px"></i>
                                    </div>
                                 </div>
                                 <div class="ms-3">
                                    <p class="mb-0">Khóa học</p>
                                    <h5 class="mb-0"><?php echo $transactionStats['total_courses']; ?></h5>
                                 </div>
                              </div>
                              </div>
                              <div class="col-md-3 col-6">
                              <div class="d-flex align-items-center">
                                 <div class="avatar">
                                    <div class="avatar-initial bg-info rounded shadow-xs">
                                    <i class="ri-money-dollar-circle-line ri-24px"></i>
                                    </div>
                                 </div>
                                 <div class="ms-3">
                                    <p class="mb-0">Doanh thu</p>
                                    <h5 class="mb-0"><?php echo $transactionStats['total_revenue']; ?></h5>
                                 </div>
                              </div>
                              </div>
                           </div>
                        </div>
                        </div>
                     </div>
                     <!--/ Transactions -->

                     <!-- Data Tables -->
                     <div class="col-12">
                        <div class="card overflow-hidden">
                        <div class="table-responsive">
                        <table class="table table-sm">
                           <thead>
                              <tr>
                                    <th class="text-truncate">Người dùng</th>
                                    <th class="text-truncate">Email</th>
                                    <th class="text-truncate">Vai trò</th>
                                    <th class="text-truncate">Trạng thái</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach ($nguoiDungList as $nguoiDung): ?>
                              <tr>
                                    <td>
                                       <div class="d-flex align-items-center">
                                          <div class="avatar avatar-sm me-4">
                                                <img src="../assets/img/avatars/<?php echo $nguoiDung['MaNguoiDung']; ?>.png" alt="Avatar" class="rounded-circle" />
                                          </div>
                                          <div>
                                                <h6 class="mb-0 text-truncate"><?php echo htmlspecialchars($nguoiDung['HoTen']); ?></h6>
                                                <small class="text-truncate"><?php echo htmlspecialchars($nguoiDung['TenDangNhap']); ?></small>
                                          </div>
                                       </div>
                                    </td>
                                    <td class="text-truncate"><?php echo htmlspecialchars($nguoiDung['Email']); ?></td>
                                    <td class="text-truncate">
                                       <div class="d-flex align-items-center">
                                          <span><?php echo htmlspecialchars($nguoiDung['VaiTro']); ?></span>
                                       </div>
                                    </td>
                                    <td>
                                       <span class="badge <?php echo ($nguoiDung['TrangThaiHoatDong'] == 'Active') ? 'bg-label-success' : 'bg-label-secondary'; ?> rounded-pill">
                                          <?php echo htmlspecialchars($nguoiDung['TrangThaiHoatDong']); ?>
                                       </span>
                                    </td>
                              </tr>
                              <?php endforeach; ?>
                           </tbody>
                        </table>
                        </div>
                        </div>
                     </div>
                     <!--/ Data Tables -->
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
         document.getElementById('day').addEventListener('click', function() {
            window.location.href = "index.php?time_period=day";
         });
         document.getElementById('month').addEventListener('click', function() {
            window.location.href = "index.php?time_period=month";
         });
         document.getElementById('year').addEventListener('click', function() {
            window.location.href = "index.php?time_period=year";
         });
         document.getElementById('total').addEventListener('click', function() {
            window.location.href = "index.php?time_period=total";
         });
      </script>
   </body>
</html>