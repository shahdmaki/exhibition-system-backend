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
        // 1. التحقق من البيانات القادمة من الزائر
        $request->validate([
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'items' => 'required|array', // مصفوفة تحتوي على المنتجات المطلوبة
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
           $isDuplicate = Order::where('customer_phone', $request->customer_phone)
                        ->where('created_at', '>=', now()->subSeconds(60))
                        ->exists();

    if ($isDuplicate) {
        return response()->json([
            'status' => 'error',
            'message' => 'عذراً، هذا الطلب مسجل مسبقاً! يرجى الانتظار دقيقة قبل المحاولة مرة أخرى.'
        ], 429); // 429 تعني Too Many Requests
    }
        // نستخدم DB::transaction عشان إذا صار خطأ بأي خطوة، يلغي كل شي وما تضرب البيانات
        return DB::transaction(function () use ($request) {
            
            // 2. إنشاء الطلب الأساسي
            $order = Order::create([
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'total_price' => 0, // سنحسبه بعد قليل
            ]);

            $totalPrice = 0;

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                
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

              
                $product->decrement('quantity', $item['quantity']);

                
                $totalPrice += ($product->price * $item['quantity']);
            }

            // 4. تحديث السعر الإجمالي النهائي في الطلب
            $order->update(['total_price' => $totalPrice]);
            $order->load('items.product');
            $formattedOrder = [
            'order_id'    => $order->id,
            'customer'    => $order->customer_name,
            'total_bill'  => $totalPrice, 
            'order_items' => $order->items->map(function ($detail) {
                return [
                    'product_name' => $detail->product->name,
                    'unit_price'   => $detail->price,
                    'quantity'     => $detail->quantity,
                    'subtotal'     => $detail->price * $detail->quantity,
                ];
            }),
        ];

        return response()->json([
            'message' => 'تم تسجيل طلبك بنجاح!',
            'data'    => $formattedOrder
        ], 201);
    
    });
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

    return response()->json([
        'status' => 'success',
        'count'  => $formattedOrders->count(),
        'orders' => $formattedOrders
    ]);
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
