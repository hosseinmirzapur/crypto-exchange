<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlinePayment extends Model
{
    use HasFactory;

    protected $fillable = ['token', 'commission', 'transaction_id', 'ref_number', 'settlement_id', 'gateway'];

    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'payment');
    }
}
