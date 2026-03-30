<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ProductController extends Controller
{
    public function store(Request $request)
{
  
   $validatedData = $request->validate([
        'name' => 'required|string|max:255', 
        'price' => 'required|numeric|min:0', 
        'quantity' => 'required|integer|min:1',
        'category_id' => 'required|exists:categories,id', 
        'booth_id' => 'required|exists:booths,id', 
        'exhibition_id' => 'required|exists:exhibitions,id', 
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);
    $userId = auth()->guard('api')->id();
    $hasApprovedBooking = \App\Models\BoothRequest::where('user_id', $userId)
        ->where('booth_id', $request->booth_id)
        ->where('exhibition_id', $request->exhibition_id)
        ->where('status', 'approved')
        ->exists();
        if (!$hasApprovedBooking) {
        return response()->json([
            'message' => 'عذراً، لا يمكنك إضافة منتجات لهذا الجناح. يجب أن يكون لديك حجز مقبول من قبل الأدمن أولاً.'
        ], 403); // 403 تعني Forbidden (غير مسموح)
    }
      $data = $validatedData;

    
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('products', 'public');
        $data['image'] = $path;
    }
  $data['user_id'] = auth()->guard('api')->id();
     $product = Product::create($data);
$product->exhibitions()->attach($request->exhibition_id, [
        'booth_id' => $request->booth_id
    ]);
    return response()->json([
        'message' => 'تمت إضافة المنتج مع الصورة بنجاح وتعيينه لك كعارض!',
        'product' => $product->load('exhibitions')
    ], 201);
}
  public function getProductsByBooth($id)

{
    $products = Product::where('booth_id', $id)->get();

    if ($products->isEmpty()) 
        {
        return response()->json([
            'message' => 'لا توجد منتجات لهذا الجناح حالياً'
        ], 404);
    }
$products->transform(function ($product) {
        $product->image = asset('storage/' . $product->image);
        return $product;
    });
    return response()->json($products, 200);
}
public function index()
{
    $products = Product::all();
    $products = Product::latest()->get();
    $products->transform(function ($product) {
        $product->image = asset('storage/' . $product->image);
        return $product;
    });
    return response()->json($products, 200);
}
public function show($id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['message' => 'المنتج غير موجود'], 404);
    }

  return response()->json([
        'id'          => $product->id,
        'name'        => $product->name,
        'price'       => $product->price,
        'quantity'    => $product->quantity,
        'category_id' => $product->category_id,
        'booth_id'    => $product->booth_id,
        'image_url'   => asset('storage/' . $product->image), 
        'created_at'  => $product->created_at,
        'updated_at'  => $product->updated_at,
    ], 200);
}
public function update(Request $request, $id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['message' => 'المنتج غير موجود'], 404);
    }

    // 1. قوانين التحقق (Validation) - اختيارية ولكن يفضل وجودها
    $request->validate([
        'name' => 'string|max:255',
        'price' => 'numeric|min:0',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $data = $request->all();

    // 2. معالجة الصورة الجديدة
    if ($request->hasFile('image')) {
        // أ - حذف الصورة القديمة من الهاردسك إذا كانت موجودة
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // ب - رفع الصورة الجديدة وتخزين مسارها
        $path = $request->file('image')->store('products', 'public');
        $data['image'] = $path;
    }

    // 3. تحديث البيانات في قاعدة البيانات
    $product->update($data);

    return response()->json([
        'message' => 'تم تحديث المنتج وصورته بنجاح',
        'product' => $product
    ], 200);
}
public function destroy($id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['message' => 'المنتج غير موجود'], 404);
    }
    if ($product->image) {
Storage::disk('public')->delete($product->image);
    }
    $product->delete();

    return response()->json(['message' => 'تم حذف المنتج بنجاح'], 200);
}
public function search($name)
{
       $products = Product::where('name', 'like', '%' . $name . '%')->get();

    
    if ($products->isEmpty()) {
        return response()->json(['message' => 'عذراً، لا يوجد منتج بهذا الاسم'], 404);
    }
    $products->transform(function ($product) {
        if ($product->image) {
            $product->image = url('storage/' . $product->image);
        }
        return $product;
    });

    return response()->json($products, 200);
}
public function myProducts()
{
    
    $currentUserId = auth()->guard('api')->id();

    
    $products = Product::where('user_id', $currentUserId)
                       ->with('category')
                       ->get();

    // 3. إرجاع النتيجة
    return response()->json([
        'status' => 'success',
        'count'  => $products->count(),
        'data'   => $products
    ], 200);
}
// داخل ProductController.php
public function allProducts()
{
    $products = Product::with(['category', 'booths', 'user'])->get();
    return response()->json([
        'status' => 'success',
        'data' => $products
    ]);
}
}
