<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exhibition;
class ExhibitionController extends Controller
{
    public function index()
    {
        $exhibitions = Exhibition::all();
        return response()->json($exhibitions, 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'location'   => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after:start_date',   
            'status'     => 'required|in:upcoming,active,completed',
        ]);
        $exhibition = Exhibition::create($request->all());

        return response()->json([
            'message' => 'تم إنشاء المعرض بنجاح',
            'data'    => $exhibition
        ], 201);
    }
    public function show($id)
{
    $exhibition = Exhibition::find($id);

    if (!$exhibition) {
        return response()->json(['message' => 'المعرض غير موجود'], 404);
    }

    return response()->json($exhibition, 200);
}

public function update(Request $request, $id)
{
    $exhibition = Exhibition::find($id);

    if (!$exhibition) {
        return response()->json(['message' => 'المعرض غير موجود'], 404);
    }

    
    $request->validate([
        'title'      => 'required|string|max:255',
        'location'   => 'required|string',
        'start_date' => 'required|date',
        'end_date'   => 'required|date|after:start_date',
        'status'     => 'required|in:upcoming,active,completed',
    ]);

    $exhibition->update($request->all());

    return response()->json([
        'message' => 'تم تحديث بيانات المعرض بنجاح',
        'data'    => $exhibition
    ], 200);
}

public function destroy($id)
{
    $exhibition = Exhibition::find($id);

    if (!$exhibition) {
        return response()->json(['message' => 'المعرض غير موجود'], 404);
    }

    $exhibition->delete();

    return response()->json(['message' => 'تم حذف المعرض بنجاح'], 200);
}
}
