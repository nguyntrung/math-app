<?php
// Kiểm tra người dùng có đăng ký VIP đang hoạt động không
$hasActiveVIP = false;
$vipType = null;
if (isset($_SESSION['MaNguoiDung'])) {
    try {
        $stmt = $conn->prepare("SELECT LoaiDangKy 
                               FROM dangkythanhvien 
                               WHERE MaNguoiDung = :userID 
                               AND TrangThai = 'DANG_HOAT_DONG' 
                               AND NgayKetThuc >= CURDATE()
                               ORDER BY NgayKetThuc DESC 
                               LIMIT 1");
        $stmt->execute(['userID' => $_SESSION['MaNguoiDung']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $hasActiveVIP = true;
            $vipType = $result['LoaiDangKy'];
        }
    } catch (PDOException $e) {
        error_log("Error checking VIP status: " . $e->getMessage());
    }
}
?>

<!-- Navbar Start -->
<div class="container-fluid position-relative shadow" style="background: linear-gradient(135deg, #e0f7ff 0%, #fff0f9 100%);">
    <nav class="navbar navbar-expand-lg navbar-light py-3 py-lg-0 px-0 px-lg-5">
        <a href="../public/" class="navbar-brand font-weight-bold" style="font-size: 28px;">
            <span style="background: linear-gradient(45deg, #4a90e2, #9b51e0); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">ToanLop5.vn</span>
        </a>
        <button type="button" class="navbar-toggler rounded-circle" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
            <div class="navbar-nav font-weight-bold mx-auto py-0">
                <a href="../public/" class="nav-item nav-link active rounded-pill px-4 mx-1 hover-scale">
                    <i class="fas fa-home mr-2"></i>Trang chủ</a>
                <a href="../public/video_lessons.php" class="nav-item nav-link rounded-pill px-4 mx-1 hover-scale">
                    <i class="fas fa-play-circle mr-2"></i>Vào học</a>
                <a href="../public/theory_lessons.php" class="nav-item nav-link rounded-pill px-4 mx-1 hover-scale">
                    <i class="fas fa-book-open mr-2"></i>Học lý thuyết</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle rounded-pill px-4 mx-1 hover-scale" data-toggle="dropdown">
                        <i class="fas fa-pencil-alt mr-2"></i>Làm bài tập</a>
                    <div class="dropdown-menu rounded-lg border-0 shadow-lg">
                        <a href="../public/essay.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-edit mr-2"></i>Bài tập tự luận</a>
                        <a href="../public/quiz.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-check-circle mr-2"></i>Trắc nghiệm vui</a>
                        <a href="../public/solutions.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-book mr-2"></i>Giải bài tập SGK</a>
                        <a href="../public/quiz_check.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-clock mr-2"></i>Kiểm tra</a>
                        <a href="../public/quiz_check_45.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fa-solid fa-trophy mr-2"></i>Thi đấu</a>
                        <a href="../public/exercise.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-redo mr-2"></i>Ôn tập</a>
                    </div>
                </div>
                <a href="contact.php" class="nav-item nav-link rounded-pill px-4 mx-1 hover-scale">
                    <i class="fas fa-phone mr-2"></i>Liên hệ</a>
                <a href="about.php" class="nav-item nav-link rounded-pill px-4 mx-1 hover-scale">
                    <i class="fas fa-users mr-2"></i>Về chúng tôi</a>
            </div>
            <?php if (isset($_SESSION['HoTen'])): ?>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle rounded-circle text-primary p-2" data-toggle="dropdown">
                        <div class="position-relative d-inline-block">
                            <img src="../assets/img/2.png" alt="Avatar" class="h-auto rounded-circle" style="width: 35px; border: 2px solid #fff;" />
                            <?php if ($hasActiveVIP): ?>
                            <div class="position-absolute" style="top: -5px; right: -5px; width: 45px; height: 45px; pointer-events: none;">
                                <!-- Hiệu ứng lấp lánh -->
                                <div class="position-absolute w-100 h-100 animate-sparkle" style="background: radial-gradient(circle at center, rgba(255,215,0,0.2) 0%, transparent 70%);"></div>
                                
                                <!-- SVG Border với animation -->
                                <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0;">
                                    <defs>
                                        <linearGradient id="vipBorder" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#FFD700">
                                                <animate attributeName="stop-color" 
                                                        values="#FFD700;#FFA500;#FFD700" 
                                                        dur="2s" 
                                                        repeatCount="indefinite" />
                                            </stop>
                                            <stop offset="100%" style="stop-color:#FFA500">
                                                <animate attributeName="stop-color" 
                                                        values="#FFA500;#FFD700;#FFA500" 
                                                        dur="2s" 
                                                        repeatCount="indefinite" />
                                            </stop>
                                        </linearGradient>
                                    </defs>
                                    <circle cx="50" cy="50" r="48" fill="none" stroke="url(#vipBorder)" stroke-width="4">
                                        <animate attributeName="stroke-dasharray" 
                                                values="0 300;300 0" 
                                                dur="8s" 
                                                repeatCount="indefinite" />
                                    </circle>
                                    <circle cx="50" cy="50" r="46" fill="none" stroke="#fff" stroke-width="2" opacity="0.5" />
                                </svg>
                                
                                <!-- VIP Badge -->
                                <div class="position-absolute" style="top: -8px; right: -8px;">
                                    <?php if ($vipType == 'NAM'): ?>
                                    <span class="d-flex align-items-center justify-content-center" 
                                        style="background: linear-gradient(45deg, #FFD700, #FFA500); 
                                                border-radius: 12px;
                                                padding: 2px 6px;
                                                font-size: 10px;
                                                color: white;
                                                font-weight: bold;
                                                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                                                text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                        VIP PRO
                                    </span>
                                    <?php else: ?>
                                    <span class="d-flex align-items-center justify-content-center" 
                                        style="background: linear-gradient(45deg, #FFD700, #FFA500);
                                                border-radius: 50%;
                                                padding: 2px 4px;
                                                font-size: 10px;
                                                color: white;
                                                font-weight: bold;
                                                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                                                text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                        <i class="fa-solid fa-crown"></i>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right rounded-lg border-0 shadow-lg">
                        <span class="dropdown-item disabled font-weight-bold">
                            <i class="fas fa-smile mr-2"></i><?php echo htmlspecialchars($_SESSION['HoTen']); ?>
                        </span>
                        <div class="dropdown-divider"></div>
                        <a href="profile.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-id-card mr-2 text-success"></i>Thông tin cá nhân</a>
                        <a href="registermember.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-graduation-cap me-2" style="color: #e46356"></i>Khóa học của bạn</a>
                        <a href="edit_profile.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-key me-2" style="color: #6587ff"></i>Đổi mật khẩu</a>
                        <a href="logout.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-sign-out-alt me-2" style="color: #687187"></i>Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary px-4 rounded-pill hover-scale">
                    <i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập</a>
            <?php endif; ?>
        </div>
    </nav>
</div>
<!-- Navbar End -->

<style>
    /* Thêm vào phần CSS của bạn */
    @keyframes sparkle {
        0% {
            transform: scale(1);
            opacity: 0.5;
        }
        50% {
            transform: scale(1.2);
            opacity: 0.8;
        }
        100% {
            transform: scale(1);
            opacity: 0.5;
        }
    }

    .animate-sparkle {
        animation: sparkle 2s infinite;
        border-radius: 50%;
    }

    /* Thêm hiệu ứng hover cho avatar */
    .nav-link:hover .position-relative img {
        transform: scale(1.1);
        transition: transform 0.3s ease;
    }

    /* Thêm box-shadow cho badge */
    .nav-link .position-absolute span {
        transition: all 0.3s ease;
    }

    .nav-link:hover .position-absolute span {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .hover-scale {
        transition: transform 0.2s ease;
    }

    .hover-scale:hover {
        transform: scale(1.05);
    }

    .navbar-nav .nav-link {
        transition: all 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.5);
    }

    .dropdown-menu {
        border-radius: 15px;
        padding: 10px;
    }

    .dropdown-item {
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: #e8f4ff;
        transform: translateX(5px);
    }

    @media (max-width: 991.98px) {
        .navbar-nav .nav-link {
            margin: 5px 0;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: none;
            padding: 0 15px;
        }
    }
</style>