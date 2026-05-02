<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
class OrderController extends Controller
{
public function store(Request $request)
{
    $request->validate([
        'customer_name' => 'required|string',
        'customer_phone' => 'required|string',
        'items' => 'required|array',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    // منع تكرار الطلب خلال دقيقة واحدة
    $isDuplicate = Order::where('customer_phone', $request->customer_phone)
                        ->where('created_at', '>=', now()->subSeconds(60))
                        ->exists();

    if ($isDuplicate) {
       return $this->sendError('عذراً، هذا الطلب مسجل مسبقاً! يرجى الانتظار دقيقة.', 429);
    }

    try {
        return DB::transaction(function () use ($request) {
            
            // 1. إنشاء الطلب الأساسي بحالة "approved" تلقائياً
            $order = Order::create([
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'total_price' => 0, 
                'status' => 'approved' // تم التغيير إلى مقبول تلقائياً
            ]);

            $totalPrice = 0;

            foreach ($request->items as $item) {
                // استخدام lockForUpdate لمنع حدوث تضارب عند الطلب المتزامن في نفس اللحظة
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();

                // التحقق من توفر الكمية
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("عذراً، الكمية المطلوبة من {$product->name} غير متوفرة.");
                }

                // إنشاء تفاصيل الطلب
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                // خصم الكمية من المخزون تلقائياً [تحديث جدول المنتجات]
                $product->decrement('quantity', $item['quantity']);

                $totalPrice += ($product->price * $item['quantity']);
            }

            // تحديث السعر النهائي للطلب
            $order->update(['total_price' => $totalPrice]);
            
            return $this->sendResponse(['order_id' => $order->id], 'تمت عملية الشراء والقبول تلقائياً لتوفر الكمية!', 201);
        });
    } catch (\Exception $e) {
       return $this->sendError($e->getMessage(), 400);
    }
}
    public function index()
{
    // جلب كل الطلبات مع المنتجات اللي بداخلها
   $orders = Order::with('items.product')->latest()->get();
   $formattedOrders = $orders->map(function ($order) {
        return [
            'order_id'   => $order->id,
            'customer'   => $order->customer_name,
            'phone'      => $order->customer_phone,
            'total_bill' => $order->total_price,
            'date'       => $order->created_at->format('Y-m-d H:i'),
            'items_count'=> $order->items->count(), 
        ];
    });

  return $this->sendResponse($formattedOrders, 'تم جلب الطلبات بنجاح.');
}
public function approveOrder($id)
{
    // استخدمي items بدلاً من products لأنها العلاقة المعرفة عندك
    $order = Order::with('items.product')->findOrFail($id);

    if ($order->status !== 'completed') {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
             
                $item->product->decrement('quantity', $item->quantity);
            }
            $order->update(['status' => 'completed']);
        });

        return response()->json(['message' => 'تمت الموافقة وخصم الكميات من المخزن بنجاح!']);
    }

    return response()->json(['message' => 'الطلب مكتمل مسبقاً']);
}
public function destroy($id)
{
    return DB::transaction(function () use ($id) {
        // 1. البحث عن الطلب
        $order = Order::with('items')->find($id);

        if (!$order) {
            return response()->json(['message' => 'الطلب غير موجود!'], 404);
        }

        // 2. إرجاع الكميات للمخزن قبل الحذف (اختياري بس احترافي)
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('quantity', $item->quantity);
            }
        }

       
        $order->delete();

        return response()->json(['message' => 'تم حذف الطلب وإعادة الكميات للمخزن بنجاح.']);
    });
}
}
