<?php

namespace App\Models;

use App\Traits\Filter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory, Filter;
    protected $fillable = ['name', 'email', 'description', 'is_seen'];
    protected $casts = ['is_seen' => 'boolean'];

    public function toggle()
    {
        $this->is_seen = !$this->is_seen;
        $this->save();
    }

}
