<?php
session_start();

if (!isset($_SESSION['MaNguoiDung'])) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Trò Chơi Sudoku</title>
    <link rel="icon" href="{{ url_for('static', filename='favicon.ico') }}" />
    <?php include '../includes/styles.php';?>

    <style>
      body {
          background-color: #f0f4f8;
          justify-content: center;
          align-items: center;
          min-height: 100vh;
          margin: 0;
      }

      .container {
          margin: 20px auto;
          display: flex;
          flex-direction: row;
          justify-content: center;
          align-items: flex-start;
          gap: 40px;
          background-color: white;
          padding: 30px;
          border-radius: 15px;
          box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      }

      #sudoku-board {
          border: 4px solid #2c3e50;
          /* Đường viền tổng thể bảng */
          border-collapse: separate;
          /* Giữ cho các ô tách biệt */
          border-spacing: 0;
          /* Loại bỏ khoảng cách giữa các ô */
      }

      #sudoku-board tr:nth-child(3) td {
          border-bottom: 3px solid #2c3e50;
          /* Đường viền dưới hàng thứ 3 */
      }

      #sudoku-board tr:nth-child(6) td {
          border-bottom: 3px solid #2c3e50;
          /* Đường viền dưới hàng thứ 6 */
      }

      #sudoku-board td {
          width: 60px;
          height: 60px;
          text-align: center;
          border: 1px solid #7f8c8d;
          /* Đường viền ô */
          position: relative;
      }

      #sudoku-board td:nth-child(3),
      #sudoku-board td:nth-child(6) {
          border-right: 3px solid #2c3e50;
          /* Đường viền dọc cho cột thứ 3 và thứ 6 */
      }

      #sudoku-board input {
          width: 100%;
          height: 100%;
          border: none;
          text-align: center;
          font-size: 24px;
          font-weight: bold;
          color: #2c3e50;
          background-color: transparent;
          outline: none;
          transition: background-color 0.3s ease;
      }

      #sudoku-board input:focus {
          background-color: #e8f4f8;
      }

      button {
          display: block;
          width: 100%;
          margin-top: 20px;
          padding: 12px 20px;
          font-size: 18px;
          background-color: #3498db;
          color: white;
          border: none;
          border-radius: 8px;
          cursor: pointer;
          transition: background-color 0.3s ease;
      }

      button:hover {
          background-color: #2980b9;
      }

      .solved-board {
          border: 4px solid #27ae60;
          border-collapse: separate;
          border-spacing: 0;
          border-radius: 10px;
          overflow: hidden;
      }

      .solved-board td {
          width: 60px;
          height: 60px;
          text-align: center;
          border: 1px solid #7f8c8d;
          background-color: #2ecc71;
          color: white;
          font-size: 24px;
          font-weight: bold;
      }

      /* Responsive Design */
      @media (max-width: 768px) {
          .container {
              flex-direction: column;
              align-items: center;
              gap: 20px;
          }

          #sudoku-board td,
          .solved-board td {
              width: 40px;
              height: 40px;
              font-size: 18px;
          }
      }
    </style>
  </head>
  <body>
    <?php include '../includes/navbar.php'; ?>
      <h2 class="text-center mt-4">SUDOKU</h2>
    <div class="container">
      <div>
        <table id="sudoku-board" class="text-white">
          <?php 
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:5000/generate_sudoku");
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $response = curl_exec($ch);
          curl_close($ch);
          $board = json_decode($response, true);
          
          foreach ($board as $row) {
              echo "<tr>";
              foreach ($row as $num) {
                  echo "<td><input type='number' value='" . ($num != 0 ? $num : "") . "' min='1' max='9' /></td>";
              }
              echo "</tr>";
          }
          ?>
        </table>
        <button onclick="checkSolution()">Kiểm tra kết quả</button>
      </div>

      <div id="solved-board-container" style="display: none">
        <table class="solved-board" id="solved-board">
        </table>
        <h3 class="text-center mt-4">ĐÁP ÁN TRÒ CHƠI</h3>
      </div>
    </div>
    <?php include '../includes/footer.php'; ?>

    <script>
      function checkSolution() {
        const board = [];
        const inputs = document.querySelectorAll("input");

        // Xây dựng board từ input
        for (let i = 0; i < 9; i++) {
          const row = [];
          for (let j = 0; j < 9; j++) {
            const value = inputs[i * 9 + j].value;
            row.push(value ? parseInt(value) : 0);
          }
          board.push(row);
        }

        fetch("http://127.0.0.1:5000/check_solution", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ board })
        })
        .then(response => response.json())
        .then(data => {
          console.log(data);

          if (data.status === "checked") {
            const result = data.result;
            const inputs = document.querySelectorAll("input");

            for (let i = 0; i < 9; i++) {
              for (let j = 0; j < 9; j++) {
                const input = inputs[i * 9 + j];
                const resultCell = result[i][j];
                input.style.backgroundColor = resultCell.color; 
              }
            }

            const solvedBoardContainer = document.getElementById("solved-board-container");
            const solvedBoardTable = document.getElementById("solved-board");
            solvedBoardContainer.style.display = "block"; 
            solvedBoardTable.innerHTML = ""; 

            const solvedBoard = data.solved_board;
            solvedBoard.forEach((row) => {
              const tr = document.createElement("tr");
              row.forEach((num) => {
                const td = document.createElement("td");
                td.textContent = num !== 0 ? num : ""; 
                tr.appendChild(td);
              });
              solvedBoardTable.appendChild(tr);
            });
          } else {
            alert("Không thể kiểm tra kết quả. Vui lòng thử lại.");
          }
        })
        .catch((error) => {
          alert("Lỗi khi kiểm tra kết quả: " + error.message);
        });
      }
    </script>
    <?php include '../includes/scripts.php'; ?>
  </body>
</html>
