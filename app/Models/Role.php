<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'label', 'color', 'icon', 'status'];

    const STATUS = ['ACTIVATED', "DISABLED"];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::upper($value);
    }

    public function abilities()
    {
        return $this->belongsToMany(Ability::class)
            ->withTimestamps();
    }

    public function abilitiesArray()
    {
        return $this->abilities->flatten()->pluck('name')->unique();
    }

    public function addAbility($ability)
    {
        if (is_string($ability)) {
            $ability = Ability::whereName($ability)->firstOrFail();
        }

        $this->abilities()->sync($ability, false);
    }
}
