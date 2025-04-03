@section('title', 'Dashboard')
@section('content')
    <h2>Chào mừng đến với Dashboard Admin</h2>
    <p>Chọn một mục từ menu để quản lý hệ thống.</p>
@endsection
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/static/styles.css">
</head>
<body>
    <div class="sidebar">
        <h3>Admin Menu</h3>
        <ul>
            <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li><a href="{{ route('admin.users') }}">Quản lý tài khoản</a></li>
            <li><a href="{{ route('admin.deposits') }}">Quản lý nạp tiền</a></li>
            <li><a href="{{ route('admin.stats') }}">Thống kê nạp tiền</a></li>
            <li><a href="#">Cài đặt hệ thống</a></li>
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
        <h2>Chào mừng đến với Dashboard Admin</h2>
        <p>Chọn một mục từ menu để quản lý hệ thống.</p>
    </section>
</body>
</html>