@extends('layouts.admin')

@section('title', 'Quản lý nạp tiền')

@section('content')
    <h2>Quản lý nạp tiền</h2>

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
        @if ($deposits->isNotEmpty())
            <table class="deposit-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Số tiền</th>
                        <th>Tokens</th>
                        <th>Ảnh xác nhận</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deposits as $deposit)
                        <tr>
                            <td>{{ $deposit->id }}</td>
                            <td>{{ $deposit->user_id }}</td>
                            <td>{{ number_format($deposit->amount, 0, ',', '.') }} VND</td>
                            <td>{{ $deposit->tokens }}</td>
                            <td>
                                @if ($deposit->proof_image)
                                    <a href="{{ asset('storage/' . $deposit->proof_image) }}" class="image-link">
                                        <img src="{{ asset('storage/' . $deposit->proof_image) }}" alt="Ảnh xác nhận" class="thumbnail">
                                    </a>
                                @else
                                    Chưa có
                                @endif
                            </td>
                            <td>{{ $deposit->status }}</td>
                            <td>{{ \Carbon\Carbon::parse($deposit->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if ($deposit->status == 'pending')
                                    <form action="{{ route('admin.deposits.update', $deposit->id) }}" method="POST" class="status-form" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="status-select">
                                            <option value="pending" selected>Chờ duyệt</option>
                                            <option value="completed">Duyệt</option>
                                            <option value="failed">Hủy</option>
                                        </select>
                                    </form>
                                @else
                                    {{ $deposit->status == 'completed' ? 'Đã duyệt' : 'Đã hủy' }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Chưa có yêu cầu nạp tiền nào.</p>
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

    .deposit-table select {
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #ccc;
        background-color: #fff;
        cursor: pointer;
    }

    .deposit-table select:focus {
        outline: none;
        border-color: #1e272e;
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
    </style>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        const closeBtn = document.getElementsByClassName('close')[0];
        const imageLinks = document.getElementsByClassName('image-link');
        const statusForms = document.getElementsByClassName('status-form');
        const statusSelects = document.getElementsByClassName('status-select');

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

        // Xử lý thay đổi trạng thái với SweetAlert
        Array.from(statusSelects).forEach((select, index) => {
            select.addEventListener('change', function (e) {
                e.preventDefault();
                const form = statusForms[index];
                const selectedValue = this.value;

                Swal.fire({
                    title: 'Xác nhận',
                    text: 'Bạn có chắc chắn muốn thay đổi trạng thái này?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Lưu',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    } else {
                        // Đặt lại giá trị select về giá trị ban đầu
                        select.value = 'pending';
                    }
                });
            });
        });
    });
    </script>
@endsection
@endsection