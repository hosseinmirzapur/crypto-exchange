<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Rank extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'label', 'criterion', 'fee', 'description', 'next_description'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::replace(' ', '_', strtoupper($value));
    }

    public function getFeeInPercent()
    {
        return $this->fee * 100;
    }

}
