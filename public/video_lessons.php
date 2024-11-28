<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

try {
    // Kiểm tra trạng thái đăng ký thành viên
    $isActiveMember = false;
    $stmt = $conn->prepare("
        SELECT COUNT(*) as active_count 
        FROM dangkythanhvien 
        WHERE MaNguoiDung = :maNguoiDung 
        AND TrangThai = 'DANG_HOAT_DONG' 
        AND NgayKetThuc >= CURRENT_DATE()
    ");
    $stmt->bindParam(':maNguoiDung', $_SESSION['MaNguoiDung']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['active_count'] > 0) {
        $isActiveMember = true;
    }

    // Lấy danh sách chương học
    $stmt = $conn->prepare("
        SELECT 
            ChuongHoc.MaChuong, 
            ChuongHoc.TenChuong, 
            ChuongHoc.MienPhi,
            ChuongHoc.ThuTu,
            COUNT(BaiHoc.MaBaiHoc) as SoBaiHoc,
            (
                SELECT COUNT(*)
                FROM tiendohoctap t
                JOIN BaiHoc b ON t.MaBaiHoc = b.MaBaiHoc
                WHERE b.MaChuong = ChuongHoc.MaChuong
                AND t.MaNguoiDung = :maNguoiDung
            ) as BaiHocHoanThanh
        FROM ChuongHoc
        LEFT JOIN BaiHoc ON ChuongHoc.MaChuong = BaiHoc.MaChuong
        GROUP BY ChuongHoc.MaChuong
        ORDER BY ChuongHoc.ThuTu ASC
    ");
    $stmt->bindParam(':maNguoiDung', $_SESSION['MaNguoiDung']);
    $stmt->execute();
    $chuongData = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kho Báu Kiến Thức</title>

    <?php include '../includes/styles.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --background-color: #f0f0e6;
            --text-color: #333;
        }

        body {
            margin: 0;
            background-color: var(--background-color);
        }

        .learning-container {
            display: flex;
            gap: 20px;
            padding: 20px 200px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Left Sidebar Styles */
        .chapters-sidebar {
            flex: 0 0 300px;
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .chapter-search {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            margin-bottom: 15px;
        }

        .chapter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .chapter-tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            background: #eee;
            border-radius: 20px;
            cursor: pointer;
        }

        .chapter-tab.active {
            background: var(--primary-color);
            color: white;
        }

        .chapter-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .chapter-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .chapter-item:hover {
            background: #e9ecef;
        }

        .chapter-item.active {
            background: #e3f2fd;
            border-left: 4px solid var(--secondary-color);
        }

        .chapter-details {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }

        .chapter-item[data-free="0"] {
            position: relative;
            opacity: 0.8;
        }

        .premium-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .chapter-item[data-free="0"]:not(.active):hover {
            cursor: pointer;
            background: #f8f9fa;
            opacity: 1;
        }

        /* Right Content Area Styles */
        .lessons-content {
            flex: 1;
        }

        .progress-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .progress-title {
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            color: white;
            margin: 0 5px;
            transition: background-color 0.3s ease;
        }

        .video-button {
            background-color: #b67ddb;
        }

        .theory-button {
            background-color: #55c57a;
        }

        .action-button:hover {
            opacity: 0.9;
            text-decoration: none;
            color: white;
        }

        .progress-stats {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .progress-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .lessons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .lessons-grid.two-columns {
            grid-template-columns: repeat(2, 1fr);
        }

        .lesson-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .lesson-image {
            width: 100%;
            height: 190px;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .lesson-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .lesson-content {
            padding: 15px;
        }

        .lesson-title {
            font-size: 1.1em;
            margin-bottom: 10px;
        }

        .lesson-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .action-button {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-size: 0.9em;
        }

        .progress-button {
            background: #e9ecef;
            color: var(--text-color);
        }

        .practice-button {
            background: var(--primary-color);
            color: white;
        }

        .video-button {
            background: var(--secondary-color);
            color: white;
        }

        /* Progress Icons */
        .progress-status {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .status-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
        }

        .not-started {
            border: 2px solid #ddd;
        }

        .in-progress {
            background: #ffd700;
            color: white;
        }

        .completed {
            background: #4CAF50;
            color: white;
        }

        /* Mobile and Tablet Responsive Adjustments */
        @media screen and (max-width: 1200px) {
            .learning-container {
                padding: 20px 50px;
            }
        }

        @media screen and (max-width: 992px) {
            .learning-container {
                flex-direction: column;
                padding: 20px;
            }

            .chapters-sidebar {
                flex: 0 0 100%;
                margin-bottom: 20px;
            }

            .lessons-content {
                width: 100%;
            }

            .lessons-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media screen and (max-width: 768px) {
            .progress-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .progress-stats {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                width: 100%;
            }

            .progress-legend {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .progress-legend span {
                margin-right: 10px;
                margin-bottom: 5px;
            }

            .lessons-grid {
                grid-template-columns: 1fr;
            }

            .chapter-search {
                font-size: 16px; /* Prevent zoom on mobile */
            }
        }

        @media screen and (max-width: 576px) {
            .learning-container {
                padding: 10px;
            }

            .lesson-card .lesson-actions {
                gap: 5px;
            }

            .lesson-actions a {
                width: 100%;
                text-align: center;
                padding: 10px;
            }
            .lessons-grid.two-columns {
                grid-template-columns: repeat(1, 1fr);
            }
        }

        /* Additional Responsive Adjustments */
        .learning-container {
            max-width: 1000px;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }

        /* Ensure images are responsive */
        .lesson-image img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        /* Improve touch targets for mobile */
        @media (max-width: 768px) {
            .chapter-item, 
            .lesson-actions a {
                min-height: 50px;
                display: flex;
                align-items: center;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid pt-4">
        <div class="learning-container">
            <!-- Left Sidebar -->
            <div class="chapters-sidebar">
                <h5 class="text-center text-primary">DANH SÁCH CHƯƠNG</h5>
                <input type="text" class="chapter-search" placeholder="Tìm nhanh kỹ năng...">
                <div class="chapter-list">
                    <?php if (!empty($chuongData)): ?>
                        <?php foreach ($chuongData as $chuong): ?>
                        <div class="chapter-item <?= isset($_GET['maChuong']) && $_GET['maChuong'] === $chuong['MaChuong'] ? 'active' : '' ?>" 
                            data-chapter="<?= $chuong['MaChuong'] ?>"
                            data-free="<?= $chuong['MienPhi'] ?>"
                            onclick="handleChapterClick('<?= $chuong['MaChuong'] ?>', <?= $chuong['MienPhi'] ?>, <?= $isActiveMember ? 'true' : 'false' ?>)">
                            <div class="chapter-title fw-bold"><?= htmlspecialchars($chuong['TenChuong']) ?></div>
                                <div class="chapter-details">
                                    Chủ điểm: <?= $chuong['SoBaiHoc'] ?>
                                    <?php if ($chuong['ThuTu'] > 1 && !$isActiveMember): ?>
                                        <span class="premium-badge"><i class="fas fa-lock text-warning"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-message">Chưa có chương học nào</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Content Area -->
            <div class="lessons-content">
                <div class="progress-header">
                    <div class="progress-title fw-bold" id="chapterTitle"></div>
                    <div class="progress-stats">
                        <div class="progress-indicator">
                            <span id="completedPoints">0</span>/<span id="totalPoints">0</span>
                        </div>
                        <div class="d-flex justify-content-end">
                            <div class="progress-legend">
                                <span class="mr-3"><i class="fa-solid fa-circle-dot" style="color: #c6cfe1"></i> Chưa hoàn thành</span>
                                <span class="mr-3"><i class="fa-solid fa-circle-dot" style="color: #03a9f4"></i> Đang hoàn thành</span>
                                <span class="mr-3"><i class="fa-regular fa-circle-check" style="color: #3bab60"></i> Đã hoàn thành</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lessons-grid" id="lessonsGrid">
                    <!-- Lessons will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>

    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
    function handleChapterClick(maChuong, mienPhi, isActiveMember) {
        // Nếu là chương miễn phí (chương 1) thì cho phép truy cập
        if (mienPhi === 1) {
            loadChapterLessons(maChuong);
            return;
        }
        
        // Nếu không phải chương miễn phí và không phải thành viên active
        if (!isActiveMember) {
            // window.location.href = 'registermember.php';
            // return;
            // Hiển thị popup xác nhận
            Swal.fire({
                title: 'Đăng Ký Thành Viên',
                text: 'Chương này yêu cầu bạn đăng ký thành viên. Bạn có muốn đăng ký không?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Đăng ký ngay',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Chuyển đến trang đăng ký thành viên
                    window.location.href = 'registermember.php';
                }
            });
            return;
        }
        
        // Nếu là thành viên active, cho phép truy cập
        loadChapterLessons(maChuong);
    }
    function loadChapterLessons(maChuong) {
    // Update active chapter
    document.querySelectorAll('.chapter-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-chapter="${maChuong}"]`).classList.add('active');

    // AJAX request to get chapter lessons
    fetch(`get_chapter_lessons.php?maChuong=${maChuong}`)
        .then(response => response.json())
        .then(data => {
            if (data.requiresMembership) {
                window.location.href = 'registermember.php';
                return;
            }
            
            const lessonsGrid = document.getElementById('lessonsGrid');
            document.getElementById('chapterTitle').textContent = data.chapterTitle;
            
            // Update progress indicators
            document.getElementById('completedPoints').textContent = data.completedLessons;
            document.getElementById('totalPoints').textContent = data.totalLessons;

                // Clear previous lessons
                lessonsGrid.innerHTML = '';

                // Check if there's only one lesson, if so, force 2-column layout
                if (data.lessons.length === 1) {
                    lessonsGrid.classList.add('two-columns');
                } else {
                    lessonsGrid.classList.remove('two-columns');
                }

                
                // Generate lesson cards
                lessonsGrid.innerHTML = data.lessons.map(lesson => `
                    <div class="lesson-card">
                        <div class="lesson-image">
                            <a href="video_lessons_detail.php?maBaiHoc=${lesson.MaBaiHoc}" class="lesson-image">
                                <img src="../assets/img/toan5edu.png" alt="Lesson">
                            </a>
                        </div>
                        <div class="lesson-content">
                            <div class="lesson-title">
                                <a href="video_lessons_detail.php?maBaiHoc=${lesson.MaBaiHoc}" class="text-decoration-none fw-bold text-success">
                                    ${lesson.TenBai}
                                </a>
                            </div>
                            <div class="lesson-actions d-flex justify-content-between align-items-center">
                                <a href="video_lessons_detail.php?maBaiHoc=${lesson.MaBaiHoc}" class="action-button video-button flex-grow-1">
                                    <i class="fa-solid fa-video"></i> Video
                                </a>
                                <a href="theory_lessons.php?maBaiHoc=${lesson.MaBaiHoc}" class="action-button theory-button flex-grow-1">
                                    <i class="fa-solid fa-book-open"></i> Lý thuyết
                                </a>
                            </div>
                            <div class="lesson-actions d-flex justify-content-between">
                                <a href="quiz_detail.php?maBaiHoc=${lesson.MaBaiHoc}" class="d-flex flex-column align-items-center text-decoration-none fw-bold text-body-secondary">
                                    <i class="fa-solid fa-circle-question" style="color: #b67ddb"></i> TNghiệm
                                </a>
                                <a href="essay_detail.php?maBaiHoc=${lesson.MaBaiHoc}" class="d-flex flex-column align-items-center text-decoration-none fw-bold text-body-secondary">
                                    <i class="fas fa-edit" style="color: #55c57a"></i>Tự luận
                                </a>
                                <a href="quizdetail.php?maBaiHoc=${lesson.MaBaiHoc}" class="d-flex flex-column align-items-center text-decoration-none fw-bold text-body-secondary">
                                    <i class="fas fa-tasks" style="color: #8b8b8b"></i>
                                    <span>Bài tập</span>
                                </a>
                                <a href="learning_progress.php" class="d-flex flex-column align-items-center text-decoration-none fw-bold text-body-secondary">
                                    <i class="fa-regular fa-circle-check" style="color: #3bab60"></i>
                                    <span>Tiến độ</span>
                                </a>
                            </div>
                        </div>
                    </div>
                `).join('');
                
                lessonsGrid.innerHTML += `
                    <div class="lesson-card">
                        <div class="lesson-image">
                            <img src="../assets/img/toan5edu.png" alt="Lesson">
                        </div>
                        <div class="lesson-title p-2 mb-0">
                            <p class="text-success m-0">
                                ${data.chapterTitle} - Kiểm tra
                            </p>
                        </div>
                        <div class="lesson-content">
                            <div class="lesson-actions d-flex justify-content-between align-items-center mt-0">
                                <a href="quiz_check.php?maChuong=${maChuong}" class="action-button theory-button">
                                    <i class="fas fa-pencil-alt"></i> Làm bài kiểm tra
                                </a>
                            </div>
                            <div class="text-center m-2">
                                <a href="history.php" class="text-decoration-none fw-bold" style="color: #55c57a" 
                                    onmouseover="this.style.color='#3e8e41'" 
                                    onmouseout="this.style.color='#55c57a'">
                                    Lịch sử kiểm tra
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });
    }

    // Load first chapter lessons by default
    document.addEventListener('DOMContentLoaded', () => {
        const firstChapter = document.querySelector('.chapter-item');
        if (firstChapter) {
            loadChapterLessons(firstChapter.dataset.chapter);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.querySelector('.chapter-search');
        const chapterItems = document.querySelectorAll('.chapter-item');

        searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();

            chapterItems.forEach(item => {
                const chapterTitle = item.querySelector('.chapter-title').textContent.toLowerCase();
                if (chapterTitle.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Add to existing script
    function adjustMobileLayout() {
        const isMobile = window.innerWidth <= 768;
        const lessonsGrid = document.getElementById('lessonsGrid');
        
        if (isMobile) {
            lessonsGrid.classList.remove('two-columns');
        }
    }

    window.addEventListener('resize', adjustMobileLayout);
    document.addEventListener('DOMContentLoaded', adjustMobileLayout);

    </script>
</body>
</html>
