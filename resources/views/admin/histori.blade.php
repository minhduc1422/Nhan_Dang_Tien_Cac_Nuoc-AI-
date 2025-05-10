@extends('layouts.admin')

@section('title', 'Lịch sử nhận diện tờ tiền')

@section('content')
    <!-- Tiêu đề -->
    <h2 class="fade-in">Lịch sử nhận diện tờ tiền</h2>

    <!-- Thông báo -->
    @if (session('error'))
        <div class="alert alert-error fade-in">
            {{ session('error') }}
        </div>
    @endif

    <!-- Bảng lịch sử -->
    <div class="table-container fade-in">
        @if (isset($detections) && $detections->isNotEmpty())
            <table class="deposit-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 20%;">Người dùng</th>
                        <th style="width: 20%;">Kết quả nhận diện</th>
                        <th style="width: 15%;">Hình ảnh</th>
                        <th style="width: 20%;">Thời gian</th>
                        <th style="width: 10%;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detections as $detection)
                        <tr>
                            <td>{{ $detection->id }}</td>
                            <td>{{ $detection->user_name ?? 'N/A' }}</td>
                            <td>{{ $detection->result ?? 'N/A' }}</td>
                            <td>
                                @if ($detection->image)
                                    @php
                                        $imagePath = asset('storage/' . $detection->image);
                                        $fileExists = file_exists(public_path('storage/' . $detection->image));
                                    @endphp
                                    @if ($fileExists)
                                        <a href="{{ $imagePath }}" class="image-link">
                                            <img src="{{ $imagePath }}" alt="Hình ảnh nhận diện" class="thumbnail">
                                        </a>
                                    @else
                                        <span>-</span>
                                    @endif
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($detection->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="status {{ strtolower($detection->status ?? 'N/A') }}">{{ $detection->status ?? 'N/A' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Phân trang tùy chỉnh -->
            <div class="pagination">
                @if ($detections->onFirstPage())
                    <span class="disabled">Previous</span>
                @else
                    <a href="{{ $detections->previousPageUrl() }}">Previous</a>
                @endif

                @foreach ($detections->getUrlRange(1, $detections->lastPage()) as $page => $url)
                    @if ($page == $detections->currentPage())
                        <span class="current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($detections->hasMorePages())
                    <a href="{{ $detections->nextPageUrl() }}">Next</a>
                @else
                    <span class="disabled">Next</span>
                @endif
            </div>
        @else
            <p>Chưa có lịch sử nhận diện tờ tiền.</p>
        @endif
    </div>

    <!-- Modal phóng to hình ảnh -->
    <div id="imageModal" class="modal">
        <span class="close">×</span>
        <img class="modal-content" id="modalImage">
    </div>

    <style>
        :root {
            --primary-color: #00c4cc;
            --secondary-color: #1e272e;
            --background-color: #f8f9fa;
            --text-color: #333;
            --error-color: #ff4444;
        }

        .table-container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .deposit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        .deposit-table th,
        .deposit-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .deposit-table th {
            background-color: var(--secondary-color);
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
        }

        .deposit-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .deposit-table tr:hover {
            background-color: #e6f7fa;
            transition: background 0.3s ease;
        }

        .deposit-table td {
            color: var(--text-color);
        }

        .thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            border: 1px solid #ddd;
            transition: transform 0.2s ease;
        }

        .thumbnail:hover {
            transform: scale(1.1);
        }

        .image-link {
            display: inline-block;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 50px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.85);
            transition: opacity 0.3s ease;
        }

        .modal-content {
            margin: auto;
            display: block;
            width: 90%;
            max-width: 800px;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: scale(1);
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .close:hover,
        .close:focus {
            color: var(--primary-color);
        }

        h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: var(--secondary-color);
            text-align: center;
            font-weight: 600;
        }

        p {
            text-align: center;
            color: #666;
            font-size: 16px;
        }

        .alert {
            padding: 15px;
            margin: 20px auto;
            width: 95%;
            max-width: 1200px;
            border-radius: 8px;
            text-align: center;
            transition: opacity 0.6s ease;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .pagination a, .pagination span {
            color: var(--secondary-color);
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .pagination a:hover {
            background-color: var(--primary-color);
            color: #fff;
        }

        .pagination .current {
            background-color: var(--primary-color);
            color: #fff;
            border: 1px solid var(--primary-color);
        }

        .pagination .disabled {
            color: #aaa;
            border-color: #ddd;
            cursor: not-allowed;
        }

        .status {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 14px;
            text-transform: capitalize;
            display: inline-block;
        }

        .status.pending {
            background: #ffd700;
            color: #333;
        }

        .status.completed {
            background: #4caf50;
            color: #fff;
        }

        .status.failed {
            background: var(--error-color);
            color: #fff;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        const closeBtn = document.getElementsByClassName('close')[0];
        const imageLinks = document.getElementsByClassName('image-link');

        Array.from(imageLinks).forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const imageUrl = this.href;
                const img = new Image();
                img.src = imageUrl;
                img.onload = function() {
                    modal.style.display = 'block';
                    modalImg.src = imageUrl;
                    modal.classList.add('show');
                };
                img.onerror = function() {
                    alert('Không thể tải hình ảnh. Vui lòng kiểm tra lại.');
                };
            });
        });

        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
            modal.classList.remove('show');
        });

        window.addEventListener('click', function (e) {
            if (e.target == modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
            }
        });

        // Hiệu ứng fade-in khi tải trang
        document.querySelectorAll('.fade-in').forEach(element => {
            setTimeout(() => {
                element.classList.add('visible');
            }, 100);
        });
    });
    </script>
@endsection