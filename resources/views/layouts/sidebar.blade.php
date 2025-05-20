<nav class="sidebar">
    <h3>Mục Lục</h3>
    <ul>
        <li><a href="#hero">Giới Thiệu</a></li>
        <li><a href="#discover">Khám Phá</a></li>
        <li><a href="#currencies">Tiền Tệ Đông Nam Á</a></li>
        <li><a href="#cta">Bắt Đầu Hành Trình</a></li>
        @if(!Auth::check())
            <li><a href="{{ route('login') }}">Đăng Nhập/Đăng Ký</a></li>
        @else
            <li class="user-info-sidebar">
                <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://via.placeholder.com/40' }}" alt="Avatar" class="user-avatar" onclick="goToProfile()">
                <span>Xin chào, {{ Auth::user()->name }} (lần sử dụng: {{ Auth::user()->tokens }})</span>
            </li>
            @if(Auth::user()->role === 'admin')
                <li><a href="{{ route('admin.dashboard') }}">Admin Panel</a></li>
            @else
                <li><a href="{{ route('deposit') }}" class="deposit-btn">Nạp tiền</a></li>
            @endif
            <li>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Đăng Xuất</button>
                </form>
            </li>
        @endif
    </ul>
</nav>