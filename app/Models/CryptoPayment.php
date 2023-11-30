<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'crypto_network_id',
        'address',
        'memo',
        'tag',
        'tx_id',
        'withdraw_id',
        'commission'
    ];

    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'payment');
    }

    public function network()
    {
        return $this->belongsTo(CryptoNetwork::class, 'crypto_network_id', 'id');
    }
}
