@extends('layouts.admin')

@section('title', 'Cấu hình mô hình nhận diện tiền')

@section('content')
<div class="admin-table-container">
    <h2>Cấu hình mô hình nhận diện tiền</h2>

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

        <!-- Model Source Selection -->
        <div class="form-group">
            <label for="model_source">Nguồn mô hình nhận diện:</label>
            <select name="source" id="model_source" class="form-control">
                <option value="">Chọn nguồn mô hình</option>
                <option value="run">YOLOv8 (run)</option>
                <option value="runs">COCO JSON (runs)</option>
            </select>
            @error('source')
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
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('config-form');
    const submitBtn = document.getElementById('submit-btn');
    const modelSource = document.getElementById('model_source');

    // Enable submit button when a source is selected
    modelSource.addEventListener('change', function () {
        submitBtn.disabled = !modelSource.value;
    });

    // Fetch current model source on page load
    $.ajax({
        url: '{{ route("api.current_detection_model") }}',
        type: 'GET',
        success: function (response) {
            if (response.source) {
                modelSource.value = response.source;
                submitBtn.disabled = false;
            }
        },
        error: function (xhr) {
            Swal.fire('Lỗi!', 'Không thể lấy nguồn mô hình hiện tại: ' + (xhr.responseJSON?.error || 'Lỗi không xác định'), 'error');
        }
    });

    // Handle form submission
    form.addEventListener('submit', function (e) {
        e.preventDefault();
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
                const formData = new FormData(form);
                const data = {
                    source: formData.get('source'),
                    _token: formData.get('_token'),
                    _method: 'PUT'
                };

                if (!data.source) {
                    Swal.fire('Lỗi!', 'Vui lòng chọn nguồn mô hình!', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu cấu hình';
                    return;
                }

                $.ajax({
                    url: 'http://localhost:55015/update_detection_model',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ source: data.source }),
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
                        let errorMsg = xhr.responseJSON?.detail || 'Lỗi khi cập nhật mô hình!';
                        if (xhr.status === 400) {
                            errorMsg = xhr.responseJSON?.detail || 'Nguồn mô hình không hợp lệ!';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Lỗi server! Vui lòng thử lại sau.';
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
});
</script>
@endsection