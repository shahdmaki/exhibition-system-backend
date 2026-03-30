<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',          // (admin, exhibitor, visitor)
        'phone',
        'company_name', 
        'is_approved',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
public function bookings()
{
    // المستخدم الواحد (العارض) يمكن أن يكون له عدة حجوزات
    return $this->hasMany(BookingBooth::class);
}
    // دالات JWT المطلوبة
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role // لتسهيل قراءة الدور من التوكن مباشرة
        ];
    }
    public function boothRequests()
{
    return $this->hasMany(BoothRequest::class);
}
}