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
        /* CSS cho thanh mục lục ngang */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #1a1a1a;
            padding: 8px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .topbar h3 {
            color: #fff;
            margin: 0;
            font-size: 1.1em;
        }
        .topbar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        .topbar ul li {
            margin: 0 10px;
        }
        .topbar ul li a, .topbar ul li .deposit-btn, .topbar ul li .logout-btn {
            color: #fff;
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 0.85em;
            transition: background 0.3s ease;
        }
        .topbar ul li a:hover, .topbar ul li .deposit-btn:hover, .topbar ul li .logout-btn:hover {
            background: #555;
        }
        .topbar ul li form {
            display: inline;
        }
        .user-info-topbar {
            display: flex;
            align-items: center;
        }
        .user-avatar {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
            cursor: pointer;
            border: 2px solid #ff8c00;
        }
        .user-info-topbar span {
            color: #fff;
            font-size: 0.85em;
        }
        /* Điều chỉnh các phần khác để không bị che bởi topbar */
        header {
            margin-top: 50px;
        }
        /* Các CSS khác giữ nguyên */
        .loader {
            width: 100px;
            height: 15px;
            border-radius: 15px;
            background: linear-gradient(orange 0 0) 0/0% no-repeat lightblue;
            animation: l2 2s infinite steps(10);
            margin: 8px auto;
        }
        @keyframes l2 {
            100% {background-size:110%}
        }
        #loading {
            text-align: center;
            display: none;
        }
        #loading p {
            margin: 4px 0;
            font-size: 0.9em;
            color: #2c3e50;
        }
        #chat-container {
            position: fixed;
            bottom: 15px;
            right: 15px;
            width: 280px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        #chat-header {
            background: #2c3e50;
            color: white;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            font-size: 0.9em;
        }
        #chat-messages {
            max-height: 250px;
            overflow-y: auto;
            padding: 8px;
            display: none;
        }
        #chat-input-container {
            display: flex;
            padding: 8px;
            border-top: 1px solid #ddd;
            display: none;
        }
        #chat-input {
            flex: 1;
            padding: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 4px;
            font-size: 0.8em;
        }
        #chat-send {
            padding: 4px 8px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8em;
        }
        #chat-container.active #chat-messages,
        #chat-container.active #chat-input-container {
            display: block;
        }
        .message {
            margin: 4px 0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }
        .user-message {
            background: hsl(136, 91.50%, 46.10%);
            color: black;
            margin-left: 20%;
            margin-right: 4px;
        }
        .bot-message {
            background: #f1f1f1;
            margin-right: 20%;
            margin-left: 4px;
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
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin: 15px auto;
            max-width: 100%;
        }
        #result-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .info-bar {
            background: #e8f4f8;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #d1e7dd;
            font-size: 0.9em;
            color: #333;
        }
        .info-bar p {
            margin: 4px 0;
        }
        #result-error {
            text-align: center;
            font-size: 0.9em;
            margin-top: 8px;
        }
        .token-display {
            background: #2c3e50;
            color: white;
            padding: 8px 15px;
            border-radius: 15px;
            margin: 8px auto;
            display: inline-block;
            font-size: 0.9em;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .token-display i {
            margin-right: 4px;
        }
        /* Tích hợp styles.css với kích thước điều chỉnh */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f8f9fa;
            color: #333;
            background: url('https://www.transparenttextures.com/patterns/arabesque.png'), #fff;
        }
        section {
            background: transparent !important;
        }
        header {
            padding: 15px 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 1200px;
            margin: 50px auto 0;
            position: relative;
        }
        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: url('https://www.transparenttextures.com/patterns/arabesque.png');
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .hero {
            position: relative;
            padding: 30px 20px;
            max-width: 1200px;
            margin: 0 auto;
            height: auto;
            min-height: 500px;
        }
        #bg-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }
        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        .hero-content h1 {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .hero-content p {
            font-size: 16px;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        #upload-container {
            margin-bottom: 15px;
        }
        #image-input {
            margin: 8px auto;
            display: block;
            width: 100%;
            max-width: 300px;
        }
        #upload-button {
            padding: 8px 16px;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16;
        }
        #upload-button:hover {
            background-color: #34495e;
        }
        #loading {
            margin-top: 15px;
            font-size: 16px;
            color: #fff;
        }
        .info-bar {
            background-color: #E6F7FA;
            border-radius: 6px;
            padding: 10px;
            margin-top: 8px;
            text-align: left;
            color: #333;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        #detection-info {
            border-left: 3px solid #00c4cc;
        }
        #money-details {
            border-left: 3px solid #4ecdc4;
        }
        .info-bar p {
            margin: 4px 0;
            font-size: 14px;
            color: #333;
        }
        .info-bar strong {
            color: #1a2a44;
        }
        .discover {
            padding: 30px 20px;
            text-align: center;
            background-color: #fff;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }
        .discover-header {
            text-align: center;
        }
        .discover h2 {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .discover p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .discover-grid {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .discover-item {
            display: flex;
            flex-direction: row;
            align-items: center;
            background-color: #fff;
            border-radius: 8px;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            box-shadow: #ccc 1px 2px 8px 3px;
        }
        .discover-item img {
            width: 100%;
            max-width: 400px;
            height: auto;
            border-radius: 8px;
            margin: 0 15px;
        }
        .discover-text {
            flex: 1;
            padding: 0 15px;
            max-width: 500px;
        }
        .discover-text h3 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .discover-text p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
        }
        .currencies {
            padding: 30px 20px;
            text-align: center;
            background-color: #f8f9fa;
            max-width: 1200px;
            margin: 0 auto;
        }
        .currencies-header {
            text-align: center;
        }
        .currencies h2 {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .currencies p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .currencies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .currency-item {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: #ccc 1px 2px 8px 3px;
        }
        .currency-item h3 {
            font-size: 20px;
            margin-bottom: 8px;
        }
        .currency-item p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }
        .cta {
            text-align: center;
            padding: 30px 20px;
            background-color: #1a2a44;
            color: #fff;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            background: url('https://www.transparenttextures.com/patterns/arabesque.png'), #1a2a44;
        }
        .cta h3 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .cta p {
            font-size: 16px;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .cta button {
            padding: 8px 16px;
            background-color: #fff;
            color: #333;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .cta button:hover {
            background-color: #eee;
        }
        footer {
            text-align: center;
            padding: 30px 20px;
            background-color: #fff;
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-nav {
            margin-bottom: 15px;
        }
        .footer-nav a {
            margin: 0 10px;
            color: #333;
            text-decoration: none;
            font-size: 14px;
        }
        .footer-nav a:hover {
            color: #00c4cc;
        }
        .footer-social {
            margin-bottom: 15px;
        }
        .footer-social a {
            margin: 0 8px;
            color: #333;
            font-size: 20px;
            text-decoration: none;
        }
        .footer-social a:hover {
            color: #00c4cc;
        }
        footer p {
            color: #666;
            font-size: 14px;
        }
        /* Media queries cho di động */
        @media (max-width: 768px) {
            .topbar {
                padding: 8px 10px;
            }
            .topbar h3 {
                font-size: 1em;
            }
            .topbar ul li {
                margin: 0 8px;
            }
            .topbar ul li a, .topbar ul li .deposit-btn, .topbar ul li .logout-btn {
                font-size: 0.8em;
                padding: 3px 6px;
            }
            .user-info-topbar span {
                font-size: 0.8em;
            }
            .user-avatar {
                width: 22px;
                height: 22px;
                margin-right: 6px;
            }
            header {
                padding: 10px 15px;
            }
            .logo {
                font-size: 20px;
            }
            .hero {
                padding: 20px 15px;
                min-height: 400px;
            }
            .hero-content h1 {
                font-size: 28px;
                margin-bottom: 10px;
            }
            .hero-content p {
                font-size: 14px;
                margin-bottom: 15px;
            }
            #upload-container {
                margin-bottom: 10px;
            }
            #image-input {
                max-width: 250px;
            }
            #upload-button {
                padding: 6px 12px;
                font-size: 14px;
            }
            #loading {
                margin-top: 10px;
                font-size: 14px;
            }
            .loader {
                width: 80px;
                height: 12px;
                margin: 6px auto;
            }
            #loading p {
                font-size: 0.8em;
            }
            #result-container {
                padding: 10px;
                margin: 10px auto;
            }
            #result-image {
                margin-bottom: 8px;
            }
            .info-bar {
                padding: 8px;
                margin: 6px 0;
                font-size: 0.8em;
            }
            .info-bar p {
                font-size: 13px;
            }
            #result-error {
                font-size: 0.8em;
                margin-top: 6px;
            }
            .token-display {
                padding: 6px 12px;
                font-size: 0.8em;
                margin: 6px auto;
            }
            .discover {
                padding: 20px 15px;
            }
            .discover h2 {
                font-size: 24px;
                margin-bottom: 10px;
            }
            .discover p {
                font-size: 14px;
                margin-bottom: 20px;
            }
            .discover-item {
                max-width: 100%;
                margin: 0 auto;
            }
            .discover-item img {
                max-width: 300px;
                margin: 0 10px;
            }
            .discover-text {
                padding: 0 10px;
                max-width: 400px;
            }
            .discover-text h3 {
                font-size: 20px;
                margin-bottom: 6px;
            }
            .discover-text p {
                font-size: 14px;
            }
            .currencies {
                padding: 20px 15px;
            }
            .currencies h2 {
                font-size: 24px;
                margin-bottom: 10px;
            }
            .currencies p {
                font-size: 14px;
                margin-bottom: 20px;
            }
            #youtube-video-wrapper {
                margin: 10px auto;
            }
            #youtube-video-wrapper iframe {
                height: 180px;
            }
            .currencies-grid {
                gap: 10px;
            }
            .currency-item {
                padding: 10px;
            }
            .currency-item h3 {
                font-size: 18px;
                margin-bottom: 6px;
            }
            .currency-item p {
                font-size: 13px;
            }
            .cta {
                padding: 20px 15px;
            }
            .cta h3 {
                font-size: 22px;
                margin-bottom: 10px;
            }
            .cta p {
                font-size: 14px;
                margin-bottom: 15px;
            }
            .cta button {
                padding: 6px 12px;
                font-size: 14px;
            }
            footer {
                padding: 20px 15px;
            }
            .footer-nav a {
                margin: 0 8px;
                font-size: 12px;
            }
            .footer-social a {
                margin: 0 6px;
                font-size: 18px;
            }
            footer p {
                font-size: 12px;
            }
            #chat-container {
                width: 90%;
                bottom: 10px;
                right: 10px;
            }
            #chat-header {
                padding: 6px;
                font-size: 0.85em;
            }
            #chat-messages {
                max-height: 200px;
                padding: 6px;
            }
            #chat-input-container {
                padding: 6px;
            }
            #chat-input {
                font-size: 0.75em;
                padding: 3px;
            }
            #chat-send {
                font-size: 0.75em;
                padding: 3px 6px;
            }
            .message {
                font-size: 0.75em;
                padding: 3px 6px;
            }
        }
        @media (max-width: 480px) {
            .topbar {
                padding: 6px 8px;
            }
            .topbar h3 {
                font-size: 0.9em;
            }
            .topbar ul li {
                margin: 0 6px;
            }
            .topbar ul li a, .topbar ul li .deposit-btn, .topbar ul li .logout-btn {
                font-size: 0.75em;
                padding: 2px 5px;
            }
            .user-avatar {
                width: 20px;
                height: 20px;
                margin-right: 5px;
            }
            .user-info-topbar span {
                font-size: 0.75em;
            }
            header {
                padding: 8px 10px;
            }
            .logo {
                font-size: 18px;
            }
            .hero {
                padding: 15px 10px;
                min-height: 350px;
            }
            .hero-content h1 {
                font-size: 24px;
            }
            .hero-content p {
                font-size: 12px;
            }
            #image-input {
                max-width: 200px;
            }
            #upload-button {
                padding: 5px 10px;
                font-size: 12px;
            }
            #loading {
                margin-top: 8px;
                font-size: 12px;
            }
            .loader {
                width: 60px;
                height: 10px;
            }
            #loading p {
                font-size: 0.75em;
            }
            #result-container {
                padding: 8px;
                margin: 8px auto;
            }
            .info-bar {
                padding: 6px;
                font-size: 0.75em;
            }
            .info-bar p {
                font-size: 12px;
            }
            #result-error {
                font-size: 0.75em;
            }
            .token-display {
                padding: 5px 10px;
                font-size: 0.75em;
            }
            .discover {
                padding: 15px 10px;
            }
            .discover h2 {
                font-size: 20px;
            }
            .discover p {
                font-size: 12px;
            }
            .discover-item img {
                max-width: 250px;
                margin: 0 8px;
            }
            .discover-text h3 {
                font-size: 18px;
            }
            .discover-text p {
                font-size: 12px;
            }
            .currencies {
                padding: 15px 10px;
            }
            .currencies h2 {
                font-size: 20px;
            }
            .currencies p {
                font-size: 12px;
            }
            #youtube-video-wrapper iframe {
                height: 150px;
            }
            .currency-item h3 {
                font-size: 16px;
            }
            .currency-item p {
                font-size: 12px;
            }
            .cta {
                padding: 15px 10px;
            }
            .cta h3 {
                font-size: 20px;
            }
            .cta p {
                font-size: 12px;
            }
            .cta button {
                padding: 5px 10px;
                font-size: 12px;
            }
            footer {
                padding: 15px 10px;
            }
            .footer-nav a {
                font-size: 11px;
                margin: 0 6px;
            }
            .footer-social a {
                font-size: 16px;
                margin: 0 5px;
            }
            footer p {
                font-size: 11px;
            }
            #chat-container {
                width: 95%;
            }
            #chat-header {
                font-size: 0.8em;
                padding: 5px;
            }
            #chat-messages {
                max-height: 180px;
            }
            #chat-input {
                font-size: 0.7em;
            }
            #chat-send {
                font-size: 0.7em;
            }
            .message {
                font-size: 0.7em;
            }
        }
    </style>
</head>
<body>
    <nav class="topbar">
        <h3>Mục Lục</h3>
        <ul>
            <li><a href="#hero">Giới Thiệu</a></li>
            <li><a href="#discover">Khám Phá</a></li>
            <li><a href="#currencies">Tiền Tệ Đông Nam Á</a></li>
            <li><a href="#cta">Bắt Đầu Hành Trình</a></li>
            @if(!Auth::check())
                <li><a href="{{ route('login') }}">Đăng Nhập/Đăng Ký</a></li>
            @else
                <li class="user-info-topbar">
                    <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://via.placeholder.com/40' }}" alt="Avatar" class="user-avatar" onclick="goToProfile()">
                    <span id="topbar-token-display">Xin chào, {{ Auth::user()->name }} (lần sử dụng: {{ Auth::user()->tokens }})</span>
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