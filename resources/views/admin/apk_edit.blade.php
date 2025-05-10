@extends('layouts.admin')

@section('title', 'Chỉnh sửa APK')

@section('content')
    <h2>Chỉnh sửa APK</h2>

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

    <div class="table-container">
        <form id="apk-form" method="POST" action="{{ route('admin.apks.update', $apk->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="form-group">
                <label for="name">Tên ứng dụng:</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $apk->name) }}" placeholder="Nhập tên ứng dụng" required>
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Version -->
            <div class="form-group">
                <label for="version">Phiên bản:</label>
                <input type="text" name="version" id="version" class="form-control" value="{{ old('version', $apk->version) }}" placeholder="Nhập phiên bản (ví dụ: 1.0.0)" required>
                @error('version')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description">Mô tả:</label>
                <textarea name="description" id="description" class="form-control" placeholder="Nhập mô tả ứng dụng">{{ old('description', $apk->description) }}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- APK File -->
            <div class="form-group">
                <label for="apk_file">File APK (bỏ trống nếu không thay đổi):</label>
                <input type="file" name="apk_file" id="apk_file" class="form-control" accept=".apk">
                @if($apk->file_path)
                    <a href="{{ asset('storage/' . $apk->file_path) }}" download class="download-link">Tải file hiện tại</a>
                @endif
                @error('apk_file')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="form-actions">
                <button type="submit" class="action-btn edit-btn" id="submit-btn">
                    <i class="fas fa-save"></i> Cập nhật APK
                </button>
            </div>
        </form>
    </div>

    <style>
    .table-container {
        width: 90%;
        max-width: 1200px;
        margin: 20px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    h2 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #1e272e;
        text-align: center;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .form-group label {
        display: block;
        font-size: 16px;
        font-weight: bold;
        color: #1e272e;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        color: #333;
        background-color: #fff;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #1e272e;
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .error-message {
        display: block;
        font-size: 14px;
        color: #721c24;
        margin-top: 5px;
    }

    .form-actions {
        text-align: center;
        margin-top: 20px;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        background-color: #3498db;
        color: #fff;
        border-radius: 4px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
        border: none;
    }

    .action-btn i {
        margin-right: 5px;
    }

    .action-btn:hover {
        background-color: #2980b9;
    }

    .download-link {
        display: block;
        margin-top: 10px;
        color: #00d8d6;
        text-decoration: none;
        font-weight: bold;
    }

    .download-link:hover {
        text-decoration: underline;
    }

    .alert {
        padding: 15px;
        margin: 20px auto;
        width: 90%;
        max-width: 1200px;
        border-radius: 5px;
        text-align: center;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    @media (max-width: 768px) {
        .table-container {
            width: 95%;
            padding: 15px;
        }

        h2 {
            font-size: 20px;
        }

        .form-group {
            max-width: 100%;
        }

        .form-control {
            font-size: 14px;
            padding: 10px;
        }

        .action-btn {
            padding: 8px 15px;
            font-size: 14px;
        }
    }
    </style>

    @section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('apk-form');
        const submitBtn = document.getElementById('submit-btn');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

            Swal.fire({
                title: 'Xác nhận',
                text: 'Bạn có chắc chắn muốn cập nhật APK này?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Cập nhật',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Cập nhật APK';
                }
            });
        });
    });
    </script>
    @endsection
@endsection