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
                        <a href="#" class="action-btn edit-btn">Sửa</a> | 
                        <a href="#" class="action-btn delete-btn">Xóa</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection