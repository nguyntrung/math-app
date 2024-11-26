<?php
   session_start();
   
   if (!isset($_SESSION['MaNguoiDung'])) {
       header('Location: login.php');
       exit();
   }
   
   include '../database/db.php';
   include '../includes/styles.php';
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Về Chúng Tôi</title>
      <style>
         .about-us-container {
         max-width: 1200px;
         margin: 0 auto;
         padding: 20px;
         font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
         }
         .hero-section {
         text-align: center;
         padding: 60px 20px;
         background: linear-gradient(135deg, #6CD4FF 0%, #1C77FF 100%);
         border-radius: 20px;
         color: white;
         margin-bottom: 40px;
         box-shadow: 0 10px 20px rgba(0,0,0,0.1);
         }
         .hero-section h1 {
         font-size: 3rem;
         margin-bottom: 20px;
         color: white;
         }
         .hero-text {
         max-width: 800px;
         margin: 0 auto;
         font-size: 1.2rem;
         line-height: 1.6;
         }
         .content-section {
         display: grid;
         gap: 30px;
         padding: 20px;
         }
         .mission-card,
         .importance-card,
         .method-card {
         background: white;
         padding: 30px;
         border-radius: 15px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.05);
         transition: transform 0.3s ease;
         }
         .mission-card:hover,
         .importance-card:hover,
         .method-card:hover {
         transform: translateY(-5px);
         }
         .card-icon {
         font-size: 2.5rem;
         margin-bottom: 20px;
         text-align: center;
         }
         h2 {
         color: #1C77FF;
         margin-bottom: 20px;
         font-size: 1.8rem;
         }
         p {
         color: #666;
         line-height: 1.6;
         margin-bottom: 0;
         }
         .benefits-section {
         background: #f8f9fa;
         padding: 40px;
         border-radius: 20px;
         margin-top: 30px;
         }
         .benefits-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
         gap: 30px;
         margin-top: 30px;
         }
         .benefit-item {
         background: white;
         padding: 30px;
         border-radius: 15px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.05);
         transition: transform 0.3s ease;
         }
         .benefit-item:hover {
         transform: translateY(-5px);
         }
         .benefit-icon {
         font-size: 2rem;
         margin-bottom: 15px;
         text-align: center;
         }
         .benefit-item h3 {
         color: #1C77FF;
         margin-bottom: 15px;
         font-size: 1.3rem;
         }
         @media (max-width: 768px) {
         .hero-section {
         padding: 40px 20px;
         }
         .hero-section h1 {
         font-size: 2.5rem;
         }
         .hero-text {
         font-size: 1rem;
         }
         .benefits-grid {
         grid-template-columns: 1fr;
         }
         .content-section {
         gap: 20px;
         }
         }
      </style>
   </head>
   <body>
      <?php include '../includes/navbar.php'; ?>
      <!-- Main Start -->
      <div class="container-fluid pt-5" style="background-color: #f0f0e6;">
        <div class="about-us-container">
            <div class="hero-section">
                <h1>Về Chúng Tôi</h1>
                <div class="hero-text">
                Chào mừng đến với trang web học toán lớp 5! Chúng tôi là một nhóm các nhà giáo dục và chuyên gia toán học đam mê, với mục tiêu mang đến cho học sinh những công cụ và tài liệu hỗ trợ tốt nhất để nâng cao kiến thức và kỹ năng toán học.
                </div>
            </div>
            <div class="content-section">
                <div class="mission-card">
                <div class="card-icon">🎯</div>
                <h2>Sứ Mệnh Của Chúng Tôi</h2>
                <p>Sứ mệnh của chúng tôi là giúp các em học sinh lớp 5 nắm vững kiến thức toán học cơ bản, từ đó xây dựng nền tảng vững chắc cho các bậc học cao hơn. Chúng tôi tin rằng mọi học sinh đều có thể học tốt môn toán khi được hỗ trợ đúng cách và có tài liệu phù hợp.</p>
                </div>
                <div class="importance-card">
                <div class="card-icon">🌟</div>
                <h2>Lý Do Học Toán Quan Trọng</h2>
                <p>Toán học không chỉ là môn học cần thiết trong nhà trường mà còn là kỹ năng quan trọng trong cuộc sống hàng ngày. Việc hiểu và áp dụng các khái niệm toán học giúp trẻ phát triển tư duy logic, khả năng giải quyết vấn đề, và cải thiện kỹ năng phân tích dữ liệu.</p>
                </div>
                <div class="method-card">
                <div class="card-icon">📚</div>
                <h2>Phương Pháp Giảng Dạy Của Chúng Tôi</h2>
                <p>Chúng tôi áp dụng phương pháp học tập tương tác, sử dụng các bài giảng sinh động và các bài tập thực hành nhằm khơi gợi hứng thú học toán cho học sinh. Các bài học được thiết kế để học sinh dễ dàng hiểu và áp dụng vào thực tế, từ những khái niệm cơ bản đến nâng cao.</p>
                </div>
                <div class="benefits-section">
                <div class="card-icon">💎</div>
                <h2>Lợi Ích Khi Học Toán Trên Trang Web Của Chúng Tôi</h2>
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <div class="benefit-icon">🎓</div>
                        <h3>Tiếp cận nội dung miễn phí</h3>
                        <p>Cung cấp nhiều tài liệu và bài giảng miễn phí, giúp học sinh có thể học mọi lúc, mọi nơi.</p>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">📝</div>
                        <h3>Bài tập đa dạng</h3>
                        <p>Cung cấp hệ thống bài tập phong phú từ dễ đến khó, phù hợp với mọi trình độ của học sinh lớp 5.</p>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">📊</div>
                        <h3>Tự đánh giá và theo dõi tiến độ</h3>
                        <p>Giúp học sinh tự kiểm tra kiến thức của mình qua các bài kiểm tra và theo dõi quá trình học tập.</p>
                    </div>
                </div>
                </div>
            </div>
        </div>
      </div>
      <!-- Main End -->
      <?php include '../includes/footer.php'; ?>
      <!-- Back to Top -->
      <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa-solid fa-up-long"></i></a>
      <?php include '../includes/scripts.php'; ?>
   </body>
</html>