<?php
// Kết nối với database
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "quanlyhoctoan";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form và xử lý ký tự đặc biệt
    $ho_ten = htmlspecialchars(trim($_POST["ho_ten"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $lop = htmlspecialchars(trim($_POST["lop"]));
    $noi_dung = htmlspecialchars(trim($_POST["noi_dung"]));

    // Kiểm tra tính hợp lệ của dữ liệu
    if (!empty($ho_ten) && !empty($email) && !empty($noi_dung)) {
        
        $sql = "INSERT INTO lienhe (ho_ten, email, lop, noi_dung) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $ho_ten, $email, $lop, $noi_dung);

        if ($stmt->execute()) {
            echo "Cảm ơn bạn đã liên hệ! Chúng tôi sẽ sớm liên lạc lại với bạn.";
        } else {
            echo "Đã xảy ra lỗi khi gửi tin nhắn: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Vui lòng điền đầy đủ thông tin.";
    }
} else {
    echo "Phương thức truy cập không hợp lệ.";
}

$conn->close();

?>