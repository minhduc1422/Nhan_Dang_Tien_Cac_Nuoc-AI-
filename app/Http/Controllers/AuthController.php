<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ChatApi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    // Các phương thức hiện có (giữ nguyên từ mã bạn cung cấp)
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
            'avatar' => 'nullable|image|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'avatar' => $avatarPath,
            'role' => 'user',
            'tokens' => 5,
            'token' => 5,
            'balance' => 0.00,
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

    public function showProfile()
    {
        return view('user.profile', ['user' => Auth::user()]);
    }

    public function updateName(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $user->name = $request->name;
            $user->linked_email = null;
            $user->save();

            return redirect()->route('user.profile')->with('success', 'Cập nhật tên thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi khi cập nhật tên: ' . $e->getMessage()]);
        }
    }

    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'old_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            if (!Hash::check($request->old_password, $user->password)) {
                return back()->withErrors(['old_password' => 'Mật khẩu cũ không đúng!']);
            }

            $user->password = bcrypt($request->password);
            $user->save();

            return redirect()->route('user.profile')->with('success', 'Đổi mật khẩu thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi khi đổi mật khẩu: ' . $e->getMessage()]);
        }
    }

    public function updateAvatar(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        try {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();

            return redirect()->route('user.profile')->with('success', 'Cập nhật avatar thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Lỗi khi cập nhật avatar: ' . $e->getMessage()]);
        }
    }

    public function adminDashboard()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $totalUsers = User::count();
        $totalDeposits = DB::table('deposits')->count();
        $pendingDeposits = DB::table('deposits')->where('status', 'pending')->count();
        $completedDeposits = DB::table('deposits')->where('status', 'completed')->count();
        $failedDeposits = DB::table('deposits')->where('status', 'failed')->count();
        $totalDetections = DB::table('money_detections')->count();
        $successfulDetections = DB::table('money_detections')->where('status', 'completed')->count();
        $failedDetections = DB::table('money_detections')->where('status', 'failed')->count();
        $totalPlans = DB::table('deposit_plans')->count();
        $recentDeposits = DB::table('deposits')
            ->join('users', 'deposits.user_id', '=', 'users.id')
            ->select('deposits.*', 'users.name as user_name')
            ->orderBy('deposits.created_at', 'desc')
            ->take(10)
            ->get();
        $recentDetections = DB::table('money_detections')
            ->join('users', 'money_detections.user_id', '=', 'users.id')
            ->select('money_detections.*', 'users.name as user_name')
            ->orderBy('money_detections.created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalDeposits',
            'pendingDeposits',
            'completedDeposits',
            'failedDeposits',
            'totalDetections',
            'successfulDetections',
            'failedDetections',
            'totalPlans',
            'recentDeposits',
            'recentDetections'
        ));
    }

    public function showDeposit()
    {
        if (!Auth::check() || Auth::user()->role === 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }
        $plans = DB::table('deposit_plans')->where('is_active', 1)->get();
        return view('deposit', compact('plans'));
    }

    public function requestDeposit(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:deposit_plans,id',
        ]);

        $plan = DB::table('deposit_plans')->where('id', $request->plan_id)->first();

        DB::table('deposits')->insert([
            'user_id' => Auth::user()->id,
            'amount' => $plan->amount,
            'tokens' => $plan->tokens,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Yêu cầu nạp tiền đã được gửi thành công! Vui lòng xác nhận với ảnh.']);
    }

    public function depositConfirmation(Request $request)
    {
        try {
            $request->validate([
                'proof_image' => 'required|image|max:5120',
                'plan_id' => 'required|exists:deposit_plans,id',
            ]);

            $user = Auth::user();
            $plan = DB::table('deposit_plans')->where('id', $request->plan_id)->first();
            $image = $request->file('proof_image');
            $imagePath = $image->store('deposit_proofs', 'public');

            $deposit = DB::table('deposits')
                ->where('user_id', $user->id)
                ->where('amount', $plan->amount)
                ->where('tokens', $plan->tokens)
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
                    'amount' => $plan->amount,
                    'tokens' => $plan->tokens,
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
            'status' => 'required|in:pending,completed,failed'
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

    public function manageUsers()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function deleteUser(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.users')->with('error', 'Người dùng không tồn tại!');
        }

        if ($user->id === Auth::user()->id) {
            return redirect()->route('admin.users')->with('error', 'Bạn không thể xóa chính mình!');
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Xóa tài khoản thành công!');
    }

    public function updateUserRole(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.users')->with('error', 'Người dùng không tồn tại!');
        }

        if ($user->id === Auth::user()->id) {
            return redirect()->route('admin.users')->with('error', 'Bạn không thể thay đổi vai trò của chính mình!');
        }

        $request->validate([
            'role' => 'required|in:user,admin'
        ]);

        $user->role = $request->role;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'Cập nhật vai trò thành công!');
    }

    public function stats()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $dates = [];
        $depositData = [];

        try {
            $plans = DB::table('deposit_plans')->pluck('amount')->unique()->toArray();
            $plans = array_map('floatval', $plans);

            if (empty($plans)) {
                $currentDate = now()->format('Y-m-d');
                $dates = [$currentDate];
                $depositData = [];
                return view('admin.stats', compact('dates', 'depositData'));
            }

            $deposits = DB::table('deposits')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    'amount',
                    DB::raw('COUNT(*) as count')
                )
                ->where('status', 'completed')
                ->whereIn('amount', $plans)
                ->groupBy('date', 'amount')
                ->orderBy('date', 'asc')
                ->get();

            if ($deposits->isEmpty()) {
                $currentDate = now()->format('Y-m-d');
                $dates = [$currentDate];
                foreach ($plans as $amount) {
                    $depositData[$amount] = [0];
                }
            } else {
                $grouped = $deposits->groupBy('date');
                foreach ($grouped as $date => $records) {
                    $dates[] = $date;
                    $amounts = $records->pluck('count', 'amount')->all();

                    foreach ($plans as $amount) {
                        $amounts = array_change_key_case($amounts, CASE_LOWER);
                        $depositData[$amount][] = $amounts[$amount] ?? 0;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in stats method: ' . $e->getMessage());
            $currentDate = now()->format('Y-m-d');
            $dates = [$currentDate];
            $depositData = [];
        }

        return view('admin.stats', compact('dates', 'depositData'));
    }

    public function histori()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $detections = DB::table('money_detections')
            ->join('users', 'money_detections.user_id', '=', 'users.id')
            ->select('money_detections.*', 'users.name as user_name')
            ->orderBy('money_detections.created_at', 'desc')
            ->get();
        return view('admin.histori', compact('detections'));
    }

    public function manageDepositPlans()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $plans = DB::table('deposit_plans')->get();
        return view('admin.deposit_plans', compact('plans'));
    }

    public function createDepositPlan()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        return view('admin.deposit_plans.create');
    }

    public function storeDepositPlan(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $request->validate([
            'amount' => 'required|numeric',
            'tokens' => 'required|integer',
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        DB::table('deposit_plans')->insert([
            'amount' => $request->amount,
            'tokens' => $request->tokens,
            'description' => $request->description,
            'is_active' => $request->is_active,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.deposit_plans')->with('success', 'Thêm gói nạp thành công!');
    }

    public function editDepositPlan($id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $plan = DB::table('deposit_plans')->where('id', $id)->first();
        if (!$plan) {
            return redirect()->route('admin.deposit_plans')->with('error', 'Gói nạp không tồn tại!');
        }

        return view('admin.deposit_plans.edit', compact('plan'));
    }

    public function updateDepositPlan(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $request->validate([
            'amount' => 'required|numeric',
            'tokens' => 'required|integer',
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $plan = DB::table('deposit_plans')->where('id', $id)->first();
        if (!$plan) {
            return redirect()->route('admin.deposit_plans')->with('error', 'Gói nạp không tồn tại!');
        }

        DB::table('deposit_plans')
            ->where('id', $id)
            ->update([
                'amount' => $request->amount,
                'tokens' => $request->tokens,
                'description' => $request->description,
                'is_active' => $request->is_active,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.deposit_plans')->with('success', 'Cập nhật gói nạp thành công!');
    }

    public function destroyDepositPlan($id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $plan = DB::table('deposit_plans')->where('id', $id)->first();
        if (!$plan) {
            return redirect()->route('admin.deposit_plans')->with('error', 'Gói nạp không tồn tại!');
        }

        DB::table('deposit_plans')->where('id', $id)->delete();
        return redirect()->route('admin.deposit_plans')->with('success', 'Xóa gói nạp thành công!');
    }

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

        $response = Http::attach(
            'file',
            file_get_contents($imagePath),
            $image->getClientOriginalName()
        )->post('http://localhost:55015/detect_money');

        $data = $response->json();

        if (isset($data['image'])) {
            DB::table('users')->where('id', $user->id)->decrement('tokens', 1);

            $imageBase64 = $data['image'];
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64));
            $imageName = 'money_detections/' . uniqid() . '.jpg';
            Storage::disk('public')->put($imageName, $imageData);
            $imagePath = $imageName;

            $amount = $data['detection_info']['denomination'] === 'Không nhận diện được' ? 0 : (int) filter_var($data['detection_info']['denomination'], FILTER_SANITIZE_NUMBER_INT);
            $result = $data['detection_info']['denomination'] ?? 'Không nhận diện được';
            $status = $data['detection_info']['denomination'] === 'Không nhận diện được' ? 'Thất bại' : 'Thành công';

            DB::table('money_detections')->insert([
                'user_id' => $user->id,
                'amount' => $amount,
                'result' => $result,
                'image' => $imagePath,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json($data);
    }

    public function chatbotConfig()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $config = ChatApi::latest()->first();
        if (!$config) {
            $config = new ChatApi([
                'model_name' => 'gemini-1.5-pro',
                'api_key' => ''
            ]);
        }

        return view('admin.chatbot_config', compact('config'));
    }

    public function getApiKey(Request $request)
    {
        $modelName = $request->query('model_name');
        $config = ChatApi::where('model_name', $modelName)->orderBy('updated_at', 'desc')->first();
        return response()->json(['api_key' => $config ? $config->api_key : '']);
    }

    public function showChatbotConfig()
    {
        $config = ChatApi::orderBy('created_at', 'desc')->first();
        return view('admin.chatbot_config', compact('config'));
    }

    public function updateChatbotConfig(Request $request)
    {
        $request->validate([
            'model_name' => 'required|string|in:gemini-1.5-pro,openai-gpt-4',
            'api_key' => 'required|string',
        ]);

        try {
            $response = Http::timeout(60)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('http://localhost:8000/llm-update', [
                'model_name' => $request->model_name,
                'api_key' => $request->api_key,
            ]);

            if ($response->successful()) {
                // Cập nhật local database
                $config = ChatApi::latest()->first();
                if ($config) {
                    $config->update([
                        'model_name' => $request->model_name,
                        'api_key' => $request->api_key,
                        'updated_at' => now(),
                    ]);
                } else {
                    ChatApi::create([
                        'model_name' => $request->model_name,
                        'api_key' => $request->api_key,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return response()->json(['message' => 'Cập nhật cấu hình chatbot thành công!']);
            } else {
                $error = json_decode($response->body(), true);
                $detail = $error['detail'] ?? 'Lỗi không xác định từ FastAPI';
                Log::error('Lỗi từ FastAPI /llm-update: ' . $detail);
                return response()->json(['message' => 'Lỗi: ' . $detail], $response->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Lỗi kết nối tới FastAPI /llm-update: ' . $e->getMessage());
            return response()->json(['message' => 'Không thể kết nối tới dịch vụ chatbot. Vui lòng thử lại sau.'], 500);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật chatbot config: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function chat(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập để sử dụng chatbot!'], 401);
        }

        $user = Auth::user();
        if ($user->tokens < 1) {
            return response()->json(['error' => 'Bạn không đủ token để sử dụng chatbot! Vui lòng nạp thêm.'], 403);
        }

        $request->validate([
            'question' => 'required|string|min:1',
        ]);

        try {
            $response = Http::timeout(90)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('http://localhost:8000/chat', [
                'question' => $request->question,
            ]);

            if ($response->successful()) {
                // Trừ token sau khi gọi thành công
                DB::table('users')->where('id', $user->id)->decrement('tokens', 1);
                return response()->json($response->json());
            } else {
                $error = json_decode($response->body(), true);
                $status = $response->status();
                $detail = $error['detail'] ?? 'Lỗi không xác định từ chatbot';
                Log::error('Lỗi từ FastAPI /chat: ' . $detail);
                
                if ($status == 422) {
                    return response()->json(['error' => 'Dữ liệu không hợp lệ: ' . $detail], 422);
                } elseif ($status == 429) {
                    return response()->json(['error' => 'Đã vượt quá giới hạn yêu cầu. Vui lòng thử lại sau.'], 429);
                } else {
                    return response()->json(['error' => 'Lỗi từ chatbot: ' . $detail], $status);
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Lỗi kết nối tới FastAPI /chat: ' . $e->getMessage());
            return response()->json(['error' => 'Không thể kết nối tới chatbot. Vui lòng thử lại sau.'], 500);
        } catch (\Exception $e) {
            Log::error('Lỗi khi gọi chatbot: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
}