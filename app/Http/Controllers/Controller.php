<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // دالة موحدة للردود الناجحة
    public function sendResponse($result, $message, $code = 200)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $result,
        ], $code);
    }

    // دالة موحدة لردود الأخطاء
    public function sendError($error, $code = 404)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $error,
        ], $code);
    }
}
