<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booth extends Model
{
    protected $fillable = ['exhibition_id',
     'booth_number',
      'size', 
      'price',
       'status', 
    'coordinates'];

    
    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
    public function booking()
    {
        return $this->hasOne(BookingBooth::class);
    }
    public function products()
{
    
    return $this->belongsToMany(Product::class, 'exhibition_booth_product')
                ->withPivot('exhibition_id')
                ->withTimestamps();
}
}