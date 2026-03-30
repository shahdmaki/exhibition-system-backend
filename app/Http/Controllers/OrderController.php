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

            return response()->json([
                'message' => 'تم تسجيل طلبك بنجاح!',
                'order_id' => $order->id,
                'total' => $totalPrice
            ], 201);
        });
    }
}
