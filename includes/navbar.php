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
                    <i class="fas fa-play-circle mr-2"></i>Xem bài giảng</a>
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
                        <a href="../public/quiz_check_15.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-clock mr-2"></i>Kiểm tra 15 phút</a>
                        <a href="#" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-file-alt mr-2"></i>Kiểm tra 1 tiết</a>
                        <a href="../public/exercise.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-redo mr-2"></i>Ôn tập</a>
                    </div>
                </div>
                <a href="contact.html" class="nav-item nav-link rounded-pill px-4 mx-1 hover-scale">
                    <i class="fas fa-phone mr-2"></i>Liên hệ</a>
                <a href="about.php" class="nav-item nav-link rounded-pill px-4 mx-1 hover-scale">
                    <i class="fas fa-users mr-2"></i>Về chúng tôi</a>
            </div>
            <?php if (isset($_SESSION['HoTen'])): ?>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle rounded-circle text-primary p-2" data-toggle="dropdown">
                        <i class="fas fa-user"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right rounded-lg border-0 shadow-lg">
                        <span class="dropdown-item disabled font-weight-bold">
                            <i class="fas fa-smile mr-2"></i><?php echo htmlspecialchars($_SESSION['HoTen']); ?>
                        </span>
                        <div class="dropdown-divider"></div>
                        <a href="profile.php" class="dropdown-item py-2 px-4 hover-scale">
                            <i class="fas fa-id-card mr-2"></i>Hồ sơ của bạn</a>
                        <a href="logout.php" class="dropdown-item py-2 px-4 hover-scale text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất</a>
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