@extends('layouts.admin')

@section('title', 'Lịch sử nhận diện tờ tiền')

@section('content')
    <!-- Tiêu đề -->
    <h2>Lịch sử nhận diện tờ tiền</h2>

    <!-- Thông báo -->
    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Bảng lịch sử -->
    <div class="table-container">
        @if (isset($detections) && $detections->isNotEmpty())
            <table class="deposit-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Người dùng</th>
                        <th>Kết quả nhận diện</th>
                        <th>Hình ảnh</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
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
                                    <a href="{{ asset('storage/' . $detection->image) }}" class="image-link">
                                        <img src="{{ asset('storage/' . $detection->image) }}" alt="Hình ảnh nhận diện" class="thumbnail">
                                    </a>
                                @else
                                    Không có
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($detection->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $detection->status ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
    .table-container {
        width: 90%;
        max-width: 1200px;
        margin: 20px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        border-bottom: 1px solid #ddd;
    }

    .deposit-table th {
        background-color: #1e272e;
        color: #fff;
        font-weight: bold;
    }

    .deposit-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .deposit-table tr:hover {
        background-color: #f1f1f1;
    }

    .deposit-table td {
        color: #333;
    }

    .thumbnail {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
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

    h2 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #1e272e;
        text-align: center;
    }

    p {
        text-align: center;
        color: #888;
        font-size: 16px;
    }

    .alert {
        padding: 15px;
        margin: 20px auto;
        width: 90%;
        max-width: 1200px;
        border-radius: 5px;
        text-align: center;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>

    <script>
    // JavaScript để xử lý modal phóng to hình ảnh
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        const closeBtn = document.getElementsByClassName('close')[0];
        const imageLinks = document.getElementsByClassName('image-link');

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