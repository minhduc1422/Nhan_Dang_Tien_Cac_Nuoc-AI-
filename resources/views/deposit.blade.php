<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nạp Tiền</title>
    <link rel="stylesheet" href="/static/styles.css">
    <style>
        .pricing-option {
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .pricing-option.selected {
            background-color: #4CAF50; /* Màu xanh lá khi được chọn */
            color: white;
        }
        .deposit-confirmation {
            text-align: center;
            margin-top: 20px;
        }
        .deposit-confirmation .auth-btn {
            padding: 15px 40px; /* Tăng kích thước nút */
            font-size: 1.2em; /* Tăng cỡ chữ */
            width: 100%; /* Chiếm toàn bộ chiều ngang của container */
            max-width: 300px; /* Giới hạn chiều rộng tối đa */
            margin: 20px auto; /* Căn giữa và thêm khoảng cách phía trên */
            display: block; /* Đảm bảo nút là block element */
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <h3>Mục Lục</h3>
        <ul>
            <li><a href="/">Trang Chủ</a></li>
            <li class="user-info-sidebar">
                <span>Xin chào, {{ Auth::user()->name }} (Tokens: {{ Auth::user()->tokens }})</span>
            </li>
            <li>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Đăng Xuất</button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Header -->
    <header>
        <div class="logo">Khám Phá Tiền Tệ</div>
    </header>

    <!-- Deposit Section -->
    <section class="deposit-section">
        <h2>Nạp Tiền</h2>
        <p>Vui lòng chọn gói nạp tiền, quét mã QR để thanh toán, và xác nhận nạp tiền.</p>
        <div class="pricing-table">
            <div class="pricing-option" onclick="selectPackage(50000, 20)">
                <h3>50.000 VND</h3>
                <p>20 lần sử dụng</p>
            </div>
            <div class="pricing-option" onclick="selectPackage(100000, 100)">
                <h3>100.000 VND</h3>
                <p>100 lần sử dụng</p>
            </div>
            <div class="pricing-option" onclick="selectPackage(500000, 650)">
                <h3>500.000 VND</h3>
                <p>650 lần sử dụng</p>
            </div>
        </div>
        <div class="qr-code">
            <img src="/static/image/hinhnen_6.png" alt="Mã QR Thanh Toán">
            <p>Quét mã QR để nạp tiền</p>
        </div>
        <div class="deposit-confirmation">
            <form id="deposit-confirmation-form" enctype="multipart/form-data">
                @csrf
                <label for="proof-image">Tải lên ảnh xác nhận nạp tiền:</label>
                <input type="file" id="proof-image" name="proof_image" accept="image/*" required>
                <input type="hidden" id="deposit-amount" name="amount">
                <input type="hidden" id="deposit-tokens" name="tokens">
                <button type="submit" class="auth-btn">Xác nhận nạp tiền</button>
            </form>
            <p id="confirmation-status" style="margin-top: 10px;"></p>
        </div>
    </section>

    <script>
        let selectedPackage = null;

        function selectPackage(amount, tokens) {
            // Xóa lớp selected khỏi tất cả các gói
            document.querySelectorAll('.pricing-option').forEach(option => {
                option.classList.remove('selected');
            });

            // Thêm lớp selected vào gói được chọn
            const selectedOption = event.currentTarget;
            selectedOption.classList.add('selected');

            // Cập nhật giá trị amount và tokens
            document.getElementById('deposit-amount').value = amount;
            document.getElementById('deposit-tokens').value = tokens;

            selectedPackage = { amount, tokens };
        }

        document.getElementById('deposit-confirmation-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const statusP = document.getElementById('confirmation-status');
            const amount = document.getElementById('deposit-amount').value;
            const tokens = document.getElementById('deposit-tokens').value;

            // Kiểm tra xem người dùng đã chọn gói nạp tiền chưa
            if (!amount || !tokens) {
                statusP.style.color = 'red';
                statusP.textContent = 'Lỗi: Vui lòng chọn gói nạp tiền trước khi xác nhận!';
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                statusP.style.color = 'red';
                statusP.textContent = 'Lỗi: Không tìm thấy CSRF token. Vui lòng reload trang.';
                return;
            }

            try {
                // Gửi yêu cầu nạp tiền trước
                const depositResponse = await fetch('/deposit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ amount, tokens })
                });

                const depositData = await depositResponse.json();
                if (!depositResponse.ok) {
                    throw new Error(depositData.message || 'Không thể gửi yêu cầu nạp tiền!');
                }

                // Gửi xác nhận với ảnh
                const confirmationResponse = await fetch('/deposit-confirmation', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                const text = await confirmationResponse.text();
                console.log('Phản hồi từ server:', text);

                let data;
                try {
                    data = JSON.parse(text);
                } catch (jsonError) {
                    throw new Error('Phản hồi không phải JSON: ' + text);
                }

                if (confirmationResponse.ok) {
                    statusP.style.color = 'green';
                    statusP.textContent = data.message;
                } else {
                    throw new Error(data.message || `Lỗi ${confirmationResponse.status}: Không thể xác nhận nạp tiền`);
                }
            } catch (error) {
                statusP.style.color = 'red';
                statusP.textContent = 'Lỗi: ' + error.message;
            }
        });
    </script>
</body>
</html>