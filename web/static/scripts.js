// Upload ảnh để nhận diện
async function uploadImage() {
    const fileInput = document.getElementById('image-input');
    const file = fileInput.files[0];
    if (!file) {
        alert('Vui lòng chọn một hình ảnh!');
        return;
    }

    const formData = new FormData();
    formData.append('file', file);

    const loading = document.getElementById('loading');
    const resultContainer = document.getElementById('result-container');
    const resultImage = document.getElementById('result-image');
    const resultDenomination = document.getElementById('result-denomination');
    const resultError = document.getElementById('result-error');

    loading.style.display = 'block';
    resultContainer.style.display = 'none';

    try {
        const response = await fetch('http://localhost:8000/detect_money', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Lỗi khi nhận diện tiền');
        }

        const data = await response.json();
        // Chuyển hex thành binary và tạo URL cho ảnh
        const byteArray = new Uint8Array(data.image.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));
        const blob = new Blob([byteArray], { type: 'image/jpeg' });
        resultImage.src = URL.createObjectURL(blob);
        resultImage.style.display = 'block';

        // Hiển thị thông tin phát hiện thực tế
        if (data.detections && data.detections.length > 0) {
            const detection = data.detections[0]; // Lấy phát hiện đầu tiên
            resultDenomination.textContent = `Mệnh giá: ${detection.class} (${detection.model})`;
            resultError.textContent = `Độ tin cậy: ${(detection.confidence * 100).toFixed(2)}%`;
        } else {
            resultDenomination.textContent = 'Không nhận diện được tiền tệ';
            resultError.textContent = '';
        }

        resultContainer.style.display = 'block';
    } catch (error) {
        alert('Lỗi: ' + error.message);
        resultError.textContent = 'Đã xảy ra lỗi khi nhận diện';
        resultContainer.style.display = 'block';
    } finally {
        loading.style.display = 'none';
    }
}

// Gửi tin nhắn cho chatbot
async function sendMessage() {
    const chatInput = document.getElementById('chat-input');
    const question = chatInput.value.trim();
    if (!question) {
        alert('Vui lòng nhập câu hỏi!');
        return;
    }

    const chatMessages = document.getElementById('chat-messages');
    const userMessage = document.createElement('div');
    userMessage.className = 'message user-message';
    userMessage.textContent = question;
    chatMessages.appendChild(userMessage);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    chatInput.value = '';

    try {
        const response = await fetch('http://localhost:8000/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ question: question })
        });

        if (!response.ok) {
            throw new Error('Lỗi từ chatbot');
        }

        const data = await response.json();
        const botMessage = document.createElement('div');
        botMessage.className = 'message bot-message';
        botMessage.textContent = data.response;
        chatMessages.appendChild(botMessage);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    } catch (error) {
        const botMessage = document.createElement('div');
        botMessage.className = 'message bot-message';
        botMessage.textContent = 'Đã xảy ra lỗi: ' + error.message;
        chatMessages.appendChild(botMessage);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// Gửi tin nhắn bằng phím Enter
document.getElementById('chat-input')?.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
});

// Toggle chatbox
function toggleChatbox() {
    const chatContainer = document.getElementById('chat-container');
    const chatMessages = document.getElementById('chat-messages');
    const chatInputContainer = document.getElementById('chat-input-container');
    
    if (chatContainer.classList.contains('collapsed')) {
        chatContainer.classList.remove('collapsed');
        chatMessages.style.display = 'block';
        chatInputContainer.style.display = 'flex';
    } else {
        chatContainer.classList.add('collapsed');
        chatMessages.style.display = 'none';
        chatInputContainer.style.display = 'none';
    }
}

// Cập nhật sidebar khi trang tải
document.addEventListener('DOMContentLoaded', () => {
    const userInfo = document.getElementById('user-info');
    const userName = document.getElementById('user-name');
    const loginLink = document.getElementById('login-link');
    const logoutLink = document.getElementById('logout');

    const user = JSON.parse(localStorage.getItem('user'));
    console.log('User từ localStorage:', user); // Debug

    if (user && user.ten_kh) {
        userInfo.style.display = 'block'; // Hiển thị "Xin chào [tên]!" và "Đăng xuất"
        userName.textContent = user.ten_kh;
        loginLink.style.display = 'none'; // Ẩn "Đăng nhập/Đăng ký"
    } else {
        userInfo.style.display = 'none'; // Ẩn "Xin chào [tên]!" và "Đăng xuất"
        loginLink.style.display = 'block'; // Hiển thị "Đăng nhập/Đăng ký"
    }

    // Xử lý đăng xuất
    logoutLink.addEventListener('click', (e) => {
        e.preventDefault();
        localStorage.removeItem('user');
        window.location.href = '/'; // Chuyển về trang chủ sau khi đăng xuất
    });
});z