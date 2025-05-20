<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\DepositPlan;
use App\Models\Deposit;
use App\Models\MoneyDetection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
class AuthController_app extends Controller
{
    /**
     * Hàm xác thực thủ công bằng email và password
     */
private function authenticate(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Thông tin đăng nhập không chính xác.',
                ], 401);
            }

            return $user;
        } catch (ValidationException $e) {
            Log::error('Validation error in authenticate: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ: ' . $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in authenticate: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Đăng nhập
     */
    public function login(Request $request)
    {
        try {
            $user = $this->authenticate($request);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'message' => 'Đăng nhập thành công!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in login: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Đăng ký
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng ký thành công! Bạn được tặng 5 lần sử dụng miễn phí.',
                'user' => $user,
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Validation error in register: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in register: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy danh sách gói nạp
     */
    public function getDepositPlans(Request $request)
    {
        try {
            $user = $this->authenticate($request);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }

            $plans = DepositPlan::where('is_active', 1)->get();
            if ($plans->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'data' => [],
                    'message' => 'Không có gói nạp nào khả dụng',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => $plans,
                'message' => 'Lấy danh sách gói nạp thành công',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getDepositPlans: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi lấy danh sách gói nạp: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xác nhận nạp tiền
     */
    public function depositConfirmation(Request $request)
    {
        try {
            $user = $this->authenticate($request);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }

            $request->validate([
                'plan_id' => 'required|integer|exists:deposit_plans,id',
                'proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

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
            Log::error('Validation error in depositConfirmation: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in depositConfirmation: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi xử lý xác nhận nạp tiền: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Nhận diện tiền
     */
    public function detectMoney(Request $request)
    {
        try {
            $user = $this->authenticate($request);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }

            DB::beginTransaction();
            try {
                $user = DB::table('users')->where('id', $user->id)->lockForUpdate()->first();
                if (!$user || $user->tokens <= 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Bạn không đủ token! Vui lòng nạp thêm.',
                    ], 403);
                }

                $request->validate([
                    'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
                ]);
                $imagePath = $request->file('image')->store('money_detections', 'public');

                // Gọi FastAPI
                $response = Http::timeout(120)->attach(
                    'file',
                    file_get_contents($request->file('image')->getPathname()),
                    $request->file('image')->getClientOriginalName()
                )->withHeaders(['X-Client-Type' => 'mobile'])
                 ->post('http://localhost:55015/detect_money');

                if ($response->failed()) {
                    Log::error('FastAPI error: ' . $response->body());
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Lỗi khi gọi dịch vụ nhận diện tiền',
                        'details' => $response->body(),
                    ], $response->status());
                }

                $data = $response->json();
                if (empty($data) || !isset($data['detection_info']['denomination'])) {
                    Log::error('FastAPI response is invalid: ' . json_encode($data));
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Phản hồi từ dịch vụ nhận diện không hợp lệ',
                    ], 500);
                }

                // Giảm tokens
                $affected = DB::table('users')
                    ->where('id', $user->id)
                    ->where('tokens', '>', 0)
                    ->update(['tokens' => DB::raw('tokens - 1')]);

                if ($affected === 0) {
                    throw new \Exception('Không thể giảm tokens');
                }

                $newTokens = DB::table('users')->where('id', $user->id)->value('tokens');

                // Xử lý thông tin nhận diện
                $amount = $data['detection_info']['denomination'] !== 'Không nhận diện được'
                    ? (int) filter_var($data['detection_info']['denomination'], FILTER_SANITIZE_NUMBER_INT)
                    : 0;
                $result = $data['detection_info']['denomination'] ?? 'Không nhận diện được';
                $status = $result === 'Không nhận diện được' ? 'Thất bại' : 'Thành công';

                // Lưu lịch sử nhận diện
                DB::table('money_detections')->insert([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'result' => $result,
                    'image' => $imagePath,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Nhận diện thành công',
                    'data' => $data,
                    'image_path' => asset('storage/' . $imagePath),
                    'tokens' => $newTokens,
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error in detectMoney transaction: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lỗi khi xử lý nhận diện: ' . $e->getMessage(),
                ], 500);
            }
        } catch (ValidationException $e) {
            Log::error('Validation error in detectMoney: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ: ' . $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in detectMoney: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        try {
            $user = $this->authenticate($request);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng xuất thành công!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in logout: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi đăng xuất: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy thông tin hồ sơ
     */
    public function profile(Request $request)
    {
        try {
            $user = $this->authenticate($request);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'message' => 'Lấy thông tin hồ sơ thành công',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in profile: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi lấy thông tin hồ sơ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật tên
     */
    public function updateName(Request $request)
    {
        try {
            $user = $this->authenticate($request);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }

            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $user->name = $request->name;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật tên thành công!',
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Validation error in updateName: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in updateName: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi cập nhật tên: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật mật khẩu
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = $this->authenticate($request);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }

            $request->validate([
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Đổi mật khẩu thành công!',
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Validation error in updatePassword: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in updatePassword: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi đổi mật khẩu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật avatar
     */
    public function updateAvatar(Request $request)
    {
        try {
            $user = $this->authenticate($request);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }

            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật avatar thành công!',
                'avatar' => asset('storage/' . $path),
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Validation error in updateAvatar: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in updateAvatar: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi cập nhật avatar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa tài khoản
     */
    public function deleteAccount(Request $request)
    {
        try {
            $user = $this->authenticate($request);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa tài khoản thành công!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in deleteAccount: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi xóa tài khoản: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function googleLogin(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'name' => 'required|string|max:255',
            ]);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'user',
                    'tokens' => 5,
                    'balance' => 0.00,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                    'tokens' => $user->tokens
                ],
                'message' => 'Đăng nhập Google thành công!'
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Validation error in googleLogin: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in googleLogin: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }
    
}