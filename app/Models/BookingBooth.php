<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingBooth extends Model
{
    protected $fillable = [
    'user_id', 
    'exhibition_id', 
    'booth_id', 
    'status', 
    'requested_categories'
];
public function user() {
    return $this->belongsTo(User::class);
}
public function exhibition() {
    return $this->belongsTo(Exhibition::class);
}
public function booth() {
    return $this->belongsTo(Booth::class);
}
protected $casts = [
    'requested_categories' => 'array',
];
}
