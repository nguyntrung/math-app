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
                           <!-- <p class="small mb-0"><span class="h6 mb-0">Tổng mức tăng trưởng <?php echo $transactionStats['growth_percentage']; ?>%</span> 😎 trong tháng này</p> -->
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
                                                <img src="data:image/png;base64,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" alt="Avatar" class="rounded-circle" />
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
                                          Hoạt động
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