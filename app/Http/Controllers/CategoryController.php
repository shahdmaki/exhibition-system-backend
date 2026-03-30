<?php

namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
   public function index()
    {
        $categories = Category::all();
        return response()->json($categories, 200);
    }
    public function store(Request $request)
    {
        
        $request->validate([
            //unique:categories ممنوع تكرار 
            'name' => 'required|string|unique:categories|max:255',
        ]);
        $category = Category::create([
            'name' => $request->name
        ]);
        return response()->json([
            'message' => 'تم إنشاء التصنيف بنجاح',
            'data' => $category
        ], 201);
        
    }
    
public function show($id)
{
   
    $category = Category::find($id);

    if (!$category) {
        return response()->json([
            'message' => 'عذراً، هذا التصنيف غير موجود'
        ], 404);
    }

    return response()->json($category, 200);
}

public function update(Request $request, $id)
{
    $category = Category::find($id);

    if (!$category) {
        return response()->json(['message' => 'التصنيف غير موجود'], 404);
    }

   
    $request->validate([
        'name' => 'required|string|max:255|unique:categories,name,' . $id,
    ]);

    $category->update([
        'name' => $request->name
    ]);

    return response()->json([
        'message' => 'تم تحديث التصنيف بنجاح',
        'data' => $category
    ], 200);
}


public function destroy($id)
{
    $category = Category::find($id);

    if (!$category) {
        return response()->json(['message' => 'التصنيف غير موجود'], 404);
    }

    $category->delete();

    return response()->json([
        'message' => 'تم حذف التصنيف نهائياً'
    ], 200);
}
}
