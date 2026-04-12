<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function register(Request $request) 
    {
   
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|between:2,100',
        'email' => 'required|string|email|max:100|unique:users',
        'password' => [
            'required',
            'confirmed',
            Password::min(8) // الحد الأدنى 8 خانات
                ->letters()   // يجب أن يحتوي أحرف
                ->mixedCase() // أحرف كبيرة وصغيرة
                ->numbers()   // أرقام
                ->symbols(),  // رموز مثل @ # $
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
//reg
try{
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'phone' => $request->phone,
        'company_name' =>$request->role === 'exhibitor' ? $request->company_name : null,
    ]);

//token    
  $token = auth()->guard('api')->login($user);
  return response()->json([
            'status'  => 'success',
            'message' => 'تم إنشاء الحساب بنجاح',
            'user'    => $user,
            'authorisation' => [
                'token' => $token,
                'type'  => 'bearer',
            ]
        ], 201);
}
catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'حدث خطأ أثناء التسجيل، يرجى المحاولة لاحقاً'
        ], 500);
    }
    }

public function refresh()
{
    /** @var \Tymon\JWTAuth\JWTGuard $guard */
    $guard = auth()->guard('api');
    
    $newToken = $guard->refresh();
    
    return $this->respondWithToken($newToken);
}
protected function respondWithToken($token)
{
    return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
       'expires_in' => config('jwt.ttl') * 60,
        'user' => auth()->guard('api')->user()
    ]);
}
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
   $token = auth()->guard('api')->attempt($credentials);

   
    if (!$token) {
        return response()->json([
            'message' => 'بيانات الدخول غير صحيحة',
        ], 401);
    }

   
  return $this->respondWithToken($token);
   
}
public function logout()
{
    auth()->guard('api')->logout();

    return response()->json([
        'message' => 'تم تسجيل الخروج بنجاح',
    ]);
}
}
