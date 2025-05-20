// async function uploadImage() {
//     let input = document.getElementById("image-input");
//     let file = input.files[0];
//     if (!file) {
//         alert("Vui lòng chọn ảnh!");
//         return;
//     }

//     let formData = new FormData();
//     formData.append("image", file);

//     document.getElementById("loading").style.display = "block";

//     try {
//         let response = await fetch("/detect-money", {
//             method: "POST",
//             body: formData,
//             headers: {
//                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
//             }
//         });
//         let data = await response.json();

//         document.getElementById("loading").style.display = "none";

//         if (!response.ok) {
//             throw new Error(data.error || "Lỗi không xác định!");
//         }

//         if (data.image) {
//             document.getElementById("result-image").src = data.image;
//             document.getElementById("result-container").style.display = "block";

//             let detectionInfo = document.getElementById("detection-info");
//             if (data.detection_info && data.detection_info.denomination) {
//                 detectionInfo.innerHTML = `
//                     <p><strong>Mệnh giá:</strong> ${data.detection_info.denomination}</p>
//                     <p><strong>Độ tin cậy:</strong> ${data.detection_info.confidence}</p>
//                 `;
//             } else {
//                 detectionInfo.innerHTML = "<p>Không nhận diện được mệnh giá!</p>";
//             }

//             let moneyDetails = document.getElementById("money-details");
//             if (data.money_details && data.money_details.year_of_issue) {
//                 moneyDetails.innerHTML = `
//                     <p><strong>Năm phát hành:</strong> ${data.money_details.year_of_issue}</p>
//                     <p><strong>Mô tả:</strong> ${data.money_details.description}</p>
//                 `;
//             } else {
//                 moneyDetails.innerHTML = "<p>Không có thông tin chi tiết!</p>";
//             }

//             document.getElementById("result-error").innerText = "";
//         } else {
//             document.getElementById("result-error").innerText = "Không thể nhận diện!";
//             document.getElementById("result-container").style.display = "none";
//         }
//     } catch (error) {
//         document.getElementById("loading").style.display = "none";
//         document.getElementById("result-error").innerText = error.message;
//         document.getElementById("result-container").style.display = "none";
//     }
// }

// async function sendMessage() {
//     const chatInput = document.getElementById('chat-input');
//     const chatMessages = document.getElementById('chat-messages');
//     const question = chatInput.value.trim();

//     if (!question) {
//         alert('Vui lòng nhập câu hỏi!');
//         return;
//     }

//     const userMessage = document.createElement('div');
//     userMessage.className = 'message user-message';
//     userMessage.textContent = "Bạn: " + question;
//     chatMessages.appendChild(userMessage);
//     chatMessages.scrollTop = chatMessages.scrollHeight;

//     chatInput.value = '';

//     const loadingMessage = document.createElement('div');
//     loadingMessage.className = 'message bot-message';
//     loadingMessage.textContent = "Bot đang trả lời...";
//     chatMessages.appendChild(loadingMessage);
//     chatMessages.scrollTop = chatMessages.scrollHeight;

//     try {
//         const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
//         if (!csrfToken) {
//             throw new Error('Không tìm thấy CSRF token. Vui lòng reload trang.');
//         }

//         console.log('Gửi yêu cầu POST đến /chat với câu hỏi:', question);

//         const response = await fetch('/chat', {
//             method: 'POST',
//             headers: { 
//                 'Content-Type': 'application/json',
//                 'X-CSRF-TOKEN': csrfToken
//             },
//             body: JSON.stringify({ question: question })
//         });

//         if (!response.ok) {
//             const errorText = await response.text();
//             throw new Error(`Lỗi từ server: ${response.status} - ${errorText}`);
//         }

//         const data = await response.json();
//         loadingMessage.remove();

//         const botMessage = document.createElement('div');
//         botMessage.className = 'message bot-message';
//         botMessage.textContent = "Bot: " + (data.response || "Không có phản hồi.");
//         chatMessages.appendChild(botMessage);
//     } catch (error) {
//         loadingMessage.remove();

//         const botMessage = document.createElement('div');
//         botMessage.className = 'message bot-message';
//         botMessage.textContent = "Lỗi: " + error.message;
//         chatMessages.appendChild(botMessage);
//     }

//     chatMessages.scrollTop = chatMessages.scrollHeight;
// }

// document.getElementById('chat-input')?.addEventListener('keypress', function(event) {
//     if (event.key === 'Enter' && !event.shiftKey) {
//         event.preventDefault();
//         sendMessage();
//     }
// });

