<div class="container">
    <!-- Login/Signup Section -->
    <div class="comtrainer">
        <div class="welcome-section">
            <div class="welcome-content">
                <h2>Chào Mừng Bạn!</h2>
                <p>Tham gia với chúng tôi để khám phá những điều tuyệt vời.</p>
            </div>
        </div>
        <div class="form-section">
            <div class="form-container">
                <!-- Hiển thị thông báo lỗi hoặc thành công -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Login Form -->
                <form id="login-form" class="auth-form login-form active" action="{{ route('login') }}" method="POST">
                    @csrf
                    <h3>Đăng Nhập</h3>
                    <div class="form-group">
                        <label for="email-login">Email</label>
                        <input type="email" name="email" id="email-login" placeholder="Nhập email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="password-login">Mật khẩu</label>
                        <input type="password" name="password" id="password-login" placeholder="Nhập mật khẩu" required>
                    </div>
                    <button type="submit" class="auth-btn">Đăng Nhập</button>
                    <div class="social-login">
                        <a href="{{ route('auth.google') }}" class="google-btn">
                            <i class="fab fa-google"></i> Đăng nhập bằng Google
                        </a>
                    </div>
                    <div class="switch-form">
                        <span>Chưa có tài khoản? <a href="#" class="switch-link" data-form="signup">Đăng ký</a></span>
                        <span class="forgot-password"> | <a href="#" class="forgot-link">Quên mật khẩu?</a></span>
                    </div>
                </form>

                <!-- Signup Form -->
                <form id="signup-form" class="auth-form signup-form" action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <h3>Đăng Ký</h3>
                    <div class="form-group">
                        <label for="name-signup">Họ và Tên</label>
                        <input type="text" name="name" id="name-signup" placeholder="Nhập họ và tên" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email-signup">Email</label>
                        <input type="email" name="email" id="email-signup" placeholder="Nhập email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="password-signup">Mật khẩu</label>
                        <input type="password" name="password" id="password-signup" placeholder="Nhập mật khẩu" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation-signup">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" id="password_confirmation-signup" placeholder="Xác nhận mật khẩu" required>
                    </div>
                    <div class="form-group">
                        <label for="avatar-signup">Ảnh đại diện (tùy chọn):</label>
                        <input type="file" name="avatar" id="avatar-signup" accept="image/*">
                    </div>
                    <button type="submit" class="auth-btn">Đăng Ký</button>
                    <div class="social-login">
                        <a href="{{ route('auth.google') }}" class="google-btn">
                            <i class="fab fa-google"></i> Đăng ký bằng Google
                        </a>
                    </div>
                    <p class="switch-form below-btn">Đã có tài khoản? <a href="#" class="switch-link" data-form="login">Đăng nhập</a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* General Styles */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Arial', sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Login/Signup Section */
.comtrainer {
    display: flex;
    width: 900px;
    min-height: 600px;
    background: linear-gradient(135deg, #00d8d6, #4ecdc4);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    border: 2px solid #485460;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.welcome-section {
    width: 50%;
    min-height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1e272e;
    padding: 40px;
    position: relative;
}

.welcome-content {
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
}

.welcome-content h2 {
    font-size: 36px;
    margin-bottom: 15px;
    font-weight: 700;
    line-height: 1.2;
}

.welcome-content p {
    font-size: 16px;
    line-height: 1.6;
    max-width: 80%;
    margin: 0 auto;
}

/* Form Section */
.form-section {
    width: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    padding: 40px;
    overflow-y: auto;
}

.form-container {
    width: 100%;
    max-width: 360px;
    position: relative;
}

.auth-form {
    width: 100%;
    padding: 20px 0;
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.auth-form h3 {
    color: #1e272e;
    text-align: center;
    margin-bottom: 25px;
    font-size: 24px;
    font-weight: 600;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-size: 14px;
    color: #333;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 15px;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #00d8d6;
    box-shadow: 0 0 5px rgba(0, 216, 214, 0.3);
}

.auth-btn {
    width: 100%;
    padding: 12px;
    background: #00d8d6;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background 0.3s ease, transform 0.3s ease;
    margin-top: 10px;
}

.auth-btn:hover {
    background: #4ecdc4;
    transform: scale(1.02);
}

.social-login {
    margin-top: 15px;
    text-align: center;
}

.google-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 12px;
    background: #fff;
    color: #333;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.3s ease, transform 0.3s ease;
}

.google-btn i {
    margin-right: 8px;
    color: #db4437;
}

.google-btn:hover {
    background: #f8f9fa;
    transform: scale(1.02);
}

.switch-form {
    text-align: center;
    margin-top: 15px;
    color: #7f8c8d;
    font-size: 14px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.below-btn {
    margin-top: 15px;
}

.switch-link {
    color: #00d8d6;
    text-decoration: none;
    font-weight: bold;
}

.switch-link:hover {
    text-decoration: underline;
}

.forgot-password {
    color: #7f8c8d;
}

.forgot-link {
    color: #00d8d6;
    text-decoration: none;
    font-weight: bold;
}

.forgot-link:hover {
    text-decoration: underline;
}

/* Animation Classes */
.login-form {
    opacity: 1;
    transform: translateX(0);
}

.signup-form {
    opacity: 0;
    transform: translateX(100%);
    position: absolute;
    top: 0;
    left: 0;
}

.login-form.hidden {
    opacity: 0;
    transform: translateX(-100%);
}

.signup-form.active {
    opacity: 1;
    transform: translateX(0);
}

/* Alert Styles */
.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
}

.alert-success {
    background: #d4edda;
    color: #155724;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');
    const switchLinks = document.querySelectorAll('.switch-link');

    switchLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetForm = link.getAttribute('data-form');

            if (targetForm === 'signup') {
                loginForm.classList.add('hidden');
                signupForm.classList.add('active');
            } else {
                loginForm.classList.remove('hidden');
                signupForm.classList.remove('active');
            }
        });
    });
});
</script>