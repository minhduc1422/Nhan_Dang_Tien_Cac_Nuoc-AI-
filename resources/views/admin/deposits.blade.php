@extends('layouts.admin')

@section('title', 'Quản lý nạp tiền')

@section('content')
<div class="admin-table-container">
    <h2>Quản lý nạp tiền</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Số tiền</th>
                <th>Token</th>
                <th>Ảnh Xác Nhận</th> <!-- Thêm cột mới -->
                <th>Ngày nạp</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deposits as $deposit)
                <tr>
                    <td>{{ $deposit->id }}</td>
                    <td>{{ $deposit->user_id }}</td>
                    <td>{{ number_format($deposit->amount, 2) }} VND</td>
                    <td>{{ $deposit->tokens }}</td>
                    <td>
                        @if($deposit->proof_image)
                            <img src="{{ asset('storage/' . $deposit->proof_image) }}" alt="Ảnh xác nhận" style="max-width: 80px; height: auto; display: block; margin: 0 auto;">
                        @else
                            Chưa có
                        @endif
                    </td>
                    <td>{{ $deposit->created_at }}</td>
                    <td>
                        <span class="status {{ $deposit->status }}">{{ $deposit->status }}</span>
                    </td>
                    <td>
                        @if($deposit->status === 'pending')
                            <form action="{{ route('admin.deposits.update', $deposit->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="status">
                                    <option value="completed">Hoàn thành</option>
                                    <option value="failed">Thất bại</option>
                                </select>
                                <button type="submit" class="action-btn edit-btn">Xác nhận</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection