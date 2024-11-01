<!-- Navbar Start -->
<div class="container-fluid bg-light position-relative shadow">
    <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-0 px-lg-5">
        <a href="../public/" class="navbar-brand font-weight-bold text-secondary" style="font-size: 30px;">
            <i class="flaticon-043-teddy-bear"></i>
            <span class="text-primary">ToanLop5.vn</span>
        </a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
            <div class="navbar-nav font-weight-bold mx-auto py-0">
                <a href="../public/" class="nav-item nav-link active">Trang chủ</a>
                <a href="../public/video_lessons.php" class="nav-item nav-link">Bài giảng</a>
                <a href="../public/theory_lessons.php" class="nav-item nav-link">Lý thuyết</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Luyện tập</a>
                    <div class="dropdown-menu rounded-0 m-0">
                        <a href="../public/essay.php" class="dropdown-item">Bài tập tự luận</a>
                        <a href="../public/quiz.php" class="dropdown-item">Bài tập trắc nghiệm</a>
                        <a href="../public/solutions.php" class="dropdown-item">Giải bài tập SGK</a>
                    </div>
                </div>
                <a href="contact.html" class="nav-item nav-link">Liên hệ</a>
                <a href="about.html" class="nav-item nav-link">Về chúng tôi</a>
            </div>
            <?php if (isset($_SESSION['HoTen'])): ?>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="fa-solid fa-user"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <span class="dropdown-item disabled"><?php echo htmlspecialchars($_SESSION['HoTen']); ?></span>
                        <div class="dropdown-divider"></div>
                        <a href="profile.php" class="dropdown-item">Hồ sơ của bạn</a>
                        <a href="logout.php" class="dropdown-item text-danger">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary px-4">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </nav>
</div>
<!-- Navbar End -->
