<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Người Dùng</title>
    <link rel="stylesheet" href="/static/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .profile-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        .profile-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .info-table th {
            background: #2c3e50;
            color: white;
        }
        .info-table td input {
            width: 100%;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .info-table td input[readonly] {
            background-color: #f1f1f1;
            cursor: not-allowed;
        }
        .avatar-preview {
            text-align: center;
            margin-bottom: 15px;
        }
        .avatar-preview img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ff8c00;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .avatar-preview img:hover {
            transform: scale(1.1);
        }
        .avatar-change-section {
            display: none;
            text-align: center;
            margin-top: 10px;
        }
        .avatar-change-section input[type="file"] {
            margin-bottom: 10px;
        }
        .avatar-change-section button {
            padding: 8px 15px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .avatar-change-section button:hover {
            background: #34495e;
            transform: scale(1.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .form-group button:hover {
            background: #34495e;
            transform: scale(1.1);
        }
        .error {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
            display: block;
        }
        .success {
            color: green;
            font-size: 0.9em;
            margin-bottom: 10px;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 10px;
        }
        .back-link a {
            color: #2c3e50;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Hồ Sơ Người Dùng</h2>
        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <!-- Hiển thị và đổi avatar -->
        <div class="avatar-preview">
            <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://via.placeholder.com/100' }}" alt="Avatar" onclick="toggleAvatarChange()">
        </div>
        <div class="avatar-change-section" id="avatar-change-section">
            <form action="{{ route('user.profile.update-avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="avatar" accept="image/*" required>
                <button type="submit">Đổi Avatar</button>
                @error('avatar')
                    <span class="error">{{ $message }}</span>
                @enderror
            </form>
        </div>

        <!-- Hiển thị thông tin khách hàng -->
        <form action="{{ route('user.profile.update-name') }}" method="POST">
            @csrf
            <table class="info-table">
                <tr>
                    <th>Họ và Tên</th>
                    <td>
                        <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                        @error('name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><input type="text" value="{{ Auth::user()->email }}" readonly></td>
                </tr>
                <tr>
                    <th>Số Lần Sử Dụng</th>
                    <td><input type="text" value="{{ Auth::user()->tokens }}" readonly></td>
                </tr>
                <tr>
                    <th>Vai Trò</th>
                    <td><input type="text" value="{{ Auth::user()->role }}" readonly></td>
                </tr>
                <tr>
                    <th>Số Tiền Đã Nạp</th>
                    <td><input type="text" value="{{ number_format(Auth::user()->balance, 2) }} VNĐ" readonly></td>
                </tr>
            </table>
            <div class="form-group">
                <button type="submit">Cập Nhật Tên</button>
            </div>
        </form>

        <!-- Form thay đổi mật khẩu -->
        <h3>Thay Đổi Mật Khẩu</h3>
        <form action="{{ route('user.profile.update-password') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="old_password">Mật Khẩu Cũ</label>
                <input type="password" name="old_password" id="old_password" required>
                @error('old_password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">Mật Khẩu Mới</label>
                <input type="password" name="password" id="password" required>
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">Xác Nhận Mật Khẩu Mới</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>
            <div class="form-group">
                <button type="submit">Đổi Mật Khẩu</button>
            </div>
        </form>

        <div class="back-link">
            <a href="{{ route('home') }}">Quay lại Trang Chủ</a>
        </div>
    </div>

    <script>
        function toggleAvatarChange() {
            const section = document.getElementById('avatar-change-section');
            section.style.display = section.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>