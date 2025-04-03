<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Tính năng cũ
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'Thông tin đăng nhập không đúng.']);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'user', // Thêm role mặc định
        ]);

        Auth::login($user);
        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // Tính năng mới: Admin Dashboard
    public function adminDashboard()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }
        return view('admin.dashboard');
    }

    // Tính năng mới: Nạp tiền
    public function showDeposit()
    {
        if (!Auth::check() || Auth::user()->role === 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }
        return view('deposit');
    }

    public function requestDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|in:50000,100000,500000',
            'tokens' => 'required|integer|in:20,100,650'
        ]);

        DB::table('deposits')->insert([
            'user_id' => Auth::user()->id,
            'amount' => $request->amount,
            'tokens' => $request->tokens,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Yêu cầu nạp tiền đã được gửi thành công! Vui lòng xác nhận với ảnh.']);
    }

    // Phương thức mới: Xử lý xác nhận nạp tiền - Cập nhật để luôn trả về JSON
    public function depositConfirmation(Request $request)
    {
        try {
            $request->validate([
                'proof_image' => 'required|image|max:5120', // Giới hạn 5MB
                'amount' => 'required|numeric|in:50000,100000,500000',
                'tokens' => 'required|integer|in:20,100,650'
            ]);

            $user = Auth::user();
            $image = $request->file('proof_image');
            $imagePath = $image->store('deposit_proofs', 'public'); // Lưu vào storage/public/deposit_proofs

            $deposit = DB::table('deposits')
                ->where('user_id', $user->id)
                ->where('amount', $request->amount)
                ->where('tokens', $request->tokens)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($deposit) {
                DB::table('deposits')
                    ->where('id', $deposit->id)
                    ->update(['proof_image' => $imagePath, 'updated_at' => now()]);
            } else {
                DB::table('deposits')->insert([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'tokens' => $request->tokens,
                    'status' => 'pending',
                    'proof_image' => $imagePath,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return response()->json(['message' => 'Xác nhận nạp tiền thành công! Đợi admin duyệt.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    // Tính năng mới: Quản lý nạp tiền
    public function manageDeposits()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $deposits = DB::table('deposits')->orderBy('created_at', 'desc')->get();
        return view('admin.deposits', compact('deposits'));
    }

    public function updateDeposit(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $deposit = DB::table('deposits')->where('id', $id)->first();
        if (!$deposit) {
            return redirect()->back()->with('error', 'Yêu cầu không tồn tại!');
        }

        $request->validate([
            'status' => 'required|in:completed,failed'
        ]);

        DB::table('deposits')
            ->where('id', $id)
            ->update(['status' => $request->status, 'updated_at' => now()]);

        if ($request->status === 'completed') {
            DB::table('users')
                ->where('id', $deposit->user_id)
                ->increment('tokens', $deposit->tokens);
        }

        return redirect()->route('admin.deposits')->with('success', 'Cập nhật trạng thái thành công!');
    }

    // Tính năng mới: Nhận diện tiền với kiểm tra token
    public function detectMoney(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập để nhận diện tiền!'], 401);
        }

        $user = Auth::user();
        if ($user->tokens < 1) {
            return response()->json(['error' => 'Bạn không đủ token để nhận diện tiền! Vui lòng nạp thêm.'], 403);
        }

        $request->validate(['image' => 'required|image|max:5120']);
        $image = $request->file('image');
        $imagePath = $image->getPathname();

        $response = \Illuminate\Support\Facades\Http::attach(
            'file',
            file_get_contents($imagePath),
            $image->getClientOriginalName()
        )->post('http://localhost:60074/detect_money');

        $data = $response->json();

        if (isset($data['image'])) {
            DB::table('users')->where('id', $user->id)->decrement('tokens', 1);
        }

        return response()->json($data);
    }

    // Tính năng mới: Quản lý người dùng
    public function manageUsers()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $users = User::all();
        return view('admin.users', compact('users'));
    }

    // Tính năng mới: Thống kê nạp tiền
    public function depositStats()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $stats = DB::table('deposits')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.stats', compact('stats'));
    }
}