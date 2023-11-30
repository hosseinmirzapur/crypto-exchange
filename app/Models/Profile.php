<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'national_code',
        'birthday',
        'mobile',
        'address',
        'phone',
    ];

    protected $casts = ['birthday' => 'datetime'];

    public function getPersianBirthdayAttribute()
    {
        return Jalalian::fromCarbon($this->birthday ??
            now()->subYears(10))->format('Y/m/d');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function code()
    {
        return $this->hasOne(Code::class,'user_id', 'user_id');
    }
}
