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
$stmt = $conn->prepare("SELECT * FROM cauhoiontap WHERE ThuTu = :thutu");
$stmt->bindParam(':thutu', $currentOrder);
$stmt->execute();
$baitap = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy tổng số bài tập
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM cauhoiontap");
$stmt->execute();
$totalExercises = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Nếu không còn bài tập tiếp theo
$isLastExercise = $currentOrder >= $totalExercises;

// Tạo mảng các số và xáo trộn
$numbers = [$baitap['So1'], $baitap['So2'], $baitap['So3'], $baitap['So4'], $baitap['So5']];
shuffle($numbers);

// Quyết định ngẫu nhiên loại bài tập (0: phép toán, 1: sắp xếp, 2: nối cột)
$exerciseType = rand(0, 2);

// Nếu là bài sắp xếp, sắp xếp lại mảng để có đáp án
$sortedNumbers = $numbers;
sort($sortedNumbers);

if ($exerciseType == 2) {
    // Lấy dữ liệu bài nối cột
    $stmt = $conn->prepare("SELECT * FROM noicot ORDER BY RAND() LIMIT 5");
    $stmt->execute();
    $matchingPairs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Xáo trộn danh sách các cặp câu hỏi và câu trả lời
    shuffle($matchingPairs);

    // Tách dữ liệu thành hai cột
    $leftItems = [];
    $rightItems = [];

    foreach ($matchingPairs as $index => $pair) {
        $leftItems[] = ['id' => $index, 'question' => $pair['CauHoi']];
        $rightItems[] = ['id' => $index, 'answer' => $pair['CauTraLoi']];
    }

    // Xáo trộn thứ tự hiển thị của cột phải (câu trả lời)
    shuffle($rightItems);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ôn tập</title>

    <?php include '../includes/styles.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

    <style>
    .draggable-number {
        transition: transform 0.2s, box-shadow 0.2s;
        user-select: none;
    }

    .draggable-number:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .dropzone {
        transition: all 0.3s ease;
        display: flex;
        justify-content: center;
        align-items: center;
        min-width: 60px;
        min-height: 60px;
    }

    .dropzone.drag-over {
        background-color: rgba(255, 215, 0, 0.2);
        transform: scale(1.05);
    }

    .dropzone.filled {
        border-style: solid;
        background-color: rgba(255, 215, 0, 0.1);
    }

    .sorting-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 20px 0;
    }

    .sorting-dropzone {
        width: 60px;
        height: 60px;
        border: 3px dashed #ffd700;
        border-radius: 10px;
    }

    @keyframes bounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-20px);
        }

        60% {
            transform: translateY(-10px);
        }
    }

    .success-animation {
        animation: bounce 1s;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-10px);
        }

        75% {
            transform: translateX(10px);
        }
    }

    .error-animation {
        animation: shake 0.5s;
    }

    .list-group-item {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container pt-5">
        <div class="pb-5">
            <h4 class="text-center mb-4" style="color: #ff6347;">Bài tập vui - Kéo và thả đúng vị trí</h4>

            <div class="game-container bg-white p-4 rounded-lg shadow" style="min-height: 400px;">
                <div class="text-right mb-3">
                    <span class="badge badge-pill badge-primary">Bài
                        <?php echo $currentOrder; ?>/<?php echo $totalExercises; ?></span>
                </div>

                <div class="question-area mb-4 text-center">
                    <p id="notification" class="notification text-center" style="color: #ff6347; font-weight: bold;">
                    </p>

                    <?php if ($exerciseType == 0): // Bài tập phép toán ?>
                    <div id="math-exercise">
                        <h5 style="color: #4a90e2;">Hãy kéo 2 số vào ô trống sao cho kết quả bằng <?php echo htmlspecialchars($baitap['KetQua']); ?>!</h5>
                        <div class="math-problem d-flex justify-content-center align-items-center my-4" style="font-size: 2rem;">
                            <div class="dropzone mx-2" style="width: 60px; height: 60px; border: 3px dashed #ffd700; border-radius: 10px;"></div>
                            <span class="mx-2"><?php echo htmlspecialchars($baitap['PhepToan']); ?></span>
                            <div class="dropzone mx-2" style="width: 60px; height: 60px; border: 3px dashed #ffd700; border-radius: 10px;"></div>
                            <span class="mx-2">=</span>
                            <span><?php echo htmlspecialchars($baitap['KetQua']); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($exerciseType == 1): // Bài tập sắp xếp ?>
                    <div id="sorting-exercise">
                        <h5 style="color: #4a90e2;">Hãy sắp xếp các số theo thứ tự tăng dần!</h5>
                        <div class="sorting-container">
                            <?php for($i = 0; $i < 5; $i++): ?>
                            <div class="sorting-dropzone dropzone"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($exerciseType == 2): // Bài tập nối cột ?>
                    <div id="matching-exercise">
                        <h5 style="color: #4a90e2;">Hãy nối các câu hỏi ở cột trái với câu trả lời tương ứng ở cột phải!
                        </h5>
                        <div class="row">
                            <!-- Cột câu hỏi -->
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <?php foreach ($leftItems as $item): ?>
                                    <li class="list-group-item" draggable="true" data-id="<?php echo $item['id']; ?>">
                                        <?php echo htmlspecialchars($item['question']); ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <?php foreach ($rightItems as $item): ?>
                                    <li class="list-group-item dropzone" style="cursor: pointer; min-height: 40px;"
                                        data-id="<?php echo $item['id']; ?>">
                                        <?php echo htmlspecialchars($item['answer']); ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Hiển thị dãy số chỉ khi không phải bài tập nối cột -->
                <?php if ($exerciseType != 2): ?>
                <div class="numbers-container d-flex justify-content-center flex-wrap"
                    style="gap: 20px; min-height: 70px;">
                    <?php foreach ($numbers as $index => $number): ?>
                    <div class="draggable-number d-flex justify-content-center align-items-center original-number"
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
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);" draggable="true"
                        data-value="<?php echo htmlspecialchars($number); ?>">
                        <?php echo htmlspecialchars($number); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="feedback-area text-center mt-4">
                    <button id="checkAnswer" class="btn btn-success btn-lg mb-3"
                        style="background-color: #ff6347; border: none;">
                        Kiểm tra đáp án
                    </button>

                    <div id="retryButton" style="display: none;">
                        <button class="btn btn-warning btn-lg">Thử lại</button>
                    </div>

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

                <div id="confetti"
                    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1000;">
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>
    <?php include '../includes/scripts.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const draggables = document.querySelectorAll('.draggable-number');
        const dropzones = document.querySelectorAll('.dropzone');
        const exerciseType = <?php echo $exerciseType; ?>;
        const sortedNumbers = <?php echo json_encode($sortedNumbers); ?>;
        let isAnswerCorrect = false;

        // Tạo đối tượng âm thanh
        const correctSound = new Audio('../assets/sounds/correct.mp3');
        const wrongSound = new Audio('../assets/sounds/wrong.mp3');

        // Drag and drop handling
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
                        // Hiện lại số gốc tương ứng
                        const value = draggable.dataset.value;
                        const originalNumber = document.querySelector(
                            `.original-number[data-value="${value}"]`);
                        if (originalNumber) {
                            originalNumber.style.display = 'flex';
                        }
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
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const draggedElement = tempDiv.firstChild;
                const value = draggedElement.dataset.value;

                // Ẩn số gốc tương ứng
                const originalNumber = document.querySelector(
                    `.original-number[data-value="${value}"]`);
                if (originalNumber) {
                    originalNumber.style.display = 'none';
                }

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
                            // Hiện lại số gốc khi kéo ra khỏi dropzone
                            const value = this.dataset.value;
                            const originalNumber = document.querySelector(
                                `.original-number[data-value="${value}"]`);
                            if (originalNumber) {
                                originalNumber.style.display = 'flex';
                            }
                        }, 0);
                    });
                }
            });
        });

        // Kiểm tra đáp án
        document.getElementById('checkAnswer').addEventListener('click', function() {
            if (isAnswerCorrect) return;

            const filledDropzones = document.querySelectorAll('.dropzone.filled');

            if (exerciseType === 0) { // Math exercise
                if (filledDropzones.length === 2) {
                    const num1 = parseFloat(filledDropzones[0].textContent);
                    const num2 = parseFloat(filledDropzones[1].textContent);
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
                        isCorrect = (num2 !== 0 && num1 / num2 === targetResult);
                    }

                    handleAnswer(isCorrect, filledDropzones);
                } else {
                    notification.textContent = 'Hãy điền đầy đủ hai số vào ô trống nhé! 😊';
                }
            } else { // Sorting exercise
                if (filledDropzones.length === 5) {
                    const currentNumbers = Array.from(filledDropzones).map(zone =>
                        parseInt(zone.querySelector('.draggable-number').dataset.value)
                    );

                    const isCorrect = currentNumbers.every((num, index) =>
                        num === parseInt(sortedNumbers[index])
                    );

                    handleAnswer(isCorrect, filledDropzones);
                } else {
                    notification.textContent = 'Hãy điền đầy đủ các số vào ô trống nhé! 😊';
                }
            }
        });

        function handleAnswer(isCorrect, dropzones) {
            const retryButton = document.getElementById('retryButton');

            if (isCorrect) {
                isAnswerCorrect = true;
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: {
                        y: 0.6
                    }
                });

                // Phát âm thanh khi trả lời đúng
                correctSound.play();

                dropzones.forEach(zone => {
                    zone.classList.add('success-animation');
                });

                const nextButton = document.getElementById('nextButton');
                const finishButton = document.getElementById('finishButton');
                if (nextButton) nextButton.style.display = 'block';
                if (finishButton) finishButton.style.display = 'block';

                notification.textContent = 'Chúc mừng! Bạn đã trả lời đúng! 🎉';
            } else {
                // Phát âm thanh khi trả lời sai
                wrongSound.play();

                dropzones.forEach(zone => {
                    zone.classList.add('error-animation');
                    setTimeout(() => {
                        zone.classList.remove('error-animation');
                        zone.innerHTML = '';
                        zone.classList.remove('filled');
                    }, 500);
                });

                notification.textContent = 'Hãy thử lại nhé! 😊';
                
                // Hiển thị nút "Thử lại"
                retryButton.style.display = 'block';
            }
        }
        //Xử lý sự kiện nút "Thử Lại"       
        document.getElementById('retryButton').addEventListener('click', function () {
            window.location.reload();
        });


        if (exerciseType === 2) { // Nối cột
            const draggableItems = document.querySelectorAll('.list-group-item[draggable="true"]');
            const dropZones = document.querySelectorAll('.dropzone');
            const pairs = {};

            draggableItems.forEach(item => {
                item.addEventListener('dragstart', function(e) {
                    e.dataTransfer.setData('text/plain', e.target.dataset.id);
                });
            });

            dropZones.forEach(zone => {
                zone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    zone.classList.add('drag-over');
                });

                zone.addEventListener('dragleave', function() {
                    zone.classList.remove('drag-over');
                });

                zone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    zone.classList.remove('drag-over');

                    const sourceId = e.dataTransfer.getData('text/plain');
                    const targetId = zone.dataset.id;

                    // Nếu đã có một đáp án ở dropzone, không cho thả thêm
                    if (zone.classList.contains('filled')) return;

                    // Lấy nội dung của câu hỏi
                    const draggedItem = document.querySelector(
                        `.list-group-item[data-id="${sourceId}"]`);
                    if (draggedItem) {
                        zone.textContent = draggedItem.textContent;
                        zone.classList.add('filled');
                        draggedItem.style.display = 'none';

                        // Lưu cặp nối
                        pairs[sourceId] = targetId;
                    }
                });
            });

            // Nối cột: Kiểm tra đáp án
            document.getElementById('checkAnswer').addEventListener('click', function() {
                const notification = document.getElementById('notification');
                let isCorrect = true;

                // Duyệt qua tất cả các dropzone
                dropZones.forEach(zone => {
                    const draggedId = Object.keys(pairs).find(key => pairs[key] === zone.dataset
                        .id);

                    // Nếu không tìm thấy cặp đúng hoặc cặp không khớp, đánh dấu sai
                    if (!draggedId || draggedId !== zone.dataset.id) {
                        isCorrect = false;
                    }
                });

                // Hiển thị kết quả
                if (isCorrect) {
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: {
                            y: 0.6
                        }
                    });
                    correctSound.play();
                    notification.textContent = 'Chúc mừng! Bạn đã nối đúng! 🎉';
                    document.getElementById('nextButton').style.display = 'block';
                } else {
                    wrongSound.play();
                    notification.textContent = 'Bạn đã nối sai. Thử lại nhé! 😊';
                    
                    // Hiển thị nút "Thử lại"
                    retryButton.style.display = 'block';
                }
            });
        }
    });
    </script>
</body>

</html>