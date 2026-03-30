<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoothRequest;
use App\Models\Booth;
use Illuminate\Support\Facades\Validator;
class BoothBookingController extends Controller
{
  public function requestBooth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booth_id' => 'required|exists:booths,id',
            'exhibition_id' => 'required|exists:exhibitions,id',
            'notes' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $isTaken = BoothRequest::where('booth_id', $request->booth_id)
                                ->where('status', 'approved')
                                ->exists();
                                if ($isTaken) {
            return response()->json(['message' => 'عذراً، هذا الجناح محجوز بالفعل!'], 400);
        }
        $booking = BoothRequest::create([
            'user_id' => auth()->guard('api')->id(),
            'booth_id' => $request->booth_id,
            'exhibition_id' => $request->exhibition_id,
            'notes' => $request->notes,
            'status' => 'pending' 
        ]);
        return response()->json([
            'message' => 'تم إرسال طلب حجز الجناح بنجاح، يرجى انتظار موافقة الأدمن.',
            'booking' => $booking
        ], 201);
    }
    public function getPendingRequests()
{
    $requests = BoothRequest::with(['user', 'booth', 'exhibition'])
                            ->where('status', 'pending')
                            ->get();

    return response()->json([
        'status' => 'success',
        'count'  => $requests->count(),
        'data'   => $requests
    ], 200);
}
public function approveRequest($id)
{
    $booking = BoothRequest::find($id);

    if (!$booking) {
        return response()->json(['message' => 'الطلب غير موجود'], 404);
    }
    $booking->update(['status' => 'approved']);

    return response()->json([
        'message' => 'تمت الموافقة على حجز الجناح بنجاح!',
       'booking' => $booking->load(['user', 'booth'])
    ], 200);
}
}
