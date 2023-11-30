<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use phpDocumentor\Reflection\Types\False_;
use function App\Helpers\current_user;
use function App\Helpers\errorResponse;

class Setting extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['setting_key', 'setting_value'];

    const DEFAULT_TWO_FACTOR = "EMAIL";

    const TWO_FACTOR = ['EMAIL', 'SMS', 'GOOGLE'];

    public function scopeKey(Builder $query, $key)
    {
        return $query->where('setting_key', $key);
    }
}
