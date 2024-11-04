<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Lấy số thứ tự bài tập từ tham số URL, mặc định là 1
$currentOrder = isset($_GET['order']) ? (int)$_GET['order'] : 1;

// Lấy bài tập theo thứ tự
$stmt = $conn->prepare("SELECT * FROM baitapkeoso WHERE ThuTu = :thutu");
$stmt->bindParam(':thutu', $currentOrder);
$stmt->execute();
$baitap = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy tổng số bài tập
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM baitapkeoso");
$stmt->execute();
$totalExercises = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Nếu không còn bài tập tiếp theo
$isLastExercise = $currentOrder >= $totalExercises;

// Tạo mảng các số và xáo trộn
$numbers = [$baitap['So1'], $baitap['So2'], $baitap['So3'], $baitap['So4'], $baitap['So5']];
shuffle($numbers);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ôn tập</title>

    <?php include '../includes/styles.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

    <style>
        .draggable-number {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .draggable-number:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .dropzone {
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .dropzone.drag-over {
            background-color: rgba(255, 215, 0, 0.2);
            transform: scale(1.05);
        }

        .dropzone.filled {
            border-style: solid;
            background-color: rgba(255, 215, 0, 0.1);
        }

        .dropzone .draggable-number {
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }

        .success-animation {
            animation: bounce 1s;
        }

        /* Thêm hiệu ứng shake khi trả lời sai */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .error-animation {
            animation: shake 0.5s;
        }
    </style>


</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container pt-5">
        <div class="pb-5">
            <h4 class="text-center mb-4" style="color: #ff6347;">Bài tập vui - Kéo và thả đúng vị trí</h4>
            
            <div class="game-container bg-white p-4 rounded-lg shadow" style="min-height: 400px;">
                <!-- Hiển thị số thứ tự bài tập -->
                <div class="text-right mb-3">
                    <span class="badge badge-pill badge-primary">Bài <?php echo $currentOrder; ?>/<?php echo $totalExercises; ?></span>
                </div>

                <!-- Khu vực câu hỏi -->
                <div class="question-area mb-4 text-center">
                    <p id="notification" class="notification text-center" style="color: #ff6347; font-weight: bold;"></p>
                    <h5 style="color: #4a90e2;">Hãy kéo 2 số vào ô trống sao cho tổng bằng <?php echo htmlspecialchars($baitap['KetQua']); ?>!</h5>
                    <div class="math-problem d-flex justify-content-center align-items-center my-4" style="font-size: 2rem;">
                        <div class="dropzone mx-2" style="width: 60px; height: 60px; border: 3px dashed #ffd700; border-radius: 10px;"></div>
                        <span class="mx-2"><?php echo htmlspecialchars($baitap['PhepToan']); ?></span>
                        <div class="dropzone mx-2" style="width: 60px; height: 60px; border: 3px dashed #ffd700; border-radius: 10px;"></div>
                        <span class="mx-2">=</span>
                        <span><?php echo htmlspecialchars($baitap['KetQua']); ?></span>
                    </div>
                </div>

                <!-- Khu vực chứa các số có thể kéo -->
                <div class="numbers-container d-flex justify-content-center flex-wrap" style="gap: 20px;">
                    <?php foreach ($numbers as $index => $number): ?>
                        <div class="draggable-number d-flex justify-content-center align-items-center" 
                            style="width: 50px; height: 50px; 
                                    background: linear-gradient(135deg, 
                                        <?php 
                                        $colors = [
                                            ['#ff9a9e', '#fad0c4'],
                                            ['#a1c4fd', '#c2e9fb'],
                                            ['#ffecd2', '#fcb69f'],
                                            ['#84fab0', '#8fd3f4'],
                                            ['#a6c1ee', '#fbc2eb']
                                        ];
                                        echo $colors[$index][0] . ' 0%, ' . $colors[$index][1] . ' 100%';
                                        ?>
                                    ); 
                                    border-radius: 10px; cursor: move; font-size: 1.5rem; color: white; 
                                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);" 
                            draggable="true">
                            <?php echo htmlspecialchars($number); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Khu vực phản hồi -->
                <div class="feedback-area text-center mt-4">
                    <button id="checkAnswer" class="btn btn-success btn-lg mb-3" style="background-color: #ff6347; border: none;">
                        Kiểm tra đáp án
                    </button>
                    
                    <?php if (!$isLastExercise): ?>
                    <div id="nextButton" style="display: none;">
                        <a href="?order=<?php echo $currentOrder + 1; ?>" class="btn btn-primary">
                            Bài tiếp theo
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($isLastExercise): ?>
                    <div id="finishButton" style="display: none;">
                        <a href="theory_lessons.php" class="btn btn-primary btn-lg">
                            Hoàn thành bài tập
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Hiệu ứng confetti khi trả lời đúng -->
                <div id="confetti" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1000;"></div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>

    <?php include '../includes/scripts.php'; ?>
    <script src="../assets/js/main.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const draggables = document.querySelectorAll('.draggable-number');
        const dropzones = document.querySelectorAll('.dropzone');
        let isAnswerCorrect = false;
        
        // Kéo thả xử lý
        draggables.forEach(draggable => {
            draggable.addEventListener('dragstart', function(e) {
                draggable.classList.add('dragging');
                e.dataTransfer.setData('text/plain', draggable.textContent.trim());
                e.dataTransfer.setData('text/html', draggable.outerHTML);
                
                // Nếu số đang ở trong dropzone, xóa nó khỏi dropzone
                if (draggable.parentElement.classList.contains('dropzone')) {
                    setTimeout(() => {
                        draggable.parentElement.classList.remove('filled');
                        draggable.remove();
                    }, 0);
                }
            });

            draggable.addEventListener('dragend', function() {
                draggable.classList.remove('dragging');
            });
        });

        dropzones.forEach(dropzone => {
            dropzone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropzone.classList.add('drag-over');
            });

            dropzone.addEventListener('dragleave', function() {
                dropzone.classList.remove('drag-over');
            });

            dropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropzone.classList.remove('drag-over');
                
                // Nếu dropzone đã có số, không cho thả thêm
                if (dropzone.hasChildNodes()) {
                    return;
                }
                
                const html = e.dataTransfer.getData('text/html');
                dropzone.innerHTML = html;
                dropzone.classList.add('filled');
                
                // Khôi phục lại các sự kiện cho số được thả
                const newDraggable = dropzone.querySelector('.draggable-number');
                if (newDraggable) {
                    newDraggable.addEventListener('dragstart', function(e) {
                        e.dataTransfer.setData('text/plain', this.textContent.trim());
                        e.dataTransfer.setData('text/html', this.outerHTML);
                        setTimeout(() => {
                            dropzone.classList.remove('filled');
                            this.remove();
                        }, 0);
                    });
                }
            });
        });

        // Kiểm tra đáp án
        document.getElementById('checkAnswer').addEventListener('click', function() {
            if (isAnswerCorrect) return; // Prevent multiple checks after correct answer

            const filledDropzones = document.querySelectorAll('.dropzone.filled');
            if (filledDropzones.length === 2) {
                const num1 = parseInt(filledDropzones[0].textContent);
                const num2 = parseInt(filledDropzones[1].textContent);
                const targetResult = <?php echo $baitap['KetQua']; ?>;
                const operator = '<?php echo $baitap['PhepToan']; ?>';

                let isCorrect = false;
                if (operator === '+') {
                    isCorrect = (num1 + num2 === targetResult);
                } else if (operator === '-') {
                    isCorrect = (num1 - num2 === targetResult);
                } else if (operator === '×') {
                    isCorrect = (num1 * num2 === targetResult);
                } else if (operator === '/') {
                    isCorrect = (num2 !== 0 && num1 / num2 === targetResult); // Kiểm tra chia cho 0
                }

                if (isCorrect) {
                    isAnswerCorrect = true;
                    // Hiệu ứng confetti
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 }
                    });

                    // Animation bounce
                    filledDropzones.forEach(zone => {
                        zone.classList.add('success-animation');
                    });

                    // Show next/finish button
                    const nextButton = document.getElementById('nextButton');
                    const finishButton = document.getElementById('finishButton');
                    if (nextButton) nextButton.style.display = 'block';
                    if (finishButton) finishButton.style.display = 'block';

                    notification.textContent = 'Chúc mừng! Bạn đã trả lời đúng! 🎉';
                } else {
                    // Thêm hiệu ứng rung lắc khi sai
                    filledDropzones.forEach(zone => {
                        zone.classList.add('error-animation');
                        setTimeout(() => {
                            zone.classList.remove('error-animation');
                            zone.innerHTML = '';
                            zone.classList.remove('filled');
                        }, 500);
                    });
                    
                    notification.textContent = 'Hãy thử lại nhé! 😊';
                }
            } else {
                notification.textContent = 'Hãy điền đầy đủ hai số vào ô trống nhé! 😊';
            }
        });
    });
    </script>

</body>
</html>
