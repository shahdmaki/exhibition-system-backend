<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BoothController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BoothBookingController;
use App\Http\Controllers\OrderController;
/*
|--------------------------------------------------------------------------
| Public Routes (روابط متاحة للجميع بدون تسجيل)
|--------------------------------------------------------------------------
*/
// أضيفي هذا السطر فوق مع الروابط العامة
Route::post('orders', [OrderController::class, 'store']);

Route::get('all-products', [ProductController::class, 'allProducts']);
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('products', [ProductController::class, 'index']);
Route::get('products/search/{name}', [ProductController::class, 'search']);
Route::get('booths/{id}/products', [ProductController::class, 'getProductsByBooth']);

// روابط العرض الأساسية (متاحة للجميع)
Route::get('categories', [CategoryController::class, 'index']);
Route::get('exhibitions', [ExhibitionController::class, 'index']);
Route::get('booths', [BoothController::class, 'index']);


/*
|--------------------------------------------------------------------------
| Protected Routes (روابط تحتاج توكن - auth:api)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth:api'], function () {

    // روابط عامة لأي مستخدم مسجل (Login/Logout/Refresh)
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    /* --- صلاحيات العارض فقط (Exhibitor) --- */
    Route::group(['middleware' => 'role:exhibitor'], function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);
        Route::get('my-products', [ProductController::class, 'myProducts']);
         Route::post('booth-booking/request', [BoothBookingController::class, 'requestBooth']);
           });
       

    /* --- صلاحيات الأدمن فقط (Admin) --- */
    Route::group(['middleware' => 'role:admin'], function () {
        // روابط إدارة المستخدمين (العارضين)
        Route::get('users/pending-exhibitors', [AdminController::class, 'getPendingExhibitors']);
        Route::get('users/exhibitors', [AdminController::class, 'getAllExhibitors']);
        Route::post('users/{id}/approve', [AdminController::class, 'approveExhibitor']);

        // روابط إدارة المحتوى
        Route::post('categories', [CategoryController::class, 'store']);
        Route::post('exhibitions', [ExhibitionController::class, 'store']);
        Route::post('booths', [BoothController::class, 'store']);
        Route::get('admin/dashboard/stats', [AdminController::class, 'getDashboardStats']);
         Route::get('admin/booth-requests/pending', [BoothBookingController::class, 'getPendingRequests']);
        Route::post('admin/booth-requests/{id}/approve', [BoothBookingController::class, 'approveRequest']);
       Route::get('orders', [OrderController::class, 'index']);
       Route::delete('orders/{id}', [OrderController::class, 'destroy']);
     Route::post('orders/{id}/approve', [OrderController::class, 'approveOrder']);
       });
});