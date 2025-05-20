<?php

namespace App\Http\Controllers;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ChatApi;
use App\Models\DepositPlan;
use App\Models\Deposit;
use App\Models\MoneyDetection;
use App\Models\Metadata;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\Apk;

class AuthController extends Controller
{

    // Các phương thức khác giữ nguyên
    public function signup(Request $request)
    {
        return $this->apiRegister($request);
    }

    public function getDepositPlans()
    {
        $plans = DepositPlan::where('is_active', 1)->get();
        return response()->json([
            'success' => true,
            'data' => $plans
        ], 200);
    }

    public function depositConfirmation(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|integer|exists:deposit_plans,id',
                'proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Chưa đăng nhập',
                ], 401);
            }

            $plan = DepositPlan::findOrFail($request->plan_id);

            $imagePath = null;
            if ($request->hasFile('proof_image')) {
                $imagePath = $request->file('proof_image')->store('deposit_proofs', 'public');
            }

            $deposit = Deposit::create([
                'user_id' => $user->id,
                'amount' => $plan->amount,
                'tokens' => $plan->tokens,
                'status' => 'pending',
                'proof_image' => $imagePath,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Xác nhận nạp tiền thành công! Đợi admin duyệt.',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xử lý xác nhận nạp tiền: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

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
            return redirect()->intended('/')->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
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
                'password' => Hash::make($request->password),
                'avatar' => $avatarPath,
                'role' => 'user',
                'tokens' => 5,
                'balance' => 0.00,
            ]);

            Auth::login($user);
            return redirect('/')->with('success', 'Đăng ký thành công! Bạn được tặng 5 lần sử dụng miễn phí.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Lỗi khi đăng ký: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Lỗi hệ thống: ' . $e->getMessage()])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Đăng xuất thành công!');
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

            $user->password = Hash::make($request->password);
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

        $deposit = DB::table('deposits')->insertGetId([
            'user_id' => Auth::user()->id,
            'amount' => $plan->amount,
            'tokens' => $plan->tokens,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Yêu cầu nạp tiền đã được gửi thành công! Vui lòng xác nhận với ảnh.']);
    }

    public function manageDeposits()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $deposits = DB::table('deposits')
            ->join('users', 'deposits.user_id', '=', 'users.id')
            ->join('deposit_plans', 'deposits.amount', '=', 'deposit_plans.amount')
            ->select('deposits.*', 'users.name as user_name', 'deposit_plans.tokens as plan_tokens')
            ->orderBy('deposits.created_at', 'desc')
            ->get();
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
            return redirect()->route('admin.users')->with('error', 'Bạn không có quyền truy cập!');
        }

        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.users')->with('error', 'Người dùng không tồn tại!');
        }

        if ($user->id === Auth::user()->id) {
            return redirect()->route('admin.users')->with('error', 'Bạn không thể xóa chính mình!');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.users')->with('error', 'Không thể xóa tài khoản admin!');
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
            ->select(
                'money_detections.id',
                'money_detections.user_id',
                'money_detections.amount',
                'money_detections.result',
                'money_detections.image',
                'money_detections.status',
                'money_detections.created_at',
                'money_detections.updated_at',
                'users.name as user_name'
            )
            ->orderBy('money_detections.created_at', 'desc')
            ->paginate(10);
    
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
            'amount' => 'required|numeric|min:0',
            'tokens' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $planId = DB::table('deposit_plans')->insertGetId([
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
            'amount' => 'required|numeric|min:0',
            'tokens' => 'required|integer|min:1',
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
        try {
            // Kiểm tra đăng nhập
            if (!Auth::check()) {
                return response()->json(['error' => 'Vui lòng đăng nhập để nhận diện tiền!'], 401);
            }

            $userId = Auth::id();

            // Bắt đầu transaction
            DB::beginTransaction();
            try {
                // Lấy user với lock để tránh xung đột
                $user = DB::table('users')->where('id', $userId)->lockForUpdate()->first();
                if (!$user || $user->tokens <= 0) {
                    return response()->json(['error' => 'Bạn không đủ token để nhận diện tiền! Vui lòng nạp thêm.'], 403);
                }

                // Validate ảnh đầu vào
                $request->validate(['image' => 'required|image|mimes:jpeg,png,jpg|max:5120']);
                $imagePath = $request->file('image')->store('money_detections', 'public');

                // Gọi FastAPI
                $response = Http::timeout(120)->attach(
                    'file',
                    file_get_contents($request->file('image')->getPathname()),
                    $request->file('image')->getClientOriginalName()
                )->post('http://localhost:55015/detect_money');

                if ($response->failed()) {
                    return response()->json(['error' => 'Lỗi khi gọi dịch vụ nhận diện tiền', 'details' => $response->body()], $response->status());
                }

                $data = $response->json();
                if (empty($data)) {
                    return response()->json(['error' => 'Phản hồi từ dịch vụ nhận diện rỗng'], 500);
                }

                // Giảm tokens
                $affected = DB::table('users')
                    ->where('id', $userId)
                    ->where('tokens', '>', 0)
                    ->update(['tokens' => DB::raw('tokens - 1')]);

                if ($affected === 0) {
                    throw new \Exception('Không thể giảm tokens');
                }

                // Lấy tokens mới
                $newTokens = DB::table('users')->where('id', $userId)->value('tokens');

                // Xử lý thông tin nhận diện
                $amount = isset($data['detection_info']['denomination']) && $data['detection_info']['denomination'] !== 'Không nhận diện được'
                    ? (int) filter_var($data['detection_info']['denomination'], FILTER_SANITIZE_NUMBER_INT)
                    : 0;
                $result = $data['detection_info']['denomination'] ?? 'Không nhận diện được';
                $status = $result === 'Không nhận diện được' ? 'Thất bại' : 'Thành công';

                // Lưu lịch sử nhận diện
                DB::table('money_detections')->insert([
                    'user_id' => $userId,
                    'amount' => $amount,
                
                    'result' => $result,
                    'image' => $imagePath,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::commit();
                return response()->json([
                    'message' => 'Nhận diện thành công',
                    'data' => $data,
                    'image_path' => asset('storage/' . $imagePath),
                    'tokens' => $newTokens
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Lỗi trong transaction: ' . $e->getMessage());
                return response()->json(['error' => 'Lỗi khi xử lý nhận diện: ' . $e->getMessage()], 500);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Dữ liệu không hợp lệ', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi trong detectMoney: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
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
        $config = ChatApi::where('model_name', $request->model_name)->orderBy('updated_at', 'desc')->first();
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
            ])->post('http://localhost:55015/llm-update', [
                'model_name' => $request->model_name,
                'api_key' => $request->api_key,
            ]);

            if ($response->successful()) {
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
        Log::info('API chat called', ['request' => $request->all()]);

        try {
            // Validate và làm sạch dữ liệu đầu vào
            $request->validate([
                'question' => 'required|string|min:1|max:1000', // Giới hạn độ dài
            ]);

            // Làm sạch question để loại bỏ ký tự không hợp lệ
            $question = trim(preg_replace('/[^\p{L}\p{N}\s.,!?]/u', '', $request->question));
            if (empty($question)) {
                Log::warning('Câu hỏi không hợp lệ sau khi làm sạch', ['original' => $request->question]);
                return response()->json(['error' => 'Câu hỏi chứa ký tự không hợp lệ.'], 400);
            }

            Log::info('Câu hỏi sau khi làm sạch', ['question' => $question]);

            // Gọi FastAPI
            $response = Http::timeout(90)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('http://localhost:55015/chat', [
                'question' => $question,
            ]);

            // Ghi log toàn bộ phản hồi từ FastAPI
            Log::info('Gửi yêu cầu tới FastAPI', [
                'url' => 'http://localhost:55015/chat',
                'request' => ['question' => $question],
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            // Kiểm tra phản hồi từ FastAPI
            if ($response->successful()) {
                $data = $response->json();
                Log::info('Phản hồi từ FastAPI', ['response' => $data]);

                return response()->json([
                    'status' => 'success',
                    'response' => $data['response'] ?? 'Không có phản hồi từ chatbot.',
                    'source' => $data['source'] ?? 'unknown',
                ], 200);
            } else {
                $error = $response->json() ?? [];
                $status = $response->status();
                $detail = $error['detail'] ?? $error['error'] ?? $response->body() ?? 'Lỗi không xác định từ chatbot';
                Log::error('Lỗi FastAPI chat', [
                    'status' => $status,
                    'error' => $detail,
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);

                return response()->json([
                    'error' => $detail,
                    'status' => $status,
                ], $status);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Lỗi kết nối tới FastAPI /chat', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Không thể kết nối tới chatbot. Vui lòng kiểm tra dịch vụ FastAPI.'], 503);
        } catch (ValidationException $e) {
            Log::warning('Lỗi xác thực dữ liệu trong chat', ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Dữ liệu không hợp lệ',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi bất ngờ trong chat', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function showDetectionModelConfig()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        return view('admin.change_model');
    }

    public function getCurrentDetectionModel()
    {
        try {
            $latestModel = DB::table('detection_models')
                ->select('source')
                ->orderBy('updated_at', 'desc')
                ->first();
            return response()->json(['source' => $latestModel ? $latestModel->source : null]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy nguồn mô hình hiện tại: ' . $e->getMessage());
            return response()->json(['error' => 'Không thể lấy thông tin mô hình hiện tại'], 500);
        }
    }

    public function users()
    {
        $users = DB::table('users')->paginate(10);
        return view('admin.users', compact('users'));
    }

    public function getUsers(Request $request)
    {
        $users = DB::table('users')->select('id', 'name', 'email', 'role', 'tokens')->get();
        return response()->json($users);
    }

    public function metadataConfig()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $metadata = \App\Models\Metadata::first() ?? new \App\Models\Metadata();
        return view('admin.metadata_config', compact('metadata'));
    }

    public function updateMetadataConfig(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $request->validate([
            'site_title' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'site_keywords' => 'nullable|string',
            'favicon' => 'nullable|image|mimes:ico,png,jpg,jpeg|max:2048',
            'og_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'author' => 'nullable|string|max:255',
        ]);

        try {
            $metadata = \App\Models\Metadata::first() ?? new \App\Models\Metadata();

            // Xử lý upload favicon
            if ($request->hasFile('favicon')) {
                if ($metadata->favicon) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($metadata->favicon);
                }
                $faviconPath = $request->file('favicon')->store('metadata', 'public');
                $metadata->favicon = $faviconPath;
            }

            // Xử lý upload OG image
            if ($request->hasFile('og_image')) {
                if ($metadata->og_image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($metadata->og_image);
                }
                $ogImagePath = $request->file('og_image')->store('metadata', 'public');
                $metadata->og_image = $ogImagePath;
            }

            // Cập nhật các trường khác
            $metadata->site_title = $request->site_title;
            $metadata->site_description = $request->site_description;
            $metadata->site_keywords = $request->site_keywords;
            $metadata->author = $request->author;
            $metadata->save();

            return redirect()->back()->with('success', 'Cấu hình metadata đã được cập nhật thành công!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi cập nhật metadata: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('google_id', $googleUser->id)->orWhere('email', $googleUser->email)->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }
            } else {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(uniqid()),
                    'role' => 'user',
                    'tokens' => 5,
                    'balance' => 0.00,
                ]);
            }

            Auth::login($user, true);
            return redirect('/')->with('success', 'Đăng nhập bằng Google thành công!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi đăng nhập bằng Google: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Lỗi khi đăng nhập bằng Google: ' . $e->getMessage());
        }
    }

    public function manageApks()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $apks = Apk::all();
        return view('admin.apks', compact('apks'));
    }

    public function createApk()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        return view('admin.apk_create');
    }

public function storeApk(Request $request)
{
    if (Auth::user()->role !== 'admin') {
        return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
    }

    // Debug thông tin request và file
    Log::info('Store APK request:', [
        'files' => $request->allFiles(),
        'input' => $request->all(),
        'mime_type' => $request->hasFile('apk_file') ? $request->file('apk_file')->getClientMimeType() : null,
        'extension' => $request->hasFile('apk_file') ? $request->file('apk_file')->getClientOriginalExtension() : null,
    ]);

    // Validation không dùng mimes, chỉ kiểm tra file và kích thước
    $request->validate([
        'name' => 'required|string|max:255',
        'version' => 'required|string|max:50',
        'description' => 'nullable|string',
        'apk_file' => 'required|file|max:102400', // Bỏ mimes, kiểm tra thủ công
    ]);

    try {
        // Kiểm tra phần mở rộng .apk
        $file = $request->file('apk_file');
        if (strtolower($file->getClientOriginalExtension()) !== 'apk') {
            Log::error('File không phải .apk', [
                'file' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
            ]);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'apk_file' => 'File phải là định dạng APK (.apk).',
            ]);
        }

        // Lưu file
        $filePath = $file->store('apks', 'public');

        // Tạo bản ghi trong bảng apks
        Apk::create([
            'name' => $request->name,
            'version' => $request->version,
            'description' => $request->description,
            'file_path' => $filePath,
        ]);

        return redirect()->route('admin.apks')->with('success', 'Tải lên APK thành công!');
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error when uploading APK: ' . $e->getMessage());
        return redirect()->back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        Log::error('Lỗi khi tải lên APK: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'Lỗi khi tải lên APK: ' . $e->getMessage());
    }
}
    public function editApk($id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        $apk = Apk::findOrFail($id);
        return view('admin.apk_edit', compact('apk'));
    }

public function updateApk(Request $request, $id)
{
    if (Auth::user()->role !== 'admin') {
        return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
    }

    // Debug thông tin request và file
    Log::info('Update APK request:', [
        'files' => $request->allFiles(),
        'input' => $request->all(),
        'mime_type' => $request->hasFile('apk_file') ? $request->file('apk_file')->getClientMimeType() : null,
        'extension' => $request->hasFile('apk_file') ? $request->file('apk_file')->getClientOriginalExtension() : null,
    ]);

    // Validation không dùng mimes
    $request->validate([
        'name' => 'required|string|max:255',
        'version' => 'required|string|max:50',
        'description' => 'nullable|string',
        'apk_file' => 'nullable|file|max:102400', // Bỏ mimes, kiểm tra thủ công
    ]);

    try {
        $apk = Apk::findOrFail($id);

        $data = [
            'name' => $request->name,
            'version' => $request->version,
            'description' => $request->description,
        ];

        if ($request->hasFile('apk_file')) {
            // Kiểm tra phần mở rộng .apk
            $file = $request->file('apk_file');
            if (strtolower($file->getClientOriginalExtension()) !== 'apk') {
                Log::error('File không phải .apk', [
                    'file' => $file->getClientOriginalName(),
                    'extension' => $file->getClientOriginalExtension(),
                ]);
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'apk_file' => 'File phải là định dạng APK (.apk).',
                ]);
            }

            // Xóa file cũ nếu có
            if ($apk->file_path) {
                Storage::disk('public')->delete($apk->file_path);
            }

            // Lưu file mới
            $filePath = $file->store('apks', 'public');
            $data['file_path'] = $filePath;
        }

        // Cập nhật bản ghi
        $apk->update($data);

        return redirect()->route('admin.apks')->with('success', 'Cập nhật APK thành công!');
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error when updating APK: ' . $e->getMessage());
        return redirect()->back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        Log::error('Lỗi khi cập nhật APK: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'Lỗi khi cập nhật APK: ' . $e->getMessage());
    }
}

    public function destroyApk($id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
        }

        try {
            $apk = Apk::findOrFail($id);
            if ($apk->file_path) {
                Storage::disk('public')->delete($apk->file_path);
            }
            $apk->delete();

            return redirect()->route('admin.apks')->with('success', 'Xóa APK thành công!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi xóa APK: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi xóa APK: ' . $e->getMessage());
        }
    }
}