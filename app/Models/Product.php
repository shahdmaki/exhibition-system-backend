<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
    'category_id', 
   'booth_id', 
    'name', 
    'description', 
    'price', 
    'quantity',
    'user_id',
    'image'
];
  protected $appends = ['image_url'];
  public function getImageUrlAttribute()
{
    if ($this->image) 
        {
        return asset('storage/' . $this->image);
    }return null;
}
public function category() {
    return $this->belongsTo(Category::class);
}
public function user() {
    return $this->belongsTo(User::class);
}
public function exhibitions()
{
    return $this->belongsToMany(Exhibition::class, 'exhibition_booth_product')
                ->withPivot('booth_id')
                ->withTimestamps();
}

public function booths()
{
    return $this->belongsToMany(Booth::class, 'exhibition_booth_product')
                ->withPivot('exhibition_id')
                ->withTimestamps();
}

  
}


    