<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link rel="stylesheet" href="/static/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            display: flex;
            transition: all 0.3s ease;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease; /* Hiệu ứng thu nhỏ/phóng to */
        }
        .sidebar.collapsed {
            width: 60px; /* Kích thước thu nhỏ */
        }
        .sidebar h3 {
            margin: 0 0 30px;
            font-size: 1.4em;
            text-align: center;
            color: #ecf0f1;
            transition: opacity 0.3s ease;
        }
        .sidebar.collapsed h3 {
            opacity: 0; /* Ẩn tiêu đề khi thu nhỏ */
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            margin-bottom: 10px;
        }
        .sidebar a {
            display: block;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            font-size: 0.95em;
            transition: all 0.3s ease;
            white-space: nowrap; /* Ngăn text xuống dòng */
        }
        .sidebar.collapsed a {
            padding-left: 10px;
            font-size: 0; /* Ẩn text khi thu nhỏ */
        }
        .sidebar a:hover {
            background: #3498db;
            transform: translateX(5px);
        }
        .sidebar.collapsed a:hover {
            transform: translateX(0); /* Không áp dụng hiệu ứng dịch chuyển khi thu nhỏ */
        }
        /* Thêm biểu tượng cho các mục trong sidebar */
        .sidebar ul li a::before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: #ecf0f1;
            margin-right: 10px;
            opacity: 1;
            width: 20px;
            text-align: center;
            display: inline-block;
        }
        .sidebar ul li:nth-child(1) a::before { content: "\f0e4"; } /* Dashboard */
        .sidebar ul li:nth-child(2) a::before { content: "\f007"; } /* Quản lý tài khoản */
        .sidebar ul li:nth-child(3) a::before { content: "\f3d1"; } /* Quản lý nạp tiền */
        .sidebar ul li:nth-child(4) a::before { content: "\f080"; } /* Thống kê nạp tiền */
        .sidebar ul li:nth-child(5) a::before { content: "\f1da"; } /* Lịch sử nhận diện */
        .sidebar ul li:nth-child(6) a::before { content: "\f4c4"; } /* Điều chỉnh nạp tiền */
        .sidebar ul li:nth-child(7) a::before { content: "\f27a"; } /* Cấu hình Chatbot */
        .sidebar ul li:nth-child(8) a::before { content: "\f1b2"; } /* Cấu hình mô hình nhận diện */
        .sidebar ul li:nth-child(9) a::before { content: "\f085"; } /* Cấu hình Metadata */
        .sidebar ul li:nth-child(10) a::before { content: "\f019"; } /* Quản lý APK */

        /* Header */
        header {
            margin-left: 220px;
            background: #ffffff;
            padding: 15px 25px;
            border-bottom: 1px solid #e0e4e8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: calc(100% - 220px);
            position: fixed;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        header.collapsed {
            margin-left: 60px;
            width: calc(100% - 60px);
        }
        .logo {
            font-size: 1.4em;
            font-weight: 600;
            color: #2c3e50;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-info span {
            font-size: 0.95em;
            color: #34495e;
        }
        .logout-btn {
            padding: 8px 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background: #c0392b;
            transform: scale(1.05);
        }

        /* Nội dung chính */
        .admin-content {
            margin-left: 220px;
            margin-top: 60px;
            padding: 20px;
            width: calc(100% - 220px);
            min-height: calc(100vh - 60px);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        .admin-content.collapsed {
            margin-left: 60px;
            width: calc(100% - 60px);
        }

        /* CSS cho dashboard-container */
        .dashboard-container {
            width: 90%;
            max-width: 1000px;
            margin: 20px auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            font-size: 26px;
            margin-bottom: 20px;
            color: #2c3e50;
            font-weight: 600;
        }

        p {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3><a href="{{ route('home') }}" style="text-decoration: none; color: inherit;">Admin Menu</a></h3>
        <ul>
            <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li><a href="{{ route('admin.users') }}">Quản lý tài khoản</a></li>
            <li><a href="{{ route('admin.deposits') }}">Quản lý nạp tiền</a></li>
            <li><a href="{{ route('admin.stats') }}">Thống kê nạp tiền</a></li>
            <li><a href="{{ route('admin.histori') }}">Lịch sử nhận diện</a></li>
            <li><a href="{{ route('admin.deposit_plans') }}">Điều chỉnh nạp tiền</a></li>
            <li><a href="{{ route('admin.chatbot_config') }}">Cấu hình Chatbot</a></li>
            <li><a href="{{ route('admin.change_model') }}">Cấu hình mô hình nhận diện</a></li>
            <li><a href="{{ route('admin.metadata_config') }}">Cấu hình Metadata</a></li>
            <li><a href="{{ route('admin.apks') }}">Quản lý APK</a></li>
        </ul>
    </div>

    <header>
        <div class="logo">Admin Panel</div>
        <div class="user-info">
            <span>Xin chào, {{ Auth::user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="logout-btn">Đăng xuất</button>
            </form>
        </div>
    </header>

    <section class="admin-content">
        @yield('content')
    </section>

    @yield('scripts')
    <script>
        const sidebar = document.querySelector('.sidebar');
        const header = document.querySelector('header');
        const adminContent = document.querySelector('.admin-content');

        sidebar.addEventListener('mouseenter', () => {
            sidebar.classList.remove('collapsed');
            header.classList.remove('collapsed');
            adminContent.classList.remove('collapsed');
        });

        sidebar.addEventListener('mouseleave', () => {
            sidebar.classList.add('collapsed');
            header.classList.add('collapsed');
            adminContent.classList.add('collapsed');
        });
    </script>
</body>
</html>