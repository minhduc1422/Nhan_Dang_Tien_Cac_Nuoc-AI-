@extends('layouts.admin')

@section('title', 'Quản lý Gói Nạp')

@section('content')
    <div class="admin-table-container">
        <h2>Quản lý Gói Nạp</h2>
        @if (session('success'))
            <div style="padding: 12px; margin-bottom: 20px; border-radius: 5px; background: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6; font-size: 15px; text-align: center;">
                {{ session('success') }}
            </div>
        @endif

        <div style="text-align: right; margin-bottom: 20px;">
            <a href="{{ route('admin.deposit_plans.create') }}" class="deposit-btn">Thêm gói nạp mới</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Giá tiền (VNĐ)</th>
                    <th>Số Tokens</th>
                    <th>Mô tả</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($plans as $plan)
                    <tr>
                        <td>{{ $plan->id }}</td>
                        <td>{{ number_format($plan->amount, 0, ',', '.') }}</td>
                        <td>{{ $plan->tokens }}</td>
                        <td>{{ $plan->description ?? 'Không có' }}</td>
                        <td>
                            <span class="status {{ $plan->is_active ? 'completed' : 'failed' }}">
                                {{ $plan->is_active ? 'Kích hoạt' : 'Không kích hoạt' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.deposit_plans.edit', $plan->id) }}" class="action-btn edit-btn">Sửa</a>
                            <form action="{{ route('admin.deposit_plans.destroy', $plan->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete-btn" onclick="return confirm('Bạn có chắc muốn xóa gói này?')">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center; color: #666; font-style: italic;">Chưa có gói nạp nào!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection