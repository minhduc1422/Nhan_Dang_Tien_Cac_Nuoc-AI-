<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Khám Phá Tiền Tệ</title>
    <link rel="stylesheet" href="/static/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS cho loader */
        .loader {
            width: 120px;
            height: 20px;
            border-radius: 20px;
            background:
                linear-gradient(orange 0 0) 0/0% no-repeat
                lightblue;
            animation: l2 2s infinite steps(10);
            margin: 10px auto;
            display: none;
        }
        @keyframes l2 {
            100% {background-size:110%}
        }

        /* CSS cho chatbox */
        #chat-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        #chat-header {
            background: #2c3e50;
            color: white;
            padding: 10px;
            text-align: center;
            cursor: pointer;
        }
        #chat-messages {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            display: none; /* Ẩn mặc định */
        }
        #chat-input-container {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ddd;
            display: none; /* Ẩn mặc định */
        }
        #chat-input {
            flex: 1;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 5px;
        }
        #chat-send {
            padding: 5px 10px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #chat-container.active #chat-messages,
        #chat-container.active #chat-input-container {
            display: block; /* Hiển thị khi chatbox mở rộng */
        }
        .message {
            margin: 5px 0;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .user-message {
            background: #2c3e50;
            color: white;
            margin-left: 20%;
            margin-right: 5px;
        }
        .bot-message {
            background: #f1f1f1;
            margin-right: 20%;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <!-- Sidebar - Mục Lục -->
    <nav class="sidebar">
        <h3>Mục Lục</h3>
        <ul>
            <li><a href="#hero">Giới Thiệu</a></li>
            <li><a href="#discover">Khám Phá</a></li>
            <li><a href="#cta">Bắt Đầu Hành Trình</a></li>
            @if(!Auth::check())
                <li><a href="{{ route('login') }}">Đăng Nhập/Đăng Ký</a></li>
            @else
                <li class="user-info-sidebar">
                    <span>Xin chào, {{ Auth::user()->name }} (Tokens: {{ Auth::user()->tokens }})</span>
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

    <!-- Header -->
    <header>
        <div class="logo">Khám Phá Tiền Tệ</div>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="hero">
        <div class="hero-content">
            <h1>Khám Phá Tiền Tệ Đông Nam Á</h1>
            <div class="container">
                <div class="content">
                    <p>Chào mừng bạn đến với hành trình khám phá tiền tệ Đông Nam Á!</p>
                    @if(Auth::check())
                        <div id="upload-container">
                            <input type="file" id="image-input" accept="image/*">
                            <button id="upload-button" onclick="uploadImage()">Tải Lên và Nhận Diện</button>
                        </div>
                        <div id="loading" class="loader"></div>
                        <div id="result-container" style="display: none; text-align: center;">
                            <img id="result-image" alt="Ảnh nhận diện" style="max-width: 300px; display: block; margin: auto;">
                            <div class="info-bar" id="detection-info"></div>
                            <div class="info-bar" id="money-details"></div>
                            <p id="result-error" style="color: red;"></p>
                        </div>
                    @endif
                </div>
                <div class="hero-image">
                    <img src="/static/image/hinhnen_1.png" alt="Hình Ảnh Tiền Tệ">
                </div>
            </div>
        </div>
    </section>

    <!-- Discover Section -->
    <section id="discover" class="discover">
        <div class="discover-header">
            <h2>Khám Phá Tiền Tệ Đông Nam Á</h2>
            <p>Đông Nam Á là khu vực có nền văn hóa đa dạng và phong phú...</p>
        </div>
        <div class="discover-grid">
            <div class="discover-item">
                <div class="discover-text">
                    <h3>Tìm Hiểu Lịch Sử Tiền Tệ</h3>
                    <p>Lịch sử tiền tệ Đông Nam Á là một hành trình dài đầy thú vị...</p>
                </div>
                <img src="/static/image/hinhnen_2.png" alt="Hình Ảnh Văn Hóa">
            </div>
            <div class="discover-item">
                <img src="/static/image/hinhnen_3.png" alt="Thiết Kế Tiền Tệ">
                <div class="discover-text">
                    <h3>Khám Phá Thiết Kế Tiền Tệ</h3>
                    <p>Mỗi tờ tiền và đồng xu trong khu vực Đông Nam Á đều là một tác phẩm nghệ thuật...</p>
                </div>
            </div>
            <div class="discover-item">
                <div class="discover-text">
                    <h3>Khám Phá Thêm Tiền Tệ</h3>
                    <p>Đông Nam Á là nơi hội tụ của nhiều nền văn hóa đa dạng...</p>
                </div>
                <img src="/static/image/hinhnen_4.png" alt="Thêm Tiền Tệ">
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section id="cta" class="cta">
        <h3>Bắt Đầu Hành Trình Tiền Tệ Của Bạn Ngay Hôm Nay!</h3>
        <p>Hãy cùng chúng tôi khám phá thế giới tiền tệ Đông Nam Á...</p>
        <button onclick="document.getElementById('image-input')?.click()">Khám Phá Tiền Tệ</button>
    </section>

    <!-- Footer -->
    <footer id="footer">
        <div class="footer-nav">
            <a href="#">Trang Chủ</a>
            <a href="#">Khám Phá Tiền Tệ</a>
            <a href="#">Câu Chuyện Của Chúng Tôi</a>
            <a href="#">Liên Hệ</a>
        </div>
        <div class="footer-social">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <p>© Mọi quyền được bảo lưu.</p>
    </footer>

    <!-- Chatbox -->
    <div id="chat-container">
        <div id="chat-header" onclick="toggleChatbox()">Trò Chuyện</div>
        <div id="chat-messages"></div>
        <div id="chat-input-container">
            <input type="text" id="chat-input" placeholder="Nhập câu hỏi...">
            <button id="chat-send" onclick="sendMessage()">Gửi</button>
        </div>
    </div>

    <script src="/static/scripts.js"></script>
</body>
</html>