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
                <!-- Login Form -->
                <form id="login-form" class="auth-form login-form active" action="{{ route('login') }}" method="POST">
                    @csrf
                    <h3>Đăng Nhập</h3>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Mật khẩu" required>
                    </div>
                    <button type="submit" class="auth-btn">Đăng Nhập</button>
                    <div class="switch-form">
                        <span>Chưa có tài khoản? <a href="#" class="switch-link" data-form="signup">Đăng ký</a></span>
                        <span class="forgot-password"> | <a href="#" class="forgot-link">Quên mật khẩu?</a></span>
                    </div>
                </form>

                <!-- Signup Form -->
                <form id="signup-form" class="auth-form signup-form" action="{{ route('register') }}" method="POST">
                    @csrf
                    <h3>Đăng Ký</h3>
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Họ và Tên" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Mật khẩu" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password_confirmation" placeholder="Xác nhận mật khẩu" required>
                    </div>
                    <button type="submit" class="auth-btn">Đăng Ký</button>
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
    height: 600px;
    background: #1e272e;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    border: 2px solid #485460;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.welcome-section {
    width: 50%;
    height: 100%;
    background: linear-gradient(135deg, #00d8d6, #4ecdc4);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1e272e;
    padding: 40px;
}

.welcome-content {
    text-align: center;
}

.welcome-content h2 {
    font-size: 36px;
    margin-bottom: 20px;
    font-weight: 700;
}

.welcome-content p {
    font-size: 16px;
    line-height: 1.6;
    max-width: 85%;
}

/* Form Section */
.form-section {
    width: 50%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    padding: 40px;
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
    margin-bottom: 18px;
}

.form-group input {
    width: 100%;
    padding: 12px;
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
    transition: background 0.3s ease;
}

.auth-btn:hover {
    background: #4ecdc4;
}

.switch-form {
    text-align: center;
    margin-top: 18px;
    color: #7f8c8d;
    font-size: 14px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.below-btn {
    margin-top: 18px;
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