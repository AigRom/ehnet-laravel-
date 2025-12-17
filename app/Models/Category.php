<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name_et', 'name_en', 'name_ru',
        'slug',
        'is_active',
        'sort_order',
    ];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
