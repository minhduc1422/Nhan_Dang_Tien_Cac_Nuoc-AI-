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

        const blob = await response.blob();
        resultImage.src = URL.createObjectURL(blob);
        resultImage.style.display = 'block';

        const denominations = [
            { denomination: "100,000 VND", error: 5 },
            { denomination: "50,000 THB", error: 3 },
            { denomination: "1,000,000 IDR", error: 7 },
            { denomination: "50 MYR", error: 4 }
        ];
        const randomResult = denominations[Math.floor(Math.random() * denominations.length)];

        resultDenomination.textContent = `Mệnh giá: ${randomResult.denomination}`;
        resultError.textContent = `Độ sai: ${randomResult.error}%`;

        resultContainer.style.display = 'block';
    } catch (error) {
        alert('Lỗi: ' + error.message);
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

// Prevent form submission (for demo purposes on auth pages)
document.getElementById('login-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    alert('Đăng nhập thành công (Demo)!');
});

document.getElementById('signup-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    alert('Đăng ký thành công (Demo)!');
});