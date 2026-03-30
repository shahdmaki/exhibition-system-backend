<?php

namespace App\Http\Controllers;
use App\Models\Booth;
use Illuminate\Http\Request;

class BoothController extends Controller
{
    public function store(Request $request)
    {
       
        $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'booth_number'  => 'required|string|max:50',
            'size'          => 'required|string',
            'price'         => 'required|numeric',
            'status'        => 'required|in:available,booked,under_maintenance',
        ]);

        
        $booth = Booth::create([
            'exhibition_id' => $request->exhibition_id,
            'booth_number'  => $request->booth_number,
            'size'          => $request->size,
            'price'         => $request->price,
            'status'        => $request->status,
        ]);

      
        return response()->json([
            'message' => 'تم إنشاء الجناح وربطه بالمعرض بنجاح',
            'data'    => $booth
        ], 201);
    }
    
public function index()
{
    $booths = Booth::all();
    return response()->json($booths, 200);
}


public function show($id)
{
    $booth = Booth::find($id);

    if (!$booth) {
        return response()->json(['message' => 'الجناح غير موجود'], 404);
    }

    return response()->json($booth, 200);
}


public function update(Request $request, $id)
{
    $booth = Booth::find($id);

    if (!$booth) {
        return response()->json(['message' => 'الجناح غير موجود'], 404);
    }

    $request->validate([
        'exhibition_id' => 'required|exists:exhibitions,id',
        'booth_number'  => 'required|string',
        'size'          => 'required|string',
        'price'         => 'required|numeric',
        'status'        => 'required|in:available,booked,under_maintenance',
    ]);

    $booth->update($request->all());

    return response()->json([
        'message' => 'تم تحديث بيانات الجناح بنجاح',
        'data'    => $booth
    ], 200);
}


public function destroy($id)
{
    $booth = Booth::find($id);

    if (!$booth) {
        return response()->json(['message' => 'الجناح غير موجود'], 404);
    }

    $booth->delete();

    return response()->json(['message' => 'تم حذف الجناح بنجاح'], 200);
}
}
