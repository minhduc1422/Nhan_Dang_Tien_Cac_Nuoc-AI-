@extends('layouts.admin')

@section('title', 'Quản lý APK')

@section('content')
    <h2>Quản lý APK</h2>

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
        <div class="table-actions">
            <a href="{{ route('admin.apks.create') }}" class="action-btn add-btn">
                <i class="fas fa-plus"></i> Thêm APK mới
            </a>
        </div>

        @if ($apks->isNotEmpty())
            <table class="apk-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Phiên bản</th>
                        <th>Mô tả</th>
                        <th>File APK</th>
                        <th>Thời gian</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($apks as $apk)
                        <tr>
                            <td>{{ $apk->id }}</td>
                            <td>{{ $apk->name }}</td>
                            <td>{{ $apk->version }}</td>
                            <td>{{ $apk->description ?? 'Không có' }}</td>
                            <td>
                                <a href="{{ asset('storage/' . $apk->file_path) }}" download class="download-link">
                                    Tải xuống
                                </a>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($apk->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <a href="{{ route('admin.apks.edit', $apk->id) }}" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <form action="{{ route('admin.apks.destroy', $apk->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa APK này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete-btn">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Chưa có APK nào.</p>
        @endif
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

    .table-actions {
        margin-bottom: 20px;
        text-align: right;
    }

    .apk-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
    }

    .apk-table th,
    .apk-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .apk-table th {
        background-color: #1e272e;
        color: #fff;
        font-weight: bold;
    }

    .apk-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .apk-table tr:hover {
        background-color: #f1f1f1;
    }

    .apk-table td {
        color: #333;
    }

    .download-link {
        color: #00d8d6;
        text-decoration: none;
        font-weight: bold;
    }

    .download-link:hover {
        text-decoration: underline;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
        text-decoration: none;
        border: none;
    }

    .action-btn i {
        margin-right: 5px;
    }

    .add-btn {
        background: #2ecc71;
        color: #fff;
    }

    .add-btn:hover {
        background: #27ae60;
    }

    .edit-btn {
        background: #3498db;
        color: #fff;
    }

    .edit-btn:hover {
        background: #2980b9;
    }

    .delete-btn {
        background: #e74c3c;
        color: #fff;
    }

    .delete-btn:hover {
        background: #c0392b;
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
    @endsection
@endsection