<?php
   session_start();
   
   if (!isset($_SESSION['MaNguoiDung'])) {
       header('Location: login.php');
       exit();
   }
   
   include '../database/db.php';
   
   if (!isset($_SESSION['startTime'])) {
       $_SESSION['startTime'] = time();
   }
      
   // Truy vấn bảng xếp hạng (dựa trên điểm số và thời gian thi)
   $stmtRanking = $conn->prepare("SELECT nguoidung.HoTen, ketqua.Diem, ketqua.NgayThi 
                                           FROM ketqua 
                                           JOIN nguoidung ON ketqua.MaNguoiDung = nguoidung.MaNguoiDung
                                           ORDER BY ketqua.Diem DESC, ketqua.NgayThi ASC");
   $stmtRanking->execute();
   $rankingList = $stmtRanking->fetchAll(PDO::FETCH_ASSOC);
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>Bảng xếp hạng</title>
      <?php include '../includes/styles.php'; ?>
      <style>
         .leaderboard-container {
         min-width: 1000px;
         margin: 0 auto;
         padding: 20px;
         }
         .top-header {
         text-align: center;
         margin-bottom: 40px;
         }
         .top-badge {
         display: inline-block;
         background: rgba(255, 255, 255, 0.9);
         padding: 8px 24px;
         border-radius: 20px;
         font-weight: bold;
         font-size: 1.2rem;
         }
         .podium-section {
         display: flex;
         justify-content: center;
         align-items: flex-end;
         margin-bottom: 40px;
         gap: 20px;
         }
         .podium-place {
         display: flex;
         flex-direction: column;
         align-items: center;
         position: relative;
         }
         .avatar-container {
         position: relative;
         margin-bottom: 10px;
         }
         .avatar {
         width: 80px;
         height: 80px;
         border-radius: 50%;
         overflow: hidden;
         border: 3px solid white;
         box-shadow: 0 4px 8px rgba(0,0,0,0.1);
         }
         .avatar-img {
         width: 100%;
         height: 100%;
         object-fit: cover;
         }
         .crown {
         position: absolute;
         top: -20px;
         left: 50%;
         transform: translateX(-50%);
         font-size: 24px;
         animation: float 2s ease-in-out infinite;
         }
         .rank-circle {
         position: absolute;
         bottom: -5px;
         right: -5px;
         width: 25px;
         height: 25px;
         background: white;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         font-weight: bold;
         box-shadow: 0 2px 4px rgba(0,0,0,0.1);
         }
         .user-info {
         text-align: center;
         margin-bottom: 10px;
         }
         .user-name {
         font-size: 1.1rem;
         font-weight: bold;
         margin: 5px 0;
         }
         .school-name {
         font-size: 0.9rem;
         color: #666;
         margin: 0;
         }
         .podium-block {
         width: 120px;
         display: flex;
         align-items: center;
         justify-content: center;
         border-radius: 10px 10px 0 0;
         position: relative;
         }
         .p1 {
         height: 120px;
         background: linear-gradient(45deg, #FFD700, #FFA500);
         }
         .p2 {
         height: 90px;
         background: linear-gradient(45deg, #C0C0C0, #A9A9A9);
         }
         .p3 {
         height: 60px;
         background: linear-gradient(45deg, #CD7F32, #B8860B);
         }
         .other-rankings {
         max-width: 600px;
         margin: 0 auto;
         background: white;
         border-radius: 15px;
         padding: 20px;
         box-shadow: 0 4px 8px rgba(0,0,0,0.1);
         }
         .rank-item {
         display: flex;
         align-items: center;
         padding: 15px;
         border-bottom: 1px solid #eee;
         }
         .rank-item:last-child {
         border-bottom: none;
         }
         .rank-number {
         width: 30px;
         font-weight: bold;
         text-align: center;
         color: #666;
         }
         .rank-avatar {
         width: 40px;
         height: 40px;
         border-radius: 50%;
         overflow: hidden;
         margin: 0 15px;
         }
         .rank-avatar img {
         width: 100%;
         height: 100%;
         object-fit: cover;
         }
         .rank-info {
         flex: 1;
         }
         .rank-name {
         margin: 0;
         font-weight: bold;
         }
         .rank-school {
         margin: 0;
         font-size: 0.9rem;
         color: #666;
         }
         @keyframes float {
         0% { transform: translateX(-50%) translateY(0); }
         50% { transform: translateX(-50%) translateY(-10px); }
         100% { transform: translateX(-50%) translateY(0); }
         }
      </style>
   </head>
   <body>
      <?php include '../includes/navbar.php'; ?>
      <div class="container-fluid" style="background-color: #f0f0e6;">
         <div class="container" style="background-color: #f0f0e6;">
            <div class="leaderboard-container">
               <div class="top-header">
                  <div class="top-badge">Top 10 - Khối 5</div>
               </div>
               <div class="podium-section">
                  <!-- Second Place -->
                  <div class="podium-place place-2">
                     <div class="avatar-container">
                        <div class="rank-circle">2</div>
                        <div class="avatar">
                           <img src="../assets/img/avt2.png" alt="Second place" class="avatar-img">
                        </div>
                     </div>
                     <div class="user-info">
                        <h3 class="user-name"><?= htmlspecialchars($rankingList[1]['HoTen'] ?? 'Chưa có'); ?></h3>
                        <p class="school-name">toan5.vn</p>
                     </div>
                     <div class="podium-block p2">
                        <span class="rank-number text-danger"><?= htmlspecialchars($rankingList[1]['Diem'] ?? 'Chưa có'); ?> điểm</span>
                     </div>
                  </div>
                  <!-- First Place -->
                  <div class="podium-place place-1">
                     <div class="avatar-container">
                        <div class="crown">👑</div>
                        <div class="avatar">
                           <img src="../assets/img/avt1.png" alt="First place" class="avatar-img">
                        </div>
                        <div class="rank-circle">1</div>
                     </div>
                     <div class="user-info">
                        <h3 class="user-name"><?= htmlspecialchars($rankingList[0]['HoTen'] ?? 'Chưa có'); ?></h3>
                        <p class="school-name">toan5.vn</p>
                     </div>
                     <div class="podium-block p1">
                        <span class="rank-number text-danger"><?= htmlspecialchars($rankingList[0]['Diem'] ?? 'Chưa có'); ?> điểm</span>
                     </div>
                  </div>
                  <!-- Third Place -->
                  <div class="podium-place place-3">
                     <div class="avatar-container">
                        <div class="rank-circle">3</div>
                        <div class="avatar">
                           <img src="../assets/img/avt3.png" alt="Third place" class="avatar-img">
                        </div>
                     </div>
                     <div class="user-info">
                        <h3 class="user-name"><?= htmlspecialchars($rankingList[2]['HoTen'] ?? 'Chưa có'); ?></h3>
                        <p class="school-name">toan5.vn</p>
                     </div>
                     <div class="podium-block p3">
                        <span class="rank-number text-danger"><?= htmlspecialchars($rankingList[2]['Diem'] ?? 'Chưa có'); ?> điểm</span>
                     </div>
                  </div>
               </div>
               <!-- Rest of rankings -->
               <div class="other-rankings">
                  <?php for($i = 3; $i < min(count($rankingList), 10); $i++): ?>
                  <div class="rank-item">
                     <div class="rank-number"><?= $i + 1 ?></div>
                     <div class="rank-avatar">
                        <img src="../assets/img/avt4.png" alt="Avatar">
                     </div>
                     <div class="rank-info">
                        <h5 class="rank-name"><?= htmlspecialchars($rankingList[$i]['HoTen']); ?></h5>
                        <p class="rank-school">toan5.vn</p>
                     </div>
                     <span class="rank-number text-danger"><?= htmlspecialchars($rankingList[$i]['Diem'] ?? 'Chưa có'); ?> điểm</span>
                  </div>
                  <?php endfor; ?>
               </div>
            </div>
         </div>
      </div>
      <?php include '../includes/footer.php'; ?>
      <!-- Back to Top -->
      <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>
      <?php include '../includes/scripts.php'; ?>
      <script src="../assets/js/main.js"></script>
   </body>
</html>