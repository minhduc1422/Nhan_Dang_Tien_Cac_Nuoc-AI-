<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $metadata = App\Models\Metadata::first();
    @endphp
    <title>{{ $metadata->site_title ?? 'Khám Phá Tiền Tệ' }}</title>
    <meta name="description" content="{{ $metadata->site_description ?? 'Khám phá tiền tệ Đông Nam Á' }}">
    <meta name="keywords" content="{{ $metadata->site_keywords ?? 'tiền tệ, Đông Nam Á, khám phá' }}">
    <meta name="author" content="{{ $metadata->author ?? 'Khám Phá Tiền Tệ' }}">
    @if($metadata && $metadata->og_image)
        <meta property="og:image" content="{{ asset('storage/' . $metadata->og_image) }}">
    @endif
    @if($metadata && $metadata->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $metadata->favicon) }}">
    @endif
    <link rel="stylesheet" href="{{ asset('static/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .loader {
            width: 120px;
            height: 20px;
            border-radius: 20px;
            background: linear-gradient(orange 0 0) 0/0% no-repeat lightblue;
            animation: l2 2s infinite steps(10);
            margin: 10px auto;
        }
        @keyframes l2 {
            100% {background-size:110%}
        }
        #loading {
            text-align: center;
            display: none;
        }
        #loading p {
            margin: 5px 0;
            font-size: 1em;
            color: #2c3e50;
        }
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
            background: hsl(136, 91.50%, 46.10%);
            color: black;
            margin-left: 20%;
            margin-right: 5px;
        }
        .bot-message {
            background: #f1f1f1;
            margin-right: 20%;
            margin-left: 5px;
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
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 60px;
            background: #1a1a1a;
            padding: 20px 10px;
            transition: width 0.3s ease;
            overflow-x: hidden;
            z-index: 1000;
        }
        .sidebar:hover {
            width: 250px;
        }
        .sidebar h3 {
            color: #fff;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2em;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar:hover h3 {
            opacity: 1;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 15px 0;
            display: flex;
            align-items: center;
        }
        .sidebar ul li a, .sidebar ul li .deposit-btn, .sidebar ul li .logout-btn {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.9em;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar:hover ul li a, .sidebar:hover ul li .deposit-btn, .sidebar:hover ul li .logout-btn {
            opacity: 1;
        }
        .sidebar ul li a:hover {
            background: #555;
        }
        .sidebar ul li .user-info-sidebar {
            opacity: 1;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .sidebar:hover ul li .user-info-sidebar {
            opacity: 1;
        }
        .sidebar ul li .user-avatar {
            opacity: 1;
        }
        .sidebar ul li form {
            display: inline;
        }
        .sidebar ul li a::before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: #fff;
            margin-right: 10px;
            opacity: 1;
            width: 20px;
            text-align: center;
        }
        .sidebar ul li:nth-child(1) a::before { content: "\f05a"; }
        .sidebar ul li:nth-child(2) a::before { content: "\f002"; }
        .sidebar ul li:nth-child(3) a::before { content: "\f0d6"; }
        .sidebar ul li:nth-child(4) a::before { content: "\f135"; }
        .sidebar ul li:nth-child(5) a::before { content: "\f090"; }
        .sidebar ul li .deposit-btn::before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            content: "\f3d1";
            color: #fff;
            margin-right: 10px;
            opacity: 1;
            width: 20px;
            text-align: center;
        }
        .sidebar ul li .logout-btn::before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            content: "\f2f5";
            color: #fff;
            margin-right: 10px;
            opacity: 1;
            width: 20px;
            text-align: center;
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
        #youtube-video-wrapper {
            margin: 20px auto;
            max-width: 800px;
            text-align: center;
        }
        #youtube-video-wrapper iframe {
            width: 100%;
            height: 450px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        #result-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 600px;
        }
        #result-image {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .info-bar {
            background: #e8f4f8;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #d1e7dd;
            font-size: 1em;
            color: #333;
        }
        .info-bar p {
            margin: 5px 0;
        }
        #result-error {
            text-align: center;
            font-size: 1em;
            margin-top: 10px;
        }
        .token-display {
            background: #2c3e50;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            margin: 10px auto;
            display: inline-block;
            font-size: 1em;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .token-display i {
            margin-right: 5px;
        }
        @media (max-width: 768px) {
            #result-container {
                max-width: 100%;
                padding: 10px;
            }
            #result-image {
                max-width: 100%;
                height: auto;
            }
            #upload-container {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            #upload-container input,
            #upload-container button {
                width: 100%;
                margin: 5px 0;
            }
            .token-display {
                font-size: 0.9em;
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>
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
                    <span id="sidebar-token-display">Xin chào, {{ Auth::user()->name }}<br>(lần sử dụng: {{ Auth::user()->tokens }})</span>
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
        <video autoplay loop muted poster="static/image/hinhnen_1.png" id="bg-video">
            <source src="static/image/nen.mp4" type="video/mp4">
        </video>
        <div class="hero-content">
            <h1>Khám Phá Tiền Tệ Đông Nam Á</h1>
            <p>Chào mừng bạn đến với hành trình khám phá tiền tệ Đông Nam Á!</p>
            @if(Auth::check())
                <div class="token-display">
                    <i class="fas fa-coins"></i> Số lần sử dụng còn lại: <span id="hero-token-display">{{ Auth::user()->tokens }}</span>
                </div>
            @else
                <p class="token-display" style="background: #ff4444;">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập để nhận 5 lần sử dụng miễn phí!
                </p>
            @endif
            <div id="upload-container">
                <input type="file" id="image-input" accept="image/*">
                @if(Auth::check())
                    <button id="upload-button" onclick="uploadImage()">Tải Lên và Nhận Diện</button>
                @else
                    <button disabled>Đăng nhập để nhận diện tiền</button>
                @endif
            </div>
            <div id="loading" style="display: none;">
                <div class="loader"></div>
                <p>Đang nhận diện tờ tiền...</p>
            </div>
            <div id="result-container" style="display: none;">
                <img id="result-image" alt="Ảnh nhận diện">
                <div class="info-bar" id="detection-info"></div>
                <div class="info-bar" id="money-details"></div>
                <p id="result-error" style="color: red;"></p>
            </div>
        </div>
    </section>

    <section id="discover" class="discover fade-in">
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
                <img src="{{ asset('static/image/hinhnen_2.png') }}" alt="Hình Ảnh Văn Hóa">
            </div>
            <div class="discover-item">
                <img src="{{ asset('static/image/hinhnen_3.png') }}" alt="Thiết Kế Tiền Tệ">
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
                <img src="{{ asset('static/image/hinhnen_4.png') }}" alt="Thêm Tiền Tệ">
            </div>
        </div>
    </section>

    <section id="currencies" class="currencies fade-in">
        <div class="currencies-header">
            <h2>Tiền Tệ Đông Nam Á</h2>
            <p>Khám phá các loại tiền tệ đa dạng được sử dụng trên khắp Đông Nam Á, mỗi loại mang lịch sử và thiết kế độc đáo riêng.</p>
        </div>
        <div id="youtube-video-wrapper">
            <iframe src="https://www.youtube.com/embed/m7Mi5CdotI0?autoplay=1&mute=1" allowfullscreen></iframe>
        </div>
        <div class="currencies-grid">
            <div class="currency-item">
                <h3>Rupiah Indonesia (IDR)</h3>
                <p>Được giới thiệu vào năm 1946, Rupiah thay thế gulden Đông Ấn Hà Lan. Tên "rupiah" bắt nguồn từ đồng rupee Ấn Độ.</p>
            </div>
            <div class="currency-item">
                <h3>Ringgit Malaysia (MYR)</h3>
                <p>Ban đầu là đồng đô la Malaya và British Borneo, được đổi tên thành Ringgit vào năm 1967. "Ringgit" có nghĩa là "lởm chởm" trong tiếng Malay, ám chỉ các đồng đô la Tây Ban Nha cũ.</p>
            </div>
            <div class="currency-item">
                <h3>Peso Philippines (PHP)</h3>
                <p>Được giới thiệu vào năm 1852, Peso đã được sử dụng liên tục, tồn tại qua các thời kỳ chiếm đóng của Tây Ban Nha, Mỹ và Nhật Bản.</p>
            </div>
            <div class="currency-item">
                <h3>Đô la Singapore (SGD)</h3>
                <p>Được giới thiệu vào năm 1967 sau khi độc lập, Đô la Singapore là một trong những đồng tiền mạnh nhất châu Á.</p>
            </div>
            <div class="currency-item">
                <h3>Baht Thái Lan (THB)</h3>
                <p>Baht đã được sử dụng từ thế kỷ 19, ban đầu là đơn vị trọng lượng cho vàng và bạc.</p>
            </div>
            <div class="currency-item">
                <h3>Đô la Brunei (BND)</h3>
                <p>Được neo giá với Đô la Singapore theo tỷ lệ 1:1 từ năm 1967, cả hai đồng tiền đều có thể sử dụng ở Brunei và Singapore.</p>
            </div>
            <div class="currency-item">
                <h3>Riel Campuchia (KHR)</h3>
                <p>Được giới thiệu vào năm 1953, nhưng do siêu lạm phát, đô la Mỹ cũng được sử dụng rộng rãi ở Campuchia.</p>
            </div>
            <div class="currency-item">
                <h3>Kip Lào (LAK)</h3>
                <p>Được giới thiệu vào năm 1952, Kip đã trải qua lạm phát cao qua nhiều năm.</p>
            </div>
            <div class="currency-item">
                <h3>Kyat Myanmar (MMK)</h3>
                <p>Được giới thiệu vào năm 1952, Kyat được đặt tên theo từ tiếng Phạn có nghĩa là "tiêu chuẩn giá trị".</p>
            </div>
            <div class="currency-item">
                <h3>Đồng Việt Nam (VND)</h3>
                <p>Được giới thiệu vào năm 1978, Đồng đã bị mất giá nhiều lần do lạm phát.</p>
            </div>
            <div class="currency-item">
                <h3>Đô la Timor-Leste (USD)</h3>
                <p>Timor-Leste sử dụng Đô la Mỹ làm tiền tệ chính thức, được áp dụng vào năm 2000.</p>
            </div>
        </div>
    </section>

    <section id="cta" class="cta fade-in">
        <h3>Bắt Đầu Hành Trình Khám Phá Tiền Của Bạn Ngay Hôm Nay!</h3>
        <p>Hãy cùng chúng tôi khám phá thế giới tiền tệ Đông Nam Á...</p>
        @if(Auth::check())
            <button onclick="document.getElementById('image-input')?.click()">Khám Phá Tiền Tệ</button>
        @else
            <button disabled>Đăng nhập để khám phá tiền tệ</button>
        @endif
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

    <script src="{{ asset('static/scripts.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fadeElements = document.querySelectorAll('.fade-in');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            fadeElements.forEach(element => observer.observe(element));
        });
    </script>
</body>
</html>