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
    <title>Sudoku Solver</title>
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
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 10px;
        overflow: hidden;
        }

        #sudoku-board tr:nth-child(3n) {
        border-bottom: 3px solid #2c3e50;
        }

        #sudoku-board td {
        width: 60px;
        height: 60px;
        text-align: center;
        border: 1px solid #7f8c8d;
        position: relative;
        }

        #sudoku-board td:nth-child(3n) {
        border-right: 3px solid #2c3e50;
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

            #sudoku-board td, .solved-board td {
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
        <table id="sudoku-board">
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
        <h3>Bài giải của hệ thống:</h3>
        <table class="solved-board" id="solved-board">
        </table>
      </div>
    </div>
    <?php include '../includes/footer.php'; ?>

    <script>
      function checkSolution() {
        const board = [];
        const inputs = document.querySelectorAll("input");

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
          if (data.status === "checked") {
            let result = data.result;
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
          }
        })
        .catch((error) => {
          alert("Error checking solution: " + error.message);
        });
      }
    </script>
    <?php include '../includes/scripts.php'; ?>
  </body>
</html>
