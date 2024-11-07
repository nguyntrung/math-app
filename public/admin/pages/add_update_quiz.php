<?php
session_start();
require '../../../vendor/autoload.php'; // Require Composer's autoloader
use PhpOffice\PhpSpreadsheet\IOFactory;

// Kiểm tra đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
include '../../../database/db.php';

// Khởi tạo biến cho các trường
$maCauHoi = '';
$noiDung = '';
$dapAnA = '';
$dapAnB = '';
$dapAnC = '';
$dapAnD = '';
$dapAnDung = '';
$tenBai = '';
$giaiThich = '';
$errorMessage = '';
$successMessage = '';

// Function để import câu hỏi từ Excel
function importExcelQuestions($file, $maBaiHoc, $conn) {
    try {
        $inputFileType = IOFactory::identify($file['tmp_name']);
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        // Start transaction
        $conn->beginTransaction();

        // Prepare the insert statement
        $stmt = $conn->prepare("INSERT INTO cauhoitracnghiem 
            (MaBaiHoc, NoiDung, DapAnA, DapAnB, DapAnC, DapAnD, DapAnDung, GiaiThich) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $successCount = 0;
        $errorRows = [];

        // Skip header row, start from row 2
        for ($row = 2; $row <= $highestRow; $row++) {
            $noiDung = trim($worksheet->getCell('A' . $row)->getValue());
            $dapAnA = trim($worksheet->getCell('B' . $row)->getValue());
            $dapAnB = trim($worksheet->getCell('C' . $row)->getValue());
            $dapAnC = trim($worksheet->getCell('D' . $row)->getValue());
            $dapAnD = trim($worksheet->getCell('E' . $row)->getValue());
            $dapAnDung = strtoupper(trim($worksheet->getCell('F' . $row)->getValue()));
            $giaiThich = trim($worksheet->getCell('G' . $row)->getValue());            

            // Skip empty rows
            if (empty($noiDung) && empty($dapAnA) && empty($dapAnB) && empty($dapAnC) && empty($dapAnD)) {
                continue;
            }

            // Validate data
            if (empty($noiDung) || empty($dapAnA) || empty($dapAnB) || 
                empty($dapAnC) || empty($dapAnD) || empty($dapAnDung) || 
                !in_array($dapAnDung, ['A', 'B', 'C', 'D'])) {
                $errorRows[] = $row;
                continue;
            }

            try {
                $stmt->execute([
                    $maBaiHoc,
                    $noiDung,
                    $dapAnA,
                    $dapAnB,
                    $dapAnC,
                    $dapAnD,
                    $dapAnDung,
                    $giaiThich
                ]);
                $successCount++;
            } catch (PDOException $e) {
                $errorRows[] = $row;
            }
        }

        if (empty($errorRows)) {
            $conn->commit();
            return [
                'success' => true,
                'message' => "Đã import thành công $successCount câu hỏi.",
                'successCount' => $successCount
            ];
        } else {
            $conn->rollBack();
            return [
                'success' => false,
                'message' => "Lỗi ở các dòng: " . implode(", ", $errorRows),
                'errorRows' => $errorRows
            ];
        }
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        return [
            'success' => false,
            'message' => "Lỗi xử lý file Excel: " . $e->getMessage()
        ];
    }
}

// Kiểm tra xem có mã câu hỏi để chỉnh sửa không
if (isset($_GET['id'])) {
    $maCauHoi = $_GET['id'];
    
    // Lấy thông tin câu hỏi từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT c.NoiDung, c.DapAnA, c.DapAnB, c.DapAnC, c.DapAnD, c.DapAnDung, c.GiaiThich, bh.TenBai, c.MaBaiHoc 
                           FROM cauhoitracnghiem c 
                           JOIN baihoc bh ON c.MaBaiHoc = bh.MaBaiHoc 
                           WHERE c.MaCauHoi = ?");
    $stmt->execute([$maCauHoi]);
    $cauHoi = $stmt->fetch();
    
    if ($cauHoi) {
        $noiDung = $cauHoi['NoiDung'];
        $dapAnA = $cauHoi['DapAnA'];
        $dapAnB = $cauHoi['DapAnB'];
        $dapAnC = $cauHoi['DapAnC'];
        $dapAnD = $cauHoi['DapAnD'];
        $dapAnDung = $cauHoi['DapAnDung'];
        $giaiThich = $cauHoi['GiaiThich'];
        $tenBai = $cauHoi['TenBai'];
        $selectedMaBaiHoc = $cauHoi['MaBaiHoc'];
    } else {
        $errorMessage = 'Câu hỏi không tồn tại!';
    }
}

// Xử lý POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Xử lý import Excel
    if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] == 0) {
        $allowedTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/octet-stream'
        ];
        
        if (in_array($_FILES['excelFile']['type'], $allowedTypes)) {
            $result = importExcelQuestions($_FILES['excelFile'], $_POST['maBaiHoc'], $conn);
            if ($result['success']) {
                $successMessage = $result['message'];
            } else {
                $errorMessage = $result['message'];
            }
        } else {
            $errorMessage = 'Vui lòng chọn file Excel hợp lệ (.xls hoặc .xlsx)';
        }
    } 
    // Xử lý form thông thường
    else {
        $noiDung = $_POST['noiDung'] ?? '';
        $dapAnA = $_POST['dapAnA'] ?? '';
        $dapAnB = $_POST['dapAnB'] ?? '';
        $dapAnC = $_POST['dapAnC'] ?? '';
        $dapAnD = $_POST['dapAnD'] ?? '';
        $dapAnDung = $_POST['dapAnDung'] ?? '';
        $giaiThich = $_POST['giaiThich'] ?? '';
        $maBaiHoc = $_POST['maBaiHoc'] ?? '';

        if (empty($noiDung) || empty($dapAnA) || empty($dapAnB) || empty($dapAnC) || 
            empty($dapAnD) || empty($dapAnDung) || empty($giaiThich)) {
            $errorMessage = 'Vui lòng điền tất cả các trường.';
        } else {
            try {
                if ($maCauHoi) {
                    // Cập nhật câu hỏi
                    $stmt = $conn->prepare("UPDATE cauhoitracnghiem 
                                          SET NoiDung = ?, DapAnA = ?, DapAnB = ?, DapAnC = ?, 
                                              DapAnD = ?, DapAnDung = ?, GiaiThich = ?, MaBaiHoc = ? 
                                          WHERE MaCauHoi = ?");
                    $stmt->execute([$noiDung, $dapAnA, $dapAnB, $dapAnC, $dapAnD, 
                                  $dapAnDung, $giaiThich, $maBaiHoc, $maCauHoi]);
                    $successMessage = 'Cập nhật câu hỏi thành công!';
                } else {
                    // Thêm câu hỏi mới
                    $stmt = $conn->prepare("INSERT INTO cauhoitracnghiem 
                                          (NoiDung, DapAnA, DapAnB, DapAnC, DapAnD, DapAnDung, GiaiThich, MaBaiHoc) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$noiDung, $dapAnA, $dapAnB, $dapAnC, $dapAnD, 
                                  $dapAnDung, $giaiThich, $maBaiHoc]);
                    $successMessage = 'Thêm câu hỏi thành công!';
                }
            } catch (PDOException $e) {
                $errorMessage = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        }
    }
}

