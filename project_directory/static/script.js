async function uploadImage() {
    const imageInput = document.getElementById('imageInput');
    const resultDiv = document.getElementById('result');

    if (!imageInput.files[0]) {
        resultDiv.innerHTML = '<p style="color: red;">Vui lòng chọn một ảnh!</p>';
        return;
    }

    const formData = new FormData();
    formData.append('file', imageInput.files[0]);

    resultDiv.innerHTML = '<p>Đang xử lý...</p>';

    try {
        const response = await fetch('/upload', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Lỗi khi gửi yêu cầu: ${response.statusText} (Status: ${response.status})`);
        }

        const data = await response.json();

        let resultHTML = '<h2>Kết quả nhận diện:</h2>';
        if (data.detections.length === 0) {
            resultHTML += '<p>Không phát hiện tờ tiền nào trong ảnh.</p>';
        } else {
            data.detections.forEach(detection => {
                resultHTML += `
                    <div class="detection">
                        <p><strong>Mệnh giá:</strong> ${detection.value} VND</p>
                        <p><strong>Độ tin cậy:</strong> ${(detection.confidence * 100).toFixed(2)}%</p>
                        <p><strong>Vị trí (bounding box):</strong> [${detection.box.join(', ')}]</p>
                    </div>
                `;
            });
        }

        resultHTML += `<img src="data:image/jpeg;base64,${data.image_base64}" alt="Ảnh với bounding boxes">`;
        resultDiv.innerHTML = resultHTML;
    } catch (error) {
        resultDiv.innerHTML = `<p style="color: red;">Lỗi: ${error.message}</p>`;
    }
}

function toggleChatbox() {
    const chatboxContainer = document.getElementById('chatboxContainer');
    const chatbox = document.getElementById('chatbox');
    const chatInput = document.querySelector('.chatbox-input');
    const toggleBtn = document.getElementById('toggleBtn');

    if (chatboxContainer.classList.contains('minimized')) {
        chatboxContainer.classList.remove('minimized');
        chatbox.style.display = 'block';
        chatInput.style.display = 'flex';
        toggleBtn.textContent = '-';
    } else {
        chatboxContainer.classList.add('minimized');
        chatbox.style.display = 'none';
        chatInput.style.display = 'none';
        toggleBtn.textContent = '+';
    }
}

async function sendMessage() {
    const chatInput = document.getElementById('chatInput');
    const chatbox = document.getElementById('chatbox');
    const question = chatInput.value.trim();

    if (!question) return;

    chatbox.innerHTML += `<p class="user">Bạn: ${question}</p>`;
    chatInput.value = '';
    chatbox.scrollTop = chatbox.scrollHeight;

    try {
        const response = await fetch('/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query: question })
        });

        if (!response.ok) {
            throw new Error(`Lỗi khi gửi câu hỏi: ${response.statusText} (Status: ${response.status})`);
        }

        const data = await response.json();
        chatbox.innerHTML += `<p class="bot">Trợ lý AI: ${data.result}</p>`;
    } catch (error) {
        chatbox.innerHTML += `<p class="bot">Trợ lý AI: Lỗi: ${error.message}</p>`;
    }

    chatbox.scrollTop = chatbox.scrollHeight;
}