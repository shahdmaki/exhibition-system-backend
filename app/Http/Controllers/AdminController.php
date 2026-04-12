<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

  return $this->sendResponse($exhibitors, 'تم جلب العارضين بنجاح.');
}
public function getDashboardStats()
    {
        
        $totalExhibitors = User::where('role', 'exhibitor')->count();
        $pendingExhibitors = User::where('role', 'exhibitor')->where('is_approved', 0)->count();

        
        $totalProducts = Product::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        
       
        $totalEarnings = Order::where('status', 'completed')->sum('total_price');

      
        $bestSeller = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderBy('total_sold', 'desc')
            ->with('product:id,name')
            ->first();

        // 3. تجميع كل البيانات في مصفوفة واحدة
        $stats = [
            'users' => [
                'total_exhibitors' => $totalExhibitors,
                'pending_exhibitors' => $pendingExhibitors,
            ],
            'orders' => [
                'pending_orders_count' => $pendingOrders,
                'total_earnings' => $totalEarnings,
                'best_selling_product' => $bestSeller ? $bestSeller->product->name : 'N/A',
            ],
            'content' => [
                'total_products' => $totalProducts,
                'total_exhibitions' => \App\Models\Exhibition::count(),
            ]
        ];

        // 4. استخدام الرد الموحد الذي أنشأناه في الـ Controller الأساسي
        return $this->sendResponse($stats, 'تم جلب إحصائيات لوحة التحكم بنجاح.');
    }
}