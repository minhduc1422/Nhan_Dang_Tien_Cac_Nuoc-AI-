@extends('layouts.admin')

@section('title', 'Cấu hình Metadata')

@section('content')
    <h2>Cấu hình Metadata</h2>

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
        <form id="config-form" method="POST" action="{{ route('admin.metadata_config.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Site Title -->
            <div class="form-group">
                <label for="site_title">Tiêu đề trang:</label>
                <input type="text" name="site_title" id="site_title" class="form-control" value="{{ $metadata->site_title ?? '' }}" placeholder="Nhập tiêu đề trang">
                @error('site_title')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Site Description -->
            <div class="form-group">
                <label for="site_description">Mô tả trang:</label>
                <textarea name="site_description" id="site_description" class="form-control" placeholder="Nhập mô tả trang">{{ $metadata->site_description ?? '' }}</textarea>
                @error('site_description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Site Keywords -->
            <div class="form-group">
                <label for="site_keywords">Từ khóa:</label>
                <input type="text" name="site_keywords" id="site_keywords" class="form-control" value="{{ $metadata->site_keywords ?? '' }}" placeholder="Nhập từ khóa, cách nhau bằng dấu phẩy">
                @error('site_keywords')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Favicon -->
            <div class="form-group">
                <label for="favicon">Favicon:</label>
                <input type="file" name="favicon" id="favicon" class="form-control" accept="image/*">
                @if($metadata->favicon)
                    <a href="{{ asset('storage/' . $metadata->favicon) }}" class="image-link">
                        <img src="{{ asset('storage/' . $metadata->favicon) }}" alt="Favicon" class="thumbnail">
                    </a>
                @endif
                @error('favicon')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- OG Image -->
            <div class="form-group">
                <label for="og_image">Hình ảnh OG (Open Graph):</label>
                <input type="file" name="og_image" id="og_image" class="form-control" accept="image/*">
                @if($metadata->og_image)
                    <a href="{{ asset('storage/' . $metadata->og_image) }}" class="image-link">
                        <img src="{{ asset('storage/' . $metadata->og_image) }}" alt="OG Image" class="thumbnail">
                    </a>
                @endif
                @error('og_image')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Author -->
            <div class="form-group">
                <label for="author">Tác giả:</label>
                <input type="text" name="author" id="author" class="form-control" value="{{ $metadata->author ?? '' }}" placeholder="Nhập tên tác giả">
                @error('author')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="form-actions">
                <button type="submit" class="action-btn" id="submit-btn">
                    <i class="fas fa-save"></i> Lưu cấu hình
                </button>
            </div>
        </form>
    </div>

    <!-- Modal phóng to hình ảnh -->
    <div id="imageModal" class="modal">
        <span class="close">×</span>
        <img class="modal-content" id="modalImage">
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
        background-color: #1e272e;
        color: #fff;
        text-decoration: none;
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
        background-color: #2f3b47;
    }

    .thumbnail {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 10px;
    }

    .image-link {
        display: inline-block;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        padding-top: 60px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.9);
    }

    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
    }

    .close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #fff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
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
        const form = document.getElementById('config-form');
        const submitBtn = document.getElementById('submit-btn');
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        const closeBtn = document.getElementsByClassName('close')[0];
        const imageLinks = document.getElementsByClassName('image-link');

        // Xử lý submit form với SweetAlert
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
                    form.submit();
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu cấu hình';
                }
            });
        });

        // Xử lý modal phóng to hình ảnh
        Array.from(imageLinks).forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                modal.style.display = 'block';
                modalImg.src = this.href;
            });
        });

        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function (e) {
            if (e.target == modal) {
                modal.style.display = 'none';
            }
        });
    });
    </script>
    @endsection
@endsection