// Lấy danh sách bài học để hiển thị trong dropdown
$stmt = $conn->prepare("SELECT bh.MaBaiHoc, bh.TenBai, ch.TenChuong 
                       FROM baihoc bh 
                       JOIN chuonghoc ch ON bh.MaChuong = ch.MaChuong 
                       ORDER BY ch.ThuTu, bh.ThuTu");
$stmt->execute();
$baiHocList = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="../assets/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Quản lý câu hỏi trắc nghiệm</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/vendor/fonts/remixicon/remixicon.css" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/node-waves/node-waves.css" />
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include 'sidebar.php'; ?>
            <div class="layout-page">
                <?php include 'navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="card">
                            <h5 class="card-header">
                                <?php echo $maCauHoi ? 'Chỉnh sửa câu hỏi' : 'Thêm câu hỏi'; ?>
                            </h5>
                            <div class="card-body">
                                <?php if ($errorMessage): ?>
                                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                                <?php endif; ?>
                                <?php if ($successMessage): ?>
                                <div class="alert alert-success"><?php echo $successMessage; ?></div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch" 
                                                   id="flexSwitchCheckDefault">
                                            <label class="form-check-label" for="flexSwitchCheckDefault">
                                                Nhập từ excel
                                            </label>
                                        </div>

                                        <label for="maBaiHoc" class="form-label">Bài học</label>
                                        <select class="form-select" id="maBaiHoc" name="maBaiHoc" required>
                                            <?php foreach ($baiHocList as $baiHoc): ?>
                                            <option value="<?php echo htmlspecialchars($baiHoc['MaBaiHoc']); ?>"
                                                <?php echo (isset($selectedMaBaiHoc) && $selectedMaBaiHoc == $baiHoc['MaBaiHoc']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($baiHoc['TenBai']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Excel upload section -->
                                    <div id="excelUploadSection" style="display: none;" class="mb-3">
                                        <label for="excelFile" class="form-label">Chọn file Excel</label>
                                        <input type="file" class="form-control" id="excelFile" name="excelFile" 
                                               accept=".xlsx, .xls">
                                        <small class="text-muted">Hỗ trợ file Excel (.xlsx, .xls)</small>
                                        <div class="mt-3">
                                            <a href="../template/mau_import_cauhoi.xlsx" class="btn btn-outline-primary btn-sm">
                                                <i class="ri-download-2-line"></i> Tải file mẫu
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Manual input section -->
                                    <div class="form-input-manual">
                                        <div class="mb-3">
                                            <label for="noiDung" class="form-label">Nội dung câu hỏi</label>
                                            <input type="text" class="form-control" id="noiDung" name="noiDung"
                                                value="<?php echo htmlspecialchars($noiDung); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="dapAnA" class="form-label">Đáp án A</label>
                                            <input type="text" class="form-control" id="dapAnA" name="dapAnA"
                                                value="<?php echo htmlspecialchars($dapAnA); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="dapAnB" class="form-label">Đáp án B</label>
                                            <input type="text" class="form-control" id="dapAnB" name="dapAnB"
                                                value="<?php echo htmlspecialchars($dapAnB); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="dapAnC" class="form-label">Đáp án C</label>
                                            <input type="text" class="form-control" id="dapAnC" name="dapAnC"
                                                value="<?php echo htmlspecialchars($dapAnC); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="dapAnD" class="form-label">Đáp án D</label>
                                            <input type="text" class="form-control" id="dapAnD" name="dapAnD"
                                                value="<?php echo htmlspecialchars($dapAnD); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="dapAnDung" class="form-label">Đáp án đúng</label>
                                            <select class="form-control" id="dapAnDung" name="dapAnDung">
                                                <option value="A" <?php if ($dapAnDung === 'A') echo 'selected'; ?>>A</option>
                                                <option value="B" <?php if ($dapAnDung === 'B') echo 'selected'; ?>>B</option>
                                                <option value="C" <?php if ($dapAnDung === 'C') echo 'selected'; ?>>C</option>
                                                <option value="D" <?php if ($dapAnDung === 'D') echo 'selected'; ?>>D</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="giaiThich" class="form-label">Giải Thích</label>
                                            <textarea class="form-control" id="giaiThich" name="giaiThich" 
                                                      rows="4"><?php echo htmlspecialchars($giaiThich); ?></textarea>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <?php echo $maCauHoi ? 'Cập nhật' : 'Thêm mới'; ?>
                                    </button>
                                    <a href="quiz_manager.php" class="btn btn-secondary">Quay lại</a>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php include 'footer.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>

    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const switchElement = document.getElementById('flexSwitchCheckDefault');
            const formInputs = document.querySelectorAll('.form-input-manual');
            const excelUpload = document.getElementById('excelUploadSection');
            const requiredInputs = document.querySelectorAll('.form-input-manual input[required], .form-input-manual select[required], .form-input-manual textarea[required]');

            function toggleFormElements(isExcelMode) {
                formInputs.forEach(input => {
                    input.style.display = isExcelMode ? 'none' : 'block';
                });
                excelUpload.style.display = isExcelMode ? 'block' : 'none';
                
                // Toggle required attributes
                requiredInputs.forEach(input => {
                    if (isExcelMode) {
                        input.removeAttribute('required');
                    } else {
                        input.setAttribute('required', 'required');
                    }
                });
            }

            switchElement.addEventListener('change', function() {
                toggleFormElements(this.checked);
            });

            // Initialize form state
            toggleFormElements(switchElement.checked);
        });
    </script>
</body>
</html>