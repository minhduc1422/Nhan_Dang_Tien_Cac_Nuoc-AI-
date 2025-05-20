@extends('layouts.admin')

@section('title', 'Quản lý tài khoản')

@section('content')
<div class="admin-table-container">
    <h2>Quản lý tài khoản</h2>

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
                        <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST" class="role-form" style="display:inline;">
                            @csrf
                            @method('PUT')
                            <select name="role" class="role-select">
                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </form>
                        | 
                        <!-- Form xóa tài khoản -->
                        <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="delete-form" style="display:inline;">
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
</div>

<style>
.admin-table-container {
    width: 90%;
    max-width: 1200px;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #1e272e;
    text-align: center;
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

.admin-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 16px;
}

.admin-table th,
.admin-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.admin-table th {
    background-color: #1e272e;
    color: #fff;
    font-weight: bold;
}

.admin-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.admin-table tr:hover {
    background-color: #f1f1f1;
}

.role-select {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 14px;
    cursor: pointer;
}

.role-select:focus {
    outline: none;
    border-color: #1e272e;
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

.delete-btn i {
    margin-right: 5px;
}
</style>

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleForms = document.getElementsByClassName('role-form');
    const roleSelects = document.getElementsByClassName('role-select');
    const deleteForms = document.getElementsByClassName('delete-form');
    const deleteButtons = document.getElementsByClassName('delete-btn');

    // Xử lý thay đổi vai trò với SweetAlert
    Array.from(roleSelects).forEach((select, index) => {
        select.addEventListener('change', function (e) {
            e.preventDefault();
            const form = roleForms[index];
            const selectedValue = this.value;
            const originalValue = this.getAttribute('data-original-value') || this.options[0].value;

            Swal.fire({
                title: 'Xác nhận',
                text: 'Bạn có chắc chắn muốn thay đổi vai trò này?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Lưu',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    select.value = originalValue;
                }
            });
        });

        // Lưu giá trị ban đầu
        select.setAttribute('data-original-value', select.value);
    });

    // Xử lý xóa tài khoản với SweetAlert
    Array.from(deleteButtons).forEach((button, index) => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const form = deleteForms[index];
            const userName = form.closest('tr').querySelector('td:nth-child(2)').textContent;

            Swal.fire({
                title: 'Xác nhận',
                text: `Bạn có chắc chắn muốn xóa tài khoản ${userName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Lưu',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
@endsection