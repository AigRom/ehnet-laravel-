<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'ehak_code',
        'parent_ehak_code',
        'level',
        'name_et', 'name_en', 'name_ru',
        'full_label_et', 'full_label_en', 'full_label_ru',
        'is_valid',
    ];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
