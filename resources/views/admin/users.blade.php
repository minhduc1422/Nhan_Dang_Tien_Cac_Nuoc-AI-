@extends('layouts.admin')

@section('title', 'Quản lý tài khoản')

@section('content')
<div class="admin-table-container">
    <h2>Quản lý tài khoản</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Token</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>{{ $user->tokens }}</td>
                    <td>
                        <!-- Form thay đổi vai trò -->
                        <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PUT')
                            <select name="role" onchange="this.form.submit()" class="role-select">
                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </form>
                        | 
                        <!-- Form xóa tài khoản -->
                        <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản {{ $user->name }}?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

<style>
.role-select {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 14px;
    cursor: pointer;
}

.action-btn {
    padding: 5px 10px;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
}

.edit-btn {
    background: #00d8d6;
    color: #fff;
}

.delete-btn {
    background: #e74c3c;
    color: #fff;
    border: none;
    cursor: pointer;
}

.delete-btn:hover {
    background: #c0392b;
}
</style>