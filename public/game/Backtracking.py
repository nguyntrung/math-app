from flask import Flask, jsonify, render_template, request
import random

app = Flask(__name__)

# Lưu bảng Sudoku đã giải
solved_board = None

def is_valid(board, row, col, num):
    if num in board[row]:
        return False
    for r in range(9):
        if board[r][col] == num:
            return False
    start_row, start_col = (row // 3) * 3, (col // 3) * 3
    for i in range(3):
        for j in range(3):
            if board[start_row + i][start_col + j] == num:
                return False
    return True

def solve(board):
    for row in range(9):
        for col in range(9):
            if board[row][col] == 0:
                for num in range(1, 10):
                    if is_valid(board, row, col, num):
                        board[row][col] = num
                        if solve(board):
                            return True
                        board[row][col] = 0
                return False
    return True

def generate_sudoku():
    board = [[0 for _ in range(9)] for _ in range(9)]
    for _ in range(40):
        row, col = random.randint(0, 8), random.randint(0, 8)
        num = random.randint(1, 9)
        if is_valid(board, row, col, num):
            board[row][col] = num
    for _ in range(20):
        row, col = random.randint(0, 8), random.randint(0, 8)
        board[row][col] = 0
    return board

@app.route('/')
def index():
    global solved_board
    board = generate_sudoku()  # Tạo bảng Sudoku ngẫu nhiên
    solved_board = [row[:] for row in board]  # Lưu bảng đã giải
    solve(solved_board)  # Giải Sudoku
    return render_template('index.html', board=board, solved_board=solved_board)

@app.route('/check_solution', methods=['POST'])
def check_solution():
    data = request.get_json()
    user_board = data['board']
    
    if not solved_board:
        return jsonify({'status': 'error', 'message': 'No solved board available.'})
    
    result = []
    for i in range(9):
        row_result = []
        for j in range(9):
            if solved_board[i][j] == user_board[i][j]:
                row_result.append({'value': user_board[i][j], 'color': 'green'})
            else:
                row_result.append({'value': user_board[i][j], 'color': 'red'})
        result.append(row_result)
    
    return jsonify({'status': 'checked', 'result': result, 'solved_board': solved_board})

if __name__ == '__main__':
    app.run(debug=True)
