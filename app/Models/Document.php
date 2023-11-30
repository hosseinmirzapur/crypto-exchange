<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;
    protected $fillable = [
        'type', 'image', 'status'
    ];

    const TYPES = ["SELFIE", 'BANK_CARD', "NATIONAL_CARD"];
    const STATUS = ["REJECTED", "PENDING", "ACCEPTED", "CONFLICTS", "BLURRED"];

    public function getImageAttribute($value)
    {
        return Storage::url($value);
    }

}