// function toggleChatbox() {
//     const chatContainer = document.getElementById('chat-container');
//     chatContainer.classList.toggle('active');
// }

// function goToProfile() {
//     window.location.href = "/profile";
// }






// async function uploadImage() {
//     const input = document.getElementById("image-input");
//     const file = input.files[0];
//     if (!file) {
//         showError("Vui lòng chọn ảnh!");
//         return;
//     }

//     const formData = new FormData();
//     formData.append("file", file);

//     showLoading(true);

//     try {
//         const token = localStorage.getItem("auth_token");
//         const headers = {};
//         if (token) {
//             headers["Authorization"] = `Bearer ${token}`;
//         }

//         const response = await fetchWithRetry("http://192.168.77.67:55015/detect_money", {
//             method: "POST",
//             body: formData,
//             headers: headers
//         });

//         const data = await response.json();

//         showLoading(false);

//         if (!response.ok) {
//             throw new Error(data.detail || "Lỗi không xác định!");
//         }

//         const resultContainer = document.getElementById("result-container");
//         const resultImage = document.getElementById("result-image");
//         const detectionInfo = document.getElementById("detection-info");
//         const moneyDetails = document.getElementById("money-details");
//         const resultError = document.getElementById("result-error");

//         if (data.image) {
//             resultImage.src = data.image;
//             resultContainer.style.display = "block";

//             detectionInfo.innerHTML = data.detection_info && data.detection_info.denomination
//                 ? `
//                     <p><strong>Mệnh giá:</strong> ${data.detection_info.denomination}</p>
//                     <p><strong>Độ tin cậy:</strong> ${data.detection_info.confidence}</p>
//                 `
//                 : "<p>Không nhận diện được mệnh giá!</p>";

//             moneyDetails.innerHTML = data.money_details && data.money_details.year_of_issue
//                 ? `
//                     <p><strong>Năm phát hành:</strong> ${data.money_details.year_of_issue}</p>
//                     <p><strong>Mô tả:</strong> ${data.money_details.description}</p>
//                 `
//                 : "<p>Không có thông tin chi tiết!</p>";

//             resultError.innerText = "";
//         } else {
//             resultError.innerText = "Không thể nhận diện!";
//             resultContainer.style.display = "none";
//         }
//     } catch (error) {
//         showLoading(false);
//         showError(error.message);
//         document.getElementById("result-container").style.display = "none";
//     }
// }

// // Gửi tin nhắn cho chatbot
// async function sendMessage() {
//     const chatInput = document.getElementById("chat-input");
//     const question = chatInput.value.trim();
//     if (!question) {
//         showError("Vui lòng nhập câu hỏi!");
//         return;
//     }

//     const chatMessages = document.getElementById("chat-messages");
//     const userMessage = document.createElement("div");
//     userMessage.className = "message user-message";
//     userMessage.textContent = question;
//     chatMessages.appendChild(userMessage);
//     chatMessages.scrollTop = chatMessages.scrollHeight;

//     chatInput.value = "";
//     showLoading(true);

//     try {
//         const response = await fetchWithRetry("http://192.168.77.67:55015/chat", {
//             method: "POST",
//             headers: { "Content-Type": "application/json" },
//             body: JSON.stringify({ question: question })
//         });

//         showLoading(false);

//         if (!response.ok) {
//             throw new Error((await response.json()).error || "Lỗi từ chatbot");
//         }

//         const data = await response.json();
//         const botMessage = document.createElement("div");
//         botMessage.className = "message bot-message";
//         botMessage.textContent = data.response;
//         chatMessages.appendChild(botMessage);
//         chatMessages.scrollTop = chatMessages.scrollHeight;
//     } catch (error) {
//         showLoading(false);
//         const botMessage = document.createElement("div");
//         botMessage.className = "message bot-message error";
//         botMessage.textContent = `Đã xảy ra lỗi: ${error.message}`;
//         chatMessages.appendChild(botMessage);
//         chatMessages.scrollTop = chatMessages.scrollHeight;
//     }
// }

// // Hàm tiện ích
// function showError(message) {
//     const resultError = document.getElementById("result-error");
//     resultError.innerText = message;
//     resultError.style.display = "block";
//     setTimeout(() => resultError.style.display = "none", 5000);
// }

// function showLoading(show) {
//     document.getElementById("loading").style.display = show ? "block" : "none";
// }

