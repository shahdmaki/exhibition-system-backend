<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoothRequest extends Model
{
  protected $fillable = [
        'user_id', 
        'booth_id', 
        'exhibition_id', 
        'status', 
        'notes'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function booth()
    {
        return $this->belongsTo(Booth::class);
    }
    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
