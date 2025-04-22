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
                <p>Thành công: <span class="sta completed">{{ $successfulDetections }}</span></p>
                <p>Thất bại: <span class="status failed">{{ $failedDetections }}</span></p>
            </div>
            <div class="stats-card">
                <h3>Tổng gói nạp</h3>
                <p class="stats-value">{{ $totalPlans }}</p>
            </div>
        </div>

        <!-- Yêu cầu nạp tiền gần đây -->
        <h2>Yêu cầu nạp tiền gần đây</h2>
        <table class="admin-table">tus
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
                        <td>{{ $deposit->created_at }}</td>
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
                        <td>{{ $detection->created_at }}</td>
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
@endsection