// async function fetchWithRetry(url, options, retries = 3, delay = 1000) {
//     for (let i = 0; i < retries; i++) {
//         try {
//             const response = await fetch(url, options);
//             return response;
//         } catch (error) {
//             if (i === retries - 1) throw error;
//             await new Promise(resolve => setTimeout(resolve, delay));
//         }
//     }
// }

// // Gửi tin nhắn bằng phím Enter
// document.getElementById("chat-input")?.addEventListener("keypress", function (event) {
//     if (event.key === "Enter") {
//         sendMessage();
//     }
// });

// // Toggle chatbox
// function toggleChatbox() {
//     const chatContainer = document.getElementById("chat-container");
//     const chatMessages = document.getElementById("chat-messages");
//     const chatInputContainer = document.getElementById("chat-input-container");

//     if (chatContainer.classList.contains("collapsed")) {
//         chatContainer.classList.remove("collapsed");
//         chatMessages.style.display = "block";
//         chatInputContainer.style.display = "flex";
//     } else {
//         chatContainer.classList.add("collapsed");
//         chatMessages.style.display = "none";
//         chatInputContainer.style.display = "none";
//     }
// }

// // Cập nhật sidebar khi trang tải
// document.addEventListener("DOMContentLoaded", () => {
//     const userInfo = document.getElementById("user-info");
//     const userName = document.getElementById("user-name");
//     const loginLink = document.getElementById("login-link");
//     const logoutLink = document.getElementById("logout");

//     const user = JSON.parse(localStorage.getItem("user"));

//     if (user && user.ten_kh) {
//         userInfo.style.display = "block";
//         userName.textContent = user.ten_kh;
//         loginLink.style.display = "none";
//     } else {
//         userInfo.style.display = "none";
//         loginLink.style.display = "block";
//     }

//     logoutLink.addEventListener("click", (e) => {
//         e.preventDefault();
//         localStorage.removeItem("user");
//         localStorage.removeItem("auth_token");
//         window.location.href = "/";
//     });
// });

/**
 * Đăng nhập người dùng với email và mật khẩu.
 * @param {string} email - Email của người dùng.
 * @param {string} password - Mật khẩu của người dùng.
 * @returns {Promise<void>}
 */
async function login(email, password) {
    try {
        const response = await fetch("/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ email, password }),
        });
        const data = await response.json();
        console.log("Phản hồi từ /login:", data);
        if (response.ok && data.status === "success") {
            showSuccess("Đăng nhập thành công!");
            window.location.href = "/";
        } else {
            showError(`Đăng nhập thất bại: ${data.message || data.error || "Lỗi không xác định"}`);
        }
    } catch (error) {
        console.error("Lỗi khi đăng nhập:", error);
        showError(`Lỗi khi đăng nhập: ${error.message}`);
    }
}

/**
 * Đăng xuất người dùng.
 * @returns {Promise<void>}
 */
async function logout() {
    try {
        await fetch("/logout", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
        });
    } catch (error) {
        console.error("Lỗi khi đăng xuất:", error);
    } finally {
        window.location.href = "/login";
    }
}

/**
 * Xử lý kết quả nhận diện từ FastAPI.
 * @param {Object} data - Dữ liệu trả về từ FastAPI.
 */
function processDetectionResult(data) {
    const resultContainer = document.getElementById("result-container");
    const resultImage = document.getElementById("result-image");
    const detectionInfo = document.getElementById("detection-info");
    const moneyDetails = document.getElementById("money-details");
    const resultError = document.getElementById("result-error");

    if (!resultContainer || !resultImage || !detectionInfo || !moneyDetails || !resultError) {
        console.error("Một hoặc nhiều phần tử giao diện không được tìm thấy!");
        showError("Lỗi giao diện, vui lòng thử lại!");
        return;
    }

    // Xóa nội dung lỗi cũ
    resultError.innerText = "";
    resultError.style.display = "none";

    if (data && data.image && data.detection_info) {
        resultImage.src = data.image;
        detectionInfo.innerHTML = data.detection_info.denomination
            ? `
                <p><strong>Mệnh giá:</strong> ${data.detection_info.denomination}</p>
                <p><strong>Độ tin cậy:</strong> ${data.detection_info.confidence || "N/A"}</p>
            `
            : "<p>Không nhận diện được mệnh giá!</p>";

        moneyDetails.innerHTML = data.money_details && data.money_details.year_of_issue
            ? `
                <p><strong>Năm phát hành:</strong> ${data.money_details.year_of_issue}</p>
                <p><strong>Mô tả:</strong> ${data.money_details.description || "N/A"}</p>
                <p><strong>Màu sắc:</strong> ${data.money_details.color || "N/A"}</p>
            `
            : "<p>Không có thông tin chi tiết!</p>";

        resultContainer.style.display = "block";
        showSuccess("Nhận diện thành công!");
    } else {
        resultError.innerText = "Không thể nhận diện! Dữ liệu trả về không hợp lệ hoặc thiếu thông tin.";
        resultContainer.style.display = "none";
        showError("Không thể nhận diện!");
    }
}

