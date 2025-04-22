@extends('layouts.admin')

@section('title', 'Cấu hình Chatbot')

@section('content')
<div class="admin-table-container">
    <h2>Cấu hình Chatbot</h2>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form chỉnh sửa cấu hình -->
    <form id="config-form">
        @csrf
        @method('PUT')

        <!-- Chatbot Model Selection -->
        <div class="form-group">
            <label for="model_name">Mô hình Chatbot:</label>
            <select name="model_name" id="model_name" class="form-control">
                <option value="gemini-1.5-pro" {{ $config->model_name == 'gemini-1.5-pro' ? 'selected' : '' }}>Google Gemini 1.5 Pro</option>
                <option value="openai-gpt-4" {{ $config->model_name == 'openai-gpt-4' ? 'selected' : '' }}>OpenAI (GPT-4)</option>
            </select>
            @error('model_name')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- API Key Input -->
        <div class="form-group">
            <label for="api_key">API Key:</label>
            <div class="input-wrapper">
                <input type="password" name="api_key" id="api_key" class="form-control" value="{{ $config->api_key ?? '' }}" placeholder="Nhập API Key">
                <button type="button" id="toggle-api-key" class="toggle-visibility">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('api_key')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="form-actions">
            <button type="submit" class="action-btn edit-btn" id="submit-btn" disabled>
                <i class="fas fa-save"></i> Lưu cấu hình
            </button>
        </div>
    </form>
</div>

<style>
.admin-table-container {
    width: 90%;
    max-width: 1000px;
    margin: 40px auto;
    background-color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.admin-table-container h2 {
    text-align: center;
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #34495e;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    color: #333;
    background-color: #fff;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #00d8d6;
}

.input-wrapper {
    position: relative;
}

.toggle-visibility {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #666;
    font-size: 16px;
}

.toggle-visibility:hover {
    color: #00d8d6;
}

.error-message {
    display: block;
    font-size: 13px;
    color: #e74c3c;
    margin-top: 5px;
}

.form-actions {
    text-align: center;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 15px;
    text-decoration: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.3s ease;
    border: none;
}

.action-btn i {
    margin-right: 5px;
}

.edit-btn {
    background: #3498db;
    color: #fff;
}

.edit-btn:hover {
    background: #2980b9;
    transform: scale(1.05);
}

.edit-btn:disabled {
    background: #95a5a6;
    cursor: not-allowed;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    text-align: center;
    font-size: 14px;
}

.alert-success {
    background-color: #e7f7e8;
    color: #2ecc71;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #fce8e6;
    color: #e74c3c;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .admin-table-container {
        width: 95%;
        padding: 20px;
        margin: 20px auto;
    }

    .admin-table-container h2 {
        font-size: 20px;
        margin-bottom: 20px;
    }

    .form-control {
        padding: 10px;
        font-size: 13px;
    }

    .action-btn {
        padding: 6px 12px;
        font-size: 13px;
    }
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Toggle API Key visibility
document.getElementById('toggle-api-key').addEventListener('click', function () {
    const apiKeyInput = document.getElementById('api_key');
    const icon = this.querySelector('i');
    if (apiKeyInput.type === 'password') {
        apiKeyInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        apiKeyInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Lấy API Key khi thay đổi mô hình
document.getElementById('model_name').addEventListener('change', function () {
    const modelName = this.value;
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;

    $.ajax({
        url: '{{ route("admin.chatbot_config.get_key") }}',
        type: 'GET',
        data: { model_name: modelName },
        success: function (response) {
            $('#api_key').val(response.api_key || '');
            submitBtn.disabled = false;
        },
        error: function (xhr) {
            Swal.fire('Lỗi!', 'Không thể lấy API key: ' + (xhr.responseJSON?.error || 'Lỗi không xác định'), 'error');
            submitBtn.disabled = false;
        }
    });
});

// Gửi form bằng AJAX
document.getElementById('config-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

    Swal.fire({
        title: 'Xác nhận',
        text: 'Bạn có chắc chắn muốn lưu cấu hình này?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Lưu',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData(this);
            const data = {
                model_name: formData.get('model_name'),
                api_key: formData.get('api_key'),
                _token: formData.get('_token'),
                _method: 'PUT'
            };

            if (!data.model_name || !data.api_key) {
                Swal.fire('Lỗi!', 'Vui lòng nhập đầy đủ mô hình và API key!', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu cấu hình';
                return;
            }

            console.log('Dữ liệu gửi đi:', data);

            $.ajax({
                url: '{{ route("admin.chatbot_config.update") }}',
                type: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(data),
                headers: {
                    'X-CSRF-TOKEN': data._token
                },
                success: function (response) {
                    Swal.fire('Thành công!', response.message, 'success').then(() => {
                        window.location.reload();
                    });
                },
                error: function (xhr) {
                    console.error('Lỗi từ server:', xhr.responseJSON);
                    let errorMsg = xhr.responseJSON?.message || 'Lỗi khi cập nhật cấu hình!';
                    if (xhr.status === 429 || errorMsg.includes('insufficient_quota')) {
                        errorMsg = 'API Key đã hết quota. Vui lòng kiểm tra tài khoản OpenAI/Gemini hoặc sử dụng key mới.';
                    }
                    Swal.fire('Lỗi!', errorMsg, 'error');
                },
                complete: function () {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu cấu hình';
                }
            });
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu cấu hình';
        }
    });
});

// Trigger change để hiển thị API key mặc định
$(document).ready(function () {
    $('#model_name').trigger('change');
});
</script>
@endsection