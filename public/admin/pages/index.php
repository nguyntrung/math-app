<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
include 'C:\xampp\htdocs\math-app\database\db.php';

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
                  Main
                  <?php include 'footer.php'; ?>
               </div>
               <!-- Content wrapper -->
            </div>
         </div>
         <div class="layout-overlay layout-menu-toggle"></div>
      </div>
      <?php include 'other.php'; ?>
   </body>
</html>