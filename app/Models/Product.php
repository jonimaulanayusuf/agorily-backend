<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'thumbnail_url',
        'price',
        'description',
        'stock',
    ];

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