/**
 * Hiển thị hoặc ẩn biểu tượng loading cho nhận diện ảnh.
 * @param {boolean} show - Hiển thị nếu true, ẩn nếu false.
 */
function showImageLoading(show) {
    const loadingElement = document.getElementById("loading");
    if (loadingElement) {
        loadingElement.style.display = show ? "block" : "none";
    } else {
        console.warn("Element with id 'loading' not found.");
    }
}

/**
 * Hiển thị hoặc ẩn hiệu ứng ba chấm cho chatbot.
 * @param {boolean} show - Hiển thị nếu true, ẩn nếu false.
 */
function showChatLoading(show) {
    const chatMessages = document.getElementById("chat-messages");
    let loadingDots = document.getElementById("chat-loading-dots");

    if (show) {
        if (!loadingDots) {
            loadingDots = document.createElement("div");
            loadingDots.id = "chat-loading-dots";
            loadingDots.className = "message bot-message";
            loadingDots.textContent = "...";
            chatMessages.appendChild(loadingDots);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    } else {
        if (loadingDots) {
            loadingDots.remove();
        }
    }
}

/**
 * Gửi tin nhắn tới chatbot.
 * @returns {Promise<void>}
 */
async function sendMessage() {
    const chatInput = document.getElementById("chat-input");
    const question = chatInput.value.trim();
    if (!question) {
        showError("Vui lòng nhập câu hỏi!");
        return;
    }
    if (!question.match(/[\p{L}\p{N}\s.,!?]/u)) {
        showError("Câu hỏi chứa ký tự không hợp lệ. Vui lòng nhập lại!");
        return;
    }

    const chatMessages = document.getElementById("chat-messages");
    const userMessage = document.createElement("div");
    userMessage.className = "message user-message";
    userMessage.textContent = question;
    chatMessages.appendChild(userMessage);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    chatInput.value = "";
    showChatLoading(true);

    try {
        const response = await fetchWithRetry("/chat", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ question: question }),
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            let errorMessage = errorData.error || errorData.detail || "Lỗi không xác định từ server";
            if (response.status === 400) {
                errorMessage = errorData.detail || "Yêu cầu không hợp lệ.";
            } else if (response.status === 401) {
                errorMessage = "Vui lòng đăng nhập để sử dụng chatbot.";
            } else if (response.status === 429) {
                errorMessage = "Đã vượt quá giới hạn yêu cầu.";
            } else if (response.status === 500) {
                errorMessage = errorData.detail || "Lỗi hệ thống từ chatbot.";
            }
            throw new Error(errorMessage);
        }

        const data = await response.json();
        const botMessage = document.createElement("div");
        botMessage.className = "message bot-message";
        botMessage.textContent = data.response || "Không có phản hồi từ chatbot.";
        chatMessages.appendChild(botMessage);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    } catch (error) {
        console.error("Lỗi khi gửi tin nhắn:", error);
        const botMessage = document.createElement("div");
        botMessage.className = "message bot-message error";
        botMessage.textContent = `Đã xảy ra lỗi: ${error.message}`;
        chatMessages.appendChild(botMessage);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    } finally {
        showChatLoading(false);
    }
}
/**
 * Hiển thị thông báo lỗi.
 * @param {string} message - Thông điệp lỗi.
 */
function showError(message) {
    const resultError = document.getElementById("result-error");
    if (resultError) {
        resultError.innerText = message;
        resultError.style.display = "block";
        setTimeout(() => {
            resultError.style.display = "none";
            resultError.innerText = "";
        }, 10000);
    } else {
        console.error("Element with id 'result-error' not found for error display.");
    }
}

/**
 * Hiển thị thông báo thành công.
 * @param {string} message - Thông điệp thành công.
 */
