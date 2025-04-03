async function uploadImage() {
    let input = document.getElementById("image-input");
    let file = input.files[0];
    if (!file) {
        alert("Vui lòng chọn ảnh!");
        return;
    }

    let formData = new FormData();
    formData.append("image", file);

    document.getElementById("loading").style.display = "block";

    try {
        let response = await fetch("/detect-money", {
            method: "POST",
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        let data = await response.json();

        document.getElementById("loading").style.display = "none";

        if (!response.ok) {
            throw new Error(data.error || "Lỗi không xác định!");
        }

        if (data.image) {
            document.getElementById("result-image").src = data.image;
            document.getElementById("result-container").style.display = "block";

            let detectionInfo = document.getElementById("detection-info");
            if (data.detection_info && data.detection_info.denomination) {
                detectionInfo.innerHTML = `
                    <p><strong>Mệnh giá:</strong> ${data.detection_info.denomination}</p>
                    <p><strong>Độ tin cậy:</strong> ${data.detection_info.confidence}</p>
                `;
            } else {
                detectionInfo.innerHTML = "<p>Không nhận diện được mệnh giá!</p>";
            }

            let moneyDetails = document.getElementById("money-details");
            if (data.money_details && data.money_details.year_of_issue) {
                moneyDetails.innerHTML = `
                    <p><strong>Năm phát hành:</strong> ${data.money_details.year_of_issue}</p>
                    <p><strong>Mô tả:</strong> ${data.money_details.description}</p>
                `;
            } else {
                moneyDetails.innerHTML = "<p>Không có thông tin chi tiết!</p>";
            }

            document.getElementById("result-error").innerText = "";
        } else {
            document.getElementById("result-error").innerText = "Không thể nhận diện!";
            document.getElementById("result-container").style.display = "none";
        }
    } catch (error) {
        document.getElementById("loading").style.display = "none";
        document.getElementById("result-error").innerText = error.message;
        document.getElementById("result-container").style.display = "none";
    }
}

async function sendMessage() {
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const question = chatInput.value.trim();

    if (!question) {
        alert('Vui lòng nhập câu hỏi!');
        return;
    }

    const userMessage = document.createElement('div');
    userMessage.className = 'message user-message';
    userMessage.textContent = "Bạn: " + question;
    chatMessages.appendChild(userMessage);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    chatInput.value = '';

    const loadingMessage = document.createElement('div');
    loadingMessage.className = 'message bot-message';
    loadingMessage.textContent = "Bot đang trả lời...";
    chatMessages.appendChild(loadingMessage);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            throw new Error('Không tìm thấy CSRF token. Vui lòng reload trang.');
        }

        console.log('Gửi yêu cầu POST đến /chat với câu hỏi:', question); // Thêm log để debug

        const response = await fetch('/chat', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ question: question })
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Lỗi từ server: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        loadingMessage.remove();

        const botMessage = document.createElement('div');
        botMessage.className = 'message bot-message';
        botMessage.textContent = "Bot: " + (data.response || "Không có phản hồi.");
        chatMessages.appendChild(botMessage);
    } catch (error) {
        loadingMessage.remove();

        const botMessage = document.createElement('div');
        botMessage.className = 'message bot-message';
        botMessage.textContent = "Lỗi: " + error.message;
        chatMessages.appendChild(botMessage);
    }

    chatMessages.scrollTop = chatMessages.scrollHeight;
}

document.getElementById('chat-input')?.addEventListener('keypress', function(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
});

function toggleChatbox() {
    const chatContainer = document.getElementById('chat-container');
    chatContainer.classList.toggle('active');
}