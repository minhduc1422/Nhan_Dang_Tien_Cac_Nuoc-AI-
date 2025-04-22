<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Gói Nạp Mới - Admin</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-control { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; display: inline-block; transition: transform 0.3s ease; }
        .btn:hover { transform: scale(1.1); }
        .btn-success { background: #5cb85c; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Thêm Gói Nạp Mới</h1>
        <form action="{{ route('admin.deposit_plans.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Giá tiền (VNĐ)</label>
                <input type="number" name="amount" class="form-control" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Số Tokens</label>
                <input type="number" name="tokens" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <input type="text" name="description" class="form-control">
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="is_active" class="form-control">
                    <option value="1">Kích hoạt</option>
                    <option value="0">Không kích hoạt</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Lưu</button>
            <a href="{{ route('admin.deposit_plans') }}" class="btn btn-secondary">Hủy</a>
        </form>
    </div>
</body>
</html>