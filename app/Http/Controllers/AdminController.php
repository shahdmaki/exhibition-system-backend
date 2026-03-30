<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
public function approveExhibitor($id){
    $user = User::findOrFail($id);
    if ($user->role !== 'exhibitor') {
        return response()->json(['message' => 'هذا المستخدم ليس عارضاً!'], 400);
    }
    $user->is_approved = true;
$user->save();
return response()->json([
            'status'  => 'success',
            'message' => 'تمت الموافقة على العارض ' . $user->name . ' بنجاح.',
            'data'    => $user
        ], 200);
}
//عرض قائمة "فقط" للناس الذين يحتاجون موافقة.
public function getPendingExhibitors()
{
    $pending = User::where('role', 'exhibitor')
                   ->where('is_approved', false)
                   ->get();

    return response()->json([
        'status' => 'success',
        'count'  => $pending->count(),
        'data'   => $pending
    ], 200);
}
public function getAllExhibitors()
{
    $exhibitors = User::where('role', 'exhibitor')->get();

    return response()->json([
        'status' => 'success',
        'data'   => $exhibitors
    ], 200);
}
public function getDashboardStats()
{
    return response()->json([
        'total_exhibitors' => \App\Models\User::where('role', 'exhibitor')->count(),
        'pending_exhibitors' => \App\Models\User::where('role', 'exhibitor')->where('is_approved', 0)->count(),
        'total_products' => \App\Models\Product::count(),
        'total_booth_requests' => \App\Models\BoothRequest::count(),
        'pending_booth_requests' => \App\Models\BoothRequest::where('status', 'pending')->count(),
        'total_exhibitions' => \App\Models\Exhibition::count(), // عدد المعارض الكلي
'approved_booths_count' => \App\Models\BoothRequest::where('status', 'approved')->count(), // عدد الأجنحة المحجوزة فعلياً

    ], 200);
}
}