function showSuccess(message) {
    const resultError = document.getElementById("result-error");
    if (resultError) {
        resultError.innerText = message;
        resultError.style.color = "green";
        resultError.style.display = "block";
        setTimeout(() => {
            resultError.style.display = "none";
            resultError.style.color = "red";
            resultError.innerText = "";
        }, 5000);
    } else {
        console.error("Element with id 'result-error' not found for success display.");
    }
}

/**
 * Thực hiện yêu cầu fetch với cơ chế thử lại.
 * @param {string} url - URL của yêu cầu.
 * @param {Object} options - Tùy chọn cho fetch.
 * @param {number} [maxRetries=3] - Số lần thử lại.
 * @param {number} [delay=1000] - Độ trễ giữa các lần thử (ms).
 * @returns {Promise<Response>}
 */
async function fetchWithRetry(url, options, maxRetries = 3, delay = 1000) {
    for (let i = 0; i < maxRetries; i++) {
        try {
            const response = await fetch(url, options);
            return response;
        } catch (error) {
            console.error(`Lỗi khi gọi ${url}, lần thử ${i + 1}:`, error);
            if (i === maxRetries - 1) throw error;
            await new Promise(resolve => setTimeout(resolve, delay * (i + 1)));
        }
    }
}

/**
 * Chuyển đổi trạng thái hiển thị của chatbox.
 */
function toggleChatbox() {
    const chatContainer = document.getElementById("chat-container");
    if (chatContainer) {
        chatContainer.classList.toggle("active");
    }
}

/**
 * Chuyển hướng đến trang profile.
 */
function goToProfile() {
    window.location.href = "/profile";
}

/**
 * Tải lên hình ảnh để nhận diện tiền.
 * @returns {Promise<void>}
 */
async function uploadImage() {
    const resultContainer = document.getElementById("result-container");
    if (resultContainer) {
        resultContainer.style.display = "none";
    }

    const input = document.getElementById("image-input");
    const file = input.files[0];
    if (!file) {
        showError("Vui lòng chọn một hình ảnh!");
        return;
    }

    const validExtensions = [".jpg", ".jpeg", ".png"];
    const extension = file.name.toLowerCase().slice(file.name.lastIndexOf("."));
    if (!["image/jpeg", "image/png"].includes(file.type) || !validExtensions.includes(extension)) {
        showError("Chỉ hỗ trợ file JPEG hoặc PNG!");
        return;
    }

    if (file.size === 0) {
        showError("File rỗng, vui lòng chọn file hợp lệ!");
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        showError("Kích thước file vượt quá 5MB!");
        return;
    }

    showImageLoading(true);
    try {
        const formData = new FormData();
        formData.append("image", file);

        const response = await fetchWithRetry("/detect-money", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData,
        });

        const detectionData = await response.json();
        console.log("Phản hồi từ /detect-money:", detectionData);

        if (!response.ok) {
            throw new Error(detectionData.error || "Lỗi không xác định từ server!");
        }

        if (!detectionData.data || !detectionData.data.detection_info) {
            throw new Error("Dữ liệu nhận diện không hợp lệ!");
        }

        processDetectionResult(detectionData.data);

        // Cập nhật số tokens trên giao diện
        const heroTokenDisplay = document.getElementById("hero-token-display");
        const sidebarTokenDisplay = document.getElementById("sidebar-token-display");
        if (heroTokenDisplay) {
            heroTokenDisplay.textContent = detectionData.tokens;
        }
        if (sidebarTokenDisplay) {
            const userName = sidebarTokenDisplay.textContent.split('<br>')[0].split(', ')[1];
            sidebarTokenDisplay.innerHTML = `Xin chào, ${userName}<br>(lần sử dụng: ${detectionData.tokens})`;
        }
    } catch (error) {
        console.error("Lỗi trong uploadImage:", error);
        showError(`Lỗi nhận diện: ${error.message}`);
    } finally {
        showImageLoading(false);
    }
}

/**
 * Khởi tạo các sự kiện khi trang được tải.
 */
document.addEventListener("DOMContentLoaded", () => {
    const userAvatar = document.querySelector(".user-avatar");
    if (userAvatar) {
        userAvatar.addEventListener("click", goToProfile);
    }

    const loginForm = document.getElementById("login-form");
    if (loginForm) {
        loginForm.addEventListener("submit", async (event) => {
            event.preventDefault();
            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;
            await login(email, password);
        });
    }

    const chatInput = document.getElementById("chat-input");
    if (chatInput) {
        chatInput.addEventListener("keypress", (event) => {
            if (event.key === "Enter") {
                sendMessage();
            }
        });
    }
});