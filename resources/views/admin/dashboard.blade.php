@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="admin-table-container">
        <h2>Chào mừng đến với Dashboard Admin</h2>

        <!-- Thống kê tổng quan -->
        <div class="stats-grid">
            <div class="stats-card">
                <h3>Tổng người dùng</h3>
                <p class="stats-value">{{ $totalUsers }}</p>
            </div>
            <div class="stats-card">
                <h3>Tổng yêu cầu nạp tiền</h3>
                <p class="stats-value">{{ $totalDeposits }}</p>
                <p>Đang chờ: <span class="status pending">{{ $pendingDeposits }}</span></p>
                <p>Hoàn thành: <span class="status completed">{{ $completedDeposits }}</span></p>
                <p>Thất bại: <span class="status failed">{{ $failedDeposits }}</span></p>
            </div>
            <div class="stats-card">
                <h3>Tổng lần nhận diện</h3>
                <p class="stats-value">{{ $totalDetections }}</p>
                <p>Thành công: <span class="status completed">{{ $successfulDetections }}</span></p>
                <p>Thất bại: <span class="status failed">{{ $failedDetections }}</span></p>
            </div>
            <div class="stats-card">
                <h3>Tổng gói nạp</h3>
                <p class="stats-value">{{ $totalPlans }}</p>
            </div>
        </div>

        <!-- Yêu cầu nạp tiền gần đây -->
        <h2>Yêu cầu nạp tiền gần đây</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>Số tiền</th>
                    <th>Tokens</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentDeposits as $deposit)
                    <tr>
                        <td>{{ $deposit->id }}</td>
                        <td>{{ $deposit->user_name }}</td>
                        <td>{{ number_format($deposit->amount, 0, ',', '.') }} VNĐ</td>
                        <td>{{ $deposit->tokens }}</td>
                        <td>
                            <span class="status {{ strtolower($deposit->status) }}">{{ $deposit->status }}</span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($deposit->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.deposits') }}" class="action-btn edit-btn">Xem chi tiết</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 12px; text-align: center; color: #666; font-style: italic;">Chưa có yêu cầu nạp tiền nào!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Lần nhận diện gần đây -->
        <h2>Lần nhận diện gần đây</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>Số tiền</th>
                    <th>Kết quả</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentDetections as $detection)
                    <tr>
                        <td>{{ $detection->id }}</td>
                        <td>{{ $detection->user_name }}</td>
                        <td>{{ number_format($detection->amount, 0, ',', '.') }} VNĐ</td>
                        <td>{{ $detection->result }}</td>
                        <td>
                            <span class="status {{ strtolower($detection->status) }}">{{ $detection->status }}</span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($detection->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.histori') }}" class="action-btn edit-btn">Xem chi tiết</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 12px; text-align: center; color: #666; font-style: italic;">Chưa có lịch sử nhận diện nào!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <style>
        .admin-table-container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #1e272e;
            text-align: center;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stats-card {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .stats-value {
            font-size: 24px;
            font-weight: bold;
            color: #00c4cc;
            margin: 10px 0;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
            margin-top: 20px;
        }

        .admin-table th,
        .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .admin-table th {
            background-color: #1e272e;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
        }

        .admin-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .admin-table tr:hover {
            background-color: #e6f7fa;
            transition: background 0.3s ease;
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
            background: #ff4444;
            color: #fff;
        }

        .action-btn {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .edit-btn {
            background-color: #00c4cc;
            color: #fff;
        }

        .edit-btn:hover {
            background-color: #009b9f;
        }
    </style>
@endsection