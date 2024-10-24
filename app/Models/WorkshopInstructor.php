<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkshopInstructor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
            'name',
            'occupation',
            'avatar',
    ];

    public function workshops(): HasMany
    {
        return $this->hasMany(Workshop::class);
    }

   

}