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
    $hoten = htmlspecialchars(trim($_POST["HoTen"]));
    $email = htmlspecialchars(trim($_POST["Email"]));
    $noidung = htmlspecialchars(trim($_POST["NoiDung"]));

    // Kiểm tra tính hợp lệ của dữ liệu
    if (!empty($hoten) && !empty($email) && !empty($noidung)) {
        
        $sql = "INSERT INTO lienhe (HoTen, Email, NoiDung) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $hoten, $email, $noidung);

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