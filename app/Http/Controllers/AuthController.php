<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * إنشاء حساب جديد
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'role' => 'required|string|in:admin,exhibitor,visitor',
            'phone' => 'nullable|string',
            'company_name' => 'required_if:role,exhibitor|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'phone' => $request->phone,
                'company_name' => $request->role === 'exhibitor' ? $request->company_name : null,
            ]);

            /** @var \Tymon\JWTAuth\JWTGuard $guard */
            $guard = Auth::guard('api');
            $token = $guard->login($user);

            return response()->json([
                'status'  => 'success',
                'message' => 'تم إنشاء الحساب بنجاح',
                'user'    => $user,
                'authorisation' => [
                    'token' => $token,
                    'type'  => 'bearer',
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'حدث خطأ أثناء التسجيل: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تسجيل الدخول التقليدي
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('api');
        $token = $guard->attempt($credentials);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات الدخول غير صحيحة',
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * إرسال رمز OTP للإيميل
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;
        $otp = rand(100000, 999999);

        try {
            DB::table('otp_codes')->updateOrInsert(
                ['email' => $email],
                ['otp' => $otp, 'created_at' => now()]
            );

            Notification::route('mail', $email)
                        ->notify(new SendOtpNotification($otp));

            return response()->json([
                'status' => 'success',
                'message' => 'تم إرسال الكود بنجاح إلى ' . $email
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'فشل الإرسال: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحقق من الـ OTP وتسجيل الدخول
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|numeric',
        ]);

        $entry = DB::table('otp_codes')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$entry || Carbon::parse($entry->created_at)->addMinutes(10)->isPast()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'كود التحقق غير صحيح أو انتهت صلاحيته'
            ], 400);
        }

        // استخدام query() يحل مشكلة التنبيه الأحمر تحت where
        $user = User::query()->where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'المستخدم غير موجود'
            ], 404);
        }

        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('api');
        $token = $guard->login($user);

        DB::table('otp_codes')->where('email', $request->email)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'تم التحقق وتسجيل الدخول بنجاح',
            'access_token' => $token,
            'token_type' => 'bearer',
            'user'    => $user
        ], 200);
    }

    /**
     * تسجيل الخروج
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    /**
     * تحديث التوكن
     */
    public function refresh()
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('api');
        return $this->respondWithToken($guard->refresh());
    }

    /**
     * تنسيق استجابة التوكن
     */
    protected function respondWithToken($token)
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('api');

        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
            'user' => $guard->user()
        ]);
    }
}