<?php

namespace App\Models;

use App\Traits\Filter;
use App\Traits\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Account extends Model
{
    use Filter;

    use HasFactory, SoftDeletes, Status;

    protected $fillable = [
        'card_number', 'sheba', 'account_number', 'bank', 'status',
    ];
    const STATUS = ["REJECTED", "PENDING", "ACCEPTED", "CONFLICT", "USER_CONFLICT"];

    const SHEBA = [
        '010' => "markazi",
        '011' => "sanatomadan",
        '012' => "mellat",
        '013' => "refah",
        '014' => "maskan",
        '015' => "sepah",
        '016' => "keshavrzi",
        '017' => "melli",
        '018' => "tejarat",
        '019' => "saderat",
        '021' => "post",
        '022' => "tosee_taavon",
        '051' => "tosee",
        '052' => "ghavvamin",
        '053' => "karafarin",
        '054' => "parsian",
        '055' => "novin",
        '056' => "saman",
        '057' => "pasargad",
        '058' => "sarmayeh",
        '059' => "sina",
        '061' => "shahr",
        '062' => "ayandeh",
        '063' => "ansar",
        '064' => "gardeshgari",
        '065' => "hekmat",
        '066' => "day",
        '069' => "iranzamin",
        '070' => "resalat",
        '095' => "venezuela",
    ];

    protected $appends = ['logo', 'bank_label'];

    public function getBankLabelAttribute()
    {
        return !empty($this->bank) ? trans('bank.'.$this->bank) : '-';
    }

    public function getLogoAttribute()
    {
        $path = '/banks/' . $this->bank . '.png';
        $logo = '/logo/logo.png';
        return Storage::exists($path) ?
            Storage::url($path) :
            Storage::url($logo);
    }

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function findBank($sheba)
    {
        $e = substr($sheba, 2, 3);
        return static::SHEBA[$e] ?? "";
    }

    public function setShebaAttribute($value)
    {
        $this->attributes['sheba'] = $value;
        $this->attributes['bank'] = $this->findBank($value);

    }

}
