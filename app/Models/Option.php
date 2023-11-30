<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'section',
        'option_key',
        'option_value',
        'key_label',
    ];

    const SECTIONS = ["SOCIAL", "CONTACT", "App", "ACCOUNT"];

    /**
     * @param $query
     * @param $section
     * @param $key
     * @return mixed
     */

    public function scopeSectionKey($query, $section, $key)
    {
        return $query->where('section', $section)
            ->where('option_key', $key);
    }
}
