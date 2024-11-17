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
    $stmtRanking = $conn->prepare("SELECT nguoidung.HoTen, ketquakiemtra.Diem, ketquakiemtra.NgayThi 
                                            FROM ketquakiemtra 
                                            JOIN nguoidung ON ketquakiemtra.MaNguoiDung = nguoidung.MaNguoiDung
                                            ORDER BY ketquakiemtra.Diem DESC, ketquakiemtra.NgayThi ASC");
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
            background: #fff;
            border-radius: 20px;
            margin-bottom: 20px;
            padding: 30px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .leaderboard-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            color: #4A90E2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .leaderboard-title i {
            color: #FFD700;
            margin-right: 10px;
        }

        .ranking-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            border: none;
        }

        .ranking-table thead {
            background: linear-gradient(45deg, #4A90E2, #67B26F);
            color: white;
        }

        .ranking-table th {
            padding: 15px;
            font-size: 1.1rem;
            text-align: center;
            border: none;
        }

        .ranking-table td {
            padding: 12px;
            font-size: 1.1rem;
            text-align: center;
            border: none;
            border-bottom: 1px solid #eee;
        }

        .ranking-table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            transition: all 1s;
        }

        .medal {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .rank-1 {
            background-color: rgba(255, 215, 0, 0.1);
        }

        .rank-2 {
            background-color: rgba(192, 192, 192, 0.1);
        }

        .rank-3 {
            background-color: rgba(205, 127, 50, 0.1);
        }

        .empty-message {
            text-align: center;
            padding: 30px;
            font-size: 1.2rem;
            color: #6c757d;
            background: #f8f9fa;
            border-radius: 15px;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }
    </style>

</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container pt-5">
        <div class="leaderboard-container">
            <h3 class="leaderboard-title">
                <i class="fa-solid fa-trophy"></i>
                Bảng Xếp Hạng Học Sinh
            </h3>

            <?php if (!empty($rankingList)): ?>
            <table class="table ranking-table">
                <thead>
                    <tr>
                        <th>Thứ tự</th>
                        <th>Tên học sinh</th>
                        <th>Điểm số</th>
                        <th>Ngày thi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rankingList as $index => $rank): ?>
                    <tr class="<?php echo ($index < 3) ? 'rank-'.($index+1) : ''; ?>">
                        <td>
                            <?php
                            if ($index === 0) echo '<span class="medal">🥇</span>';
                            else if ($index === 1) echo '<span class="medal">🥈</span>';
                            else if ($index === 2) echo '<span class="medal">🥉</span>';
                            else echo ($index + 1);
                            ?>
                        </td>
                        <td><?= htmlspecialchars($rank['HoTen']); ?></td>
                        <td>
                            <span style="color: #28a745; font-weight: bold;">
                                <?= $rank['Diem']; ?> điểm
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($rank['NgayThi'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-message">
                <i class="fa-solid fa-face-smile mb-3" style="font-size: 3rem; color: #6c757d;"></i>
                <p>Chưa có bạn nào làm bài kiểm tra. Hãy là người đầu tiên nhé!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>

</html>