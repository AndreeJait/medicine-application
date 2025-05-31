<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'description',
        'unit',
        'stock',
    ];

    protected $with = ['images']; // ⬅️ ini akan auto-load images

    public function images()
    {
        return $this->hasMany(MedicineImage::class);
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
