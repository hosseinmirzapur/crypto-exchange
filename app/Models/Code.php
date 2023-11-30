<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Code extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'code', 'position', 'created_at'];
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'expire_at' => 'datetime'
    ];

    /**
     *
     * @param $user User
     * @param null $situation
     * @return int
     */
    public static function generateCode($user, $position = null): int
    {
        $code = rand(100000, 999999);

        static::create([
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'position' => $position,
            'created_at' => Carbon::now()
        ]);

        return $code;
    }

    protected static function booted()
    {
        static::creating(function ($code) {
            $code->expire_at = now()->addMinutes(2);
        });
    }
}
