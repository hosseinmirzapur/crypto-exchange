<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Finnotech extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','type','items'];

    protected $casts = ['items' => 'array'];
}
