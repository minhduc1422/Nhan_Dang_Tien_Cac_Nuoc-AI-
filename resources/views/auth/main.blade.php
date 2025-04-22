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
            display: none;
        }
        #chat-input-container {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ddd;
            display: none;
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
            display: block;
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

        /* CSS cho hero section */
        .hero {
            padding: 50px;
            background-color: #00c4cc;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            background: url('https://www.transparenttextures.com/patterns/arabesque.png'), #00c4cc;
        }
        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .content {
            flex: 1;
            max-width: 50%;
            text-align: left;
            padding-right: 20px;
        }
        .hero-image {
            flex: 1;
            max-width: 50%;
            text-align: right;
        }
        .hero-image img {
            max-width: 100%;
            height: auto;
        }
        #upload-container {
            margin: 20px 0;
        }
        #image-input {
            display: block;
            margin: 10px 0;
        }
        #upload-button {
            padding: 10px 20px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #upload-button:hover {
            background: #34495e;
        }
        #result-container {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #result-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }
        .info-bar {
            background: #f1f1f1;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            width: 100%;
            text-align: center;
            overflow-wrap: break-word;
            word-break: break-all;
        }
        #result-error {
            margin-top: 10px;
            color: red;
            width: 100%;
            text-align: center;
            overflow-wrap: break-word;
            word-break: break-all;
        }

        img {
            transition: transform 0.3s ease;
        }
        img:hover {
            transform: scale(1.1);
        }

        .discover-item img {
            max-width: 100%;
            height: auto;
            transition: transform 0.3s ease;
        }
        .discover-item img:hover {
            transform: scale(1.1);
        }

        button, .deposit-btn, .logout-btn {
            transition: transform 0.3s ease;
        }
        button:hover, .deposit-btn:hover, .logout-btn:hover {
            transform: scale(1.1);
        }

        .user-info-sidebar {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            cursor: pointer;
            border: 2px solid #ff8c00;
        }
        .user-info-sidebar span {
            color: #fff;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
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
                    <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://via.placeholder.com/40' }}" alt="Avatar" class="user-avatar" onclick="goToProfile()">
                    <span>Xin chào, {{ Auth::user()->name }}<br> 
                    (lần sử dụng: {{ Auth::user()->tokens }})</span>
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

    <header>
        <div class="logo">Khám Phá Tiền Tệ</div>
    </header>

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
                        <div id="result-container" style="display: none;">
                            <img id="result-image" alt="Ảnh nhận diện">
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

    <section id="discover" class="discover">
        <div class="discover-header">
            <h2>Khám Phá Tiền Tệ Đông Nam Á</h2>
            <p>Đông Nam Á là khu vực có nền văn hóa đa dạng và phong phú, nơi giao thoa giữa những truyền thống lâu đời và sự hiện đại hóa không ngừng. Vùng đất này là cái nôi của những điệu múa duyên dáng như Apsara của Campuchia, những ngôi chùa vàng rực rỡ ở Thái Lan, hay những lễ hội đèn lồng lung linh ở Việt Nam. Ẩm thực Đông Nam Á cũng là một bản giao hưởng của hương vị, từ vị cay nồng của Tom Yum Thái Lan, sự tinh tế của Phở Việt Nam, đến hương thơm quyến rũ của Nasi Goreng Indonesia. Mỗi quốc gia trong khu vực mang một màu sắc riêng, đan xen giữa tín ngưỡng Phật giáo, Hồi giáo, Thiên Chúa giáo và các phong tục bản địa độc đáo. Chính sự đa dạng này đã tạo nên một Đông Nam Á đầy sức hút, nơi mà mỗi bước chân của du khách đều là một hành trình khám phá những câu chuyện văn hóa kỳ diệu.</p>
        </div>
        <div class="discover-grid">
            <div class="discover-item">
                <div class="discover-text">
                    <h3>Tìm Hiểu Lịch Sử Tiền Tệ</h3>
                    <p>Lịch sử tiền tệ Đông Nam Á rất hấp dẫn, từ vỏ sò, hạt cau thời xưa đến đồng xu kim loại khắc hình vua chúa. Qua các tuyến thương mại, tiền tệ nơi đây mang dấu ấn Trung Quốc, Ấn Độ và phương Tây. Đến nay, mỗi đồng tiền đều kể một câu chuyện về quá khứ đầy màu sắc.</p>
                </div>
                <img src="/static/image/hinhnen_2.png" alt="Hình Ảnh Văn Hóa">
            </div>
            <div class="discover-item">
                <img src="/static/image/hinhnen_3.png" alt="Thiết Kế Tiền Tệ">
                <div class="discover-text">
                    <h3>Khám Phá Thiết Kế Tiền Tệ</h3>
                    <p>Mỗi tờ tiền và đồng xu ở Đông Nam Á như một bức tranh nhỏ. Có tờ vẽ chùa vàng Thái Lan, có đồng khắc Angkor Wat của Campuchia, hay hình ảnh ruộng lúa Việt Nam. Chúng không chỉ để tiêu mà còn thể hiện văn hóa và lịch sử độc đáo.</p>
                </div>
            </div>
            <div class="discover-item">
                <div class="discover-text">
                    <h3>Khám Phá Thêm Tiền Tệ</h3>
                    <p>Đông Nam Á là vùng đất đa văn hóa, nơi chùa Phật, nhà thờ và đền Hồi giáo cùng tồn tại. Từ múa Apsara Campuchia, lễ hội đèn lồng Việt Nam đến ẩm thực cay nồng của Thái Lan, mỗi nước mang một nét riêng. Sự phong phú này làm nên sức hút đặc biệt cho khu vực.</p>
                </div>
                <img src="/static/image/hinhnen_4.png" alt="Thêm Tiền Tệ">
            </div>
        </div>
    </section>

    <section id="cta" class="cta">
        <h3>Bắt Đầu Hành Trình khám phá tiền Của Bạn Ngay Hôm Nay!</h3>
        <p>Hãy cùng chúng tôi khám phá thế giới tiền tệ Đông Nam Á...</p>
        <button onclick="document.getElementById('image-input')?.click()">Khám Phá Tiền Tệ</button>
    </section>

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