<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exhibition extends Model
{
    protected $fillable = [
        'title', 
        'description', 
        'start_date', 
        'end_date', 
        'location', 
        'status', 
        'floor_plan_image'
    ];
    public function categories()
{
    return $this->belongsToMany(Category::class);
} 
public function booths()
{
    return $this->hasMany(Booth::class);
}
public function products()
{
    return $this->belongsToMany(Product::class, 'exhibition_booth_product')
                ->withPivot('booth_id')
                ->withTimestamps();
}
}
