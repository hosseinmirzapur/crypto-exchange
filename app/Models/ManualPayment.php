<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ManualPayment extends Model
{
    use HasFactory;
    protected $fillable = ['image', 'account_id', 'ref_id'];

    public function getImageAttribute($image_path)
    {
        return Storage::url($image_path);
    }

    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'payment');
    }

}
