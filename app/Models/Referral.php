<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'link'];
    public $timestamps = false;


    public function getLinkAttribute($value)
    {
        return config('app.front_login_url'). '?code='. $value;
    }


    /**
     * @return Referral
     */
    public function generateCode(): Referral
    {
        $length = strlen($this->id);

        do {

            $code = mt_rand(pow(10, ($length + 5)), pow(10, ($length + 6)));
            $link = Str::random();

            $referral = static::where('code', $code)
                ->orWhere('link', $link)
                ->exists();

        } while ($referral);

        $this->fill([
            'code' => $code,
            'link' => $link
        ]);


        return $this;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
